<?php

/**
 * Class Model
 * 
 * Handles class data operations
 */
class ClassModel extends Model
{
    protected $table = 'classes';
    protected $fillable = [
        'class_name', 'class_code', 'course_id', 'semester', 'academic_year',
        'class_teacher_id', 'room_number', 'capacity', 'description', 'status'
    ];
    
    /**
     * Find class by code
     */
    public function findByCode($classCode)
    {
        return $this->findBy('class_code', $classCode);
    }
    
    /**
     * Get classes by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id AND status = :status', [
            'course_id' => $courseId,
            'status' => 'active'
        ], 'class_name ASC');
    }
    
    /**
     * Get classes by semester
     */
    public function getBySemester($semester)
    {
        return $this->where('semester = :semester AND status = :status', [
            'semester' => $semester,
            'status' => 'active'
        ], 'class_name ASC');
    }
    
    /**
     * Get classes by academic year
     */
    public function getByAcademicYear($academicYear)
    {
        return $this->where('academic_year = :year AND status = :status', [
            'year' => $academicYear,
            'status' => 'active'
        ], 'class_name ASC');
    }
    
    /**
     * Get class with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT c.*, 
                       co.course_name, co.course_code,
                       f.first_name as teacher_first_name, f.last_name as teacher_last_name
                FROM {$this->table} c
                LEFT JOIN courses co ON c.course_id = co.id
                LEFT JOIN faculty f ON c.class_teacher_id = f.id
                WHERE c.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get class students
     */
    public function getStudents($classId)
    {
        $studentModel = new Student();
        return $studentModel->where('class_id = :class_id AND status = :status', [
            'class_id' => $classId,
            'status' => STATUS_ACTIVE
        ], 'roll_number ASC');
    }
    
    /**
     * Get class sections
     */
    public function getSections($classId)
    {
        $sectionModel = new Section();
        return $sectionModel->where('class_id = :class_id AND status = :status', [
            'class_id' => $classId,
            'status' => 'active'
        ], 'section_name ASC');
    }
    
    /**
     * Get class timetable
     */
    public function getTimetable($classId, $day = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($day) {
            $where .= ' AND day = :day';
            $params['day'] = $day;
        }
        
        $sql = "SELECT t.*, s.subject_name, f.first_name, f.last_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                JOIN faculty f ON t.faculty_id = f.id
                WHERE {$where}
                ORDER BY t.day, t.start_time";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get class teacher
     */
    public function getClassTeacher($classId)
    {
        $class = $this->find($classId);
        if ($class && $class['class_teacher_id']) {
            $facultyModel = new Faculty();
            return $facultyModel->find($class['class_teacher_id']);
        }
        return null;
    }
    
    /**
     * Set class teacher
     */
    public function setClassTeacher($classId, $facultyId)
    {
        return $this->update($classId, ['class_teacher_id' => $facultyId]);
    }
    
    /**
     * Get class attendance statistics
     */
    public function getAttendanceStats($classId, $date = null)
    {
        $where = 'class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($date) {
            $where .= ' AND attendance_date = :date';
            $params['date'] = $date;
        } else {
            $where .= ' AND attendance_date = :today';
            $params['today'] = today();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(DISTINCT sa.student_id) as total_students,
                    SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN sa.status = 'late' THEN 1 ELSE 0 END) as late
                FROM student_attendance sa
                JOIN students s ON sa.student_id = s.id
                WHERE s.{$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['total_students'] > 0) {
            $result['attendance_percentage'] = round(($result['present'] / $result['total_students']) * 100, 2);
        } else {
            $result['attendance_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get class exam results
     */
    public function getExamResults($classId, $examId = null)
    {
        $where = 's.class_id = :class_id';
        $params = ['class_id' => $classId];
        
        if ($examId) {
            $where .= ' AND r.exam_id = :exam_id';
            $params['exam_id'] = $examId;
        }
        
        $sql = "SELECT r.*, s.first_name, s.last_name, s.roll_number, sub.subject_name 
                FROM results r
                JOIN students s ON r.student_id = s.id
                JOIN subjects sub ON r.subject_id = sub.id
                WHERE {$where}
                ORDER BY s.roll_number, sub.subject_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get class statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_classes,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_classes,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_classes,
                    AVG(capacity) as average_capacity,
                    SUM(capacity) as total_capacity
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get classes with student count
     */
    public function getClassesWithStudentCount()
    {
        $sql = "SELECT c.*, COUNT(s.id) as student_count, c.capacity,
                       CASE 
                           WHEN c.capacity > 0 THEN ROUND((COUNT(s.id) / c.capacity) * 100, 2)
                           ELSE 0 
                       END as occupancy_percentage
                FROM {$this->table} c
                LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                GROUP BY c.id
                ORDER BY c.class_name";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Search classes
     */
    public function search($query, $courseId = null, $limit = 10)
    {
        $where = '(class_name LIKE :query OR class_code LIKE :query) AND status = :status';
        $params = ['query' => "%{$query}%", 'status' => 'active'];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        $sql = "SELECT id, class_code, class_name, semester 
                FROM {$this->table} 
                WHERE {$where}
                ORDER BY class_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Promote class to next semester
     */
    public function promoteToNextSemester($classId)
    {
        $class = $this->find($classId);
        if (!$class) {
            throw new Exception('Class not found');
        }
        
        $newSemester = $class['semester'] + 1;
        
        // Check if course has this semester
        $courseModel = new Course();
        $course = $courseModel->find($class['course_id']);
        
        if ($newSemester > $course['total_semesters']) {
            throw new Exception('Cannot promote beyond course duration');
        }
        
        return $this->update($classId, ['semester' => $newSemester]);
    }
}