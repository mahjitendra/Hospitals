<?php

/**
 * Student Registration Controller
 * 
 * Handles student registration and enrollment
 */
class RegistrationController extends Controller
{
    private $registrationModel;
    private $studentModel;
    private $courseModel;
    private $classModel;
    private $sectionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->registrationModel = new Registration();
        $this->studentModel = new Student();
        $this->courseModel = new Course();
        $this->classModel = new ClassModel();
        $this->sectionModel = new Section();
    }
    
    /**
     * Display registrations list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $session = $this->input('session', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (student_name LIKE :search OR registration_number LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($session)) {
            $where .= ' AND academic_session = :session';
            $params['session'] = $session;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $registrations = $this->registrationModel->paginate($page, 25, $where, $params, 'registration_date DESC');
        $courses = $this->courseModel->all('course_name ASC');
        $sessions = $this->getAcademicSessions();
        
        $this->render('student/registrations/index', [
            'title' => 'Student Registration',
            'registrations' => $registrations,
            'courses' => $courses,
            'sessions' => $sessions,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'session' => $session,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show registration form
     */
    public function create()
    {
        $this->requirePermission('student_register');
        
        $courses = $this->courseModel->where('status = :status', ['status' => 'active'], 'course_name ASC');
        $classes = $this->classModel->all('class_name ASC');
        $sections = $this->sectionModel->all('section_name ASC');
        
        $this->render('student/registrations/create', [
            'title' => 'Register New Student',
            'courses' => $courses,
            'classes' => $classes,
            'sections' => $sections
        ]);
    }
    
    /**
     * Store registration
     */
    public function store()
    {
        $this->requirePermission('student_register');
        
        $data = $this->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'exists:sections,id',
            'academic_session' => 'required',
            'semester' => 'required|numeric|min:1|max:10',
            'roll_number' => 'required|unique:registrations',
            'registration_fee' => 'required|numeric|min:0'
        ]);
        
        try {
            $this->registrationModel->beginTransaction();
            
            // Generate registration number
            $data['registration_number'] = $this->generateRegistrationNumber();
            $data['registration_date'] = now();
            $data['registered_by'] = $this->user()['id'];
            $data['status'] = 'active';
            
            $registrationId = $this->registrationModel->create($data);
            
            // Update student with registration details
            $this->studentModel->update($data['student_id'], [
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'roll_number' => $data['roll_number'],
                'academic_session' => $data['academic_session'],
                'current_semester' => $data['semester']
            ]);
            
            // Create fee structure for student
            $this->createStudentFees($data['student_id'], $data['course_id'], $data['academic_session']);
            
            $this->registrationModel->commit();
            
            $student = $this->studentModel->find($data['student_id']);
            
            // Send registration confirmation
            $this->sendRegistrationConfirmation($student, $data);
            
            $this->logActivity('student_registered', "Registered student: {$student['first_name']} {$student['last_name']}", $data);
            $this->flash('success', 'Student registered successfully');
            
            $this->redirect('/students/registrations');
            
        } catch (Exception $e) {
            $this->registrationModel->rollback();
            Logger::error('Student registration failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to register student: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show registration details
     */
    public function show($id)
    {
        $registration = $this->registrationModel->find($id);
        if (!$registration) {
            $this->flash('error', 'Registration not found');
            $this->redirect('/students/registrations');
        }
        
        $registration['student'] = $this->studentModel->find($registration['student_id']);
        $registration['course'] = $this->courseModel->find($registration['course_id']);
        $registration['class'] = $this->classModel->find($registration['class_id']);
        $registration['section'] = $this->sectionModel->find($registration['section_id']);
        
        $this->render('student/registrations/show', [
            'title' => 'Registration Details',
            'registration' => $registration
        ]);
    }
    
    /**
     * Cancel registration
     */
    public function cancel($id)
    {
        $this->requirePermission('student_register');
        
        $registration = $this->registrationModel->find($id);
        if (!$registration) {
            return $this->json(['success' => false, 'message' => 'Registration not found']);
        }
        
        $reason = $this->input('reason', '');
        
        try {
            $this->registrationModel->update($id, [
                'status' => 'cancelled',
                'cancelled_by' => $this->user()['id'],
                'cancelled_date' => now(),
                'cancellation_reason' => $reason
            ]);
            
            // Update student status
            $this->studentModel->update($registration['student_id'], ['status' => STATUS_INACTIVE]);
            
            $this->logActivity('registration_cancelled', "Cancelled registration: {$registration['registration_number']}");
            
            return $this->json(['success' => true, 'message' => 'Registration cancelled']);
            
        } catch (Exception $e) {
            Logger::error('Registration cancellation failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to cancel registration']);
        }
    }
    
    /**
     * Generate registration number
     */
    private function generateRegistrationNumber()
    {
        $year = date('Y');
        $lastRegistration = $this->registrationModel->last('registration_number LIKE :pattern', ['pattern' => "REG{$year}%"]);
        
        if ($lastRegistration) {
            $lastNumber = (int) substr($lastRegistration['registration_number'], -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "REG{$year}" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get academic sessions
     */
    private function getAcademicSessions()
    {
        $currentYear = date('Y');
        $sessions = [];
        
        for ($i = -2; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $sessions[] = $year . '-' . ($year + 1);
        }
        
        return $sessions;
    }
    
    /**
     * Create student fees
     */
    private function createStudentFees($studentId, $courseId, $academicSession)
    {
        $feeStructureModel = new FeeStructure();
        $feeModel = new Fee();
        
        $feeStructures = $feeStructureModel->where('course_id = :course_id AND academic_session = :session', [
            'course_id' => $courseId,
            'session' => $academicSession
        ]);
        
        foreach ($feeStructures as $structure) {
            $feeModel->create([
                'student_id' => $studentId,
                'fee_structure_id' => $structure['id'],
                'amount' => $structure['amount'],
                'due_date' => $structure['due_date'],
                'status' => FEE_PENDING
            ]);
        }
    }
    
    /**
     * Send registration confirmation
     */
    private function sendRegistrationConfirmation($student, $registrationData)
    {
        $course = $this->courseModel->find($registrationData['course_id']);
        
        // Send email
        $this->sendMail($student['email'], 'Registration Confirmation', 'registration_confirmation', [
            'student' => $student,
            'registration_number' => $registrationData['registration_number'],
            'course' => $course,
            'academic_session' => $registrationData['academic_session']
        ]);
        
        // Send SMS
        $message = "Dear {$student['first_name']}, your registration for {$course['course_name']} is confirmed. Registration No: {$registrationData['registration_number']}";
        $this->sendSMS($student['phone'], $message);
    }
}