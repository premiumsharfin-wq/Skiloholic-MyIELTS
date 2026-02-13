<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/mail.php';

if (!isset($_SESSION['verification_email']) && !isLoggedIn()) {
    redirect('login.php');
}

$email = $_SESSION['verification_email'] ?? $_SESSION['user_email'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitize($_POST['code']);

    // Check code
    $stmt = $pdo->prepare("SELECT id, verification_code, verification_expiry FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['verification_code'] === $code && strtotime($user['verification_expiry']) > time()) {
            // Success
            $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL, verification_expiry = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Send Welcome Email
            $subject = "Welcome to MyIELTS Premium Service";
            $body = "<h1>Welcome to MyIELTS!</h1>
                     <p>We are thrilled to have you on board.</p>
                     <p>Did you know about our <strong>In-Person Premium Service</strong>?</p>
                     <p>We offer one-to-one guidance sessions to help you ace your exam.</p>
                     <p>Contact us via WhatsApp: <strong>+8801724413624</strong></p>
                     <p>Happy Learning!</p>";
            sendEmail($email, $subject, $body);

            flash('success', "Email verified successfully! Please login.");
            unset($_SESSION['verification_email']);
            redirect('login.php');
        } else {
            $error = "Invalid or expired verification code.";
        }
    } else {
        $error = "User not found.";
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="font-weight-light my-2">Verify Email</h3>
                </div>
                <div class="card-body">
                    <p class="text-center">Enter the code sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control text-center" style="letter-spacing: 5px; font-size: 1.5rem;" id="code" name="code" placeholder="123456" maxlength="6" required>
                            <label for="code">Verification Code</label>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit">Verify</button>
                            <a href="resend_code.php" class="btn btn-link text-decoration-none">Resend Code</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
