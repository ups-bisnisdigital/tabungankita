<?php
require_once 'Database.php';

class TransactionController {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db ? $db : (new Database())->getConnection();
    }
    
    public function getAllTransactions() {
        $transactions = [];
        $query = "SELECT * FROM transactions ORDER BY date DESC, id DESC";
        $result = $this->db->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        return $transactions;
    }
    
    public function addTransaction($type, $amount, $description, $date) {
        // Get admin ID from session (for demonstration, we'll use 1 as default)
        $adminId = 1;
        
        $query = "INSERT INTO transactions (type, amount, description, date, created_by) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sdssi", $type, $amount, $description, $date, $adminId);
        
        return $stmt->execute();
    }

    public function deleteTransaction($id) {
        $query = "DELETE FROM transactions WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}