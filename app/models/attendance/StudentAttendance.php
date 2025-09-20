<?php

/**
 * Student Attendance Model
 * 
 * Handles student attendance data operations
 */
class StudentAttendance extends Model
{
    protected $table = 'student_attendance';
    protected $fillable = [
        'student_id', 'class_id', 'section_id', 'subject_id', 'faculty_id',
        'attendance_date', 'period', 'status', 'remarks', 'marked_by',
        'marked_at', 'device_id', 'location'
    ];
    
    /**
     * Get attendance by student
     */
    public function getByStudent($studentId, $startDate = null, $endDate = null)
    {
        $where = 'student_id = :student_id';
        $params = ['student_id' => $studentId];
        
        if ($startDate && $endDate) {
            $where .= ' AND attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->where($where, $params, 'attendance_date DESC, period ASC');
    }
    
    /**
     * Get attendance by class
     */
    public function getByClass($classId, $date = null, $sectionId = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($date) {
            $where .= ' AND attendance_date = :date';
            $params['date'] = $date;
        }
        
        if ($sectionId) {
            $where .= ' AND section_id = :section_id';
            $params['section_id'] = $sectionId;
        }
        
        $sql = "SELECT sa.*, s.first_name, s.last_name, s.roll_number, sub.subject_name
                FROM {$this->table} sa
                JOIN students s ON sa.student_id = s.id
                LEFT JOIN subjects sub ON sa.subject_id = sub.id
                WHERE {$where}
                ORDER BY s.roll_number, sa.period";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get attendance by subject
     */
    public function getBySubject($subjectId, $date = null)
    {
        $where = 'subject_id = :subject_id';
        $params = ['subject_id' => $subjectId];
        
        if ($date) {
            $where .= ' AND attendance_date = :date';
            $params['date'] = $date;
        }
        
        return $this->where($where, $params, 'attendance_date DESC, period ASC');
    }
    
    /**
     * Mark attendance
     */
    public function markAttendance($data)
    {
        // Check if attendance already marked
        $existing = $this->first(
            'student_id = :student_id AND attendance_date = :date AND period = :period',
            [
                'student_id' => $data['student_id'],
                'date' => $data['attendance_date'],
                'period' => $data['period']
            ]
        );
        
        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], [
                'status' => $data['status'],
                'remarks' => $data['remarks'] ?? null,
                'marked_by' => $data['marked_by'],
                'marked_at' => now()
            ]);
        } else {
            // Create new record
            $data['marked_at'] = now();
            return $this->create($data);
        }
    }
    
    /**
     * Bulk mark attendance
     */
    public function bulkMarkAttendance($attendanceData)
    {
        $this->beginTransaction();
        
        try {
            foreach ($attendanceData as $data) {
                $this->markAttendance($data);
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($studentId, $startDate = null, $endDate = null)
    {
        $where = 'student_id = :student_id';
        $params = ['student_id' => $studentId];
        
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
                    SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days
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
     * Get class attendance summary
     */
    public function getClassAttendanceSummary($classId, $date)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT sa.student_id) as total_students,
                    SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN sa.status = 'late' THEN 1 ELSE 0 END) as late
                FROM {$this->table} sa
                JOIN students s ON sa.student_id = s.id
                WHERE s.class_id = :class_id AND sa.attendance_date = :date";
        
        $result = $this->db->fetch($sql, ['class_id' => $classId, 'date' => $date]);
        
        if ($result['total_students'] > 0) {
            $result['attendance_percentage'] = round(($result['present'] / $result['total_students']) * 100, 2);
        } else {
            $result['attendance_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get low attendance students
     */
    public function getLowAttendanceStudents($threshold = 75)
    {
        $sql = "SELECT s.id, s.first_name, s.last_name, s.student_id, s.roll_number,
                       COUNT(sa.id) as total_days,
                       SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_days,
                       ROUND((SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) / COUNT(sa.id)) * 100, 2) as attendance_percentage
                FROM students s
                JOIN {$this->table} sa ON s.id = sa.student_id
                WHERE s.status = 'active' AND sa.attendance_date >= :start_date
                GROUP BY s.id
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
        
        if (!empty($filters['class_id'])) {
            $where .= ' AND s.class_id = :class_id';
            $params['class_id'] = $filters['class_id'];
        }
        
        if (!empty($filters['section_id'])) {
            $where .= ' AND s.section_id = :section_id';
            $params['section_id'] = $filters['section_id'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where .= ' AND sa.attendance_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $filters['start_date'];
            $params['end_date'] = $filters['end_date'];
        }
        
        $sql = "SELECT s.id, s.first_name, s.last_name, s.student_id, s.roll_number,
                       COUNT(sa.id) as total_days,
                       SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_days,
                       SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                       SUM(CASE WHEN sa.status = 'late' THEN 1 ELSE 0 END) as late_days,
                       ROUND((SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) / COUNT(sa.id)) * 100, 2) as attendance_percentage
                FROM students s
                LEFT JOIN {$this->table} sa ON s.id = sa.student_id
                WHERE {$where}
                GROUP BY s.id
                ORDER BY s.roll_number";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get monthly attendance summary
     */
    public function getMonthlyAttendance($studentId, $year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');
        
        $sql = "SELECT 
                    DAY(attendance_date) as day,
                    status,
                    subject_id,
                    period
                FROM {$this->table}
                WHERE student_id = :student_id 
                AND YEAR(attendance_date) = :year 
                AND MONTH(attendance_date) = :month
                ORDER BY attendance_date, period";
        
        return $this->db->fetchAll($sql, [
            'student_id' => $studentId,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    /**
     * Check if attendance marked
     */
    public function isAttendanceMarked($classId, $date, $period, $sectionId = null)
    {
        $where = 'class_id = :class_id AND attendance_date = :date AND period = :period';
        $params = ['class_id' => $classId, 'date' => $date, 'period' => $period];
        
        if ($sectionId) {
            $where .= ' AND section_id = :section_id';
            $params['section_id'] = $sectionId;
        }
        
        return $this->exists($where, $params);
    }
}