<?php

/**
 * Employee Model
 * 
 * Handles employee data operations (extends Faculty for non-teaching staff)
 */
class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = [
        'user_id', 'employee_id', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'blood_group', 'photo', 'department_id',
        'designation_id', 'employee_type', 'joining_date', 'confirmation_date',
        'qualification', 'experience', 'skills', 'salary', 'bank_account',
        'ifsc_code', 'pan_number', 'aadhar_number', 'address', 'city', 'state',
        'pincode', 'emergency_contact', 'emergency_relation', 'emergency_phone',
        'marital_status', 'spouse_name', 'spouse_phone', 'medical_conditions',
        'status', 'remarks'
    ];
    
    /**
     * Find employee by employee ID
     */
    public function findByEmployeeId($employeeId)
    {
        return $this->findBy('employee_id', $employeeId);
    }
    
    /**
     * Find employee by user ID
     */
    public function findByUserId($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get employees by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id = :department_id AND status = :status', [
            'department_id' => $departmentId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get employees by type
     */
    public function getByType($employeeType)
    {
        return $this->where('employee_type = :type AND status = :status', [
            'type' => $employeeType,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get employees by designation
     */
    public function getByDesignation($designationId)
    {
        return $this->where('designation_id = :designation_id AND status = :status', [
            'designation_id' => $designationId,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get employee with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT e.*, 
                       d.department_name,
                       des.designation_name,
                       u.email as user_email, u.last_login
                FROM {$this->table} e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get employee payroll
     */
    public function getPayroll($employeeId, $year = null, $month = null)
    {
        $payrollModel = new Payroll();
        $where = 'employee_id = :employee_id';
        $params = ['employee_id' => $employeeId];
        
        if ($year && $month) {
            $where .= ' AND YEAR(pay_period_end) = :year AND MONTH(pay_period_end) = :month';
            $params['year'] = $year;
            $params['month'] = $month;
        }
        
        return $payrollModel->where($where, $params, 'pay_period_end DESC');
    }
    
    /**
     * Get employee attendance
     */
    public function getAttendance($employeeId, $startDate = null, $endDate = null)
    {
        $attendanceModel = new EmployeeAttendance();
        $where = 'employee_id = :employee_id';
        $params = ['employee_id' => $employeeId];
        
        if ($startDate && $endDate) {
            $where .= ' AND attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $attendanceModel->where($where, $params, 'attendance_date DESC');
    }
    
    /**
     * Get employee leaves
     */
    public function getLeaves($employeeId, $year = null)
    {
        $leaveModel = new Leave();
        $where = 'employee_id = :employee_id';
        $params = ['employee_id' => $employeeId];
        
        if ($year) {
            $where .= ' AND YEAR(start_date) = :year';
            $params['year'] = $year;
        }
        
        return $leaveModel->where($where, $params, 'start_date DESC');
    }
    
    /**
     * Get employee performance
     */
    public function getPerformance($employeeId, $year = null)
    {
        $performanceModel = new Performance();
        $where = 'employee_id = :employee_id';
        $params = ['employee_id' => $employeeId];
        
        if ($year) {
            $where .= ' AND review_year = :year';
            $params['year'] = $year;
        }
        
        return $performanceModel->where($where, $params, 'review_date DESC');
    }
    
    /**
     * Get birthday employees
     */
    public function getBirthdayEmployees($date = null)
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
     * Get employee statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_employees,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_employees,
                    SUM(CASE WHEN employee_type = 'teaching' THEN 1 ELSE 0 END) as teaching_staff,
                    SUM(CASE WHEN employee_type = 'non_teaching' THEN 1 ELSE 0 END) as non_teaching_staff,
                    SUM(CASE WHEN employee_type = 'administrative' THEN 1 ELSE 0 END) as administrative_staff,
                    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_employees,
                    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_employees
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Search employees
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT id, employee_id, first_name, last_name, email, phone, designation_id 
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
     * Get employees by salary range
     */
    public function getBySalaryRange($minSalary, $maxSalary)
    {
        return $this->where('salary BETWEEN :min_salary AND :max_salary AND status = :status', [
            'min_salary' => $minSalary,
            'max_salary' => $maxSalary,
            'status' => STATUS_ACTIVE
        ], 'salary DESC');
    }
    
    /**
     * Get retiring employees
     */
    public function getRetiringEmployees($months = 6)
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
     * Get employee hierarchy
     */
    public function getHierarchy($departmentId = null)
    {
        $where = 'status = :status';
        $params = ['status' => STATUS_ACTIVE];
        
        if ($departmentId) {
            $where .= ' AND department_id = :department_id';
            $params['department_id'] = $departmentId;
        }
        
        $sql = "SELECT e.*, d.department_name, des.designation_name, des.level
                FROM {$this->table} e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                WHERE {$where}
                ORDER BY d.department_name, des.level DESC, e.first_name";
        
        return $this->db->fetchAll($sql, $params);
    }
}