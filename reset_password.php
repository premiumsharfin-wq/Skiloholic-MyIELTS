<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    redirect('login.php');
}

// Check if token valid
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = "Invalid or expired token.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user['id']]);

        flash('success', "Password reset successfully. Please login.");
        redirect('login.php');
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="font-weight-light my-2">Reset Password</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="inputPassword" type="password" name="password" placeholder="New Password" required />
                            <label for="inputPassword">New Password</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="inputConfirmPassword" type="password" name="confirm_password" placeholder="Confirm Password" required />
                            <label for="inputConfirmPassword">Confirm Password</label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                            <a class="small text-decoration-none" href="login.php">Return to login</a>
                            <button class="btn btn-primary" type="submit">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
