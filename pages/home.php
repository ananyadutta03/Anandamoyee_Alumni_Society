<?php
$pdo = getDBConnection();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Welcome to <br><span class="highlight">Kheora Anandamoyee High School Alumni Association</span></h1>
        <p>Honoring our roots, empowering future generations</p>
        <a href="<?= SITE_URL ?>/pages/about.php" class="btn btn-primary-custom btn-hero">
            Learn More <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- About Preview Section -->
<section class="about-section section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="about-img">
                    <img src="<?= SITE_URL ?>/assets/images/about-preview.jpg" alt="About Anandamoyee Alumni Society"
                         onerror="this.outerHTML='<div class=\'placeholder-img rounded\' style=\'height:400px\'><i class=\'bi bi-building\'></i></div>'">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h3>About Anandamoyee Alumni Association</h3>
                    <p>Honoring our roots, empowering future generations.</p>
                    <p></p>

                    <!-- <div class="about-features">
                        <div class="feature-item">
                            <i class="bi bi-people-fill"></i>
                            <div>
                                <strong>Networking</strong>
                                <p class="mb-0">Connect with fellow Anandamoyee alumni across industries and batches.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-calendar-event-fill"></i>
                            <div>
                                <strong>Events</strong>
                                <p class="mb-0">Regular gatherings, homecomings, and professional meetups.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-heart-fill"></i>
                            <div>
                                <strong>Community Service</strong>
                                <p class="mb-0">Making a positive impact through social service initiatives.</p>
                            </div>
                        </div>
                    </div> -->

                    <a href="<?= SITE_URL ?>/pages/about.php" class="btn btn-primary-custom mt-4">
                        Read More <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="section-padding bg-light-custom">
    <div class="container">
        <div class="section-title">
            <h2>Upcoming Events</h2>
            <p>Stay updated with our latest events and activities</p>
        </div>

        <div class="row g-4">
            <?php
            $stmt = $pdo->query("SELECT * FROM events WHERE status = 'published' ORDER BY event_date DESC LIMIT 3");
            $events = $stmt->fetchAll();

            if (empty($events)):
            ?>
                <div class="col-12">
                    <div class="no-results">
                        <i class="bi bi-calendar-x"></i>
                        <h4>No upcoming events</h4>
                        <p>Check back soon for new events!</p>
                    </div>
                </div>
            <?php else: ?>
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
                                <span class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i><?= sanitize($event['location']) ?>
                                </span>
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
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?= SITE_URL ?>/pages/events.php" class="btn btn-outline-primary-custom px-4">
                View All Events <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Alumni Biography Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Featured Alumni</h2>
            <p>Meet distinguished alumni of our community</p>
        </div>

        <div class="row g-4">
            <?php
            $stmt = $pdo->query("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
            $newsItems = $stmt->fetchAll();

            if (empty($newsItems)):
            ?>
                <div class="col-12">
                    <div class="no-results">
                        <i class="bi bi-person-vcard"></i>
                        <h4>No biographies available yet</h4>
                        <p>Stay tuned for featured alumni profiles!</p>
                    </div>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?= SITE_URL ?>/pages/news.php" class="btn btn-outline-primary-custom px-4">
                View All Biographies <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>
