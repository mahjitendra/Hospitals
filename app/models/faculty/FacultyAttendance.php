<?php

/**
 * Faculty Attendance Model
 * 
 * Handles faculty attendance data operations
 */
class FacultyAttendance extends Model
{
    protected $table = 'faculty_attendance';
    protected $fillable = [
        'faculty_id', 'attendance_date', 'status', 'check_in_time', 'check_out_time',
        'working_hours', 'overtime_hours', 'break_time', 'location', 'device_id',
        'remarks', 'marked_by', 'approved_by'
    ];
    
    /**
     * Get attendance by faculty
     */
    public function getByFaculty($facultyId, $startDate = null, $endDate = null)
    {
        $where = 'faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($startDate && $endDate) {
            $where .= ' AND attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->where($where, $params, 'attendance_date DESC');
    }
    
    /**
     * Get attendance by date
     */
    public function getByDate($date)
    {
        $sql = "SELECT fa.*, f.first_name, f.last_name, f.employee_id, d.department_name
                FROM {$this->table} fa
                JOIN faculty f ON fa.faculty_id = f.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE fa.attendance_date = :date
                ORDER BY f.first_name";
        
        return $this->db->fetchAll($sql, ['date' => $date]);
    }
    
    /**
     * Mark attendance
     */
    public function markAttendance($data)
    {
        // Check if attendance already marked
        $existing = $this->first(
            'faculty_id = :faculty_id AND attendance_date = :date',
            ['faculty_id' => $data['faculty_id'], 'date' => $data['attendance_date']]
        );
        
        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], [
                'status' => $data['status'],
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'overtime_hours' => $data['overtime_hours'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'marked_by' => $data['marked_by']
            ]);
        } else {
            // Create new record
            return $this->create($data);
        }
    }
    
    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($facultyId, $startDate = null, $endDate = null)
    {
        $where = 'faculty_id = :faculty_id';
        $params = ['faculty_id' => $facultyId];
        
        if ($startDate && $endDate) {
            $where .= ' AND attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        } else {
            // Default to current month
            $where .= ' AND attendance_date >= :start_date';
            $params['start_date'] = date('Y-m-01');
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days,
                    AVG(working_hours) as avg_working_hours,
                    SUM(overtime_hours) as total_overtime
                FROM {$this->table}
                WHERE {$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['total_days'] > 0) {
            $result['attendance_percentage'] = round(($result['present_days'] / $result['total_days']) * 100, 2);
        } else {
            $result['attendance_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get department attendance summary
     */
    public function getDepartmentAttendanceSummary($departmentId, $date)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT fa.faculty_id) as total_faculty,
                    SUM(CASE WHEN fa.status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN fa.status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN fa.status = 'late' THEN 1 ELSE 0 END) as late,
                    AVG(fa.working_hours) as avg_working_hours
                FROM {$this->table} fa
                JOIN faculty f ON fa.faculty_id = f.id
                WHERE f.department_id = :department_id AND fa.attendance_date = :date";
        
        $result = $this->db->fetch($sql, ['department_id' => $departmentId, 'date' => $date]);
        
        if ($result['total_faculty'] > 0) {
            $result['attendance_percentage'] = round(($result['present'] / $result['total_faculty']) * 100, 2);
        } else {
            $result['attendance_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get low attendance faculty
     */
    public function getLowAttendanceFaculty($threshold = 80)
    {
        $sql = "SELECT f.id, f.first_name, f.last_name, f.employee_id,
                       COUNT(fa.id) as total_days,
                       SUM(CASE WHEN fa.status = 'present' THEN 1 ELSE 0 END) as present_days,
                       ROUND((SUM(CASE WHEN fa.status = 'present' THEN 1 ELSE 0 END) / COUNT(fa.id)) * 100, 2) as attendance_percentage
                FROM faculty f
                JOIN {$this->table} fa ON f.id = fa.faculty_id
                WHERE f.status = 'active' AND fa.attendance_date >= :start_date
                GROUP BY f.id
                HAVING attendance_percentage < :threshold
                ORDER BY attendance_percentage ASC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => date('Y-m-01'),
            'threshold' => $threshold
        ]);
    }
    
    /**
     * Get attendance report
     */
    public function getAttendanceReport($filters = [])
    {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['department_id'])) {
            $where .= ' AND f.department_id = :department_id';
            $params['department_id'] = $filters['department_id'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where .= ' AND fa.attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $filters['start_date'];
            $params['end_date'] = $filters['end_date'];
        }
        
        $sql = "SELECT f.id, f.first_name, f.last_name, f.employee_id,
                       COUNT(fa.id) as total_days,
                       SUM(CASE WHEN fa.status = 'present' THEN 1 ELSE 0 END) as present_days,
                       SUM(CASE WHEN fa.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                       SUM(CASE WHEN fa.status = 'late' THEN 1 ELSE 0 END) as late_days,
                       SUM(fa.working_hours) as total_working_hours,
                       SUM(fa.overtime_hours) as total_overtime_hours,
                       ROUND((SUM(CASE WHEN fa.status = 'present' THEN 1 ELSE 0 END) / COUNT(fa.id)) * 100, 2) as attendance_percentage
                FROM faculty f
                LEFT JOIN {$this->table} fa ON f.id = fa.faculty_id
                WHERE {$where}
                GROUP BY f.id
                ORDER BY f.first_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Check if attendance marked
     */
    public function isAttendanceMarked($facultyId, $date)
    {
        return $this->exists(
            'faculty_id = :faculty_id AND attendance_date = :date',
            ['faculty_id' => $facultyId, 'date' => $date]
        );
    }
    
    /**
     * Get perfect attendance faculty
     */
    public function getPerfectAttendanceFaculty($startDate, $endDate)
    {
        $sql = "SELECT f.id, f.first_name, f.last_name, f.employee_id,
                       COUNT(fa.id) as total_days,
                       SUM(CASE WHEN fa.status = 'present' THEN 1 ELSE 0 END) as present_days
                FROM faculty f
                JOIN {$this->table} fa ON f.id = fa.faculty_id
                WHERE f.status = 'active' 
                AND fa.attendance_date BETWEEN :start_date AND :end_date
                GROUP BY f.id
                HAVING present_days = total_days
                ORDER BY f.first_name";
        
        return $this->db->fetchAll($sql, ['start_date' => $startDate, 'end_date' => $endDate]);
    }
}