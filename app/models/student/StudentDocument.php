<?php

/**
 * Student Document Model
 * 
 * Handles student document data operations
 */
class StudentDocument extends Model
{
    protected $table = 'student_documents';
    protected $fillable = [
        'student_id', 'document_type', 'document_name', 'filename', 'original_name',
        'file_path', 'file_size', 'file_type', 'description', 'uploaded_by',
        'verification_status', 'verified_by', 'verified_date', 'expiry_date',
        'is_mandatory', 'is_verified', 'status'
    ];
    
    /**
     * Get documents by student
     */
    public function getByStudent($studentId)
    {
        return $this->where('student_id = :student_id', ['student_id' => $studentId], 'created_at DESC');
    }
    
    /**
     * Get documents by type
     */
    public function getByType($documentType, $studentId = null)
    {
        $where = 'document_type = :type';
        $params = ['type' => $documentType];
        
        if ($studentId) {
            $where .= ' AND student_id = :student_id';
            $params['student_id'] = $studentId;
        }
        
        return $this->where($where, $params, 'created_at DESC');
    }
    
    /**
     * Get pending verification documents
     */
    public function getPendingVerification()
    {
        $sql = "SELECT sd.*, s.first_name, s.last_name, s.student_id
                FROM {$this->table} sd
                JOIN students s ON sd.student_id = s.id
                WHERE sd.verification_status = 'pending'
                ORDER BY sd.created_at ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get verified documents
     */
    public function getVerifiedDocuments($studentId = null)
    {
        $where = 'verification_status = :status';
        $params = ['status' => 'verified'];
        
        if ($studentId) {
            $where .= ' AND student_id = :student_id';
            $params['student_id'] = $studentId;
        }
        
        return $this->where($where, $params, 'verified_date DESC');
    }
    
    /**
     * Get expired documents
     */
    public function getExpiredDocuments()
    {
        return $this->where(
            'expiry_date IS NOT NULL AND expiry_date < :today AND status = :status',
            ['today' => today(), 'status' => 'active'],
            'expiry_date ASC'
        );
    }
    
    /**
     * Get expiring documents
     */
    public function getExpiringDocuments($days = 30)
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $sql = "SELECT sd.*, s.first_name, s.last_name, s.student_id, s.email, s.phone
                FROM {$this->table} sd
                JOIN students s ON sd.student_id = s.id
                WHERE sd.expiry_date IS NOT NULL 
                AND sd.expiry_date BETWEEN :today AND :expiry_date 
                AND sd.status = 'active'
                ORDER BY sd.expiry_date ASC";
        
        return $this->db->fetchAll($sql, ['today' => today(), 'expiry_date' => $expiryDate]);
    }
    
    /**
     * Verify document
     */
    public function verifyDocument($id, $verifiedBy, $remarks = null)
    {
        return $this->update($id, [
            'verification_status' => 'verified',
            'is_verified' => 1,
            'verified_by' => $verifiedBy,
            'verified_date' => now(),
            'verification_remarks' => $remarks
        ]);
    }
    
    /**
     * Reject document
     */
    public function rejectDocument($id, $rejectedBy, $reason)
    {
        return $this->update($id, [
            'verification_status' => 'rejected',
            'is_verified' => 0,
            'verified_by' => $rejectedBy,
            'verified_date' => now(),
            'verification_remarks' => $reason
        ]);
    }
    
    /**
     * Get mandatory documents checklist
     */
    public function getMandatoryDocumentsChecklist($studentId)
    {
        $mandatoryTypes = [
            'birth_certificate' => 'Birth Certificate',
            'transfer_certificate' => 'Transfer Certificate',
            'marksheet' => 'Previous Marksheet',
            'photo' => 'Photograph',
            'aadhar' => 'Aadhar Card'
        ];
        
        $checklist = [];
        
        foreach ($mandatoryTypes as $type => $name) {
            $document = $this->first(
                'student_id = :student_id AND document_type = :type',
                ['student_id' => $studentId, 'type' => $type]
            );
            
            $checklist[$type] = [
                'name' => $name,
                'uploaded' => $document ? true : false,
                'verified' => $document ? $document['is_verified'] : false,
                'document' => $document
            ];
        }
        
        return $checklist;
    }
    
    /**
     * Get document statistics
     */
    public function getStats($studentId = null)
    {
        $where = '1=1';
        $params = [];
        
        if ($studentId) {
            $where .= ' AND student_id = :student_id';
            $params['student_id'] = $studentId;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_documents,
                    SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END) as verified_documents,
                    SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending_documents,
                    SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) as rejected_documents,
                    SUM(CASE WHEN is_mandatory = 1 THEN 1 ELSE 0 END) as mandatory_documents,
                    SUM(CASE WHEN is_mandatory = 1 AND verification_status = 'verified' THEN 1 ELSE 0 END) as verified_mandatory,
                    SUM(file_size) as total_storage_used
                FROM {$this->table}
                WHERE {$where}";
        
        $result = $this->db->fetch($sql, $params);
        
        if ($result['mandatory_documents'] > 0) {
            $result['mandatory_completion_rate'] = round(($result['verified_mandatory'] / $result['mandatory_documents']) * 100, 2);
        } else {
            $result['mandatory_completion_rate'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get documents by verification status
     */
    public function getByVerificationStatus($status)
    {
        $sql = "SELECT sd.*, s.first_name, s.last_name, s.student_id
                FROM {$this->table} sd
                JOIN students s ON sd.student_id = s.id
                WHERE sd.verification_status = :status
                ORDER BY sd.created_at ASC";
        
        return $this->db->fetchAll($sql, ['status' => $status]);
    }
    
    /**
     * Bulk verify documents
     */
    public function bulkVerify($documentIds, $verifiedBy)
    {
        $this->beginTransaction();
        
        try {
            foreach ($documentIds as $id) {
                $this->verifyDocument($id, $verifiedBy);
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get document types
     */
    public function getDocumentTypes()
    {
        return [
            'birth_certificate' => 'Birth Certificate',
            'transfer_certificate' => 'Transfer Certificate',
            'marksheet' => 'Previous Marksheet',
            'photo' => 'Photograph',
            'aadhar' => 'Aadhar Card',
            'passport' => 'Passport',
            'medical_certificate' => 'Medical Certificate',
            'caste_certificate' => 'Caste Certificate',
            'income_certificate' => 'Income Certificate',
            'migration_certificate' => 'Migration Certificate',
            'character_certificate' => 'Character Certificate',
            'other' => 'Other Document'
        ];
    }
}