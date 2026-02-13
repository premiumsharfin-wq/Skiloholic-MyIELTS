<?php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    redirect('support.php');
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>

<h2>Support Messages</h2>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr><td colspan="6" class="text-center py-4">No messages found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo $msg['status'] === 'unread' ? 'table-warning' : ''; ?>">
                                <td><?php echo format_date($msg['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                                <td><div style="max-width: 300px; max-height: 100px; overflow: auto;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div></td>
                                <td>
                                    <?php if ($msg['status'] === 'unread'): ?>
                                        <span class="badge bg-danger">Unread</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Read</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($msg['status'] === 'unread'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="mark_read" value="1">
                                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Mark as Read"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
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
