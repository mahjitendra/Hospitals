<?php

/**
 * Designation Model
 * 
 * Handles designation data operations
 */
class Designation extends Model
{
    protected $table = 'designations';
    protected $fillable = [
        'designation_name', 'description', 'level', 'min_qualification',
        'min_experience', 'responsibilities', 'reporting_to', 'salary_range_min',
        'salary_range_max', 'benefits', 'status'
    ];
    
    /**
     * Find designation by name
     */
    public function findByName($name)
    {
        return $this->findBy('designation_name', $name);
    }
    
    /**
     * Get designations by level
     */
    public function getByLevel($level)
    {
        return $this->where('level = :level AND status = :status', [
            'level' => $level,
            'status' => 'active'
        ], 'designation_name ASC');
    }
    
    /**
     * Get designation hierarchy
     */
    public function getHierarchy()
    {
        $designations = $this->where('status = :status', ['status' => 'active'], 'level DESC, designation_name ASC');
        
        $hierarchy = [];
        foreach ($designations as $designation) {
            $level = $designation['level'];
            if (!isset($hierarchy[$level])) {
                $hierarchy[$level] = [];
            }
            $hierarchy[$level][] = $designation;
        }
        
        return $hierarchy;
    }
    
    /**
     * Get subordinate designations
     */
    public function getSubordinates($designationId)
    {
        return $this->where('reporting_to = :reporting_to AND status = :status', [
            'reporting_to' => $designationId,
            'status' => 'active'
        ], 'level DESC, designation_name ASC');
    }
    
    /**
     * Get supervisor designation
     */
    public function getSupervisor($designationId)
    {
        $designation = $this->find($designationId);
        
        if ($designation && $designation['reporting_to']) {
            return $this->find($designation['reporting_to']);
        }
        
        return null;
    }
    
    /**
     * Get designation employees
     */
    public function getEmployees($designationId)
    {
        $db = Database::getInstance();
        $sql = "SELECT f.*, d.department_name 
                FROM faculty f 
                LEFT JOIN departments d ON f.department_id = d.id 
                WHERE f.designation_id = :designation_id AND f.status = 'active'
                
                UNION ALL
                
                SELECT s.*, d.department_name 
                FROM staff s 
                LEFT JOIN departments d ON s.department_id = d.id 
                WHERE s.designation_id = :designation_id AND s.status = 'active'
                
                ORDER BY first_name";
        
        return $db->fetchAll($sql, ['designation_id' => $designationId]);
    }
    
    /**
     * Get designation statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_designations,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_designations,
                    AVG(level) as average_level,
                    MAX(level) as highest_level,
                    MIN(level) as lowest_level
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get employee count by designation
     */
    public function getEmployeeCount($designationId)
    {
        $db = Database::getInstance();
        
        $facultyCount = $db->count('faculty', 'designation_id = :designation_id AND status = :status', [
            'designation_id' => $designationId,
            'status' => STATUS_ACTIVE
        ]);
        
        $staffCount = $db->count('staff', 'designation_id = :designation_id AND status = :status', [
            'designation_id' => $designationId,
            'status' => STATUS_ACTIVE
        ]);
        
        return $facultyCount + $staffCount;
    }
    
    /**
     * Check if designation can be deleted
     */
    public function canDelete($designationId)
    {
        $employeeCount = $this->getEmployeeCount($designationId);
        $subordinateCount = $this->count('reporting_to = :reporting_to', ['reporting_to' => $designationId]);
        
        return $employeeCount === 0 && $subordinateCount === 0;
    }
    
    /**
     * Get career progression path
     */
    public function getCareerPath($designationId)
    {
        $designation = $this->find($designationId);
        $path = [];
        
        // Get progression path (higher levels)
        $higherLevels = $this->where('level > :level AND status = :status', [
            'level' => $designation['level'],
            'status' => 'active'
        ], 'level ASC');
        
        $path['next_levels'] = $higherLevels;
        
        // Get previous levels
        $lowerLevels = $this->where('level < :level AND status = :status', [
            'level' => $designation['level'],
            'status' => 'active'
        ], 'level DESC');
        
        $path['previous_levels'] = $lowerLevels;
        
        return $path;
    }
}