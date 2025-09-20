<?php

/**
 * Student Model
 * 
 * Handles student data operations
 */
class Student extends Model
{
    protected $table = 'students';
    protected $fillable = [
        'user_id', 'student_id', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'blood_group', 'photo', 'course_id', 'class_id',
        'section_id', 'admission_date', 'admission_number', 'roll_number',
        'address', 'city', 'state', 'pincode', 'country',
        'father_name', 'father_phone', 'father_occupation', 'father_email',
        'mother_name', 'mother_phone', 'mother_occupation', 'mother_email',
        'guardian_name', 'guardian_phone', 'guardian_relation', 'guardian_email',
        'previous_school', 'previous_marks', 'transfer_certificate',
        'caste', 'category', 'religion', 'nationality',
        'aadhar_number', 'passport_number', 'bank_account', 'ifsc_code',
        'transport_required', 'hostel_required', 'library_card_number',
        'medical_conditions', 'allergies', 'emergency_contact',
        'status', 'remarks'
    ];
    
    /**
     * Find student by student ID
     */
    public function findByStudentId($studentId)
    {
        return $this->findBy('student_id', $studentId);
    }
    
    /**
     * Find student by user ID
     */
    public function findByUserId($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get students by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id AND status = :status', [
            'course_id' => $courseId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get students by class
     */
    public function getByClass($classId)
    {
        return $this->where('class_id = :class_id AND status = :status', [
            'class_id' => $classId,
            'status' => STATUS_ACTIVE
        ], 'roll_number ASC');
    }
    
    /**
     * Get students by section
     */
    public function getBySection($sectionId)
    {
        return $this->where('section_id = :section_id AND status = :status', [
            'section_id' => $sectionId,
            'status' => STATUS_ACTIVE
        ], 'roll_number ASC');
    }
    
    /**
     * Search students
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT id, student_id, first_name, last_name, email, phone, photo 
                FROM {$this->table} 
                WHERE (first_name LIKE :query OR last_name LIKE :query 
                       OR student_id LIKE :query OR email LIKE :query) 
                AND status = :status 
                ORDER BY first_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [
            'query' => "%{$query}%",
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Get student with course and class details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT s.*, 
                       c.course_name, c.course_code,
                       cl.class_name,
                       sec.section_name,
                       d.department_name
                FROM {$this->table} s
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN departments d ON c.department_id = d.id
                WHERE s.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get students for attendance
     */
    public function getForAttendance($classId, $sectionId = null)
    {
        $where = 'class_id = :class_id AND status = :status';
        $params = ['class_id' => $classId, 'status' => STATUS_ACTIVE];
        
        if ($sectionId) {
            $where .= ' AND section_id = :section_id';
            $params['section_id'] = $sectionId;
        }
        
        return $this->where($where, $params, 'roll_number ASC');
    }
    
    /**
     * Get birthday students
     */
    public function getBirthdayStudents($date = null)
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
     * Get students by admission year
     */
    public function getByAdmissionYear($year)
    {
        return $this->where('YEAR(admission_date) = :year', ['year' => $year], 'admission_date ASC');
    }
    
    /**
     * Get graduating students
     */
    public function getGraduatingStudents($courseId = null)
    {
        $where = 'status = :status';
        $params = ['status' => STATUS_ACTIVE];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        // Add logic to determine graduating students based on course duration
        $sql = "SELECT s.*, c.duration, c.duration_type 
                FROM {$this->table} s 
                JOIN courses c ON s.course_id = c.id 
                WHERE {$where}";
        
        $students = $this->db->fetchAll($sql, $params);
        $graduating = [];
        
        foreach ($students as $student) {
            $admissionDate = new DateTime($student['admission_date']);
            $duration = $student['duration'];
            $durationType = $student['duration_type'];
            
            $graduationDate = clone $admissionDate;
            $graduationDate->modify("+{$duration} {$durationType}");
            
            $now = new DateTime();
            $diff = $now->diff($graduationDate);
            
            // Students graduating within 6 months
            if ($diff->days <= 180 && $graduationDate > $now) {
                $graduating[] = $student;
            }
        }
        
        return $graduating;
    }
    
    /**
     * Get student statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN status = 'graduated' THEN 1 ELSE 0 END) as graduated,
                    SUM(CASE WHEN status = 'transferred' THEN 1 ELSE 0 END) as transferred,
                    SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) as dropped,
                    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male,
                    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get students by course statistics
     */
    public function getStatsByCourse()
    {
        $sql = "SELECT c.course_name, c.course_code,
                       COUNT(s.id) as total_students,
                       SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END) as active_students
                FROM courses c
                LEFT JOIN {$this->table} s ON c.id = s.course_id
                GROUP BY c.id, c.course_name, c.course_code
                ORDER BY c.course_name";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get admission statistics by month
     */
    public function getAdmissionStats($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(admission_date) as month,
                    COUNT(*) as admissions
                FROM {$this->table} 
                WHERE YEAR(admission_date) = :year
                GROUP BY MONTH(admission_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Update student status
     */
    public function updateStatus($id, $status, $remarks = null)
    {
        $data = ['status' => $status];
        if ($remarks) {
            $data['remarks'] = $remarks;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Promote students to next class
     */
    public function promoteStudents($fromClassId, $toClassId, $studentIds = [])
    {
        $where = 'class_id = :from_class AND status = :status';
        $params = ['from_class' => $fromClassId, 'status' => STATUS_ACTIVE];
        
        if (!empty($studentIds)) {
            $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
            $where .= " AND id IN ({$placeholders})";
            $params = array_merge($params, $studentIds);
        }
        
        $sql = "UPDATE {$this->table} SET class_id = :to_class WHERE {$where}";
        $params['to_class'] = $toClassId;
        
        return $this->db->query($sql, $params);
    }
}