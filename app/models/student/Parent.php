<?php

/**
 * Parent Model
 * 
 * Handles parent/guardian data operations
 */
class ParentModel extends Model
{
    protected $table = 'parents';
    protected $fillable = [
        'user_id', 'student_id', 'first_name', 'last_name', 'email', 'phone',
        'relation', 'occupation', 'company', 'annual_income', 'qualification',
        'address', 'city', 'state', 'pincode', 'country', 'photo',
        'is_primary', 'can_pickup', 'emergency_contact', 'status'
    ];
    
    /**
     * Find parent by user ID
     */
    public function findByUserId($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get parents by student
     */
    public function getByStudent($studentId)
    {
        return $this->where('student_id = :student_id', ['student_id' => $studentId], 'is_primary DESC, relation ASC');
    }
    
    /**
     * Get primary parent
     */
    public function getPrimaryParent($studentId)
    {
        return $this->first(
            'student_id = :student_id AND is_primary = :primary',
            ['student_id' => $studentId, 'primary' => 1]
        );
    }
    
    /**
     * Get emergency contacts
     */
    public function getEmergencyContacts($studentId)
    {
        return $this->where(
            'student_id = :student_id AND emergency_contact = :emergency',
            ['student_id' => $studentId, 'emergency' => 1],
            'is_primary DESC'
        );
    }
    
    /**
     * Get children for parent
     */
    public function getChildren($parentUserId)
    {
        $parent = $this->findByUserId($parentUserId);
        if (!$parent) {
            return [];
        }
        
        $db = Database::getInstance();
        $sql = "SELECT s.* FROM students s 
                JOIN parents p ON s.id = p.student_id 
                WHERE p.user_id = :user_id AND s.status = 'active'
                ORDER BY s.first_name";
        
        return $db->fetchAll($sql, ['user_id' => $parentUserId]);
    }
    
    /**
     * Get parent with student details
     */
    public function findWithStudentDetails($id)
    {
        $sql = "SELECT p.*, s.first_name as student_first_name, s.last_name as student_last_name,
                       s.student_id, s.class_id, s.course_id,
                       c.course_name, cl.class_name
                FROM {$this->table} p
                JOIN students s ON p.student_id = s.id
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                WHERE p.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Search parents
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT id, first_name, last_name, email, phone, relation 
                FROM {$this->table} 
                WHERE (first_name LIKE :query OR last_name LIKE :query OR email LIKE :query) 
                AND status = :status 
                ORDER BY first_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [
            'query' => "%{$query}%",
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Get parent statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_parents,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_parents,
                    SUM(CASE WHEN is_primary = 1 THEN 1 ELSE 0 END) as primary_parents,
                    SUM(CASE WHEN emergency_contact = 1 THEN 1 ELSE 0 END) as emergency_contacts,
                    SUM(CASE WHEN relation = 'father' THEN 1 ELSE 0 END) as fathers,
                    SUM(CASE WHEN relation = 'mother' THEN 1 ELSE 0 END) as mothers,
                    SUM(CASE WHEN relation = 'guardian' THEN 1 ELSE 0 END) as guardians,
                    AVG(annual_income) as average_income
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get parents by relation
     */
    public function getByRelation($relation)
    {
        return $this->where('relation = :relation AND status = :status', [
            'relation' => $relation,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get parents by income range
     */
    public function getByIncomeRange($minIncome, $maxIncome)
    {
        return $this->where(
            'annual_income BETWEEN :min_income AND :max_income AND status = :status',
            ['min_income' => $minIncome, 'max_income' => $maxIncome, 'status' => STATUS_ACTIVE],
            'annual_income DESC'
        );
    }
    
    /**
     * Link parent to multiple students
     */
    public function linkToStudents($parentId, $studentIds)
    {
        $this->beginTransaction();
        
        try {
            foreach ($studentIds as $studentId) {
                // Create link record
                $this->db->insert('parent_students', [
                    'parent_id' => $parentId,
                    'student_id' => $studentId,
                    'linked_date' => now()
                ]);
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Unlink parent from student
     */
    public function unlinkFromStudent($parentId, $studentId)
    {
        return $this->db->delete('parent_students', 
            'parent_id = :parent_id AND student_id = :student_id',
            ['parent_id' => $parentId, 'student_id' => $studentId]
        );
    }
    
    /**
     * Get communication preferences
     */
    public function getCommunicationPreferences($parentId)
    {
        $parent = $this->find($parentId);
        
        return [
            'email_notifications' => $parent['email_notifications'] ?? 1,
            'sms_notifications' => $parent['sms_notifications'] ?? 1,
            'attendance_alerts' => $parent['attendance_alerts'] ?? 1,
            'fee_reminders' => $parent['fee_reminders'] ?? 1,
            'exam_notifications' => $parent['exam_notifications'] ?? 1,
            'result_notifications' => $parent['result_notifications'] ?? 1
        ];
    }
    
    /**
     * Update communication preferences
     */
    public function updateCommunicationPreferences($parentId, $preferences)
    {
        return $this->update($parentId, $preferences);
    }
}