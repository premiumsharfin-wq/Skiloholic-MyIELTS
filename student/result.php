<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'test'; // 'test' or 'custom'
$user_id = $_SESSION['user_id'];

$result = null;

if ($type === 'custom') {
    $stmt = $pdo->prepare("SELECT * FROM custom_evaluations WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $result = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT tr.*, t.title FROM test_results tr JOIN tests t ON tr.test_id = t.id WHERE tr.id = ? AND tr.user_id = ?");
    $stmt->execute([$id, $user_id]);
    $result = $stmt->fetch();
}

if (!$result) {
    flash('error', "Result not found.");
    redirect("index.php");
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2 class="mb-4">Evaluation Result</h2>

        <?php if ($result['status'] === 'pending'): ?>
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Evaluation Pending</h5>
                </div>
                <div class="card-body">
                    <p class="lead">Your submission has been received and is currently under review.</p>
                    <p><strong>Estimated Time:</strong> Normally 1 to 3 hours. (Up to 12 hours during busy periods)</p>
                    <p>You will be notified once the evaluation is complete.</p>
                    <a href="index.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Evaluated Result -->
            <div class="card border-success mb-4 shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i> Evaluation Complete</h5>
                    <span class="badge bg-light text-success fs-5">Band Score: <?php echo $result['score_band']; ?></span>
                </div>
                <div class="card-body">
                    <h5 class="text-primary mb-3">Detailed Feedback</h5>

                    <?php
                    $feedback = json_decode($result['feedback'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Fallback if not JSON
                        $feedback = ['general' => $result['feedback']];
                    }
                    ?>

                    <div class="row mb-4">
                        <?php if (isset($feedback['task_achievement'])): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold text-muted">Task Achievement</h6>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($feedback['task_achievement'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($feedback['cohesion'])): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold text-muted">Cohesion & Coherence</h6>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($feedback['cohesion'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($feedback['lexical'])): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold text-muted">Lexical Resource</h6>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($feedback['lexical'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($feedback['grammatical'])): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold text-muted">Grammatical Range</h6>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($feedback['grammatical'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($feedback['general']) && !empty($feedback['general'])): ?>
                        <div class="col-12">
                            <div class="alert alert-secondary">
                                <strong>Additional Comments:</strong><br>
                                <?php echo nl2br(htmlspecialchars($feedback['general'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Submission Details -->
        <div class="card mb-4">
            <div class="card-header">
                Your Submission
                <span class="float-end small text-muted">Submitted on: <?php echo format_date($result['submitted_at'] ?? $result['created_at']); ?></span>
            </div>
            <div class="card-body bg-light">
                <?php if ($type === 'custom'): ?>
                    <h6><strong>Question:</strong></h6>
                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($result['question'])); ?></p>
                    <hr>
                    <h6><strong>Answer:</strong></h6>
                    <div class="p-3 bg-white border rounded">
                        <?php echo nl2br(htmlspecialchars($result['answer'])); ?>
                    </div>
                <?php else: ?>
                    <?php
                    $submission = json_decode($result['submission_data'], true);
                    if (is_array($submission)): ?>
                        <?php if (!empty($submission['task1'])): ?>
                            <h6><strong>Task 1 Answer:</strong></h6>
                            <div class="p-3 bg-white border rounded mb-3">
                                <?php echo nl2br(htmlspecialchars($submission['task1'])); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($submission['task2'])): ?>
                            <h6><strong>Task 2 Answer:</strong></h6>
                            <div class="p-3 bg-white border rounded">
                                <?php echo nl2br(htmlspecialchars($submission['task2'])); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Single answer or legacy format -->
                        <div class="p-3 bg-white border rounded">
                            <?php echo nl2br(htmlspecialchars($result['submission_data'])); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
