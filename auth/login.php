<?php
require_once __DIR__ . '/../config/init.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/auth/login.php');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('danger', 'Please fill in all fields.');
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'pending') {
                setFlash('warning', 'Your account is pending approval. Please wait for admin confirmation.');
            } elseif ($user['status'] === 'rejected') {
                setFlash('danger', 'Your account has been rejected. Please contact support.');
            } else {
                // Login successful
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];

                setFlash('success', 'Welcome back, ' . $user['name'] . '!');

                if ($user['role'] === 'admin') {
                    redirect(SITE_URL . '/admin/index.php');
                } else {
                    redirect(SITE_URL . '/index.php');
                }
            }
        } else {
            setFlash('danger', 'Invalid email or password.');
        }
    }
}

$pageTitle = 'Login - ' . SITE_NAME;
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
    <div class="auth-card">
        <div class="auth-logo">
            <a href="<?= SITE_URL ?>/index.php">
                <i class="bi bi-mortarboard-fill" style="font-size: 3rem; color: var(--color-primary);"></i>
            </a>
            <h4><?= SITE_NAME ?></h4>
            <p>Login to your account</p>
        </div>

        <form method="POST" action="">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?= sanitize($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
    <label class="form-label">Password</label>

    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-lock"></i>
        </span>

        <input
            type="password"
            name="password"
            id="loginPassword"
            class="form-control"
            placeholder="Enter your password"
            required
        >

        <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="togglePassword('loginPassword', 'loginPasswordIcon')"
            tabindex="-1"
            aria-label="Show or hide password"
        >
            <i class="bi bi-eye" id="loginPasswordIcon"></i>
        </button>
    </div>
</div>

            <button type="submit" class="btn btn-primary-custom w-100 py-2 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>

            <div class="text-center">
                <p class="text-muted mb-0">Don't have an account?
                    <a href="<?= SITE_URL ?>/auth/register.php" style="color: var(--color-primary-dark); font-weight: 600;">Register Here</a>
                </p>

                <p class="text-muted mb-0">
                    <a href="<?= SITE_URL ?>/" style="color: var(--color-primary-dark); font-weight: 600;">Forget Password ?</a>
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
    // Auto dismiss flash messages
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

    // Auto dismiss flash messages
    setTimeout(function() {
        document.querySelectorAll('.flash-message .alert').forEach(function(el) {
            el.classList.remove('show');

            setTimeout(() => el.remove(), 300);
        });
    }, 5000);
</script>
</body>
</html>
