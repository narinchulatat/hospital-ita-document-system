<?php
/**
 * Database Class
 * Handles database operations and connections
 */

class Database {
    private $pdo;
    private static $instance = null;
    private $transactionLevel = 0;
    private $statementCache = [];
    
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
     * Execute a prepared statement with caching
     */
    public function execute($query, $params = []) {
        try {
            // Use cached statement if available
            $cacheKey = md5($query);
            if (isset($this->statementCache[$cacheKey])) {
                $stmt = $this->statementCache[$cacheKey];
            } else {
                $stmt = $this->pdo->prepare($query);
                // Cache the statement for reuse
                $this->statementCache[$cacheKey] = $stmt;
            }
            
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage() . " | Query: " . $query);
            throw new Exception("Query execution failed: " . $e->getMessage());
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
     * Begin transaction with nesting support
     */
    public function beginTransaction() {
        if ($this->transactionLevel == 0) {
            $result = $this->pdo->beginTransaction();
        } else {
            // Use savepoints for nested transactions
            $result = $this->pdo->exec("SAVEPOINT trans_level_" . $this->transactionLevel);
        }
        $this->transactionLevel++;
        return $result;
    }
    
    /**
     * Commit transaction with nesting support
     */
    public function commit() {
        $this->transactionLevel--;
        if ($this->transactionLevel == 0) {
            return $this->pdo->commit();
        } else {
            // Release savepoint
            return $this->pdo->exec("RELEASE SAVEPOINT trans_level_" . $this->transactionLevel);
        }
    }
    
    /**
     * Rollback transaction with nesting support
     */
    public function rollback() {
        $this->transactionLevel--;
        if ($this->transactionLevel == 0) {
            return $this->pdo->rollback();
        } else {
            // Rollback to savepoint
            return $this->pdo->exec("ROLLBACK TO SAVEPOINT trans_level_" . $this->transactionLevel);
        }
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
     * Insert data into table with better error handling
     */
    public function insert($tableName, $data) {
        try {
            $fields = array_keys($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            $query = "INSERT INTO `{$tableName}` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
            
            $this->execute($query, array_values($data));
            return $this->lastInsertId();
        } catch (Exception $e) {
            error_log("Insert failed for table {$tableName}: " . $e->getMessage());
            throw new Exception("Insert operation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update data in table with better error handling
     */
    public function update($tableName, $data, $conditions) {
        try {
            if (empty($conditions)) {
                throw new Exception("Update operation requires conditions to prevent accidental mass updates");
            }
            
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
        } catch (Exception $e) {
            error_log("Update failed for table {$tableName}: " . $e->getMessage());
            throw new Exception("Update operation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete data from table with better error handling
     */
    public function delete($tableName, $conditions) {
        try {
            if (empty($conditions)) {
                throw new Exception("Delete operation requires conditions to prevent accidental mass deletion");
            }
            
            $whereClause = [];
            $params = [];
            
            foreach ($conditions as $field => $value) {
                $whereClause[] = "`{$field}` = ?";
                $params[] = $value;
            }
            
            $query = "DELETE FROM `{$tableName}` WHERE " . implode(' AND ', $whereClause);
            
            $stmt = $this->execute($query, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Delete failed for table {$tableName}: " . $e->getMessage());
            throw new Exception("Delete operation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check if we're in a transaction
     */
    public function inTransaction() {
        return $this->transactionLevel > 0;
    }
    
    /**
     * Get current transaction level
     */
    public function getTransactionLevel() {
        return $this->transactionLevel;
    }
    
    /**
     * Clear statement cache
     */
    public function clearStatementCache() {
        $this->statementCache = [];
    }
    
    /**
     * Execute multiple queries in a transaction
     */
    public function executeTransaction($queries) {
        $this->beginTransaction();
        
        try {
            $results = [];
            foreach ($queries as $query) {
                if (is_array($query)) {
                    $results[] = $this->execute($query['sql'], $query['params'] ?? []);
                } else {
                    $results[] = $this->execute($query);
                }
            }
            
            $this->commit();
            return $results;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}