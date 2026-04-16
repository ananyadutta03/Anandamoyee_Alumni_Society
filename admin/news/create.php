<?php
$pageTitle = 'Add News - Admin';
$adminPage = 'news';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/news/create.php');
    }

    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $author  = trim($_POST['author'] ?? '');
    $status  = $_POST['status'] ?? 'published';
    $errors  = [];

    if (empty($title))   $errors[] = 'Title is required.';
    if (empty($content)) $errors[] = 'Content is required.';

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image'], 'news');
        if (!$image) $errors[] = 'Invalid image file.';
    }

    if (empty($errors)) {
        $slug = slugify($title);
        $check = $pdo->prepare("SELECT id FROM news WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-' . uniqid();

        $stmt = $pdo->prepare("INSERT INTO news (title, slug, content, excerpt, image, author, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $excerpt ?: null, $image, $author ?: 'Admin', $status]);

        setFlash('success', 'News article created successfully!');
        redirect(SITE_URL . '/admin/news/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Add News Article</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/news/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrfField() ?>

                <div class="mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= sanitize($_POST['title'] ?? '') ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Author</label>
                        <input type="text" name="author" class="form-control" value="<?= sanitize($_POST['author'] ?? 'Admin') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Excerpt (Short Summary)</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?= sanitize($_POST['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-control" rows="8" required><?= sanitize($_POST['content'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-plus-lg me-1"></i> Create Article
                </button>
            </form>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
