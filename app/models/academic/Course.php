<?php

/**
 * Course Model
 * 
 * Handles course data operations
 */
class Course extends Model
{
    protected $table = 'courses';
    protected $fillable = [
        'course_code', 'course_name', 'department_id', 'course_type',
        'duration', 'duration_type', 'total_semesters', 'total_subjects',
        'eligibility', 'description', 'fees', 'syllabus', 'objectives',
        'career_prospects', 'admission_criteria', 'course_coordinator',
        'status'
    ];
    
    /**
     * Find course by code
     */
    public function findByCode($courseCode)
    {
        return $this->findBy('course_code', $courseCode);
    }
    
    /**
     * Get courses by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => 'active'
        ], 'course_name ASC');
    }
    
    /**
     * Get courses by type
     */
    public function getByType($type)
    {
        return $this->where('course_type = :type AND status = :status', [
            'type' => $type,
            'status' => 'active'
        ], 'course_name ASC');
    }
    
    /**
     * Get active courses
     */
    public function getActiveCourses()
    {
        return $this->where('status = :status', ['status' => 'active'], 'course_name ASC');
    }
    
    /**
     * Get course with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT c.*, d.department_name, d.department_code
                FROM {$this->table} c
                LEFT JOIN departments d ON c.department_id = d.id
                WHERE c.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get course subjects
     */
    public function getSubjects($courseId)
    {
        $subjectModel = new Subject();
        return $subjectModel->where('course_id = :course_id', ['course_id' => $courseId], 'semester ASC, subject_name ASC');
    }
    
    /**
     * Get course students count
     */
    public function getStudentCount($courseId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM students WHERE course_id = :course_id AND status = 'active'";
        $result = $db->fetch($sql, ['course_id' => $courseId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get course faculty
     */
    public function getFaculty($courseId)
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT f.* 
                FROM faculty f
                JOIN faculty_subjects fs ON f.id = fs.faculty_id
                JOIN subjects s ON fs.subject_id = s.id
                WHERE s.course_id = :course_id AND f.status = 'active'
                ORDER BY f.first_name";
        
        return $db->fetchAll($sql, ['course_id' => $courseId]);
    }
    
    /**
     * Get course statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_courses,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_courses,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_courses,
                    SUM(CASE WHEN course_type = 'undergraduate' THEN 1 ELSE 0 END) as undergraduate,
                    SUM(CASE WHEN course_type = 'postgraduate' THEN 1 ELSE 0 END) as postgraduate,
                    SUM(CASE WHEN course_type = 'diploma' THEN 1 ELSE 0 END) as diploma,
                    SUM(CASE WHEN course_type = 'certificate' THEN 1 ELSE 0 END) as certificate
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get courses with student count
     */
    public function getCoursesWithStudentCount()
    {
        $sql = "SELECT c.*, COUNT(s.id) as student_count
                FROM {$this->table} c
                LEFT JOIN students s ON c.id = s.course_id AND s.status = 'active'
                GROUP BY c.id
                ORDER BY c.course_name";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Search courses
     */
    public function search($query, $limit = 10)
    {
        $sql = "SELECT id, course_code, course_name, course_type 
                FROM {$this->table} 
                WHERE (course_name LIKE :query OR course_code LIKE :query) 
                AND status = 'active' 
                ORDER BY course_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }
    
    /**
     * Get course fee structure
     */
    public function getFeeStructure($courseId, $academicSession = null)
    {
        $feeStructureModel = new FeeStructure();
        $where = 'course_id = :course_id';
        $params = ['course_id' => $courseId];
        
        if ($academicSession) {
            $where .= ' AND academic_session = :session';
            $params['session'] = $academicSession;
        }
        
        return $feeStructureModel->where($where, $params, 'semester ASC, fee_type ASC');
    }
    
    /**
     * Get course admission statistics
     */
    public function getAdmissionStats($courseId, $year = null)
    {
        $year = $year ?? date('Y');
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM admissions 
                WHERE course_id = :course_id AND YEAR(application_date) = :year";
        
        return $db->fetch($sql, ['course_id' => $courseId, 'year' => $year]);
    }
}