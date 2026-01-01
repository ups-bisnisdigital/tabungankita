<?php
session_start();
require_once 'controllers/StudentController.php';
require_once 'controllers/Database.php';
require_once 'controllers/DateHelper.php';

if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit();
}

$isAdmin = isset($_SESSION['admin_logged_in']);
$studentController = new StudentController();
$db = (new Database())->getConnection();

$monthPairs = [];
for ($m = 1; $m <= 12; $m++) {
    $next = $m === 12 ? 1 : $m + 1;
    $monthPairs[sprintf('%02d-%02d', $m, $next)] = [
        'label' => date('F', mktime(0, 0, 0, $m, 1)) . '-' . date('F', mktime(0, 0, 0, $next, 1)),
        'first' => $m,
        'second' => $next
    ];
}

$defaultPair = sprintf('%02d-%02d', (int)date('m'), ((int)date('m') % 12) + 1);
$pair = isset($_GET['pair']) && isset($monthPairs[$_GET['pair']]) ? $_GET['pair'] : $defaultPair;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$firstMonth = $monthPairs[$pair]['first'];
$secondMonth = $monthPairs[$pair]['second'];
$firstYear = $year;
$secondYear = ($firstMonth === 12 && $secondMonth === 1) ? $year + 1 : $year;

$startDate = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $firstYear, $firstMonth));
$endDate = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $secondYear, $secondMonth));
$endDate->modify('last day of this month');

$weekBuckets = [];
$cursor = clone $startDate;
// Align to start of week (Sunday)
if ((int)$cursor->format('w') !== 0) { // 0 is Sunday
    $cursor->modify('last sunday');
}

while ($cursor <= $endDate) {
    $weekStart = clone $cursor;
    $weekEnd = clone $cursor;
    $weekEnd->modify('+6 days');
    
    $bucketStart = $weekStart < $startDate ? $startDate : $weekStart;
    $bucketEnd = $weekEnd > $endDate ? $endDate : $weekEnd;
    
    // Use DateHelper to get consistent week/year
    $weekInfo = DateHelper::getWeekYear($weekStart->format('Y-m-d'));
    
    $weekBuckets[] = [
        'label' => $bucketStart->format('d M Y') . ' - ' . $bucketEnd->format('d M Y'),
        'year' => $weekInfo['year'],
        'week' => $weekInfo['week'],
        'start' => $bucketStart->format('Y-m-d'),
        'end' => $bucketEnd->format('Y-m-d')
    ];
    $cursor->modify('+7 days');
}

$paymentsMap = [];
if (!empty($weekBuckets)) {
    $conditions = [];
    foreach ($weekBuckets as $wb) {
        $conditions[] = "(week_number = " . (int)$wb['week'] . " AND year = " . (int)$wb['year'] . ")";
    }
    
    $sql = "SELECT student_id, week_number, year FROM payments WHERE " . implode(' OR ', $conditions);
    $result = $db->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sid = (int)$row['student_id'];
            $yr = (int)$row['year'];
            $wk = (int)$row['week_number'];
            if (!isset($paymentsMap[$sid])) $paymentsMap[$sid] = [];
            if (!isset($paymentsMap[$sid][$yr])) $paymentsMap[$sid][$yr] = [];
            $paymentsMap[$sid][$yr][$wk] = true;
        }
    }
}

$students = $studentController->getAllStudents();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan Pembayaran Kas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .week-header { white-space: nowrap; font-size: 12px; }
        .badge-mini { padding: 4px 8px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ringkasan Kas 2 Bulan</h1>
            <div class="user-menu">
                <?php if ($isAdmin): ?>
                    <span class="user-badge admin"><i class="fas fa-crown"></i> Admin</span>
                <?php else: ?>
                    <span class="user-badge user"><i class="fas fa-user"></i> User</span>
                <?php endif; ?>
                <a href="dashboard.php" class="btn-logout" title="Kembali"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Pilih Jenis Bulan</h2>
                <p>Total Minggu: <?php echo count($weekBuckets); ?></p>
            </div>
            <div class="card-body">
                <form method="get" class="form-inline" action="summary_2months.php">
                    <div class="form-group">
                        <label for="pair">Bulan:</label>
                        <select id="pair" name="pair">
                            <?php foreach ($monthPairs as $key => $info): ?>
                                <option value="<?php echo $key; ?>" <?php echo $key === $pair ? 'selected' : ''; ?>><?php echo $info['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Tahun:</label>
                        <input type="number" id="year" name="year" value="<?php echo $year; ?>" min="2000" max="2100">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Ringkasan Pembayaran Mingguan</h2>
                <p>Periode: <?php echo $startDate->format('d M Y'); ?> - <?php echo $endDate->format('d M Y'); ?></p>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <?php foreach ($weekBuckets as $wb): ?>
                                <th class="week-header">Minggu <?php echo $wb['week']; ?><br><?php echo $wb['label']; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $index => $student): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <?php foreach ($weekBuckets as $wb): ?>
                                        <?php
                                            $paid = isset($paymentsMap[(int)$student['id']]) &&
                                                    isset($paymentsMap[(int)$student['id']][$wb['year']]) &&
                                                    isset($paymentsMap[(int)$student['id']][$wb['year']][$wb['week']]);
                                        ?>
                                        <td>
                                            <?php if ($paid): ?>
                                                <span class="badge badge-success badge-mini"><i class="fas fa-check"></i> Bayar</span>
                                            <?php else: ?>
                                                <span class="badge badge-error badge-mini"><i class="fas fa-times"></i> Belum</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo 2 + count($weekBuckets); ?>" class="text-center">Tidak ada data Mahasiswa</td>
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