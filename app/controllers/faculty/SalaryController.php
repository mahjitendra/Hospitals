<?php

/**
 * Salary Management Controller
 * 
 * Handles salary and payroll operations
 */
class SalaryController extends Controller
{
    private $salaryModel;
    private $facultyModel;
    private $payrollModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->salaryModel = new Salary();
        $this->facultyModel = new Faculty();
        $this->payrollModel = new Payroll();
    }
    
    /**
     * Display salary management dashboard
     */
    public function index()
    {
        $stats = $this->getSalaryStats();
        $recentPayrolls = $this->getRecentPayrolls();
        $pendingPayrolls = $this->getPendingPayrolls();
        
        $this->render('faculty/salary/index', [
            'title' => 'Salary Management',
            'stats' => $stats,
            'recentPayrolls' => $recentPayrolls,
            'pendingPayrolls' => $pendingPayrolls
        ]);
    }
    
    /**
     * Show faculty salary details
     */
    public function show($facultyId)
    {
        $faculty = $this->facultyModel->find($facultyId);
        if (!$faculty) {
            $this->flash('error', 'Faculty not found');
            $this->redirect('/faculty');
        }
        
        $salaryStructure = $this->salaryModel->getFacultySalaryStructure($facultyId);
        $payrollHistory = $this->payrollModel->getFacultyPayrolls($facultyId);
        $currentSalary = $this->salaryModel->getCurrentSalary($facultyId);
        
        $this->render('faculty/salary/show', [
            'title' => 'Salary Details',
            'faculty' => $faculty,
            'salaryStructure' => $salaryStructure,
            'payrollHistory' => $payrollHistory,
            'currentSalary' => $currentSalary
        ]);
    }
    
    /**
     * Show edit salary form
     */
    public function edit($facultyId)
    {
        $this->requirePermission('salary_manage');
        
        $faculty = $this->facultyModel->find($facultyId);
        if (!$faculty) {
            $this->flash('error', 'Faculty not found');
            $this->redirect('/faculty');
        }
        
        $currentSalary = $this->salaryModel->getCurrentSalary($facultyId);
        
        $this->render('faculty/salary/edit', [
            'title' => 'Edit Salary Structure',
            'faculty' => $faculty,
            'currentSalary' => $currentSalary
        ]);
    }
    
    /**
     * Update salary structure
     */
    public function update($facultyId)
    {
        $this->requirePermission('salary_manage');
        
        $faculty = $this->facultyModel->find($facultyId);
        if (!$faculty) {
            $this->flash('error', 'Faculty not found');
            $this->redirect('/faculty');
        }
        
        $data = $this->validate([
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'da' => 'required|numeric|min:0',
            'medical_allowance' => 'numeric|min:0',
            'transport_allowance' => 'numeric|min:0',
            'special_allowance' => 'numeric|min:0',
            'pf_deduction' => 'numeric|min:0',
            'esi_deduction' => 'numeric|min:0',
            'tax_deduction' => 'numeric|min:0',
            'other_deductions' => 'numeric|min:0',
            'effective_from' => 'required|date',
            'remarks' => 'max:500'
        ]);
        
        try {
            $this->salaryModel->beginTransaction();
            
            // Calculate totals
            $grossSalary = $data['basic_salary'] + $data['hra'] + $data['da'] + 
                          ($data['medical_allowance'] ?? 0) + ($data['transport_allowance'] ?? 0) + 
                          ($data['special_allowance'] ?? 0);
            
            $totalDeductions = ($data['pf_deduction'] ?? 0) + ($data['esi_deduction'] ?? 0) + 
                              ($data['tax_deduction'] ?? 0) + ($data['other_deductions'] ?? 0);
            
            $netSalary = $grossSalary - $totalDeductions;
            
            $data['faculty_id'] = $facultyId;
            $data['gross_salary'] = $grossSalary;
            $data['total_deductions'] = $totalDeductions;
            $data['net_salary'] = $netSalary;
            $data['updated_by'] = $this->user()['id'];
            
            // Create new salary record
            $salaryId = $this->salaryModel->create($data);
            
            // Update faculty base salary
            $this->facultyModel->update($facultyId, ['salary' => $netSalary]);
            
            $this->salaryModel->commit();
            
            $this->logActivity('salary_updated', "Updated salary for faculty: {$faculty['first_name']} {$faculty['last_name']}", $data);
            $this->flash('success', 'Salary structure updated successfully');
            
            $this->redirect('/faculty/salary/' . $facultyId);
            
        } catch (Exception $e) {
            $this->salaryModel->rollback();
            Logger::error('Salary update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update salary structure');
            $this->back();
        }
    }
    
    /**
     * Generate payroll
     */
    public function generatePayroll()
    {
        $this->requirePermission('payroll_generate');
        
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $departmentId = $this->input('department_id', '');
        
        try {
            $this->payrollModel->beginTransaction();
            
            $faculty = $this->getFacultyForPayroll($departmentId);
            $generatedCount = 0;
            
            foreach ($faculty as $member) {
                // Check if payroll already generated
                if ($this->payrollModel->isPayrollGenerated($member['id'], $year, $month)) {
                    continue;
                }
                
                $payrollData = $this->calculatePayroll($member, $year, $month);
                $this->payrollModel->create($payrollData);
                $generatedCount++;
            }
            
            $this->payrollModel->commit();
            
            $this->logActivity('payroll_generated', "Generated payroll for {$generatedCount} faculty members for {$year}-{$month}");
            
            return $this->json(['success' => true, 'message' => "Payroll generated for {$generatedCount} faculty members"]);
            
        } catch (Exception $e) {
            $this->payrollModel->rollback();
            Logger::error('Payroll generation failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to generate payroll']);
        }
    }
    
    /**
     * Show payroll report
     */
    public function payrollReport()
    {
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $department = $this->input('department', '');
        
        $payrolls = $this->getPayrollData($year, $month, $department);
        $summary = $this->getPayrollSummary($year, $month, $department);
        
        $this->render('faculty/salary/payroll_report', [
            'title' => 'Payroll Report',
            'payrolls' => $payrolls,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
            'department' => $department
        ]);
    }
    
    /**
     * Export payroll
     */
    public function exportPayroll()
    {
        $this->requirePermission('payroll_export');
        
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $format = $this->input('format', 'excel');
        
        $payrolls = $this->getPayrollData($year, $month);
        
        if ($format === 'excel') {
            return $this->exportPayrollExcel($payrolls, $year, $month);
        } elseif ($format === 'pdf') {
            return $this->generatePDF('faculty/salary/payroll_pdf', ['payrolls' => $payrolls], "payroll_{$year}_{$month}");
        }
        
        $this->flash('error', 'Invalid export format');
        $this->back();
    }
    
    /**
     * Calculate payroll for faculty
     */
    private function calculatePayroll($faculty, $year, $month)
    {
        $salary = $this->salaryModel->getCurrentSalary($faculty['id']);
        $attendance = $this->getAttendanceForPayroll($faculty['id'], $year, $month);
        $leaves = $this->getLeaveForPayroll($faculty['id'], $year, $month);
        
        // Calculate working days
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workingDays = $this->getWorkingDays($year, $month);
        $presentDays = $attendance['present_days'] ?? $workingDays;
        
        // Calculate salary based on attendance
        $dailySalary = $salary['net_salary'] / $workingDays;
        $earnedSalary = $dailySalary * $presentDays;
        
        // Deduct for unauthorized leaves
        $unauthorizedLeaves = $leaves['unauthorized'] ?? 0;
        $leaveDeduction = $dailySalary * $unauthorizedLeaves;
        
        $finalSalary = $earnedSalary - $leaveDeduction;
        
        return [
            'faculty_id' => $faculty['id'],
            'year' => $year,
            'month' => $month,
            'basic_salary' => $salary['basic_salary'],
            'gross_salary' => $salary['gross_salary'],
            'total_deductions' => $salary['total_deductions'],
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'leave_days' => $leaves['total'] ?? 0,
            'unauthorized_leaves' => $unauthorizedLeaves,
            'leave_deduction' => $leaveDeduction,
            'net_salary' => $finalSalary,
            'generated_by' => $this->user()['id'],
            'status' => 'generated'
        ];
    }
    
    /**
     * Get salary statistics
     */
    private function getSalaryStats()
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(DISTINCT f.id) as total_faculty,
                    AVG(s.net_salary) as average_salary,
                    MIN(s.net_salary) as min_salary,
                    MAX(s.net_salary) as max_salary,
                    SUM(p.net_salary) as total_payroll_current_month
                FROM faculty f
                LEFT JOIN salaries s ON f.id = s.faculty_id
                LEFT JOIN payrolls p ON f.id = p.faculty_id 
                    AND p.year = :year AND p.month = :month
                WHERE f.status = 'active'";
        
        return $db->fetch($sql, ['year' => date('Y'), 'month' => date('m')]);
    }
    
    /**
     * Get recent payrolls
     */
    private function getRecentPayrolls()
    {
        $db = Database::getInstance();
        $sql = "SELECT p.*, f.first_name, f.last_name, f.employee_id 
                FROM payrolls p 
                JOIN faculty f ON p.faculty_id = f.id 
                ORDER BY p.created_at DESC 
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get pending payrolls
     */
    private function getPendingPayrolls()
    {
        $db = Database::getInstance();
        $sql = "SELECT p.*, f.first_name, f.last_name, f.employee_id 
                FROM payrolls p 
                JOIN faculty f ON p.faculty_id = f.id 
                WHERE p.status = 'generated'
                ORDER BY p.created_at ASC";
        
        return $db->fetchAll($sql);
    }
}