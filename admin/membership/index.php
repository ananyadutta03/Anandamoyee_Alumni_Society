<?php
$pageTitle = 'Membership Management - Admin';
$adminPage = 'membership';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

// Auto-downgrade expired executive members
$pdo->exec("UPDATE users SET membership_plan = 'general', plan_expires_at = NULL WHERE membership_plan = 'executive' AND plan_expires_at IS NOT NULL AND plan_expires_at < CURDATE()");

// Get filter
$filter = $_GET['filter'] ?? 'all';
$planLabels = ['general' => 'General Member', 'executive' => 'Executive Member', 'lifetime' => 'Lifetime Member'];

// Count per plan
$counts = [];
$counts['all'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'approved'")->fetchColumn();
foreach ($planLabels as $slug => $label) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'approved' AND membership_plan = ?");
    $s->execute([$slug]);
    $counts[$slug] = $s->fetchColumn();
}

// Pending payments count
$pendingCount = $pdo->query("SELECT COUNT(*) FROM membership_payments WHERE status = 'pending'")->fetchColumn();

// Get members
if ($filter !== 'all' && array_key_exists($filter, $planLabels)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'user' AND status = 'approved' AND membership_plan = ? ORDER BY name ASC");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' AND status = 'approved' ORDER BY membership_plan DESC, name ASC");
    $filter = 'all';
}
$members = $stmt->fetchAll();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Membership Management</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/membership/payments.php" class="btn btn-sm btn-primary-custom">
                <i class="bi bi-credit-card me-1"></i> Payments
                <?php if ($pendingCount > 0): ?>
                    <span class="badge bg-danger"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/membership/plans.php" class="btn btn-sm btn-outline-secondary ms-2">
                <i class="bi bi-gear me-1"></i> Plan Settings
            </a>
        </div>
    </div>

    <div class="admin-main">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 text-center">
                    <h3 class="mb-0"><?= $counts['all'] ?></h3>
                    <small class="text-muted">Total Members</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 text-center">
                    <h3 class="mb-0" style="color:var(--color-text-muted);"><?= $counts['general'] ?></h3>
                    <small class="text-muted">General</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 text-center">
                    <h3 class="mb-0" style="color:var(--color-primary);"><?= $counts['executive'] ?></h3>
                    <small class="text-muted">Executive</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 text-center">
                    <h3 class="mb-0" style="color:#ffc107;"><?= $counts['lifetime'] ?></h3>
                    <small class="text-muted">Lifetime</small>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?>">All (<?= $counts['all'] ?>)</a>
            <a href="?filter=general" class="btn btn-sm <?= $filter === 'general' ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?>">General (<?= $counts['general'] ?>)</a>
            <a href="?filter=executive" class="btn btn-sm <?= $filter === 'executive' ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?>">Executive (<?= $counts['executive'] ?>)</a>
            <a href="?filter=lifetime" class="btn btn-sm <?= $filter === 'lifetime' ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?>">Lifetime (<?= $counts['lifetime'] ?>)</a>
        </div>

        <!-- Members Table -->
        <div class="admin-table">
            <div class="table-header">
                <h5><i class="bi bi-people me-2"></i>Members — <?= $filter === 'all' ? 'All Plans' : ($planLabels[$filter] ?? 'All') ?></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Batch</th>
                            <th>Plan</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td><strong><?= sanitize($m['name']) ?></strong></td>
                            <td><?= sanitize($m['email']) ?></td>
                            <td><?= sanitize($m['batch'] ?? '-') ?></td>
                            <td>
                                <?php
                                    $badgeClass = ['general' => 'bg-secondary', 'executive' => 'bg-primary', 'lifetime' => 'bg-warning text-dark'];
                                ?>
                                <span class="badge <?= $badgeClass[$m['membership_plan']] ?? 'bg-secondary' ?>">
                                    <?= $planLabels[$m['membership_plan']] ?? ucfirst($m['membership_plan']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($m['membership_plan'] === 'executive' && $m['plan_expires_at']): ?>
                                    <?= formatDate($m['plan_expires_at']) ?>
                                    <?php if (strtotime($m['plan_expires_at']) < strtotime('+30 days')): ?>
                                        <br><small class="text-danger">Expiring soon</small>
                                    <?php endif; ?>
                                <?php elseif ($m['membership_plan'] === 'lifetime'): ?>
                                    <span class="text-muted">Never</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/membership/edit.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" title="Change Plan">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/admin/members/view.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary" title="View Profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($members)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No members found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
