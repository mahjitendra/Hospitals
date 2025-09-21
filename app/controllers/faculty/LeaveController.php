<?php

/**
 * Leave Management Controller
 * 
 * Handles leave applications and approvals
 */
class LeaveController extends Controller
{
    private $leaveModel;
    private $facultyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->leaveModel = new Leave();
        $this->facultyModel = new Faculty();
    }
    
    /**
     * Display leave applications
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $status = $this->input('status', '');
        $type = $this->input('type', '');
        $faculty = $this->input('faculty', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        if (!empty($type)) {
            $where .= ' AND leave_type = :type';
            $params['type'] = $type;
        }
        
        if (!empty($faculty)) {
            $where .= ' AND faculty_id = :faculty';
            $params['faculty'] = $faculty;
        }
        
        $leaves = $this->leaveModel->paginate($page, 25, $where, $params, 'application_date DESC');
        $facultyList = $this->facultyModel->all('first_name ASC');
        
        $this->render('faculty/leave/index', [
            'title' => 'Leave Management',
            'leaves' => $leaves,
            'facultyList' => $facultyList,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'faculty' => $faculty
            ]
        ]);
    }
    
    /**
     * Show create leave form
     */
    public function create()
    {
        $user = $this->user();
        $faculty = $this->facultyModel->findByUserId($user['id']);
        
        if (!$faculty) {
            $this->flash('error', 'Faculty profile not found');
            $this->redirect('/dashboard');
        }
        
        $leaveBalance = $this->leaveModel->getLeaveBalance($faculty['id']);
        
        $this->render('faculty/leave/create', [
            'title' => 'Apply for Leave',
            'faculty' => $faculty,
            'leaveBalance' => $leaveBalance
        ]);
    }
    
    /**
     * Store leave application
     */
    public function store()
    {
        $data = $this->validate([
            'leave_type' => 'required|in:sick,casual,earned,maternity,paternity,emergency',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|min:10',
            'contact_during_leave' => 'phone',
            'medical_certificate' => 'required_if:leave_type,sick'
        ]);
        
        // Validate dates
        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $this->flash('error', 'End date must be after start date');
            $this->back();
            return;
        }
        
        $user = $this->user();
        $faculty = $this->facultyModel->findByUserId($user['id']);
        
        if (!$faculty) {
            $this->flash('error', 'Faculty profile not found');
            $this->redirect('/dashboard');
        }
        
        try {
            $this->leaveModel->beginTransaction();
            
            // Calculate leave days
            $leaveDays = $this->calculateLeaveDays($data['start_date'], $data['end_date']);
            
            // Check leave balance
            $leaveBalance = $this->leaveModel->getLeaveBalance($faculty['id']);
            if (!$this->hasLeaveBalance($data['leave_type'], $leaveDays, $leaveBalance)) {
                throw new Exception('Insufficient leave balance');
            }
            
            // Handle medical certificate upload
            if ($this->request->hasFile('medical_certificate')) {
                $certificate = $this->upload('medical_certificate', 'leave/certificates', ['pdf', 'jpg', 'jpeg', 'png']);
                $data['medical_certificate'] = $certificate['filename'];
            }
            
            $data['faculty_id'] = $faculty['id'];
            $data['leave_days'] = $leaveDays;
            $data['application_date'] = now();
            $data['status'] = LEAVE_PENDING;
            
            $leaveId = $this->leaveModel->create($data);
            
            $this->leaveModel->commit();
            
            // Send notification to HOD/Admin
            $this->sendLeaveNotification($faculty, $data);
            
            $this->logActivity('leave_applied', "Applied for leave: {$data['leave_type']} from {$data['start_date']} to {$data['end_date']}", $data);
            $this->flash('success', 'Leave application submitted successfully');
            
            $this->redirect('/faculty/leave');
            
        } catch (Exception $e) {
            $this->leaveModel->rollback();
            Logger::error('Leave application failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to submit leave application: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show leave details
     */
    public function show($id)
    {
        $leave = $this->leaveModel->find($id);
        if (!$leave) {
            $this->flash('error', 'Leave application not found');
            $this->redirect('/faculty/leave');
        }
        
        $leave['faculty'] = $this->facultyModel->find($leave['faculty_id']);
        
        if ($leave['approved_by']) {
            $leave['approved_by_user'] = $this->userModel->find($leave['approved_by']);
        }
        
        $this->render('faculty/leave/show', [
            'title' => 'Leave Application Details',
            'leave' => $leave
        ]);
    }
    
    /**
     * Approve leave
     */
    public function approve($id)
    {
        $this->requirePermission('leave_approve');
        
        $leave = $this->leaveModel->find($id);
        if (!$leave) {
            return $this->json(['success' => false, 'message' => 'Leave application not found']);
        }
        
        if ($leave['status'] !== LEAVE_PENDING) {
            return $this->json(['success' => false, 'message' => 'Leave already processed']);
        }
        
        $remarks = $this->input('remarks', '');
        
        try {
            $this->leaveModel->update($id, [
                'status' => LEAVE_APPROVED,
                'approved_by' => $this->user()['id'],
                'approved_date' => now(),
                'approval_remarks' => $remarks
            ]);
            
            // Update leave balance
            $this->updateLeaveBalance($leave);
            
            // Send approval notification
            $faculty = $this->facultyModel->find($leave['faculty_id']);
            $this->sendLeaveApprovalNotification($faculty, $leave);
            
            $this->logActivity('leave_approved', "Approved leave application for: {$faculty['first_name']} {$faculty['last_name']}");
            
            return $this->json(['success' => true, 'message' => 'Leave approved successfully']);
            
        } catch (Exception $e) {
            Logger::error('Leave approval failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to approve leave']);
        }
    }
    
    /**
     * Reject leave
     */
    public function reject($id)
    {
        $this->requirePermission('leave_approve');
        
        $leave = $this->leaveModel->find($id);
        if (!$leave) {
            return $this->json(['success' => false, 'message' => 'Leave application not found']);
        }
        
        if ($leave['status'] !== LEAVE_PENDING) {
            return $this->json(['success' => false, 'message' => 'Leave already processed']);
        }
        
        $reason = $this->input('reason', '');
        
        try {
            $this->leaveModel->update($id, [
                'status' => LEAVE_REJECTED,
                'rejected_by' => $this->user()['id'],
                'rejected_date' => now(),
                'rejection_reason' => $reason
            ]);
            
            // Send rejection notification
            $faculty = $this->facultyModel->find($leave['faculty_id']);
            $this->sendLeaveRejectionNotification($faculty, $leave, $reason);
            
            $this->logActivity('leave_rejected', "Rejected leave application for: {$faculty['first_name']} {$faculty['last_name']}");
            
            return $this->json(['success' => true, 'message' => 'Leave rejected']);
            
        } catch (Exception $e) {
            Logger::error('Leave rejection failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to reject leave']);
        }
    }
    
    /**
     * Calculate leave days
     */
    private function calculateLeaveDays($startDate, $endDate)
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = $start->diff($end);
        
        return $interval->days + 1; // Include both start and end dates
    }
    
    /**
     * Check leave balance
     */
    private function hasLeaveBalance($leaveType, $requestedDays, $leaveBalance)
    {
        $available = $leaveBalance[$leaveType] ?? 0;
        return $available >= $requestedDays;
    }
    
    /**
     * Update leave balance
     */
    private function updateLeaveBalance($leave)
    {
        $this->leaveModel->deductLeaveBalance(
            $leave['faculty_id'],
            $leave['leave_type'],
            $leave['leave_days']
        );
    }
    
    /**
     * Send leave notification
     */
    private function sendLeaveNotification($faculty, $leaveData)
    {
        // Get HOD or admin to notify
        $department = $this->departmentModel->find($faculty['department_id']);
        $hod = null;
        
        if ($department && $department['hod_id']) {
            $hod = $this->facultyModel->find($department['hod_id']);
        }
        
        $notificationData = [
            'title' => 'New Leave Application',
            'message' => "Leave application from {$faculty['first_name']} {$faculty['last_name']} for {$leaveData['leave_type']} leave",
            'type' => 'info',
            'sender_id' => $faculty['user_id'],
            'delivery_method' => 'system,email'
        ];
        
        if ($hod) {
            $notificationData['recipient_id'] = $hod['user_id'];
            $notificationData['recipient_type'] = 'faculty';
        } else {
            // Send to admin
            $adminUsers = $this->userModel->getUsersByRole(ROLE_ADMIN);
            foreach ($adminUsers as $admin) {
                $notificationData['recipient_id'] = $admin['id'];
                $notificationData['recipient_type'] = 'user';
                $this->notify([$admin], $notificationData['message'], 'info', $notificationData);
            }
        }
    }
}