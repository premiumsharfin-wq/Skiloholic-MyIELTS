<?php
require_once 'includes/header.php';

// Handle Ban/Unban
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $uid = $_POST['user_id'];
    $new_status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $uid]);
    flash('success', "User status updated to $new_status.");
    redirect('users.php');
}

// Search & List
$search = $_GET['search'] ?? '';
$params = [];
$sql = "SELECT * FROM users WHERE role != 'admin'";

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Users</h2>
    <form class="d-flex">
        <input class="form-control me-2" type="search" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Target</th>
                        <th>Exam Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="text-center py-4">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo '../' . ($user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default_avatar.png'); ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                            <div class="text-muted small">ID: <?php echo htmlspecialchars($user['candidate_id'] ?? '-'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($user['mobile']); ?></div>
                                </td>
                                <td><?php echo $user['target_score'] ? $user['target_score'] : '-'; ?></td>
                                <td><?php echo $user['exam_date'] ? date('M j, Y', strtotime($user['exam_date'])) : '-'; ?></td>
                                <td>
                                    <?php if ($user['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Banned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <input type="hidden" name="status" value="banned">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Ban User" onclick="return confirm('Are you sure you want to ban this user?');">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Unban User">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
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
