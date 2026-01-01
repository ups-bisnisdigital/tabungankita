<?php
require_once 'Database.php';
require_once 'TransactionController.php';

class StudentController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getAllStudents() {
        $students = [];
        $query = "SELECT * FROM students ORDER BY name ASC";
        $result = $this->db->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        return $students;
    }
    
    public function checkPaymentStatus($studentId, $week, $year) {
        $query = "SELECT COUNT(*) as count FROM payments 
                 WHERE student_id = ? AND week_number = ? AND year = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("iii", $studentId, $week, $year);
        if (!$stmt->execute()) {
            return false;
        }
        $count = 0;
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                $count = isset($row['count']) ? (int)$row['count'] : 0;
            }
        } else {
            $stmt->bind_result($count);
            $stmt->fetch();
        }
        
        return $count > 0;
    }
    
    public function addWeeklyPayment($studentId, $week, $year) {
        // Start transaction
        if (method_exists($this->db, 'begin_transaction')) {
            $this->db->begin_transaction();
        } else {
            $this->db->autocommit(false);
            $this->db->query('START TRANSACTION');
        }
        
        try {
            // Add payment record
            $paymentQuery = "INSERT INTO payments (student_id, amount, week_number, year) 
                           VALUES (?, 10000, ?, ?)";
            $stmt = $this->db->prepare($paymentQuery);
            if (!$stmt) {
                throw new Exception('Failed to prepare payment insert');
            }
            $stmt->bind_param("iii", $studentId, $week, $year);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute payment insert');
            }
            
            
            
            // Commit transaction
            $this->db->commit();
            if (method_exists($this->db, 'autocommit')) {
                $this->db->autocommit(true);
            }
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            if (method_exists($this->db, 'autocommit')) {
                $this->db->autocommit(true);
            }
            return false;
        }
    }

    public function cancelWeeklyPayment($studentId, $week, $year) {
        // Start transaction
        if (method_exists($this->db, 'begin_transaction')) {
            $this->db->begin_transaction();
        } else {
            $this->db->autocommit(false);
            $this->db->query('START TRANSACTION');
        }
        
        try {
            // Delete payment record
            $paymentQuery = "DELETE FROM payments WHERE student_id = ? AND week_number = ? AND year = ?";
            $stmt = $this->db->prepare($paymentQuery);
            if (!$stmt) {
                throw new Exception('Failed to prepare payment delete');
            }
            $stmt->bind_param("iii", $studentId, $week, $year);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute payment delete');
            }
            
            // Commit transaction
            $this->db->commit();
            if (method_exists($this->db, 'autocommit')) {
                $this->db->autocommit(true);
            }
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            if (method_exists($this->db, 'autocommit')) {
                $this->db->autocommit(true);
            }
            return false;
        }
    }
}