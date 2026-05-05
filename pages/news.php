<?php
require_once __DIR__ . '/../config/init.php';

$pageTitle = 'Alumni Biography - ' . SITE_NAME;
$currentPage = 'news';

$pdo = getDBConnection();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$totalCount = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$pagination = paginate($totalCount, ITEMS_PER_PAGE, $page);

$stmt = $pdo->prepare("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$newsItems = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Alumni Biography</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">Alumni Biography</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Biography Listing -->
<section class="section-padding">
    <div class="container">
        <?php if (empty($newsItems)): ?>
            <div class="no-results">
                <i class="bi bi-person-vcard"></i>
                <h4>No biographies available yet</h4>
                <p>Stay tuned for featured alumni profiles!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($newsItems as $news): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom h-100">
                        <div class="card-img-wrapper">
                            <?php if ($news['image']): ?>
                                <img src="<?= UPLOAD_URL ?>news/<?= sanitize($news['image']) ?>" alt="<?= sanitize($news['title']) ?>">
                            <?php else: ?>
                                <div class="placeholder-img w-100 h-100"><i class="bi bi-person-circle"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= SITE_URL ?>/pages/news_detail.php?slug=<?= sanitize($news['slug']) ?>">
                                    <?= sanitize($news['title']) ?>
                                </a>
                            </h5>
                            <?php if (!empty($news['designation'])): ?>
                                <p class="text-muted small mb-2"><i class="bi bi-briefcase me-1"></i><?= sanitize($news['designation']) ?></p>
                            <?php endif; ?>
                            <p class="card-text">
                                <?= sanitize(truncateText(strip_tags($news['content']), 120)) ?>
                            </p>
                        </div>
                        <div class="card-footer-custom">
                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i><?= formatDate($news['created_at']) ?>
                            </span>
                            <a href="<?= SITE_URL ?>/pages/news_detail.php?slug=<?= sanitize($news['slug']) ?>" class="read-more">
                                Read Biography <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
