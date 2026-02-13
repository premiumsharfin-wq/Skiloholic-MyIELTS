<?php
require_once 'includes/header.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    flash('success', "Test deleted successfully.");
    redirect('tests.php');
}

// Handle Add Test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $type = sanitize($_POST['type']);
    $question = $_POST['question']; // Allow some HTML or just raw text? Sanitize for XSS but keep format?
    // Using sanitize() strips tags via htmlspecialchars. This is safe but user might want formatting.
    // Given "donot know programming", safe is better.
    $question = sanitize($question);

    $image_path = null;
    $error = '';

    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        if ($file['size'] > 20 * 1024 * 1024) { // 20MB
            $error = "Image size exceeds 20MB.";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed_exts)) {
                $error = "Invalid file format. Only JPG, JPEG, PNG, GIF, WEBP allowed.";
            } else {
                $filename = 'uploads/test_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], '../' . $filename)) {
                    $image_path = $filename;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }
    } elseif (($type === 'Task 1' || $type === 'Full') && empty($_FILES['image']['name'])) {
         // Task 1 and Full usually need image (for Task 1 part). Prompt said "For task 1 it is mandatory".
         // For Full, it implies Task 1 is included, so mandatory too? I'll make it mandatory for Task 1 only strictly, optional for Full but recommended.
         if ($type === 'Task 1') {
             $error = "Image is mandatory for Task 1.";
         }
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO tests (title, category, type, question_data, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category, $type, $question, $image_path]);
        flash('success', "Test added successfully.");
        redirect('tests.php');
    } else {
        flash('error', $error);
    }
}

// Fetch Tests
$stmt = $pdo->query("SELECT * FROM tests ORDER BY created_at DESC");
$tests = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Tests</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestModal">
        <i class="fas fa-plus me-2"></i> Add New Test
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Image</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tests)): ?>
                        <tr><td colspan="6" class="text-center py-4">No tests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($test['title']); ?></td>
                                <td><?php echo htmlspecialchars($test['category']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $test['type'] === 'Full' ? 'primary' : 'secondary'; ?>">
                                        <?php echo htmlspecialchars($test['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($test['image_path']): ?>
                                        <a href="../<?php echo $test['image_path']; ?>" target="_blank">View Image</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($test['created_at']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $test['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_test" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Test Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="MyIELTS">MyIELTS Series</option>
                                <option value="Cambridge">Cambridge Series</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="Full">Full Test</option>
                                <option value="Task 1">Task 1</option>
                                <option value="Task 2">Task 2</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Data</label>
                        <textarea name="question" class="form-control" rows="5" required placeholder="Enter the question text here. For Full Tests, include both tasks."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference Image (Max 20MB)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Mandatory for Task 1.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
