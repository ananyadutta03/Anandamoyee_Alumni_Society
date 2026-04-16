<?php
$pageTitle = 'Add Event - Admin';
$adminPage = 'events';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/events/create.php');
    }

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate   = $_POST['event_date'] ?? '';
    $location    = trim($_POST['location'] ?? '');
    $status      = $_POST['status'] ?? 'published';
    $registrationFee     = floatval($_POST['registration_fee'] ?? 0);
    $paymentInstructions = trim($_POST['payment_instructions'] ?? '');
    $errors      = [];

    if (empty($title))       $errors[] = 'Title is required.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (empty($eventDate))   $errors[] = 'Event date is required.';

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image'], 'events');
        if (!$image) $errors[] = 'Invalid image file. Allowed: jpg, jpeg, png, gif, webp (max 5MB).';
    }

    if (empty($errors)) {
        $slug = slugify($title);
        // Ensure unique slug
        $check = $pdo->prepare("SELECT id FROM events WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $slug .= '-' . uniqid();
        }

        $stmt = $pdo->prepare("INSERT INTO events (title, slug, description, image, event_date, location, status, registration_fee, payment_instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $image, $eventDate, $location ?: null, $status, $registrationFee, $paymentInstructions ?: null]);

        setFlash('success', 'Event created successfully!');
        redirect(SITE_URL . '/admin/events/index.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Add New Event</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/events/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrfField() ?>

                <div class="mb-3">
                    <label class="form-label">Event Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= sanitize($_POST['title'] ?? '') ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Event Date *</label>
                        <input type="date" name="event_date" class="form-control" required value="<?= sanitize($_POST['event_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= sanitize($_POST['location'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="6" required><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Registration Fee (BDT)</label>
                        <input type="number" name="registration_fee" class="form-control" min="0" step="0.01" value="<?= sanitize($_POST['registration_fee'] ?? '0') ?>">
                        <small class="text-muted">Set 0 for free events</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Instructions</label>
                    <textarea name="payment_instructions" class="form-control" rows="3" placeholder="e.g., bKash: 01XXXXXXXXX (Personal)&#10;Bank: ABC Bank, A/C: 123456&#10;Reference: Use your Student ID"><?= sanitize($_POST['payment_instructions'] ?? '') ?></textarea>
                    <small class="text-muted">Shown to users during registration. Leave empty if no payment needed.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Event Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <img id="imagePreview" class="img-preview d-none mt-2" alt="Preview">
                </div>

                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-plus-lg me-1"></i> Create Event
                </button>
            </form>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
