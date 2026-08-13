<?php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

$pageTitle = "All Notices";

/*
|--------------------------------------------------------------------------
| Shared Notice
|--------------------------------------------------------------------------
|
| Example:
| notices.php?notice=21
|
| If a valid notice ID is provided, that notice modal
| will automatically open.
|
*/

$sharedNoticeId = isset($_GET['notice'])
    ? (int) $_GET['notice']
    : 0;


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$perPage = 6;

$currentPage = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}


/*
|--------------------------------------------------------------------------
| Check Shared Notice
|--------------------------------------------------------------------------
|
| Only allow published notices.
|
*/

if ($sharedNoticeId > 0) {

    $sharedNoticeStmt = $pdo->prepare("
        SELECT id
        FROM notices
        WHERE id = :id
        AND status = 'published'
        LIMIT 1
    ");

    $sharedNoticeStmt->execute([
        ':id' => $sharedNoticeId
    ]);

    if (!$sharedNoticeStmt->fetch()) {
        $sharedNoticeId = 0;
    }
}


/*
|--------------------------------------------------------------------------
| Get Total Notices
|--------------------------------------------------------------------------
*/

$countStmt = $pdo->query("
    SELECT COUNT(*)
    FROM notices
    WHERE status = 'published'
");

$totalNotices = (int) $countStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Calculate Pagination
|--------------------------------------------------------------------------
*/

$totalPages = max(
    1,
    (int) ceil($totalNotices / $perPage)
);

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;


/*
|--------------------------------------------------------------------------
| Get Notices
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        content,
        image,
        created_at
    FROM notices
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(
    ':limit',
    $perPage,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':offset',
    $offset,
    PDO::PARAM_INT
);

$stmt->execute();

$notices = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

include __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| Navbar
|--------------------------------------------------------------------------
*/

include __DIR__ . '/../includes/navbar.php';

?>

<style>

/*
|--------------------------------------------------------------------------
| All Notices Page
|--------------------------------------------------------------------------
*/

.all-notices-section {
    padding: 70px 0;
}


/*
|--------------------------------------------------------------------------
| Page Header
|--------------------------------------------------------------------------
*/

.notices-page-header {
    margin-bottom: 45px;
}

.notices-page-header .section-subtitle {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #198754;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
}

.notices-page-header h1 {
    font-weight: 700;
    color: #212529;
    margin-bottom: 10px;
}

.notices-page-header p {
    color: #6c757d;
    max-width: 650px;
    margin: 0 auto;
}


/*
|--------------------------------------------------------------------------
| Notice Card
|--------------------------------------------------------------------------
*/

.all-notice-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    overflow: hidden;
    height: 100%;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}


/*
|--------------------------------------------------------------------------
| Notice Image
|--------------------------------------------------------------------------
*/

.all-notice-image {
    width: 100%;
    height: 220px;
    overflow: hidden;
    background: #f8f9fa;
}

.all-notice-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}


/*
|--------------------------------------------------------------------------
| No Image
|--------------------------------------------------------------------------
*/

.all-notice-no-image {
    width: 100%;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f5ee;
    color: #198754;
    font-size: 45px;
}


/*
|--------------------------------------------------------------------------
| Card Header
|--------------------------------------------------------------------------
*/

.all-notice-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 18px 20px 0;
}

.all-notice-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f5ee;
    color: #198754;
    font-size: 18px;
}

.all-notice-date {
    color: #8a8f98;
    font-size: 12px;
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| Card Body
|--------------------------------------------------------------------------
*/

.all-notice-body {
    padding: 20px;
}

.all-notice-title {
    font-size: 19px;
    font-weight: 700;
    color: #212529;
    line-height: 1.4;
    margin-bottom: 10px;
}

.all-notice-description {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 0;
}


/*
|--------------------------------------------------------------------------
| Card Footer
|--------------------------------------------------------------------------
*/

.all-notice-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 20px 20px;
    border-top: 1px solid #f0f0f0;
}

.all-notice-type {
    color: #6c757d;
    font-size: 12px;
}


/*
|--------------------------------------------------------------------------
| Read More
|--------------------------------------------------------------------------
*/

.all-notice-read-btn {
    border: 0;
    background: transparent;
    color: #198754;
    font-size: 14px;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
}

.all-notice-read-btn:hover {
    color: #146c43;
}


/*
|--------------------------------------------------------------------------
| Share Button
|--------------------------------------------------------------------------
*/

.notice-share-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #dee2e6;
    background: #ffffff;
    color: #198754;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: none;
}

.notice-share-btn:hover {
    background: #f8f9fa;
    color: #198754;
}


