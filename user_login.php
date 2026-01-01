<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

// Handle user login (no PIN required)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user_logged_in'] = true;
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UANG KAS - User Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="back-button">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            
            <div class="app-icon">
                <i class="fas fa-user"></i>
            </div>
            <h1>Login User</h1>
            <p class="subtitle">Klik tombol dibawah untuk masuk</p>
            
            <form method="POST" action="">
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i> Masuk sebagai User
                </button>
            </form>
            
            <div class="app-info">
                <p><i class="fas fa-info-circle"></i> User hanya dapat melihat data, tidak dapat mengubah</p>
            </div>
        </div>
    </div>
    <script src="assets/js/pwa.js"></script>
</body>
</html>