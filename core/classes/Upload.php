<?php

/**
 * File Upload Handler Class
 * 
 * Handles file uploads with validation and security
 */
class Upload
{
    private $config;
    private $errors = [];
    
    public function __construct()
    {
        $this->config = config('app.upload');
    }
    
    /**
     * Handle file upload
     */
    public function handle($field, $destination = 'uploads', $allowedTypes = null)
    {
        if (!isset($_FILES[$field])) {
            throw new Exception('No file uploaded');
        }
        
        $file = $_FILES[$field];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadError($file['error']));
        }
        
        // Validate file
        $this->validateFile($file, $allowedTypes);
        
        if (!empty($this->errors)) {
            throw new Exception(implode(', ', $this->errors));
        }
        
        // Generate unique filename
        $filename = $this->generateFilename($file['name']);
        
        // Create destination directory
        $uploadPath = $this->createUploadPath($destination);
        $filePath = $uploadPath . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Set proper permissions
        chmod($filePath, 0644);
        
        return [
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $filePath,
            'url' => $this->getFileUrl($destination, $filename),
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
    
    /**
     * Handle multiple file uploads
     */
    public function handleMultiple($field, $destination = 'uploads', $allowedTypes = null)
    {
        if (!isset($_FILES[$field])) {
            throw new Exception('No files uploaded');
        }
        
        $files = $_FILES[$field];
        $uploadedFiles = [];
        
        // Normalize file array structure
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                try {
                    $uploadedFiles[] = $this->handleSingleFile($file, $destination, $allowedTypes);
                } catch (Exception $e) {
                    // Log error but continue with other files
                    Logger::error('File upload failed: ' . $e->getMessage());
                }
            }
        }
        
        return $uploadedFiles;
    }
    
    /**
     * Handle single file from multiple upload
     */
    private function handleSingleFile($file, $destination, $allowedTypes)
    {
        // Validate file
        $this->validateFile($file, $allowedTypes);
        
        if (!empty($this->errors)) {
            throw new Exception(implode(', ', $this->errors));
        }
        
        // Generate unique filename
        $filename = $this->generateFilename($file['name']);
        
        // Create destination directory
        $uploadPath = $this->createUploadPath($destination);
        $filePath = $uploadPath . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Set proper permissions
        chmod($filePath, 0644);
        
        return [
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $filePath,
            'url' => $this->getFileUrl($destination, $filename),
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file, $allowedTypes = null)
    {
        $this->errors = [];
        
        // Check file size
        if ($file['size'] > $this->config['max_file_size']) {
            $maxSize = $this->formatBytes($this->config['max_file_size']);
            $this->errors[] = "File size exceeds maximum allowed size of {$maxSize}";
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = $allowedTypes ?? $this->config['allowed_extensions'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $this->errors[] = "File type '{$extension}' is not allowed";
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!$this->isAllowedMimeType($mimeType, $extension)) {
            $this->errors[] = "Invalid file type";
        }
        
        // Check for malicious content
        if ($this->containsMaliciousContent($file['tmp_name'])) {
            $this->errors[] = "File contains potentially malicious content";
        }
    }
    
    /**
     * Check if MIME type is allowed
     */
    private function isAllowedMimeType($mimeType, $extension)
    {
        $allowedMimes = [
            'jpg' => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'txt' => ['text/plain'],
            'csv' => ['text/csv', 'application/csv'],
            'zip' => ['application/zip'],
            'mp4' => ['video/mp4'],
            'mp3' => ['audio/mpeg'],
        ];
        
        return isset($allowedMimes[$extension]) && in_array($mimeType, $allowedMimes[$extension]);
    }
    
    /**
     * Check for malicious content
     */
    private function containsMaliciousContent($filePath)
    {
        // Read first few bytes to check for malicious patterns
        $handle = fopen($filePath, 'rb');
        $content = fread($handle, 1024);
        fclose($handle);
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            return true;
        }
        
        // Check for script tags
        if (stripos($content, '<script') !== false) {
            return true;
        }
        
        // Check for executable signatures
        $signatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xCA\xFE\xBA\xBE", // Java class file
        ];
        
        foreach ($signatures as $signature) {
            if (strpos($content, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate unique filename
     */
    private function generateFilename($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize basename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 50); // Limit length
        
        // Generate unique identifier
        $uniqueId = uniqid() . '_' . mt_rand(1000, 9999);
        
        return $basename . '_' . $uniqueId . '.' . $extension;
    }
    
    /**
     * Create upload directory path
     */
    private function createUploadPath($destination)
    {
        $basePath = __DIR__ . '/../../public/uploads';
        $uploadPath = $basePath . '/' . trim($destination, '/');
        
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        return $uploadPath;
    }
    
    /**
     * Get file URL
     */
    private function getFileUrl($destination, $filename)
    {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/uploads/' . trim($destination, '/') . '/' . $filename;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadError($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds the maximum allowed size',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the maximum allowed size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Delete uploaded file
     */
    public function delete($filePath)
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * Resize image
     */
    public function resizeImage($filePath, $width, $height, $quality = 90)
    {
        if (!extension_loaded('gd')) {
            throw new Exception('GD extension is required for image resizing');
        }
        
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            throw new Exception('Invalid image file');
        }
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $imageType = $imageInfo[2];
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($width / $originalWidth, $height / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        // Create image resource
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            default:
                throw new Exception('Unsupported image type');
        }
        
        // Create new image
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Save resized image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $filePath, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $filePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $filePath);
                break;
        }
        
        // Clean up memory
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
    }
}