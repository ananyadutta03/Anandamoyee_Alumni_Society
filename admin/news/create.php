<?php
$pageTitle = 'Add Alumni Biography - Admin';
$adminPage = 'news';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/news/create.php');
    }

    $name        = trim($_POST['title'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $status      = $_POST['status'] ?? 'published';
    $errors      = [];

    if (empty($name))    $errors[] = 'Alumni name is required.';
    if (empty($content)) $errors[] = 'Biography is required.';

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image'], 'news');
        if (!$image) $errors[] = 'Invalid image file.';
    }

    if (empty($errors)) {
        $slug = slugify($name);
        $check = $pdo->prepare("SELECT id FROM news WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-' . uniqid();

        $stmt = $pdo->prepare("INSERT INTO news (title, designation, slug, content, image, author, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $designation ?: null, $slug, $content, $image, 'Admin', $status]);

        setFlash('success', 'Biography added successfully!');
        redirect(SITE_URL . '/admin/news/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #bioEditor { min-height: 250px; background: #fff; }
    .ql-editor a { color: #0d6efd !important; text-decoration: underline; }
    .ql-editor strong { font-weight: 700; }
</style>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Add Alumni Biography</h4>
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
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($_POST['title'] ?? '') ?>" placeholder="e.g., Samiul Sumon">
                        <small class="text-muted">URL will be generated from this name.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control" value="<?= sanitize($_POST['designation'] ?? '') ?>" placeholder="e.g., Lecturer, AIUB">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" style="max-width: 200px;">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <div class="mb-3">
                    <label class="form-label">Biography *</label>
                    <div id="bioEditor"><?= $_POST['content'] ?? '' ?></div>
                    <textarea name="content" id="bioContent" style="display:none;"><?= sanitize($_POST['content'] ?? '') ?></textarea>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Use toolbar to bold text or add links. Links will appear blue automatically.</small>
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-plus-lg me-1"></i> Add Biography
                </button>
            </form>
        </div>

<!-- Quill JS -->
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
        },
        placeholder: 'Write a short biography about this alumni...'
    });

    // Sync editor content to hidden textarea on submit
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
