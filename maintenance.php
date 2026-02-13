<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Check if really maintenance
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
$stmt->execute();
$maintenance = $stmt->fetchColumn() === '1';

if (!$maintenance) {
    redirect('index.php');
}

// Fetch End Time
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_end'");
$stmt->execute();
$end_time = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - MyIELTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .maintenance-card { text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; width: 100%; }
        .logo { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="maintenance-card">
    <img src="assets/images/logo.png" alt="MyIELTS" height="60" class="logo">
    <h1 class="mb-3">We'll be back soon!</h1>
    <p class="lead text-muted mb-4">
        Our website is currently undergoing scheduled maintenance. We apologize for the inconvenience and appreciate your patience.
    </p>

    <?php if ($end_time): ?>
        <h5 class="mb-3">Expected to be back in:</h5>
        <div id="countdown" class="display-4 fw-bold text-primary mb-4"></div>
        <script>
            const endTime = new Date("<?php echo $end_time; ?>").getTime();

            const x = setInterval(function() {
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("countdown").innerHTML = "Any moment now!";
                } else {
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    document.getElementById("countdown").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
                }
            }, 1000);
        </script>
    <?php else: ?>
        <p class="fw-bold">We will be back shortly.</p>
    <?php endif; ?>

    <div class="mt-4">
        <a href="mailto:support@myielts.com" class="text-decoration-none">Contact Support</a>
    </div>
</div>

</body>
</html>
