<?php
session_start();
require_once 'controllers/TransactionController.php';

// Only admin can access this page
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

$transactionController = new TransactionController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_transaction'])) {
        $type = $_POST['type'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
        if ($transactionController->addTransaction($type, $amount, $description, $date)) {
            header('Location: transactions.php?success=added');
            exit();
        } else {
            header('Location: transactions.php?error=add');
            exit();
        }
    } elseif (isset($_POST['delete_transaction'])) {
        $id = $_POST['transaction_id'];
        if ($transactionController->deleteTransaction($id)) {
            header('Location: transactions.php?success=deleted');
            exit();
        } else {
            header('Location: transactions.php?error=delete');
            exit();
        }
    }
}

// Get all transactions
$transactions = $transactionController->getAllTransactions();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UANG KAS - Kelola Transaksi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Kelola Transaksi</h1>
            <div class="user-menu">
                <span class="user-badge admin"><i class="fas fa-crown"></i> Admin</span>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'added'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Transaksi berhasil ditambahkan!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Transaksi berhasil dihapus!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'add'): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Gagal menambahkan transaksi!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'delete'): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Gagal menghapus transaksi!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Tambah Transaksi Baru</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Jenis Transaksi:</label>
                            <select id="type" name="type" required>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">Jumlah (Rp):</label>
                            <input type="number" id="amount" name="amount" min="0" step="1000" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi:</label>
                        <textarea id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Tanggal:</label>
                        <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <button type="submit" name="add_transaction" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Transaksi
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Daftar Transaksi</h2>
                <p>Total: <?php echo count($transactions); ?> transaksi</p>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $transaction): ?>
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
                                    <td>
                                        <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                            <button type="submit" name="delete_transaction" class="btn btn-error btn-sm">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada transaksi</td>
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