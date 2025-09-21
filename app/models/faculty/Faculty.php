<?php

/**
 * Faculty Model
 * 
 * Handles faculty data operations
 */
class Faculty extends Model
{
    protected $table = 'faculty';
    protected $fillable = [
        'user_id', 'employee_id', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'blood_group', 'photo', 'department_id',
        'designation_id', 'faculty_type', 'joining_date', 'confirmation_date',
        'qualification', 'specialization', 'experience', 'research_interests',
        'publications', 'awards', 'salary', 'bank_account', 'ifsc_code',
        'pan_number', 'aadhar_number', 'address', 'city', 'state', 'pincode',
        'emergency_contact', 'emergency_relation', 'emergency_phone',
        'marital_status', 'spouse_name', 'spouse_phone', 'medical_conditions',
        'status', 'remarks'
    ];
    
    /**
     * Find faculty by employee ID
     */
    public function findByEmployeeId($employeeId)
    {
        return $this->findBy('employee_id', $employeeId);
    }
    
    /**
     * Find faculty by user ID
     */
    public function findByUserId($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get faculty by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get faculty by type
     */
    public function getByType($facultyType)
    {
        return $this->where('faculty_type = :type AND status = :status', [
            'type' => $facultyType,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get faculty by designation
     */
    public function getByDesignation($designationId)
    {
        return $this->where('designation_id = :designation_id AND status = :status', [
            'designation_id' => $designationId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get faculty with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT f.*, 
                       d.department_name, d.department_code,
                       des.designation_name, des.level,
                       u.email as user_email, u.last_login
                FROM {$this->table} f
                LEFT JOIN departments d ON f.department_id = d.id
                LEFT JOIN designations des ON f.designation_id = des.id
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get faculty subjects
     */
    public function getSubjects($facultyId, $academicYear = null)
    {
        $where = 'fs.faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($academicYear) {
            $where .= ' AND fs.academic_year = :year';
            $params['year'] = $academicYear;
        }
        
        $sql = "SELECT s.*, fs.academic_year, fs.semester 
                FROM subjects s
                JOIN faculty_subjects fs ON s.id = fs.subject_id
                WHERE {$where}
                ORDER BY fs.academic_year DESC, s.semester, s.subject_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get faculty classes
     */
    public function getClasses($facultyId, $academicYear = null)
    {
        $where = 'ct.faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($academicYear) {
            $where .= ' AND ct.academic_year = :year';
            $params['year'] = $academicYear;
        }
        
        $sql = "SELECT c.*, ct.is_class_teacher, ct.academic_year 
                FROM classes c
                JOIN class_teachers ct ON c.id = ct.class_id
                WHERE {$where}
                ORDER BY ct.academic_year DESC, c.class_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get faculty timetable
     */
    public function getTimetable($facultyId, $day = null)
    {
        $where = 'faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        $sql = "SELECT t.*, s.subject_name, c.class_name, sec.section_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get faculty statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_faculty,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_faculty,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_faculty,
                    SUM(CASE WHEN faculty_type = 'permanent' THEN 1 ELSE 0 END) as permanent_faculty,
                    SUM(CASE WHEN faculty_type = 'contract' THEN 1 ELSE 0 END) as contract_faculty,
                    SUM(CASE WHEN faculty_type = 'visiting' THEN 1 ELSE 0 END) as visiting_faculty,
                    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_faculty,
                    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_faculty,
                    AVG(experience) as average_experience,
                    AVG(salary) as average_salary
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get birthday faculty
     */
    public function getBirthdayFaculty($date = null)
    {
        $date = $date ?? today();
        $monthDay = date('m-d', strtotime($date));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE DATE_FORMAT(date_of_birth, '%m-%d') = :month_day 
                AND status = :status 
                ORDER BY first_name ASC";
        
        return $this->db->fetchAll($sql, [
            'month_day' => $monthDay,
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Get faculty by qualification
     */
    public function getByQualification($qualification)
    {
        return $this->where('qualification LIKE :qualification AND status = :status', [
            'qualification' => "%{$qualification}%",
            'status' => STATUS_ACTIVE
        ], 'experience DESC');
    }
    
    /**
     * Get senior faculty
     */
    public function getSeniorFaculty($experienceYears = 10)
    {
        return $this->where('experience >= :experience AND status = :status', [
            'experience' => $experienceYears,
            'status' => STATUS_ACTIVE
        ], 'experience DESC');
    }
    
    /**
     * Get faculty workload
     */
    public function getWorkload($facultyId)
    {
        $db = Database::getInstance();
        
        // Count subjects taught
        $subjectCount = $db->count('faculty_subjects', 'faculty_id = :faculty_id', ['faculty_id' => $facultyId]);
        
        // Count classes handled
        $classCount = $db->count('class_teachers', 'faculty_id = :faculty_id', ['faculty_id' => $facultyId]);
        
        // Count weekly hours from timetable
        $sql = "SELECT SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) as weekly_hours
                FROM timetable 
                WHERE faculty_id = :faculty_id";
        
        $hoursResult = $db->fetch($sql, ['faculty_id' => $facultyId]);
        $weeklyHours = $hoursResult['weekly_hours'] ?? 0;
        
        return [
            'subjects_taught' => $subjectCount,
            'classes_handled' => $classCount,
            'weekly_hours' => $weeklyHours,
            'workload_score' => $this->calculateWorkloadScore($subjectCount, $classCount, $weeklyHours)
        ];
    }
    
    /**
     * Calculate workload score
     */
    private function calculateWorkloadScore($subjects, $classes, $hours)
    {
        // Simple workload calculation
        return ($subjects * 2) + ($classes * 3) + ($hours * 0.5);
    }
    
    /**
     * Get faculty performance metrics
     */
    public function getPerformanceMetrics($facultyId)
    {
        $db = Database::getInstance();
        
        // Student pass rate in faculty's subjects
        $sql = "SELECT 
                    COUNT(DISTINCT r.student_id) as total_students,
                    SUM(CASE WHEN r.grade IN ('A+', 'A', 'B+', 'B') THEN 1 ELSE 0 END) as good_performers,
                    AVG(r.marks_obtained) as average_marks
                FROM results r
                JOIN exam_subjects es ON r.exam_id = es.exam_id AND r.subject_id = es.subject_id
                JOIN faculty_subjects fs ON es.subject_id = fs.subject_id
                WHERE fs.faculty_id = :faculty_id
                AND r.created_at >= :start_date";
        
        $performance = $db->fetch($sql, [
            'faculty_id' => $facultyId,
            'start_date' => date('Y-01-01') // Current year
        ]);
        
        if ($performance['total_students'] > 0) {
            $performance['success_rate'] = round(($performance['good_performers'] / $performance['total_students']) * 100, 2);
        } else {
            $performance['success_rate'] = 0;
        }
        
        return $performance;
    }
}