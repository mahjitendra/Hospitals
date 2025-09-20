<?php

/**
 * Fee Management Controller
 * 
 * Handles fee collection and management
 */
class FeeController extends Controller
{
    private $feeModel;
    private $feeStructureModel;
    private $paymentModel;
    private $studentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->feeModel = new Fee();
        $this->feeStructureModel = new FeeStructure();
        $this->paymentModel = new Payment();
        $this->studentModel = new Student();
    }
    
    /**
     * Display fees dashboard
     */
    public function index()
    {
        $stats = $this->getFeeStats();
        $recentPayments = $this->getRecentPayments();
        $pendingFees = $this->getPendingFees();
        $overdues = $this->getOverdueFees();
        
        $this->render('fees/index', [
            'title' => 'Fee Management',
            'stats' => $stats,
            'recentPayments' => $recentPayments,
            'pendingFees' => $pendingFees,
            'overdues' => $overdues
        ]);
    }
    
    /**
     * Show fee collection form
     */
    public function collect()
    {
        $this->requirePermission('fee_collect');
        
        $studentId = $this->input('student_id');
        $student = null;
        $pendingFees = [];
        
        if ($studentId) {
            $student = $this->studentModel->find($studentId);
            if ($student) {
                $pendingFees = $this->getStudentPendingFees($studentId);
            }
        }
        
        $this->render('fees/collect', [
            'title' => 'Collect Fee',
            'student' => $student,
            'pendingFees' => $pendingFees
        ]);
    }
    
    /**
     * Process fee payment
     */
    public function processPayment()
    {
        $this->requirePermission('fee_collect');
        
        $data = $this->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required',
            'payment_method' => 'required|in:cash,card,online,cheque,dd',
            'amount' => 'required|numeric|min:1',
            'transaction_id' => 'max:100',
            'remarks' => 'max:255'
        ]);
        
        try {
            $this->paymentModel->beginTransaction();
            
            $student = $this->studentModel->find($data['student_id']);
            $totalAmount = 0;
            
            // Validate fee IDs and calculate total
            foreach ($data['fee_ids'] as $feeId) {
                $fee = $this->feeModel->find($feeId);
                if (!$fee || $fee['student_id'] != $data['student_id'] || $fee['status'] !== FEE_PENDING) {
                    throw new Exception('Invalid fee selected');
                }
                $totalAmount += $fee['amount'];
            }
            
            // Validate payment amount
            if ($data['amount'] != $totalAmount) {
                throw new Exception('Payment amount does not match fee total');
            }
            
            // Create payment record
            $paymentId = $this->paymentModel->create([
                'student_id' => $data['student_id'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_date' => now(),
                'collected_by' => $this->user()['id'],
                'remarks' => $data['remarks'] ?? null,
                'status' => PAYMENT_SUCCESS
            ]);
            
            // Update fee status
            foreach ($data['fee_ids'] as $feeId) {
                $this->feeModel->update($feeId, [
                    'status' => FEE_PAID,
                    'payment_id' => $paymentId,
                    'paid_date' => now()
                ]);
            }
            
            // Generate receipt
            $receiptModel = new Receipt();
            $receiptId = $receiptModel->create([
                'payment_id' => $paymentId,
                'student_id' => $data['student_id'],
                'receipt_number' => $this->generateReceiptNumber(),
                'amount' => $data['amount'],
                'receipt_date' => now()
            ]);
            
            $this->paymentModel->commit();
            
            // Send notifications
            $this->sendPaymentNotifications($student, $data['amount'], $receiptId);
            
            $this->logActivity('fee_collected', "Collected fee from: {$student['first_name']} {$student['last_name']}", $data);
            $this->flash('success', 'Fee collected successfully');
            
            $this->redirect('/fees/receipt/' . $receiptId);
            
        } catch (Exception $e) {
            $this->paymentModel->rollback();
            Logger::error('Fee collection failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to collect fee: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show fee receipt
     */
    public function receipt($receiptId)
    {
        $receiptModel = new Receipt();
        $receipt = $receiptModel->find($receiptId);
        
        if (!$receipt) {
            $this->flash('error', 'Receipt not found');
            $this->redirect('/fees');
        }
        
        $receipt['student'] = $this->studentModel->find($receipt['student_id']);
        $receipt['payment'] = $this->paymentModel->find($receipt['payment_id']);
        $receipt['fees'] = $this->getReceiptFees($receipt['payment_id']);
        
        $this->render('fees/receipt', [
            'title' => 'Fee Receipt',
            'receipt' => $receipt
        ]);
    }
    
    /**
     * Download receipt PDF
     */
    public function downloadReceipt($receiptId)
    {
        $receiptModel = new Receipt();
        $receipt = $receiptModel->find($receiptId);
        
        if (!$receipt) {
            $this->flash('error', 'Receipt not found');
            $this->redirect('/fees');
        }
        
        $receipt['student'] = $this->studentModel->find($receipt['student_id']);
        $receipt['payment'] = $this->paymentModel->find($receipt['payment_id']);
        $receipt['fees'] = $this->getReceiptFees($receipt['payment_id']);
        
        return $this->generatePDF('fees/receipt_pdf', ['receipt' => $receipt], 'receipt_' . $receipt['receipt_number']);
    }
    
    /**
     * Get fee statistics
     */
    private function getFeeStats()
    {
        $db = Database::getInstance();
        
        // Current month stats
        $currentMonth = date('Y-m');
        
        $sql = "SELECT 
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as collected,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue,
                    COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
                FROM fees 
                WHERE DATE_FORMAT(due_date, '%Y-%m') = :month";
        
        return $db->fetch($sql, ['month' => $currentMonth]);
    }
    
    /**
     * Get recent payments
     */
    private function getRecentPayments()
    {
        $db = Database::getInstance();
        $sql = "SELECT p.*, s.first_name, s.last_name, s.student_id 
                FROM payments p 
                JOIN students s ON p.student_id = s.id 
                ORDER BY p.payment_date DESC 
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get pending fees
     */
    private function getPendingFees()
    {
        $db = Database::getInstance();
        $sql = "SELECT f.*, s.first_name, s.last_name, s.student_id 
                FROM fees f 
                JOIN students s ON f.student_id = s.id 
                WHERE f.status = 'pending' 
                ORDER BY f.due_date ASC 
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get overdue fees
     */
    private function getOverdueFees()
    {
        $db = Database::getInstance();
        $sql = "SELECT f.*, s.first_name, s.last_name, s.student_id 
                FROM fees f 
                JOIN students s ON f.student_id = s.id 
                WHERE f.status = 'overdue' 
                ORDER BY f.due_date ASC 
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get student pending fees
     */
    private function getStudentPendingFees($studentId)
    {
        return $this->feeModel->where('student_id = :student_id AND status = :status', [
            'student_id' => $studentId,
            'status' => FEE_PENDING
        ], 'due_date ASC');
    }
    
    /**
     * Generate receipt number
     */
    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $receiptModel = new Receipt();
        $lastReceipt = $receiptModel->last('receipt_number LIKE :pattern', ['pattern' => "RCP{$year}{$month}%"]);
        
        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt['receipt_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "RCP{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Send payment notifications
     */
    private function sendPaymentNotifications($student, $amount, $receiptId)
    {
        // Send SMS
        if (!empty($student['phone'])) {
            $message = "Fee payment of Rs.{$amount} received successfully. Receipt: {$receiptId}";
            $this->sendSMS($student['phone'], $message);
        }
        
        // Send email
        if (!empty($student['email'])) {
            $this->sendMail($student['email'], 'Fee Payment Confirmation', 'fee_payment_confirmation', [
                'student' => $student,
                'amount' => $amount,
                'receipt_id' => $receiptId
            ]);
        }
    }
    
    /**
     * Get receipt fees
     */
    private function getReceiptFees($paymentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT f.*, fs.fee_type 
                FROM fees f 
                JOIN fee_structures fs ON f.fee_structure_id = fs.id 
                WHERE f.payment_id = :payment_id";
        
        return $db->fetchAll($sql, ['payment_id' => $paymentId]);
    }
}