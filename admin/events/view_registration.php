<?php
$pageTitle = 'View Registration - Admin';
$adminPage = 'events';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT er.*, u.name, u.email, u.student_id, u.batch, u.department, u.phone, u.profile_image, u.membership_plan, e.title as event_title, e.event_date, e.location, e.registration_fee, e.id as eid
    FROM event_registrations er
    JOIN users u ON er.user_id = u.id
    JOIN events e ON er.event_id = e.id
    WHERE er.id = ?");
$stmt->execute([$id]);
$reg = $stmt->fetch();

if (!$reg) {
    setFlash('danger', 'Registration not found.');
    redirect(SITE_URL . '/admin/events/index.php');
}
?>

<style>
    .admin-main .card-custom { height: auto; }
    .admin-main .card-custom:hover { transform: none; }
</style>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Registration Details</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/events/registrations.php?id=<?= $reg['eid'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Registrations
            </a>
        </div>
    </div>

    <div class="admin-main">
        <!-- Status Banner -->
        <div class="alert <?php
            if ($reg['status'] === 'approved') echo 'alert-success';
            elseif ($reg['status'] === 'pending') echo 'alert-warning';
            else echo 'alert-danger';
        ?> d-flex align-items-center justify-content-between mb-4">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                Status: <strong><?= ucfirst($reg['status']) ?></strong>
                &bull; Registered: <strong><?= formatDate($reg['created_at'], 'M d, Y \a\t h:i A') ?></strong>
            </div>
            <div>
                <?php if ($reg['status'] === 'pending'): ?>
                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                        <input type="hidden" name="event_id" value="<?= $reg['eid'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </form>
                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                        <input type="hidden" name="event_id" value="<?= $reg['eid'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
                    </form>
                <?php elseif ($reg['status'] === 'approved'): ?>
                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                        <input type="hidden" name="event_id" value="<?= $reg['eid'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-pause-fill me-1"></i>Revoke</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                        <input type="hidden" name="event_id" value="<?= $reg['eid'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1"></i>Re-approve</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- User Profile Card -->
            <div class="col-lg-4">
                <div class="card card-custom text-center p-4">
                    <div class="mb-3">
                        <?php if ($reg['profile_image']): ?>
                            <img src="<?= UPLOAD_URL ?>members/<?= sanitize($reg['profile_image']) ?>" alt="Profile"
                                 style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--color-primary);">
                        <?php else: ?>
                            <div style="width:120px;height:120px;border-radius:50%;background:var(--color-light);display:flex;align-items:center;justify-content:center;margin:0 auto;border:4px solid var(--color-primary);">
                                <i class="bi bi-person-fill" style="font-size:3rem;color:var(--color-primary);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="mb-1"><?= sanitize($reg['name']) ?></h5>
                    <p class="text-muted small mb-1"><?= sanitize($reg['email']) ?></p>
                    <p class="text-muted small mb-2"><?= sanitize($reg['student_id'] ?? 'N/A') ?> &bull; <?= sanitize($reg['department'] ?? 'N/A') ?> &bull; Batch <?= sanitize($reg['batch'] ?? 'N/A') ?></p>
                    <?php if ($reg['phone']): ?>
                        <p class="text-muted small mb-0"><i class="bi bi-phone me-1"></i><?= sanitize($reg['phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Details -->
            <div class="col-lg-8">
                <!-- Event Info -->
                <div class="card card-custom mb-3">
                    <div class="card-body p-4">
                        <h6 class="mb-3"><i class="bi bi-calendar-event me-2" style="color:var(--color-primary);"></i>Event Details</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Event</label>
                                <strong><?= sanitize($reg['event_title']) ?></strong>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small d-block">Date</label>
                                <strong><?= formatDate($reg['event_date'], 'M d, Y') ?></strong>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small d-block">Fee</label>
                                <strong><?= $reg['registration_fee'] > 0 ? '&#2547;' . number_format($reg['registration_fee']) : 'Free' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card card-custom mb-3">
                    <div class="card-body p-4">
                        <h6 class="mb-3"><i class="bi bi-credit-card me-2" style="color:var(--color-primary);"></i>Payment Information</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Transaction ID</label>
                                <strong><?= sanitize($reg['transaction_id'] ?? 'Not provided') ?></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Submitted On</label>
                                <strong><?= formatDate($reg['created_at'], 'M d, Y \a\t h:i A') ?></strong>
                            </div>
                        </div>

                        <?php if ($reg['payment_proof']): ?>
                            <label class="text-muted small d-block mb-2">Payment Proof</label>
                            <a href="<?= UPLOAD_URL ?>payments/<?= sanitize($reg['payment_proof']) ?>" target="_blank">
                                <img src="<?= UPLOAD_URL ?>payments/<?= sanitize($reg['payment_proof']) ?>" alt="Payment Proof"
                                     style="max-width:100%;max-height:400px;border-radius:8px;border:1px solid var(--color-border);">
                            </a>
                            <small class="d-block text-muted mt-1">Click image to view full size</small>
                        <?php else: ?>
                            <p class="text-muted mb-0">No payment proof uploaded.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Admin Remarks -->
                <?php if ($reg['admin_remarks']): ?>
                <div class="card card-custom">
                    <div class="card-body p-4">
                        <h6 class="mb-2"><i class="bi bi-chat-text me-2" style="color:var(--color-primary);"></i>Admin Remarks</h6>
                        <p class="text-muted mb-0"><?= nl2br(sanitize($reg['admin_remarks'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
