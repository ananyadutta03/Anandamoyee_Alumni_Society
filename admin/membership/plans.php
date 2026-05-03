<?php
$pageTitle = 'Plan Settings - Admin';
$adminPage = 'membership';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/membership/plans.php');
    }

    $planId       = intval($_POST['plan_id'] ?? 0);
    $price        = floatval($_POST['price'] ?? 0);
    $description  = trim($_POST['description'] ?? '');
    $features     = trim($_POST['features'] ?? '');
    $instructions = trim($_POST['payment_instructions'] ?? '');

    $stmt = $pdo->prepare("UPDATE membership_plans SET price = ?, description = ?, features = ?, payment_instructions = ? WHERE id = ?");
    $stmt->execute([$price, $description, $features, $instructions ?: null, $planId]);

    setFlash('success', 'Plan updated successfully!');
    redirect(SITE_URL . '/admin/membership/plans.php');
}

$plans = $pdo->query("SELECT * FROM membership_plans ORDER BY price ASC")->fetchAll();
$planIcons = ['general' => 'bi-person-check', 'executive' => 'bi-star', 'lifetime' => 'bi-gem'];
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Plan Settings</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/membership/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Membership
            </a>
        </div>
    </div>

    <div class="admin-main">
        <div class="row g-4">
            <?php foreach ($plans as $plan): ?>
            <div class="col-lg-4">
                <div class="card card-custom">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <i class="bi <?= $planIcons[$plan['slug']] ?? 'bi-person' ?>" style="font-size:2rem;color:var(--color-primary);"></i>
                            <h5 class="mt-2"><?= sanitize($plan['name']) ?></h5>
                        </div>

                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Price (BDT)</label>
                                <input type="number" name="price" class="form-control" value="<?= $plan['price'] ?>" min="0" step="0.01" <?= $plan['slug'] === 'general' ? 'readonly' : '' ?>>
                                <?php if ($plan['slug'] === 'general'): ?>
                                    <small class="text-muted">General is always free</small>
                                <?php elseif ($plan['slug'] === 'executive'): ?>
                                    <small class="text-muted">Per year (after 1st year)</small>
                                <?php else: ?>
                                    <small class="text-muted">One-time payment</small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= sanitize($plan['description']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Features (one per line)</label>
                                <textarea name="features" class="form-control" rows="4"><?= sanitize($plan['features'] ?? '') ?></textarea>
                            </div>

                            <?php if ($plan['price'] > 0): ?>
                            <div class="mb-3">
                                <label class="form-label">Payment Instructions</label>
                                <textarea name="payment_instructions" class="form-control" rows="3" placeholder="bKash/Nagad number, bank details..."><?= sanitize($plan['payment_instructions'] ?? '') ?></textarea>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="payment_instructions" value="">
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary-custom w-100">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
