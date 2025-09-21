<?php

/**
 * Guardian Model
 * 
 * Handles guardian data operations (extends parent functionality)
 */
class Guardian extends Model
{
    protected $table = 'guardians';
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'phone', 'relation',
        'occupation', 'company', 'address', 'city', 'state', 'pincode',
        'photo', 'is_legal_guardian', 'court_order_number', 'court_order_date',
        'emergency_contact', 'status'
    ];
    
    /**
     * Find guardian by user ID
     */
    public function findByUserId($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get guardian students
     */
    public function getStudents($guardianId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.* FROM students s 
                JOIN guardian_students gs ON s.id = gs.student_id 
                WHERE gs.guardian_id = :guardian_id AND s.status = 'active'
                ORDER BY s.first_name";
        
        return $db->fetchAll($sql, ['guardian_id' => $guardianId]);
    }
    
    /**
     * Link guardian to student
     */
    public function linkToStudent($data)
    {
        return $this->db->insert('guardian_students', [
            'guardian_id' => $data['guardian_id'],
            'student_id' => $data['student_id'],
            'relation' => $data['relation'],
            'is_primary' => $data['is_primary'] ?? 0,
            'can_pickup' => $data['can_pickup'] ?? 0,
            'emergency_contact' => $data['emergency_contact'] ?? 0,
            'linked_date' => now()
        ]);
    }
    
    /**
     * Unlink guardian from student
     */
    public function unlinkFromStudent($guardianId, $studentId)
    {
        return $this->db->delete('guardian_students', 
            'guardian_id = :guardian_id AND student_id = :student_id',
            ['guardian_id' => $guardianId, 'student_id' => $studentId]
        );
    }
    
    /**
     * Get legal guardians
     */
    public function getLegalGuardians()
    {
        return $this->where('is_legal_guardian = :legal AND status = :status', [
            'legal' => 1,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
    }
    
    /**
     * Get guardian with court order details
     */
    public function findWithCourtOrder($id)
    {
        $guardian = $this->find($id);
        
        if ($guardian && $guardian['is_legal_guardian']) {
            $guardian['court_order_details'] = [
                'order_number' => $guardian['court_order_number'],
                'order_date' => $guardian['court_order_date'],
                'is_valid' => $this->isCourtOrderValid($guardian['court_order_date'])
            ];
        }
        
        return $guardian;
    }
    
    /**
     * Check if court order is valid
     */
    private function isCourtOrderValid($orderDate)
    {
        if (!$orderDate) return false;
        
        // Assuming court orders are valid for 5 years
        $expiryDate = date('Y-m-d', strtotime($orderDate . ' +5 years'));
        return $expiryDate >= today();
    }
    
    /**
     * Get guardian statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_guardians,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_guardians,
                    SUM(CASE WHEN is_legal_guardian = 1 THEN 1 ELSE 0 END) as legal_guardians,
                    SUM(CASE WHEN emergency_contact = 1 THEN 1 ELSE 0 END) as emergency_contacts
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Search guardians
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
}