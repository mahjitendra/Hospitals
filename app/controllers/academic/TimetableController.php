<?php

/**
 * Timetable Management Controller
 * 
 * Handles timetable creation and management
 */
class TimetableController extends Controller
{
    private $timetableModel;
    private $classModel;
    private $sectionModel;
    private $subjectModel;
    private $facultyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->timetableModel = new Timetable();
        $this->classModel = new ClassModel();
        $this->sectionModel = new Section();
        $this->subjectModel = new Subject();
        $this->facultyModel = new Faculty();
    }
    
    /**
     * Display timetable
     */
    public function index()
    {
        $classId = $this->input('class_id');
        $sectionId = $this->input('section_id');
        $facultyId = $this->input('faculty_id');
        $view = $this->input('view', 'class'); // class, faculty, room
        
        $timetable = [];
        $title = 'Timetable';
        
        if ($view === 'class' && $classId) {
            $timetable = $this->getClassTimetable($classId, $sectionId);
            $class = $this->classModel->find($classId);
            $title = 'Timetable - ' . $class['class_name'];
            
            if ($sectionId) {
                $section = $this->sectionModel->find($sectionId);
                $title .= ' (' . $section['section_name'] . ')';
            }
        } elseif ($view === 'faculty' && $facultyId) {
            $timetable = $this->getFacultyTimetable($facultyId);
            $faculty = $this->facultyModel->find($facultyId);
            $title = 'Timetable - ' . $faculty['first_name'] . ' ' . $faculty['last_name'];
        }
        
        $classes = $this->classModel->all('class_name ASC');
        $sections = $this->sectionModel->all('section_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/timetable/index', [
            'title' => $title,
            'timetable' => $timetable,
            'classes' => $classes,
            'sections' => $sections,
            'faculty' => $faculty,
            'filters' => [
                'class_id' => $classId,
                'section_id' => $sectionId,
                'faculty_id' => $facultyId,
                'view' => $view
            ]
        ]);
    }
    
    /**
     * Show create timetable form
     */
    public function create()
    {
        $this->requirePermission('timetable_create');
        
        $classes = $this->classModel->all('class_name ASC');
        $sections = $this->sectionModel->all('section_name ASC');
        $subjects = $this->subjectModel->all('subject_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/timetable/create', [
            'title' => 'Create Timetable',
            'classes' => $classes,
            'sections' => $sections,
            'subjects' => $subjects,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Store timetable
     */
    public function store()
    {
        $this->requirePermission('timetable_create');
        
        $data = $this->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'faculty_id' => 'required|exists:faculty,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required',
            'end_time' => 'required',
            'room_number' => 'max:50',
            'academic_session' => 'required'
        ]);
        
        // Validate time
        if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
            $this->flash('error', 'End time must be after start time');
            $this->back();
            return;
        }
        
        // Check for conflicts
        $conflicts = $this->checkTimetableConflicts($data);
        if (!empty($conflicts)) {
            $this->flash('error', 'Timetable conflicts found: ' . implode(', ', $conflicts));
            $this->back();
            return;
        }
        
        try {
            $timetableId = $this->timetableModel->create($data);
            
            $this->logActivity('timetable_created', 'Created timetable entry', $data);
            $this->flash('success', 'Timetable created successfully');
            
            $this->redirect('/academic/timetable');
            
        } catch (Exception $e) {
            Logger::error('Timetable creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create timetable');
            $this->back();
        }
    }
    
    /**
     * Show edit timetable form
     */
    public function edit($id)
    {
        $this->requirePermission('timetable_edit');
        
        $timetable = $this->timetableModel->find($id);
        if (!$timetable) {
            $this->flash('error', 'Timetable entry not found');
            $this->redirect('/academic/timetable');
        }
        
        $classes = $this->classModel->all('class_name ASC');
        $sections = $this->sectionModel->all('section_name ASC');
        $subjects = $this->subjectModel->all('subject_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/timetable/edit', [
            'title' => 'Edit Timetable',
            'timetable' => $timetable,
            'classes' => $classes,
            'sections' => $sections,
            'subjects' => $subjects,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Update timetable
     */
    public function update($id)
    {
        $this->requirePermission('timetable_edit');
        
        $timetable = $this->timetableModel->find($id);
        if (!$timetable) {
            $this->flash('error', 'Timetable entry not found');
            $this->redirect('/academic/timetable');
        }
        
        $data = $this->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'faculty_id' => 'required|exists:faculty,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required',
            'end_time' => 'required',
            'room_number' => 'max:50',
            'academic_session' => 'required'
        ]);
        
        // Check for conflicts (excluding current entry)
        $conflicts = $this->checkTimetableConflicts($data, $id);
        if (!empty($conflicts)) {
            $this->flash('error', 'Timetable conflicts found: ' . implode(', ', $conflicts));
            $this->back();
            return;
        }
        
        try {
            $this->timetableModel->update($id, $data);
            
            $this->logActivity('timetable_updated', 'Updated timetable entry', $data);
            $this->flash('success', 'Timetable updated successfully');
            
            $this->redirect('/academic/timetable');
            
        } catch (Exception $e) {
            Logger::error('Timetable update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update timetable');
            $this->back();
        }
    }
    
    /**
     * Delete timetable entry
     */
    public function destroy($id)
    {
        $this->requirePermission('timetable_delete');
        
        $timetable = $this->timetableModel->find($id);
        if (!$timetable) {
            return $this->json(['success' => false, 'message' => 'Timetable entry not found']);
        }
        
        try {
            $this->timetableModel->delete($id);
            
            $this->logActivity('timetable_deleted', 'Deleted timetable entry', $timetable);
            
            return $this->json(['success' => true, 'message' => 'Timetable entry deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Timetable deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete timetable entry']);
        }
    }
    
    /**
     * Get class timetable
     */
    private function getClassTimetable($classId, $sectionId = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($sectionId) {
            $where .= ' AND (section_id = :section_id OR section_id IS NULL)';
            $params['section_id'] = $sectionId;
        }
        
        $db = Database::getInstance();
        $sql = "SELECT t.*, s.subject_name, s.subject_code, f.first_name, f.last_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                JOIN faculty f ON t.faculty_id = f.id
                WHERE {$where}
                ORDER BY 
                    CASE t.day 
                        WHEN 'monday' THEN 1
                        WHEN 'tuesday' THEN 2
                        WHEN 'wednesday' THEN 3
                        WHEN 'thursday' THEN 4
                        WHEN 'friday' THEN 5
                        WHEN 'saturday' THEN 6
                        WHEN 'sunday' THEN 7
                    END,
                    t.start_time";
        
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Get faculty timetable
     */
    private function getFacultyTimetable($facultyId)
    {
        $db = Database::getInstance();
        $sql = "SELECT t.*, s.subject_name, c.class_name, sec.section_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE t.faculty_id = :faculty_id
                ORDER BY 
                    CASE t.day 
                        WHEN 'monday' THEN 1
                        WHEN 'tuesday' THEN 2
                        WHEN 'wednesday' THEN 3
                        WHEN 'thursday' THEN 4
                        WHEN 'friday' THEN 5
                        WHEN 'saturday' THEN 6
                        WHEN 'sunday' THEN 7
                    END,
                    t.start_time";
        
        return $db->fetchAll($sql, ['faculty_id' => $facultyId]);
    }
    
    /**
     * Check timetable conflicts
     */
    private function checkTimetableConflicts($data, $excludeId = null)
    {
        $conflicts = [];
        $db = Database::getInstance();
        
        $where = 'day = :day AND academic_session = :session AND (
                    (start_time <= :start_time AND end_time > :start_time) OR
                    (start_time < :end_time AND end_time >= :end_time) OR
                    (start_time >= :start_time AND end_time <= :end_time)
                )';
        
        $params = [
            'day' => $data['day'],
            'session' => $data['academic_session'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time']
        ];
        
        if ($excludeId) {
            $where .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        
        // Check faculty conflict
        $facultyConflict = $db->exists('timetable', $where . ' AND faculty_id = :faculty_id', 
            array_merge($params, ['faculty_id' => $data['faculty_id']]));
        
        if ($facultyConflict) {
            $conflicts[] = 'Faculty already has a class at this time';
        }
        
        // Check class conflict
        $classWhere = $where . ' AND class_id = :class_id';
        if (!empty($data['section_id'])) {
            $classWhere .= ' AND section_id = :section_id';
            $params['section_id'] = $data['section_id'];
        }
        
        $classConflict = $db->exists('timetable', $classWhere, 
            array_merge($params, ['class_id' => $data['class_id']]));
        
        if ($classConflict) {
            $conflicts[] = 'Class already has a subject at this time';
        }
        
        // Check room conflict (if room specified)
        if (!empty($data['room_number'])) {
            $roomConflict = $db->exists('timetable', $where . ' AND room_number = :room', 
                array_merge($params, ['room' => $data['room_number']]));
            
            if ($roomConflict) {
                $conflicts[] = 'Room is already occupied at this time';
            }
        }
        
        return $conflicts;
    }
}