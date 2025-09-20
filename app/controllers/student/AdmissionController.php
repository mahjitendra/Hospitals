<?php

/**
 * Student Admission Controller
 * 
 * Handles student admission process
 */
class AdmissionController extends Controller
{
    private $admissionModel;
    private $studentModel;
    private $courseModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->admissionModel = new Admission();
        $this->studentModel = new Student();
        $this->courseModel = new Course();
        $this->userModel = new User();
    }
    
    /**
     * Display admissions list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (first_name LIKE :search OR last_name LIKE :search OR application_number LIKE :search OR email LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $admissions = $this->admissionModel->paginate($page, 25, $where, $params, 'application_date DESC');
        $courses = $this->courseModel->all('course_name ASC');
        
        $this->render('student/admissions/index', [
            'title' => 'Admission Management',
            'admissions' => $admissions,
            'courses' => $courses,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show admission application form
     */
    public function create()
    {
        $courses = $this->courseModel->where('status = :status', ['status' => 'active'], 'course_name ASC');
        
        $this->render('student/admissions/create', [
            'title' => 'New Admission Application',
            'courses' => $courses
        ], 'guest');
    }
    
    /**
     * Store admission application
     */
    public function store()
    {
        $data = $this->validate([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:admissions',
            'phone' => 'required|phone',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'course_id' => 'required|exists:courses,id',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'father_name' => 'required|min:2',
            'mother_name' => 'required|min:2',
            'guardian_phone' => 'required|phone',
            'previous_school' => 'required|min:2',
            'previous_marks' => 'required|numeric|min:0|max:100'
        ]);
        
        try {
            $this->admissionModel->beginTransaction();
            
            // Generate application number
            $data['application_number'] = $this->generateApplicationNumber();
            $data['application_date'] = now();
            $data['status'] = 'pending';
            
            // Handle document uploads
            if ($this->request->hasFile('documents')) {
                $documents = $this->upload('documents', 'admissions/documents', ['pdf', 'jpg', 'jpeg', 'png']);
                $data['documents'] = json_encode($documents);
            }
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'admissions/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
            }
            
            $admissionId = $this->admissionModel->create($data);
            
            $this->admissionModel->commit();
            
            // Send confirmation email
            $this->sendAdmissionConfirmation($data);
            
            $this->logActivity('admission_applied', "New admission application: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Admission application submitted successfully. Application Number: ' . $data['application_number']);
            
            $this->redirect('/admission/status/' . $data['application_number']);
            
        } catch (Exception $e) {
            $this->admissionModel->rollback();
            Logger::error('Admission application failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to submit application: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show admission details
     */
    public function show($id)
    {
        $admission = $this->admissionModel->find($id);
        if (!$admission) {
            $this->flash('error', 'Admission not found');
            $this->redirect('/admissions');
        }
        
        $admission['course'] = $this->courseModel->find($admission['course_id']);
        
        $this->render('student/admissions/show', [
            'title' => 'Admission Details',
            'admission' => $admission
        ]);
    }
    
    /**
     * Approve admission
     */
    public function approve($id)
    {
        $admission = $this->admissionModel->find($id);
        if (!$admission) {
            return $this->json(['success' => false, 'message' => 'Admission not found']);
        }
        
        if ($admission['status'] !== 'pending') {
            return $this->json(['success' => false, 'message' => 'Admission already processed']);
        }
        
        try {
            $this->admissionModel->beginTransaction();
            
            // Update admission status
            $this->admissionModel->update($id, [
                'status' => 'approved',
                'approved_by' => $this->user()['id'],
                'approved_date' => now()
            ]);
            
            // Create student record
            $studentData = $this->prepareStudentData($admission);
            $studentId = $this->studentModel->create($studentData);
            
            // Create user account
            $userId = $this->createStudentUser($admission);
            
            // Update student with user_id
            $this->studentModel->update($studentId, ['user_id' => $userId]);
            
            // Update admission with student_id
            $this->admissionModel->update($id, ['student_id' => $studentId]);
            
            $this->admissionModel->commit();
            
            // Send approval notification
            $this->sendApprovalNotification($admission, $studentData['student_id']);
            
            $this->logActivity('admission_approved', "Approved admission: {$admission['first_name']} {$admission['last_name']}");
            
            return $this->json(['success' => true, 'message' => 'Admission approved successfully']);
            
        } catch (Exception $e) {
            $this->admissionModel->rollback();
            Logger::error('Admission approval failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to approve admission']);
        }
    }
    
    /**
     * Reject admission
     */
    public function reject($id)
    {
        $admission = $this->admissionModel->find($id);
        if (!$admission) {
            return $this->json(['success' => false, 'message' => 'Admission not found']);
        }
        
        if ($admission['status'] !== 'pending') {
            return $this->json(['success' => false, 'message' => 'Admission already processed']);
        }
        
        $reason = $this->input('reason', '');
        
        try {
            $this->admissionModel->update($id, [
                'status' => 'rejected',
                'rejected_by' => $this->user()['id'],
                'rejected_date' => now(),
                'rejection_reason' => $reason
            ]);
            
            // Send rejection notification
            $this->sendRejectionNotification($admission, $reason);
            
            $this->logActivity('admission_rejected', "Rejected admission: {$admission['first_name']} {$admission['last_name']}");
            
            return $this->json(['success' => true, 'message' => 'Admission rejected']);
            
        } catch (Exception $e) {
            Logger::error('Admission rejection failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to reject admission']);
        }
    }
    
    /**
     * Check admission status
     */
    public function status($applicationNumber)
    {
        $admission = $this->admissionModel->findBy('application_number', $applicationNumber);
        
        if (!$admission) {
            $this->flash('error', 'Application not found');
            $this->redirect('/');
        }
        
        $this->render('student/admissions/status', [
            'title' => 'Admission Status',
            'admission' => $admission
        ], 'guest');
    }
    
    /**
     * Generate application number
     */
    private function generateApplicationNumber()
    {
        $year = date('Y');
        $lastAdmission = $this->admissionModel->last('application_number LIKE :pattern', ['pattern' => "ADM{$year}%"]);
        
        if ($lastAdmission) {
            $lastNumber = (int) substr($lastAdmission['application_number'], -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "ADM{$year}" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Prepare student data from admission
     */
    private function prepareStudentData($admission)
    {
        return [
            'student_id' => $this->generateStudentId(),
            'first_name' => $admission['first_name'],
            'last_name' => $admission['last_name'],
            'email' => $admission['email'],
            'phone' => $admission['phone'],
            'date_of_birth' => $admission['date_of_birth'],
            'gender' => $admission['gender'],
            'blood_group' => $admission['blood_group'] ?? null,
            'photo' => $admission['photo'],
            'course_id' => $admission['course_id'],
            'admission_date' => now(),
            'address' => $admission['address'],
            'city' => $admission['city'],
            'state' => $admission['state'],
            'pincode' => $admission['pincode'],
            'father_name' => $admission['father_name'],
            'mother_name' => $admission['mother_name'],
            'guardian_phone' => $admission['guardian_phone'],
            'previous_school' => $admission['previous_school'],
            'previous_marks' => $admission['previous_marks'],
            'status' => STATUS_ACTIVE
        ];
    }
    
    /**
     * Create student user account
     */
    private function createStudentUser($admission)
    {
        $userData = [
            'name' => $admission['first_name'] . ' ' . $admission['last_name'],
            'email' => $admission['email'],
            'password' => bcrypt($admission['date_of_birth']), // Default password
            'status' => STATUS_ACTIVE
        ];
        
        $userId = $this->userModel->create($userData);
        
        // Assign student role
        $auth = new Auth();
        $auth->assignRole($userId, ROLE_STUDENT);
        
        return $userId;
    }
    
    /**
     * Generate student ID
     */
    private function generateStudentId()
    {
        $year = date('Y');
        $lastStudent = $this->studentModel->last('student_id LIKE :pattern', ['pattern' => $year . '%']);
        
        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent['student_id'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Send admission confirmation
     */
    private function sendAdmissionConfirmation($data)
    {
        // Send email
        $this->sendMail($data['email'], 'Admission Application Received', 'admission_received', [
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'application_number' => $data['application_number'],
            'course' => $this->courseModel->find($data['course_id'])['course_name']
        ]);
        
        // Send SMS
        $message = "Dear {$data['first_name']}, your admission application {$data['application_number']} has been received. You will be notified about the status soon.";
        $this->sendSMS($data['phone'], $message);
    }
    
    /**
     * Send approval notification
     */
    private function sendApprovalNotification($admission, $studentId)
    {
        $course = $this->courseModel->find($admission['course_id']);
        
        // Send email
        $this->sendMail($admission['email'], 'Admission Approved', 'admission_approved', [
            'name' => $admission['first_name'] . ' ' . $admission['last_name'],
            'student_id' => $studentId,
            'course' => $course['course_name']
        ]);
        
        // Send SMS
        $message = "Congratulations! Your admission to {$course['course_name']} has been approved. Student ID: {$studentId}";
        $this->sendSMS($admission['phone'], $message);
    }
    
    /**
     * Send rejection notification
     */
    private function sendRejectionNotification($admission, $reason)
    {
        // Send email
        $this->sendMail($admission['email'], 'Admission Status Update', 'admission_rejected', [
            'name' => $admission['first_name'] . ' ' . $admission['last_name'],
            'reason' => $reason
        ]);
        
        // Send SMS
        $message = "We regret to inform you that your admission application has not been approved. Please check your email for details.";
        $this->sendSMS($admission['phone'], $message);
    }
}