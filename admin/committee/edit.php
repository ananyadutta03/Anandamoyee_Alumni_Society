<?php
$pageTitle = 'Edit Committee Member - Admin';
$adminPage = 'committee';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM executive_committee WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    setFlash('danger', 'Committee member not found.');
    redirect(SITE_URL . '/admin/committee/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/committee/edit.php?id=' . $id);
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

    $image = $member['image'];
    if (!empty($_FILES['image']['name'])) {
        $newImage = uploadImage($_FILES['image'], 'committee');
        if ($newImage) {
            if ($image) deleteImage($image, 'committee');
            $image = $newImage;
        } else {
            $errors[] = 'Invalid image file.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE executive_committee SET name = ?, designation = ?, image = ?, bio = ?, email = ?, phone = ?, linkedin = ?, sort_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $designation, $image, $bio ?: null, $email ?: null, $phone ?: null, $linkedin ?: null, $sortOrder, $isActive, $id]);

        setFlash('success', 'Committee member updated successfully!');
        redirect(SITE_URL . '/admin/committee/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Edit Committee Member</h4>
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
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($member['name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Designation *</label>
                        <input type="text" name="designation" class="form-control" required value="<?= sanitize($member['designation']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= sanitize($member['bio'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= sanitize($member['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= sanitize($member['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" name="linkedin" class="form-control" value="<?= sanitize($member['linkedin'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $member['sort_order'] ?>" min="0">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $member['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <?php if ($member['image']): ?>
                        <div class="current-image mb-2">
                            <img src="<?= UPLOAD_URL ?>committee/<?= sanitize($member['image']) ?>" alt="Current" style="border-radius: 50%;">
                            <small class="d-block text-muted mt-1">Current photo</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <small class="text-muted">Leave empty to keep current photo</small>
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-lg me-1"></i> Update Member
                </button>
            </form>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
