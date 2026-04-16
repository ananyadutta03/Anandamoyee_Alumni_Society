<?php
$pageTitle = 'Manage Events - Admin';
$adminPage = 'events';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$events = $pdo->query("SELECT * FROM events ORDER BY created_at DESC")->fetchAll();

// Get registration counts per event
$regCounts = [];
$regData = $pdo->query("SELECT event_id, COUNT(*) as total, SUM(status = 'pending') as pending_count FROM event_registrations GROUP BY event_id")->fetchAll();
foreach ($regData as $r) {
    $regCounts[$r['event_id']] = $r;
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Manage Events</h4>
        <div class="user-info">
            <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?></span>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-table">
            <div class="table-header">
                <h5><i class="bi bi-calendar-event me-2"></i>All Events (<?= count($events) ?>)</h5>
                <a href="<?= SITE_URL ?>/admin/events/create.php" class="btn btn-sm btn-primary-custom">
                    <i class="bi bi-plus-lg me-1"></i> Add Event
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Registrations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $i => $event): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if ($event['image']): ?>
                                    <img src="<?= UPLOAD_URL ?>events/<?= sanitize($event['image']) ?>" alt="" class="img-preview" style="max-width:60px;max-height:40px;">
                                <?php else: ?>
                                    <span class="text-muted small">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($event['title']) ?></strong></td>
                            <td><?= formatDate($event['event_date']) ?></td>
                            <td><?= sanitize($event['location'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $event['status'] ?>"><?= ucfirst($event['status']) ?></span></td>
                            <td>
                                <?php $rc = $regCounts[$event['id']] ?? ['total' => 0, 'pending_count' => 0]; ?>
                                <a href="<?= SITE_URL ?>/admin/events/registrations.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-info" title="View Registrations">
                                    <i class="bi bi-people me-1"></i><?= $rc['total'] ?>
                                    <?php if ($rc['pending_count'] > 0): ?>
                                        <span class="badge bg-warning text-dark"><?= $rc['pending_count'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/events/edit.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="<?= SITE_URL ?>/admin/events/delete.php" class="d-inline delete-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($events)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No events found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
