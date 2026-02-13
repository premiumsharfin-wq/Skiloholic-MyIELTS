<?php
require_once 'includes/header.php';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = sanitize($_POST['message']);

    // Check if key exists
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = 'broadcast_message'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'broadcast_message'");
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('broadcast_message', ?)");
    }
    $stmt->execute([$message]);

    flash('success', "Broadcast message updated.");
    redirect('broadcast.php');
}

// Fetch Current
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'broadcast_message'");
$stmt->execute();
$current_message = $stmt->fetchColumn();
?>

<h2>Broadcast Message</h2>
<p>This message will be visible on the Homepage and User Profile.</p>

<div class="card shadow-sm col-md-8">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Message Content</label>
                <textarea name="message" class="form-control" rows="5" placeholder="Enter announcement here..."><?php echo htmlspecialchars($current_message ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Publish Broadcast</button>
            <?php if ($current_message): ?>
                <a href="broadcast.php?clear=1" class="btn btn-outline-danger float-end" onclick="document.querySelector('textarea').value=''; return true;">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
