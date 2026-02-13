<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mpkhydsc_myielts');
define('DB_USER', 'mpkhydsc_sharfin');
define('DB_PASS', 'DevNerds@Sharfin9090');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // In production, we should not echo the error directly to avoid exposing credentials
    // For now, we'll log it and show a generic message
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>