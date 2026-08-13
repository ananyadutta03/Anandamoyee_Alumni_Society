<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'Change Password - ' . SITE_NAME;
$userPage = 'password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/user/change_password.php');
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $errors = [];

    if (empty($currentPassword)) $errors[] = 'Current password is required.';
    if (strlen($newPassword) < 8) $errors[] = 'New password must be at least 8 characters.';
    if ($newPassword !== $confirmPassword) $errors[] = 'New passwords do not match.';

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $_SESSION['user_id']]);

        setFlash('success', 'Password changed successfully!');
        redirect(SITE_URL . '/user/change_password.php');
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
            <h4 class="page-title">Change Password</h4>
            <div class="user-info">
                <a href="<?= SITE_URL ?>/user/dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="admin-main">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="admin-form">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock" style="font-size:3rem;color:var(--color-primary);"></i>
                            <h5 class="mt-2">Update Your Password</h5>
                            <p class="text-muted small">Enter your current password and choose a new one</p>
                        </div>

                        <form method="POST" action="">
                            <?= csrfField() ?>

                        
<div class="mb-3">
    <label class="form-label">Current Password *</label>

    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-lock"></i>
        </span>

        <input
            type="password"
            name="current_password"
            id="currentPassword"
            class="form-control"
            required
            placeholder="Enter current password"
        >

        <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="togglePassword('currentPassword', 'currentPasswordIcon')"
            tabindex="-1"
            aria-label="Show or hide current password"
        >
            <i class="bi bi-eye" id="currentPasswordIcon"></i>
        </button>
    </div>
</div>

<hr>

<div class="mb-3">
    <label class="form-label">New Password *</label>

    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-lock-fill"></i>
        </span>

        <input
            type="password"
            name="new_password"
            id="newPassword"
            class="form-control"
            required
            minlength="8"
            placeholder="Min 8 characters"
        >

        <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="togglePassword('newPassword', 'newPasswordIcon')"
            tabindex="-1"
            aria-label="Show or hide new password"
        >
            <i class="bi bi-eye" id="newPasswordIcon"></i>
        </button>
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Confirm New Password *</label>

    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-lock-fill"></i>
        </span>

        <input
            type="password"
            name="confirm_password"
            id="confirmPassword"
            class="form-control"
            required
            placeholder="Re-enter new password"
        >

        <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="togglePassword('confirmPassword', 'confirmPasswordIcon')"
            tabindex="-1"
            aria-label="Show or hide confirm password"
        >
            <i class="bi bi-eye" id="confirmPasswordIcon"></i>
        </button>
    </div>
</div>



                            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                                <i class="bi bi-check-lg me-1"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
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
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === "password") {
            input.type = "text";

            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        } else {
            input.type = "password";

            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }
    }
</script>

</body>
</html>
