<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pdo = getDBConnection();

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$event = $stmt->fetch();

if (!$event) {
    setFlash('danger', 'Event not found.');
    redirect(SITE_URL . '/pages/events.php');
}

// Get full user data
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Check if user is approved
if ($user['status'] !== 'approved') {
    setFlash('danger', 'Your account must be approved before you can register for events.');
    redirect(SITE_URL . '/pages/event_detail.php?slug=' . $slug);
}

// Check if already registered
$checkStmt = $pdo->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ?");
$checkStmt->execute([$event['id'], $_SESSION['user_id']]);
$existingReg = $checkStmt->fetch();

if ($existingReg) {
    setFlash('info', 'You have already registered for this event. Status: ' . ucfirst($existingReg['status']));
    redirect(SITE_URL . '/pages/event_detail.php?slug=' . $slug);
}

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/pages/event_register.php?slug=' . $slug);
    }

    $transactionId = trim($_POST['transaction_id'] ?? '');
    $paymentProof  = null;
    $errors = [];

    // Payment proof required if event has a fee
    if ($event['registration_fee'] > 0) {
        if (empty($_FILES['payment_proof']['name'])) {
            $errors[] = 'Payment proof screenshot is required for paid events.';
        } else {
            $paymentProof = uploadImage($_FILES['payment_proof'], 'payments');
            if (!$paymentProof) {
                $errors[] = 'Invalid image file. Allowed: jpg, jpeg, png, gif, webp (max 5MB).';
            }
        }
    } elseif (!empty($_FILES['payment_proof']['name'])) {
        $paymentProof = uploadImage($_FILES['payment_proof'], 'payments');
    }

    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, payment_proof, transaction_id) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$event['id'], $_SESSION['user_id'], $paymentProof, $transactionId ?: null]);

            setFlash('success', 'Registration submitted successfully! Please wait for admin approval.');
            redirect(SITE_URL . '/pages/event_detail.php?slug=' . $slug);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                setFlash('warning', 'You have already registered for this event.');
            } else {
                setFlash('danger', 'An error occurred. Please try again.');
            }
            redirect(SITE_URL . '/pages/event_detail.php?slug=' . $slug);
        }
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}

$pageTitle = 'Register for ' . sanitize($event['title']) . ' - ' . SITE_NAME;
$currentPage = 'events';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Event Registration</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/events.php">Events</a></li>
                <li class="breadcrumb-item active">Register</li>
            </ol>
        </nav>
    </div>
</section>

<style>
    .reg-section .card-custom { height: auto; }
    .reg-section .card-custom:hover { transform: none; }
</style>

<section class="section-padding reg-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Event Summary -->
                <div class="card card-custom mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3"><i class="bi bi-calendar-event me-2" style="color:var(--color-primary);"></i><?= sanitize($event['title']) ?></h5>
                        <div class="d-flex flex-wrap gap-3">
                            <span class="badge p-2 px-3" style="background:var(--color-primary);color:#fff;">
                                <i class="bi bi-calendar3 me-1"></i> <?= formatDate($event['event_date'], 'l, F j, Y') ?>
                            </span>
                            <?php if ($event['location']): ?>
                            <span class="badge bg-light text-dark p-2 px-3">
                                <i class="bi bi-geo-alt me-1"></i> <?= sanitize($event['location']) ?>
                            </span>
                            <?php endif; ?>
                            <span class="badge p-2 px-3 <?= $event['registration_fee'] > 0 ? 'bg-warning text-dark' : 'bg-success' ?>">
                                <i class="bi bi-tag me-1"></i>
                                <?= $event['registration_fee'] > 0 ? '&#2547;' . number_format($event['registration_fee']) : 'Free' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <?php if ($event['registration_fee'] > 0 && $event['payment_instructions']): ?>
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-1"></i> Payment Instructions</h6>
                    <p class="mb-0"><?= nl2br(sanitize($event['payment_instructions'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <div class="card card-custom">
                    <div class="card-body p-4">
                        <h5 class="mb-4"><i class="bi bi-pencil-square me-2" style="color:var(--color-primary);"></i>Registration Form</h5>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <?= csrfField() ?>

                            <!-- Auto-filled User Details (read-only) -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?= sanitize($user['name']) ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="text" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" class="form-control" value="<?= sanitize($user['student_id'] ?? 'N/A') ?>" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" value="<?= sanitize($user['department'] ?? 'N/A') ?>" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Batch</label>
                                    <input type="text" class="form-control" value="<?= sanitize($user['batch'] ?? 'N/A') ?>" disabled>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Payment Details -->
                            <?php if ($event['registration_fee'] > 0): ?>
                            <div class="mb-3">
                                <label class="form-label">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Enter your bKash/Bank transaction ID" value="<?= sanitize($_POST['transaction_id'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Proof (Screenshot) *</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/*" required id="imageInput">
                                <small class="text-muted">Upload a screenshot of your payment. Allowed: jpg, png, gif, webp (max 5MB)</small>
                                <img id="imagePreview" class="d-none mt-2" alt="Preview" style="max-width:300px;max-height:200px;border-radius:8px;">
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-check-circle me-1"></i> This is a free event. No payment required.
                            </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                                <i class="bi bi-send me-1"></i> Submit Registration
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Image preview
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
if (imageInput && imagePreview) {
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
