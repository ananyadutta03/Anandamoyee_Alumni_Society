<?php
$pageTitle = 'Manage Committee - Admin';
$adminPage = 'committee';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$members = $pdo->query("SELECT * FROM executive_committee ORDER BY sort_order ASC")->fetchAll();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Executive Committee</h4>
        <div class="user-info">
            <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?></span>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-table">
            <div class="table-header">
                <h5><i class="bi bi-person-badge me-2"></i>Committee Members (<?= count($members) ?>)</h5>
                <a href="<?= SITE_URL ?>/admin/committee/create.php" class="btn btn-sm btn-primary-custom">
                    <i class="bi bi-plus-lg me-1"></i> Add Member
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= $member['sort_order'] ?></td>
                            <td>
                                <?php if ($member['image']): ?>
                                    <img src="<?= UPLOAD_URL ?>committee/<?= sanitize($member['image']) ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><i class="bi bi-person"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($member['name']) ?></strong></td>
                            <td><?= sanitize($member['designation']) ?></td>
                            <td>
                                <?php
                                    $typeLabels = ['advisor' => 'Advisor', 'executive_member' => 'Executive Member', 'general_member' => 'General Member', 'life_member' => 'Life Member'];
                                    $typeColors = ['advisor' => 'bg-info', 'executive_member' => 'bg-primary', 'general_member' => 'bg-success', 'life_member' => 'bg-warning text-dark'];
                                ?>
                                <span class="badge <?= $typeColors[$member['committee_type']] ?? 'bg-secondary' ?>">
                                    <?= $typeLabels[$member['committee_type']] ?? $member['committee_type'] ?>
                                </span>
                            </td>
                            <td>
                                <?= $member['email'] ? sanitize($member['email']) : '-' ?><br>
                                <small class="text-muted"><?= $member['phone'] ? sanitize($member['phone']) : '' ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $member['is_active'] ? 'badge-approved' : 'badge-rejected' ?>">
                                    <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/committee/edit.php?id=<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="<?= SITE_URL ?>/admin/committee/delete.php" class="d-inline delete-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No committee members found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
