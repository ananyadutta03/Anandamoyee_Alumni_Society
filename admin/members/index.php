<?php
$pageTitle = 'Manage Members - Admin';
$adminPage = 'members';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM users WHERE role = 'user'";
if ($filter === 'pending')  $sql .= " AND status = 'pending'";
if ($filter === 'approved') $sql .= " AND status = 'approved'";
if ($filter === 'rejected') $sql .= " AND status = 'rejected'";
$sql .= " ORDER BY created_at DESC";

$members = $pdo->query($sql)->fetchAll();

$counts = [
    'all'      => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'pending'  => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'approved'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'rejected'")->fetchColumn(),
];
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Manage Members</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/members/create_admin.php" class="btn btn-sm btn-primary-custom">
                <i class="bi bi-person-plus me-1"></i> Create Admin
            </a>
        </div>
    </div>

    <div class="admin-main">
        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all" style="<?= $filter === 'all' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    All <span class="badge bg-secondary ms-1"><?= $counts['all'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending" style="<?= $filter === 'pending' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    Pending <span class="badge bg-warning text-dark ms-1"><?= $counts['pending'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="?filter=approved" style="<?= $filter === 'approved' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    Approved <span class="badge bg-success ms-1"><?= $counts['approved'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'rejected' ? 'active' : '' ?>" href="?filter=rejected" style="<?= $filter === 'rejected' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    Rejected <span class="badge bg-danger ms-1"><?= $counts['rejected'] ?></span>
                </a>
            </li>
        </ul>

        <div class="admin-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $i => $member): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= sanitize($member['name']) ?></strong></td>
                            <td><?= sanitize($member['email']) ?></td>
                            <td><?= sanitize($member['student_id'] ?? '-') ?></td>
                            <td><?= sanitize($member['department'] ?? '-') ?></td>
                            <td><?= sanitize($member['batch'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $member['status'] ?>"><?= ucfirst($member['status']) ?></span></td>
                            <td><small><?= formatDate($member['created_at']) ?></small></td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/members/view.php?id=<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary-custom" title="View Details"><i class="bi bi-eye"></i></a>
                                <?php if ($member['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                <?php elseif ($member['status'] === 'approved'): ?>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Suspend"><i class="bi bi-pause-fill"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/members/approve.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Re-approve"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="<?= SITE_URL ?>/admin/members/delete.php" class="d-inline delete-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No members found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
