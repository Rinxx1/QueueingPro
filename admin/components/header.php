<?php
// Start session and check authentication
session_start();
require_once '../connections/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get username from session
$username = $_SESSION['username'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'QueueingPro Admin'; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <div class="logo-section">
                    <i class="fas fa-users"></i>
                    <h1>QueueingPro</h1>
                    <span class="subtitle">Admin Dashboard</span>
                </div>
            </div>
            <div class="header-right">
                <div class="admin-info">
                    <div class="admin-profile">
                        <i class="fas fa-user-shield"></i>
                        <span class="admin-name"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <div class="datetime">
                        <span id="currentDateTime"></span>
                    </div>
                </div>
                <button type="button" class="logout-btn" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <nav class="admin-nav">
            <div class="nav-tabs">
                <a href="index.php" class="nav-tab <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="counters.php" class="nav-tab <?php echo ($currentPage == 'counters') ? 'active' : ''; ?>">
                    <i class="fas fa-desktop"></i>
                    <span>Counters</span>
                </a>
                <a href="transactions.php" class="nav-tab <?php echo ($currentPage == 'transactions') ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transactions</span>
                </a>
                <a href="users.php" class="nav-tab <?php echo ($currentPage == 'users') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="video.php" class="nav-tab <?php echo ($currentPage == 'video') ? 'active' : ''; ?>">
                    <i class="fas fa-video"></i>
                    <span>Video</span>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
        
        <!-- Import header.js for functionality -->
        <script src="scripts/header.js"></script>
