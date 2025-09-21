<?php

/**
 * Academic Calendar Model
 * 
 * Handles academic calendar and events data operations
 */
class AcademicCalendar extends Model
{
    protected $table = 'academic_calendar';
    protected $fillable = [
        'title', 'description', 'event_type', 'start_date', 'end_date',
        'start_time', 'end_time', 'location', 'is_holiday', 'is_working_day',
        'target_audience', 'priority', 'created_by', 'updated_by', 'status'
    ];
    
    /**
     * Get events between dates
     */
    public function getEventsBetween($startDate, $endDate)
    {
        return $this->where(
            'start_date BETWEEN :start_date AND :end_date OR end_date BETWEEN :start_date AND :end_date',
            ['start_date' => $startDate, 'end_date' => $endDate],
            'start_date ASC'
        );
    }
    
    /**
     * Get events by type
     */
    public function getByType($eventType, $startDate = null, $endDate = null)
    {
        $where = 'event_type = :type';
        $params = ['type' => $eventType];
        
        if ($startDate && $endDate) {
            $where .= ' AND start_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Get holidays
     */
    public function getHolidays($year = null, $month = null)
    {
        $where = 'is_holiday = :holiday';
        $params = ['holiday' => 1];
        
        if ($year && $month) {
            $where .= ' AND YEAR(start_date) = :year AND MONTH(start_date) = :month';
            $params['year'] = $year;
            $params['month'] = $month;
        } elseif ($year) {
            $where .= ' AND YEAR(start_date) = :year';
            $params['year'] = $year;
        }
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Get working days
     */
    public function getWorkingDays($year, $month)
    {
        $where = 'is_working_day = :working AND YEAR(start_date) = :year AND MONTH(start_date) = :month';
        $params = ['working' => 1, 'year' => $year, 'month' => $month];
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Get upcoming events
     */
    public function getUpcomingEvents($days = 30, $targetAudience = null)
    {
        $endDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $where = 'start_date BETWEEN :today AND :end_date AND status = :status';
        $params = ['today' => today(), 'end_date' => $endDate, 'status' => 'scheduled'];
        
        if ($targetAudience) {
            $where .= ' AND (target_audience = :audience OR target_audience = :all)';
            $params['audience'] = $targetAudience;
            $params['all'] = 'all';
        }
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Get events by priority
     */
    public function getByPriority($priority)
    {
        return $this->where('priority = :priority AND status = :status', [
            'priority' => $priority,
            'status' => 'scheduled'
        ], 'start_date ASC');
    }
    
    /**
     * Get calendar statistics
     */
    public function getStats($year = null)
    {
        $where = '1=1';
        $params = [];
        
        if ($year) {
            $where .= ' AND YEAR(start_date) = :year';
            $params['year'] = $year;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_events,
                    SUM(CASE WHEN event_type = 'academic' THEN 1 ELSE 0 END) as academic_events,
                    SUM(CASE WHEN event_type = 'exam' THEN 1 ELSE 0 END) as exam_events,
                    SUM(CASE WHEN event_type = 'holiday' THEN 1 ELSE 0 END) as holidays,
                    SUM(CASE WHEN event_type = 'meeting' THEN 1 ELSE 0 END) as meetings,
                    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority,
                    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_events,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_events
                FROM {$this->table}
                WHERE {$where}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Check for conflicts
     */
    public function hasConflict($startDate, $endDate, $location = null, $excludeId = null)
    {
        $where = '(start_date <= :end_date AND end_date >= :start_date)';
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($location) {
            $where .= ' AND location = :location';
            $params['location'] = $location;
        }
        
        if ($excludeId) {
            $where .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->exists($where, $params);
    }
    
    /**
     * Get monthly event count
     */
    public function getMonthlyEventCount($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(start_date) as month,
                    COUNT(*) as event_count,
                    SUM(CASE WHEN event_type = 'holiday' THEN 1 ELSE 0 END) as holidays,
                    SUM(CASE WHEN event_type = 'exam' THEN 1 ELSE 0 END) as exams
                FROM {$this->table}
                WHERE YEAR(start_date) = :year
                GROUP BY MONTH(start_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
}