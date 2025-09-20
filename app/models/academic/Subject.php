<?php

/**
 * Subject Model
 * 
 * Handles subject data operations
 */
class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = [
        'subject_code', 'subject_name', 'course_id', 'department_id',
        'semester', 'subject_type', 'credits', 'theory_hours', 'practical_hours',
        'total_hours', 'description', 'prerequisites', 'learning_outcomes',
        'assessment_pattern', 'reference_books', 'syllabus_file', 'status'
    ];
    
    /**
     * Find subject by code
     */
    public function findByCode($subjectCode)
    {
        return $this->findBy('subject_code', $subjectCode);
    }
    
    /**
     * Get subjects by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id AND status = :status', [
            'course_id' => $courseId,
            'status' => 'active'
        ], 'semester ASC, subject_name ASC');
    }
    
    /**
     * Get subjects by semester
     */
    public function getBySemester($courseId, $semester)
    {
        return $this->where('course_id = :course_id AND semester = :semester AND status = :status', [
            'course_id' => $courseId,
            'semester' => $semester,
            'status' => 'active'
        ], 'subject_name ASC');
    }
    
    /**
     * Get subjects by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => 'active'
        ], 'subject_name ASC');
    }
    
    /**
     * Get subjects by type
     */
    public function getByType($type)
    {
        return $this->where('subject_type = :type AND status = :status', [
            'type' => $type,
            'status' => 'active'
        ], 'subject_name ASC');
    }
    
    /**
     * Get subject with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT s.*, 
                       c.course_name, c.course_code,
                       d.department_name, d.department_code
                FROM {$this->table} s
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN departments d ON s.department_id = d.id
                WHERE s.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get subject faculty
     */
    public function getFaculty($subjectId, $academicYear = null)
    {
        $where = 'fs.subject_id = :subject_id';
        $params = ['subject_id' => $subjectId];
        
        if ($academicYear) {
            $where .= ' AND fs.academic_year = :year';
            $params['year'] = $academicYear;
        }
        
        $sql = "SELECT f.*, fs.academic_year, fs.semester 
                FROM faculty f
                JOIN faculty_subjects fs ON f.id = fs.faculty_id
                WHERE {$where}
                ORDER BY fs.academic_year DESC, f.first_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get subject students
     */
    public function getStudents($subjectId)
    {
        $sql = "SELECT s.* 
                FROM students s
                JOIN subjects sub ON s.course_id = sub.course_id
                WHERE sub.id = :subject_id AND s.status = 'active'
                ORDER BY s.first_name";
        
        return $this->db->fetchAll($sql, ['subject_id' => $subjectId]);
    }
    
    /**
     * Get subject exams
     */
    public function getExams($subjectId)
    {
        $db = Database::getInstance();
        $sql = "SELECT e.* 
                FROM exams e
                JOIN exam_subjects es ON e.id = es.exam_id
                WHERE es.subject_id = :subject_id
                ORDER BY e.exam_date DESC";
        
        return $db->fetchAll($sql, ['subject_id' => $subjectId]);
    }
    
    /**
     * Get subject results statistics
     */
    public function getResultStats($subjectId, $examId = null)
    {
        $where = 'subject_id = :subject_id';
        $params = ['subject_id' => $subjectId];
        
        if ($examId) {
            $where .= ' AND exam_id = :exam_id';
            $params['exam_id'] = $examId;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_students,
                    AVG(marks_obtained) as average_marks,
                    MAX(marks_obtained) as highest_marks,
                    MIN(marks_obtained) as lowest_marks,
                    SUM(CASE WHEN grade IN ('A+', 'A') THEN 1 ELSE 0 END) as excellent,
                    SUM(CASE WHEN grade IN ('B+', 'B') THEN 1 ELSE 0 END) as good,
                    SUM(CASE WHEN grade IN ('C+', 'C') THEN 1 ELSE 0 END) as average,
                    SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) as failed
                FROM results 
                WHERE {$where}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Search subjects
     */
    public function search($query, $courseId = null, $limit = 10)
    {
        $where = '(subject_name LIKE :query OR subject_code LIKE :query) AND status = :status';
        $params = ['query' => "%{$query}%", 'status' => 'active'];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        $sql = "SELECT id, subject_code, subject_name, semester, credits 
                FROM {$this->table} 
                WHERE {$where}
                ORDER BY subject_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get subject statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_subjects,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_subjects,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_subjects,
                    SUM(CASE WHEN subject_type = 'core' THEN 1 ELSE 0 END) as core_subjects,
                    SUM(CASE WHEN subject_type = 'elective' THEN 1 ELSE 0 END) as elective_subjects,
                    SUM(CASE WHEN subject_type = 'practical' THEN 1 ELSE 0 END) as practical_subjects,
                    AVG(credits) as average_credits,
                    SUM(credits) as total_credits
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get subjects by faculty
     */
    public function getByFaculty($facultyId, $academicYear = null)
    {
        $where = 'fs.faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($academicYear) {
            $where .= ' AND fs.academic_year = :year';
            $params['year'] = $academicYear;
        }
        
        $sql = "SELECT s.*, fs.academic_year, fs.semester 
                FROM {$this->table} s
                JOIN faculty_subjects fs ON s.id = fs.subject_id
                WHERE {$where}
                ORDER BY fs.academic_year DESC, s.semester, s.subject_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Check if subject has prerequisites
     */
    public function hasPrerequisites($subjectId)
    {
        $subject = $this->find($subjectId);
        return !empty($subject['prerequisites']);
    }
    
    /**
     * Get prerequisite subjects
     */
    public function getPrerequisites($subjectId)
    {
        $subject = $this->find($subjectId);
        if (empty($subject['prerequisites'])) {
            return [];
        }
        
        $prerequisiteIds = explode(',', $subject['prerequisites']);
        $prerequisites = [];
        
        foreach ($prerequisiteIds as $id) {
            $prereq = $this->find(trim($id));
            if ($prereq) {
                $prerequisites[] = $prereq;
            }
        }
        
        return $prerequisites;
    }
}