<?php

/**
 * Student Transfer Model
 * 
 * Handles student transfer data operations
 */
class Transfer extends Model
{
    protected $table = 'student_transfers';
    protected $fillable = [
        'student_id', 'transfer_number', 'transfer_type', 'transfer_date',
        'reason', 'destination_institution', 'destination_course', 'destination_class',
        'initiated_by', 'approved_by', 'approved_date', 'rejected_by', 'rejected_date',
        'rejection_reason', 'transfer_certificate_issued', 'certificate_number',
        'certificate_date', 'status', 'remarks'
    ];
    
    /**
     * Find transfer by number
     */
    public function findByNumber($transferNumber)
    {
        return $this->findBy('transfer_number', $transferNumber);
    }
    
    /**
     * Get transfers by student
     */
    public function getByStudent($studentId)
    {
        return $this->where('student_id = :student_id', ['student_id' => $studentId], 'transfer_date DESC');
    }
    
    /**
     * Get transfers by type
     */
    public function getByType($transferType)
    {
        return $this->where('transfer_type = :type', ['type' => $transferType], 'transfer_date DESC');
    }
    
    /**
     * Get pending transfers
     */
    public function getPendingTransfers()
    {
        $sql = "SELECT t.*, s.first_name, s.last_name, s.student_id, c.course_name
                FROM {$this->table} t
                JOIN students s ON t.student_id = s.id
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE t.status = 'pending'
                ORDER BY t.transfer_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get approved transfers
     */
    public function getApprovedTransfers()
    {
        return $this->where('status = :status', ['status' => 'approved'], 'approved_date DESC');
    }
    
    /**
     * Get transfer with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT t.*, 
                       s.first_name, s.last_name, s.student_id, s.email, s.phone,
                       c.course_name, cl.class_name,
                       initiator.name as initiated_by_name,
                       approver.name as approved_by_name
                FROM {$this->table} t
                JOIN students s ON t.student_id = s.id
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                LEFT JOIN users initiator ON t.initiated_by = initiator.id
                LEFT JOIN users approver ON t.approved_by = approver.id
                WHERE t.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get transfer statistics
     */
    public function getStats($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    COUNT(*) as total_transfers,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN transfer_type = 'internal' THEN 1 ELSE 0 END) as internal_transfers,
                    SUM(CASE WHEN transfer_type = 'external' THEN 1 ELSE 0 END) as external_transfers,
                    SUM(CASE WHEN transfer_type = 'course_change' THEN 1 ELSE 0 END) as course_changes,
                    SUM(CASE WHEN transfer_type = 'class_change' THEN 1 ELSE 0 END) as class_changes
                FROM {$this->table}
                WHERE YEAR(transfer_date) = :year";
        
        $result = $this->db->fetch($sql, ['year' => $year]);
        
        if ($result['total_transfers'] > 0) {
            $result['approval_rate'] = round(($result['approved'] / $result['total_transfers']) * 100, 2);
        } else {
            $result['approval_rate'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get monthly transfer trends
     */
    public function getMonthlyTrends($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(transfer_date) as month,
                    COUNT(*) as transfers,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
                FROM {$this->table}
                WHERE YEAR(transfer_date) = :year
                GROUP BY MONTH(transfer_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Issue transfer certificate
     */
    public function issueTransferCertificate($transferId, $certificateNumber)
    {
        return $this->update($transferId, [
            'transfer_certificate_issued' => 1,
            'certificate_number' => $certificateNumber,
            'certificate_date' => now()
        ]);
    }
    
    /**
     * Get transfers requiring certificate
     */
    public function getTransfersRequiringCertificate()
    {
        $sql = "SELECT t.*, s.first_name, s.last_name, s.student_id
                FROM {$this->table} t
                JOIN students s ON t.student_id = s.id
                WHERE t.status = 'approved' 
                AND t.transfer_certificate_issued = 0
                AND t.transfer_type = 'external'
                ORDER BY t.approved_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Generate certificate number
     */
    public function generateCertificateNumber()
    {
        $year = date('Y');
        $lastCertificate = $this->last('certificate_number LIKE :pattern', ['pattern' => "TC{$year}%"]);
        
        if ($lastCertificate) {
            $lastNumber = (int) substr($lastCertificate['certificate_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "TC{$year}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}