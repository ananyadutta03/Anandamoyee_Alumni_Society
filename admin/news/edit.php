<?php
$pageTitle = 'Edit Alumni Biography - Admin';
$adminPage = 'news';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('danger', 'Biography not found.');
    redirect(SITE_URL . '/admin/news/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/news/edit.php?id=' . $id);
    }

    $name        = trim($_POST['title'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $status      = $_POST['status'] ?? 'published';
    $errors      = [];

    if (empty($name))    $errors[] = 'Alumni name is required.';
    if (empty($content)) $errors[] = 'Biography is required.';

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
        $slug = slugify($name);
        $check = $pdo->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) $slug .= '-' . uniqid();

        $stmt = $pdo->prepare("UPDATE news SET title = ?, designation = ?, slug = ?, content = ?, image = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $designation ?: null, $slug, $content, $image, $status, $id]);

        setFlash('success', 'Biography updated successfully!');
        redirect(SITE_URL . '/admin/news/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #bioEditor { min-height: 250px; background: #fff; }
    .ql-editor a { color: #0d6efd !important; text-decoration: underline; }
    .ql-editor strong { font-weight: 700; }
</style>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Edit Alumni Biography</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/news/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-form">
            <form method="POST" action="" enctype="multipart/form-data" id="bioForm">
                <?= csrfField() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Alumni Name *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($news['title']) ?>">
                        <small class="text-muted">URL slug: <code><?= sanitize($news['slug']) ?></code></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control" value="<?= sanitize($news['designation'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" style="max-width: 200px;">
                        <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <?php if ($news['image']): ?>
                        <div class="current-image mb-2">
                            <img src="<?= UPLOAD_URL ?>news/<?= sanitize($news['image']) ?>" alt="Current" style="max-height:120px;border-radius:8px;">
                            <small class="d-block text-muted mt-1">Current photo</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <small class="text-muted">Leave empty to keep current photo</small>
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <div class="mb-3">
                    <label class="form-label">Biography *</label>
                    <div id="bioEditor"></div>
                    <textarea name="content" id="bioContent" style="display:none;"></textarea>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Use toolbar to bold text or add links. Links will appear blue automatically.</small>
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-lg me-1"></i> Update Biography
                </button>
            </form>
        </div>

<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    const quill = new Quill('#bioEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                ['link'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    // Load existing content
    const existingContent = <?= json_encode($news['content']) ?>;
    quill.root.innerHTML = existingContent;

    document.getElementById('bioForm').addEventListener('submit', function(e) {
        const html = quill.root.innerHTML;
        document.getElementById('bioContent').value = html;
        if (quill.getText().trim().length === 0) {
            e.preventDefault();
            alert('Please write the biography.');
        }
    });
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
