<?php
require_once __DIR__ . '/../config/init.php';

$pageTitle = 'Events - ' . SITE_NAME;
$currentPage = 'events';

$pdo = getDBConnection();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$totalCount = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
$pagination = paginate($totalCount, ITEMS_PER_PAGE, $page);

$stmt = $pdo->prepare("SELECT * FROM events WHERE status = 'published' ORDER BY event_date DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Events</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">Events</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Events Listing -->
<section class="section-padding">
    <div class="container">
        <?php if (empty($events)): ?>
            <div class="no-results">
                <i class="bi bi-calendar-x"></i>
                <h4>No events found</h4>
                <p>Check back soon for upcoming events!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom">
                        <div class="card-img-wrapper">
                            <?php if ($event['image']): ?>
                                <img src="<?= UPLOAD_URL ?>events/<?= sanitize($event['image']) ?>" alt="<?= sanitize($event['title']) ?>">
                            <?php else: ?>
                                <div class="placeholder-img w-100 h-100"><i class="bi bi-calendar-event"></i></div>
                            <?php endif; ?>
                            <span class="date-badge">
                                <i class="bi bi-calendar3 me-1"></i> <?= formatDate($event['event_date']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= SITE_URL ?>/pages/event_detail.php?slug=<?= sanitize($event['slug']) ?>">
                                    <?= sanitize($event['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text"><?= sanitize(truncateText($event['description'])) ?></p>
                        </div>
                        <div class="card-footer-custom">
                            <?php if ($event['location']): ?>
                                <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= sanitize($event['location']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/pages/event_detail.php?slug=<?= sanitize($event['slug']) ?>" class="read-more">
                                Read More <i class="bi bi-arrow-right"></i>
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
