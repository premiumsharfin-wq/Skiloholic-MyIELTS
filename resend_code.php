<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/mail.php';

$email = $_SESSION['verification_email'] ?? $_SESSION['user_email'] ?? '';

if (!$email) {
    redirect('login.php');
}

// Generate new code
$verification_code = generateVerificationCode();
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expiry = ? WHERE email = ?");
$stmt->execute([$verification_code, $expiry, $email]);

// Send Email
$subject = "Resend Verification Code - MyIELTS";
$body = "<h2>Verification Code</h2>
         <p>Your new verification code is: <strong>$verification_code</strong></p>
         <p>This code expires in 5 minutes.</p>";

if (sendEmail($email, $subject, $body)) {
    flash('success', "A new verification code has been sent.");
} else {
    flash('error', "Failed to send email.");
}

redirect('verify.php');
?>