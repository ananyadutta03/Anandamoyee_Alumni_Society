<?php
require_once __DIR__ . '/../config/init.php';

$pdo = getDBConnection();
$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('danger', 'Biography not found.');
    redirect(SITE_URL . '/pages/news.php');
}

$pageTitle = sanitize($news['title']) . ' - Biography - ' . SITE_NAME;
$currentPage = 'news';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1><?= sanitize($news['title']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/news.php">Alumni Biography</a></li>
                <li class="breadcrumb-item active"><?= sanitize($news['title']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<style>
    .bio-content a { color: #0d6efd !important; text-decoration: underline; }
    .bio-content strong, .bio-content b { font-weight: 700; color: var(--color-dark); }
    .bio-content p { margin-bottom: 1rem; }
    .bio-photo {
        width: 220px; height: 220px; border-radius: 50%;
        object-fit: cover; border: 5px solid var(--color-primary);
        box-shadow: var(--shadow-lg);
    }
</style>

<!-- Biography Detail -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <!-- Alumni Header -->
                <div class="text-center mb-5">
                    <?php if ($news['image']): ?>
                        <img src="<?= UPLOAD_URL ?>news/<?= sanitize($news['image']) ?>" alt="<?= sanitize($news['title']) ?>" class="bio-photo mb-3">
                    <?php else: ?>
                        <div class="bio-photo d-inline-flex align-items-center justify-content-center bg-light mb-3">
                            <i class="bi bi-person-fill" style="font-size: 5rem; color: var(--color-primary);"></i>
                        </div>
                    <?php endif; ?>
                    <h2 class="mb-2"><?= sanitize($news['title']) ?></h2>
                    <?php if (!empty($news['designation'])): ?>
                        <p class="lead text-muted mb-0">
                            <i class="bi bi-briefcase me-1"></i><?= sanitize($news['designation']) ?>
                        </p>
                    <?php endif; ?>
                    <p class="text-muted small mt-2">
                        <i class="bi bi-calendar3 me-1"></i>Added on <?= formatDate($news['created_at'], 'F j, Y') ?>
                    </p>
                </div>

                <hr class="my-4">

                <!-- Biography Content -->
                <div class="bio-content" style="line-height: 1.9; font-size: 1.05rem; color: var(--color-text-muted);">
                    <?= sanitizeHtml($news['content']) ?>
                </div>

                <!-- Back Button -->
                <div class="text-center mt-5">
                    <a href="<?= SITE_URL ?>/pages/news.php" class="btn btn-outline-primary-custom">
                        <i class="bi bi-arrow-left me-1"></i> Back to Alumni Biography
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
