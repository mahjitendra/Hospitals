<?php

/**
 * Academic Session Controller
 * 
 * Handles academic session management
 */
class SessionController extends Controller
{
    private $sessionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->sessionModel = new AcademicSession();
    }
    
    /**
     * Display sessions list
     */
    public function index()
    {
        $sessions = $this->sessionModel->all('start_date DESC');
        $currentSession = $this->sessionModel->getCurrentSession();
        
        $this->render('academic/sessions/index', [
            'title' => 'Academic Sessions',
            'sessions' => $sessions,
            'currentSession' => $currentSession
        ]);
    }
    
    /**
     * Show create session form
     */
    public function create()
    {
        $this->requirePermission('session_create');
        
        $this->render('academic/sessions/create', [
            'title' => 'Create Academic Session'
        ]);
    }
    
    /**
     * Store new session
     */
    public function store()
    {
        $this->requirePermission('session_create');
        
        $data = $this->validate([
            'session_name' => 'required|min:2|max:20|unique:academic_sessions',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'description' => 'max:500',
            'is_current' => 'boolean',
            'status' => 'required|in:active,inactive,completed'
        ]);
        
        // Validate dates
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->flash('error', 'End date must be after start date');
            $this->back();
            return;
        }
        
        try {
            $this->sessionModel->beginTransaction();
            
            // If this is set as current session, update others
            if ($data['is_current']) {
                $this->sessionModel->update(null, ['is_current' => 0], '1=1');
            }
            
            $sessionId = $this->sessionModel->create($data);
            
            $this->sessionModel->commit();
            
            $this->logActivity('session_created', "Created academic session: {$data['session_name']}", $data);
            $this->flash('success', 'Academic session created successfully');
            
            $this->redirect('/academic/sessions');
            
        } catch (Exception $e) {
            $this->sessionModel->rollback();
            Logger::error('Session creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create session');
            $this->back();
        }
    }
    
    /**
     * Show session details
     */
    public function show($id)
    {
        $session = $this->sessionModel->find($id);
        if (!$session) {
            $this->flash('error', 'Session not found');
            $this->redirect('/academic/sessions');
        }
        
        $session['statistics'] = $this->getSessionStatistics($id);
        $session['courses'] = $this->getSessionCourses($id);
        $session['events'] = $this->getSessionEvents($id);
        
        $this->render('academic/sessions/show', [
            'title' => 'Session Details',
            'session' => $session
        ]);
    }
    
    /**
     * Set current session
     */
    public function setCurrent($id)
    {
        $this->requirePermission('session_manage');
        
        $session = $this->sessionModel->find($id);
        if (!$session) {
            return $this->json(['success' => false, 'message' => 'Session not found']);
        }
        
        try {
            $this->sessionModel->beginTransaction();
            
            // Remove current flag from all sessions
            $this->sessionModel->db->query("UPDATE academic_sessions SET is_current = 0");
            
            // Set this session as current
            $this->sessionModel->update($id, ['is_current' => 1]);
            
            $this->sessionModel->commit();
            
            $this->logActivity('current_session_changed', "Set current session to: {$session['session_name']}");
            
            return $this->json(['success' => true, 'message' => 'Current session updated successfully']);
            
        } catch (Exception $e) {
            $this->sessionModel->rollback();
            Logger::error('Session update failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to update current session']);
        }
    }
    
    /**
     * Complete session
     */
    public function complete($id)
    {
        $this->requirePermission('session_manage');
        
        $session = $this->sessionModel->find($id);
        if (!$session) {
            return $this->json(['success' => false, 'message' => 'Session not found']);
        }
        
        if ($session['status'] === 'completed') {
            return $this->json(['success' => false, 'message' => 'Session already completed']);
        }
        
        try {
            $this->sessionModel->beginTransaction();
            
            // Update session status
            $this->sessionModel->update($id, [
                'status' => 'completed',
                'completed_date' => now(),
                'is_current' => 0
            ]);
            
            // Process session completion tasks
            $this->processSessionCompletion($id);
            
            $this->sessionModel->commit();
            
            $this->logActivity('session_completed', "Completed academic session: {$session['session_name']}");
            
            return $this->json(['success' => true, 'message' => 'Session completed successfully']);
            
        } catch (Exception $e) {
            $this->sessionModel->rollback();
            Logger::error('Session completion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to complete session']);
        }
    }
    
    /**
     * Get session statistics
     */
    private function getSessionStatistics($sessionId)
    {
        $db = Database::getInstance();
        
        // Get student enrollment for this session
        $sql = "SELECT 
                    COUNT(*) as total_students,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_students,
                    COUNT(CASE WHEN status = 'graduated' THEN 1 END) as graduated_students
                FROM students 
                WHERE academic_session = (SELECT session_name FROM academic_sessions WHERE id = :session_id)";
        
        return $db->fetch($sql, ['session_id' => $sessionId]);
    }
    
    /**
     * Get session courses
     */
    private function getSessionCourses($sessionId)
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT c.* 
                FROM courses c
                JOIN students s ON c.id = s.course_id
                WHERE s.academic_session = (SELECT session_name FROM academic_sessions WHERE id = :session_id)
                ORDER BY c.course_name";
        
        return $db->fetchAll($sql, ['session_id' => $sessionId]);
    }
    
    /**
     * Get session events
     */
    private function getSessionEvents($sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        
        $eventModel = new Event();
        return $eventModel->where(
            'event_date BETWEEN :start_date AND :end_date',
            ['start_date' => $session['start_date'], 'end_date' => $session['end_date']],
            'event_date ASC'
        );
    }
    
    /**
     * Process session completion tasks
     */
    private function processSessionCompletion($sessionId)
    {
        // Archive session data
        $this->archiveSessionData($sessionId);
        
        // Generate completion reports
        $this->generateSessionReports($sessionId);
        
        // Update student statuses
        $this->updateStudentStatuses($sessionId);
    }
    
    /**
     * Archive session data
     */
    private function archiveSessionData($sessionId)
    {
        // Implementation for archiving session data
        // This could involve moving data to archive tables
    }
    
    /**
     * Generate session reports
     */
    private function generateSessionReports($sessionId)
    {
        // Generate comprehensive session completion reports
        // This could include performance reports, attendance summaries, etc.
    }
    
    /**
     * Update student statuses
     */
    private function updateStudentStatuses($sessionId)
    {
        // Update students who completed their course
        // Mark them as graduated or promote to next level
    }
}