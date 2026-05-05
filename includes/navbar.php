<!-- Top Utility Bar -->
<div class="top-bar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Social Icons -->
            <div class="social-icons d-none d-md-block">
                <a href="https://www.facebook.com/kheora.alumni.association" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://youtu.be/sia8WlMQyY4?si=Ui5dtidK4-7ToJdU" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>

            <!-- Auth Links -->
            <div class="auth-links ms-auto">
                <?php if (isLoggedIn()): ?>
                    <span class="text-dark me-2">
                        <i class="bi bi-person-circle"></i> <?= sanitize($_SESSION['user_name']) ?>
                    </span>
                    <?php if (isAdmin()): ?>
                        <a href="<?= SITE_URL ?>/admin/index.php" class="me-2">
                            <i class="bi bi-speedometer2"></i> Admin
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/user/dashboard.php" class="me-2">
                            <i class="bi bi-speedometer2"></i> My Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/auth/logout.php" class="btn-register">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="me-2">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    <a href="<?= SITE_URL ?>/auth/register.php" class="btn-register">
                        <i class="bi bi-person-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= SITE_URL ?>/index.php">
            <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.outerHTML='<span class=\'fw-bold fs-4\' style=\'color: var(--color-primary-dark)\'><i class=\'bi bi-mortarboard-fill me-2\'></i><?= SITE_NAME ?></span>'">
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'about' ? 'active' : '' ?>" href="<?= SITE_URL ?>/pages/about.php">About Us</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= ($currentPage ?? '') === 'committee' ? 'active' : '' ?>" href="<?= SITE_URL ?>/pages/committee.php" id="committeeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Executive Committee</a>
                    <ul class="dropdown-menu" aria-labelledby="committeeDropdown">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/committee.php?type=advisor">Advisor</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/committee.php?type=executive_member">Executive Member</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/committee.php?type=general_member">General Member</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/committee.php?type=life_member">Life Member</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/committee.php">View All</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'members' ? 'active' : '' ?>" href="<?= SITE_URL ?>/pages/members.php">Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'events' ? 'active' : '' ?>" href="<?= SITE_URL ?>/pages/events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'news' ? 'active' : '' ?>" href="<?= SITE_URL ?>/pages/news.php">Alumni Biography</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'contact' ? 'active' : '' ?>" href="<?= SITE_URL ?>/pages/contact.php">Contact Us</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
