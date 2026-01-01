<?php
require_once 'Database.php';

class HistoryController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getFilteredHistory($search = '', $startDate = '', $endDate = '', $type = '') {
        $history = [];
        
        $query = "";
        
        $params = [];
        $types = '';
        
        if (!empty($type) && $type === 'payment') {
            $query = "
                SELECT 
                    paid_at as date,
                    CONCAT(' ', s.name, ' (Minggu ', p.week_number, ', ', p.year, ')') as description,
                    'payment' as type,
                    p.amount,
                    UNIX_TIMESTAMP(p.paid_at) as sort_ts
                FROM payments p
                JOIN students s ON p.student_id = s.id
                WHERE 1=1
            ";
            if (!empty($search)) {
                $query .= " AND s.name LIKE ?";
                $params[] = "%$search%";
                $types .= 's';
            }
            if (!empty($startDate)) {
                $query .= " AND DATE(p.paid_at) >= ?";
                $params[] = $startDate;
                $types .= 's';
            }
            if (!empty($endDate)) {
                $query .= " AND DATE(p.paid_at) <= ?";
                $params[] = $endDate;
                $types .= 's';
            }
        } else {
            $query = "
                SELECT date, description, type, amount, UNIX_TIMESTAMP(date) as sort_ts 
                FROM transactions 
                WHERE 1=1
            ";
            if (!empty($search)) {
                $query .= " AND description LIKE ?";
                $params[] = "%$search%";
                $types .= 's';
            }
            if (!empty($startDate)) {
                $query .= " AND date >= ?";
                $params[] = $startDate;
                $types .= 's';
            }
            if (!empty($endDate)) {
                $query .= " AND date <= ?";
                $params[] = $endDate;
                $types .= 's';
            }
            if (!empty($type) && in_array($type, array('income', 'expense'))) {
                $query .= " AND type = ?";
                $params[] = $type;
                $types .= 's';
            }
            if (empty($type)) {
                $query .= "
                    UNION ALL
                    SELECT 
                        paid_at as date, 
                        CONCAT(' ', s.name, ' (Minggu ', p.week_number, ', ', p.year, ')') as description,
                        'payment' as type,
                        p.amount,
                        UNIX_TIMESTAMP(p.paid_at) as sort_ts
                    FROM payments p
                    JOIN students s ON p.student_id = s.id
                    WHERE 1=1
                ";
                if (!empty($search)) {
                    $query .= " AND s.name LIKE ?";
                    $params[] = "%$search%";
                    $types .= 's';
                }
                if (!empty($startDate)) {
                    $query .= " AND DATE(p.paid_at) >= ?";
                    $params[] = $startDate;
                    $types .= 's';
                }
                if (!empty($endDate)) {
                    $query .= " AND DATE(p.paid_at) <= ?";
                    $params[] = $endDate;
                    $types .= 's';
                }
            }
        }
        
        $query .= " ORDER BY sort_ts DESC";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return $history;
        }
        if (!empty($params)) {
            $bind = array_merge(array($types), $params);
            $refs = array();
            foreach ($bind as $k => $v) {
                $refs[$k] = &$bind[$k];
            }
            call_user_func_array(array($stmt, 'bind_param'), $refs);
        }
        if (!$stmt->execute()) {
            return $history;
        }
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $history[] = $row;
                }
            }
        } else {
            $dateVal = $descVal = $typeVal = '';
            $amountVal = 0;
            $stmt->bind_result($dateVal, $descVal, $typeVal, $amountVal);
            while ($stmt->fetch()) {
                $history[] = array(
                    'date' => $dateVal,
                    'description' => $descVal,
                    'type' => $typeVal,
                    'amount' => $amountVal
                );
            }
        }
        
        return $history;
    }
}