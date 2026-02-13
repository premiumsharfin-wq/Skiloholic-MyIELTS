<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/mail.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);

        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
        $subject = "Reset your MyIELTS Password";
        $body = "<h2>Password Reset Request</h2>
                 <p>Hi " . htmlspecialchars($user['name']) . ",</p>
                 <p>We received a request to reset your password. Click the link below to set a new password:</p>
                 <p><a href='$reset_link'>$reset_link</a></p>
                 <p>If you didn't request this, please ignore this email.</p>";

        if (sendEmail($email, $subject, $body)) {
            $success = "A password reset link has been sent to your email.";
        } else {
            $error = "Failed to send email. Please try again.";
        }
    } else {
        $error = "If an account exists with that email, a reset link will be sent."; // Don't reveal user existence
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="font-weight-light my-2">Password Recovery</h3>
                </div>
                <div class="card-body">
                    <div class="small mb-3 text-muted">Enter your email address and we will send you a link to reset your password.</div>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="inputEmail" type="email" name="email" placeholder="name@example.com" required />
                            <label for="inputEmail">Email address</label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                            <a class="small text-decoration-none" href="login.php">Return to login</a>
                            <button class="btn btn-primary" type="submit">Reset Password</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="register.php">Need an account? Sign up!</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
