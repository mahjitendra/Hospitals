<?php

/**
 * Designation Management Controller
 * 
 * Handles job designations and hierarchy
 */
class DesignationController extends Controller
{
    private $designationModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->designationModel = new Designation();
    }
    
    /**
     * Display designations list
     */
    public function index()
    {
        $designations = $this->designationModel->all('level DESC, designation_name ASC');
        
        $this->render('faculty/designations/index', [
            'title' => 'Designation Management',
            'designations' => $designations
        ]);
    }
    
    /**
     * Show create designation form
     */
    public function create()
    {
        $this->requirePermission('designation_create');
        
        $this->render('faculty/designations/create', [
            'title' => 'Create Designation'
        ]);
    }
    
    /**
     * Store new designation
     */
    public function store()
    {
        $this->requirePermission('designation_create');
        
        $data = $this->validate([
            'designation_name' => 'required|min:2|max:100|unique:designations',
            'description' => 'min:10',
            'level' => 'required|numeric|min:1|max:10',
            'min_qualification' => 'required|min:2',
            'min_experience' => 'required|numeric|min:0',
            'responsibilities' => 'min:10',
            'reporting_to' => 'exists:designations,id',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $designationId = $this->designationModel->create($data);
            
            $this->logActivity('designation_created', "Created designation: {$data['designation_name']}", $data);
            $this->flash('success', 'Designation created successfully');
            
            $this->redirect('/faculty/designations');
            
        } catch (Exception $e) {
            Logger::error('Designation creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create designation');
            $this->back();
        }
    }
    
    /**
     * Show designation details
     */
    public function show($id)
    {
        $designation = $this->designationModel->find($id);
        if (!$designation) {
            $this->flash('error', 'Designation not found');
            $this->redirect('/faculty/designations');
        }
        
        $designation['employees'] = $this->getDesignationEmployees($id);
        $designation['reporting_structure'] = $this->getReportingStructure($id);
        
        $this->render('faculty/designations/show', [
            'title' => 'Designation Details',
            'designation' => $designation
        ]);
    }
    
    /**
     * Show edit designation form
     */
    public function edit($id)
    {
        $this->requirePermission('designation_edit');
        
        $designation = $this->designationModel->find($id);
        if (!$designation) {
            $this->flash('error', 'Designation not found');
            $this->redirect('/faculty/designations');
        }
        
        $designations = $this->designationModel->where('id != :id', ['id' => $id], 'designation_name ASC');
        
        $this->render('faculty/designations/edit', [
            'title' => 'Edit Designation',
            'designation' => $designation,
            'designations' => $designations
        ]);
    }
    
    /**
     * Update designation
     */
    public function update($id)
    {
        $this->requirePermission('designation_edit');
        
        $designation = $this->designationModel->find($id);
        if (!$designation) {
            $this->flash('error', 'Designation not found');
            $this->redirect('/faculty/designations');
        }
        
        $data = $this->validate([
            'designation_name' => 'required|min:2|max:100',
            'description' => 'min:10',
            'level' => 'required|numeric|min:1|max:10',
            'min_qualification' => 'required|min:2',
            'min_experience' => 'required|numeric|min:0',
            'responsibilities' => 'min:10',
            'reporting_to' => 'exists:designations,id',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            // Check name uniqueness
            $existingDesignation = $this->designationModel->findBy('designation_name', $data['designation_name']);
            if ($existingDesignation && $existingDesignation['id'] != $id) {
                throw new Exception('Designation name already exists');
            }
            
            $this->designationModel->update($id, $data);
            
            $this->logActivity('designation_updated', "Updated designation: {$data['designation_name']}", $data);
            $this->flash('success', 'Designation updated successfully');
            
            $this->redirect('/faculty/designations');
            
        } catch (Exception $e) {
            Logger::error('Designation update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update designation: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Delete designation
     */
    public function destroy($id)
    {
        $this->requirePermission('designation_delete');
        
        $designation = $this->designationModel->find($id);
        if (!$designation) {
            return $this->json(['success' => false, 'message' => 'Designation not found']);
        }
        
        // Check if designation is in use
        $employeeCount = $this->getDesignationEmployeeCount($id);
        if ($employeeCount > 0) {
            return $this->json(['success' => false, 'message' => 'Cannot delete designation that is assigned to employees']);
        }
        
        try {
            $this->designationModel->delete($id);
            
            $this->logActivity('designation_deleted', "Deleted designation: {$designation['designation_name']}", $designation);
            
            return $this->json(['success' => true, 'message' => 'Designation deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Designation deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete designation']);
        }
    }
    
    /**
     * Get designation hierarchy
     */
    public function hierarchy()
    {
        $hierarchy = $this->designationModel->getHierarchy();
        
        return $this->json($hierarchy);
    }
    
    /**
     * Get designation employees
     */
    private function getDesignationEmployees($designationId)
    {
        $db = Database::getInstance();
        $sql = "SELECT f.*, d.department_name 
                FROM faculty f 
                LEFT JOIN departments d ON f.department_id = d.id 
                WHERE f.designation_id = :designation_id AND f.status = 'active'
                ORDER BY f.first_name";
        
        return $db->fetchAll($sql, ['designation_id' => $designationId]);
    }
    
    /**
     * Get reporting structure
     */
    private function getReportingStructure($designationId)
    {
        $designation = $this->designationModel->find($designationId);
        $structure = [];
        
        // Get subordinates
        $subordinates = $this->designationModel->where('reporting_to = :id', ['id' => $designationId]);
        $structure['subordinates'] = $subordinates;
        
        // Get supervisor
        if ($designation['reporting_to']) {
            $structure['supervisor'] = $this->designationModel->find($designation['reporting_to']);
        }
        
        return $structure;
    }
    
    /**
     * Get designation employee count
     */
    private function getDesignationEmployeeCount($designationId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM faculty WHERE designation_id = :designation_id";
        $result = $db->fetch($sql, ['designation_id' => $designationId]);
        return $result['count'] ?? 0;
    }
}