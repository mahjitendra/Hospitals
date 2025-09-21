<?php

/**
 * Alumni Model
 * 
 * Handles alumni data operations
 */
class Alumni extends Model
{
    protected $table = 'alumni';
    protected $fillable = [
        'student_id', 'first_name', 'last_name', 'email', 'phone', 'course_id',
        'graduation_year', 'graduation_date', 'admission_year', 'student_id_number',
        'final_grade', 'final_percentage', 'achievements', 'current_company',
        'current_position', 'profession', 'work_experience', 'current_salary',
        'linkedin_profile', 'facebook_profile', 'twitter_profile', 'website',
        'address', 'city', 'state', 'country', 'pincode', 'photo',
        'is_available_for_mentoring', 'is_available_for_placement', 'status'
    ];
    
    /**
     * Find alumni by student ID
     */
    public function findByStudentId($studentId)
    {
        return $this->findBy('student_id', $studentId);
    }
    
    /**
     * Get alumni by graduation year
     */
    public function getByGraduationYear($year)
    {
        return $this->where('graduation_year = :year', ['year' => $year], 'final_percentage DESC, first_name ASC');
    }
    
    /**
     * Get alumni by course
     */
    public function getByCourse($courseId)
    {
        return $this->where('course_id = :course_id', ['course_id' => $courseId], 'graduation_year DESC, first_name ASC');
    }
    
    /**
     * Get alumni by profession
     */
    public function getByProfession($profession)
    {
        return $this->where('profession LIKE :profession', ['profession' => "%{$profession}%"], 'work_experience DESC');
    }
    
    /**
     * Get alumni available for mentoring
     */
    public function getAvailableForMentoring()
    {
        return $this->where(
            'is_available_for_mentoring = :mentoring AND status = :status',
            ['mentoring' => 1, 'status' => STATUS_ACTIVE],
            'work_experience DESC'
        );
    }
    
    /**
     * Get alumni available for placement
     */
    public function getAvailableForPlacement()
    {
        return $this->where(
            'is_available_for_placement = :placement AND status = :status',
            ['placement' => 1, 'status' => STATUS_ACTIVE],
            'current_company ASC'
        );
    }
    
    /**
     * Search alumni
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT id, first_name, last_name, email, phone, current_company, current_position, graduation_year
                FROM {$this->table} 
                WHERE (first_name LIKE :query OR last_name LIKE :query 
                       OR current_company LIKE :query OR current_position LIKE :query) 
                AND status = :status 
                ORDER BY graduation_year DESC, first_name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [
            'query' => "%{$query}%",
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Get alumni statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_alumni,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_alumni,
                    COUNT(DISTINCT graduation_year) as graduation_years,
                    COUNT(DISTINCT course_id) as courses_represented,
                    SUM(CASE WHEN current_company IS NOT NULL THEN 1 ELSE 0 END) as employed_alumni,
                    SUM(CASE WHEN is_available_for_mentoring = 1 THEN 1 ELSE 0 END) as mentors_available,
                    AVG(current_salary) as average_salary,
                    MAX(current_salary) as highest_salary
                FROM {$this->table}";
        
        $result = $this->db->fetch($sql);
        
        if ($result['total_alumni'] > 0) {
            $result['employment_rate'] = round(($result['employed_alumni'] / $result['total_alumni']) * 100, 2);
        } else {
            $result['employment_rate'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get alumni by year statistics
     */
    public function getAlumniByYear()
    {
        $sql = "SELECT 
                    graduation_year,
                    COUNT(*) as alumni_count,
                    AVG(final_percentage) as average_percentage
                FROM {$this->table}
                WHERE status = 'active'
                GROUP BY graduation_year
                ORDER BY graduation_year DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get alumni by course statistics
     */
    public function getAlumniByCourse()
    {
        $sql = "SELECT c.course_name, c.course_code,
                       COUNT(a.id) as alumni_count,
                       AVG(a.final_percentage) as average_percentage,
                       SUM(CASE WHEN a.current_company IS NOT NULL THEN 1 ELSE 0 END) as employed_count
                FROM courses c
                LEFT JOIN {$this->table} a ON c.id = a.course_id AND a.status = 'active'
                GROUP BY c.id, c.course_name, c.course_code
                ORDER BY alumni_count DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get alumni by profession statistics
     */
    public function getAlumniByProfession()
    {
        $sql = "SELECT 
                    profession,
                    COUNT(*) as count,
                    AVG(current_salary) as average_salary
                FROM {$this->table}
                WHERE profession IS NOT NULL AND status = 'active'
                GROUP BY profession
                ORDER BY count DESC
                LIMIT 10";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get top performers
     */
    public function getTopPerformers($limit = 10)
    {
        return $this->where('status = :status', ['status' => STATUS_ACTIVE], 'final_percentage DESC', $limit);
    }
    
    /**
     * Get recent graduates
     */
    public function getRecentGraduates($months = 12)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$months} months"));
        
        return $this->where(
            'graduation_date >= :cutoff_date AND status = :status',
            ['cutoff_date' => $cutoffDate, 'status' => STATUS_ACTIVE],
            'graduation_date DESC'
        );
    }
    
    /**
     * Update employment status
     */
    public function updateEmploymentStatus($id, $employmentData)
    {
        return $this->update($id, [
            'current_company' => $employmentData['company'],
            'current_position' => $employmentData['position'],
            'profession' => $employmentData['profession'],
            'current_salary' => $employmentData['salary'] ?? null,
            'work_experience' => $employmentData['experience'] ?? null
        ]);
    }
    
    /**
     * Add achievement
     */
    public function addAchievement($alumniId, $achievement)
    {
        $alumni = $this->find($alumniId);
        $currentAchievements = $alumni['achievements'] ? json_decode($alumni['achievements'], true) : [];
        
        $currentAchievements[] = [
            'title' => $achievement['title'],
            'description' => $achievement['description'],
            'date' => $achievement['date'] ?? now(),
            'category' => $achievement['category'] ?? 'general'
        ];
        
        return $this->update($alumniId, ['achievements' => json_encode($currentAchievements)]);
    }
}