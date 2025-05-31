<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "student_portal";
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli(
                $this->host, 
                $this->username, 
                $this->password, 
                $this->database
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    // Prepared statement helper for SELECT queries
    public function select($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    // Prepared statement helper for INSERT/UPDATE/DELETE
    public function execute($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    // Get last inserted ID
    public function getLastId() {
        return $this->connection->insert_id;
    }
    
    // Escape string for security
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
}

// global database instance
$db = new Database();
?>