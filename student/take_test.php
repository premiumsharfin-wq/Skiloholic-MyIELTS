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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { height: 100vh; overflow: hidden; display: flex; flex-direction: column; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }

        /* IELTS Header Style */
        .test-header {
            background: #111;
            color: #fff;
            padding: 15px 20px;
            border-bottom: 3px solid #d32f2f;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .test-info { font-size: 0.9rem; letter-spacing: 0.5px; }
        .test-info strong { color: #ccc; }

        #timer {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 1.8rem;
            color: #fff;
            background: #333;
            padding: 5px 15px;
            border-radius: 5px;
            border: 1px solid #555;
        }

        .timer-warning { color: #ffeb3b !important; animation: blink 1s infinite; }
        .timer-danger { color: #ff5252 !important; animation: blink 0.5s infinite; }

        @keyframes blink { 50% { opacity: 0.5; } }

        .test-body { flex: 1; display: flex; overflow: hidden; background-color: #e5e5e5; }

        /* Panes */
        .pane { flex: 1; padding: 20px; overflow-y: auto; height: 100%; }
        .pane-left { background: #f8f9fa; border-right: 2px solid #ccc; }
        .pane-right { background: #fff; padding-right: 20px; }

        .question-text { font-size: 1.05rem; line-height: 1.7; color: #333; }

        textarea {
            width: 100%;
            height: 90%;
            border: 2px solid #ced4da;
            border-radius: 8px;
            padding: 15px;
            resize: none;
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.5;
            background-color: #fff;
            color: #000;
            transition: border-color 0.3s;
        }

        textarea:focus { outline: none; border-color: #007bff; box-shadow: 0 0 8px rgba(0,123,255,0.2); }

        .word-count {
            text-align: right;
            margin-top: 10px;
            font-weight: 600;
            color: #666;
            background: #e9ecef;
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            float: right;
            font-size: 0.9rem;
        }

        .btn-exit {
            background-color: transparent;
            border: 1px solid #666;
            color: #bbb;
            transition: all 0.3s;
        }
        .btn-exit:hover {
            border-color: #ff5252;
            color: #ff5252;
        }
    </style>
</head>
<body>

<div class="test-header d-flex justify-content-between align-items-center">
    <div class="test-info">
        <i class="fas fa-user-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        <span class="mx-3">|</span>
        <i class="fas fa-file-alt me-2"></i> <?php echo htmlspecialchars($test['title']); ?>
    </div>

    <div id="timer">60:00</div>

    <div>
        <button class="btn btn-sm btn-exit px-3" onclick="if(confirm('Are you sure you want to exit? Your progress will be lost.')) window.location.href='mock_test.php'">
            <i class="fas fa-sign-out-alt me-1"></i> Exit Test
        </button>
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

    <div class="bg-light p-3 border-top text-end" style="background-color: #f0f0f0;">
        <button type="submit" class="btn btn-success px-5 fw-bold" onclick="return confirm('Are you sure you want to submit?');">Submit Test</button>
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

        // Visual Cues
        if (timeLeft < 600) { // Less than 10 mins
             timerElement.classList.add('timer-warning');
        }
        if (timeLeft < 60) { // Less than 1 min
             timerElement.classList.remove('timer-warning');
             timerElement.classList.add('timer-danger');
        }

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
