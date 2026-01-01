<?php
session_start();
require_once 'controllers/HistoryController.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit();
}

$isAdmin = isset($_SESSION['admin_logged_in']);
$historyController = new HistoryController();

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Get filtered history
$history = $historyController->getFilteredHistory($search, $startDate, $endDate, $type);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UANG KAS - Riwayat Transaksi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#4e73df">
    <link rel="apple-touch-icon" href="assets/icons/icon.svg">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Riwayat Transaksi</h1>
            <div class="user-menu">
                <?php if ($isAdmin): ?>
                    <span class="user-badge admin"><i class="fas fa-crown"></i> Admin</span>
                <?php else: ?>
                    <span class="user-badge user"><i class="fas fa-user"></i> User</span>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Filter Data</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Cari:</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Cari deskripsi...">
                        </div>
                        
                        <div class="form-group">
                            <label for="type">Jenis:</label>
                            <select id="type" name="type">
                                <option value="">Semua</option>
                                <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                                <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
                                <option value="payment" <?php echo $type === 'payment' ? 'selected' : ''; ?>>Kas</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Dari Tanggal:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">Sampai Tanggal:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Terapkan Filter
                        </button>
                        <a href="history.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Semua Riwayat Transaksi</h2>
                <p>Total: <?php echo count($history); ?> transaksi</p>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($item['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td>
                                        <?php if ($item['type'] === 'income'): ?>
                                            <span class="badge badge-success">Pemasukan</span>
                                        <?php elseif ($item['type'] === 'expense'): ?>
                                            <span class="badge badge-error">Pengeluaran</span>
                                        <?php elseif ($item['type'] === 'payment'): ?>
                                            <span class="badge badge-success">Kas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="<?php echo ($item['type'] === 'income' || $item['type'] === 'payment') ? 'text-success' : 'text-error'; ?>">
                                        <?php echo (($item['type'] === 'income' || $item['type'] === 'payment') ? '+' : '-'); ?> 
                                        Rp <?php echo number_format($item['amount'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <?php echo ($search || $startDate || $endDate || $type) ? 
                                        'Tidak ada data yang sesuai dengan filter' : 
                                        'Belum ada riwayat transaksi'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($history)): ?>
        <div class="export-section">
            <a href="export_history.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success">
                <i class="fas fa-download"></i> Export ke CSV
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>