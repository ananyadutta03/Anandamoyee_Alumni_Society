<?php
$pageTitle = 'Create Admin - Admin';
$adminPage = 'members';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/members/create_admin.php');
    }

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors   = [];

    if (empty($name))  $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors[] = 'This email is already registered.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'approved')");
        $stmt->execute([$name, $email, $hash]);

        setFlash('success', 'New admin "' . sanitize($name) . '" created successfully!');
        redirect(SITE_URL . '/admin/members/create_admin.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}

// Get existing admins
$admins = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'admin' ORDER BY created_at ASC")->fetchAll();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Create Admin</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/members/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Members
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="row g-4">
            <!-- Create Admin Form -->
            <div class="col-lg-5">
                <div class="admin-form">
                    <h5 class="mb-3"><i class="bi bi-person-plus-fill me-2"></i>New Admin Account</h5>
                    <form method="POST" action="">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= sanitize($_POST['name'] ?? '') ?>" placeholder="Enter full name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="Enter email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min 8 characters">
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-person-plus me-1"></i> Create Admin
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Admins List -->
            <div class="col-lg-7">
                <div class="admin-table">
                    <h5 class="mb-3"><i class="bi bi-shield-lock me-2"></i>Existing Admins (<?= count($admins) ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $i => $admin): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= sanitize($admin['name']) ?></strong></td>
                                    <td><?= sanitize($admin['email']) ?></td>
                                    <td><small><?= formatDate($admin['created_at']) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
