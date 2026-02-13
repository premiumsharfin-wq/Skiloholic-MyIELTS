<?php
require_once 'includes/header.php';

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'test'; // 'test' or 'custom'
$error = '';

// Fetch Data
$submission = null;
if ($type === 'custom') {
    $stmt = $pdo->prepare("SELECT ce.*, u.name, u.email FROM custom_evaluations ce JOIN users u ON ce.user_id = u.id WHERE ce.id = ?");
    $stmt->execute([$id]);
    $submission = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT tr.*, t.title, t.question_data, u.name, u.email FROM test_results tr JOIN tests t ON tr.test_id = t.id JOIN users u ON tr.user_id = u.id WHERE tr.id = ?");
    $stmt->execute([$id]);
    $submission = $stmt->fetch();
}

if (!$submission) {
    flash('error', "Submission not found.");
    redirect('evaluation.php');
}

// Handle Evaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ta_score = $_POST['ta_score'];
    $cc_score = $_POST['cc_score'];
    $lr_score = $_POST['lr_score'];
    $gra_score = $_POST['gra_score'];

    $feedback = [
        'ta_score' => $ta_score,
        'cc_score' => $cc_score,
        'lr_score' => $lr_score,
        'gra_score' => $gra_score,
        'task_achievement' => $_POST['ta_feedback'],
        'cohesion' => $_POST['cc_feedback'],
        'lexical' => $_POST['lr_feedback'],
        'grammatical' => $_POST['gra_feedback'],
        'general' => $_POST['general_comments']
    ];

    // Calculate Overall (Round to nearest 0.5)
    $avg = ($ta_score + $cc_score + $lr_score + $gra_score) / 4;
    $overall_score = round($avg * 2) / 2;

    $feedback_json = json_encode($feedback);
    $evaluated_at = date('Y-m-d H:i:s');

    try {
        if ($type === 'custom') {
            $stmt = $pdo->prepare("UPDATE custom_evaluations SET status = 'evaluated', evaluated_by = ?, score_band = ?, feedback = ?, evaluated_at = ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE test_results SET status = 'evaluated', evaluated_by = ?, score_band = ?, feedback = ?, evaluated_at = ? WHERE id = ?");
        }
        $stmt->execute([$_SESSION['user_id'], $overall_score, $feedback_json, $evaluated_at, $id]);

        // Notify User
        require_once '../config/mail.php';
        sendEmail($submission['email'], "Test Evaluation Complete", "Your submission has been evaluated. Your Band Score is: <strong>$overall_score</strong>. Login to view detailed feedback.");

        flash('success', "Evaluation saved successfully.");
        redirect('evaluation.php');
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Pre-fill if editing
$existing_feedback = $submission['feedback'] ? json_decode($submission['feedback'], true) : [];
?>

<div class="row">
    <!-- Submission View -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Submission by <?php echo htmlspecialchars($submission['name']); ?>
                <span class="float-end badge bg-light text-dark"><?php echo ucfirst($type); ?></span>
            </div>
            <div class="card-body" style="max-height: 80vh; overflow-y: auto;">
                <?php if ($type === 'custom'): ?>
                    <h6><strong>Question:</strong></h6>
                    <div class="p-2 bg-light border mb-3"><?php echo nl2br(htmlspecialchars($submission['question'])); ?></div>
                    <h6><strong>Answer:</strong></h6>
                    <div class="p-2 bg-white border"><?php echo nl2br(htmlspecialchars($submission['answer'])); ?></div>
                <?php else: ?>
                    <h6><strong>Test:</strong> <?php echo htmlspecialchars($submission['title']); ?></h6>
                    <h6><strong>Question:</strong></h6>
                    <div class="p-2 bg-light border mb-3"><?php echo nl2br(htmlspecialchars($submission['question_data'])); ?></div>

                    <h6><strong>Answer:</strong></h6>
                    <?php
                    $ans = json_decode($submission['submission_data'], true);
                    if (is_array($ans)): ?>
                        <?php if (!empty($ans['task1'])): ?>
                            <div class="mb-3">
                                <strong>Task 1:</strong>
                                <div class="p-2 bg-white border"><?php echo nl2br(htmlspecialchars($ans['task1'])); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ans['task2'])): ?>
                            <div class="mb-3">
                                <strong>Task 2:</strong>
                                <div class="p-2 bg-white border"><?php echo nl2br(htmlspecialchars($ans['task2'])); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-2 bg-white border"><?php echo nl2br(htmlspecialchars($submission['submission_data'])); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">Evaluation Form</div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Task Achievement -->
                    <div class="mb-3">
                        <label class="fw-bold">Task Achievement / Response</label>
                        <div class="row">
                            <div class="col-3">
                                <input type="number" name="ta_score" class="form-control" placeholder="Score" step="0.5" max="9" required value="<?php echo $existing_feedback['ta_score'] ?? ''; ?>">
                            </div>
                            <div class="col-9">
                                <textarea name="ta_feedback" class="form-control" rows="2" placeholder="Feedback..."><?php echo $existing_feedback['task_achievement'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Cohesion -->
                    <div class="mb-3">
                        <label class="fw-bold">Cohesion & Coherence</label>
                        <div class="row">
                            <div class="col-3">
                                <input type="number" name="cc_score" class="form-control" placeholder="Score" step="0.5" max="9" required value="<?php echo $existing_feedback['cc_score'] ?? ''; ?>">
                            </div>
                            <div class="col-9">
                                <textarea name="cc_feedback" class="form-control" rows="2" placeholder="Feedback..."><?php echo $existing_feedback['cohesion'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Lexical -->
                    <div class="mb-3">
                        <label class="fw-bold">Lexical Resource</label>
                        <div class="row">
                            <div class="col-3">
                                <input type="number" name="lr_score" class="form-control" placeholder="Score" step="0.5" max="9" required value="<?php echo $existing_feedback['lr_score'] ?? ''; ?>">
                            </div>
                            <div class="col-9">
                                <textarea name="lr_feedback" class="form-control" rows="2" placeholder="Feedback..."><?php echo $existing_feedback['lexical'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Grammar -->
                    <div class="mb-3">
                        <label class="fw-bold">Grammatical Range</label>
                        <div class="row">
                            <div class="col-3">
                                <input type="number" name="gra_score" class="form-control" placeholder="Score" step="0.5" max="9" required value="<?php echo $existing_feedback['gra_score'] ?? ''; ?>">
                            </div>
                            <div class="col-9">
                                <textarea name="gra_feedback" class="form-control" rows="2" placeholder="Feedback..."><?php echo $existing_feedback['grammatical'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- General -->
                    <div class="mb-3">
                        <label class="fw-bold">General Comments</label>
                        <textarea name="general_comments" class="form-control" rows="3"><?php echo $existing_feedback['general'] ?? ''; ?></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Evaluation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
