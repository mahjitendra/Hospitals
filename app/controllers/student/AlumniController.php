<?php

/**
 * Alumni Management Controller
 * 
 * Handles alumni operations and networking
 */
class AlumniController extends Controller
{
    private $alumniModel;
    private $studentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->alumniModel = new Alumni();
        $this->studentModel = new Student();
    }
    
    /**
     * Display alumni directory
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $year = $this->input('year', '');
        $profession = $this->input('profession', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (first_name LIKE :search OR last_name LIKE :search OR company LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($year)) {
            $where .= ' AND graduation_year = :year';
            $params['year'] = $year;
        }
        
        if (!empty($profession)) {
            $where .= ' AND profession LIKE :profession';
            $params['profession'] = "%{$profession}%";
        }
        
        $alumni = $this->alumniModel->paginate($page, 25, $where, $params, 'graduation_year DESC, first_name ASC');
        $courses = $this->getCourses();
        $graduationYears = $this->getGraduationYears();
        
        $this->render('student/alumni/index', [
            'title' => 'Alumni Directory',
            'alumni' => $alumni,
            'courses' => $courses,
            'graduationYears' => $graduationYears,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'year' => $year,
                'profession' => $profession
            ]
        ]);
    }
    
    /**
     * Show alumni profile
     */
    public function show($id)
    {
        $alumni = $this->alumniModel->find($id);
        if (!$alumni) {
            $this->flash('error', 'Alumni not found');
            $this->redirect('/alumni');
        }
        
        $alumni['course'] = $this->getCourseDetails($alumni['course_id']);
        $alumni['achievements'] = $this->getAlumniAchievements($id);
        $alumni['events'] = $this->getAlumniEvents($id);
        
        $this->render('student/alumni/show', [
            'title' => 'Alumni Profile',
            'alumni' => $alumni
        ]);
    }
    
    /**
     * Convert student to alumni
     */
    public function convertStudent($studentId)
    {
        $this->requirePermission('student_graduate');
        
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            return $this->json(['success' => false, 'message' => 'Student not found']);
        }
        
        if ($student['status'] !== STATUS_ACTIVE) {
            return $this->json(['success' => false, 'message' => 'Only active students can be converted to alumni']);
        }
        
        $graduationData = $this->validate([
            'graduation_date' => 'required|date',
            'graduation_year' => 'required|numeric',
            'final_grade' => 'required|in:A+,A,B+,B,C+,C,D,F',
            'final_percentage' => 'required|numeric|min:0|max:100',
            'achievements' => 'max:1000',
            'remarks' => 'max:500'
        ]);
        
        try {
            $this->alumniModel->beginTransaction();
            
            // Create alumni record
            $alumniData = array_merge($graduationData, [
                'student_id' => $studentId,
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'email' => $student['email'],
                'phone' => $student['phone'],
                'course_id' => $student['course_id'],
                'admission_year' => date('Y', strtotime($student['admission_date'])),
                'student_id_number' => $student['student_id']
            ]);
            
            $alumniId = $this->alumniModel->create($alumniData);
            
            // Update student status
            $this->studentModel->update($studentId, [
                'status' => STATUS_GRADUATED,
                'graduation_date' => $graduationData['graduation_date'],
                'alumni_id' => $alumniId
            ]);
            
            $this->alumniModel->commit();
            
            // Send graduation notifications
            $this->sendGraduationNotifications($student, $graduationData);
            
            $this->logActivity('student_graduated', "Converted student to alumni: {$student['first_name']} {$student['last_name']}", $graduationData);
            
            return $this->json(['success' => true, 'message' => 'Student successfully converted to alumni']);
            
        } catch (Exception $e) {
            $this->alumniModel->rollback();
            Logger::error('Alumni conversion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to convert student to alumni']);
        }
    }
    
    /**
     * Update alumni profile
     */
    public function updateProfile($id)
    {
        $alumni = $this->alumniModel->find($id);
        if (!$alumni) {
            return $this->json(['success' => false, 'message' => 'Alumni not found']);
        }
        
        // Check if current user can update this profile
        if (!$this->canUpdateAlumniProfile($id)) {
            return $this->json(['success' => false, 'message' => 'Access denied']);
        }
        
        $data = $this->validate([
            'current_company' => 'max:100',
            'current_position' => 'max:100',
            'profession' => 'max:100',
            'work_experience' => 'numeric|min:0',
            'current_salary' => 'numeric|min:0',
            'linkedin_profile' => 'url',
            'achievements' => 'max:1000',
            'address' => 'max:255',
            'city' => 'max:50',
            'state' => 'max:50',
            'country' => 'max:50'
        ]);
        
        try {
            $this->alumniModel->update($id, $data);
            
            $this->logActivity('alumni_profile_updated', "Updated alumni profile: {$alumni['first_name']} {$alumni['last_name']}", $data);
            
            return $this->json(['success' => true, 'message' => 'Profile updated successfully']);
            
        } catch (Exception $e) {
            Logger::error('Alumni profile update failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to update profile']);
        }
    }
    
    /**
     * Get alumni statistics
     */
    public function statistics()
    {
        $stats = [
            'total_alumni' => $this->alumniModel->count(),
            'by_year' => $this->getAlumniByYear(),
            'by_course' => $this->getAlumniByCourse(),
            'by_profession' => $this->getAlumniByProfession(),
            'employment_rate' => $this->getEmploymentRate(),
            'salary_stats' => $this->getSalaryStatistics()
        ];
        
        $this->render('student/alumni/statistics', [
            'title' => 'Alumni Statistics',
            'stats' => $stats
        ]);
    }
    
    /**
     * Send graduation notifications
     */
    private function sendGraduationNotifications($student, $graduationData)
    {
        // Send congratulations email
        if (!empty($student['email'])) {
            $this->sendMail($student['email'], 'Congratulations on Your Graduation!', 'graduation_congratulations', [
                'student' => $student,
                'graduation_date' => $graduationData['graduation_date'],
                'final_grade' => $graduationData['final_grade']
            ]);
        }
        
        // Send SMS
        if (!empty($student['phone'])) {
            $message = "Congratulations {$student['first_name']}! You have successfully graduated. Welcome to our alumni network!";
            $this->sendSMS($student['phone'], $message);
        }
    }
    
    /**
     * Check if user can update alumni profile
     */
    private function canUpdateAlumniProfile($alumniId)
    {
        $user = $this->user();
        
        // Admin can update any profile
        if ($this->hasRole(ROLE_ADMIN)) {
            return true;
        }
        
        // Alumni can update their own profile
        $alumni = $this->alumniModel->findByUserId($user['id']);
        return $alumni && $alumni['id'] == $alumniId;
    }
    
    /**
     * Get courses
     */
    private function getCourses()
    {
        $courseModel = new Course();
        return $courseModel->all('course_name ASC');
    }
    
    /**
     * Get graduation years
     */
    private function getGraduationYears()
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT graduation_year FROM alumni ORDER BY graduation_year DESC";
        $result = $db->fetchAll($sql);
        return array_column($result, 'graduation_year');
    }
    
    /**
     * Get alumni achievements
     */
    private function getAlumniAchievements($alumniId)
    {
        $achievementModel = new AlumniAchievement();
        return $achievementModel->where('alumni_id = :alumni_id', ['alumni_id' => $alumniId], 'achievement_date DESC');
    }
    
    /**
     * Get alumni events
     */
    private function getAlumniEvents($alumniId)
    {
        $eventModel = new AlumniEvent();
        return $eventModel->getAlumniEvents($alumniId);
    }
}