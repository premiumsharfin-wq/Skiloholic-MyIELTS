<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$test_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->execute([$test_id]);
$test = $stmt->fetch();

if (!$test) {
    flash('error', "Test not found.");
    redirect('mock_test.php');
}

// Layout: Full screen or minimal header
// We'll use a custom minimal layout to simulate the test environment.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Writing Test - <?php echo htmlspecialchars($test['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
        .test-header { background: #eee; padding: 10px; border-bottom: 1px solid #ccc; }
        .test-body { flex: 1; display: flex; overflow: hidden; }
        .pane { flex: 1; padding: 20px; overflow-y: auto; }
        .pane-left { background: #f9f9f9; border-right: 1px solid #ddd; }
        .pane-right { background: #fff; }
        textarea { width: 100%; height: 90%; border: 1px solid #ccc; padding: 10px; resize: none; font-family: Arial, sans-serif; font-size: 14px; }
        .word-count { text-align: right; margin-top: 5px; font-weight: bold; }
    </style>
</head>
<body>

<div class="test-header d-flex justify-content-between align-items-center">
    <div>
        <strong>Candidate:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?> |
        <strong>Test:</strong> <?php echo htmlspecialchars($test['title']); ?>
    </div>
    <div class="text-danger fw-bold fs-4" id="timer">60:00</div>
    <div>
        <button class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure you want to exit?')) window.location.href='mock_test.php'">Exit</button>
    </div>
</div>

<form action="submit_test.php" method="POST" id="testForm" class="h-100">
    <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
    <input type="hidden" name="test_type" value="<?php echo $test['type']; ?>">

    <?php if ($test['type'] == 'Full'): ?>
        <!-- Full Test: Tabs for Task 1 and Task 2 -->
        <ul class="nav nav-tabs px-3 pt-2 bg-light" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="task1-tab" data-bs-toggle="tab" data-bs-target="#task1-pane" type="button" role="tab">Task 1</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="task2-tab" data-bs-toggle="tab" data-bs-target="#task2-pane" type="button" role="tab">Task 2</button>
            </li>
        </ul>
        <div class="tab-content h-100" id="myTabContent">
            <!-- Task 1 Pane -->
            <div class="tab-pane fade show active h-100" id="task1-pane" role="tabpanel">
                <div class="test-body h-100">
                    <div class="pane pane-left">
                        <h5>Task 1</h5>
                        <p>You should spend about 20 minutes on this task.</p>
                        <?php if ($test['image_path']): ?>
                            <img src="<?php echo '../' . $test['image_path']; ?>" class="img-fluid mb-3" alt="Task 1 Image">
                        <?php endif; ?>
                        <div class="question-text"><?php echo nl2br($test['question_data']); ?></div>
                    </div>
                    <div class="pane pane-right">
                        <textarea name="answer_task1" id="answer_task1" placeholder="Type your answer here..."></textarea>
                        <div class="word-count">Words: <span id="wc_task1">0</span></div>
                    </div>
                </div>
            </div>
            <!-- Task 2 Pane -->
            <div class="tab-pane fade h-100" id="task2-pane" role="tabpanel">
                <div class="test-body h-100">
                    <div class="pane pane-left">
                        <h5>Task 2</h5>
                        <p>You should spend about 40 minutes on this task.</p>
                        <div class="question-text"><?php echo nl2br($test['question_data']); // Assuming question data contains both or structured text. For simplicity, just showing generic text or if structured JSON, parse it. But user said "question" in singular. For Full test, we need 2 questions. ?></div>
                        <!-- Note: DB schema has 1 question field. For full test, admin should put both questions or use JSON. I will assume admin enters "Task 1: ... Task 2: ..." in the text field or I'll fix this in Admin Panel later. For now, displaying as is. -->
                    </div>
                    <div class="pane pane-right">
                        <textarea name="answer_task2" id="answer_task2" placeholder="Type your answer here..."></textarea>
                        <div class="word-count">Words: <span id="wc_task2">0</span></div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Single Task -->
        <div class="test-body">
            <div class="pane pane-left">
                <h5><?php echo $test['type']; ?></h5>
                <?php if ($test['image_path']): ?>
                    <img src="<?php echo '../' . $test['image_path']; ?>" class="img-fluid mb-3" alt="Task Image">
                <?php endif; ?>
                <div class="question-text"><?php echo nl2br($test['question_data']); ?></div>
            </div>
            <div class="pane pane-right">
                <textarea name="answer" id="answer" placeholder="Type your answer here..."></textarea>
                <div class="word-count">Words: <span id="wc">0</span></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="fixed-bottom bg-light p-2 border-top text-end">
        <button type="submit" class="btn btn-success px-4">Submit Test</button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Word Count Logic
    function updateWordCount(textareaId, spanId) {
        const text = document.getElementById(textareaId).value;
        const count = text.trim().split(/\s+/).filter(word => word.length > 0).length;
        document.getElementById(spanId).innerText = count;
    }

    if (document.getElementById('answer')) {
        document.getElementById('answer').addEventListener('input', () => updateWordCount('answer', 'wc'));
    }
    if (document.getElementById('answer_task1')) {
        document.getElementById('answer_task1').addEventListener('input', () => updateWordCount('answer_task1', 'wc_task1'));
    }
    if (document.getElementById('answer_task2')) {
        document.getElementById('answer_task2').addEventListener('input', () => updateWordCount('answer_task2', 'wc_task2'));
    }

    // Timer Logic
    let timeLeft = 60 * 60; // 60 minutes
    const timerElement = document.getElementById('timer');

    const timerInterval = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        timerElement.innerText = `${minutes}:${seconds}`;

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            alert("Time is up! Submitting test automatically.");
            document.getElementById('testForm').submit();
        }
        timeLeft--;
    }, 1000);
</script>
</body>
</html>
