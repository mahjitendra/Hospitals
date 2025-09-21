<?php

/**
 * Semester Model
 * 
 * Handles semester data operations
 */
class Semester extends Model
{
    protected $table = 'semesters';
    protected $fillable = [
        'course_id', 'semester_number', 'semester_name', 'academic_session',
        'start_date', 'end_date', 'exam_start_date', 'exam_end_date',
        'result_declaration_date', 'actual_start_date', 'actual_end_date',
        'description', 'status'
    ];
    
    /**
     * Get semesters by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id', ['course_id' => $courseId], 'semester_number ASC');
    }
    
    /**
     * Get current semester
     */
    public function getCurrentSemester($courseId = null)
    {
        $where = 'status = :status AND start_date <= :today AND end_date >= :today';
        $params = ['status' => 'ongoing', 'today' => today()];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        return $this->first($where, $params);
    }
    
    /**
     * Get upcoming semesters
     */
    public function getUpcomingSemesters($courseId = null)
    {
        $where = 'start_date > :today AND status = :status';
        $params = ['today' => today(), 'status' => 'upcoming'];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        return $this->where($where, $params, 'start_date ASC');
    }
    
    /**
     * Get semester with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT s.*, c.course_name, c.course_code
                FROM {$this->table} s
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE s.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get semester subjects
     */
    public function getSubjects($semesterId)
    {
        $semester = $this->find($semesterId);
        if (!$semester) {
            return [];
        }
        
        $subjectModel = new Subject();
        return $subjectModel->where(
            'course_id = :course_id AND semester = :semester',
            ['course_id' => $semester['course_id'], 'semester' => $semester['semester_number']],
            'subject_name ASC'
        );
    }
    
    /**
     * Get semester students
     */
    public function getStudents($semesterId)
    {
        $semester = $this->find($semesterId);
        if (!$semester) {
            return [];
        }
        
        $studentModel = new Student();
        return $studentModel->where(
            'course_id = :course_id AND current_semester = :semester AND academic_session = :session',
            [
                'course_id' => $semester['course_id'],
                'semester' => $semester['semester_number'],
                'session' => $semester['academic_session']
            ],
            'roll_number ASC'
        );
    }
    
    /**
     * Get semester exams
     */
    public function getExams($semesterId)
    {
        $semester = $this->find($semesterId);
        if (!$semester) {
            return [];
        }
        
        $examModel = new Exam();
        return $examModel->where(
            'course_id = :course_id AND semester = :semester AND academic_session = :session',
            [
                'course_id' => $semester['course_id'],
                'semester' => $semester['semester_number'],
                'session' => $semester['academic_session']
            ],
            'exam_date ASC'
        );
    }
    
    /**
     * Get semester statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_semesters,
                    SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming,
                    SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    COUNT(DISTINCT course_id) as courses_covered,
                    COUNT(DISTINCT academic_session) as academic_sessions
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Check if semester can be started
     */
    public function canStart($semesterId)
    {
        $semester = $this->find($semesterId);
        if (!$semester) {
            return false;
        }
        
        return $semester['status'] === 'upcoming' && $semester['start_date'] <= today();
    }
    
    /**
     * Check if semester can be completed
     */
    public function canComplete($semesterId)
    {
        $semester = $this->find($semesterId);
        if (!$semester) {
            return false;
        }
        
        return $semester['status'] === 'ongoing' && $semester['end_date'] <= today();
    }
    
    /**
     * Get semester performance
     */
    public function getPerformance($semesterId)
    {
        $semester = $this->find($semesterId);
        if (!$semester) {
            return [];
        }
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(DISTINCT r.student_id) as total_students,
                    AVG(r.marks_obtained) as average_marks,
                    SUM(CASE WHEN r.grade IN ('A+', 'A') THEN 1 ELSE 0 END) as excellent_performers,
                    SUM(CASE WHEN r.grade = 'F' THEN 1 ELSE 0 END) as failed_students
                FROM results r
                JOIN exams e ON r.exam_id = e.id
                WHERE e.course_id = :course_id 
                AND e.semester = :semester 
                AND e.academic_session = :session";
        
        $result = $db->fetch($sql, [
            'course_id' => $semester['course_id'],
            'semester' => $semester['semester_number'],
            'session' => $semester['academic_session']
        ]);
        
        if ($result['total_students'] > 0) {
            $result['excellence_rate'] = round(($result['excellent_performers'] / $result['total_students']) * 100, 2);
            $result['failure_rate'] = round(($result['failed_students'] / $result['total_students']) * 100, 2);
        } else {
            $result['excellence_rate'] = 0;
            $result['failure_rate'] = 0;
        }
        
        return $result;
    }
}