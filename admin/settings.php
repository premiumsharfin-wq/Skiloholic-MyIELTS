<?php
require_once 'includes/header.php';
require_once '../config/mail.php';

// Fetch Current Status
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
$stmt->execute();
$maintenance = $stmt->fetchColumn() === '1';

// Handle Toggle Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['disable_maintenance'])) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = '0' WHERE setting_key = 'maintenance_mode'");
        $stmt->execute();
        flash('success', "Maintenance mode disabled. Website is live.");
        redirect('settings.php');
    }

    if (isset($_POST['request_maintenance'])) {
        // Send Verification Code
        $code = generateVerificationCode();
        $_SESSION['maintenance_code'] = $code;
        $_SESSION['maintenance_expiry'] = time() + 300; // 5 mins

        $email = $_SESSION['user_email'];
        sendEmail($email, "Maintenance Mode Verification", "Code to enable maintenance mode: <strong>$code</strong>");

        $_SESSION['maintenance_pending'] = true;
        flash('info', "Verification code sent to your email.");
    }

    if (isset($_POST['verify_maintenance'])) {
        $code = $_POST['code'];
        $end_time = $_POST['end_time'];

        if (isset($_SESSION['maintenance_code']) && $_SESSION['maintenance_code'] === $code && time() < $_SESSION['maintenance_expiry']) {
            // Enable Maintenance
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_mode', '1') ON DUPLICATE KEY UPDATE setting_value = '1'");
            $stmt->execute();

            // Set End Time
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_end', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$end_time, $end_time]);

            unset($_SESSION['maintenance_code']);
            unset($_SESSION['maintenance_pending']);
            flash('success', "Maintenance mode enabled.");
            redirect('settings.php');
        } else {
            flash('error', "Invalid or expired code.");
        }
    }
}
?>

<h2>System Settings</h2>

<div class="card shadow-sm col-md-6">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-tools me-2"></i> Maintenance Mode</h5>
    </div>
    <div class="card-body">
        <p>
            Current Status:
            <?php if ($maintenance): ?>
                <span class="badge bg-danger fs-5">Maintenance Mode ON</span>
            <?php else: ?>
                <span class="badge bg-success fs-5">Live</span>
            <?php endif; ?>
        </p>

        <?php if ($maintenance): ?>
            <form method="POST">
                <input type="hidden" name="disable_maintenance" value="1">
                <button type="submit" class="btn btn-success">Go Live (Disable Maintenance)</button>
            </form>
        <?php else: ?>
            <?php if (!isset($_SESSION['maintenance_pending'])): ?>
                <form method="POST">
                    <input type="hidden" name="request_maintenance" value="1">
                    <button type="submit" class="btn btn-danger">Enable Maintenance Mode</button>
                </form>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="verify_maintenance" value="1">
                    <div class="mb-3">
                        <label>Enter Verification Code</label>
                        <input type="text" name="code" class="form-control" placeholder="123456" required>
                    </div>
                    <div class="mb-3">
                        <label>Expected End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Verify & Enable</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
