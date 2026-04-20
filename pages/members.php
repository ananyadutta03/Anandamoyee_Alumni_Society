<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();

$pageTitle = 'Members Directory - ' . SITE_NAME;
$currentPage = 'members';

$pdo = getDBConnection();

// Search filter
$search = trim($_GET['search'] ?? '');

$sql = "SELECT name, email, batch, profile_image, created_at FROM users WHERE status = 'approved' AND role = 'user'";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Members Directory</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">Members</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Members Directory -->
<section class="section-padding">
    <div class="container">
        <!-- Search & Filter -->
        <div class="card card-custom mb-4">
            <div class="card-body p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= sanitize($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <p class="text-muted mb-3"><?= count($members) ?> member(s) found</p>

        <?php if (empty($members)): ?>
            <div class="no-results">
                <i class="bi bi-people"></i>
                <h4>No members found</h4>
                <p>Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($members as $member): ?>
            <div class="member-card">
                <?php if ($member['profile_image']): ?>
                    <img src="<?= UPLOAD_URL ?>members/<?= sanitize($member['profile_image']) ?>" alt="<?= sanitize($member['name']) ?>" class="member-avatar">
                <?php else: ?>
                    <div class="member-avatar d-flex align-items-center justify-content-center bg-light">
                        <i class="bi bi-person-fill" style="font-size: 1.5rem; color: var(--color-primary);"></i>
                    </div>
                <?php endif; ?>
                <div class="member-info">
                    <h6><?= sanitize($member['name']) ?></h6>
                    <?php if ($member['batch']): ?>
                        <p><i class="bi bi-mortarboard me-1"></i>Batch: <?= sanitize($member['batch']) ?></p>
                    <?php endif; ?>
                    <p><i class="bi bi-envelope me-1"></i><?= sanitize($member['email']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
