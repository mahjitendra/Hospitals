<?php

/**
 * Guardian Management Controller
 * 
 * Handles guardian-specific operations (extends parent functionality)
 */
class GuardianController extends Controller
{
    private $guardianModel;
    private $studentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->guardianModel = new Guardian();
        $this->studentModel = new Student();
    }
    
    /**
     * Display guardians list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        $guardians = $this->guardianModel->paginate($page, 25, $where, $params, 'first_name ASC');
        
        $this->render('student/guardians/index', [
            'title' => 'Guardian Management',
            'guardians' => $guardians,
            'search' => $search
        ]);
    }
    
    /**
     * Show guardian dashboard
     */
    public function dashboard()
    {
        $user = $this->user();
        $guardian = $this->guardianModel->findByUserId($user['id']);
        
        if (!$guardian) {
            $this->flash('error', 'Guardian profile not found');
            $this->redirect('/dashboard');
        }
        
        $wards = $this->getGuardianWards($guardian['id']);
        
        $data = [
            'guardian' => $guardian,
            'wards' => $wards,
            'notifications' => $this->getNotifications($guardian['id'], 'guardian')
        ];
        
        // Get data for each ward
        foreach ($data['wards'] as &$ward) {
            $ward['attendance'] = $this->getStudentAttendanceStats($ward['id']);
            $ward['fees'] = $this->getStudentFeeStatus($ward['id']);
            $ward['recent_results'] = $this->getStudentRecentResults($ward['id']);
        }
        
        $this->render('student/guardians/dashboard', [
            'title' => 'Guardian Dashboard',
            'data' => $data
        ]);
    }
    
    /**
     * Link guardian to student
     */
    public function linkStudent()
    {
        $data = $this->validate([
            'guardian_id' => 'required|exists:guardians,id',
            'student_id' => 'required|exists:students,id',
            'relation' => 'required|in:guardian,uncle,aunt,grandfather,grandmother,other',
            'is_primary' => 'boolean',
            'can_pickup' => 'boolean',
            'emergency_contact' => 'boolean'
        ]);
        
        try {
            // Check if link already exists
            $existing = $this->guardianModel->exists(
                'guardian_id = :guardian_id AND student_id = :student_id',
                ['guardian_id' => $data['guardian_id'], 'student_id' => $data['student_id']]
            );
            
            if ($existing) {
                throw new Exception('Guardian is already linked to this student');
            }
            
            $linkId = $this->guardianModel->linkToStudent($data);
            
            $guardian = $this->guardianModel->find($data['guardian_id']);
            $student = $this->studentModel->find($data['student_id']);
            
            $this->logActivity('guardian_linked', "Linked guardian: {$guardian['first_name']} {$guardian['last_name']} to student: {$student['first_name']} {$student['last_name']}", $data);
            
            return $this->json(['success' => true, 'message' => 'Guardian linked successfully']);
            
        } catch (Exception $e) {
            Logger::error('Guardian linking failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Unlink guardian from student
     */
    public function unlinkStudent()
    {
        $guardianId = $this->input('guardian_id');
        $studentId = $this->input('student_id');
        
        try {
            $result = $this->guardianModel->unlinkFromStudent($guardianId, $studentId);
            
            if ($result) {
                $this->logActivity('guardian_unlinked', "Unlinked guardian from student");
                return $this->json(['success' => true, 'message' => 'Guardian unlinked successfully']);
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to unlink guardian']);
            }
            
        } catch (Exception $e) {
            Logger::error('Guardian unlinking failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to unlink guardian']);
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
    
    /**
     * Get notifications for guardian
     */
    private function getNotifications($guardianId, $type, $limit = 5)
    {
        $notificationModel = new Notification();
        return $notificationModel->where(
            'recipient_id = :recipient_id AND recipient_type = :type AND is_read = :read',
            ['recipient_id' => $guardianId, 'type' => $type, 'read' => 0],
            'created_at DESC',
            $limit
        );
    }
    
    /**
     * Get student attendance stats
     */
    private function getStudentAttendanceStats($studentId)
    {
        $attendanceModel = new StudentAttendance();
        return $attendanceModel->getAttendanceStats($studentId);
    }
    
    /**
     * Get student fee status
     */
    private function getStudentFeeStatus($studentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    SUM(amount) as total_fees,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount
                FROM fees 
                WHERE student_id = :student_id";
        
        return $db->fetch($sql, ['student_id' => $studentId]);
    }
    
    /**
     * Get student recent results
     */
    private function getStudentRecentResults($studentId)
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