<?php

/**
 * Student Management Controller
 * 
 * Handles student CRUD operations and management
 */
class StudentController extends Controller
{
    private $studentModel;
    private $userModel;
    private $courseModel;
    private $classModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->studentModel = new Student();
        $this->userModel = new User();
        $this->courseModel = new Course();
        $this->classModel = new ClassModel();
    }
    
    /**
     * Display students list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $class = $this->input('class', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (first_name LIKE :search OR last_name LIKE :search OR student_id LIKE :search OR email LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($class)) {
            $where .= ' AND class_id = :class';
            $params['class'] = $class;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $students = $this->studentModel->paginate($page, 25, $where, $params, 'first_name ASC');
        $courses = $this->courseModel->all('name ASC');
        $classes = $this->classModel->all('name ASC');
        
        $this->render('student/index', [
            'title' => 'Student Management',
            'students' => $students,
            'courses' => $courses,
            'classes' => $classes,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'class' => $class,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create student form
     */
    public function create()
    {
        $this->requirePermission('student_create');
        
        $courses = $this->courseModel->all('name ASC');
        $classes = $this->classModel->all('name ASC');
        
        $this->render('student/create', [
            'title' => 'Add New Student',
            'courses' => $courses,
            'classes' => $classes
        ]);
    }
    
    /**
     * Store new student
     */
    public function store()
    {
        $this->requirePermission('student_create');
        
        $data = $this->validate([
            'student_id' => 'required|unique:students',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'email|unique:students',
            'phone' => 'phone',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'course_id' => 'required|exists:courses,id',
            'class_id' => 'required|exists:classes,id',
            'admission_date' => 'required|date',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'father_name' => 'required|min:2',
            'mother_name' => 'required|min:2',
            'guardian_phone' => 'required|phone'
        ]);
        
        try {
            $this->studentModel->beginTransaction();
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'students/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
            }
            
            // Generate student ID if not provided
            if (empty($data['student_id'])) {
                $data['student_id'] = $this->generateStudentId();
            }
            
            // Set default status
            $data['status'] = STATUS_ACTIVE;
            
            // Create student
            $studentId = $this->studentModel->create($data);
            
            // Create user account if email provided
            if (!empty($data['email'])) {
                $this->createStudentUser($studentId, $data);
            }
            
            $this->studentModel->commit();
            
            $this->logActivity('student_created', "Created student: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Student added successfully');
            
            $this->redirect('/students');
            
        } catch (Exception $e) {
            $this->studentModel->rollback();
            Logger::error('Student creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to add student: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show student details
     */
    public function show($id)
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        // Get related data
        $student['course'] = $this->courseModel->find($student['course_id']);
        $student['class'] = $this->classModel->find($student['class_id']);
        $student['documents'] = $this->getStudentDocuments($id);
        $student['attendance'] = $this->getAttendanceStats($id);
        $student['fees'] = $this->getFeeStatus($id);
        $student['results'] = $this->getRecentResults($id);
        
        $this->render('student/show', [
            'title' => 'Student Profile',
            'student' => $student
        ]);
    }
    
    /**
     * Show edit student form
     */
    public function edit($id)
    {
        $this->requirePermission('student_edit');
        
        $student = $this->studentModel->find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $courses = $this->courseModel->all('name ASC');
        $classes = $this->classModel->all('name ASC');
        
        $this->render('student/edit', [
            'title' => 'Edit Student',
            'student' => $student,
            'courses' => $courses,
            'classes' => $classes
        ]);
    }
    
    /**
     * Update student
     */
    public function update($id)
    {
        $this->requirePermission('student_edit');
        
        $student = $this->studentModel->find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $data = $this->validate([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'email',
            'phone' => 'phone',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'course_id' => 'required|exists:courses,id',
            'class_id' => 'required|exists:classes,id',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'father_name' => 'required|min:2',
            'mother_name' => 'required|min:2',
            'guardian_phone' => 'required|phone',
            'status' => 'required|in:active,inactive,suspended,graduated,transferred,dropped'
        ]);
        
        try {
            $this->studentModel->beginTransaction();
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'students/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
                
                // Delete old photo
                if (!empty($student['photo'])) {
                    $oldPhotoPath = public_path('uploads/students/photos/' . $student['photo']);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
            }
            
            // Check email uniqueness (excluding current student)
            if (!empty($data['email'])) {
                $existingStudent = $this->studentModel->findBy('email', $data['email']);
                if ($existingStudent && $existingStudent['id'] != $id) {
                    throw new Exception('Email already exists');
                }
            }
            
            // Update student
            $this->studentModel->update($id, $data);
            
            $this->studentModel->commit();
            
            $this->logActivity('student_updated', "Updated student: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Student updated successfully');
            
            $this->redirect('/students');
            
        } catch (Exception $e) {
            $this->studentModel->rollback();
            Logger::error('Student update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update student: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Delete student
     */
    public function destroy($id)
    {
        $this->requirePermission('student_delete');
        
        $student = $this->studentModel->find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        try {
            $this->studentModel->beginTransaction();
            
            // Soft delete student
            $this->studentModel->softDelete($id);
            
            // Deactivate user account if exists
            if (!empty($student['user_id'])) {
                $this->userModel->update($student['user_id'], ['status' => STATUS_INACTIVE]);
            }
            
            $this->studentModel->commit();
            
            $this->logActivity('student_deleted', "Deleted student: {$student['first_name']} {$student['last_name']}", $student);
            $this->flash('success', 'Student deleted successfully');
            
        } catch (Exception $e) {
            $this->studentModel->rollback();
            Logger::error('Student deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete student');
        }
        
        $this->redirect('/students');
    }
    
    /**
     * Export students data
     */
    public function export()
    {
        $this->requirePermission('student_export');
        
        $format = $this->input('format', 'excel');
        $students = $this->studentModel->all();
        
        if ($format === 'excel') {
            return $this->exportExcel($students, 'students_' . date('Y-m-d'), [
                'Student ID', 'First Name', 'Last Name', 'Email', 'Phone', 
                'Course', 'Class', 'Status', 'Admission Date'
            ]);
        } elseif ($format === 'pdf') {
            return $this->generatePDF('student/export_pdf', ['students' => $students], 'students_' . date('Y-m-d'));
        }
        
        $this->flash('error', 'Invalid export format');
        $this->back();
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
     * Create user account for student
     */
    private function createStudentUser($studentId, $data)
    {
        $userData = [
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['date_of_birth']), // Default password
            'status' => STATUS_ACTIVE
        ];
        
        $userId = $this->userModel->create($userData);
        
        // Assign student role
        $auth = new Auth();
        $auth->assignRole($userId, ROLE_STUDENT);
        
        // Link user to student
        $this->studentModel->update($studentId, ['user_id' => $userId]);
        
        return $userId;
    }
    
    /**
     * Get student documents
     */
    private function getStudentDocuments($studentId)
    {
        $documentModel = new StudentDocument();
        return $documentModel->where('student_id = :student_id', ['student_id' => $studentId]);
    }
    
    /**
     * Get attendance statistics
     */
    private function getAttendanceStats($studentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                FROM student_attendance 
                WHERE student_id = :student_id 
                AND attendance_date >= :start_date";
        
        $result = $db->fetch($sql, [
            'student_id' => $studentId,
            'start_date' => date('Y-m-01') // Current month
        ]);
        
        if ($result['total_days'] > 0) {
            $result['percentage'] = round(($result['present_days'] / $result['total_days']) * 100, 2);
        } else {
            $result['percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get fee status
     */
    private function getFeeStatus($studentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    SUM(amount) as total_fees,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount
                FROM fees 
                WHERE student_id = :student_id";
        
        $result = $db->fetch($sql, ['student_id' => $studentId]);
        
        return [
            'total' => $result['total_fees'] ?? 0,
            'paid' => $result['paid_amount'] ?? 0,
            'pending' => $result['pending_amount'] ?? 0
        ];
    }
    
    /**
     * Get recent results
     */
    private function getRecentResults($studentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT r.*, e.exam_name, s.subject_name 
                FROM results r
                JOIN exams e ON r.exam_id = e.id
                JOIN subjects s ON r.subject_id = s.id
                WHERE r.student_id = :student_id 
                ORDER BY e.exam_date DESC 
                LIMIT 5";
        
        return $db->fetchAll($sql, ['student_id' => $studentId]);
    }
}