/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

.notice-modal .modal-content {
    border: 0;
    border-radius: 16px;
    overflow: hidden;
}

.notice-modal .modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #eeeeee;
}

.notice-modal-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f5ee;
    color: #198754;
    font-size: 19px;
}

.notice-modal .modal-title {
    font-size: 19px;
    font-weight: 700;
    color: #212529;
}

.notice-modal .modal-body {
    padding: 25px;
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
    margin-bottom: 25px;
}

.notice-modal-image img {
    width: 100%;
    max-height: 450px;
    object-fit: contain;
    display: block;
}


/*
|--------------------------------------------------------------------------
| Full Content
|--------------------------------------------------------------------------
*/

.notice-full-content {
    color: #343a40;
    font-size: 15px;
    line-height: 1.8;
    word-break: break-word;
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

.notice-pagination {
    margin-top: 50px;
}

.notice-pagination .pagination {
    gap: 6px;
}

.notice-pagination .page-link {
    border: 1px solid #dee2e6;
    border-radius: 8px !important;
    color: #198754;
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 500;
}

.notice-pagination .page-link:hover {
    background: #e8f5ee;
    border-color: #198754;
    color: #146c43;
}

.notice-pagination .page-item.active .page-link {
    background: #198754;
    border-color: #198754;
    color: #ffffff;
}

.notice-pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background: #f8f9fa;
    border-color: #e9ecef;
}


/*
|--------------------------------------------------------------------------
| Page Information
|--------------------------------------------------------------------------
*/

.notice-page-info {
    text-align: center;
    color: #6c757d;
    font-size: 13px;
    margin-top: 15px;
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

.no-notices {
    text-align: center;
    padding: 70px 20px;
    background: #f8f9fa;
    border: 1px dashed #ced4da;
    border-radius: 16px;
}

.no-notices i {
    font-size: 50px;
    color: #adb5bd;
    margin-bottom: 15px;
}

.no-notices h4 {
    font-weight: 600;
    color: #343a40;
}

.no-notices p {
    color: #6c757d;
    margin-bottom: 0;
}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 576px) {

    .all-notices-section {
        padding: 50px 0;
    }

    .notices-page-header {
        margin-bottom: 30px;
    }

    .notices-page-header h1 {
        font-size: 28px;
    }

    .all-notice-image {
        height: 190px;
    }

    .all-notice-title {
        font-size: 17px;
    }

    .notice-modal .modal-body {
        padding: 18px;
    }

    .notice-modal-image,
    .notice-modal-image img {
        max-height: 300px;
    }

    .notice-pagination .pagination {
        gap: 3px;
    }

    .notice-pagination .page-link {
        min-width: 36px;
        height: 36px;
        font-size: 13px;
    }

    .all-notice-footer {
        align-items: center;
    }

}

</style>

<!--
|--------------------------------------------------------------------------
| All Notices Section
|--------------------------------------------------------------------------
-->

<section class="all-notices-section">

