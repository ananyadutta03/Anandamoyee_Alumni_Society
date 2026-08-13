<?php
require_once __DIR__ . '/../config/init.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/auth/register.php');
    }

    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $batch      = trim($_POST['batch'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $errors     = [];

    if (empty($name))     $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($batch))   $errors[] = 'Batch is required.';
    if (empty($phone))   $errors[] = 'Phone number is required.';

    if (empty($errors)) {
        $pdo = getDBConnection();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, batch, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
    $name,
    $email,
    $hash,
    $batch,
    $phone
]);


$user_id = $pdo->lastInsertId();


// Send email to all admins

require_once __DIR__ . '/../includes/email_helper.php';


$adminStmt = $pdo->prepare("
    SELECT email 
    FROM users 
    WHERE role='admin'
    AND status='approved'
");


$adminStmt->execute();


while($admin = $adminStmt->fetch())
{


$emailBody = "

<h2>New User Registration</h2>

<p>A new alumni member registered.</p>

<table border='1' cellpadding='8'>

<tr>
<td>Name</td>
<td>$name</td>
</tr>


<tr>
<td>Email</td>
<td>$email</td>
</tr>


<tr>
<td>Batch</td>
<td>$batch</td>
</tr>


<tr>
<td>Phone</td>
<td>$phone</td>
</tr>


</table>


<p>Please login to admin panel and approve.</p>


";


sendEmail(
    $admin['email'],
    "New Alumni Registration",
    $emailBody
);


}



setFlash(
'success',
'Registration successful! Your account is pending admin approval.'
);


redirect(
SITE_URL . '/auth/login.php'
);
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}

$pageTitle = 'Register - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Flash Messages -->
<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 550px;">
        <div class="auth-logo">
            <a href="<?= SITE_URL ?>/index.php">
                <i class="bi bi-mortarboard-fill" style="font-size: 3rem; color: var(--color-primary);"></i>
            </a>
            <h4><?= SITE_NAME ?></h4>
            <p>Create your alumni account</p>
        </div>

        <form method="POST" action="">
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= sanitize($_POST['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>

            <div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Password *</label>

        <div class="input-group">
            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                required
                minlength="8"
                placeholder="Min 8 characters"
            >

            <button
                type="button"
                class="btn btn-outline-secondary"
                onclick="togglePassword('password', 'passwordIcon')"
                tabindex="-1"
            >
                <i class="bi bi-eye" id="passwordIcon"></i>
            </button>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Confirm Password *</label>

        <div class="input-group">
            <input
                type="password"
                name="confirm_password"
                id="confirm_password"
                class="form-control"
                required
            >

            <button
                type="button"
                class="btn btn-outline-secondary"
                onclick="togglePassword('confirm_password', 'confirmPasswordIcon')"
                tabindex="-1"
            >
                <i class="bi bi-eye" id="confirmPasswordIcon"></i>
            </button>
        </div>
    </div>
</div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Batch *</label>
                    <input type="text" name="batch" class="form-control" required placeholder="e.g., 2010" value="<?= sanitize($_POST['batch'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+880..." value="<?= sanitize($_POST['phone'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom w-100 py-2 mb-3">
                <i class="bi bi-person-plus me-1"></i> Register
            </button>

            <div class="text-center">
                <p class="text-muted mb-0">Already have an account?
                    <a href="<?= SITE_URL ?>/auth/login.php" style="color: var(--color-primary-dark); font-weight: 600;">Login Here</a>
                </p>
                <a href="<?= SITE_URL ?>/index.php" class="text-muted small mt-2 d-inline-block">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(function() {
        document.querySelectorAll('.flash-message .alert').forEach(function(el) {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 300);
        });
    }, 5000);

    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }
    }

    setTimeout(function() {
        document.querySelectorAll('.flash-message .alert').forEach(function(el) {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 300);
        });
    }, 5000);
</script>
</body>
</html>
