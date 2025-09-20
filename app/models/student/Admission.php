<?php

/**
 * Admission Model
 * 
 * Handles admission application data operations
 */
class Admission extends Model
{
    protected $table = 'admissions';
    protected $fillable = [
        'application_number', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'blood_group', 'photo', 'course_id',
        'application_date', 'address', 'city', 'state', 'pincode',
        'father_name', 'father_phone', 'father_occupation', 'father_email',
        'mother_name', 'mother_phone', 'mother_occupation', 'mother_email',
        'guardian_name', 'guardian_phone', 'guardian_relation',
        'previous_school', 'previous_marks', 'previous_percentage',
        'entrance_exam_score', 'documents', 'status', 'remarks',
        'approved_by', 'approved_date', 'rejected_by', 'rejected_date',
        'rejection_reason', 'student_id'
    ];
    
    /**
     * Find admission by application number
     */
    public function findByApplicationNumber($applicationNumber)
    {
        return $this->findBy('application_number', $applicationNumber);
    }
    
    /**
     * Get admissions by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id', ['course_id' => $courseId], 'application_date DESC');
    }
    
    /**
     * Get admissions by status
     */
    public function getByStatus($status)
    {
        return $this->where('status = :status', ['status' => $status], 'application_date DESC');
    }
    
    /**
     * Get pending admissions
     */
    public function getPendingAdmissions()
    {
        return $this->where('status = :status', ['status' => 'pending'], 'application_date ASC');
    }
    
    /**
     * Get approved admissions
     */
    public function getApprovedAdmissions()
    {
        return $this->where('status = :status', ['status' => 'approved'], 'approved_date DESC');
    }
    
    /**
     * Get admission with details
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT a.*, 
                       c.course_name, c.course_code, c.duration, c.fees,
                       approver.name as approved_by_name,
                       rejector.name as rejected_by_name
                FROM {$this->table} a
                LEFT JOIN courses c ON a.course_id = c.id
                LEFT JOIN users approver ON a.approved_by = approver.id
                LEFT JOIN users rejector ON a.rejected_by = rejector.id
                WHERE a.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get admission statistics
     */
    public function getStats($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_applicants,
                    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_applicants
                FROM {$this->table}
                WHERE YEAR(application_date) = :year";
        
        $result = $this->db->fetch($sql, ['year' => $year]);
        
        if ($result['total_applications'] > 0) {
            $result['approval_rate'] = round(($result['approved'] / $result['total_applications']) * 100, 2);
            $result['rejection_rate'] = round(($result['rejected'] / $result['total_applications']) * 100, 2);
        } else {
            $result['approval_rate'] = 0;
            $result['rejection_rate'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get monthly admission trends
     */
    public function getMonthlyTrends($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT 
                    MONTH(application_date) as month,
                    COUNT(*) as applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
                FROM {$this->table}
                WHERE YEAR(application_date) = :year
                GROUP BY MONTH(application_date)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Get admissions by course statistics
     */
    public function getStatsByCourse($year = null)
    {
        $year = $year ?? date('Y');
        
        $sql = "SELECT c.course_name, c.course_code,
                       COUNT(a.id) as total_applications,
                       SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) as approved,
                       SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                       SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM courses c
                LEFT JOIN {$this->table} a ON c.id = a.course_id AND YEAR(a.application_date) = :year
                GROUP BY c.id, c.course_name, c.course_code
                ORDER BY total_applications DESC";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
    
    /**
     * Search admissions
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT id, application_number, first_name, last_name, email, phone, status 
                FROM {$this->table} 
                WHERE (first_name LIKE :query OR last_name LIKE :query 
                       OR application_number LIKE :query OR email LIKE :query) 
                ORDER BY application_date DESC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }
    
    /**
     * Check if email already applied
     */
    public function hasApplied($email, $courseId = null)
    {
        $where = 'email = :email AND status IN (:pending, :approved)';
        $params = ['email' => $email, 'pending' => 'pending', 'approved' => 'approved'];
        
        if ($courseId) {
            $where .= ' AND course_id = :course_id';
            $params['course_id'] = $courseId;
        }
        
        return $this->exists($where, $params);
    }
    
    /**
     * Get admission documents
     */
    public function getDocuments($admissionId)
    {
        $admission = $this->find($admissionId);
        if ($admission && !empty($admission['documents'])) {
            return json_decode($admission['documents'], true);
        }
        return [];
    }
}