<?php

/**
 * Timetable Model
 * 
 * Handles timetable data operations
 */
class Timetable extends Model
{
    protected $table = 'timetable';
    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'faculty_id', 'day',
        'start_time', 'end_time', 'room_number', 'academic_session',
        'semester', 'period_number', 'is_lab', 'lab_batch', 'status'
    ];
    
    /**
     * Get timetable by class
     */
    public function getByClass($classId, $sectionId = null, $day = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($sectionId) {
            $where .= ' AND (section_id = :section_id OR section_id IS NULL)';
            $params['section_id'] = $sectionId;
        }
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        $sql = "SELECT t.*, s.subject_name, s.subject_code, f.first_name, f.last_name 
                FROM {$this->table} t
                JOIN subjects s ON t.subject_id = s.id
                JOIN faculty f ON t.faculty_id = f.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get timetable by faculty
     */
    public function getByFaculty($facultyId, $day = null)
    {
        $where = 'faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        $sql = "SELECT t.*, s.subject_name, c.class_name, sec.section_name 
                FROM {$this->table} t
                JOIN subjects s ON t.subject_id = s.id
                JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get timetable by room
     */
    public function getByRoom($roomNumber, $day = null)
    {
        $where = 'room_number = :room';
        $params = ['room' => $roomNumber];
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        $sql = "SELECT t.*, s.subject_name, c.class_name, f.first_name, f.last_name 
                FROM {$this->table} t
                JOIN subjects s ON t.subject_id = s.id
                JOIN classes c ON t.class_id = c.id
                JOIN faculty f ON t.faculty_id = f.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Check for conflicts
     */
    public function hasConflict($data, $excludeId = null)
    {
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
        $facultyConflict = $this->exists($where . ' AND faculty_id = :faculty_id', 
            array_merge($params, ['faculty_id' => $data['faculty_id']]));
        
        // Check class conflict
        $classWhere = $where . ' AND class_id = :class_id';
        if (!empty($data['section_id'])) {
            $classWhere .= ' AND section_id = :section_id';
            $params['section_id'] = $data['section_id'];
        }
        
        $classConflict = $this->exists($classWhere, 
            array_merge($params, ['class_id' => $data['class_id']]));
        
        // Check room conflict
        $roomConflict = false;
        if (!empty($data['room_number'])) {
            $roomConflict = $this->exists($where . ' AND room_number = :room', 
                array_merge($params, ['room' => $data['room_number']]));
        }
        
        return $facultyConflict || $classConflict || $roomConflict;
    }
    
    /**
     * Get weekly timetable
     */
    public function getWeeklyTimetable($classId, $sectionId = null)
    {
        $timetable = $this->getByClass($classId, $sectionId);
        
        $weekly = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        foreach ($days as $day) {
            $weekly[$day] = array_filter($timetable, function($slot) use ($day) {
                return $slot['day'] === $day;
            });
            
            // Sort by start time
            usort($weekly[$day], function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
        }
        
        return $weekly;
    }
    
    /**
     * Get free periods
     */
    public function getFreePeriods($classId, $sectionId, $day)
    {
        $occupiedSlots = $this->getByClass($classId, $sectionId, $day);
        
        // Define standard periods
        $standardPeriods = [
            ['start' => '09:00', 'end' => '09:45'],
            ['start' => '09:45', 'end' => '10:30'],
            ['start' => '10:45', 'end' => '11:30'],
            ['start' => '11:30', 'end' => '12:15'],
            ['start' => '13:00', 'end' => '13:45'],
            ['start' => '13:45', 'end' => '14:30'],
            ['start' => '14:30', 'end' => '15:15'],
            ['start' => '15:15', 'end' => '16:00']
        ];
        
        $freeSlots = [];
        foreach ($standardPeriods as $period) {
            $isOccupied = false;
            
            foreach ($occupiedSlots as $slot) {
                if ($slot['start_time'] === $period['start']) {
                    $isOccupied = true;
                    break;
                }
            }
            
            if (!$isOccupied) {
                $freeSlots[] = $period;
            }
        }
        
        return $freeSlots;
    }
    
    /**
     * Get timetable statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_slots,
                    COUNT(DISTINCT class_id) as classes_scheduled,
                    COUNT(DISTINCT faculty_id) as faculty_assigned,
                    COUNT(DISTINCT subject_id) as subjects_scheduled,
                    COUNT(DISTINCT room_number) as rooms_used,
                    SUM(CASE WHEN is_lab = 1 THEN 1 ELSE 0 END) as lab_sessions,
                    AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration_minutes
                FROM {$this->table}
                WHERE status = 'active'";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get room utilization
     */
    public function getRoomUtilization()
    {
        $sql = "SELECT 
                    room_number,
                    COUNT(*) as total_slots,
                    COUNT(DISTINCT day) as days_used,
                    COUNT(DISTINCT faculty_id) as faculty_count,
                    SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) as total_hours
                FROM {$this->table}
                WHERE room_number IS NOT NULL AND status = 'active'
                GROUP BY room_number
                ORDER BY total_slots DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get faculty workload from timetable
     */
    public function getFacultyWorkload()
    {
        $sql = "SELECT f.first_name, f.last_name, f.employee_id,
                       COUNT(t.id) as total_periods,
                       SUM(TIMESTAMPDIFF(HOUR, t.start_time, t.end_time)) as weekly_hours,
                       COUNT(DISTINCT t.subject_id) as subjects_taught,
                       COUNT(DISTINCT t.class_id) as classes_handled
                FROM faculty f
                LEFT JOIN {$this->table} t ON f.id = t.faculty_id AND t.status = 'active'
                WHERE f.status = 'active'
                GROUP BY f.id
                ORDER BY weekly_hours DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Clone timetable to new session
     */
    public function cloneToNewSession($fromSession, $toSession)
    {
        $this->beginTransaction();
        
        try {
            $timetableSlots = $this->where('academic_session = :session', ['session' => $fromSession]);
            $clonedCount = 0;
            
            foreach ($timetableSlots as $slot) {
                unset($slot['id']);
                unset($slot['created_at']);
                unset($slot['updated_at']);
                
                $slot['academic_session'] = $toSession;
                
                $this->create($slot);
                $clonedCount++;
            }
            
            $this->commit();
            return $clonedCount;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}