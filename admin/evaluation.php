<?php
require_once 'includes/header.php';

// Fetch all pending
$stmt = $pdo->query("SELECT tr.id, u.name, t.title, tr.submitted_at as date, tr.status, tr.type, 'test' as source
                     FROM test_results tr
                     JOIN users u ON tr.user_id = u.id
                     JOIN tests t ON tr.test_id = t.id
                     ORDER BY tr.submitted_at ASC");
$test_subs = $stmt->fetchAll();

$stmt = $pdo->query("SELECT ce.id, u.name, 'Custom Evaluation' as title, ce.created_at as date, ce.status, 'Custom' as type, 'custom' as source
                     FROM custom_evaluations ce
                     JOIN users u ON ce.user_id = u.id
                     ORDER BY ce.created_at ASC");
$custom_subs = $stmt->fetchAll();

$submissions = array_merge($test_subs, $custom_subs);

// Sort by date (Oldest first for pending, Newest first for evaluated?)
// Let's sort by Status (Pending first), then Date.
usort($submissions, function($a, $b) {
    if ($a['status'] === $b['status']) {
        return strtotime($a['date']) - strtotime($b['date']);
    }
    return $a['status'] === 'pending' ? -1 : 1;
});
?>

<h2>Test Evaluation</h2>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>User</th>
                        <th>Test Title</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr><td colspan="6" class="text-center py-4">No submissions found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): ?>
                            <tr class="<?php echo $sub['status'] === 'pending' ? 'table-warning' : ''; ?>">
                                <td><?php echo htmlspecialchars($sub['name']); ?></td>
                                <td><?php echo htmlspecialchars($sub['title']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($sub['type']); ?></span></td>
                                <td><?php echo format_date($sub['date']); ?></td>
                                <td>
                                    <?php if ($sub['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Evaluated</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="evaluate_submission.php?id=<?php echo $sub['id']; ?>&type=<?php echo $sub['source']; ?>" class="btn btn-sm btn-primary">
                                        <?php echo $sub['status'] === 'pending' ? 'Evaluate' : 'Review'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
