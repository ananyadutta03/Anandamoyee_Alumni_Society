<?php
require_once __DIR__ . '/../config/init.php';

$pageTitle = 'Contact Us - ' . SITE_NAME;
$currentPage = 'contact';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/pages/contact.php');
    }

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $errors  = [];

    if (empty($name))    $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($subject)) $errors[] = 'Subject is required.';
    if (empty($message)) $errors[] = 'Message is required.';

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        setFlash('success', 'Your message has been sent successfully! We will get back to you soon.');
        redirect(SITE_URL . '/pages/contact.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active">Contact Us</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Contact Section -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <h3 class="mb-4">Send Us a Message</h3>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= sanitize($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required value="<?= sanitize($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" required value="<?= sanitize($_POST['subject'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="6" required><?= sanitize($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary-custom px-4 py-2">
                                <i class="bi bi-send me-1"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-5">
                <div class="contact-info-card">
                    <h3 class="mb-4">Get in Touch</h3>

                    <div class="info-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <h6>Address</h6>
                            <p>Kheora, Kasba, Brahmanbaria-3460</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <h6>Email</h6>
                            <p>anandamoyeean@gmail.com</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <h6>Phone</h6>
                            <p>+880 1611 759094</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <h6>Office Hours</h6>
                            <p>Sundayday - Thursday: 10:00 AM - 5:00 PM</p>
                        </div>
                    </div>

                    <hr>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
