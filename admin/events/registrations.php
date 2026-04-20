<?php
$pageTitle = 'Event Registrations - Admin';
$adminPage = 'events';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$eventId = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    setFlash('danger', 'Event not found.');
    redirect(SITE_URL . '/admin/events/index.php');
}

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT er.*, u.name, u.email, u.batch, u.profile_image
        FROM event_registrations er
        JOIN users u ON er.user_id = u.id
        WHERE er.event_id = ?";
$params = [$eventId];

if ($filter === 'pending')  { $sql .= " AND er.status = 'pending'"; }
if ($filter === 'approved') { $sql .= " AND er.status = 'approved'"; }
if ($filter === 'rejected') { $sql .= " AND er.status = 'rejected'"; }
$sql .= " ORDER BY er.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

$countStmt = $pdo->prepare("SELECT
    COUNT(*) as total,
    SUM(status = 'pending') as pending_count,
    SUM(status = 'approved') as approved_count,
    SUM(status = 'rejected') as rejected_count
    FROM event_registrations WHERE event_id = ?");
$countStmt->execute([$eventId]);
$counts = $countStmt->fetch();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Registrations: <?= sanitize($event['title']) ?></h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/events/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Events
            </a>
        </div>
    </div>

    <div class="admin-main">
        <!-- Event Info -->
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
            <div>
                <i class="bi bi-calendar-event me-2"></i>
                <strong><?= formatDate($event['event_date'], 'M d, Y') ?></strong>
                <?php if ($event['location']): ?>
                    &bull; <i class="bi bi-geo-alt me-1"></i><?= sanitize($event['location']) ?>
                <?php endif; ?>
                &bull; Fee: <strong><?= $event['registration_fee'] > 0 ? '&#2547;' . number_format($event['registration_fee']) : 'Free' ?></strong>
            </div>
            <span class="badge bg-primary"><?= $counts['total'] ?> total registrations</span>
        </div>

        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?id=<?= $eventId ?>&filter=all" style="<?= $filter === 'all' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    All <span class="badge bg-secondary ms-1"><?= $counts['total'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?id=<?= $eventId ?>&filter=pending" style="<?= $filter === 'pending' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    Pending <span class="badge bg-warning text-dark ms-1"><?= $counts['pending_count'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="?id=<?= $eventId ?>&filter=approved" style="<?= $filter === 'approved' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    Approved <span class="badge bg-success ms-1"><?= $counts['approved_count'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'rejected' ? 'active' : '' ?>" href="?id=<?= $eventId ?>&filter=rejected" style="<?= $filter === 'rejected' ? 'background:var(--color-primary);color:#fff' : '' ?>">
                    Rejected <span class="badge bg-danger ms-1"><?= $counts['rejected_count'] ?></span>
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
                            <th>Transaction ID</th>
                            <th>Payment Proof</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $i => $reg): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= sanitize($reg['name']) ?></strong></td>
                            <td><?= sanitize($reg['email']) ?></td>
                            <td><?= sanitize($reg['transaction_id'] ?? '-') ?></td>
                            <td>
                                <?php if ($reg['payment_proof']): ?>
                                    <a href="<?= UPLOAD_URL ?>payments/<?= sanitize($reg['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-image me-1"></i>View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= $reg['status'] ?>"><?= ucfirst($reg['status']) ?></span></td>
                            <td><small><?= formatDate($reg['created_at']) ?></small></td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/events/view_registration.php?id=<?= $reg['id'] ?>" class="btn btn-sm btn-outline-primary-custom" title="View Details"><i class="bi bi-eye"></i></a>
                                <?php if ($reg['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                <?php elseif ($reg['status'] === 'approved'): ?>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Revoke"><i class="bi bi-pause-fill"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= SITE_URL ?>/admin/events/approve_registration.php" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Re-approve"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($registrations)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No registrations found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
