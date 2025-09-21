<?php

/**
 * Academic Session Model
 * 
 * Handles academic session data operations
 */
class AcademicSession extends Model
{
    protected $table = 'academic_sessions';
    protected $fillable = [
        'session_name', 'start_date', 'end_date', 'description', 'is_current',
        'status', 'completed_date'
    ];
    
    /**
     * Get current session
     */
    public function getCurrentSession()
    {
        return $this->first('is_current = :current', ['current' => 1]);
    }
    
    /**
     * Get active sessions
     */
    public function getActiveSessions()
    {
        return $this->where('status = :status', ['status' => 'active'], 'start_date DESC');
    }
    
    /**
     * Get session by name
     */
    public function findByName($sessionName)
    {
        return $this->findBy('session_name', $sessionName);
    }
    
    /**
     * Get upcoming sessions
     */
    public function getUpcomingSessions()
    {
        return $this->where('start_date > :today AND status = :status', [
            'today' => today(),
            'status' => 'active'
        ], 'start_date ASC');
    }
    
    /**
     * Get completed sessions
     */
    public function getCompletedSessions()
    {
        return $this->where('status = :status', ['status' => 'completed'], 'end_date DESC');
    }
    
    /**
     * Check if session is active
     */
    public function isSessionActive($sessionId)
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return false;
        }
        
        $today = today();
        return $session['status'] === 'active' && 
               $session['start_date'] <= $today && 
               $session['end_date'] >= $today;
    }
    
    /**
     * Get session duration
     */
    public function getSessionDuration($sessionId)
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return 0;
        }
        
        $start = new DateTime($session['start_date']);
        $end = new DateTime($session['end_date']);
        $interval = $start->diff($end);
        
        return $interval->days;
    }
    
    /**
     * Get session progress
     */
    public function getSessionProgress($sessionId)
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return 0;
        }
        
        $start = new DateTime($session['start_date']);
        $end = new DateTime($session['end_date']);
        $today = new DateTime();
        
        if ($today < $start) {
            return 0; // Not started
        }
        
        if ($today > $end) {
            return 100; // Completed
        }
        
        $totalDays = $start->diff($end)->days;
        $elapsedDays = $start->diff($today)->days;
        
        return round(($elapsedDays / $totalDays) * 100, 2);
    }
    
    /**
     * Get session statistics
     */
    public function getSessionStats($sessionId)
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return [];
        }
        
        $db = Database::getInstance();
        
        // Get student enrollment
        $studentSql = "SELECT COUNT(*) as count FROM students WHERE academic_session = :session";
        $studentResult = $db->fetch($studentSql, ['session' => $session['session_name']]);
        
        // Get courses offered
        $courseSql = "SELECT COUNT(DISTINCT course_id) as count FROM students WHERE academic_session = :session";
        $courseResult = $db->fetch($courseSql, ['session' => $session['session_name']]);
        
        // Get exams conducted
        $examSql = "SELECT COUNT(*) as count FROM exams WHERE academic_session = :session";
        $examResult = $db->fetch($examSql, ['session' => $session['session_name']]);
        
        return [
            'total_students' => $studentResult['count'] ?? 0,
            'courses_offered' => $courseResult['count'] ?? 0,
            'exams_conducted' => $examResult['count'] ?? 0,
            'duration_days' => $this->getSessionDuration($sessionId),
            'progress_percentage' => $this->getSessionProgress($sessionId)
        ];
    }
    
    /**
     * Get overlapping sessions
     */
    public function getOverlappingSessions($startDate, $endDate, $excludeId = null)
    {
        $where = '(start_date <= :end_date AND end_date >= :start_date)';
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($excludeId) {
            $where .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Generate session name
     */
    public function generateSessionName($startYear)
    {
        $endYear = $startYear + 1;
        return "{$startYear}-{$endYear}";
    }
    
    /**
     * Archive old sessions
     */
    public function archiveOldSessions($years = 5)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$years} years"));
        
        return $this->db->query(
            "UPDATE {$this->table} SET status = 'archived' WHERE end_date < :cutoff_date AND status = 'completed'",
            ['cutoff_date' => $cutoffDate]
        );
    }
}