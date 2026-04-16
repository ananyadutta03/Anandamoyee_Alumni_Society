<?php
require_once __DIR__ . '/../config/init.php';

$pdo = getDBConnection();
$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$event = $stmt->fetch();

if (!$event) {
    setFlash('danger', 'Event not found.');
    redirect(SITE_URL . '/pages/events.php');
}

$pageTitle = sanitize($event['title']) . ' - ' . SITE_NAME;
$currentPage = 'events';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1><?= sanitize($event['title']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/events.php">Events</a></li>
                <li class="breadcrumb-item active"><?= sanitize($event['title']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Event Detail -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Event Image -->
                <?php if ($event['image']): ?>
                <div class="mb-4 rounded overflow-hidden shadow">
                    <img src="<?= UPLOAD_URL ?>events/<?= sanitize($event['image']) ?>" alt="<?= sanitize($event['title']) ?>" class="w-100" style="max-height: 500px; object-fit: cover;">
                </div>
                <?php endif; ?>

                <!-- Event Meta -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <span class="badge p-2 px-3" style="background:var(--color-primary);color:#fff;">
                        <i class="bi bi-calendar3 me-1"></i> <?= formatDate($event['event_date'], 'l, F j, Y') ?>
                    </span>
                    <?php if ($event['location']): ?>
                    <span class="badge bg-light text-dark p-2 px-3">
                        <i class="bi bi-geo-alt me-1"></i> <?= sanitize($event['location']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Event Content -->
                <div class="mb-4" style="line-height: 1.8; color: var(--color-text-muted);">
                    <?= nl2br(sanitize($event['description'])) ?>
                </div>

                <!-- Registration Section -->
                <?php if (isLoggedIn()):
                    $regStmt = $pdo->prepare("SELECT status FROM event_registrations WHERE event_id = ? AND user_id = ?");
                    $regStmt->execute([$event['id'], $_SESSION['user_id']]);
                    $existingReg = $regStmt->fetch();

                    if ($existingReg): ?>
                        <div class="alert alert-<?= $existingReg['status'] === 'approved' ? 'success' : ($existingReg['status'] === 'pending' ? 'warning' : 'danger') ?> mb-4">
                            <i class="bi bi-info-circle me-1"></i>
                            You are registered for this event. Status: <strong><?= ucfirst($existingReg['status']) ?></strong>
                        </div>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/pages/event_register.php?slug=<?= sanitize($event['slug']) ?>" class="btn btn-primary-custom btn-lg mb-4 d-inline-block">
                            <i class="bi bi-pencil-square me-1"></i> Register for this Event
                            <?php if (($event['registration_fee'] ?? 0) > 0): ?>
                                (&#2547;<?= number_format($event['registration_fee']) ?>)
                            <?php else: ?>
                                (Free)
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="btn btn-outline-primary-custom btn-lg mb-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login to Register
                    </a>
                <?php endif; ?>

                <!-- Back Button -->
                <div>
                    <a href="<?= SITE_URL ?>/pages/events.php" class="btn btn-outline-primary-custom">
                        <i class="bi bi-arrow-left me-1"></i> Back to Events
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
