<?php

/**
 * Leave Model
 * 
 * Handles leave application data operations
 */
class Leave extends Model
{
    protected $table = 'leaves';
    protected $fillable = [
        'faculty_id', 'leave_type', 'start_date', 'end_date', 'leave_days',
        'reason', 'application_date', 'contact_during_leave', 'medical_certificate',
        'approved_by', 'approved_date', 'rejected_by', 'rejected_date',
        'rejection_reason', 'approval_remarks', 'status'
    ];
    
    /**
     * Get leaves by faculty
     */
    public function getByFaculty($facultyId, $year = null)
    {
        $where = 'faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($year) {
            $where .= ' AND YEAR(start_date) = :year';
            $params['year'] = $year;
        }
        
        return $this->where($where, $params, 'start_date DESC');
    }
    
    /**
     * Get leaves by type
     */
    public function getByType($leaveType, $facultyId = null)
    {
        $where = 'leave_type = :type';
        $params = ['type' => $leaveType];
        
        if ($facultyId) {
            $where .= ' AND faculty_id = :faculty_id';
            $params['faculty_id'] = $facultyId;
        }
        
        return $this->where($where, $params, 'start_date DESC');
    }
    
    /**
     * Get pending leaves
     */
    public function getPendingLeaves()
    {
        $sql = "SELECT l.*, f.first_name, f.last_name, f.employee_id, d.department_name
                FROM {$this->table} l
                JOIN faculty f ON l.faculty_id = f.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE l.status = 'pending'
                ORDER BY l.application_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get approved leaves
     */
    public function getApprovedLeaves($startDate = null, $endDate = null)
    {
        $where = 'status = :status';
        $params = ['status' => LEAVE_APPROVED];
        
        if ($startDate && $endDate) {
            $where .= ' AND start_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Get leave balance for faculty
     */
    public function getLeaveBalance($facultyId, $year = null)
    {
        $year = $year ?? date('Y');
        
        // Get leave entitlements (this could be from a separate table)
        $entitlements = $this->getLeaveEntitlements($facultyId);
        
        // Get used leaves
        $usedLeaves = $this->getUsedLeaves($facultyId, $year);
        
        $balance = [];
        foreach ($entitlements as $type => $entitled) {
            $used = $usedLeaves[$type] ?? 0;
            $balance[$type] = max(0, $entitled - $used);
        }
        
        return $balance;
    }
    
    /**
     * Get leave entitlements
     */
    private function getLeaveEntitlements($facultyId)
    {
        // Default entitlements (this could be configurable)
        return [
            'sick' => 12,
            'casual' => 12,
            'earned' => 30,
            'maternity' => 180,
            'paternity' => 15,
            'emergency' => 5
        ];
    }
    
    /**
     * Get used leaves
     */
    private function getUsedLeaves($facultyId, $year)
    {
        $sql = "SELECT 
                    leave_type,
                    SUM(leave_days) as used_days
                FROM {$this->table}
                WHERE faculty_id = :faculty_id 
                AND YEAR(start_date) = :year 
                AND status = 'approved'
                GROUP BY leave_type";
        
        $result = $this->db->fetchAll($sql, ['faculty_id' => $facultyId, 'year' => $year]);
        
        $used = [];
        foreach ($result as $row) {
            $used[$row['leave_type']] = $row['used_days'];
        }
        
        return $used;
    }
    
    /**
     * Check leave overlap
     */
    public function hasOverlap($facultyId, $startDate, $endDate, $excludeId = null)
    {
        $where = 'faculty_id = :faculty_id AND status = :status AND (
                    (start_date <= :start_date AND end_date >= :start_date) OR
                    (start_date <= :end_date AND end_date >= :end_date) OR
                    (start_date >= :start_date AND end_date <= :end_date)
                )';
        
        $params = [
            'faculty_id' => $facultyId,
            'status' => LEAVE_APPROVED,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        if ($excludeId) {
            $where .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->exists($where, $params);
    }
    
    /**
     * Get leave statistics
     */
    public function getStats($year = null, $departmentId = null)
    {
        $year = $year ?? date('Y');
        
        $where = 'YEAR(l.start_date) = :year';
        $params = ['year' => $year];
        
        if ($departmentId) {
            $where .= ' AND f.department_id = :department_id';
            $params['department_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN l.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN l.status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN l.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN l.status = 'approved' THEN l.leave_days ELSE 0 END) as total_leave_days,
                    AVG(CASE WHEN l.status = 'approved' THEN l.leave_days END) as avg_leave_days,
                    SUM(CASE WHEN l.leave_type = 'sick' AND l.status = 'approved' THEN l.leave_days ELSE 0 END) as sick_leave_days,
                    SUM(CASE WHEN l.leave_type = 'casual' AND l.status = 'approved' THEN l.leave_days ELSE 0 END) as casual_leave_days,
                    SUM(CASE WHEN l.leave_type = 'earned' AND l.status = 'approved' THEN l.leave_days ELSE 0 END) as earned_leave_days
                FROM {$this->table} l
                JOIN faculty f ON l.faculty_id = f.id
                WHERE {$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['total_applications'] > 0) {
            $result['approval_rate'] = round(($result['approved'] / $result['total_applications']) * 100, 2);
        } else {
            $result['approval_rate'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get monthly leave trends
     */
    public function getMonthlyTrends($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(start_date) as month,
                    COUNT(*) as applications,
                    SUM(CASE WHEN status = 'approved' THEN leave_days ELSE 0 END) as approved_days
                FROM {$this->table}
                WHERE YEAR(start_date) = :year
                GROUP BY MONTH(start_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Deduct leave balance
     */
    public function deductLeaveBalance($facultyId, $leaveType, $days)
    {
        // This would update a leave balance table
        // For now, we'll just log the deduction
        Logger::info("Leave balance deducted", [
            'faculty_id' => $facultyId,
            'leave_type' => $leaveType,
            'days' => $days
        ]);
        
        return true;
    }
    
    /**
     * Get faculty on leave today
     */
    public function getFacultyOnLeaveToday()
    {
        $today = today();
        
        $sql = "SELECT l.*, f.first_name, f.last_name, f.employee_id, d.department_name
                FROM {$this->table} l
                JOIN faculty f ON l.faculty_id = f.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE l.status = 'approved'
                AND :today BETWEEN l.start_date AND l.end_date
                ORDER BY f.first_name";
        
        return $this->db->fetchAll($sql, ['today' => $today]);
    }
}