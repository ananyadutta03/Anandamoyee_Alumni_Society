<?php
require_once __DIR__ . '/../config/init.php';

$pageTitle = 'About Us - ' . SITE_NAME;
$currentPage = 'about';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>About Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">About Us</li>
            </ol>
        </nav>
    </div>
</section>

<!-- About Content -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="about-img" style="height: 100%; min-height: 400px;">
                    <img src="<?= SITE_URL ?>/assets/images/about-full.jpg" alt="About AIUB Alumni Society" style="height: 100%; object-fit: cover;"
                         onerror="this.outerHTML='<div class=\'placeholder-img rounded\' style=\'height:100%;min-height:400px\'><i class=\'bi bi-building\'></i></div>'">
                </div>
            </div>
            <div class="col-lg-6">
                <h2 style="font-size: 2rem; margin-bottom: 20px;">AIUB Society </h2>

                <p class="text-muted">AIUB Society is a non-political and non-profit making  Society. </p>

            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section-padding bg-light-custom">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <i class="bi bi-bullseye" style="font-size: 3rem; color: var(--color-primary);"></i>
                        </div>
                        <h4 class="text-center mb-3">Our Mission</h4>
                        <p class="text-muted">To ....... </p>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-primary-custom me-2"></i> Our Focus......</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <i class="bi bi-eye" style="font-size: 3rem; color: var(--color-primary);"></i>
                        </div>
                        <h4 class="text-center mb-3">Our Vision</h4>
                        <p class="text-muted">To be ......</p>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><i class="bi bi-star-fill text-primary-custom me-2"></i> Visions.....</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section-padding">
    <div class="container">
        <?php
        $pdo = getDBConnection();
        $memberCount = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'approved'")->fetchColumn();
        $eventCount = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
        ?>
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="card card-custom p-4">
                    <i class="bi bi-people-fill" style="font-size: 2.5rem; color: var(--color-primary);"></i>
                    <h3 class="mt-2 mb-0"><?= $memberCount ?>+</h3>
                    <p class="text-muted mb-0">Members</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-4">
                    <i class="bi bi-calendar-event-fill" style="font-size: 2.5rem; color: var(--color-primary);"></i>
                    <h3 class="mt-2 mb-0"><?= $eventCount ?>+</h3>
                    <p class="text-muted mb-0">Events</p>
                </div>
            </div>  
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
