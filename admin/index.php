<?php


$pageTitle = 'Dashboard - Admin';
$adminPage = 'dashboard';



include __DIR__ . '/includes/admin_header.php';
include __DIR__ . '/includes/admin_sidebar.php';

$currentAdmin = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");

$currentAdmin->execute([
    $_SESSION['user_id']
]);

$currentAdmin = $currentAdmin->fetch();
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
            <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name'] ?? '') ?></span>
        </div>
    </div>

    <div class="admin-main">


    <div class="card shadow-sm border-0 mb-4">

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="d-flex align-items-center">

                    <img
    src="<?= !empty($currentAdmin['profile_image'])
        ? UPLOAD_URL . 'members/' . sanitize($currentAdmin['profile_image'])
        : SITE_URL . '/assets/images/default-avatar.png'; ?>"
    class="rounded-circle me-3"
    width="80"
    
    style="width:120px;height:120px;border-radius:50%;object-fit:cover"
>

                    <div>

                        <h4 class="mb-1">

                            <?= sanitize($currentAdmin['name']) ?>

                        </h4>

                        <span class="badge bg-danger">

                            Administrator

                        </span>

                        <div class="text-muted mt-2">

                            <i class="bi bi-envelope"></i>

                            <?= sanitize($currentAdmin['email']) ?>

                        </div>

                        <div class="text-muted">

                            <i class="bi bi-telephone"></i>

                            <?= sanitize($currentAdmin['phone'] ?? '-') ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-4 mt-4 mt-md-0">
    <div class="dashboard-actions">

        <a href="<?= SITE_URL ?>/admin/profile/edit.php"
           class="btn action-btn btn-edit">
            <i class="bi bi-pencil-square"></i>
            <span>Edit Profile</span>
        </a>

        <a href="<?= SITE_URL ?>/admin/profile/change_password.php"
           class="btn action-btn btn-password">
            <i class="bi bi-shield-lock"></i>
            <span>Change Password</span>
        </a>

        <a href="<?= SITE_URL ?>/admin/profile/add-notice.php"
           class="btn action-btn btn-notice">
            <i class="bi bi-megaphone"></i>
            <span>Publish Notice</span>
        </a>

    </div>
</div>

        </div>

    </div>

</div>

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

<div> 

<div class="footer-bottom">
    <div class="container">
        <p>
            &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
            <br>

            Developed By -

            <a
                href="https://ananyadutta03.github.io/my_portfolio"
                target="_blank"
                
            >
                <strong> Ananya Dutta <strong>
            </a>

            &amp;

            <a
                href="https://swarupkst.com"
                target="_blank"
                
            >
                <strong> Swarup Biswas <strong>
            </a>
        </p>
    </div>
</div>

<style>
    /* =========================================================
   Footer
   ========================================================= */

.site-footer {
    background: #ffffff;
    color: #495057;
    border-top: 1px solid #e9ecef;
    padding-top: 55px;
    margin-top: 50px;
}

/* Footer headings */
.site-footer h5 {
    color: #212529;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 18px;
    position: relative;
}

.site-footer h5::after {
    content: "";
    display: block;
    width: 35px;
    height: 3px;
    background: #198754;
    border-radius: 5px;
    margin-top: 8px;
}

.site-footer p {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.7;
}

/* Footer logo */
.footer-logo img {
    max-width: 75px;
    height: auto;
    margin-bottom: 12px;
}

/* =========================================================
   Social Links
   ========================================================= */

.footer-social {
    display: flex;
    gap: 10px;
}

.footer-social a {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: #f8f9fa;
    color: #495057;

    border: 1px solid #e9ecef;
    border-radius: 50%;

    text-decoration: none;

    transition: all 0.25s ease;
}

.footer-social a:hover {
    background: #198754;
    color: #ffffff;
    border-color: #198754;
    transform: translateY(-3px);
}

/* =========================================================
   Quick Links
   ========================================================= */

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    color: #6c757d;
    font-size: 20px;

    text-decoration: none;

    transition: all 0.2s ease;
}

.footer-links a i {
    font-size: 20px;
    color: #198754;
}

.footer-links a:hover {
    color: #198754;
    padding-left: 4px;
}

/* =========================================================
   Contact
   ========================================================= */

.footer-contact {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 10px;
}

.contact-item > i {
    color: #198754;
    font-size: 16px;
    margin-top: 3px;
}

.contact-item p {
    margin: 0;
}

.footer-email {
    color: #6c757d;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
}

.footer-email:hover {
    color: #198754;
}

/* =========================================================
   Footer Bottom
   ========================================================= */

.footer-bottom {
    margin-top: 25px;
    padding: 20px 0;

    background: #f8f9fa;
    border-top: 1px solid #e9ecef;

    text-align: center;
}

.footer-bottom p {
    margin: 0;
    color: #2a2a2b;
    font-size: 13px;
    line-height: 1.8;
}

/* Developer links */
.footer-bottom a {
    color: #222222 !important;
    font-size: 13px !important;
    text-decoration: none !important;

    transition: all 0.25s ease;
}

.footer-bottom a:hover {
    color: #198754 !important;
    text-decoration: underline !important;
}

/* =========================================================
   Mobile
   ========================================================= */

@media (max-width: 991px) {
    .site-footer {
        padding-top: 40px;
    }

    .site-footer h5::after {
        margin-left: auto;
        margin-right: auto;
    }

    .footer-social {
        justify-content: center;
    }
}

@media (max-width: 767px) {
    .site-footer {
        padding-top: 35px;
    }

    .footer-bottom {
        padding: 18px 10px;
    }

    .footer-bottom p {
        font-size: 12px;
    }
}
</style>