```
<div class="container">


    <!-- Page Header -->

    <div class="notices-page-header text-center">

        <span class="section-subtitle">

            <i class="bi bi-megaphone-fill"></i>

            Latest Updates

        </span>


        <h1>
            All Notices
        </h1>


        <p>
            Stay updated with all the latest announcements
            and important information from the Alumni Association.
        </p>

    </div>


    <?php if (empty($notices)): ?>


        <!-- No Notices -->

        <div class="no-notices">

            <i class="bi bi-megaphone"></i>

            <h4>
                No notices available
            </h4>

            <p>
                There are currently no published notices.
            </p>

        </div>


    <?php else: ?>


        <!-- Notice Grid -->

        <div class="row g-4">


            <?php foreach ($notices as $notice): ?>


                <div class="col-md-6 col-lg-4">

                    <div class="all-notice-card">


                        <!-- Notice Image -->

                        <?php if (!empty($notice['image'])): ?>

                            <div class="all-notice-image">

                                <img
                                    src="<?= SITE_URL . '/' . htmlspecialchars($notice['image']) ?>"
                                    alt="<?= sanitize($notice['title']) ?>"
                                    loading="lazy"
                                >

                            </div>

                        <?php else: ?>

                            <div class="all-notice-no-image">

                                <i class="bi bi-megaphone-fill"></i>

                            </div>

                        <?php endif; ?>


                        <!-- Notice Header -->

                        <div class="all-notice-header">

                            <div class="all-notice-icon">

                                <i class="bi bi-megaphone-fill"></i>

                            </div>


                            <span class="all-notice-date">

                                <i class="bi bi-calendar3 me-1"></i>

                                <?= formatDate($notice['created_at']) ?>

                            </span>

                        </div>


                        <!-- Notice Body -->

                        <div class="all-notice-body">

                            <h5 class="all-notice-title">

                                <?= sanitize($notice['title']) ?>

                            </h5>


                            <p class="all-notice-description">

                                <?= sanitize(
                                    truncateText(
                                        strip_tags($notice['content']),
                                        150
                                    )
                                ) ?>

                            </p>

                        </div>


                        <!-- Footer -->

                        <div class="all-notice-footer">


                            <span class="all-notice-type">

                                <i class="bi bi-info-circle me-1"></i>

                                Announcement

                            </span>


                            <div class="d-flex align-items-center gap-3">


                                <!-- Share Button -->

                                <button
                                    type="button"
                                    class="notice-share-btn"
                                    onclick="shareNotice(
                                        <?= (int) $notice['id'] ?>,
                                        <?= htmlspecialchars(
                                            json_encode($notice['title']),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    )"
                                    title="Share Notice"
                                    aria-label="Share Notice"
                                >

                                    <i class="bi bi-share"></i>

                                </button>


                                <!-- Read More -->

                                <button
                                    type="button"
                                    class="all-notice-read-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#noticeModal<?= (int) $notice['id'] ?>"
                                >

                                    Read More

                                    <i class="bi bi-arrow-right ms-1"></i>

                                </button>


                            </div>

                        </div>


                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | Notice Modal
                |--------------------------------------------------------------------------
                -->

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

                                    <div class="notice-modal-icon">

                                        <i class="bi bi-megaphone-fill"></i>

                                    </div>


                                    <div class="ms-3">

                                        <h5 class="modal-title mb-1">

                                            <?= sanitize($notice['title']) ?>

                                        </h5>


                                        <small class="text-muted">

                                            <i class="bi bi-calendar3 me-1"></i>

                                            <?= formatDate($notice['created_at']) ?>

                                        </small>

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>

                            </div>


                            <!-- Modal Body -->

                            <div class="modal-body">


                                <!-- Image -->

                                <?php if (!empty($notice['image'])): ?>

                                    <div class="notice-modal-image">

                                        <img
                                            src="<?= SITE_URL . '/' . htmlspecialchars($notice['image']) ?>"
                                            alt="<?= sanitize($notice['title']) ?>"
                                        >

                                    </div>

                                <?php endif; ?>


                                <!-- Content -->

                                <div class="notice-full-content">

                                    <?= nl2br(
                                        sanitize($notice['content'])
                                    ) ?>

                                </div>


                            </div>


                            <!-- Modal Footer -->

                            <div class="modal-footer">


                                <!-- Share -->

                                <button
                                    type="button"
                                    class="btn btn-success"
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
                                    class="btn btn-secondary"
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


        <!--
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        -->

        <?php if ($totalPages > 1): ?>

            <div class="notice-pagination">

                <nav aria-label="Notice pagination">

                    <ul class="pagination justify-content-center">


                        <!-- Previous -->

                        <li
                            class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>"
                        >

                            <?php if ($currentPage > 1): ?>

                                <a
                                    class="page-link"
                                    href="?page=<?= $currentPage - 1 ?>"
                                    aria-label="Previous"
                                >

                                    <i class="bi bi-chevron-left"></i>

                                </a>

                            <?php else: ?>

                                <span class="page-link">

                                    <i class="bi bi-chevron-left"></i>

                                </span>

                            <?php endif; ?>

                        </li>


                        <?php

                        $startPage = max(
                            1,
                            $currentPage - 2
                        );

                        $endPage = min(
                            $totalPages,
                            $currentPage + 2
                        );


                        if ($currentPage <= 3) {

                            $startPage = 1;

                            $endPage = min(
                                5,
                                $totalPages
                            );
                        }


                        if ($currentPage >= $totalPages - 2) {

                            $startPage = max(
                                1,
                                $totalPages - 4
                            );

                            $endPage = $totalPages;
                        }

                        ?>


                        <!-- First Page -->

                        <?php if ($startPage > 1): ?>

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?page=1"
                                >
                                    1
                                </a>

                            </li>


                            <?php if ($startPage > 2): ?>

                                <li class="page-item disabled">

                                    <span class="page-link">
                                        ...
                                    </span>

                                </li>

                            <?php endif; ?>

                        <?php endif; ?>


                        <!-- Page Numbers -->

                        <?php for (
                            $i = $startPage;
                            $i <= $endPage;
                            $i++
                        ): ?>

                            <li
                                class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>"
                            >

                                <a
                                    class="page-link"
                                    href="?page=<?= $i ?>"
                                >

                                    <?= $i ?>

                                </a>

                            </li>

                        <?php endfor; ?>


                        <!-- Last Page -->

                        <?php if ($endPage < $totalPages): ?>

                            <?php if ($endPage < $totalPages - 1): ?>

                                <li class="page-item disabled">

                                    <span class="page-link">
                                        ...
                                    </span>

                                </li>

                            <?php endif; ?>


                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="?page=<?= $totalPages ?>"
                                >

                                    <?= $totalPages ?>

                                </a>

                            </li>

                        <?php endif; ?>


                        <!-- Next -->

                        <li
                            class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>"
                        >

                            <?php if ($currentPage < $totalPages): ?>

                                <a
                                    class="page-link"
                                    href="?page=<?= $currentPage + 1 ?>"
                                    aria-label="Next"
                                >

                                    <i class="bi bi-chevron-right"></i>

                                </a>

                            <?php else: ?>

                                <span class="page-link">

                                    <i class="bi bi-chevron-right"></i>

                                </span>

                            <?php endif; ?>

                        </li>


                    </ul>

                </nav>


                <!-- Page Information -->

                <div class="notice-page-info">

                    Showing

                    <?= $offset + 1 ?>

                    -

                    <?= min(
                        $offset + $perPage,
                        $totalNotices
                    ) ?>

                    of

                    <?= $totalNotices ?>

                    notices

                </div>

            </div>

        <?php endif; ?>


    <?php endif; ?>


</div>
```

