<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle Target Score & Exam Date Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_goals'])) {
    $target_score = sanitize($_POST['target_score']);
    $exam_date = sanitize($_POST['exam_date']);

    $stmt = $pdo->prepare("UPDATE users SET target_score = ?, exam_date = ? WHERE id = ?");
    $stmt->execute([$target_score, $exam_date, $_SESSION['user_id']]);
    flash('success', "Goals updated successfully!");
    redirect('index.php');
}

// Fetch Last 4 Test Results
$stmt = $pdo->prepare("SELECT tr.*, t.title FROM test_results tr JOIN tests t ON tr.test_id = t.id WHERE tr.user_id = ? ORDER BY tr.submitted_at DESC LIMIT 4");
$stmt->execute([$_SESSION['user_id']]);
$recent_results = $stmt->fetchAll();

// Calculate Average Band Score (from evaluated tests)
$stmt = $pdo->prepare("SELECT AVG(score_band) FROM test_results WHERE user_id = ? AND status = 'evaluated'");
$stmt->execute([$_SESSION['user_id']]);
$average_score = number_format($stmt->fetchColumn(), 1);

require_once '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <h2 class="mb-4">Dashboard</h2>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">Target Score</h5>
                        <p class="display-4"><?php echo $user['target_score'] ? $user['target_score'] : '-'; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">Average Band Score</h5>
                        <p class="display-4"><?php echo $average_score > 0 ? $average_score : '-'; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">Exam Countdown</h5>
                        <p class="display-6" id="countdown">-</p>
                        <small><?php echo $user['exam_date'] ? date("F j, Y", strtotime($user['exam_date'])) : 'Set date'; ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals Form -->
        <div class="card mb-4">
            <div class="card-header">Set Your Goals</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="update_goals" value="1">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Target Band Score</label>
                            <select name="target_score" class="form-select">
                                <option value="">Select</option>
                                <?php for($i=1; $i<=9; $i+=0.5): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($user['target_score'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exam Date</label>
                            <input type="date" name="exam_date" class="form-control" value="<?php echo $user['exam_date']; ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Update Goals</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header">Recent Mock Tests</div>
            <div class="card-body">
                <?php if (count($recent_results) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_results as $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['title']); ?></td>
                                        <td><?php echo format_date($result['submitted_at']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $result['status'] == 'evaluated' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($result['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $result['score_band'] ? $result['score_band'] : '-'; ?></td>
                                        <td><a href="result.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No tests taken yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Band Score Calculator (Simple Implementation) -->
        <div class="card mb-4">
            <div class="card-header">Band Score Calculator (Overall)</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-3">
                        <input type="number" id="listening" class="form-control" placeholder="L" step="0.5" max="9">
                    </div>
                    <div class="col-3">
                        <input type="number" id="reading" class="form-control" placeholder="R" step="0.5" max="9">
                    </div>
                    <div class="col-3">
                        <input type="number" id="writing" class="form-control" placeholder="W" step="0.5" max="9">
                    </div>
                    <div class="col-3">
                        <input type="number" id="speaking" class="form-control" placeholder="S" step="0.5" max="9">
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-secondary btn-sm" onclick="calculateOverall()">Calculate</button>
                    <span class="ms-3 fw-bold" id="overall_score">Overall: -</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Countdown Timer
<?php if ($user['exam_date']): ?>
    const examDate = new Date("<?php echo $user['exam_date']; ?>").getTime();
    const countdownElement = document.getElementById("countdown");

    const x = setInterval(function() {
        const now = new Date().getTime();
        const distance = examDate - now;

        if (distance < 0) {
            clearInterval(x);
            countdownElement.innerHTML = "EXAM DAY!";
        } else {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            countdownElement.innerHTML = days + " Days";
        }
    }, 1000);
<?php endif; ?>

// Band Calculator
function calculateOverall() {
    const l = parseFloat(document.getElementById('listening').value) || 0;
    const r = parseFloat(document.getElementById('reading').value) || 0;
    const w = parseFloat(document.getElementById('writing').value) || 0;
    const s = parseFloat(document.getElementById('speaking').value) || 0;

    if (l && r && w && s) {
        const avg = (l + r + w + s) / 4;
        // Round to nearest 0.5
        const rounded = Math.round(avg * 2) / 2;
        document.getElementById('overall_score').innerText = "Overall: " + rounded;
    } else {
        alert("Please enter all 4 scores.");
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
