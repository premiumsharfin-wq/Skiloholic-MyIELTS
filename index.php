<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="p-5 mb-4 bg-light rounded-3 jumbotron text-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold text-primary">The Real IELTS Experience</h1>
        <p class="col-md-8 fs-4 mx-auto">Providing a premium platform for IELTS preparation in Bangladesh. Powered by Skiloholic.</p>
        <?php if(!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg px-4 gap-3">Register Now</a>
            <a href="login.php" class="btn btn-outline-secondary btn-lg px-4">Login</a>
        <?php else: ?>
            <a href="student/index.php" class="btn btn-primary btn-lg px-4">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<!-- Features Section -->
<div class="row align-items-md-stretch mb-5">
    <div class="col-md-4">
        <div class="h-100 p-5 text-white bg-dark rounded-3">
            <h2>Mock Tests</h2>
            <p>Experience real exam-like conditions with our Mock Tests. Currently offering comprehensive Writing modules (Task 1 & Task 2) with expert evaluation.</p>
            <a href="student/mock_test.php" class="btn btn-outline-light" type="button">Start Testing</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="h-100 p-5 bg-light border rounded-3">
            <h2>Detailed Evaluation</h2>
            <p>Get your writing evaluated by experts with detailed feedback on Task Achievement, Cohesion, Lexical Resource, and Grammatical Range.</p>
            <a href="student/custom_evaluation.php" class="btn btn-outline-secondary" type="button">Custom Evaluation</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="h-100 p-5 text-white bg-secondary rounded-3">
            <h2>Track Progress</h2>
            <p>Monitor your improvement with our advanced dashboard. Calculate your band scores, set targets, and view your test history analytics.</p>
            <?php if(isLoggedIn()): ?>
                <a href="student/index.php" class="btn btn-outline-light" type="button">View Progress</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light" type="button">Login to Track</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- About Section -->
<div class="row mb-5">
    <div class="col-12 text-center">
        <h3>About MyIELTS</h3>
        <p class="lead">MyIELTS is developed by Sharfin Hossain to help students achieve their desired band scores.</p>
        <p>We provide authentic practice materials and accurate assessment to guide your preparation journey.</p>
    </div>
</div>

<?php
// Check for broadcast message
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'broadcast_message'");
$stmt->execute();
$broadcast = $stmt->fetchColumn();

if ($broadcast) {
    echo '<div class="alert alert-info text-center mt-4" role="alert">
            <i class="fas fa-bullhorn"></i> <strong>Announcement:</strong> ' . htmlspecialchars($broadcast) . '
          </div>';
}
?>

<?php require_once 'includes/footer.php'; ?>
