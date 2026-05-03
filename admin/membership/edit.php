<?php
$pageTitle = 'Edit Member Plan - Admin';
$adminPage = 'membership';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    setFlash('danger', 'Member not found.');
    redirect(SITE_URL . '/admin/membership/index.php');
}

$planLabels = ['general' => 'General Member', 'executive' => 'Executive Member', 'lifetime' => 'Lifetime Member'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/membership/edit.php?id=' . $id);
    }

    $newPlan   = $_POST['membership_plan'] ?? 'general';
    $expiresAt = trim($_POST['plan_expires_at'] ?? '');

    if ($newPlan === 'lifetime') {
        $pdo->prepare("UPDATE users SET membership_plan = 'lifetime', plan_expires_at = NULL WHERE id = ?")->execute([$id]);
    } elseif ($newPlan === 'executive') {
        $exp = $expiresAt ?: date('Y-m-d', strtotime('+12 months'));
        $pdo->prepare("UPDATE users SET membership_plan = 'executive', plan_expires_at = ? WHERE id = ?")->execute([$exp, $id]);
    } else {
        $pdo->prepare("UPDATE users SET membership_plan = 'general', plan_expires_at = NULL WHERE id = ?")->execute([$id]);
    }

    setFlash('success', 'Membership plan updated for ' . $member['name']);
    redirect(SITE_URL . '/admin/membership/index.php');
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Edit Member Plan</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/membership/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Member Info -->
                <div class="card card-custom mb-4">
                    <div class="card-body p-4 text-center">
                        <h5><?= sanitize($member['name']) ?></h5>
                        <p class="text-muted mb-1"><?= sanitize($member['email']) ?></p>
                        <p class="text-muted small mb-2">Batch: <?= sanitize($member['batch'] ?? 'N/A') ?></p>
                        <span class="badge <?php
                            $bc = ['general' => 'bg-secondary', 'executive' => 'bg-primary', 'lifetime' => 'bg-warning text-dark'];
                            echo $bc[$member['membership_plan']] ?? 'bg-secondary';
                        ?>">
                            Current: <?= $planLabels[$member['membership_plan']] ?? ucfirst($member['membership_plan']) ?>
                        </span>
                        <?php if ($member['plan_expires_at']): ?>
                            <br><small class="text-muted">Expires: <?= formatDate($member['plan_expires_at']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="card card-custom">
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <?= csrfField() ?>

                            <div class="mb-3">
                                <label class="form-label">Membership Plan *</label>
                                <select name="membership_plan" class="form-select" id="planSelect">
                                    <option value="general" <?= $member['membership_plan'] === 'general' ? 'selected' : '' ?>>General Member (Free)</option>
                                    <option value="executive" <?= $member['membership_plan'] === 'executive' ? 'selected' : '' ?>>Executive Member</option>
                                    <option value="lifetime" <?= $member['membership_plan'] === 'lifetime' ? 'selected' : '' ?>>Lifetime Member</option>
                                </select>
                            </div>

                            <div class="mb-3" id="expiresField" style="<?= $member['membership_plan'] === 'executive' ? '' : 'display:none;' ?>">
                                <label class="form-label">Expires On</label>
                                <input type="date" name="plan_expires_at" class="form-control" value="<?= $member['plan_expires_at'] ?? date('Y-m-d', strtotime('+12 months')) ?>">
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100" onclick="return confirm('Update membership plan for <?= sanitize($member['name']) ?>?')">
                                <i class="bi bi-check-lg me-1"></i> Update Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('planSelect').addEventListener('change', function() {
    document.getElementById('expiresField').style.display = this.value === 'executive' ? '' : 'none';
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
