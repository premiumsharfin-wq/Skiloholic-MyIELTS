<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/mail.php';

if (isLoggedIn()) {
    redirect('student/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $mobile = sanitize($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($mobile)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $verification_code = generateVerificationCode();
            $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, mobile, verification_code, verification_expiry) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashed_password, $mobile, $verification_code, $expiry])) {
                    // Send Email
                    $subject = "Verify your MyIELTS Account";
                    $body = "<h2>Welcome to MyIELTS</h2>
                             <p>Please verify your email address to complete registration.</p>
                             <p>Your verification code is: <strong>$verification_code</strong></p>
                             <p>This code expires in 5 minutes.</p>";

                    if (sendEmail($email, $subject, $body)) {
                        $_SESSION['verification_email'] = $email;
                        redirect('verify.php');
                    } else {
                        $error = "Registration successful, but failed to send verification email. Please contact support.";
                    }
                } else {
                    $error = "Registration failed. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="font-weight-light my-2">Create Account</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
                            <label for="name">Full Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Mobile Number" required>
                            <label for="mobile">Mobile Number</label>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3 mb-md-0">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                                    <label for="password">Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3 mb-md-0">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                                    <label for="confirm_password">Confirm Password</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 mb-0">
                            <div class="d-grid"><button class="btn btn-primary btn-block" type="submit">Create Account</button></div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="login.php">Have an account? Go to login</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
