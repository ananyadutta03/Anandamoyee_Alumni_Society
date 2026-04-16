<?php
require_once __DIR__ . '/../config/init.php';

$pdo = getDBConnection();
$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('danger', 'News article not found.');
    redirect(SITE_URL . '/pages/news.php');
}

$pageTitle = sanitize($news['title']) . ' - ' . SITE_NAME;
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
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/news.php">News</a></li>
                <li class="breadcrumb-item active"><?= sanitize($news['title']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- News Detail -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- News Image -->
                <?php if ($news['image']): ?>
                <div class="mb-4 rounded overflow-hidden shadow">
                    <img src="<?= UPLOAD_URL ?>news/<?= sanitize($news['image']) ?>" alt="<?= sanitize($news['title']) ?>" class="w-100" style="max-height: 500px; object-fit: cover;">
                </div>
                <?php endif; ?>

                <!-- News Meta -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <span class="badge p-2 px-3" style="background:var(--color-primary);color:#fff;">
                        <i class="bi bi-clock me-1"></i> <?= formatDate($news['created_at'], 'l, F j, Y') ?>
                    </span>
                    <?php if ($news['author']): ?>
                    <span class="badge bg-light text-dark p-2 px-3">
                        <i class="bi bi-person me-1"></i> <?= sanitize($news['author']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- News Content -->
                <div class="mb-4" style="line-height: 1.8; color: var(--color-text-muted);">
                    <?= nl2br(sanitize($news['content'])) ?>
                </div>

                <!-- Back Button -->
                <a href="<?= SITE_URL ?>/pages/news.php" class="btn btn-outline-primary-custom">
                    <i class="bi bi-arrow-left me-1"></i> Back to News
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