</section>

<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>

/*
|--------------------------------------------------------------------------
| Share Notice
|--------------------------------------------------------------------------
*/

async function shareNotice(noticeId, noticeTitle) {

    /*
    |--------------------------------------------------------------------------
    | Build Permanent Notice URL
    |--------------------------------------------------------------------------
    |
    | Example:
    | http://localhost/Anandamoyee_Website/pages/notices.php?notice=21
    |
    */

    const noticeUrl =
        window.location.origin +
        window.location.pathname +
        '?notice=' +
        encodeURIComponent(noticeId);


    const shareData = {

        title: noticeTitle,

        text:
            'Check out this notice from Anandamoyee Alumni Association.',

        url: noticeUrl

    };


    /*
    |--------------------------------------------------------------------------
    | Mobile / Supported Browser
    |--------------------------------------------------------------------------
    */

    if (
        navigator.share &&
        typeof navigator.share === 'function'
    ) {

        try {

            await navigator.share(shareData);

            return;

        } catch (error) {

            /*
            | User cancelled the share dialog.
            */

            if (error.name === 'AbortError') {

                return;

            }

            console.error(
                'Native share failed:',
                error
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Desktop / Fallback
    |--------------------------------------------------------------------------
    */

    try {

        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {

            await navigator.clipboard.writeText(
                noticeUrl
            );

            alert(
                'Notice link copied to clipboard!'
            );

            return;
        }


        /*
        | Clipboard API unavailable
        */

        const textArea =
            document.createElement('textarea');

        textArea.value = noticeUrl;

        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';

        document.body.appendChild(textArea);

        textArea.focus();
        textArea.select();

        document.execCommand('copy');

        textArea.remove();

        alert(
            'Notice link copied to clipboard!'
        );

    } catch (error) {

        /*
        | Last fallback
        */

        window.prompt(
            'Copy this notice link:',
            noticeUrl
        );

    }

}


/*
|--------------------------------------------------------------------------
| Open Shared Notice Automatically
|--------------------------------------------------------------------------
|
| When someone opens:
|
| notices.php?notice=21
|
| the page will automatically open Notice #21.
|
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const sharedNoticeId =
            <?= (int) $sharedNoticeId ?>;


        if (sharedNoticeId <= 0) {

            return;

        }


        const modalElement =
            document.getElementById(
                'noticeModal' + sharedNoticeId
            );


        if (!modalElement) {

            return;

        }


        /*
        | Make sure Bootstrap is loaded.
        */

        if (
            typeof bootstrap === 'undefined'
        ) {

            return;

        }


        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );


        modal.show();


        /*
        |--------------------------------------------------------------------------
        | Optional:
        | Remove ?notice=ID from browser URL after opening.
        |
        | We DON'T remove it because the URL itself should
        | remain shareable/bookmarkable.
        |--------------------------------------------------------------------------
        */

    }
);

</script>

<?php

include __DIR__ . '/../includes/footer.php';

?>

</body>

</html>
