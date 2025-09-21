<?php

/**
 * Academic Calendar Controller
 * 
 * Handles academic calendar and events
 */
class CalendarController extends Controller
{
    private $calendarModel;
    private $eventModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->calendarModel = new AcademicCalendar();
        $this->eventModel = new Event();
    }
    
    /**
     * Display academic calendar
     */
    public function index()
    {
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $view = $this->input('view', 'month'); // month, week, day
        
        $events = $this->getCalendarEvents($year, $month);
        $holidays = $this->getHolidays($year, $month);
        $examDates = $this->getExamDates($year, $month);
        
        $this->render('academic/calendar/index', [
            'title' => 'Academic Calendar',
            'events' => $events,
            'holidays' => $holidays,
            'examDates' => $examDates,
            'month' => $month,
            'year' => $year,
            'view' => $view
        ]);
    }
    
    /**
     * Get events for calendar (AJAX)
     */
    public function events()
    {
        $start = $this->input('start');
        $end = $this->input('end');
        
        $events = $this->calendarModel->getEventsBetween($start, $end);
        
        // Format events for calendar
        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_date'],
                'end' => $event['end_date'],
                'color' => $this->getEventColor($event['type']),
                'description' => $event['description']
            ];
        }
        
        return $this->json($formattedEvents);
    }
    
    /**
     * Store new event
     */
    public function store()
    {
        $this->requirePermission('calendar_manage');
        
        $data = $this->validate([
            'title' => 'required|min:2|max:200',
            'description' => 'max:1000',
            'event_type' => 'required|in:academic,exam,holiday,meeting,event,deadline',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'max:200',
            'is_holiday' => 'boolean',
            'is_working_day' => 'boolean',
            'target_audience' => 'required|in:all,students,faculty,staff,parents',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled'
        ]);
        
        try {
            $data['created_by'] = $this->user()['id'];
            $eventId = $this->calendarModel->create($data);
            
            // Send notifications if required
            if ($data['target_audience'] !== 'all') {
                $this->sendEventNotifications($data);
            }
            
            $this->logActivity('calendar_event_created', "Created calendar event: {$data['title']}", $data);
            
            return $this->json(['success' => true, 'message' => 'Event created successfully', 'event_id' => $eventId]);
            
        } catch (Exception $e) {
            Logger::error('Calendar event creation failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to create event']);
        }
    }
    
    /**
     * Update event
     */
    public function update($id)
    {
        $this->requirePermission('calendar_manage');
        
        $event = $this->calendarModel->find($id);
        if (!$event) {
            return $this->json(['success' => false, 'message' => 'Event not found']);
        }
        
        $data = $this->validate([
            'title' => 'required|min:2|max:200',
            'description' => 'max:1000',
            'event_type' => 'required|in:academic,exam,holiday,meeting,event,deadline',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'max:200',
            'is_holiday' => 'boolean',
            'is_working_day' => 'boolean',
            'target_audience' => 'required|in:all,students,faculty,staff,parents',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled'
        ]);
        
        try {
            $data['updated_by'] = $this->user()['id'];
            $this->calendarModel->update($id, $data);
            
            $this->logActivity('calendar_event_updated', "Updated calendar event: {$data['title']}", $data);
            
            return $this->json(['success' => true, 'message' => 'Event updated successfully']);
            
        } catch (Exception $e) {
            Logger::error('Calendar event update failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to update event']);
        }
    }
    
    /**
     * Delete event
     */
    public function destroy($id)
    {
        $this->requirePermission('calendar_manage');
        
        $event = $this->calendarModel->find($id);
        if (!$event) {
            return $this->json(['success' => false, 'message' => 'Event not found']);
        }
        
        try {
            $this->calendarModel->delete($id);
            
            $this->logActivity('calendar_event_deleted', "Deleted calendar event: {$event['title']}", $event);
            
            return $this->json(['success' => true, 'message' => 'Event deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Calendar event deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete event']);
        }
    }
    
    /**
     * Import holidays
     */
    public function importHolidays()
    {
        $this->requirePermission('calendar_manage');
        
        $year = $this->input('year', date('Y'));
        $country = $this->input('country', 'IN');
        
        try {
            $holidays = $this->fetchNationalHolidays($year, $country);
            $importedCount = 0;
            
            foreach ($holidays as $holiday) {
                // Check if holiday already exists
                $existing = $this->calendarModel->first(
                    'start_date = :date AND event_type = :type AND title = :title',
                    ['date' => $holiday['date'], 'type' => 'holiday', 'title' => $holiday['name']]
                );
                
                if (!$existing) {
                    $this->calendarModel->create([
                        'title' => $holiday['name'],
                        'event_type' => 'holiday',
                        'start_date' => $holiday['date'],
                        'end_date' => $holiday['date'],
                        'is_holiday' => 1,
                        'is_working_day' => 0,
                        'target_audience' => 'all',
                        'priority' => 'medium',
                        'status' => 'scheduled',
                        'created_by' => $this->user()['id']
                    ]);
                    $importedCount++;
                }
            }
            
            $this->logActivity('holidays_imported', "Imported {$importedCount} holidays for year {$year}");
            
            return $this->json(['success' => true, 'message' => "Imported {$importedCount} holidays"]);
            
        } catch (Exception $e) {
            Logger::error('Holiday import failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to import holidays']);
        }
    }
    
    /**
     * Get calendar events
     */
    private function getCalendarEvents($year, $month)
    {
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        return $this->calendarModel->where(
            'start_date BETWEEN :start_date AND :end_date',
            ['start_date' => $startDate, 'end_date' => $endDate],
            'start_date ASC'
        );
    }
    
    /**
     * Get holidays
     */
    private function getHolidays($year, $month)
    {
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        return $this->calendarModel->where(
            'start_date BETWEEN :start_date AND :end_date AND is_holiday = :holiday',
            ['start_date' => $startDate, 'end_date' => $endDate, 'holiday' => 1],
            'start_date ASC'
        );
    }
    
    /**
     * Get exam dates
     */
    private function getExamDates($year, $month)
    {
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $examModel = new Exam();
        return $examModel->where(
            'exam_date BETWEEN :start_date AND :end_date',
            ['start_date' => $startDate, 'end_date' => $endDate],
            'exam_date ASC'
        );
    }
    
    /**
     * Get event color based on type
     */
    private function getEventColor($type)
    {
        $colors = [
            'academic' => '#007bff',
            'exam' => '#dc3545',
            'holiday' => '#28a745',
            'meeting' => '#ffc107',
            'event' => '#17a2b8',
            'deadline' => '#fd7e14'
        ];
        
        return $colors[$type] ?? '#6c757d';
    }
    
    /**
     * Send event notifications
     */
    private function sendEventNotifications($eventData)
    {
        $recipients = $this->getEventRecipients($eventData['target_audience']);
        
        $notificationData = [
            'title' => 'New Calendar Event',
            'message' => "New event: {$eventData['title']} on {$eventData['start_date']}",
            'type' => 'info',
            'delivery_method' => 'system,email'
        ];
        
        $notificationModel = new Notification();
        $notificationModel->sendBulk($recipients, $notificationData);
    }
    
    /**
     * Get event recipients
     */
    private function getEventRecipients($targetAudience)
    {
        $recipients = [];
        
        switch ($targetAudience) {
            case 'students':
                $students = $this->studentModel->where('status = :status', ['status' => STATUS_ACTIVE]);
                foreach ($students as $student) {
                    $recipients[] = ['id' => $student['id'], 'type' => 'student'];
                }
                break;
            case 'faculty':
                $faculty = $this->facultyModel->where('status = :status', ['status' => STATUS_ACTIVE]);
                foreach ($faculty as $member) {
                    $recipients[] = ['id' => $member['id'], 'type' => 'faculty'];
                }
                break;
            // Add other audience types as needed
        }
        
        return $recipients;
    }
    
    /**
     * Fetch national holidays (placeholder)
     */
    private function fetchNationalHolidays($year, $country)
    {
        // This would integrate with a holiday API
        // For now, return some common holidays
        return [
            ['name' => 'New Year\'s Day', 'date' => "{$year}-01-01"],
            ['name' => 'Independence Day', 'date' => "{$year}-08-15"],
            ['name' => 'Gandhi Jayanti', 'date' => "{$year}-10-02"]
        ];
    }
}