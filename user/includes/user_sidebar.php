<!-- User Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <?php
        $pdo = getDBConnection();
        $userStmt = $pdo->prepare("SELECT profile_image, membership_plan FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $sidebarUser = $userStmt->fetch();
        ?>
        <?php if ($sidebarUser && $sidebarUser['profile_image']): ?>
            <img src="<?= UPLOAD_URL ?>members/<?= sanitize($sidebarUser['profile_image']) ?>" alt="Profile"
                 style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--color-accent);">
        <?php else: ?>
            <i class="bi bi-person-circle"></i>
        <?php endif; ?>
        <h5><?= sanitize($_SESSION['user_name']) ?></h5>
        <small>
            <span class="badge <?= ($sidebarUser['membership_plan'] ?? 'free') === 'premium' ? 'bg-warning text-dark' : 'bg-secondary' ?>" style="font-size:0.65rem;">
                <?= ucfirst($sidebarUser['membership_plan'] ?? 'free') ?> Member
            </span>
        </small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">My Account</div>
        <a href="<?= SITE_URL ?>/user/dashboard.php" class="<?= ($userPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/user/profile.php" class="<?= ($userPage ?? '') === 'profile' ? 'active' : '' ?>">
            <i class="bi bi-person"></i> Edit Profile
        </a>
        <a href="<?= SITE_URL ?>/user/change_password.php" class="<?= ($userPage ?? '') === 'password' ? 'active' : '' ?>">
            <i class="bi bi-shield-lock"></i> Change Password
        </a>

        <div class="nav-label">Events</div>
        <a href="<?= SITE_URL ?>/user/my_registrations.php" class="<?= ($userPage ?? '') === 'registrations' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> My Registrations
        </a>

        <div class="nav-label">Membership</div>
        <a href="<?= SITE_URL ?>/user/membership.php" class="<?= ($userPage ?? '') === 'membership' ? 'active' : '' ?>">
            <i class="bi bi-star"></i> Membership Plans
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
