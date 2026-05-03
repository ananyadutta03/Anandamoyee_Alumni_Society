<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'My Dashboard - ' . SITE_NAME;
$userPage = 'dashboard';

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect(SITE_URL . '/auth/logout.php');
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
            <h4 class="page-title">My Dashboard</h4>
            <div class="user-info">
                <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($user['name']) ?></span>
            </div>
        </div>

        <div class="admin-main">
            <!-- Profile Overview Card -->
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card card-custom text-center p-4">
                        <div class="mb-3">
                            <?php if ($user['profile_image']): ?>
                                <img src="<?= UPLOAD_URL ?>members/<?= sanitize($user['profile_image']) ?>" alt="Profile"
                                     style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--color-primary);">
                            <?php else: ?>
                                <div style="width:120px;height:120px;border-radius:50%;background:var(--color-light);display:flex;align-items:center;justify-content:center;margin:0 auto;border:4px solid var(--color-primary);">
                                    <i class="bi bi-person-fill" style="font-size:3rem;color:var(--color-primary);"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5 class="mb-1"><?= sanitize($user['name']) ?></h5>
                        <p class="text-muted small mb-2"><?= sanitize($user['email']) ?></p>
                        <?php
                            $planBadge = ['general' => 'bg-secondary', 'executive' => 'bg-primary', 'lifetime' => 'bg-warning text-dark'];
                            $planLabel = ['general' => 'General Member', 'executive' => 'Executive Member', 'lifetime' => 'Lifetime Member'];
                            $mp = $user['membership_plan'] ?? 'general';
                        ?>
                        <span class="badge <?= $planBadge[$mp] ?? 'bg-secondary' ?> mb-3">
                            <i class="bi bi-star-fill me-1"></i><?= $planLabel[$mp] ?? ucfirst($mp) ?>
                        </span>
                        <a href="<?= SITE_URL ?>/user/profile.php" class="btn btn-sm btn-primary-custom">
                            <i class="bi bi-pencil me-1"></i> Edit Profile
                        </a>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Info Cards Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="stat-card" style="border-left-color:#28a745;">
                                <div class="stat-icon bg-success"><i class="bi bi-calendar3"></i></div>
                                <div class="stat-info">
                                    <h3 style="font-size:1.1rem;"><?= sanitize($user['batch'] ?? 'N/A') ?></h3>
                                    <p>Batch</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat-card" style="border-left-color:#6f42c1;">
                                <div class="stat-icon" style="background:rgba(111,66,193,0.15);color:#6f42c1;"><i class="bi bi-telephone-fill"></i></div>
                                <div class="stat-info">
                                    <h3 style="font-size:1.1rem;"><?= sanitize($user['phone'] ?? 'N/A') ?></h3>
                                    <p>Phone</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Card -->
                    <div class="card card-custom">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><i class="bi bi-star-fill me-2" style="color:var(--color-accent);"></i>Membership Plan</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if ($mp === 'lifetime'): ?>
                                            Lifetime Member &bull; Valid forever
                                        <?php elseif ($mp === 'executive'): ?>
                                            Executive Member
                                            <?php if ($user['plan_expires_at']): ?>
                                                &bull; Expires: <?= formatDate($user['plan_expires_at']) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            General Member &bull; Upgrade to unlock more features
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <a href="<?= SITE_URL ?>/user/membership.php" class="btn btn-sm btn-accent">
                                    <?= $mp === 'general' ? 'Upgrade' : 'Manage' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4">
                <div class="col-md-4">
                    <a href="<?= SITE_URL ?>/user/profile.php" class="card card-custom text-center p-4 text-decoration-none">
                        <i class="bi bi-person-gear" style="font-size:2.5rem;color:var(--color-primary);"></i>
                        <h6 class="mt-2 mb-1">Edit Profile</h6>
                        <p class="text-muted small mb-0">Update your personal information</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= SITE_URL ?>/user/change_password.php" class="card card-custom text-center p-4 text-decoration-none">
                        <i class="bi bi-shield-lock" style="font-size:2.5rem;color:var(--color-accent);"></i>
                        <h6 class="mt-2 mb-1">Change Password</h6>
                        <p class="text-muted small mb-0">Update your account security</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= SITE_URL ?>/user/membership.php" class="card card-custom text-center p-4 text-decoration-none">
                        <i class="bi bi-star" style="font-size:2.5rem;color:#ffc107;"></i>
                        <h6 class="mt-2 mb-1">Membership Plans</h6>
                        <p class="text-muted small mb-0">View or upgrade your plan</p>
                    </a>
                </div>
            </div>

            <!-- Bio Section -->
            <?php if (!empty($user['bio'])): ?>
            <div class="card card-custom mt-4">
                <div class="card-body p-4">
                    <h6><i class="bi bi-person-lines-fill me-2"></i>About Me</h6>
                    <p class="text-muted mb-0"><?= nl2br(sanitize($user['bio'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Social Links -->
            <?php if (!empty($user['linkedin']) || !empty($user['facebook']) || !empty($user['twitter'])): ?>
            <div class="card card-custom mt-3">
                <div class="card-body p-4">
                    <h6><i class="bi bi-link-45deg me-2"></i>Social Links</h6>
                    <div class="d-flex gap-3 mt-2">
                        <?php if (!empty($user['linkedin'])): ?>
                            <a href="<?= sanitize($user['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-primary-custom">
                                <i class="fab fa-linkedin-in me-1"></i> LinkedIn
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($user['facebook'])): ?>
                            <a href="<?= sanitize($user['facebook']) ?>" target="_blank" class="btn btn-sm btn-outline-primary-custom">
                                <i class="fab fa-facebook-f me-1"></i> Facebook
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($user['twitter'])): ?>
                            <a href="<?= sanitize($user['twitter']) ?>" target="_blank" class="btn btn-sm btn-outline-primary-custom">
                                <i class="fab fa-twitter me-1"></i> Twitter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div> <!-- /.admin-main -->
    </div> <!-- /.admin-content -->
</div> <!-- /.admin-wrapper -->

<!-- Flash Messages -->
<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
</body>
</html>
