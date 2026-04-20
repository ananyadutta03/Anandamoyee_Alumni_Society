<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'Edit Profile - ' . SITE_NAME;
$userPage = 'profile';

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect(SITE_URL . '/auth/logout.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/user/profile.php');
    }

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $twitter  = trim($_POST['twitter'] ?? '');
    $errors   = [];

    if (empty($name))  $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

    // Check email uniqueness (exclude current user)
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->execute([$email, $_SESSION['user_id']]);
        if ($checkStmt->fetch()) {
            $errors[] = 'This email is already in use by another account.';
        }
    }

    // Handle profile picture
    $profileImage = $user['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $newImage = uploadImage($_FILES['profile_image'], 'members');
        if ($newImage) {
            if ($profileImage) deleteImage($profileImage, 'members');
            $profileImage = $newImage;
        } else {
            $errors[] = 'Invalid image file. Allowed: jpg, jpeg, png, gif, webp (max 5MB).';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, bio = ?, linkedin = ?, facebook = ?, twitter = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([
            $name, $email, $phone ?: null, $address ?: null, $bio ?: null,
            $linkedin ?: null, $facebook ?: null, $twitter ?: null,
            $profileImage, $_SESSION['user_id']
        ]);

        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        setFlash('success', 'Profile updated successfully!');
        redirect(SITE_URL . '/user/profile.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include __DIR__ . '/includes/user_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <h4 class="page-title">Edit Profile</h4>
            <div class="user-info">
                <a href="<?= SITE_URL ?>/user/dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="admin-main">
            <div class="admin-form">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <!-- Profile Picture -->
                    <div class="text-center mb-4">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?= UPLOAD_URL ?>members/<?= sanitize($user['profile_image']) ?>" alt="Profile"
                                 id="profilePreview" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--color-primary);">
                        <?php else: ?>
                            <div id="profilePlaceholder" style="width:120px;height:120px;border-radius:50%;background:var(--color-light);display:flex;align-items:center;justify-content:center;margin:0 auto;border:4px solid var(--color-primary);">
                                <i class="bi bi-person-fill" style="font-size:3rem;color:var(--color-primary);"></i>
                            </div>
                            <img id="profilePreview" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--color-primary);display:none;" alt="Preview">
                        <?php endif; ?>
                        <div class="mt-2">
                            <label for="profileImageInput" class="btn btn-sm btn-outline-primary-custom" style="cursor:pointer;">
                                <i class="bi bi-camera me-1"></i> Change Photo
                            </label>
                            <input type="file" name="profile_image" id="profileImageInput" accept="image/*" class="d-none">
                        </div>
                    </div>

                    <hr>

                    <!-- Read-only Anandamoyee Details -->
                    <p class="text-muted small mb-3"><i class="bi bi-lock me-1"></i>Anandamoyee Details (cannot be changed)</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Batch</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['batch'] ?? 'N/A') ?>" disabled style="background:#e9ecef;">
                        </div>
                    </div>

                    <hr>

                    <!-- Editable Fields -->
                    <p class="text-muted small mb-3"><i class="bi bi-pencil me-1"></i>Personal Information</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= sanitize($user['name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required value="<?= sanitize($user['email']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="+880...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Home Address</label>
                            <input type="text" name="address" class="form-control" value="<?= sanitize($user['address'] ?? '') ?>" placeholder="Your home address...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="4" placeholder="Write a short bio about yourself..."><?= sanitize($user['bio'] ?? '') ?></textarea>
                    </div>

                    <hr>
                    <p class="text-muted small mb-3"><i class="bi bi-link-45deg me-1"></i>Social Links</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fab fa-linkedin-in me-1"></i> LinkedIn</label>
                            <input type="url" name="linkedin" class="form-control" value="<?= sanitize($user['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fab fa-facebook-f me-1"></i> Facebook</label>
                            <input type="url" name="facebook" class="form-control" value="<?= sanitize($user['facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fab fa-twitter me-1"></i> Twitter</label>
                            <input type="url" name="twitter" class="form-control" value="<?= sanitize($user['twitter'] ?? '') ?>" placeholder="https://twitter.com/...">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                        <a href="<?= SITE_URL ?>/user/dashboard.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
<script>
// Profile image preview
document.getElementById('profileImageInput').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById('profilePreview');
            let placeholder = document.getElementById('profilePlaceholder');
            preview.src = e.target.result;
            preview.style.display = 'inline-block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
