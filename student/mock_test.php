<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

// Fetch Tests
$stmt = $pdo->prepare("SELECT * FROM tests ORDER BY created_at DESC");
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); // Group by ID (not useful here, better just fetchAll)
// Fetch All
$stmt = $pdo->prepare("SELECT * FROM tests ORDER BY created_at DESC");
$stmt->execute();
$all_tests = $stmt->fetchAll();

// Organize by Type
$tests_by_type = [
    'Full' => [],
    'Task 1' => [],
    'Task 2' => []
];

foreach ($all_tests as $t) {
    $tests_by_type[$t['type']][] = $t;
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2 class="mb-4">Mock Tests</h2>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Note: Only Writing modules are available currently. Others coming soon.
        </div>

        <ul class="nav nav-tabs mb-4" id="testTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="full-tab" data-bs-toggle="tab" data-bs-target="#full" type="button">Full Test</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="task1-tab" data-bs-toggle="tab" data-bs-target="#task1" type="button">Task 1</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="task2-tab" data-bs-toggle="tab" data-bs-target="#task2" type="button">Task 2</button>
            </li>
        </ul>

        <div class="tab-content" id="testTabContent">
            <?php foreach (['Full', 'Task 1', 'Task 2'] as $type): ?>
                <div class="tab-pane fade <?php echo $type === 'Full' ? 'show active' : ''; ?>" id="<?php echo strtolower(str_replace(' ', '', $type)); ?>">

                    <?php
                    $current_tests = $tests_by_type[$type];
                    $myielts = array_filter($current_tests, function($t) { return $t['category'] === 'MyIELTS'; });
                    $cambridge = array_filter($current_tests, function($t) { return $t['category'] === 'Cambridge'; });
                    ?>

                    <!-- MyIELTS Series -->
                    <h4 class="mt-3 text-primary">MyIELTS Series</h4>
                    <div class="list-group mb-4">
                        <?php if (empty($myielts)): ?>
                            <div class="list-group-item text-muted">No tests available.</div>
                        <?php else: ?>
                            <?php foreach ($myielts as $test): ?>
                                <a href="take_test.php?id=<?php echo $test['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($test['title']); ?>
                                    <span class="badge bg-primary rounded-pill">Start</span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Cambridge Series -->
                    <h4 class="mt-3 text-danger">Cambridge Series</h4>
                    <div class="list-group">
                        <?php if (empty($cambridge)): ?>
                            <div class="list-group-item text-muted">No tests available.</div>
                        <?php else: ?>
                            <?php foreach ($cambridge as $test): ?>
                                <a href="take_test.php?id=<?php echo $test['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($test['title']); ?>
                                    <span class="badge bg-danger rounded-pill">Start</span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
