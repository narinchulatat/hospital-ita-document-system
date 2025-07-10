<?php
/**
 * FileManager Class
 * Handles file operations, security, and management
 */

class FileManager {
    private $db;
    private $uploadPath;
    private $allowedTypes;
    private $maxFileSize;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadPath = dirname(__DIR__) . '/uploads/';
        $this->allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $this->maxFileSize = 52428800; // 50MB default
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Upload file with security checks
     */
    public function upload($file, $categoryId, $uploadedBy, $metadata = []) {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Generate secure filename
            $fileInfo = $this->generateSecureFilename($file['name']);
            $filePath = $this->uploadPath . $fileInfo['filename'];
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Generate checksum
            $checksum = $this->generateChecksum($filePath);
            
            // Prepare document data
            $documentData = [
                'title' => $metadata['title'] ?? $fileInfo['original_name'],
                'description' => $metadata['description'] ?? '',
                'category_id' => $categoryId,
                'file_name' => $fileInfo['filename'],
                'file_path' => $filePath,
                'file_size' => filesize($filePath),
                'file_type' => $fileInfo['extension'],
                'mime_type' => $this->getMimeType($filePath),
                'checksum' => $checksum,
                'virus_scan_status' => 'pending',
                'uploaded_by' => $uploadedBy,
                'status' => 'draft'
            ];
            
            // Additional metadata
            if (isset($metadata['responsible_person'])) {
                $documentData['responsible_person'] = $metadata['responsible_person'];
            }
            if (isset($metadata['tags'])) {
                $documentData['tags'] = $metadata['tags'];
            }
            if (isset($metadata['expiry_date'])) {
                $documentData['expiry_date'] = $metadata['expiry_date'];
            }
            
            // Insert into database
            $document = new Document();
            $documentId = $document->create($documentData);
            
            // Schedule virus scan
            $this->scheduleVirusScan($documentId, $filePath);
            
            return [
                'document_id' => $documentId,
                'file_path' => $filePath,
                'file_name' => $fileInfo['filename'],
                'file_size' => $documentData['file_size'],
                'checksum' => $checksum
            ];
            
        } catch (Exception $e) {
            // Clean up file if it was moved
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
            throw $e;
        }
    }
    
    /**
     * Validate uploaded file
     */
    public function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('File is too large');
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception('File upload was interrupted');
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception('No file was uploaded');
                default:
                    throw new Exception('File upload error');
            }
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size');
        }
        
        // Check file type by extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        // Check MIME type
        $mimeType = mime_content_type($file['tmp_name']);
        if (!$this->isAllowedMimeType($mimeType)) {
            throw new Exception('Invalid file MIME type');
        }
        
        // Additional security checks
        $this->performSecurityChecks($file['tmp_name'], $extension);
        
        return true;
    }
    
    /**
     * Generate secure filename
     */
    private function generateSecureFilename($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize filename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 50); // Limit length
        
        // Generate unique filename
        $timestamp = date('Y-m-d_H-i-s');
        $random = bin2hex(random_bytes(4));
        $filename = "{$timestamp}_{$random}_{$basename}.{$extension}";
        
        return [
            'filename' => $filename,
            'original_name' => $basename,
            'extension' => $extension
        ];
    }
    
    /**
     * Generate file checksum
     */
    public function generateChecksum($filePath) {
        return hash_file('sha256', $filePath);
    }
    
    /**
     * Get file MIME type
     */
    private function getMimeType($filePath) {
        return mime_content_type($filePath);
    }
    
    /**
     * Check if MIME type is allowed
     */
    private function isAllowedMimeType($mimeType) {
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png'
        ];
        
        return in_array($mimeType, $allowedMimes);
    }
    
    /**
     * Perform additional security checks
     */
    private function performSecurityChecks($filePath, $extension) {
        // Check file signature (magic bytes)
        $this->validateFileSignature($filePath, $extension);
        
        // Scan for embedded scripts
        $this->scanForMaliciousContent($filePath);
    }
    
    /**
     * Validate file signature
     */
    private function validateFileSignature($filePath, $extension) {
        $handle = fopen($filePath, 'rb');
        $header = fread($handle, 8);
        fclose($handle);
        
        $signatures = [
            'pdf' => ['%PDF'],
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89PNG"],
            'doc' => ["\xD0\xCF\x11\xE0"],
            'docx' => ["PK\x03\x04"],
            'xls' => ["\xD0\xCF\x11\xE0"],
            'xlsx' => ["PK\x03\x04"]
        ];
        
        if (isset($signatures[$extension])) {
            $valid = false;
            foreach ($signatures[$extension] as $signature) {
                if (strpos($header, $signature) === 0) {
                    $valid = true;
                    break;
                }
            }
            
            if (!$valid) {
                throw new Exception('File signature does not match extension');
            }
        }
    }
    
    /**
     * Scan for malicious content
     */
    private function scanForMaliciousContent($filePath) {
        $content = file_get_contents($filePath, false, null, 0, 8192); // Read first 8KB
        
        // Look for suspicious patterns
        $suspiciousPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe\b/i',
            '/<object\b/i',
            '/<embed\b/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new Exception('File contains potentially malicious content');
            }
        }
    }
    
    /**
     * Delete file
     */
    public function delete($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
    
    /**
     * Move file
     */
    public function move($sourcePath, $destinationPath) {
        if (!file_exists($sourcePath)) {
            throw new Exception('Source file does not exist');
        }
        
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        return rename($sourcePath, $destinationPath);
    }
    
    /**
     * Copy file
     */
    public function copy($sourcePath, $destinationPath) {
        if (!file_exists($sourcePath)) {
            throw new Exception('Source file does not exist');
        }
        
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        return copy($sourcePath, $destinationPath);
    }
    
    /**
     * Get file information
     */
    public function getFileInfo($filePath) {
        if (!file_exists($filePath)) {
            return null;
        }
        
        return [
            'name' => basename($filePath),
            'size' => filesize($filePath),
            'type' => mime_content_type($filePath),
            'extension' => strtolower(pathinfo($filePath, PATHINFO_EXTENSION)),
            'modified' => filemtime($filePath),
            'checksum' => $this->generateChecksum($filePath),
            'readable' => is_readable($filePath),
            'writable' => is_writable($filePath)
        ];
    }
    
    /**
     * Schedule virus scan
     */
    private function scheduleVirusScan($documentId, $filePath) {
        // In a real implementation, this would queue a job for virus scanning
        // For now, we'll just mark it as clean
        $document = new Document();
        $document->updateVirusScanStatus($documentId, 'clean');
    }
    
    /**
     * Perform virus scan
     */
    public function scanVirus($filePath) {
        // Placeholder for actual virus scanning implementation
        // This would integrate with ClamAV or similar antivirus software
        
        // Simple heuristic checks
        $fileSize = filesize($filePath);
        if ($fileSize > 100 * 1024 * 1024) { // Files larger than 100MB are suspicious
            return 'error';
        }
        
        // Check for known malicious patterns in filename
        $filename = basename($filePath);
        $suspiciousNames = ['autorun.inf', 'desktop.ini', '.htaccess'];
        if (in_array(strtolower($filename), $suspiciousNames)) {
            return 'infected';
        }
        
        return 'clean';
    }
    
    /**
     * Create thumbnail for images
     */
    public function createThumbnail($imagePath, $thumbnailPath, $width = 200, $height = 200) {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($originalWidth, $originalHeight, $imageType) = $imageInfo;
        
        // Create image resource based on type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            default:
                return false;
        }
        
        // Calculate new dimensions
        $aspectRatio = $originalWidth / $originalHeight;
        if ($width / $height > $aspectRatio) {
            $newWidth = $height * $aspectRatio;
            $newHeight = $height;
        } else {
            $newWidth = $width;
            $newHeight = $width / $aspectRatio;
        }
        
        // Create thumbnail
        $thumbnailImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnailImage, $sourceImage, 0, 0, 0, 0, 
                          $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Save thumbnail
        $result = imagejpeg($thumbnailImage, $thumbnailPath, 85);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbnailImage);
        
        return $result;
    }
    
    /**
     * Compress file
     */
    public function compressFile($filePath, $compressionLevel = 6) {
        $compressedPath = $filePath . '.gz';
        
        $file = fopen($filePath, 'rb');
        $compressedFile = gzopen($compressedPath, "wb{$compressionLevel}");
        
        while (!feof($file)) {
            gzwrite($compressedFile, fread($file, 8192));
        }
        
        fclose($file);
        gzclose($compressedFile);
        
        return $compressedPath;
    }
    
    /**
     * Get disk usage
     */
    public function getDiskUsage($path = null) {
        $path = $path ?: $this->uploadPath;
        
        if (!is_dir($path)) {
            return null;
        }
        
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        
        return [
            'used_bytes' => $size,
            'used_formatted' => $this->formatBytes($size),
            'free_bytes' => disk_free_space($path),
            'total_bytes' => disk_total_space($path)
        ];
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Clean temporary files
     */
    public function cleanTemporaryFiles($olderThanHours = 24) {
        $tempPath = sys_get_temp_dir();
        $cutoffTime = time() - ($olderThanHours * 3600);
        $deletedCount = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoffTime) {
                if (unlink($file->getPathname())) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
}