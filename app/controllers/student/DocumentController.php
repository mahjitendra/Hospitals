<?php

/**
 * Student Document Controller
 * 
 * Handles student document management
 */
class DocumentController extends Controller
{
    private $documentModel;
    private $studentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->documentModel = new StudentDocument();
        $this->studentModel = new Student();
    }
    
    /**
     * Display student documents
     */
    public function index($studentId)
    {
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        // Check permission
        if (!$this->canAccessStudent($studentId)) {
            $this->flash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
        
        $documents = $this->documentModel->where('student_id = :student_id', ['student_id' => $studentId], 'created_at DESC');
        
        $this->render('student/documents/index', [
            'title' => 'Student Documents',
            'student' => $student,
            'documents' => $documents
        ]);
    }
    
    /**
     * Show upload form
     */
    public function create($studentId)
    {
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        // Check permission
        if (!$this->canAccessStudent($studentId)) {
            $this->flash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
        
        $documentTypes = $this->getDocumentTypes();
        
        $this->render('student/documents/create', [
            'title' => 'Upload Document',
            'student' => $student,
            'documentTypes' => $documentTypes
        ]);
    }
    
    /**
     * Store document
     */
    public function store($studentId)
    {
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        // Check permission
        if (!$this->canAccessStudent($studentId)) {
            $this->flash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
        
        $data = $this->validate([
            'document_type' => 'required|in:birth_certificate,transfer_certificate,marksheet,photo,aadhar,passport,medical_certificate,other',
            'document_name' => 'required|min:2|max:100',
            'description' => 'max:255'
        ]);
        
        if (!$this->request->hasFile('document')) {
            $this->flash('error', 'Please select a file to upload');
            $this->back();
            return;
        }
        
        try {
            // Upload document
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $document = $this->upload('document', 'students/documents', $allowedTypes);
            
            $data['student_id'] = $studentId;
            $data['filename'] = $document['filename'];
            $data['original_name'] = $document['original_name'];
            $data['file_size'] = $document['size'];
            $data['file_type'] = $document['type'];
            $data['uploaded_by'] = $this->user()['id'];
            
            $documentId = $this->documentModel->create($data);
            
            $this->logActivity('document_uploaded', "Uploaded document for student: {$student['first_name']} {$student['last_name']}", $data);
            $this->flash('success', 'Document uploaded successfully');
            
            $this->redirect('/students/' . $studentId . '/documents');
            
        } catch (Exception $e) {
            Logger::error('Document upload failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to upload document: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Download document
     */
    public function download($documentId)
    {
        $document = $this->documentModel->find($documentId);
        if (!$document) {
            $this->flash('error', 'Document not found');
            $this->redirect('/students');
        }
        
        // Check permission
        if (!$this->canAccessStudent($document['student_id'])) {
            $this->flash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
        
        $filePath = public_path('uploads/students/documents/' . $document['filename']);
        
        if (!file_exists($filePath)) {
            $this->flash('error', 'File not found');
            $this->redirect('/students/' . $document['student_id'] . '/documents');
        }
        
        $this->logActivity('document_downloaded', "Downloaded document: {$document['document_name']}");
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $document['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    /**
     * Delete document
     */
    public function destroy($documentId)
    {
        $document = $this->documentModel->find($documentId);
        if (!$document) {
            return $this->json(['success' => false, 'message' => 'Document not found']);
        }
        
        // Check permission
        if (!$this->canAccessStudent($document['student_id'])) {
            return $this->json(['success' => false, 'message' => 'Access denied']);
        }
        
        try {
            // Delete file
            $filePath = public_path('uploads/students/documents/' . $document['filename']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete record
            $this->documentModel->delete($documentId);
            
            $this->logActivity('document_deleted', "Deleted document: {$document['document_name']}");
            
            return $this->json(['success' => true, 'message' => 'Document deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Document deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete document']);
        }
    }
    
    /**
     * Check if user can access student data
     */
    private function canAccessStudent($studentId)
    {
        $user = $this->user();
        
        // Admin can access all
        if ($this->hasRole(ROLE_ADMIN)) {
            return true;
        }
        
        // Student can access own data
        if ($this->hasRole(ROLE_STUDENT)) {
            $student = $this->studentModel->findByUserId($user['id']);
            return $student && $student['id'] == $studentId;
        }
        
        // Parent can access their child's data
        if ($this->hasRole(ROLE_PARENT)) {
            $parentModel = new ParentModel();
            $children = $parentModel->getChildren($user['id']);
            return in_array($studentId, array_column($children, 'id'));
        }
        
        // Faculty with permission can access
        if ($this->hasPermission('student_view')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get document types
     */
    private function getDocumentTypes()
    {
        return [
            'birth_certificate' => 'Birth Certificate',
            'transfer_certificate' => 'Transfer Certificate',
            'marksheet' => 'Previous Marksheet',
            'photo' => 'Photograph',
            'aadhar' => 'Aadhar Card',
            'passport' => 'Passport',
            'medical_certificate' => 'Medical Certificate',
            'other' => 'Other Document'
        ];
    }
}