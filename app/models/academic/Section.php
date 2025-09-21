<?php

/**
 * Section Model
 * 
 * Handles class section data operations
 */
class Section extends Model
{
    protected $table = 'sections';
    protected $fillable = [
        'section_name', 'class_id', 'section_teacher_id', 'room_number',
        'capacity', 'description', 'status'
    ];
    
    /**
     * Get sections by class
     */
    public function getByClass($classId)
    {
        return $this->where('class_id = :class_id AND status = :status', [
            'class_id' => $classId,
            'status' => 'active'
        ], 'section_name ASC');
    }
    
    /**
     * Get section with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT s.*, 
                       c.class_name, c.course_id,
                       f.first_name as teacher_first_name, f.last_name as teacher_last_name
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN faculty f ON s.section_teacher_id = f.id
                WHERE s.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get section students
     */
    public function getStudents($sectionId)
    {
        $studentModel = new Student();
        return $studentModel->where('section_id = :section_id AND status = :status', [
            'section_id' => $sectionId,
            'status' => STATUS_ACTIVE
        ], 'roll_number ASC');
    }
    
    /**
     * Get section timetable
     */
    public function getTimetable($sectionId, $day = null)
    {
        $where = 'section_id = :section_id';
        $params = ['section_id' => $sectionId];
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        $sql = "SELECT t.*, s.subject_name, f.first_name, f.last_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                JOIN faculty f ON t.faculty_id = f.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get section attendance statistics
     */
    public function getAttendanceStats($sectionId, $date = null)
    {
        $where = 's.section_id = :section_id';
        $params = ['section_id' => $sectionId];
        
        if ($date) {
            $where .= ' AND sa.attendance_date = :date';
            $params['date'] = $date;
        } else {
            $where .= ' AND sa.attendance_date = :today';
            $params['today'] = today();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_students,
                    SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN sa.status = 'late' THEN 1 ELSE 0 END) as late
                FROM students s
                LEFT JOIN student_attendance sa ON s.id = sa.student_id
                WHERE {$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['total_students'] > 0) {
            $result['attendance_percentage'] = round(($result['present'] / $result['total_students']) * 100, 2);
        } else {
            $result['attendance_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get section statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_sections,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_sections,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_sections,
                    AVG(capacity) as average_capacity,
                    SUM(capacity) as total_capacity
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get sections with student count
     */
    public function getSectionsWithStudentCount()
    {
        $sql = "SELECT s.*, c.class_name, COUNT(st.id) as student_count,
                       CASE 
                           WHEN s.capacity > 0 THEN ROUND((COUNT(st.id) / s.capacity) * 100, 2)
                           ELSE 0 
                       END as occupancy_percentage
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN students st ON s.id = st.section_id AND st.status = 'active'
                GROUP BY s.id
                ORDER BY c.class_name, s.section_name";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Search sections
     */
    public function search($query, $classId = null, $limit = 10)
    {
        $where = 'section_name LIKE :query AND status = :status';
        $params = ['query' => "%{$query}%", 'status' => 'active'];
        
        if ($classId) {
            $where .= ' AND class_id = :class_id';
            $params['class_id'] = $classId;
        }
        
        $sql = "SELECT id, section_name, capacity 
                FROM {$this->table} 
                WHERE {$where}
                ORDER BY section_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get available capacity
     */
    public function getAvailableCapacity($sectionId)
    {
        $section = $this->find($sectionId);
        if (!$section) {
            return 0;
        }
        
        $studentCount = $this->getStudentCount($sectionId);
        return max(0, $section['capacity'] - $studentCount);
    }
    
    /**
     * Get student count
     */
    private function getStudentCount($sectionId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM students WHERE section_id = :section_id AND status = 'active'";
        $result = $db->fetch($sql, ['section_id' => $sectionId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Check if section is full
     */
    public function isFull($sectionId)
    {
        return $this->getAvailableCapacity($sectionId) <= 0;
    }
    
    /**
     * Get section performance
     */
    public function getPerformance($sectionId, $examId = null)
    {
        $where = 's.section_id = :section_id';
        $params = ['section_id' => $sectionId];
        
        if ($examId) {
            $where .= ' AND r.exam_id = :exam_id';
            $params['exam_id'] = $examId;
        }
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_students,
                    AVG(r.marks_obtained) as average_marks,
                    MAX(r.marks_obtained) as highest_marks,
                    MIN(r.marks_obtained) as lowest_marks,
                    SUM(CASE WHEN r.grade IN ('A+', 'A') THEN 1 ELSE 0 END) as excellent_performers,
                    SUM(CASE WHEN r.grade = 'F' THEN 1 ELSE 0 END) as failed_students
                FROM students s
                LEFT JOIN results r ON s.id = r.student_id
                WHERE {$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['total_students'] > 0) {
            $result['excellence_rate'] = round(($result['excellent_performers'] / $result['total_students']) * 100, 2);
            $result['failure_rate'] = round(($result['failed_students'] / $result['total_students']) * 100, 2);
        } else {
            $result['excellence_rate'] = 0;
            $result['failure_rate'] = 0;
        }
        
        return $result;
    }
}