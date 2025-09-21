<?php

/**
 * Teacher Model
 * 
 * Handles teacher-specific data operations (extends Faculty)
 */
class Teacher extends Faculty
{
    protected $table = 'faculty';
    
    /**
     * Get teachers only
     */
    public function getTeachers()
    {
        return $this->where('faculty_type = :type AND status = :status', [
            'type' => 'teaching',
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get teacher subjects
     */
    public function getTeacherSubjects($teacherId, $academicYear = null)
    {
        return $this->getSubjects($teacherId, $academicYear);
    }
    
    /**
     * Get teacher classes
     */
    public function getTeacherClasses($teacherId, $academicYear = null)
    {
        return $this->getClasses($teacherId, $academicYear);
    }
    
    /**
     * Get teacher students
     */
    public function getStudents($teacherId)
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT s.* 
                FROM students s
                JOIN classes c ON s.class_id = c.id
                JOIN class_teachers ct ON c.id = ct.class_id
                WHERE ct.faculty_id = :teacher_id AND s.status = 'active'
                ORDER BY s.first_name";
        
        return $db->fetchAll($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Get teacher performance
     */
    public function getTeacherPerformance($teacherId)
    {
        return $this->getPerformanceMetrics($teacherId);
    }
    
    /**
     * Get class teacher assignments
     */
    public function getClassTeacherAssignments($teacherId)
    {
        $db = Database::getInstance();
        $sql = "SELECT c.*, ct.academic_year, ct.assigned_date
                FROM classes c
                JOIN class_teachers ct ON c.id = ct.class_id
                WHERE ct.faculty_id = :teacher_id AND ct.is_class_teacher = 1
                ORDER BY ct.academic_year DESC";
        
        return $db->fetchAll($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Get subject assignments
     */
    public function getSubjectAssignments($teacherId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.*, fs.academic_year, fs.semester, fs.assigned_date
                FROM subjects s
                JOIN faculty_subjects fs ON s.id = fs.subject_id
                WHERE fs.faculty_id = :teacher_id
                ORDER BY fs.academic_year DESC, s.semester, s.subject_name";
        
        return $db->fetchAll($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Get teaching load
     */
    public function getTeachingLoad($teacherId)
    {
        $workload = $this->getWorkload($teacherId);
        
        // Add teaching-specific metrics
        $db = Database::getInstance();
        
        // Count total students taught
        $sql = "SELECT COUNT(DISTINCT s.id) as total_students
                FROM students s
                JOIN classes c ON s.class_id = c.id
                JOIN class_teachers ct ON c.id = ct.class_id
                WHERE ct.faculty_id = :teacher_id AND s.status = 'active'";
        
        $studentResult = $db->fetch($sql, ['teacher_id' => $teacherId]);
        $workload['students_taught'] = $studentResult['total_students'] ?? 0;
        
        return $workload;
    }
    
    /**
     * Get teacher availability
     */
    public function getAvailability($teacherId, $day = null, $time = null)
    {
        $where = 'faculty_id = :faculty_id';
        $params = ['faculty_id' => $teacherId];
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        if ($time) {
            $where .= ' AND :time BETWEEN start_time AND end_time';
            $params['time'] = $time;
        }
        
        $sql = "SELECT * FROM timetable WHERE {$where}";
        $schedule = $this->db->fetchAll($sql, $params);
        
        return empty($schedule); // True if available (no conflicts)
    }
    
    /**
     * Get teacher research output
     */
    public function getResearchOutput($teacherId)
    {
        $teacher = $this->find($teacherId);
        
        return [
            'publications' => $teacher['publications'] ? json_decode($teacher['publications'], true) : [],
            'research_interests' => $teacher['research_interests'] ? explode(',', $teacher['research_interests']) : [],
            'awards' => $teacher['awards'] ? json_decode($teacher['awards'], true) : []
        ];
    }
    
    /**
     * Add publication
     */
    public function addPublication($teacherId, $publication)
    {
        $teacher = $this->find($teacherId);
        $publications = $teacher['publications'] ? json_decode($teacher['publications'], true) : [];
        
        $publications[] = [
            'title' => $publication['title'],
            'journal' => $publication['journal'],
            'year' => $publication['year'],
            'authors' => $publication['authors'],
            'doi' => $publication['doi'] ?? null,
            'type' => $publication['type'] ?? 'journal'
        ];
        
        return $this->update($teacherId, ['publications' => json_encode($publications)]);
    }
    
    /**
     * Add award
     */
    public function addAward($teacherId, $award)
    {
        $teacher = $this->find($teacherId);
        $awards = $teacher['awards'] ? json_decode($teacher['awards'], true) : [];
        
        $awards[] = [
            'title' => $award['title'],
            'organization' => $award['organization'],
            'date' => $award['date'],
            'description' => $award['description'] ?? null
        ];
        
        return $this->update($teacherId, ['awards' => json_encode($awards)]);
    }
}