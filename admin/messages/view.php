<?php
$pageTitle = 'View Message - Admin';
$adminPage = 'messages';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$msg = $stmt->fetch();

if (!$msg) {
    setFlash('danger', 'Message not found.');
    redirect(SITE_URL . '/admin/messages/index.php');
}

// Mark as read
if (!$msg['is_read']) {
    $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">View Message</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/messages/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-form">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">From</label>
                    <p><?= sanitize($msg['name']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <p><a href="mailto:<?= sanitize($msg['email']) ?>"><?= sanitize($msg['email']) ?></a></p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Subject</label>
                    <p><?= sanitize($msg['subject']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Received</label>
                    <p><?= formatDate($msg['created_at'], 'F j, Y g:i A') ?> (<?= timeAgo($msg['created_at']) ?>)</p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Message</label>
                <div class="bg-light p-4 rounded" style="line-height: 1.8;">
                    <?= nl2br(sanitize($msg['message'])) ?>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="mailto:<?= sanitize($msg['email']) ?>?subject=Re: <?= sanitize($msg['subject']) ?>" class="btn btn-primary-custom">
                    <i class="bi bi-reply me-1"></i> Reply via Email
                </a>
                <form method="POST" action="<?= SITE_URL ?>/admin/messages/delete.php" class="delete-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
