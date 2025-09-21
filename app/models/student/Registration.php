<?php

/**
 * Student Registration Model
 * 
 * Handles student registration data operations
 */
class Registration extends Model
{
    protected $table = 'student_registrations';
    protected $fillable = [
        'student_id', 'course_id', 'class_id', 'section_id', 'academic_session',
        'semester', 'registration_number', 'registration_date', 'roll_number',
        'registration_fee', 'fee_paid', 'payment_id', 'registered_by',
        'status', 'remarks', 'cancelled_by', 'cancelled_date', 'cancellation_reason'
    ];
    
    /**
     * Find registration by number
     */
    public function findByNumber($registrationNumber)
    {
        return $this->findBy('registration_number', $registrationNumber);
    }
    
    /**
     * Get registrations by student
     */
    public function getByStudent($studentId)
    {
        return $this->where('student_id = :student_id', ['student_id' => $studentId], 'registration_date DESC');
    }
    
    /**
     * Get registrations by course
     */
    public function getByCourse($courseId, $academicSession = null)
    {
        $where = 'course_id = :course_id';
        $params = ['course_id' => $courseId];
        
        if ($academicSession) {
            $where .= ' AND academic_session = :session';
            $params['session'] = $academicSession;
        }
        
        return $this->where($where, $params, 'registration_date DESC');
    }
    
    /**
     * Get registrations by class
     */
    public function getByClass($classId, $academicSession = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($academicSession) {
            $where .= ' AND academic_session = :session';
            $params['session'] = $academicSession;
        }
        
        return $this->where($where, $params, 'roll_number ASC');
    }
    
    /**
     * Get registration with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT r.*, 
                       s.first_name, s.last_name, s.email, s.phone,
                       c.course_name, c.course_code,
                       cl.class_name,
                       sec.section_name,
                       u.name as registered_by_name
                FROM {$this->table} r
                JOIN students s ON r.student_id = s.id
                JOIN courses c ON r.course_id = c.id
                LEFT JOIN classes cl ON r.class_id = cl.id
                LEFT JOIN sections sec ON r.section_id = sec.id
                LEFT JOIN users u ON r.registered_by = u.id
                WHERE r.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Check if student is registered for session
     */
    public function isStudentRegistered($studentId, $academicSession)
    {
        return $this->exists(
            'student_id = :student_id AND academic_session = :session AND status = :status',
            ['student_id' => $studentId, 'session' => $academicSession, 'status' => 'active']
        );
    }
    
    /**
     * Get registration statistics
     */
    public function getStats($academicSession = null)
    {
        $where = '1=1';
        $params = [];
        
        if ($academicSession) {
            $where .= ' AND academic_session = :session';
            $params['session'] = $academicSession;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_registrations,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_registrations,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_registrations,
                    SUM(registration_fee) as total_registration_fees,
                    SUM(CASE WHEN fee_paid = 1 THEN registration_fee ELSE 0 END) as collected_fees,
                    COUNT(DISTINCT course_id) as courses_with_registrations,
                    COUNT(DISTINCT class_id) as classes_with_registrations
                FROM {$this->table}
                WHERE {$where}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Get monthly registration trends
     */
    public function getMonthlyTrends($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(registration_date) as month,
                    COUNT(*) as registrations,
                    SUM(registration_fee) as fees_collected
                FROM {$this->table}
                WHERE YEAR(registration_date) = :year
                GROUP BY MONTH(registration_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Get course-wise registrations
     */
    public function getCourseWiseRegistrations($academicSession = null)
    {
        $where = '1=1';
        $params = [];
        
        if ($academicSession) {
            $where .= ' AND r.academic_session = :session';
            $params['session'] = $academicSession;
        }
        
        $sql = "SELECT c.course_name, c.course_code,
                       COUNT(r.id) as total_registrations,
                       SUM(CASE WHEN r.status = 'active' THEN 1 ELSE 0 END) as active_registrations
                FROM courses c
                LEFT JOIN {$this->table} r ON c.id = r.course_id AND {$where}
                GROUP BY c.id, c.course_name, c.course_code
                ORDER BY total_registrations DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Cancel registration
     */
    public function cancelRegistration($id, $reason, $cancelledBy)
    {
        return $this->update($id, [
            'status' => 'cancelled',
            'cancelled_by' => $cancelledBy,
            'cancelled_date' => now(),
            'cancellation_reason' => $reason
        ]);
    }
    
    /**
     * Reactivate registration
     */
    public function reactivateRegistration($id)
    {
        return $this->update($id, [
            'status' => 'active',
            'cancelled_by' => null,
            'cancelled_date' => null,
            'cancellation_reason' => null
        ]);
    }
    
    /**
     * Get pending fee registrations
     */
    public function getPendingFeeRegistrations()
    {
        $sql = "SELECT r.*, s.first_name, s.last_name, s.phone, s.email
                FROM {$this->table} r
                JOIN students s ON r.student_id = s.id
                WHERE r.fee_paid = 0 AND r.status = 'active'
                ORDER BY r.registration_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Mark fee as paid
     */
    public function markFeeAsPaid($id, $paymentId)
    {
        return $this->update($id, [
            'fee_paid' => 1,
            'payment_id' => $paymentId,
            'fee_paid_date' => now()
        ]);
    }
}