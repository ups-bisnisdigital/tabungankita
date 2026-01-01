<?php
require_once 'Database.php';

class DashboardController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getDashboardStats() {
        $stats = [
            'balance' => 0,
            'total_income' => 0,
            'total_expenses' => 0,
            'student_count' => 0,
            'recent_transactions' => []
        ];
        
        // Get total income (transactions + student payments)
        $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'";
        $income = 0;
        $incomeResult = $this->db->query($incomeQuery);
        if ($incomeResult instanceof mysqli_result) {
            $row = $incomeResult->fetch_assoc();
            $income = isset($row['total']) ? $row['total'] : 0;
        }
        
        // Get total student payments
        $paymentsQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments";
        $payments = 0;
        $paymentsResult = $this->db->query($paymentsQuery);
        if ($paymentsResult instanceof mysqli_result) {
            $row = $paymentsResult->fetch_assoc();
            $payments = isset($row['total']) ? $row['total'] : 0;
        }
        
        // Calculate total income
        $stats['total_income'] = $income + $payments;
        
        // Get total expenses
        $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'";
        $stats['total_expenses'] = 0;
        $expenseResult = $this->db->query($expenseQuery);
        if ($expenseResult instanceof mysqli_result) {
            $row = $expenseResult->fetch_assoc();
            $stats['total_expenses'] = isset($row['total']) ? $row['total'] : 0;
        }
        
        // Calculate balance
        $stats['balance'] = $stats['total_income'] - $stats['total_expenses'];
        
        // Get student count
        $studentQuery = "SELECT COUNT(*) as count FROM students";
        $stats['student_count'] = 0;
        $studentResult = $this->db->query($studentQuery);
        if ($studentResult instanceof mysqli_result) {
            $row = $studentResult->fetch_assoc();
            $stats['student_count'] = isset($row['count']) ? $row['count'] : 0;
        }
        
        // Get recent transactions (last 5)
        $transactionsQuery = "SELECT date, description, type, amount FROM transactions ORDER BY date DESC, id DESC LIMIT 5";
        
        $transactionsResult = $this->db->query($transactionsQuery);
        if ($transactionsResult instanceof mysqli_result) {
            while ($row = $transactionsResult->fetch_assoc()) {
                $stats['recent_transactions'][] = $row;
            }
        }
        
        return $stats;
    }
}