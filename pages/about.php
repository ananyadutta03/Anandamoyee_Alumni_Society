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
                    <img src="<?= SITE_URL ?>/assets/images/about-full.jpg" alt="About Anandamoyee Alumni Society" style="height: 100%; object-fit: cover;"
                         onerror="this.outerHTML='<div class=\'placeholder-img rounded\' style=\'height:100%;min-height:400px\'><i class=\'bi bi-building\'></i></div>'">
                </div>
            </div>
            <div class="col-lg-6">
                <h2 style="font-size: 2rem; margin-bottom: 20px;">Anandamoyee Alumni Association</h2>

                <p class="text-muted">Kheora Anandamoyee High School Alumni Association is a network of former students committed to staying connected with each other and supporting the growth and development of their alma mater. The association works to foster alumni engagement, support students, and contribute to the long-term progress of the school and community.</p>

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
                        <p class="text-muted">To The mission of the Kheora Anandamoyee High School Alumni Association is to foster a lifelong connection among former students, strengthen the bond between alumni and the school, and contribute to the academic, cultural, and social development of the institution. The association aims to support current students through mentorship, scholarships, and resources while encouraging alumni to collaborate in initiatives that promote educational excellence, community service, and the continued growth of our beloved school. </p>
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
                        <p class="text-muted">To build a strong and united community of alumni who remain connected to their roots, support the development of Kheora Anandamoyee High School, and contribute to the educational, social, and professional growth of its students and graduates. The association envisions empowering future generations through mentorship, collaboration, and resources while preserving the traditions, values, and legacy of our beloved institution.</p>
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
