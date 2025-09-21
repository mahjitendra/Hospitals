<?php

/**
 * Salary Model
 * 
 * Handles salary structure data operations
 */
class Salary extends Model
{
    protected $table = 'salaries';
    protected $fillable = [
        'faculty_id', 'basic_salary', 'hra', 'da', 'medical_allowance',
        'transport_allowance', 'special_allowance', 'other_allowances',
        'gross_salary', 'pf_deduction', 'esi_deduction', 'tax_deduction',
        'other_deductions', 'total_deductions', 'net_salary', 'effective_from',
        'effective_to', 'updated_by', 'remarks', 'status'
    ];
    
    /**
     * Get current salary for faculty
     */
    public function getCurrentSalary($facultyId)
    {
        return $this->first(
            'faculty_id = :faculty_id AND status = :status AND (effective_to IS NULL OR effective_to >= :today)',
            ['faculty_id' => $facultyId, 'status' => 'active', 'today' => today()],
            'effective_from DESC'
        );
    }
    
    /**
     * Get salary history
     */
    public function getSalaryHistory($facultyId)
    {
        return $this->where('faculty_id = :faculty_id', ['faculty_id' => $facultyId], 'effective_from DESC');
    }
    
    /**
     * Get faculty salary structure
     */
    public function getFacultySalaryStructure($facultyId)
    {
        $sql = "SELECT s.*, f.first_name, f.last_name, f.employee_id,
                       d.department_name, des.designation_name
                FROM {$this->table} s
                JOIN faculty f ON s.faculty_id = f.id
                LEFT JOIN departments d ON f.department_id = d.id
                LEFT JOIN designations des ON f.designation_id = des.id
                WHERE s.faculty_id = :faculty_id
                ORDER BY s.effective_from DESC";
        
        return $this->db->fetchAll($sql, ['faculty_id' => $facultyId]);
    }
    
    /**
     * Get salary statistics
     */
    public function getStats($departmentId = null)
    {
        $where = 's.status = :status AND (s.effective_to IS NULL OR s.effective_to >= :today)';
        $params = ['status' => 'active', 'today' => today()];
        
        if ($departmentId) {
            $where .= ' AND f.department_id = :department_id';
            $params['department_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_salaries,
                    AVG(s.net_salary) as average_salary,
                    MIN(s.net_salary) as min_salary,
                    MAX(s.net_salary) as max_salary,
                    SUM(s.net_salary) as total_salary_expense,
                    AVG(s.basic_salary) as average_basic,
                    AVG(s.gross_salary) as average_gross,
                    AVG(s.total_deductions) as average_deductions
                FROM {$this->table} s
                JOIN faculty f ON s.faculty_id = f.id
                WHERE {$where}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Get salary ranges by designation
     */
    public function getSalaryRangesByDesignation()
    {
        $sql = "SELECT des.designation_name,
                       MIN(s.net_salary) as min_salary,
                       MAX(s.net_salary) as max_salary,
                       AVG(s.net_salary) as avg_salary,
                       COUNT(s.id) as employee_count
                FROM designations des
                LEFT JOIN faculty f ON des.id = f.designation_id
                LEFT JOIN {$this->table} s ON f.id = s.faculty_id 
                    AND s.status = 'active' 
                    AND (s.effective_to IS NULL OR s.effective_to >= CURDATE())
                GROUP BY des.id, des.designation_name
                ORDER BY avg_salary DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get salary increment history
     */
    public function getIncrementHistory($facultyId)
    {
        $sql = "SELECT 
                    s1.effective_from,
                    s1.net_salary as new_salary,
                    s2.net_salary as previous_salary,
                    (s1.net_salary - s2.net_salary) as increment_amount,
                    ROUND(((s1.net_salary - s2.net_salary) / s2.net_salary) * 100, 2) as increment_percentage
                FROM {$this->table} s1
                LEFT JOIN {$this->table} s2 ON s1.faculty_id = s2.faculty_id 
                    AND s2.effective_from < s1.effective_from
                    AND s2.id = (
                        SELECT MAX(id) FROM {$this->table} 
                        WHERE faculty_id = s1.faculty_id 
                        AND effective_from < s1.effective_from
                    )
                WHERE s1.faculty_id = :faculty_id
                ORDER BY s1.effective_from DESC";
        
        return $this->db->fetchAll($sql, ['faculty_id' => $facultyId]);
    }
    
    /**
     * Calculate annual CTC
     */
    public function calculateAnnualCTC($salaryId)
    {
        $salary = $this->find($salaryId);
        if (!$salary) {
            return 0;
        }
        
        $monthlyCTC = $salary['gross_salary'];
        $annualCTC = $monthlyCTC * 12;
        
        // Add annual benefits (bonus, gratuity, etc.)
        $annualBenefits = $this->calculateAnnualBenefits($salary);
        
        return $annualCTC + $annualBenefits;
    }
    
    /**
     * Calculate annual benefits
     */
    private function calculateAnnualBenefits($salary)
    {
        // Calculate bonus (typically 1 month salary)
        $bonus = $salary['basic_salary'];
        
        // Calculate gratuity (4.81% of basic salary)
        $gratuity = $salary['basic_salary'] * 0.0481 * 12;
        
        // Add other benefits as needed
        return $bonus + $gratuity;
    }
    
    /**
     * Get due for increment
     */
    public function getDueForIncrement($months = 12)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$months} months"));
        
        $sql = "SELECT f.*, s.net_salary, s.effective_from
                FROM faculty f
                JOIN {$this->table} s ON f.id = s.faculty_id
                WHERE s.effective_from <= :cutoff_date
                AND s.status = 'active'
                AND (s.effective_to IS NULL OR s.effective_to >= CURDATE())
                AND f.status = 'active'
                ORDER BY s.effective_from ASC";
        
        return $this->db->fetchAll($sql, ['cutoff_date' => $cutoffDate]);
    }
}