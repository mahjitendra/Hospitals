<?php

/**
 * Student Profile Controller
 * 
 * Handles student profile management
 */
class ProfileController extends Controller
{
    private $studentModel;
    private $userModel;
    private $documentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->studentModel = new Student();
        $this->userModel = new User();
        $this->documentModel = new StudentDocument();
    }
    
    /**
     * Show student profile
     */
    public function show($id = null)
    {
        // If no ID provided, show current user's profile
        if (!$id) {
            $user = $this->user();
            $student = $this->studentModel->findByUserId($user['id']);
            
            if (!$student) {
                $this->flash('error', 'Student profile not found');
                $this->redirect('/dashboard');
            }
            
            $id = $student['id'];
        } else {
            // Check permission to view other profiles
            if (!$this->hasPermission('student_view')) {
                $this->flash('error', 'Access denied');
                $this->redirect('/dashboard');
            }
        }
        
        $student = $this->studentModel->findWithDetails($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        // Get additional data
        $student['documents'] = $this->documentModel->where('student_id = :student_id', ['student_id' => $id]);
        $student['attendance'] = $this->getAttendanceStats($id);
        $student['fees'] = $this->getFeeStatus($id);
        $student['results'] = $this->getRecentResults($id);
        $student['timetable'] = $this->getTimetable($student['class_id'], $student['section_id']);
        
        $this->render('student/profile/show', [
            'title' => 'Student Profile',
            'student' => $student
        ]);
    }
    
    /**
     * Show edit profile form
     */
    public function edit($id = null)
    {
        // If no ID provided, edit current user's profile
        if (!$id) {
            $user = $this->user();
            $student = $this->studentModel->findByUserId($user['id']);
            
            if (!$student) {
                $this->flash('error', 'Student profile not found');
                $this->redirect('/dashboard');
            }
            
            $id = $student['id'];
        } else {
            // Check permission to edit other profiles
            if (!$this->hasPermission('student_edit')) {
                $this->flash('error', 'Access denied');
                $this->redirect('/dashboard');
            }
        }
        
        $student = $this->studentModel->find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $this->render('student/profile/edit', [
            'title' => 'Edit Profile',
            'student' => $student
        ]);
    }
    
    /**
     * Update profile
     */
    public function update($id = null)
    {
        // If no ID provided, update current user's profile
        if (!$id) {
            $user = $this->user();
            $student = $this->studentModel->findByUserId($user['id']);
            
            if (!$student) {
                $this->flash('error', 'Student profile not found');
                $this->redirect('/dashboard');
            }
            
            $id = $student['id'];
        } else {
            // Check permission to edit other profiles
            if (!$this->hasPermission('student_edit')) {
                $this->flash('error', 'Access denied');
                $this->redirect('/dashboard');
            }
        }
        
        $student = $this->studentModel->find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $data = $this->validate([
            'phone' => 'phone',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'guardian_phone' => 'required|phone',
            'emergency_contact' => 'required|phone',
            'medical_conditions' => 'max:500',
            'allergies' => 'max:500'
        ]);
        
        try {
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
            
            $this->studentModel->update($id, $data);
            
            $this->logActivity('profile_updated', "Updated student profile: {$student['first_name']} {$student['last_name']}", $data);
            $this->flash('success', 'Profile updated successfully');
            
            $this->redirect('/profile');
            
        } catch (Exception $e) {
            Logger::error('Profile update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update profile: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Change password
     */
    public function changePassword()
    {
        $data = $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|strong_password|confirmed'
        ]);
        
        try {
            $auth = new Auth();
            $auth->changePassword($data['current_password'], $data['new_password']);
            
            $this->logActivity('password_changed', 'Changed password');
            
            return $this->json(['success' => true, 'message' => 'Password changed successfully']);
            
        } catch (Exception $e) {
            Logger::error('Password change failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
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
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
                FROM student_attendance 
                WHERE student_id = :student_id 
                AND attendance_date >= :start_date";
        
        $result = $db->fetch($sql, [
            'student_id' => $studentId,
            'start_date' => date('Y-m-01')
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
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount
                FROM fees 
                WHERE student_id = :student_id";
        
        $result = $db->fetch($sql, ['student_id' => $studentId]);
        
        return [
            'total' => $result['total_fees'] ?? 0,
            'paid' => $result['paid_amount'] ?? 0,
            'pending' => $result['pending_amount'] ?? 0,
            'overdue' => $result['overdue_amount'] ?? 0
        ];
    }
    
    /**
     * Get recent results
     */
    private function getRecentResults($studentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT r.*, e.exam_name, s.subject_name, s.subject_code 
                FROM results r
                JOIN exams e ON r.exam_id = e.id
                JOIN subjects s ON r.subject_id = s.id
                WHERE r.student_id = :student_id 
                ORDER BY e.exam_date DESC 
                LIMIT 10";
        
        return $db->fetchAll($sql, ['student_id' => $studentId]);
    }
    
    /**
     * Get timetable
     */
    private function getTimetable($classId, $sectionId = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($sectionId) {
            $where .= ' AND section_id = :section_id';
            $params['section_id'] = $sectionId;
        }
        
        $db = Database::getInstance();
        $sql = "SELECT t.*, s.subject_name, s.subject_code, f.first_name, f.last_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN faculty f ON t.faculty_id = f.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $db->fetchAll($sql, $params);
    }
}