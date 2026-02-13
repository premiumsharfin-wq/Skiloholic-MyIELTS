<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Pic Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $maxSize = 4 * 1024 * 1024; // 4MB
        if ($file['size'] > $maxSize) {
            $error = "File size exceeds 4MB limit.";
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($ext), $allowed)) {
                $filename = 'uploads/profile_' . $user_id . '_' . time() . '.' . $ext;
                $target = __DIR__ . '/../' . $filename;

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    // Update DB
                    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    $stmt->execute([$filename, $user_id]);
                    $_SESSION['profile_pic'] = $filename; // Update session
                    $success = "Profile picture updated successfully.";
                } else {
                    $error = "Failed to upload file.";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
            }
        }
    } else {
        $error = "Error uploading file.";
    }
}

// Handle Candidate ID Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    $cid = sanitize($_POST['candidate_id']);
    $stmt = $pdo->prepare("UPDATE users SET candidate_id = ? WHERE id = ?");
    $stmt->execute([$cid, $user_id]);
    $success = "Candidate ID updated.";
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch Broadcast
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'broadcast_message'");
$stmt->execute();
$broadcast = $stmt->fetchColumn();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2 class="mb-4">My Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Admin Notice -->
        <?php if ($broadcast): ?>
            <div class="alert alert-info border-info shadow-sm mb-4">
                <h5 class="alert-heading"><i class="fas fa-bullhorn me-2"></i> Announcement</h5>
                <p class="mb-0"><?php echo htmlspecialchars($broadcast); ?></p>
            </div>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Profile Details</div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-md-3 text-center">
                        <img src="<?php echo get_profile_pic($user['profile_pic']); ?>" class="img-thumbnail rounded-circle mb-2" width="150" height="150" style="object-fit: cover;">
                        <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadModal">Change Photo</button>
                    </div>
                    <div class="col-md-9">
                        <h3 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="mb-2"><span class="badge bg-success rounded-pill px-3 py-2"><?php echo ucfirst($user['role']); ?></span></p>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">EMAIL</label>
                                <p class="lead mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                <?php if($user['email_verified']): ?>
                                    <small class="text-success"><i class="fas fa-check-circle"></i> Verified</small>
                                <?php else: ?>
                                    <small class="text-danger"><i class="fas fa-times-circle"></i> Not Verified</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">MOBILE</label>
                                <p class="lead mb-0"><?php echo htmlspecialchars($user['mobile']); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">CANDIDATE ID</label>
                                <form method="POST" class="d-flex align-items-center">
                                    <input type="text" name="candidate_id" class="form-control me-2" value="<?php echo htmlspecialchars($user['candidate_id'] ?? ''); ?>" placeholder="Set ID">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                </form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small">ACCOUNT STATUS</label>
                                <p class="lead mb-0 text-capitalize"><?php echo htmlspecialchars($user['status']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_pic" class="form-label">Select Image (Max 4MB)</label>
                        <input type="file" class="form-control" name="profile_pic" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
