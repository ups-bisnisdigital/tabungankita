<?php
session_start();
require_once 'controllers/HistoryController.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit();
}

$historyController = new HistoryController();

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Get filtered history
$history = $historyController->getFilteredHistory($search, $startDate, $endDate, $type);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=riwayat_transaksi_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Add CSV header
fputcsv($output, ['Tanggal', 'Deskripsi', 'Jenis', 'Jumlah (Rp)']);

// Add data rows
foreach ($history as $row) {
    $typeText = '';
    if ($row['type'] === 'income') {
        $typeText = 'Pemasukan';
    } elseif ($row['type'] === 'expense') {
        $typeText = 'Pengeluaran';
    } else {
        $typeText = 'Pembayaran Siswa';
    }
    
    $amount = number_format($row['amount'], 0, ',', '.');
    
    fputcsv($output, [
        date('d/m/Y', strtotime($row['date'])),
        $row['description'],
        $typeText,
        $amount
    ]);
}

fclose($output);
exit();