<?php
require_once 'Database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function adminLogin($username, $pin) {
        // In a real application, you should use password_hash() and password_verify()
        // For demo purposes, we're using a simple PIN check
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            return false;
        }
        $result = $stmt->get_result();
        if (!$result) {
            return false;
        }
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            // Simple PIN check - in production, use password_verify()
            if ($admin['pin'] === $pin) {
                return true;
            }
        }
        
        return false;
    }
}