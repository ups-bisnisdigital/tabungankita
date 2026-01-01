<?php
session_start();
require_once 'controllers/StudentController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/DateHelper.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit();
}

$isAdmin = isset($_SESSION['admin_logged_in']);
$studentController = new StudentController();
$dashboardController = new DashboardController();
$students = $studentController->getAllStudents();

// Handle date selection (Admin only)
$currentDate = date('Y-m-d');
if ($isAdmin && isset($_GET['date'])) {
    $currentDate = $_GET['date'];
}

$weekInfo = DateHelper::getWeekYear($currentDate);
$currentWeek = $weekInfo['week'];
$currentYear = $weekInfo['year'];
$weekRange = DateHelper::getWeekRange($currentWeek, $currentYear);

// Handle weekly payment and cancellation
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && isset($_POST['action'])) {
    $studentId = $_POST['student_id'];
    $action = $_POST['action'];

    if ($action === 'pay') {
        if ($studentController->addWeeklyPayment($studentId, $currentWeek, $currentYear)) {
            header('Location: students.php?success=payment');
            exit();
        } else {
            header('Location: students.php?error=payment');
            exit();
        }
    } elseif ($action === 'cancel') {
        if ($studentController->cancelWeeklyPayment($studentId, $currentWeek, $currentYear)) {
            header('Location: students.php?success=cancel');
            exit();
        } else {
            header('Location: students.php?error=cancel');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UANG KAS - Daftar Mahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daftar Mahasiswa</h1>
            <div class="user-menu">
                <?php if ($isAdmin): ?>
                    <form action="" method="GET" class="date-selector" style="display: inline-block; margin-right: 10px;">
                        <input type="date" name="date" value="<?php echo $currentDate; ?>" onchange="this.form.submit()" class="form-control" style="padding: 5px;">
                    </form>
                    <span class="user-badge admin"><i class="fas fa-crown"></i> Admin</span>
                <?php else: ?>
                    <span class="user-badge user"><i class="fas fa-user"></i> User</span>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'payment'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Pembayaran berhasil dicatat!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'cancel'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Pembayaran berhasil dibatalkan!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'payment'): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Gagal mencatat pembayaran!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'cancel'): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Gagal membatalkan pembayaran!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>Data Mahasiswa</h2>
                    <p class="text-muted">
                        <i class="fas fa-calendar-alt"></i> 
                        Periode: <?php echo date('d M Y', strtotime($weekRange['start'])); ?> - <?php echo date('d M Y', strtotime($weekRange['end'])); ?>
                        (Minggu ke-<?php echo $currentWeek; ?>)
                    </p>
                </div>
                <p>Total: <?php echo count($students); ?> Mahasiswa</p>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <?php if ($isAdmin): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $index => $student): ?>
                                <?php
                                $hasPaid = $studentController->checkPaymentStatus(
                                    $student['id'], 
                                    $currentWeek, 
                                    $currentYear
                                );
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td>
                                        <?php if ($hasPaid): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Sudah
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-error">
                                                <i class="fas fa-times"></i> Belum
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <?php if (!$hasPaid): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                    <input type="hidden" name="action" value="pay">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Bayar
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-error btn-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan pembayaran ini?')">
                                                        <i class="fas fa-undo"></i> Batal
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $isAdmin ? 4 : 3; ?>" class="text-center">
                                    Tidak ada data Mahasiswa
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Informasi</h3>
            <p>• Setiap pembayaran mingguan akan menambahkan Rp 10.000 ke saldo kas</p>
            <p>• Pembayaran hanya dapat dilakukan sekali per minggu per orang</p>
            <p>• Status pembayaran direset setiap minggu</p>
        </div>
    </div>
    <script src="assets/js/pwa.js"></script>
</body>
</html>