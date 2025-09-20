<?php

/**
 * Fee Model
 * 
 * Handles fee data operations
 */
class Fee extends Model
{
    protected $table = 'fees';
    protected $fillable = [
        'student_id', 'fee_structure_id', 'amount', 'discount_amount',
        'fine_amount', 'total_amount', 'due_date', 'payment_id',
        'paid_date', 'status', 'remarks'
    ];
    
    /**
     * Get fees by student
     */
    public function getByStudent($studentId)
    {
        return $this->where('student_id = :student_id', ['student_id' => $studentId], 'due_date ASC');
    }
    
    /**
     * Get pending fees by student
     */
    public function getPendingByStudent($studentId)
    {
        return $this->where('student_id = :student_id AND status = :status', [
            'student_id' => $studentId,
            'status' => FEE_PENDING
        ], 'due_date ASC');
    }
    
    /**
     * Get overdue fees
     */
    public function getOverdueFees()
    {
        return $this->where('due_date < :today AND status = :status', [
            'today' => today(),
            'status' => FEE_PENDING
        ], 'due_date ASC');
    }
    
    /**
     * Get fees by payment
     */
    public function getByPayment($paymentId)
    {
        return $this->where('payment_id = :payment_id', ['payment_id' => $paymentId]);
    }
    
    /**
     * Get fee with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT f.*, 
                       s.first_name, s.last_name, s.student_id, s.roll_number,
                       fs.fee_type, fs.description as fee_description,
                       p.payment_date, p.payment_method, p.transaction_id
                FROM {$this->table} f
                JOIN students s ON f.student_id = s.id
                JOIN fee_structures fs ON f.fee_structure_id = fs.id
                LEFT JOIN payments p ON f.payment_id = p.id
                WHERE f.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Calculate total fee amount
     */
    public function calculateTotal($feeId)
    {
        $fee = $this->find($feeId);
        if (!$fee) {
            return 0;
        }
        
        $total = $fee['amount'];
        
        // Subtract discount
        if ($fee['discount_amount'] > 0) {
            $total -= $fee['discount_amount'];
        }
        
        // Add fine if overdue
        if ($fee['due_date'] < today() && $fee['status'] === FEE_PENDING) {
            $fine = $this->calculateLateFee($fee['amount'], $fee['due_date']);
            $total += $fine;
            
            // Update fine amount
            $this->update($feeId, ['fine_amount' => $fine, 'total_amount' => $total]);
        }
        
        return $total;
    }
    
    /**
     * Calculate late fee
     */
    private function calculateLateFee($amount, $dueDate)
    {
        $lateFeePercentage = config('app.late_fee_percentage', 2);
        $gracePeriod = config('app.grace_period_days', 7);
        
        $daysOverdue = (strtotime(today()) - strtotime($dueDate)) / (24 * 60 * 60);
        
        if ($daysOverdue <= $gracePeriod) {
            return 0;
        }
        
        return ($amount * $lateFeePercentage) / 100;
    }
    
    /**
     * Mark fee as paid
     */
    public function markAsPaid($feeId, $paymentId)
    {
        return $this->update($feeId, [
            'payment_id' => $paymentId,
            'paid_date' => now(),
            'status' => FEE_PAID
        ]);
    }
    
    /**
     * Apply discount
     */
    public function applyDiscount($feeId, $discountAmount, $reason = null)
    {
        $fee = $this->find($feeId);
        if (!$fee) {
            throw new Exception('Fee not found');
        }
        
        $newTotal = $fee['amount'] - $discountAmount + ($fee['fine_amount'] ?? 0);
        
        return $this->update($feeId, [
            'discount_amount' => $discountAmount,
            'total_amount' => $newTotal,
            'remarks' => $reason
        ]);
    }
    
    /**
     * Get fee statistics
     */
    public function getStats($startDate = null, $endDate = null)
    {
        $where = '1=1';
        $params = [];
        
        if ($startDate && $endDate) {
            $where .= ' AND due_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_fees,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as collected_amount,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount,
                    SUM(discount_amount) as total_discounts,
                    SUM(fine_amount) as total_fines,
                    COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_count
                FROM {$this->table}
                WHERE {$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['total_amount'] > 0) {
            $result['collection_percentage'] = round(($result['collected_amount'] / $result['total_amount']) * 100, 2);
        } else {
            $result['collection_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get monthly collection report
     */
    public function getMonthlyCollection($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(paid_date) as month,
                    SUM(amount) as collected_amount,
                    COUNT(*) as fee_count
                FROM {$this->table}
                WHERE status = 'paid' AND YEAR(paid_date) = :year
                GROUP BY MONTH(paid_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Get fee defaulters
     */
    public function getDefaulters($days = 30)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $sql = "SELECT f.*, s.first_name, s.last_name, s.student_id, s.phone, s.email,
                       fs.fee_type
                FROM {$this->table} f
                JOIN students s ON f.student_id = s.id
                JOIN fee_structures fs ON f.fee_structure_id = fs.id
                WHERE f.due_date <= :cutoff_date AND f.status = 'pending'
                ORDER BY f.due_date ASC";
        
        return $this->db->fetchAll($sql, ['cutoff_date' => $cutoffDate]);
    }
    
    /**
     * Update overdue fees
     */
    public function updateOverdueFees()
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'overdue' 
                WHERE due_date < :today AND status = 'pending'";
        
        return $this->db->query($sql, ['today' => today()]);
    }
    
    /**
     * Get student fee summary
     */
    public function getStudentFeeSummary($studentId)
    {
        $sql = "SELECT 
                    SUM(amount) as total_fees,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount,
                    SUM(discount_amount) as total_discounts,
                    SUM(fine_amount) as total_fines
                FROM {$this->table}
                WHERE student_id = :student_id";
        
        return $this->db->fetch($sql, ['student_id' => $studentId]);
    }
}