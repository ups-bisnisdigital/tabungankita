<?php
session_start();
require_once 'controllers/AuthController.php';

// Redirect to appropriate dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
} elseif (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UANG KAS - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#4e73df">
    <link rel="apple-touch-icon" href="assets/icons/icon.svg">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="app-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <h1>UANG KAS</h1>
            <p class="subtitle">3A BISNIS DIGITAL</p>
            
            <div class="login-options">
                <a href="admin_login.php" class="btn btn-primary">
                    <i class="fas fa-lock"></i> Login Admin
                </a>
                <a href="user_login.php" class="btn btn-secondary">
                    <i class="fas fa-user"></i> Login User
                </a>
            </div>
            
            <div class="app-info">
                <p><i class="fas fa-info-circle"></i> Admin membutuhkan PIN untuk login</p>
                <p><i class="fas fa-info-circle"></i> Jika hanya ingin melihat data silahkan login sebagai user</p>
            </div>
        </div>
    </div>
</body>
</html>