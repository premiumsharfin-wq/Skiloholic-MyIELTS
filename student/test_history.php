<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = $_POST['delete_id'];
    $del_type = $_POST['type']; // 'test' or 'custom'

    try {
        if ($del_type === 'custom') {
            $stmt = $pdo->prepare("DELETE FROM custom_evaluations WHERE id = ? AND user_id = ?");
        } else {
            $stmt = $pdo->prepare("DELETE FROM test_results WHERE id = ? AND user_id = ?");
        }
        $stmt->execute([$del_id, $user_id]);
        $success = "Record deleted successfully.";
    } catch (PDOException $e) {
        $error = "Failed to delete record.";
    }
}

// Fetch Test Results
$stmt = $pdo->prepare("SELECT tr.id, tr.test_id, tr.score_band, tr.status, tr.submitted_at as date, 'test' as type, t.title
                       FROM test_results tr
                       JOIN tests t ON tr.test_id = t.id
                       WHERE tr.user_id = ?
                       ORDER BY tr.submitted_at DESC");
$stmt->execute([$user_id]);
$test_results = $stmt->fetchAll();

// Fetch Custom Evaluations
$stmt = $pdo->prepare("SELECT id, NULL as test_id, score_band, status, created_at as date, 'custom' as type, 'Custom Evaluation' as title
                       FROM custom_evaluations
                       WHERE user_id = ?
                       ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$custom_results = $stmt->fetchAll();

// Merge and Sort
$history = array_merge($test_results, $custom_results);
usort($history, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2 class="mb-4">Test History</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($history)): ?>
            <div class="alert alert-secondary">No history found. Take a test to see results here.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Test / Type</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td><?php echo format_date($item['date']); ?></td>
                                <td>
                                    <?php if ($item['type'] === 'test'): ?>
                                        <span class="badge bg-primary me-2">Mock Test</span>
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary me-2">Custom</span>
                                        Writing Task 2
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Evaluated</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $item['score_band'] ? $item['score_band'] : '-'; ?></strong></td>
                                <td>
                                    <a href="result.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn btn-sm btn-info text-white me-2" title="View Result">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="type" value="<?php echo $item['type']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
