<?php
$pageTitle = 'Edit News - Admin';
$adminPage = 'news';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('danger', 'News article not found.');
    redirect(SITE_URL . '/admin/news/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/news/edit.php?id=' . $id);
    }

    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $author  = trim($_POST['author'] ?? '');
    $status  = $_POST['status'] ?? 'published';
    $errors  = [];

    if (empty($title))   $errors[] = 'Title is required.';
    if (empty($content)) $errors[] = 'Content is required.';

    $image = $news['image'];
    if (!empty($_FILES['image']['name'])) {
        $newImage = uploadImage($_FILES['image'], 'news');
        if ($newImage) {
            if ($image) deleteImage($image, 'news');
            $image = $newImage;
        } else {
            $errors[] = 'Invalid image file.';
        }
    }

    if (empty($errors)) {
        $slug = slugify($title);
        $check = $pdo->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) $slug .= '-' . uniqid();

        $stmt = $pdo->prepare("UPDATE news SET title = ?, slug = ?, content = ?, excerpt = ?, image = ?, author = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $content, $excerpt ?: null, $image, $author ?: 'Admin', $status, $id]);

        setFlash('success', 'News article updated successfully!');
        redirect(SITE_URL . '/admin/news/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Edit News Article</h4>
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
                    <input type="text" name="title" class="form-control" required value="<?= sanitize($news['title']) ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Author</label>
                        <input type="text" name="author" class="form-control" value="<?= sanitize($news['author'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?= sanitize($news['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-control" rows="8" required><?= sanitize($news['content']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Featured Image</label>
                    <?php if ($news['image']): ?>
                        <div class="current-image mb-2">
                            <img src="<?= UPLOAD_URL ?>news/<?= sanitize($news['image']) ?>" alt="Current">
                            <small class="d-block text-muted mt-1">Current image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <small class="text-muted">Leave empty to keep current image</small>
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-lg me-1"></i> Update Article
                </button>
            </form>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
