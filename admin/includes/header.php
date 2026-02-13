<?php
// Admin Header
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

// Path prefix is always one level up for admin root pages
$prefix = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MyIELTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>MyIELTS Admin</h3>
        </div>

        <ul class="list-unstyled components">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php"><i class="fas fa-users me-2"></i> Manage Users</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'tests.php' ? 'active' : ''; ?>">
                <a href="tests.php"><i class="fas fa-file-alt me-2"></i> Manage Tests</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'evaluation.php' ? 'active' : ''; ?>">
                <a href="evaluation.php"><i class="fas fa-check-double me-2"></i> Test Evaluation</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'broadcast.php' ? 'active' : ''; ?>">
                <a href="broadcast.php"><i class="fas fa-bullhorn me-2"></i> Broadcast</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">
                <a href="support.php"><i class="fas fa-envelope me-2"></i> Support</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php"><i class="fas fa-cogs me-2"></i> Settings</a>
            </li>
            <li>
                <a href="../index.php"><i class="fas fa-home me-2"></i> Back to Home</a>
            </li>
            <li>
                <a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm rounded">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3">Admin: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                    <img src="<?php echo get_profile_pic($_SESSION['profile_pic'] ?? null); ?>" class="rounded-circle" width="30" height="30">
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <?php
        if (isset($_SESSION['flash_success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show">' . $_SESSION['flash_success'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show">' . $_SESSION['flash_error'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            unset($_SESSION['flash_error']);
        }
        ?>
