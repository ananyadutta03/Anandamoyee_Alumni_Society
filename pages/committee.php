<?php
require_once __DIR__ . '/../config/init.php';

$pageTitle = 'Executive Committee - ' . SITE_NAME;
$currentPage = 'committee';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM executive_committee WHERE is_active = 1 ORDER BY sort_order ASC");
$members = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Executive Committee</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">Executive Committee</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Committee Grid -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Our Leadership Team</h2>
            <p>Meet the dedicated members leading AIUB Society</p>
        </div>

        <?php if (empty($members)): ?>
            <div class="no-results">
                <i class="bi bi-people"></i>
                <h4>Committee information coming soon</h4>
            </div>
        <?php else: ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($members as $member): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="committee-card">
                        <?php if ($member['image']): ?>
                            <img src="<?= UPLOAD_URL ?>committee/<?= sanitize($member['image']) ?>" alt="<?= sanitize($member['name']) ?>" class="member-img">
                        <?php else: ?>
                            <div class="member-img d-flex align-items-center justify-content-center bg-light" style="margin: 0 auto 20px;">
                                <i class="bi bi-person-fill" style="font-size: 3rem; color: var(--color-primary);"></i>
                            </div>
                        <?php endif; ?>
                        <h5><?= sanitize($member['name']) ?></h5>
                        <p class="designation"><?= sanitize($member['designation']) ?></p>
                        <?php if ($member['bio']): ?>
                            <p class="text-muted small"><?= sanitize(truncateText($member['bio'], 100)) ?></p>
                        <?php endif; ?>
                        <div class="social-links mt-2">
                            <?php if ($member['email']): ?>
                                <a href="mailto:<?= sanitize($member['email']) ?>" title="Email"><i class="bi bi-envelope-fill"></i></a>
                            <?php endif; ?>
                            <?php if ($member['linkedin']): ?>
                                <a href="<?= sanitize($member['linkedin']) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                            <?php if ($member['phone']): ?>
                                <a href="tel:<?= sanitize($member['phone']) ?>" title="Phone"><i class="bi bi-telephone-fill"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
