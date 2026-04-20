<?php
$pageTitle = 'View Member - Admin';
$adminPage = 'members';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    setFlash('danger', 'Invalid member ID.');
    redirect(SITE_URL . '/admin/members/index.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    setFlash('danger', 'Member not found.');
    redirect(SITE_URL . '/admin/members/index.php');
}
?>

<style>
    .admin-main .card-custom { height: auto; }
    .admin-main .card-custom:hover { transform: none; }
</style>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Member Details</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/members/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Members
            </a>
        </div>
    </div>

    <div class="admin-main">
        <!-- Status Banner -->
        <div class="alert <?php
            if ($member['status'] === 'approved') echo 'alert-success';
            elseif ($member['status'] === 'pending') echo 'alert-warning';
            else echo 'alert-danger';
        ?> d-flex align-items-center justify-content-between mb-4">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                Status: <strong><?= ucfirst($member['status']) ?></strong>
                &bull; Joined: <strong><?= formatDate($member['created_at']) ?></strong>
            </div>
            <div>
                <?php if ($member['status'] === 'pending'): ?>
                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </form>
                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
                    </form>
                <?php elseif ($member['status'] === 'approved'): ?>
                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-pause-fill me-1"></i>Suspend</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1"></i>Re-approve</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- Profile Card -->
            <div class="col-lg-4">
                <div class="card card-custom text-center p-4">
                    <div class="mb-3">
                        <?php if ($member['profile_image']): ?>
                            <img src="<?= UPLOAD_URL ?>members/<?= sanitize($member['profile_image']) ?>" alt="Profile"
                                 style="width:140px;height:140px;border-radius:50%;object-fit:cover;border:4px solid var(--color-primary);">
                        <?php else: ?>
                            <div style="width:140px;height:140px;border-radius:50%;background:var(--color-light);display:flex;align-items:center;justify-content:center;margin:0 auto;border:4px solid var(--color-primary);">
                                <i class="bi bi-person-fill" style="font-size:3.5rem;color:var(--color-primary);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="mb-1"><?= sanitize($member['name']) ?></h5>
                    <p class="text-muted small mb-2"><?= sanitize($member['email']) ?></p>
                    <span class="badge <?= ($member['membership_plan'] ?? 'free') === 'premium' ? 'bg-warning text-dark' : 'bg-secondary' ?> mb-2">
                        <i class="bi bi-star-fill me-1"></i><?= ucfirst($member['membership_plan'] ?? 'free') ?> Member
                    </span>
                    <?php if (($member['membership_plan'] ?? 'free') === 'premium' && $member['plan_expires_at']): ?>
                        <p class="text-muted small mb-0">Expires: <?= formatDate($member['plan_expires_at']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Details -->
            <div class="col-lg-8">
                <!-- Combined: Anandamoyee Details + Personal Info -->
                <div class="card card-custom mb-3">
                    <div class="card-body p-4">
                        <h6 class="mb-3"><i class="bi bi-mortarboard-fill me-2" style="color:var(--color-primary);"></i>Anandamoyee Details</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small d-block">Batch</label>
                                <strong><?= sanitize($member['batch'] ?? 'N/A') ?></strong>
                            </div>
                        </div>

                        <hr class="my-3">

                        <h6 class="mb-3"><i class="bi bi-person-lines-fill me-2" style="color:var(--color-primary);"></i>Personal Information</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Full Name</label>
                                <strong><?= sanitize($member['name']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Email Address</label>
                                <strong><?= sanitize($member['email']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Phone</label>
                                <strong><?= sanitize($member['phone'] ?? 'N/A') ?></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Home Address</label>
                                <strong><?= sanitize($member['address'] ?? 'N/A') ?></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Registered</label>
                                <strong><?= formatDate($member['created_at'], 'M d, Y \a\t h:i A') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                <?php if (!empty($member['bio'])): ?>
                <div class="card card-custom mb-3">
                    <div class="card-body p-4">
                        <h6 class="mb-2"><i class="bi bi-chat-quote me-2" style="color:var(--color-primary);"></i>Bio</h6>
                        <p class="text-muted mb-0"><?= nl2br(sanitize($member['bio'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Social Links -->
                <?php if (!empty($member['linkedin']) || !empty($member['facebook']) || !empty($member['twitter'])): ?>
                <div class="card card-custom">
                    <div class="card-body p-4">
                        <h6 class="mb-2"><i class="bi bi-link-45deg me-2" style="color:var(--color-primary);"></i>Social Links</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <?php if (!empty($member['linkedin'])): ?>
                                <a href="<?= sanitize($member['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-primary-custom">
                                    <i class="bi bi-linkedin me-1"></i> LinkedIn
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($member['facebook'])): ?>
                                <a href="<?= sanitize($member['facebook']) ?>" target="_blank" class="btn btn-sm btn-outline-primary-custom">
                                    <i class="bi bi-facebook me-1"></i> Facebook
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($member['twitter'])): ?>
                                <a href="<?= sanitize($member['twitter']) ?>" target="_blank" class="btn btn-sm btn-outline-primary-custom">
                                    <i class="bi bi-twitter-x me-1"></i> Twitter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
