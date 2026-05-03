<?php
require_once __DIR__ . '/../config/init.php';

$currentPage = 'committee';

$committeeTypes = [
    'advisor'          => 'Advisor',
    'executive_member' => 'Executive Member',
    'general_member'   => 'General Member',
    'life_member'      => 'Life Member',
];

$selectedType = $_GET['type'] ?? '';
$pdo = getDBConnection();

if ($selectedType && array_key_exists($selectedType, $committeeTypes)) {
    $pageTitle = $committeeTypes[$selectedType] . ' - Executive Committee - ' . SITE_NAME;
    $stmt = $pdo->prepare("SELECT * FROM executive_committee WHERE is_active = 1 AND committee_type = ? ORDER BY sort_order ASC");
    $stmt->execute([$selectedType]);
    $members = $stmt->fetchAll();
    $heading = $committeeTypes[$selectedType];
    $subtitle = 'Members serving as ' . $committeeTypes[$selectedType];
} else {
    $pageTitle = 'Executive Committee - ' . SITE_NAME;
    $members = $pdo->query("SELECT * FROM executive_committee WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
    $heading = 'Our Leadership Team';
    $subtitle = 'Meet the dedicated members leading Anandamoyee Alumni Association';
    $selectedType = '';
}

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
                <?php if ($selectedType): ?>
                    <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/committee.php">Executive Committee</a></li>
                    <li class="breadcrumb-item active"><?= $committeeTypes[$selectedType] ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item active">Executive Committee</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</section>

<!-- Category Tabs -->
<section class="pt-4 pb-0">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= SITE_URL ?>/pages/committee.php" class="btn <?= $selectedType === '' ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?> btn-sm px-3">
                All
            </a>
            <?php foreach ($committeeTypes as $key => $label): ?>
                <a href="<?= SITE_URL ?>/pages/committee.php?type=<?= $key ?>" class="btn <?= $selectedType === $key ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?> btn-sm px-3">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Committee Grid -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2><?= sanitize($heading) ?></h2>
            <p><?= sanitize($subtitle) ?></p>
        </div>

        <?php if (empty($members)): ?>
            <div class="no-results">
                <i class="bi bi-people"></i>
                <h4>No members found in this category</h4>
                <p><a href="<?= SITE_URL ?>/pages/committee.php">View all committee members</a></p>
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
                        <?php if (!$selectedType): ?>
                            <span class="badge bg-secondary mb-2" style="font-size: 0.7rem;"><?= $committeeTypes[$member['committee_type']] ?? '' ?></span>
                        <?php endif; ?>
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
