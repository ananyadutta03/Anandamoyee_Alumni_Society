<?php
$pageTitle = 'Add Committee Member - Admin';
$adminPage = 'committee';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/committee/create.php');
    }

    $name        = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $bio         = trim($_POST['bio'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $linkedin    = trim($_POST['linkedin'] ?? '');
    $sortOrder   = intval($_POST['sort_order'] ?? 0);
    $isActive    = isset($_POST['is_active']) ? 1 : 0;
    $errors      = [];

    if (empty($name))        $errors[] = 'Name is required.';
    if (empty($designation)) $errors[] = 'Designation is required.';

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image'], 'committee');
        if (!$image) $errors[] = 'Invalid image file.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO executive_committee (name, designation, image, bio, email, phone, linkedin, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $designation, $image, $bio ?: null, $email ?: null, $phone ?: null, $linkedin ?: null, $sortOrder, $isActive]);

        setFlash('success', 'Committee member added successfully!');
        redirect(SITE_URL . '/admin/committee/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Add Committee Member</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/committee/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrfField() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Designation *</label>
                        <input type="text" name="designation" class="form-control" required value="<?= sanitize($_POST['designation'] ?? '') ?>" placeholder="e.g., President">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= sanitize($_POST['bio'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" name="linkedin" class="form-control" value="<?= sanitize($_POST['linkedin'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= sanitize($_POST['sort_order'] ?? '0') ?>" min="0">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-plus-lg me-1"></i> Add Member
                </button>
            </form>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
