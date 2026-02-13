<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../config/mail.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $success = "Password changed successfully.";

            // Send email notification
            sendEmail($user['email'], "Security Alert: Password Changed", "Your password was changed successfully.");
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password incorrect.";
    }
}

// Handle Email Change Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_email_change'])) {
    $new_email = sanitize($_POST['new_email']);

    // Check if new email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$new_email]);
    if ($stmt->fetch()) {
        $error = "Email already in use.";
    } else {
        $old_code = generateVerificationCode();
        $new_code = generateVerificationCode();
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $stmt = $pdo->prepare("INSERT INTO email_changes (user_id, new_email, old_email_code, new_email_code, expiry) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $new_email, $old_code, $new_code, $expiry]);

        // Send Emails
        $user_email = $_SESSION['user_email'];
        sendEmail($user_email, "Email Change Request", "Code for OLD email: <strong>$old_code</strong>");
        sendEmail($new_email, "Email Change Request", "Code for NEW email: <strong>$new_code</strong>");

        $_SESSION['email_change_pending'] = true;
        $success = "Verification codes sent to both emails.";
    }
}

// Handle Email Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_email_change'])) {
    $old_code_input = $_POST['old_code'];
    $new_code_input = $_POST['new_code'];

    $stmt = $pdo->prepare("SELECT * FROM email_changes WHERE user_id = ? AND expiry > NOW() ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $change = $stmt->fetch();

    if ($change) {
        if ($change['old_email_code'] === $old_code_input && $change['new_email_code'] === $new_code_input) {
            // Update User Email
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$change['new_email'], $user_id]);

            // Cleanup
            $stmt = $pdo->prepare("DELETE FROM email_changes WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $_SESSION['user_email'] = $change['new_email'];
            unset($_SESSION['email_change_pending']);
            $success = "Email updated successfully.";
        } else {
            $error = "Invalid verification codes.";
        }
    } else {
        $error = "Request expired or invalid.";
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2 class="mb-4">Settings</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Change Password -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">Change Password</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Change Email -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">Change Email Address</div>
            <div class="card-body">
                <p class="text-muted small">Changing your email requires verification from both your current email and the new email address.</p>

                <?php if (!isset($_SESSION['email_change_pending'])): ?>
                <form method="POST">
                    <input type="hidden" name="request_email_change" value="1">
                    <div class="mb-3">
                        <label>New Email Address</label>
                        <input type="email" name="new_email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Request Change</button>
                </form>
                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="verify_email_change" value="1">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Code sent to Old Email</label>
                            <input type="text" name="old_code" class="form-control" placeholder="123456" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Code sent to New Email</label>
                            <input type="text" name="new_code" class="form-control" placeholder="123456" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Verify & Update</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
