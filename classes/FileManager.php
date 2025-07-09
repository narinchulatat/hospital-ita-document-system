<?php
/**
 * FileManager Class
 * Handles file operations and management
 */

class FileManager {
    private $db;
    private $uploadPath;
    private $allowedTypes;
    private $maxFileSize;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadPath = UPLOAD_PATH;
        $this->allowedTypes = ALLOWED_FILE_TYPES;
        $this->maxFileSize = MAX_FILE_SIZE;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Upload file
     */
    public function uploadFile($file, $subDir = '') {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('ไม่พบไฟล์ที่อัปโหลด');
        }
        
        // Validate file
        $this->validateFile($file);
        
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file['name'], $subDir);
        $targetPath = $this->uploadPath . $subDir . $filename;
        
        // Create subdirectory if needed
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('ไม่สามารถอัปโหลดไฟล์ได้');
        }
        
        return [
            'filename' => $filename,
            'file_path' => $targetPath,
            'file_size' => filesize($targetPath),
            'file_type' => $this->getFileExtension($filename),
            'mime_type' => $this->getMimeType($targetPath),
            'original_name' => $file['name']
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('ขนาดไฟล์ใหญ่เกินไป (สูงสุด ' . $this->formatFileSize($this->maxFileSize) . ')');
        }
        
        // Check file type
        $extension = $this->getFileExtension($file['name']);
        if (!in_array(strtolower($extension), $this->allowedTypes)) {
            throw new Exception('ประเภทไฟล์ไม่ได้รับอนุญาต');
        }
        
        // Check MIME type
        $mimeType = $this->getMimeType($file['tmp_name']);
        if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
            throw new Exception('ประเภทไฟล์ไม่ถูกต้อง');
        }
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('เกิดข้อผิดพลาดในการอัปโหลดไฟล์');
        }
        
        return true;
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName, $subDir = '') {
        $extension = $this->getFileExtension($originalName);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize filename
        $basename = $this->sanitizeFilename($basename);
        
        $timestamp = date('Y-m-d_H-i-s');
        $randomString = substr(md5(uniqid(rand(), true)), 0, 8);
        
        $filename = "{$basename}_{$timestamp}_{$randomString}.{$extension}";
        
        // Ensure uniqueness
        $counter = 1;
        $originalFilename = $filename;
        while (file_exists($this->uploadPath . $subDir . $filename)) {
            $filename = "{$basename}_{$timestamp}_{$randomString}_({$counter}).{$extension}";
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename) {
        // Remove or replace unsafe characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        $filename = trim($filename, '_');
        
        // Limit length
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }
        
        return $filename ?: 'file';
    }
    
    /**
     * Get file extension
     */
    private function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Get MIME type
     */
    private function getMimeType($filePath) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($finfo, $filePath);
        } elseif (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        } else {
            // Fallback based on extension
            $extension = $this->getFileExtension($filePath);
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png'
            ];
            
            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }
    }
    
    /**
     * Delete file
     */
    public function deleteFile($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Move file
     */
    public function moveFile($sourcePath, $destinationPath) {
        // Create destination directory if needed
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        return rename($sourcePath, $destinationPath);
    }
    
    /**
     * Copy file
     */
    public function copyFile($sourcePath, $destinationPath) {
        // Create destination directory if needed
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        return copy($sourcePath, $destinationPath);
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filePath) {
        if (!file_exists($filePath)) {
            return null;
        }
        
        return [
            'name' => basename($filePath),
            'size' => filesize($filePath),
            'type' => $this->getFileExtension($filePath),
            'mime_type' => $this->getMimeType($filePath),
            'modified' => filemtime($filePath),
            'is_readable' => is_readable($filePath),
            'is_writable' => is_writable($filePath)
        ];
    }
    
    /**
     * Format file size
     */
    public function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Get directory size
     */
    public function getDirectorySize($directory) {
        $size = 0;
        
        if (is_dir($directory)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
    
    /**
     * Clean temporary files
     */
    public function cleanTempFiles($olderThanHours = 24) {
        $tempPath = TEMP_PATH;
        
        if (!is_dir($tempPath)) {
            return 0;
        }
        
        $cutoffTime = time() - ($olderThanHours * 3600);
        $deletedCount = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempPath),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoffTime) {
                if (unlink($file->getRealPath())) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Create directory
     */
    public function createDirectory($path, $permissions = 0755) {
        if (!is_dir($path)) {
            return mkdir($path, $permissions, true);
        }
        return true;
    }
    
    /**
     * Get file icon class
     */
    public function getFileIcon($filename) {
        global $FILE_TYPE_ICONS;
        
        $extension = $this->getFileExtension($filename);
        return $FILE_TYPE_ICONS[$extension] ?? $FILE_TYPE_ICONS['default'];
    }
    
    /**
     * Scan directory for files
     */
    public function scanDirectory($directory, $recursive = false) {
        $files = [];
        
        if (!is_dir($directory)) {
            return $files;
        }
        
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );
        } else {
            $iterator = new DirectoryIterator($directory);
        }
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'type' => $this->getFileExtension($file->getFilename())
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * Create ZIP archive
     */
    public function createZipArchive($files, $archivePath) {
        $zip = new ZipArchive();
        
        if ($zip->open($archivePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("ไม่สามารถสร้างไฟล์ ZIP ได้");
        }
        
        foreach ($files as $file) {
            if (is_array($file)) {
                $zip->addFile($file['path'], $file['name']);
            } else {
                $zip->addFile($file, basename($file));
            }
        }
        
        $zip->close();
        
        return $archivePath;
    }
    
    /**
     * Extract ZIP archive
     */
    public function extractZipArchive($archivePath, $extractTo) {
        $zip = new ZipArchive();
        
        if ($zip->open($archivePath) !== TRUE) {
            throw new Exception("ไม่สามารถเปิดไฟล์ ZIP ได้");
        }
        
        if (!is_dir($extractTo)) {
            mkdir($extractTo, 0755, true);
        }
        
        $result = $zip->extractTo($extractTo);
        $zip->close();
        
        if (!$result) {
            throw new Exception("ไม่สามารถแตกไฟล์ ZIP ได้");
        }
        
        return true;
    }
    
    /**
     * Generate thumbnail for image
     */
    public function generateThumbnail($imagePath, $thumbnailPath, $width = 150, $height = 150) {
        $imageInfo = getimagesize($imagePath);
        
        if (!$imageInfo) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        
        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // Calculate thumbnail dimensions
        $aspectRatio = $sourceWidth / $sourceHeight;
        
        if ($aspectRatio > 1) {
            $thumbWidth = $width;
            $thumbHeight = $width / $aspectRatio;
        } else {
            $thumbHeight = $height;
            $thumbWidth = $height * $aspectRatio;
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled(
            $thumbnail, $sourceImage,
            0, 0, 0, 0,
            $thumbWidth, $thumbHeight,
            $sourceWidth, $sourceHeight
        );
        
        // Create directory for thumbnail
        $thumbnailDir = dirname($thumbnailPath);
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }
        
        // Save thumbnail
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case 'image/png':
                $result = imagepng($thumbnail, $thumbnailPath);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $result;
    }
    
    /**
     * Check if file is image
     */
    public function isImage($filePath) {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $extension = $this->getFileExtension($filePath);
        
        return in_array($extension, $imageExtensions);
    }
    
    /**
     * Check disk space
     */
    public function getDiskSpace($directory = null) {
        $directory = $directory ?: $this->uploadPath;
        
        return [
            'free' => disk_free_space($directory),
            'total' => disk_total_space($directory),
            'used' => disk_total_space($directory) - disk_free_space($directory)
        ];
    }
    
    /**
     * Get storage statistics
     */
    public function getStorageStatistics() {
        $stats = [];
        
        // Upload directory size
        $stats['upload_size'] = $this->getDirectorySize($this->uploadPath);
        
        // Backup directory size
        $stats['backup_size'] = $this->getDirectorySize(BACKUP_PATH);
        
        // Temp directory size
        $stats['temp_size'] = $this->getDirectorySize(TEMP_PATH);
        
        // Disk space
        $diskSpace = $this->getDiskSpace();
        $stats['disk_free'] = $diskSpace['free'];
        $stats['disk_total'] = $diskSpace['total'];
        $stats['disk_used'] = $diskSpace['used'];
        
        // File count by type
        $files = $this->scanDirectory($this->uploadPath, true);
        $stats['file_count_by_type'] = [];
        
        foreach ($files as $file) {
            $type = $file['type'];
            if (!isset($stats['file_count_by_type'][$type])) {
                $stats['file_count_by_type'][$type] = 0;
            }
            $stats['file_count_by_type'][$type]++;
        }
        
        return $stats;
    }
}