<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <i class="bi bi-mortarboard-fill"></i>
        <h5><?= SITE_NAME ?></h5>
        <small>Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="<?= SITE_URL ?>/admin/index.php" class="<?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-label">Content</div>
        <a href="<?= SITE_URL ?>/admin/events/index.php" class="<?= ($adminPage ?? '') === 'events' ? 'active' : '' ?>">
            <i class="bi bi-calendar-event"></i> Events
        </a>
        <a href="<?= SITE_URL ?>/admin/news/index.php" class="<?= ($adminPage ?? '') === 'news' ? 'active' : '' ?>">
            <i class="bi bi-person-vcard"></i> Alumni Biography
        </a>

        <div class="nav-label">People</div>
        <a href="<?= SITE_URL ?>/admin/committee/index.php" class="<?= ($adminPage ?? '') === 'committee' ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i> Committee
        </a>
        <a href="<?= SITE_URL ?>/admin/members/index.php" class="<?= ($adminPage ?? '') === 'members' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="<?= SITE_URL ?>/admin/members/create_admin.php" class="<?= ($adminPage ?? '') === 'create_admin' ? 'active' : '' ?>">
            <i class="bi bi-shield-plus"></i> Create Admin
        </a>
        <a href="<?= SITE_URL ?>/admin/membership/index.php" class="<?= ($adminPage ?? '') === 'membership' ? 'active' : '' ?>">
            <i class="bi bi-card-checklist"></i> Membership
            <?php
                $pendingPayments = $pdo->query("SELECT COUNT(*) FROM membership_payments WHERE status = 'pending'")->fetchColumn();
                if ($pendingPayments > 0):
            ?>
                <span class="badge bg-danger"><?= $pendingPayments ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-label">Communication</div>
        <a href="<?= SITE_URL ?>/admin/messages/index.php" class="<?= ($adminPage ?? '') === 'messages' ? 'active' : '' ?>">
            <i class="bi bi-envelope"></i> Messages
            <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-label">Navigation</div>
        <a href="<?= SITE_URL ?>/index.php">
            <i class="bi bi-arrow-left"></i> Back to Site
        </a>
        <a href="<?= SITE_URL ?>/auth/logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>
