<?php
/**
 * Database Class
 * Handles database operations and connections
 */

class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        try {
            if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
                // Ensure directory exists for SQLite
                $dbDir = dirname(DB_PATH);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                
                $dsn = "sqlite:" . DB_PATH;
                $this->pdo = new PDO($dsn, null, null, DB_OPTIONS);
                
                // Enable foreign keys for SQLite
                $this->pdo->exec("PRAGMA foreign_keys = ON");
                
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
            }
            
            // Test the connection
            $this->pdo->query("SELECT 1");
            
        } catch (PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
            
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
                    $error .= "\nSQLite path: " . DB_PATH;
                } else {
                    $error .= "\nDSN: mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                    $error .= "\nUser: " . DB_USER;
                }
            }
            
            error_log($error);
            
            // Try to provide helpful error messages
            if (strpos($e->getMessage(), 'No such file or directory') !== false) {
                throw new Exception("Cannot connect to MySQL server. Please ensure MySQL is running and accessible.");
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                throw new Exception("Database access denied. Please check your username and password.");
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                throw new Exception("Database '" . DB_NAME . "' does not exist. Please create the database first.");
            } else {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Get database type
     */
    public function getDatabaseType() {
        return defined('DB_TYPE') ? DB_TYPE : 'mysql';
    }
    
    /**
     * Check if using SQLite
     */
    public function isSQLite() {
        return $this->getDatabaseType() === 'sqlite';
    }
    
    /**
     * Execute a prepared statement
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }
    
    /**
     * Fetch a single row
     */
    public function fetch($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        $query = "SHOW TABLES LIKE ?";
        $result = $this->fetch($query, [$tableName]);
        return !empty($result);
    }
    
    /**
     * Get table row count
     */
    public function getRowCount($tableName, $conditions = []) {
        $query = "SELECT COUNT(*) as count FROM `{$tableName}`";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "`{$field}` = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $result = $this->fetch($query, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Insert data into table
     */
    public function insert($tableName, $data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO `{$tableName}` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->execute($query, array_values($data));
        return $this->lastInsertId();
    }
    
    /**
     * Update data in table
     */
    public function update($tableName, $data, $conditions) {
        $setClause = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $setClause[] = "`{$field}` = ?";
            $params[] = $value;
        }
        
        $whereClause = [];
        foreach ($conditions as $field => $value) {
            $whereClause[] = "`{$field}` = ?";
            $params[] = $value;
        }
        
        $query = "UPDATE `{$tableName}` SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete data from table
     */
    public function delete($tableName, $conditions) {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "`{$field}` = ?";
            $params[] = $value;
        }
        
        $query = "DELETE FROM `{$tableName}` WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }
}