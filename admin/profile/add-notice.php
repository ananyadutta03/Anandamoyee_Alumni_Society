```php
<?php
/**
 * Admin - Publish Notice
 * File: admin/profile/add-notice.php
 */

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/auth/login.php');
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    redirect(SITE_URL . '/index.php');
}

$pdo = getDBConnection();

$pageTitle = "Publish Notice";
$userPage = 'notices';

/*
|--------------------------------------------------------------------------
| Initialize Variables
|--------------------------------------------------------------------------
*/

$success = '';
$error = '';
$messageType = '';

$title = '';
$content = '';
$status = 'published';


/*
|--------------------------------------------------------------------------
| Flash Message Helper
|--------------------------------------------------------------------------
*/

function setNoticeMessage(string $message, string $type = 'error'): void
{
    $_SESSION['notice_message'] = $message;
    $_SESSION['notice_message_type'] = $type;
}


/*
|--------------------------------------------------------------------------
| Handle Delete Notice
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice'])) {

    $noticeId = (int) ($_POST['notice_id'] ?? 0);

    if ($noticeId <= 0) {

        setNoticeMessage(
            "Invalid notice ID.",
            "error"
        );

    } else {

        try {

            $stmt = $pdo->prepare("
                DELETE FROM notices
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $noticeId
            ]);

            if ($stmt->rowCount() > 0) {

                setNoticeMessage(
                    "Notice deleted successfully!",
                    "deleted"
                );

            } else {

                setNoticeMessage(
                    "Notice not found or already deleted.",
                    "error"
                );
            }

        } catch (PDOException $e) {

            setNoticeMessage(
                "Unable to delete notice.",
                "error"
            );
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


/*
|--------------------------------------------------------------------------
| Handle Publish / Draft
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_notice'])) {

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $status = $_POST['status'] ?? 'published';

    if (!in_array($status, ['published', 'draft'], true)) {
        $status = 'published';
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($title === '') {

        setNoticeMessage(
            "Please enter a notice title.",
            "error"
        );

    } elseif (mb_strlen($title) > 255) {

        setNoticeMessage(
            "Notice title cannot exceed 255 characters.",
            "error"
        );

    } elseif ($content === '') {

        setNoticeMessage(
            "Please enter the notice content.",
            "error"
        );

    } else {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO notices (
                    title,
                    content,
                    status
                )
                VALUES (
                    :title,
                    :content,
                    :status
                )
            ");

            $stmt->execute([
                ':title'   => $title,
                ':content' => $content,
                ':status'  => $status
            ]);


            if ($status === 'published') {

                setNoticeMessage(
                    "Notice published successfully!",
                    "success"
                );

            } else {

                setNoticeMessage(
                    "Notice saved as draft successfully!",
                    "success"
                );
            }

        } catch (PDOException $e) {

            setNoticeMessage(
                "Something went wrong while saving the notice.",
                "error"
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


/*
|--------------------------------------------------------------------------
| Read Flash Message
|--------------------------------------------------------------------------
*/

if (!empty($_SESSION['notice_message'])) {

    $success = $_SESSION['notice_message'];

    $messageType = $_SESSION['notice_message_type'] ?? 'error';

    unset($_SESSION['notice_message']);
    unset($_SESSION['notice_message_type']);
}


/*
|--------------------------------------------------------------------------
| Get Existing Notices
|--------------------------------------------------------------------------
*/

try {

    $noticeStmt = $pdo->query("
        SELECT
            id,
            title,
            content,
            status,
            created_at
        FROM notices
        ORDER BY created_at DESC
    ");

    $allNotices = $noticeStmt->fetchAll();

} catch (PDOException $e) {

    $allNotices = [];

    if (!$success) {

        $success = "Unable to load notices.";
        $messageType = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?> | Admin
    </title>


    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;500;700&display=swap"
        rel="stylesheet"
    >


    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- Main CSS -->
    <link
        href="<?= SITE_URL ?>/assets/css/style.css"
        rel="stylesheet"
    >


    <!-- Admin CSS -->
    <link
        href="<?= SITE_URL ?>/assets/css/admin.css"
        rel="stylesheet"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | Notice Page
        |--------------------------------------------------------------------------
        */

        .notice-page-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }


        .notice-card {
            background: #ffffff;
            border: 0;
            border-radius: 18px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07);
            overflow: hidden;
            transform: none !important;
    transition: none !important;
        }

        .notice-card:hover {
    transform: none !important;
}


        .notice-header {
            padding: 24px 28px;
            border-bottom: 1px solid #eeeeee;
            background: #ffffff;
        }


        .notice-header h4 {
            margin: 0;
            font-weight: 700;
            color: #212529;
        }


        .notice-header p {
            margin: 6px 0 0;
            color: #6c757d;
            font-size: 14px;
        }


        .notice-header-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f5ee;
            color: #198754;
            border-radius: 12px;
            font-size: 22px;
            flex-shrink: 0;
        }


        .notice-body {
            padding: 30px;
        }


        /*
        |--------------------------------------------------------------------------
        | Form
        |--------------------------------------------------------------------------
        */

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #343a40;
        }


        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px 14px;
            border: 1px solid #dee2e6;
        }


        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.10);
        }


        #content {
            min-height: 250px;
            resize: vertical;
        }


        /*
        |--------------------------------------------------------------------------
        | Buttons
        |--------------------------------------------------------------------------
        */

        .notice-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }


        .notice-actions .btn {
            border-radius: 10px;
            padding: 11px 20px;
            font-weight: 600;
        }


        .btn-publish {
            background: #198754;
            border-color: #198754;
            color: #ffffff;
        }


        .btn-publish:hover {
            background: #157347;
            border-color: #157347;
            color: #ffffff;
        }


        .btn-draft {
            background: #6c757d;
            border-color: #6c757d;
            color: #ffffff;
        }


        .btn-draft:hover {
            background: #5c636a;
            border-color: #5c636a;
            color: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | Alert Messages
        |--------------------------------------------------------------------------
        */

        .notice-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
        }


        .notice-alert-success {
            background: #d1e7dd;
            border: 1px solid #a3cfbb;
            color: #0f5132;
        }


        .notice-alert-success i {
            color: #198754;
            font-size: 18px;
        }


        .notice-alert-deleted {
            background: #f8d7da;
            border: 1px solid #f1aeb5;
            color: #842029;
        }


        .notice-alert-deleted i {
            color: #dc3545;
            font-size: 18px;
        }


        .notice-alert-error {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            color: #664d03;
        }


        .notice-alert-error i {
            color: #ffc107;
            font-size: 18px;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Notices
        |--------------------------------------------------------------------------
        */

        .existing-notices {
            border-top: 1px solid #e9ecef;
            padding-top: 30px;
            margin-top: 35px;
        }


        .existing-notices h5 {
            font-weight: 700;
            color: #212529;
        }


        .notice-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }


        .notice-list-item {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 14px;
            padding: 16px;
            transition: all 0.25s ease;
        }


        .notice-list-item:hover {
            border-color: #ced4da;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }


        .notice-list-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #e8f5ee;
            color: #198754;
            font-size: 20px;
        }


        .notice-list-content {
            flex: 1;
            min-width: 0;
        }


        .notice-list-title {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            word-break: break-word;
        }


        .notice-list-description {
            margin: 7px 0 5px;
            color: #6c757d;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }


        .notice-list-date {
            color: #8a8f98;
            font-size: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        .notice-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }


        .status-published {
            background: #d1e7dd;
            color: #146c43;
        }


        .status-draft {
            background: #fff3cd;
            color: #997404;
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Button
        |--------------------------------------------------------------------------
        */

        .notice-list-action {
            flex-shrink: 0;
        }


        .notice-list-action form {
            margin: 0;
        }


        .btn-delete-notice {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #ffffff;
            color: #dc3545;
            border: 1px solid #dc3545;
            transition: all 0.25s ease;
        }


        .btn-delete-notice:hover {
            background: #dc3545;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(220, 53, 69, 0.20);
        }


        /*
        |--------------------------------------------------------------------------
        | No Notices
        |--------------------------------------------------------------------------
        */

        .no-notices-box {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border: 1px dashed #ced4da;
            border-radius: 14px;
        }


        .no-notices-box i {
            display: block;
            font-size: 35px;
            color: #adb5bd;
            margin-bottom: 10px;
        }


        .no-notices-box h6 {
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .notice-header {
                padding: 20px;
            }

            .notice-body {
                padding: 20px;
            }

            .notice-list-item {
                align-items: flex-start;
                padding: 14px;
                gap: 12px;
            }

            .notice-list-icon {
                width: 42px;
                height: 42px;
                min-width: 42px;
                font-size: 17px;
            }

            .notice-list-title {
                font-size: 14px;
            }

            .notice-list-description {
                font-size: 13px;
            }

            .btn-delete-notice {
                width: 38px;
                height: 38px;
            }
        }


        @media (max-width: 576px) {

            .notice-header {
                padding: 18px;
            }

            .notice-body {
                padding: 18px;
            }

            .notice-header-icon {
                width: 42px;
                height: 42px;
                font-size: 18px;
            }

            .notice-header h4 {
                font-size: 18px;
            }

            .notice-header p {
                font-size: 13px;
            }

            .notice-actions {
                flex-direction: column;
            }

            .notice-actions .btn {
                width: 100%;
            }

            .notice-list-item {
                flex-wrap: wrap;
            }

            .notice-list-content {
                width: calc(100% - 60px);
            }

            .notice-list-action {
                width: 100%;
                display: flex;
                justify-content: flex-end;
                margin-top: 5px;
            }
        }

    </style>

</head>


<body>

<div class="admin-wrapper">

    <!-- Sidebar Overlay -->
    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>


    <!-- Admin Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <!-- Admin Content -->
    <div class="admin-content">


        <!-- Topbar -->
        <div class="admin-topbar">

            <button
                class="sidebar-toggle"
                id="sidebarToggle"
                type="button"
            >
                <i class="bi bi-list"></i>
            </button>


            <h4 class="page-title">
                Publish Notice
            </h4>


            <div class="user-info">

                <a
                    href="<?= SITE_URL ?>/admin/index.php"
                    class="btn btn-sm btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Dashboard
                </a>

            </div>

        </div>


        <!-- Main Content -->
        <div class="admin-main">

            <div class="notice-page-wrapper">

                <div class="notice-card">


                    <!-- Header -->
                    <div class="notice-header">

                        <div class="d-flex align-items-center gap-3">

                            <div class="notice-header-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>

                            <div>

                                <h4>
                                    Publish Notice
                                </h4>

                                <p>
                                    Create and publish a notice for your alumni members.
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- Body -->
                    <div class="notice-body">


                        <!-- Flash Message -->
                        <?php if ($success): ?>

                            <div
                                class="notice-alert notice-alert-<?= htmlspecialchars($messageType) ?>"
                            >

                                <?php if ($messageType === 'success'): ?>

                                    <i class="bi bi-check-circle-fill"></i>

                                <?php elseif ($messageType === 'deleted'): ?>

                                    <i class="bi bi-trash3-fill"></i>

                                <?php else: ?>

                                    <i class="bi bi-exclamation-triangle-fill"></i>

                                <?php endif; ?>

                                <span>
                                    <?= htmlspecialchars($success) ?>
                                </span>

                            </div>

                        <?php endif; ?>


                        <!-- Publish Form -->
                        <form
                            method="POST"
                            action=""
                        >


                            <!-- Notice Title -->
                            <div class="mb-4">

                                <label
                                    for="title"
                                    class="form-label"
                                >
                                    Notice Title
                                </label>

                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    class="form-control"
                                    placeholder="Enter notice title"
                                    maxlength="255"
                                    required
                                >

                            </div>


                            <!-- Notice Content -->
                            <div class="mb-4">

                                <label
                                    for="content"
                                    class="form-label"
                                >
                                    Notice Content
                                </label>

                                <textarea
                                    id="content"
                                    name="content"
                                    class="form-control"
                                    placeholder="Write your notice here..."
                                    required
                                ></textarea>

                                <div class="form-text">
                                    Write the complete information that should be shown on the home page.
                                </div>

                            </div>


                            <!-- Status -->
                            <div class="mb-4">

                                <label
                                    for="status"
                                    class="form-label"
                                >
                                    Notice Status
                                </label>

                                <select
                                    name="status"
                                    id="status"
                                    class="form-select"
                                >

                                    <option value="published">
                                        Published
                                    </option>

                                    <option value="draft">
                                        Save as Draft
                                    </option>

                                </select>

                            </div>


                            <!-- Actions -->
                            <div class="notice-actions">

                                <button
                                    type="submit"
                                    name="submit"
                                    value="published"
                                    class="btn btn-publish"
                                    onclick="
                                        document.getElementById('status').value='published';
                                    "
                                >
                                    <i class="bi bi-megaphone-fill me-1"></i>
                                    Publish Notice
                                </button>


                                <button
                                    type="submit"
                                    name="submit"
                                    value="draft"
                                    class="btn btn-draft"
                                    onclick="
                                        document.getElementById('status').value='draft';
                                    "
                                >
                                    <i class="bi bi-file-earmark me-1"></i>
                                    Save Draft
                                </button>


                                <a
                                    href="<?= SITE_URL ?>/admin/index.php"
                                    class="btn btn-outline-secondary"
                                >
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancel
                                </a>

                            </div>

                        </form>


                        <!-- Existing Notices -->
                        <div class="existing-notices">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div>

                                    <h5 class="mb-1">
                                        <i class="bi bi-list-ul me-2"></i>
                                        Published & Draft Notices
                                    </h5>

                                    <p class="text-muted small mb-0">
                                        Manage your previously created notices.
                                    </p>

                                </div>

                            </div>


                            <?php if (empty($allNotices)): ?>

                                <div class="no-notices-box">

                                    <i class="bi bi-megaphone"></i>

                                    <h6>
                                        No notices found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Your published and draft notices will appear here.
                                    </p>

                                </div>

                            <?php else: ?>

                                <div class="notice-list">

                                    <?php foreach ($allNotices as $notice): ?>

                                        <div class="notice-list-item">


                                            <!-- Icon -->
                                            <div class="notice-list-icon">

                                                <?php if ($notice['status'] === 'published'): ?>

                                                    <i class="bi bi-megaphone-fill"></i>

                                                <?php else: ?>

                                                    <i class="bi bi-file-earmark-text-fill"></i>

                                                <?php endif; ?>

                                            </div>


                                            <!-- Content -->
                                            <div class="notice-list-content">

                                                <div class="d-flex align-items-center gap-2 flex-wrap">

                                                    <h6 class="notice-list-title mb-0">
                                                        <?= htmlspecialchars($notice['title']) ?>
                                                    </h6>


                                                    <?php if ($notice['status'] === 'published'): ?>

                                                        <span class="notice-status status-published">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            Published
                                                        </span>

                                                    <?php else: ?>

                                                        <span class="notice-status status-draft">
                                                            <i class="bi bi-file-earmark"></i>
                                                            Draft
                                                        </span>

                                                    <?php endif; ?>

                                                </div>


                                                <p class="notice-list-description">

                                                    <?= htmlspecialchars(
                                                        mb_strimwidth(
                                                            strip_tags($notice['content']),
                                                            0,
                                                            180,
                                                            '...'
                                                        )
                                                    ) ?>

                                                </p>


                                                <div class="notice-list-date">

                                                    <i class="bi bi-calendar3 me-1"></i>

                                                    <?= formatDate($notice['created_at']) ?>

                                                </div>

                                            </div>


                                            <!-- Delete -->
                                            <div class="notice-list-action">

                                                <form
                                                    method="POST"
                                                    action=""
                                                    
                                        
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="notice_id"
                                                        value="<?= (int) $notice['id'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        name="delete_notice"
                                                        value="1"
                                                        class="btn btn-delete-notice"
                                                        title="Delete Notice"
                                                    >
                                                        <i class="bi bi-trash3"></i>
                                                    </button>

                                                </form>

                                            </div>


                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- Admin JS -->
<script
    src="<?= SITE_URL ?>/assets/js/admin.js"
></script>

</body>

</html>
```
