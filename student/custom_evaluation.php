<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../config/mail.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    if (empty($question) || empty($answer)) {
        flash('error', "Both question and answer are required.");
    } else {
        $stmt = $pdo->prepare("INSERT INTO custom_evaluations (user_id, question, answer, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $question, $answer]);
        $id = $pdo->lastInsertId();

        // Notify Admins
        $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin'");
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $subject = "New Custom Evaluation - " . $_SESSION['user_name'];
        $body = "A new custom evaluation has been submitted by <strong>" . htmlspecialchars($_SESSION['user_name']) . "</strong>.<br>
                 Date: " . date('Y-m-d H:i:s') . "<br><br>
                 Please login to the Admin Panel to evaluate it.";

        foreach ($admins as $admin_email) {
            sendEmail($admin_email, $subject, $body);
        }

        flash('success', "Custom evaluation submitted! Our experts will review it shortly.");
        redirect("result.php?id=$id&type=custom");
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2 class="mb-4">Custom Evaluation</h2>
        <div class="alert alert-info">
            Submit your Writing Task 2 essays for expert evaluation. Paste the question and your answer below.
        </div>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Question / Topic</label>
                <textarea name="question" class="form-control" rows="3" placeholder="Paste the essay question here..." required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Your Answer</label>
                <textarea name="answer" class="form-control" rows="15" placeholder="Write your essay here..." required></textarea>
                <div class="form-text text-end" id="wordCount">0 words</div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i> Submit for Evaluation</button>
        </form>
    </div>
</div>

<script>
    document.querySelector('textarea[name="answer"]').addEventListener('input', function() {
        const text = this.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        document.getElementById('wordCount').textContent = words + ' words';
    });
</script>

<?php require_once '../includes/footer.php'; ?>
