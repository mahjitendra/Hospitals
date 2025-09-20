<?php

/**
 * Department Model
 * 
 * Handles department data operations
 */
class Department extends Model
{
    protected $table = 'departments';
    protected $fillable = [
        'department_code', 'department_name', 'description', 'hod_id',
        'established_year', 'location', 'phone', 'email', 'website',
        'vision', 'mission', 'objectives', 'facilities', 'status'
    ];
    
    /**
     * Find department by code
     */
    public function findByCode($departmentCode)
    {
        return $this->findBy('department_code', $departmentCode);
    }
    
    /**
     * Get active departments
     */
    public function getActiveDepartments()
    {
        return $this->where('status = :status', ['status' => 'active'], 'department_name ASC');
    }
    
    /**
     * Get department with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT d.*, 
                       f.first_name as hod_first_name, f.last_name as hod_last_name,
                       f.email as hod_email, f.phone as hod_phone
                FROM {$this->table} d
                LEFT JOIN faculty f ON d.hod_id = f.id
                WHERE d.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get department faculty
     */
    public function getFaculty($departmentId)
    {
        $facultyModel = new Faculty();
        return $facultyModel->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get department courses
     */
    public function getCourses($departmentId)
    {
        $courseModel = new Course();
        return $courseModel->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => 'active'
        ], 'course_name ASC');
    }
    
    /**
     * Get department subjects
     */
    public function getSubjects($departmentId)
    {
        $subjectModel = new Subject();
        return $subjectModel->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => 'active'
        ], 'subject_name ASC');
    }
    
    /**
     * Get department students
     */
    public function getStudents($departmentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.* 
                FROM students s
                JOIN courses c ON s.course_id = c.id
                WHERE c.department_id = :department_id AND s.status = 'active'
                ORDER BY s.first_name";
        
        return $db->fetchAll($sql, ['department_id' => $departmentId]);
    }
    
    /**
     * Get department statistics
     */
    public function getDepartmentStats($departmentId)
    {
        $db = Database::getInstance();
        
        // Faculty count
        $facultyCount = $db->count('faculty', 'department_id = :dept_id AND status = :status', [
            'dept_id' => $departmentId,
            'status' => STATUS_ACTIVE
        ]);
        
        // Course count
        $courseCount = $db->count('courses', 'department_id = :dept_id AND status = :status', [
            'dept_id' => $departmentId,
            'status' => 'active'
        ]);
        
        // Subject count
        $subjectCount = $db->count('subjects', 'department_id = :dept_id AND status = :status', [
            'dept_id' => $departmentId,
            'status' => 'active'
        ]);
        
        // Student count
        $sql = "SELECT COUNT(s.id) as student_count
                FROM students s
                JOIN courses c ON s.course_id = c.id
                WHERE c.department_id = :dept_id AND s.status = 'active'";
        
        $studentResult = $db->fetch($sql, ['dept_id' => $departmentId]);
        $studentCount = $studentResult['student_count'] ?? 0;
        
        return [
            'faculty_count' => $facultyCount,
            'course_count' => $courseCount,
            'subject_count' => $subjectCount,
            'student_count' => $studentCount
        ];
    }
    
    /**
     * Set HOD
     */
    public function setHOD($departmentId, $facultyId)
    {
        // Verify faculty belongs to this department
        $facultyModel = new Faculty();
        $faculty = $facultyModel->find($facultyId);
        
        if (!$faculty || $faculty['department_id'] != $departmentId) {
            throw new Exception('Faculty does not belong to this department');
        }
        
        return $this->update($departmentId, ['hod_id' => $facultyId]);
    }
    
    /**
     * Remove HOD
     */
    public function removeHOD($departmentId)
    {
        return $this->update($departmentId, ['hod_id' => null]);
    }
    
    /**
     * Get HOD
     */
    public function getHOD($departmentId)
    {
        $department = $this->find($departmentId);
        if ($department && $department['hod_id']) {
            $facultyModel = new Faculty();
            return $facultyModel->find($department['hod_id']);
        }
        return null;
    }
    
    /**
     * Search departments
     */
    public function search($query, $limit = 10)
    {
        $sql = "SELECT id, department_code, department_name 
                FROM {$this->table} 
                WHERE (department_name LIKE :query OR department_code LIKE :query) 
                AND status = 'active' 
                ORDER BY department_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }
    
    /**
     * Get department performance
     */
    public function getPerformance($departmentId, $academicYear = null)
    {
        $academicYear = $academicYear ?? config('app.current_session');
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    AVG(r.marks_obtained) as average_marks,
                    COUNT(DISTINCT r.student_id) as total_students,
                    SUM(CASE WHEN r.grade IN ('A+', 'A') THEN 1 ELSE 0 END) as excellent_performers,
                    SUM(CASE WHEN r.grade = 'F' THEN 1 ELSE 0 END) as failed_students
                FROM results r
                JOIN students s ON r.student_id = s.id
                JOIN courses c ON s.course_id = c.id
                WHERE c.department_id = :dept_id AND s.academic_session = :session";
        
        $result = $db->fetch($sql, ['dept_id' => $departmentId, 'session' => $academicYear]);
        
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