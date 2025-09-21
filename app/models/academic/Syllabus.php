<?php

/**
 * Syllabus Model
 * 
 * Handles syllabus data operations
 */
class Syllabus extends Model
{
    protected $table = 'syllabus';
    protected $fillable = [
        'course_id', 'subject_id', 'semester', 'unit_number', 'unit_title',
        'topics', 'learning_objectives', 'duration_hours', 'teaching_methods',
        'assessment_methods', 'reference_materials', 'practical_exercises',
        'assignments', 'created_by', 'updated_by', 'published_by', 'published_date',
        'status'
    ];
    
    /**
     * Get syllabus by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id', ['course_id' => $courseId], 'semester ASC, unit_number ASC');
    }
    
    /**
     * Get syllabus by subject
     */
    public function getBySubject($subjectId)
    {
        return $this->where('subject_id = :subject_id', ['subject_id' => $subjectId], 'unit_number ASC');
    }
    
    /**
     * Get syllabus by semester
     */
    public function getBySemester($courseId, $semester)
    {
        return $this->where('course_id = :course_id AND semester = :semester', [
            'course_id' => $courseId,
            'semester' => $semester
        ], 'unit_number ASC');
    }
    
    /**
     * Get published syllabus
     */
    public function getPublishedSyllabus($courseId = null, $subjectId = null)
    {
        $where = 'status = :status';
        $params = ['status' => 'published'];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        if ($subjectId) {
            $where .= ' AND subject_id = :subject_id';
            $params['subject_id'] = $subjectId;
        }
        
        return $this->where($where, $params, 'semester ASC, unit_number ASC');
    }
    
    /**
     * Get syllabus with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT sy.*, 
                       c.course_name, c.course_code,
                       s.subject_name, s.subject_code,
                       creator.name as created_by_name,
                       publisher.name as published_by_name
                FROM {$this->table} sy
                LEFT JOIN courses c ON sy.course_id = c.id
                LEFT JOIN subjects s ON sy.subject_id = s.id
                LEFT JOIN users creator ON sy.created_by = creator.id
                LEFT JOIN users publisher ON sy.published_by = publisher.id
                WHERE sy.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get syllabus completion status
     */
    public function getCompletionStatus($courseId, $semester)
    {
        $sql = "SELECT s.subject_name, s.id as subject_id,
                       COUNT(sy.id) as units_created,
                       SUM(CASE WHEN sy.status = 'published' THEN 1 ELSE 0 END) as units_published
                FROM subjects s
                LEFT JOIN {$this->table} sy ON s.id = sy.subject_id
                WHERE s.course_id = :course_id AND s.semester = :semester
                GROUP BY s.id, s.subject_name
                ORDER BY s.subject_name";
        
        return $this->db->fetchAll($sql, ['course_id' => $courseId, 'semester' => $semester]);
    }
    
    /**
     * Get syllabus statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_units,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_units,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_units,
                    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_units,
                    COUNT(DISTINCT course_id) as courses_covered,
                    COUNT(DISTINCT subject_id) as subjects_covered,
                    SUM(duration_hours) as total_duration_hours,
                    AVG(duration_hours) as avg_unit_duration
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get syllabus by status
     */
    public function getByStatus($status)
    {
        $sql = "SELECT sy.*, c.course_name, s.subject_name
                FROM {$this->table} sy
                LEFT JOIN courses c ON sy.course_id = c.id
                LEFT JOIN subjects s ON sy.subject_id = s.id
                WHERE sy.status = :status
                ORDER BY sy.created_at DESC";
        
        return $this->db->fetchAll($sql, ['status' => $status]);
    }
    
    /**
     * Search syllabus
     */
    public function search($query, $courseId = null, $subjectId = null)
    {
        $where = '(sy.unit_title LIKE :query OR sy.topics LIKE :query)';
        $params = ['query' => "%{$query}%"];
        
        if ($courseId) {
            $where .= ' AND sy.course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        if ($subjectId) {
            $where .= ' AND sy.subject_id = :subject_id';
            $params['subject_id'] = $subjectId;
        }
        
        $sql = "SELECT sy.*, c.course_name, s.subject_name
                FROM {$this->table} sy
                LEFT JOIN courses c ON sy.course_id = c.id
                LEFT JOIN subjects s ON sy.subject_id = s.id
                WHERE {$where}
                ORDER BY sy.semester, sy.unit_number";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Clone syllabus to new session
     */
    public function cloneToNewSession($courseId, $fromSession, $toSession)
    {
        $this->beginTransaction();
        
        try {
            $syllabi = $this->where('course_id = :course_id AND academic_session = :session', [
                'course_id' => $courseId,
                'session' => $fromSession
            ]);
            
            $clonedCount = 0;
            
            foreach ($syllabi as $syllabus) {
                unset($syllabus['id']);
                unset($syllabus['created_at']);
                unset($syllabus['updated_at']);
                
                $syllabus['academic_session'] = $toSession;
                $syllabus['status'] = 'draft';
                $syllabus['published_by'] = null;
                $syllabus['published_date'] = null;
                
                $this->create($syllabus);
                $clonedCount++;
            }
            
            $this->commit();
            return $clonedCount;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get syllabus coverage
     */
    public function getSyllabusCoverage($subjectId, $facultyId = null)
    {
        $syllabusUnits = $this->where('subject_id = :subject_id AND status = :status', [
            'subject_id' => $subjectId,
            'status' => 'published'
        ], 'unit_number ASC');
        
        // Get covered units (this would come from a syllabus_coverage table)
        $coveredUnits = []; // Placeholder
        
        $coverage = [];
        foreach ($syllabusUnits as $unit) {
            $coverage[] = [
                'unit' => $unit,
                'covered' => in_array($unit['id'], $coveredUnits),
                'coverage_date' => null // Would come from coverage table
            ];
        }
        
        return $coverage;
    }
}