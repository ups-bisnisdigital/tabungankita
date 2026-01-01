<?php
session_start();
require_once 'controllers/DashboardController.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit();
}

$isAdmin = isset($_SESSION['admin_logged_in']);
$dashboardController = new DashboardController();
$stats = $dashboardController->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UANG KAS - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <h1>UANG KAS</h1>
                <h2>Kelas 3A</h2>
                <h3>Bisnis Digital</h3>
            </div>
            <div class="user-menu">
                <?php if ($isAdmin): ?>
                    <span class="user-badge admin"><i class="fas fa-crown"></i> Admin</span>
                <?php else: ?>
                    <span class="user-badge user"><i class="fas fa-user"></i> User</span>
                <?php endif; ?>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3>Saldo Kas</h3>
                    <p class="stat-value">Rp <?php echo number_format($stats['balance'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Aksi Cepat</h2>
            <div class="action-buttons">
                <a href="students.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> Kas Minggu Ini
                </a>
				<a href="summary_2months.php" class="btn btn-primary">
                    <i class="fas fa-calendar-week"></i> Rekapan Pembayaran Kas
                </a>
                <a href="history.php" class="btn btn-primary">
                    <i class="fas fa-history"></i> Riwayat Transaksi
                </a>
                <?php if ($isAdmin): ?>
                <a href="transactions.php" class="btn btn-success">
                    <i class="fas fa-exchange-alt"></i> Kelola Transaksi
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="recent-transactions">
            <h2>Transaksi Terbaru</h2>
            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['recent_transactions'])): ?>
                            <?php foreach ($stats['recent_transactions'] as $transaction): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($transaction['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $transaction['type'] === 'income' ? 'badge-success' : 'badge-error'; ?>">
                                            <?php echo $transaction['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran'; ?>
                                        </span>
                                    </td>
                                    <td class="<?php echo $transaction['type'] === 'income' ? 'text-success' : 'text-error'; ?>">
                                        <?php echo ($transaction['type'] === 'income' ? '+' : '-'); ?> 
                                        Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="assets/js/pwa.js"></script>
</body>
</html>