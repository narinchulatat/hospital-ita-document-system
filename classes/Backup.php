<?php
/**
 * Backup Class
 * Handles system backup and restore operations
 */

class Backup {
    private $db;
    private $backupPath;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->backupPath = BACKUP_PATH;
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Create database backup
     */
    public function createDatabaseBackup($includeFiles = false, $type = BACKUP_MANUAL, $createdBy = null) {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_database_{$timestamp}.sql";
            $filePath = $this->backupPath . $filename;
            
            // Create backup record
            $backupData = [
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => 0,
                'backup_type' => $type,
                'includes_files' => $includeFiles ? 1 : 0,
                'status' => BACKUP_CREATING,
                'created_by' => $createdBy ?: getCurrentUserId()
            ];
            
            $backupId = $this->db->insert('backups', $backupData);
            
            // Generate SQL dump
            $sqlDump = $this->generateSQLDump();
            
            // Write to file
            file_put_contents($filePath, $sqlDump);
            $fileSize = filesize($filePath);
            
            if ($includeFiles) {
                // Create archive with files
                $archiveFilename = "backup_complete_{$timestamp}.zip";
                $archivePath = $this->backupPath . $archiveFilename;
                
                $this->createCompleteBackup($filePath, $archivePath);
                
                // Update backup record with archive info
                $this->db->update('backups', [
                    'filename' => $archiveFilename,
                    'file_path' => $archivePath,
                    'file_size' => filesize($archivePath),
                    'status' => BACKUP_COMPLETED,
                    'completed_at' => date('Y-m-d H:i:s')
                ], ['id' => $backupId]);
                
                // Remove temporary SQL file
                unlink($filePath);
            } else {
                // Update backup record
                $this->db->update('backups', [
                    'file_size' => $fileSize,
                    'status' => BACKUP_COMPLETED,
                    'completed_at' => date('Y-m-d H:i:s')
                ], ['id' => $backupId]);
            }
            
            // Log activity
            logActivity(ACTION_CREATE, 'backups', $backupId);
            
            return $backupId;
            
        } catch (Exception $e) {
            // Update backup status to failed
            if (isset($backupId)) {
                $this->db->update('backups', [
                    'status' => BACKUP_FAILED,
                    'completed_at' => date('Y-m-d H:i:s')
                ], ['id' => $backupId]);
            }
            
            error_log("Backup creation failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate SQL dump
     */
    private function generateSQLDump() {
        $sql = "-- Hospital ITA Document System Backup\n";
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "START TRANSACTION;\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        
        // Get all tables
        $tables = $this->db->fetchAll("SHOW TABLES");
        $dbName = DB_NAME;
        
        foreach ($tables as $table) {
            $tableName = $table["Tables_in_{$dbName}"];
            
            // Get table structure
            $createTable = $this->db->fetch("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "-- Table structure for table `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $rows = $this->db->fetchAll("SELECT * FROM `{$tableName}`");
            
            if (!empty($rows)) {
                $sql .= "-- Dumping data for table `{$tableName}`\n";
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $sql .= "COMMIT;\n";
        
        return $sql;
    }
    
    /**
     * Create complete backup with files
     */
    private function createCompleteBackup($sqlFile, $archivePath) {
        $zip = new ZipArchive();
        
        if ($zip->open($archivePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Cannot create backup archive");
        }
        
        // Add SQL dump
        $zip->addFile($sqlFile, 'database.sql');
        
        // Add upload files
        if (is_dir(UPLOAD_PATH)) {
            $this->addDirectoryToZip($zip, UPLOAD_PATH, 'uploads/');
        }
        
        // Add configuration files (excluding sensitive data)
        $configFiles = [
            ROOT_PATH . '/config/constants.php',
            // Note: Don't include database.php for security
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, 'config/' . basename($file));
            }
        }
        
        $zip->close();
    }
    
    /**
     * Add directory to ZIP archive recursively
     */
    private function addDirectoryToZip($zip, $dir, $zipDir = '') {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipDir . substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * Get all backups
     */
    public function getAll($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT b.*, u.first_name, u.last_name
                  FROM backups b
                  LEFT JOIN users u ON b.created_by = u.id
                  ORDER BY b.created_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$limit, $offset]);
    }
    
    /**
     * Get backup by ID
     */
    public function getById($id) {
        $query = "SELECT b.*, u.first_name, u.last_name
                  FROM backups b
                  LEFT JOIN users u ON b.created_by = u.id
                  WHERE b.id = ?";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Delete backup
     */
    public function delete($id) {
        $backup = $this->getById($id);
        
        if ($backup) {
            // Delete file
            if (file_exists($backup['file_path'])) {
                unlink($backup['file_path']);
            }
            
            // Delete record
            $result = $this->db->delete('backups', ['id' => $id]);
            
            if ($result) {
                logActivity(ACTION_DELETE, 'backups', $id, $backup);
            }
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Download backup
     */
    public function download($id) {
        $backup = $this->getById($id);
        
        if (!$backup || !file_exists($backup['file_path'])) {
            return false;
        }
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . $backup['file_size']);
        
        // Output file
        readfile($backup['file_path']);
        
        // Log download
        logActivity(ACTION_DOWNLOAD, 'backups', $id);
        
        return true;
    }
    
    /**
     * Clean old backups
     */
    public function cleanOldBackups($retentionDays = null) {
        if ($retentionDays === null) {
            $retentionDays = getSetting('backup_retention_days', 30);
        }
        
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
        
        // Get old backups
        $oldBackups = $this->db->fetchAll(
            "SELECT * FROM backups WHERE created_at < ? AND status = ?",
            [$cutoffDate, BACKUP_COMPLETED]
        );
        
        $deletedCount = 0;
        
        foreach ($oldBackups as $backup) {
            if ($this->delete($backup['id'])) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Get backup statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total backups
        $stats['total'] = $this->db->getRowCount('backups');
        
        // Backups by status
        foreach ([BACKUP_CREATING, BACKUP_COMPLETED, BACKUP_FAILED] as $status) {
            $stats["status_{$status}"] = $this->db->getRowCount('backups', ['status' => $status]);
        }
        
        // Backups by type
        foreach ([BACKUP_MANUAL, BACKUP_SCHEDULED] as $type) {
            $stats["type_{$type}"] = $this->db->getRowCount('backups', ['backup_type' => $type]);
        }
        
        // Total backup size
        $sizeResult = $this->db->fetch("SELECT SUM(file_size) as total_size FROM backups WHERE status = 'completed'");
        $stats['total_size'] = $sizeResult['total_size'] ?? 0;
        
        // Latest backup
        $latestBackup = $this->db->fetch("SELECT created_at FROM backups WHERE status = 'completed' ORDER BY created_at DESC LIMIT 1");
        $stats['latest_backup'] = $latestBackup['created_at'] ?? null;
        
        return $stats;
    }
    
    /**
     * Restore database from backup
     */
    public function restoreDatabase($backupId) {
        $backup = $this->getById($backupId);
        
        if (!$backup || !file_exists($backup['file_path'])) {
            throw new Exception("Backup file not found");
        }
        
        // Check if it's a SQL file or ZIP archive
        $extension = pathinfo($backup['filename'], PATHINFO_EXTENSION);
        
        if ($extension === 'zip') {
            // Extract SQL from ZIP
            $zip = new ZipArchive();
            if ($zip->open($backup['file_path']) !== TRUE) {
                throw new Exception("Cannot open backup archive");
            }
            
            $sqlContent = $zip->getFromName('database.sql');
            $zip->close();
            
            if ($sqlContent === false) {
                throw new Exception("No database file found in backup");
            }
        } else {
            // Read SQL file directly
            $sqlContent = file_get_contents($backup['file_path']);
        }
        
        if (empty($sqlContent)) {
            throw new Exception("Backup file is empty or corrupted");
        }
        
        // Execute SQL statements
        $this->executeSQLDump($sqlContent);
        
        // Log restore activity
        logActivity('restore', 'backups', $backupId);
        
        return true;
    }
    
    /**
     * Execute SQL dump
     */
    private function executeSQLDump($sqlContent) {
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sqlContent)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        $this->db->beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $this->db->execute($statement . ';');
                }
            }
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}