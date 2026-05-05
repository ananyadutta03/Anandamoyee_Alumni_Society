<?php
$pageTitle = 'Dashboard - Admin';
$adminPage = 'dashboard';

include __DIR__ . '/includes/admin_header.php';
include __DIR__ . '/includes/admin_sidebar.php';

// Dashboard stats
$memberCount  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
$eventCount   = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$newsCount    = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$msgCount     = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

// Recent members
$recentMembers = $pdo->query("SELECT name, email, status, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent events
$recentEvents = $pdo->query("SELECT title, event_date, status FROM events ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Admin Content -->
<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Dashboard</h4>
        <div class="user-info">
            <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?></span>
        </div>
    </div>

    <div class="admin-main">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-info">
                        <h3><?= $memberCount ?></h3>
                        <p>Total Members</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left-color: #28a745;">
                    <div class="stat-icon bg-success"><i class="bi bi-calendar-event-fill"></i></div>
                    <div class="stat-info">
                        <h3><?= $eventCount ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left-color: #17a2b8;">
                    <div class="stat-icon bg-info"><i class="bi bi-person-vcard"></i></div>
                    <div class="stat-info">
                        <h3><?= $newsCount ?></h3>
                        <p>Alumni Biographies</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left-color: #dc3545;">
                    <div class="stat-icon bg-danger"><i class="bi bi-envelope-fill"></i></div>
                    <div class="stat-info">
                        <h3><?= $msgCount ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($pendingCount > 0): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong><?= $pendingCount ?></strong> member(s) pending approval.
                <a href="<?= SITE_URL ?>/admin/members/index.php?filter=pending" class="alert-link ms-2">Review now</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Recent Members -->
            <div class="col-lg-6">
                <div class="admin-table">
                    <div class="table-header">
                        <h5><i class="bi bi-people me-2"></i>Recent Members</h5>
                        <a href="<?= SITE_URL ?>/admin/members/index.php" class="btn btn-sm btn-outline-primary-custom">View All</a>
                    </div>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMembers as $member): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($member['name']) ?></strong><br>
                                    <small class="text-muted"><?= sanitize($member['email']) ?></small>
                                </td>
                                <td><span class="badge badge-<?= $member['status'] ?>"><?= ucfirst($member['status']) ?></span></td>
                                <td><small><?= timeAgo($member['created_at']) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentMembers)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No members yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="col-lg-6">
                <div class="admin-table">
                    <div class="table-header">
                        <h5><i class="bi bi-calendar-event me-2"></i>Recent Events</h5>
                        <a href="<?= SITE_URL ?>/admin/events/index.php" class="btn btn-sm btn-outline-primary-custom">View All</a>
                    </div>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvents as $event): ?>
                            <tr>
                                <td><?= sanitize($event['title']) ?></td>
                                <td><small><?= formatDate($event['event_date']) ?></small></td>
                                <td><span class="badge badge-<?= $event['status'] ?>"><?= ucfirst($event['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentEvents)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No events yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
