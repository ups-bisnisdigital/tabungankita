<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'uang_kas';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            // Check connection
            if ($this->conn->connect_error) {
                // If database doesn't exist, try to create it
                if ($this->conn->connect_errno == 1049) {
                    $this->createDatabase();
                } else {
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database error: " . $e->getMessage());
        }
        
        return $this->conn;
    }
    
    private function createDatabase() {
        // Create connection without database
        $temp_conn = new mysqli($this->host, $this->username, $this->password);
        
        if ($temp_conn->connect_error) {
            throw new Exception("Connection failed: " . $temp_conn->connect_error);
        }
        
        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($temp_conn->query($sql) === TRUE) {
            // Select the database
            $temp_conn->select_db($this->db_name);
            $this->createTables($temp_conn);
            $this->conn = $temp_conn;
        } else {
            throw new Exception("Error creating database: " . $temp_conn->error);
        }
    }
    
    private function createTables($conn) {
        // Read SQL file and execute
        $sql_file = __DIR__ . '/../uang_kas.sql';
        
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            
            // Execute multiple queries
            if ($conn->multi_query($sql)) {
                do {
                    // Discard results
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
            }
            
            // Add default admin if needed (PIN: 1234 for demo)
            $this->addDefaultAdmin($conn);
        }
    }
    
    private function addDefaultAdmin($conn) {
        $checkAdmin = "SELECT COUNT(*) as count FROM admins";
        $result = $conn->query($checkAdmin);
        
        if ($result && $result->fetch_assoc()['count'] == 0) {
            $insertAdmin = "INSERT INTO admins (username, pin) VALUES ('admin', '1234')";
            $conn->query($insertAdmin);
        }
    }
}