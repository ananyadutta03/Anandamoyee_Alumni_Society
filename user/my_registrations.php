<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'My Registrations - ' . SITE_NAME;
$userPage = 'registrations';

$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT er.*, e.title as event_title, e.slug as event_slug, e.event_date, e.location, e.registration_fee
    FROM event_registrations er
    JOIN events e ON er.event_id = e.id
    WHERE er.user_id = ?
    ORDER BY er.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$registrations = $stmt->fetchAll();
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
            <h4 class="page-title">My Registrations</h4>
            <div class="user-info">
                <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?></span>
            </div>
        </div>

        <div class="admin-main">
            <?php if (empty($registrations)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x" style="font-size:4rem;color:var(--color-border);"></i>
                    <h5 class="text-muted mt-3">No registrations yet</h5>
                    <p class="text-muted">Browse events and register for upcoming ones.</p>
                    <a href="<?= SITE_URL ?>/pages/events.php" class="btn btn-primary-custom">
                        <i class="bi bi-calendar-event me-1"></i> Browse Events
                    </a>
                </div>
            <?php else: ?>
                <div class="admin-table">
                    <div class="table-header">
                        <h5><i class="bi bi-calendar-check me-2"></i>My Event Registrations (<?= count($registrations) ?>)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Fee</th>
                                    <th>Status</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $i => $reg): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <a href="<?= SITE_URL ?>/pages/event_detail.php?slug=<?= sanitize($reg['event_slug']) ?>" class="fw-bold" style="color:var(--color-primary);">
                                            <?= sanitize($reg['event_title']) ?>
                                        </a>
                                    </td>
                                    <td><?= formatDate($reg['event_date']) ?></td>
                                    <td><?= $reg['registration_fee'] > 0 ? '&#2547;' . number_format($reg['registration_fee']) : 'Free' ?></td>
                                    <td><span class="badge badge-<?= $reg['status'] ?>"><?= ucfirst($reg['status']) ?></span></td>
                                    <td><small><?= formatDate($reg['created_at']) ?></small></td>
                                    <td>
                                        <?php if ($reg['status'] === 'pending'): ?>
                                            <form method="POST" action="<?= SITE_URL ?>/user/cancel_registration.php" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Registration">
                                                    <i class="bi bi-x-lg me-1"></i>Cancel
                                                </button>
                                            </form>
                                        <?php elseif ($reg['status'] === 'approved'): ?>
                                            <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
                                        <?php else: ?>
                                            <span class="text-danger small"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div> <!-- /.admin-main -->
    </div> <!-- /.admin-content -->
</div> <!-- /.admin-wrapper -->

<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
</body>
</html>
