<?php
// Determine path prefix based on file location
$script_name = $_SERVER['SCRIPT_NAME'];
$prefix = '';
if (strpos($script_name, '/student/') !== false || strpos($script_name, '/admin/') !== false) {
    $prefix = '../';
}
if (strpos($script_name, '/admin/includes/') !== false) {
    $prefix = '../../';
}

// Maintenance Mode Check
if (function_exists('isAdmin') && !isAdmin() && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    if ($stmt->fetchColumn() === '1') {
        // If not admin, redirect to maintenance page
        // Ensure we are not already on maintenance page or login page (admin needs to login)
        // Admin needs to login via /login.php usually.
        // If maintenance is ON, login page is blocked?
        // Prompt: "When an admin tries to set the website down...". Admin is already logged in.
        // But if admin logs out and tries to login?
        // Login page uses this header.
        // So login page is blocked for everyone except... nobody.
        // I should allow login page access.
        if (basename($_SERVER['PHP_SELF']) !== 'maintenance.php' && basename($_SERVER['PHP_SELF']) !== 'login.php' && strpos($_SERVER['PHP_SELF'], '/admin/') === false) {
             header("Location: " . $prefix . "maintenance.php");
             exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyIELTS - Powered by Skiloholic</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo $prefix; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $prefix; ?>index.php">
            <img src="<?php echo $prefix; ?>assets/images/logo.png" alt="MyIELTS Logo" height="40" class="d-inline-block align-text-top me-2">
            MyIELTS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $prefix; ?>index.php">Home</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $prefix; ?>student/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $prefix; ?>student/mock_test.php">Mock Test</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $prefix; ?>student/profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo $prefix; ?>student/index.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo $prefix; ?>student/test_history.php">Test History</a></li>
                            <li><a class="dropdown-item" href="<?php echo $prefix; ?>student/custom_evaluation.php">Custom Evaluation</a></li>
                            <li><a class="dropdown-item" href="<?php echo $prefix; ?>student/settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $prefix; ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $prefix; ?>login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $prefix; ?>register.php">Register</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $prefix; ?>contact.php">Contact Us</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 flex-grow-1">
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
