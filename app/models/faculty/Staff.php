<?php

/**
 * Staff Model
 * 
 * Handles non-teaching staff data operations
 */
class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = [
        'user_id', 'employee_id', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'blood_group', 'photo', 'department_id',
        'designation_id', 'staff_type', 'joining_date', 'confirmation_date',
        'qualification', 'experience', 'skills', 'salary', 'bank_account',
        'ifsc_code', 'pan_number', 'aadhar_number', 'address', 'city', 'state',
        'pincode', 'emergency_contact', 'emergency_relation', 'emergency_phone',
        'marital_status', 'spouse_name', 'spouse_phone', 'medical_conditions',
        'status', 'remarks'
    ];
    
    /**
     * Find staff by employee ID
     */
    public function findByEmployeeId($employeeId)
    {
        return $this->findBy('employee_id', $employeeId);
    }
    
    /**
     * Find staff by user ID
     */
    public function findByUserId($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get staff by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get staff by type
     */
    public function getByType($staffType)
    {
        return $this->where('staff_type = :type AND status = :status', [
            'type' => $staffType,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get staff with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT s.*, 
                       d.department_name,
                       des.designation_name,
                       u.email as user_email, u.last_login
                FROM {$this->table} s
                LEFT JOIN departments d ON s.department_id = d.id
                LEFT JOIN designations des ON s.designation_id = des.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get staff attendance
     */
    public function getAttendance($staffId, $startDate = null, $endDate = null)
    {
        $attendanceModel = new StaffAttendance();
        $where = 'staff_id = :staff_id';
        $params = ['staff_id' => $staffId];
        
        if ($startDate && $endDate) {
            $where .= ' AND attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $attendanceModel->where($where, $params, 'attendance_date DESC');
    }
    
    /**
     * Get staff leaves
     */
    public function getLeaves($staffId, $year = null)
    {
        $leaveModel = new Leave();
        $where = 'staff_id = :staff_id';
        $params = ['staff_id' => $staffId];
        
        if ($year) {
            $where .= ' AND YEAR(start_date) = :year';
            $params['year'] = $year;
        }
        
        return $leaveModel->where($where, $params, 'start_date DESC');
    }
    
    /**
     * Get staff statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_staff,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_staff,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_staff,
                    SUM(CASE WHEN staff_type = 'administrative' THEN 1 ELSE 0 END) as administrative,
                    SUM(CASE WHEN staff_type = 'technical' THEN 1 ELSE 0 END) as technical,
                    SUM(CASE WHEN staff_type = 'support' THEN 1 ELSE 0 END) as support,
                    SUM(CASE WHEN staff_type = 'security' THEN 1 ELSE 0 END) as security,
                    SUM(CASE WHEN staff_type = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
                    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_staff,
                    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_staff,
                    AVG(experience) as average_experience,
                    AVG(salary) as average_salary
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Search staff
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT id, employee_id, first_name, last_name, email, phone, staff_type
                FROM {$this->table} 
                WHERE (first_name LIKE :query OR last_name LIKE :query 
                       OR employee_id LIKE :query OR email LIKE :query) 
                AND status = :status 
                ORDER BY first_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [
            'query' => "%{$query}%",
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Get staff by skills
     */
    public function getBySkills($skills)
    {
        $skillsArray = is_array($skills) ? $skills : [$skills];
        $conditions = [];
        $params = [];
        
        foreach ($skillsArray as $index => $skill) {
            $conditions[] = "skills LIKE :skill_{$index}";
            $params["skill_{$index}"] = "%{$skill}%";
        }
        
        $where = '(' . implode(' OR ', $conditions) . ') AND status = :status';
        $params['status'] = STATUS_ACTIVE;
        
        return $this->where($where, $params, 'experience DESC');
    }
    
    /**
     * Get retiring staff
     */
    public function getRetiringStaff($months = 6)
    {
        $retirementAge = 60; // Configurable
        $cutoffDate = date('Y-m-d', strtotime("+{$months} months"));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE DATE_ADD(date_of_birth, INTERVAL {$retirementAge} YEAR) <= :cutoff_date
                AND status = 'active'
                ORDER BY date_of_birth ASC";
        
        return $this->db->fetchAll($sql, ['cutoff_date' => $cutoffDate]);
    }
    
    /**
     * Get staff hierarchy
     */
    public function getHierarchy($departmentId = null)
    {
        $where = 'status = :status';
        $params = ['status' => STATUS_ACTIVE];
        
        if ($departmentId) {
            $where .= ' AND department_id = :department_id';
            $params['department_id'] = $departmentId;
        }
        
        $sql = "SELECT s.*, d.department_name, des.designation_name, des.level
                FROM {$this->table} s
                LEFT JOIN departments d ON s.department_id = d.id
                LEFT JOIN designations des ON s.designation_id = des.id
                WHERE {$where}
                ORDER BY d.department_name, des.level DESC, s.first_name";
        
        return $this->db->fetchAll($sql, $params);
    }
}