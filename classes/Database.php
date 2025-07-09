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
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
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
    
    /**
     * Quote a string for safe SQL usage
     */
    public function quote($string) {
        return $this->pdo->quote($string);
    }
    
    /**
     * Bulk insert data into table
     */
    public function bulkInsert($tableName, $data) {
        if (empty($data)) {
            return 0;
        }
        
        $firstRow = reset($data);
        $fields = array_keys($firstRow);
        $placeholders = '(' . implode(', ', array_fill(0, count($fields), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($data), $placeholders));
        
        $query = "INSERT INTO `{$tableName}` (`" . implode('`, `', $fields) . "`) VALUES " . $allPlaceholders;
        
        $params = [];
        foreach ($data as $row) {
            foreach ($fields as $field) {
                $params[] = $row[$field] ?? null;
            }
        }
        
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Truncate table
     */
    public function truncate($tableName) {
        try {
            $query = "TRUNCATE TABLE `{$tableName}`";
            return $this->execute($query);
        } catch (PDOException $e) {
            error_log("Table truncation failed: " . $e->getMessage());
            throw new Exception("Table truncation failed");
        }
    }
    
    /**
     * Get database size
     */
    public function getDatabaseSize() {
        $query = "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                  FROM information_schema.tables 
                  WHERE table_schema = ?";
        
        $result = $this->fetch($query, [DB_NAME]);
        return $result ? (float)$result['size_mb'] : 0;
    }
    
    /**
     * Optimize table
     */
    public function optimizeTable($tableName) {
        try {
            $query = "OPTIMIZE TABLE `{$tableName}`";
            return $this->execute($query);
        } catch (PDOException $e) {
            error_log("Table optimization failed: " . $e->getMessage());
            throw new Exception("Table optimization failed");
        }
    }
}