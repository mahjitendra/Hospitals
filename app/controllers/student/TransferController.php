<?php

/**
 * Student Transfer Controller
 * 
 * Handles student transfer operations
 */
class TransferController extends Controller
{
    private $transferModel;
    private $studentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->transferModel = new Transfer();
        $this->studentModel = new Student();
    }
    
    /**
     * Display transfers list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $type = $this->input('type', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (student_name LIKE :search OR transfer_number LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        if (!empty($type)) {
            $where .= ' AND transfer_type = :type';
            $params['type'] = $type;
        }
        
        $transfers = $this->transferModel->paginate($page, 25, $where, $params, 'transfer_date DESC');
        
        $this->render('student/transfers/index', [
            'title' => 'Student Transfers',
            'transfers' => $transfers,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type
            ]
        ]);
    }
    
    /**
     * Show transfer form
     */
    public function create($studentId)
    {
        $this->requirePermission('student_transfer');
        
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        if ($student['status'] !== STATUS_ACTIVE) {
            $this->flash('error', 'Only active students can be transferred');
            $this->redirect('/students/' . $studentId);
        }
        
        $this->render('student/transfers/create', [
            'title' => 'Transfer Student',
            'student' => $student
        ]);
    }
    
    /**
     * Process transfer
     */
    public function store($studentId)
    {
        $this->requirePermission('student_transfer');
        
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $data = $this->validate([
            'transfer_type' => 'required|in:internal,external,course_change,class_change',
            'transfer_date' => 'required|date',
            'reason' => 'required|min:10',
            'destination_institution' => 'required_if:transfer_type,external|min:2',
            'destination_course' => 'required_if:transfer_type,course_change|exists:courses,id',
            'destination_class' => 'required_if:transfer_type,class_change|exists:classes,id',
            'remarks' => 'max:500'
        ]);
        
        try {
            $this->transferModel->beginTransaction();
            
            // Generate transfer number
            $data['transfer_number'] = $this->generateTransferNumber();
            $data['student_id'] = $studentId;
            $data['initiated_by'] = $this->user()['id'];
            $data['status'] = 'pending';
            
            // Create transfer record
            $transferId = $this->transferModel->create($data);
            
            // Update student status based on transfer type
            $this->updateStudentForTransfer($studentId, $data);
            
            $this->transferModel->commit();
            
            // Send notifications
            $this->sendTransferNotifications($student, $data);
            
            $this->logActivity('student_transfer_initiated', "Initiated transfer for student: {$student['first_name']} {$student['last_name']}", $data);
            $this->flash('success', 'Transfer initiated successfully. Transfer Number: ' . $data['transfer_number']);
            
            $this->redirect('/students/transfers');
            
        } catch (Exception $e) {
            $this->transferModel->rollback();
            Logger::error('Transfer initiation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to initiate transfer: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show transfer details
     */
    public function show($id)
    {
        $transfer = $this->transferModel->find($id);
        if (!$transfer) {
            $this->flash('error', 'Transfer not found');
            $this->redirect('/students/transfers');
        }
        
        $transfer['student'] = $this->studentModel->find($transfer['student_id']);
        $transfer['initiated_by_user'] = $this->userModel->find($transfer['initiated_by']);
        
        if ($transfer['approved_by']) {
            $transfer['approved_by_user'] = $this->userModel->find($transfer['approved_by']);
        }
        
        $this->render('student/transfers/show', [
            'title' => 'Transfer Details',
            'transfer' => $transfer
        ]);
    }
    
    /**
     * Approve transfer
     */
    public function approve($id)
    {
        $this->requirePermission('student_transfer_approve');
        
        $transfer = $this->transferModel->find($id);
        if (!$transfer) {
            return $this->json(['success' => false, 'message' => 'Transfer not found']);
        }
        
        if ($transfer['status'] !== 'pending') {
            return $this->json(['success' => false, 'message' => 'Transfer already processed']);
        }
        
        try {
            $this->transferModel->beginTransaction();
            
            // Update transfer status
            $this->transferModel->update($id, [
                'status' => 'approved',
                'approved_by' => $this->user()['id'],
                'approved_date' => now()
            ]);
            
            // Complete the transfer
            $this->completeTransfer($transfer);
            
            $this->transferModel->commit();
            
            $student = $this->studentModel->find($transfer['student_id']);
            $this->logActivity('transfer_approved', "Approved transfer for student: {$student['first_name']} {$student['last_name']}");
            
            return $this->json(['success' => true, 'message' => 'Transfer approved successfully']);
            
        } catch (Exception $e) {
            $this->transferModel->rollback();
            Logger::error('Transfer approval failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to approve transfer']);
        }
    }
    
    /**
     * Reject transfer
     */
    public function reject($id)
    {
        $this->requirePermission('student_transfer_approve');
        
        $transfer = $this->transferModel->find($id);
        if (!$transfer) {
            return $this->json(['success' => false, 'message' => 'Transfer not found']);
        }
        
        if ($transfer['status'] !== 'pending') {
            return $this->json(['success' => false, 'message' => 'Transfer already processed']);
        }
        
        $reason = $this->input('reason', '');
        
        try {
            $this->transferModel->update($id, [
                'status' => 'rejected',
                'rejected_by' => $this->user()['id'],
                'rejected_date' => now(),
                'rejection_reason' => $reason
            ]);
            
            // Revert student status
            $this->studentModel->update($transfer['student_id'], ['status' => STATUS_ACTIVE]);
            
            $student = $this->studentModel->find($transfer['student_id']);
            $this->logActivity('transfer_rejected', "Rejected transfer for student: {$student['first_name']} {$student['last_name']}");
            
            return $this->json(['success' => true, 'message' => 'Transfer rejected']);
            
        } catch (Exception $e) {
            Logger::error('Transfer rejection failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to reject transfer']);
        }
    }
    
    /**
     * Generate transfer certificate
     */
    public function generateCertificate($id)
    {
        $transfer = $this->transferModel->find($id);
        if (!$transfer || $transfer['status'] !== 'approved') {
            $this->flash('error', 'Transfer not found or not approved');
            $this->redirect('/students/transfers');
        }
        
        $transfer['student'] = $this->studentModel->findWithDetails($transfer['student_id']);
        
        return $this->generatePDF('student/transfers/certificate', ['transfer' => $transfer], 'transfer_certificate_' . $transfer['transfer_number']);
    }
    
    /**
     * Generate transfer number
     */
    private function generateTransferNumber()
    {
        $year = date('Y');
        $lastTransfer = $this->transferModel->last('transfer_number LIKE :pattern', ['pattern' => "TRF{$year}%"]);
        
        if ($lastTransfer) {
            $lastNumber = (int) substr($lastTransfer['transfer_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "TRF{$year}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Update student for transfer
     */
    private function updateStudentForTransfer($studentId, $transferData)
    {
        $updateData = [];
        
        switch ($transferData['transfer_type']) {
            case 'external':
                $updateData['status'] = STATUS_TRANSFERRED;
                break;
            case 'course_change':
                $updateData['course_id'] = $transferData['destination_course'];
                break;
            case 'class_change':
                $updateData['class_id'] = $transferData['destination_class'];
                break;
            case 'internal':
                // Handle internal transfer logic
                break;
        }
        
        if (!empty($updateData)) {
            $this->studentModel->update($studentId, $updateData);
        }
    }
    
    /**
     * Complete transfer process
     */
    private function completeTransfer($transfer)
    {
        // Generate transfer certificate
        $this->generateTransferCertificate($transfer);
        
        // Update final student status
        if ($transfer['transfer_type'] === 'external') {
            $this->studentModel->update($transfer['student_id'], [
                'status' => STATUS_TRANSFERRED,
                'transfer_date' => $transfer['transfer_date']
            ]);
        }
    }
    
    /**
     * Send transfer notifications
     */
    private function sendTransferNotifications($student, $transferData)
    {
        // Send email to student
        if (!empty($student['email'])) {
            $this->sendMail($student['email'], 'Transfer Request Initiated', 'transfer_initiated', [
                'student' => $student,
                'transfer_number' => $transferData['transfer_number'],
                'transfer_type' => $transferData['transfer_type']
            ]);
        }
        
        // Send SMS
        if (!empty($student['phone'])) {
            $message = "Transfer request {$transferData['transfer_number']} has been initiated. You will be notified about the status.";
            $this->sendSMS($student['phone'], $message);
        }
    }
    
    /**
     * Get guardian's wards
     */
    private function getGuardianWards($guardianId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.* FROM students s 
                JOIN guardian_students gs ON s.id = gs.student_id 
                WHERE gs.guardian_id = :guardian_id AND s.status = 'active'
                ORDER BY s.first_name";
        
        return $db->fetchAll($sql, ['guardian_id' => $guardianId]);
    }
}