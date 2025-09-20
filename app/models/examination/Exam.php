<?php

/**
 * Exam Model
 * 
 * Handles exam data operations
 */
class Exam extends Model
{
    protected $table = 'exams';
    protected $fillable = [
        'exam_code', 'exam_name', 'exam_type_id', 'course_id', 'class_id',
        'semester', 'academic_session', 'exam_date', 'start_time', 'end_time',
        'total_marks', 'passing_marks', 'instructions', 'syllabus_covered',
        'exam_center', 'invigilator_id', 'status', 'remarks'
    ];
    
    /**
     * Find exam by code
     */
    public function findByCode($examCode)
    {
        return $this->findBy('exam_code', $examCode);
    }
    
    /**
     * Get exams by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id', ['course_id' => $courseId], 'exam_date DESC');
    }
    
    /**
     * Get exams by class
     */
    public function getByClass($classId)
    {
        return $this->where('class_id = :class_id', ['class_id' => $classId], 'exam_date DESC');
    }
    
    /**
     * Get exams by type
     */
    public function getByType($examTypeId)
    {
        return $this->where('exam_type_id = :type_id', ['type_id' => $examTypeId], 'exam_date DESC');
    }
    
    /**
     * Get upcoming exams
     */
    public function getUpcoming($limit = 10)
    {
        return $this->where('exam_date >= :today AND status = :status', [
            'today' => today(),
            'status' => 'scheduled'
        ], 'exam_date ASC', $limit);
    }
    
    /**
     * Get ongoing exams
     */
    public function getOngoing()
    {
        $now = now();
        $today = today();
        
        return $this->where('exam_date = :today AND start_time <= :now AND end_time >= :now AND status = :status', [
            'today' => $today,
            'now' => $now,
            'status' => 'ongoing'
        ]);
    }
    
    /**
     * Get exam with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT e.*, 
                       et.type_name, et.description as type_description,
                       c.course_name, c.course_code,
                       cl.class_name,
                       f.first_name as invigilator_first_name, f.last_name as invigilator_last_name
                FROM {$this->table} e
                LEFT JOIN exam_types et ON e.exam_type_id = et.id
                LEFT JOIN courses c ON e.course_id = c.id
                LEFT JOIN classes cl ON e.class_id = cl.id
                LEFT JOIN faculty f ON e.invigilator_id = f.id
                WHERE e.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get exam subjects
     */
    public function getSubjects($examId)
    {
        $sql = "SELECT es.*, s.subject_name, s.subject_code 
                FROM exam_subjects es
                JOIN subjects s ON es.subject_id = s.id
                WHERE es.exam_id = :exam_id
                ORDER BY es.exam_date, es.start_time";
        
        return $this->db->fetchAll($sql, ['exam_id' => $examId]);
    }
    
    /**
     * Get exam students
     */
    public function getStudents($examId)
    {
        $sql = "SELECT s.* 
                FROM students s
                JOIN exams e ON s.course_id = e.course_id AND s.class_id = e.class_id
                WHERE e.id = :exam_id AND s.status = 'active'
                ORDER BY s.roll_number";
        
        return $this->db->fetchAll($sql, ['exam_id' => $examId]);
    }
    
    /**
     * Get exam results
     */
    public function getResults($examId)
    {
        $sql = "SELECT r.*, s.first_name, s.last_name, s.roll_number, sub.subject_name 
                FROM results r
                JOIN students s ON r.student_id = s.id
                JOIN subjects sub ON r.subject_id = sub.id
                WHERE r.exam_id = :exam_id
                ORDER BY s.roll_number, sub.subject_name";
        
        return $this->db->fetchAll($sql, ['exam_id' => $examId]);
    }
    
    /**
     * Get exam statistics
     */
    public function getExamStats($examId)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT r.student_id) as total_students,
                    COUNT(DISTINCT r.subject_id) as total_subjects,
                    AVG(r.marks_obtained) as average_marks,
                    MAX(r.marks_obtained) as highest_marks,
                    MIN(r.marks_obtained) as lowest_marks,
                    SUM(CASE WHEN r.marks_obtained >= e.passing_marks THEN 1 ELSE 0 END) as passed_students,
                    SUM(CASE WHEN r.marks_obtained < e.passing_marks THEN 1 ELSE 0 END) as failed_students
                FROM results r
                JOIN exams e ON r.exam_id = e.id
                WHERE r.exam_id = :exam_id";
        
        $result = $this->db->fetch($sql, ['exam_id' => $examId]);
        
        if ($result['total_students'] > 0) {
            $result['pass_percentage'] = round(($result['passed_students'] / $result['total_students']) * 100, 2);
        } else {
            $result['pass_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get exam hall tickets
     */
    public function getHallTickets($examId)
    {
        $hallTicketModel = new HallTicket();
        return $hallTicketModel->where('exam_id = :exam_id', ['exam_id' => $examId], 'student_id ASC');
    }
    
    /**
     * Check if exam can be edited
     */
    public function canEdit($examId)
    {
        $exam = $this->find($examId);
        if (!$exam) {
            return false;
        }
        
        // Cannot edit if exam is ongoing or completed
        if (in_array($exam['status'], ['ongoing', 'completed'])) {
            return false;
        }
        
        // Cannot edit if exam date has passed
        if ($exam['exam_date'] < today()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if exam can be deleted
     */
    public function canDelete($examId)
    {
        $exam = $this->find($examId);
        if (!$exam) {
            return false;
        }
        
        // Cannot delete if exam has results
        $resultModel = new Result();
        $resultCount = $resultModel->count('exam_id = :exam_id', ['exam_id' => $examId]);
        
        return $resultCount === 0;
    }
    
    /**
     * Get exam calendar
     */
    public function getCalendar($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $sql = "SELECT e.*, et.type_name, c.course_name, cl.class_name 
                FROM {$this->table} e
                JOIN exam_types et ON e.exam_type_id = et.id
                JOIN courses c ON e.course_id = c.id
                JOIN classes cl ON e.class_id = cl.id
                WHERE MONTH(e.exam_date) = :month AND YEAR(e.exam_date) = :year
                ORDER BY e.exam_date, e.start_time";
        
        return $this->db->fetchAll($sql, ['month' => $month, 'year' => $year]);
    }
    
    /**
     * Search exams
     */
    public function search($query, $limit = 10)
    {
        $sql = "SELECT id, exam_code, exam_name, exam_date, status 
                FROM {$this->table} 
                WHERE (exam_name LIKE :query OR exam_code LIKE :query) 
                ORDER BY exam_date DESC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }
}