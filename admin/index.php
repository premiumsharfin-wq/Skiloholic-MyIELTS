<?php
require_once 'includes/header.php';

// Stats
$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['tests'] = $pdo->query("SELECT COUNT(*) FROM tests")->fetchColumn();
$stats['pending'] = $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'pending'")->fetchColumn()
                    + $pdo->query("SELECT COUNT(*) FROM custom_evaluations WHERE status = 'pending'")->fetchColumn();
$stats['submissions'] = $pdo->query("SELECT COUNT(*) FROM test_results")->fetchColumn()
                        + $pdo->query("SELECT COUNT(*) FROM custom_evaluations")->fetchColumn();

// Recent Activity (Pending Submissions)
$stmt = $pdo->query("SELECT tr.id, u.name, t.title, tr.submitted_at as date, 'test' as type
                     FROM test_results tr
                     JOIN users u ON tr.user_id = u.id
                     JOIN tests t ON tr.test_id = t.id
                     WHERE tr.status = 'pending'
                     ORDER BY tr.submitted_at ASC LIMIT 5");
$recent_tests = $stmt->fetchAll();

$stmt = $pdo->query("SELECT ce.id, u.name, 'Custom Evaluation' as title, ce.created_at as date, 'custom' as type
                     FROM custom_evaluations ce
                     JOIN users u ON ce.user_id = u.id
                     WHERE ce.status = 'pending'
                     ORDER BY ce.created_at ASC LIMIT 5");
$recent_custom = $stmt->fetchAll();

$recent = array_merge($recent_tests, $recent_custom);
usort($recent, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']); // Oldest first (FIFO)
});
?>

<h2 class="mb-4">Admin Dashboard</h2>

<div class="row mb-4 g-4">
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-primary h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50 small fw-bold">Total Users</h6>
                        <h2 class="mb-0 display-6 fw-bold"><?php echo $stats['users']; ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-25"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link text-decoration-none" href="users.php">View Details</a>
                <div class="text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-success h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50 small fw-bold">Total Tests</h6>
                        <h2 class="mb-0 display-6 fw-bold"><?php echo $stats['tests']; ?></h2>
                    </div>
                    <i class="fas fa-file-alt fa-3x opacity-25"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link text-decoration-none" href="tests.php">Manage Tests</a>
                <div class="text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-warning h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50 small fw-bold">Pending Reviews</h6>
                        <h2 class="mb-0 display-6 fw-bold"><?php echo $stats['pending']; ?></h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-25"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link text-decoration-none" href="evaluation.php">Start Evaluation</a>
                <div class="text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-info h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-white-50 small fw-bold">Total Submissions</h6>
                        <h2 class="mb-0 display-6 fw-bold"><?php echo $stats['submissions']; ?></h2>
                    </div>
                    <i class="fas fa-tasks fa-3x opacity-25"></i>
                </div>
            </div>
             <div class="card-footer d-flex align-items-center justify-content-between small">
                <a class="text-white stretched-link text-decoration-none" href="evaluation.php">View All</a>
                <div class="text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Pending Evaluations (Oldest First)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recent)): ?>
            <div class="p-3 text-center text-muted">No pending evaluations.</div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Test Title</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo format_date($item['date']); ?></td>
                            <td><a href="evaluate_submission.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn btn-sm btn-primary">Evaluate</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
