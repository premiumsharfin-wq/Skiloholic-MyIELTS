<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please login to access this page.";
        header("Location: ../login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['flash_error'] = "Access denied. Admin only.";
        header("Location: ../index.php");
        exit();
    }
}
?>