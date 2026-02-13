<div class="list-group shadow-sm mb-4">
    <div class="list-group-item text-center bg-light">
        <img src="<?php echo get_profile_pic($user['profile_pic'] ?? null); ?>" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover;">
        <h5 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h5>
        <span class="badge bg-primary rounded-pill"><?php echo ucfirst($user['role']); ?></span>
    </div>
    <a href="index.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
    </a>
    <a href="profile.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user me-2"></i> My Profile
    </a>
    <a href="mock_test.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'mock_test.php' ? 'active' : ''; ?>">
        <i class="fas fa-edit me-2"></i> Mock Test
    </a>
    <a href="test_history.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'test_history.php' ? 'active' : ''; ?>">
        <i class="fas fa-history me-2"></i> Test History
    </a>
    <a href="custom_evaluation.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'custom_evaluation.php' ? 'active' : ''; ?>">
        <i class="fas fa-pen-fancy me-2"></i> Custom Evaluation
    </a>
    <a href="settings.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog me-2"></i> Settings
    </a>
    <?php if(isAdmin()): ?>
        <a href="../admin/index.php" class="list-group-item list-group-item-action list-group-item-danger">
            <i class="fas fa-user-shield me-2"></i> Admin Panel
        </a>
    <?php endif; ?>
    <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
    </a>
</div>
