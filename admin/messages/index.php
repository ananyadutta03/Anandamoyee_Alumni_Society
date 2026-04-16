<?php
$pageTitle = 'Messages - Admin';
$adminPage = 'messages';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Contact Messages</h4>
        <div class="user-info">
            <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?></span>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-table">
            <div class="table-header">
                <h5><i class="bi bi-envelope me-2"></i>Messages (<?= count($messages) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $i => $msg): ?>
                        <tr class="<?= !$msg['is_read'] ? 'table-warning' : '' ?>">
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= sanitize($msg['name']) ?></strong></td>
                            <td><?= sanitize($msg['email']) ?></td>
                            <td><?= sanitize(truncateText($msg['subject'], 40)) ?></td>
                            <td><small><?= timeAgo($msg['created_at']) ?></small></td>
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span class="badge badge-approved">Read</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">Unread</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/messages/view.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form method="POST" action="<?= SITE_URL ?>/admin/messages/delete.php" class="d-inline delete-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No messages found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
