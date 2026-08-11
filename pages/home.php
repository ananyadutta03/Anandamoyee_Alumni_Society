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


<!-- Notices Section -->
<section class="section-padding notice-section">


<div class="container">


    <!-- Section Header -->
    <div class="section-header text-center mb-5">

        <span class="section-subtitle">
            <i class="bi bi-megaphone-fill me-1"></i>
            Latest Updates
        </span>

        <h2 class="section-title">
            Latest Notices
        </h2>

        <p class="section-description">
            Stay updated with the latest announcements and important information.
        </p>

    </div>


    <?php

    $stmt = $pdo->query("
        SELECT
            id,
            title,
            content,
            image,
            status,
            created_at
        FROM notices
        WHERE status = 'published'
        ORDER BY created_at DESC
        LIMIT 3
    ");

    $notices = $stmt->fetchAll();

    ?>


    <?php if (empty($notices)): ?>

        <div class="no-results">

            <i class="bi bi-megaphone"></i>

            <h4>
                No notices available
            </h4>

            <p>
                There are currently no published notices.
            </p>

        </div>


    <?php else: ?>


        <div class="row g-4">


            <?php foreach ($notices as $notice): ?>


                <div class="col-md-6 col-lg-4">


                    <div class="card card-custom notice-card h-100">


                        <!-- Notice Image -->
                        <?php if (!empty($notice['image'])): ?>

                            <div class="notice-card-image">

                                <img
                                    src="<?= SITE_URL . '/' . htmlspecialchars($notice['image']) ?>"
                                    alt="<?= sanitize($notice['title']) ?>"
                                    loading="lazy"
                                >

                            </div>

                        <?php endif; ?>


                        <!-- Notice Header -->
                        <div class="notice-card-header">

                            <div class="notice-icon">

                                <i class="bi bi-megaphone-fill"></i>

                            </div>


                            <span class="notice-date">

                                <i class="bi bi-calendar3 me-1"></i>

                                <?= formatDate($notice['created_at']) ?>

                            </span>

                        </div>


                        <!-- Notice Content -->
                        <div class="card-body">

                            <h5 class="card-title">

                                <?= sanitize($notice['title']) ?>

                            </h5>


                            <p class="card-text">

                                <?= sanitize(
                                    truncateText(
                                        strip_tags($notice['content']),
                                        150
                                    )
                                ) ?>

                            </p>

                        </div>


                        <!-- Footer -->
                        <div class="card-footer-custom">


                            <span class="text-muted small">

                                <i class="bi bi-info-circle me-1"></i>

                                Announcement

                            </span>


                            <!-- Open Popup -->
                            <button
                                type="button"
                                class="read-more notice-read-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#noticeModal<?= (int) $notice['id'] ?>"
                            >

                                Read More

                                <i class="bi bi-arrow-right"></i>

                            </button>


                        </div>


                    </div>


                </div>



                <!-- Notice Modal -->
                <div
                    class="modal fade notice-modal"
                    id="noticeModal<?= (int) $notice['id'] ?>"
                    tabindex="-1"
                    aria-hidden="true"
                >

                    <div class="modal-dialog modal-dialog-centered modal-lg">

                        <div class="modal-content">


                            <!-- Modal Header -->
                            <div class="modal-header">


                                <div class="d-flex align-items-center">

                                    <div class="modal-notice-icon">

                                        <i class="bi bi-megaphone-fill"></i>

                                    </div>


                                    <div class="ms-3">

                                        <h5 class="modal-title">

                                            <?= sanitize($notice['title']) ?>

                                        </h5>


                                        <small class="text-muted">

                                            <i class="bi bi-calendar3 me-1"></i>

                                            <?= formatDate($notice['created_at']) ?>

                                        </small>

                                    </div>

                                </div>


                                <!-- Close Button -->
                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>


                            </div>


                            <!-- Modal Body -->
                            <div class="modal-body">


                                <!-- Notice Image -->
                                <?php if (!empty($notice['image'])): ?>

                                    <div class="notice-modal-image mb-4">

                                        <img
                                            src="<?= SITE_URL . '/' . htmlspecialchars($notice['image']) ?>"
                                            alt="<?= sanitize($notice['title']) ?>"
                                        >

                                    </div>

                                <?php endif; ?>


                                <!-- Notice Content -->
                                <div class="notice-full-content">

                                    <?= nl2br(
                                        sanitize($notice['content'])
                                    ) ?>

                                </div>


                            </div>


                            <!-- Modal Footer -->
                            <div class="modal-footer">


                                <!-- Share Button -->
                                <!-- Share Button -->
<button
    type="button"
    class="btn btn-share-notice"
    onclick="shareNotice(
        <?= (int) $notice['id'] ?>,
        <?= htmlspecialchars(
            json_encode($notice['title']),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    )"
>
    <i class="bi bi-share-fill me-1"></i>
    Share Notice
</button>


                                <!-- Close -->
                                <button
                                    type="button"
                                    class="btn btn-primary-custom"
                                    data-bs-dismiss="modal"
                                >

                                    <i class="bi bi-x-lg me-1"></i>

                                    Close

                                </button>


                            </div>


                        </div>

                    </div>

                </div>


            <?php endforeach; ?>


        </div>



        <!-- View Latest -->
        <div class="text-center mt-5">

            <a
    href="<?= SITE_URL ?>/pages/notices.php"
    class="btn btn-outline-primary-custom px-4"
>
    View All Notices
    <i class="bi bi-arrow-right ms-1"></i>
</a>

        </div>


    <?php endif; ?>


</div>


</section>

<!--
|--------------------------------------------------------------------------
| Notice CSS
|--------------------------------------------------------------------------
-->

<style>

    /*
    |--------------------------------------------------------------------------
    | Notice Image
    |--------------------------------------------------------------------------
    */

    .notice-card-image {
        width: 100%;
        height: 210px;
        overflow: hidden;
        background: #f8f9fa;
    }


    .notice-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }


    /*
    |--------------------------------------------------------------------------
    | Modal Image
    |--------------------------------------------------------------------------
    */

    .notice-modal-image {
        width: 100%;
        max-height: 450px;
        overflow: hidden;
        border-radius: 12px;
        background: #f8f9fa;
    }


    .notice-modal-image img {
        width: 100%;
        max-height: 450px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }


    /*
    |--------------------------------------------------------------------------
    | Share Button
    |--------------------------------------------------------------------------
    */

    .btn-share-notice {
        border: 1px solid #0d6efd;
        background: #ffffff;
        color: #0d6efd;
        border-radius: 8px;
        font-weight: 500;
        padding: 8px 16px;
        transition: none;
    }


    .btn-share-notice:hover {
        background: #0d6efd;
        color: #ffffff;
        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */

    @media (max-width: 576px) {

        .notice-card-image {
            height: 180px;
        }


        .notice-modal-image,
        .notice-modal-image img {
            max-height: 300px;
        }

    }

</style>

<!--
|--------------------------------------------------------------------------
| Notice Share JavaScript
|--------------------------------------------------------------------------
-->

<script>

    async function shareNotice(noticeId, noticeTitle) {

        /*
         * Generate notice URL
         */
        const noticeUrl =
            window.location.origin +
            window.location.pathname +
            '#notice-' +
            noticeId;


        /*
         * Native Share API
         */
        if (navigator.share) {

            try {

                await navigator.share({

                    title: noticeTitle,

                    text:
                        noticeTitle +
                        '\n\nRead this notice from our Alumni Association.',

                    url: noticeUrl

                });

            } catch (error) {

                /*
                 * User cancelled the share menu.
                 * No action required.
                 */

                if (error.name !== 'AbortError') {

                    console.error(
                        'Share failed:',
                        error
                    );

                }

            }

            return;
        }


        /*
         * Fallback: Copy Link
         */
        try {

            await navigator.clipboard.writeText(noticeUrl);

            showNoticeShareMessage(
                'Notice link copied to clipboard!'
            );

        } catch (error) {

            /*
             * Older browser fallback
             */
            const tempInput =
                document.createElement('input');

            tempInput.value = noticeUrl;

            document.body.appendChild(tempInput);

            tempInput.select();

            document.execCommand('copy');

            document.body.removeChild(tempInput);

            showNoticeShareMessage(
                'Notice link copied to clipboard!'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Share Success Message
    |--------------------------------------------------------------------------
    */

    function showNoticeShareMessage(message) {

        const existing =
            document.getElementById(
                'noticeShareToast'
            );

        if (existing) {
            existing.remove();
        }


        const toast =
            document.createElement('div');

        toast.id = 'noticeShareToast';

        toast.innerHTML = `
            <i class="bi bi-check-circle-fill me-2"></i>
            ${message}
        `;


        toast.style.position = 'fixed';

        toast.style.bottom = '25px';

        toast.style.right = '25px';

        toast.style.zIndex = '99999';

        toast.style.background = '#198754';

        toast.style.color = '#ffffff';

        toast.style.padding = '12px 18px';

        toast.style.borderRadius = '8px';

        toast.style.fontSize = '14px';

        toast.style.boxShadow =
            '0 5px 20px rgba(0,0,0,0.15)';


        document.body.appendChild(toast);


        setTimeout(function () {

            toast.remove();

        }, 2500);

    }

</script>






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
<script>
async function shareNotice(noticeId, noticeTitle) {

    /*
    |--------------------------------------------------------------------------
    | Create Notice URL
    |--------------------------------------------------------------------------
    */

    const noticeUrl =
        window.location.origin +
        '<?= parse_url(SITE_URL, PHP_URL_PATH) ?>' +
        '/pages/notices.php?notice=' +
        noticeId;


    const shareData = {
        title: noticeTitle,
        text: 'Check out this notice from Anandamoyee Alumni Association.',
        url: noticeUrl
    };


    /*
    |--------------------------------------------------------------------------
    | Native Share
    |--------------------------------------------------------------------------
    */

    if (navigator.share) {

        try {

            await navigator.share(shareData);

        } catch (error) {

            if (error.name !== 'AbortError') {
                console.error('Share failed:', error);
            }

        }

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Desktop Fallback - Copy Link
    |--------------------------------------------------------------------------
    */

    try {

        await navigator.clipboard.writeText(noticeUrl);

        alert('Notice link copied successfully!');

    } catch (error) {

        prompt(
            'Copy this notice link:',
            noticeUrl
        );

    }

}
</script>