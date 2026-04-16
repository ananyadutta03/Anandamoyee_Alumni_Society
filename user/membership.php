<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'Membership Plans - ' . SITE_NAME;
$userPage = 'membership';

$pdo = getDBConnection();

// Get current user
$stmt = $pdo->prepare("SELECT membership_plan, plan_expires_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$currentPlan = $user['membership_plan'] ?? 'free';

// Get all plans
$plans = $pdo->query("SELECT * FROM membership_plans WHERE is_active = 1 ORDER BY price ASC")->fetchAll();

// Handle plan switch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/user/membership.php');
    }

    $newPlan = $_POST['plan'] ?? '';

    if ($newPlan === 'free') {
        $stmt = $pdo->prepare("UPDATE users SET membership_plan = 'free', plan_expires_at = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        setFlash('success', 'You have switched to the Free plan.');
    } elseif ($newPlan === 'premium') {
        $expiresAt = date('Y-m-d', strtotime('+12 months'));
        $stmt = $pdo->prepare("UPDATE users SET membership_plan = 'premium', plan_expires_at = ? WHERE id = ?");
        $stmt->execute([$expiresAt, $_SESSION['user_id']]);
        setFlash('success', 'You have upgraded to Premium! Your plan is active until ' . formatDate($expiresAt) . '.');
    }

    redirect(SITE_URL . '/user/membership.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .plan-card {
            border: 2px solid var(--color-border);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            background: var(--color-white);
        }
        .plan-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }
        .plan-card.active {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.15);
        }
        .plan-card.featured {
            border-color: var(--color-accent);
        }
        .plan-card .plan-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            padding: 4px 20px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .plan-card .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-dark);
            font-family: var(--font-heading);
        }
        .plan-card .plan-price small {
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--color-text-muted);
        }
        .plan-features {
            list-style: none;
            padding: 0;
            text-align: left;
            margin: 20px 0;
        }
        .plan-features li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
        .plan-features li i {
            margin-right: 8px;
            width: 18px;
        }
        .plan-features li .bi-check-circle-fill { color: #28a745; }
        .plan-features li .bi-x-circle-fill { color: #dc3545; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include __DIR__ . '/includes/user_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <h4 class="page-title">Membership Plans</h4>
            <div class="user-info">
                <a href="<?= SITE_URL ?>/user/dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="admin-main">
            <!-- Current Plan Info -->
            <div class="alert <?= $currentPlan === 'premium' ? 'alert-info' : 'alert-secondary' ?> d-flex align-items-center mb-4">
                <i class="bi bi-star-fill me-2"></i>
                <div>
                    Your current plan: <strong><?= ucfirst($currentPlan) ?></strong>
                    <?php if ($currentPlan === 'premium' && $user['plan_expires_at']): ?>
                        &bull; Expires: <strong><?= formatDate($user['plan_expires_at']) ?></strong>
                    <?php elseif ($currentPlan === 'free'): ?>
                        &bull; Upgrade to Premium to unlock all features!
                    <?php endif; ?>
                </div>
            </div>

            <!-- Plan Cards -->
            <div class="row g-4 justify-content-center">
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-6 col-lg-5">
                    <div class="plan-card <?= $currentPlan === $plan['slug'] ? 'active' : '' ?> <?= $plan['slug'] === 'premium' ? 'featured' : '' ?>">

                        <?php if ($currentPlan === $plan['slug']): ?>
                            <span class="plan-badge bg-primary text-white">Current Plan</span>
                        <?php elseif ($plan['slug'] === 'premium'): ?>
                            <span class="plan-badge" style="background:var(--color-accent);color:#fff;">Recommended</span>
                        <?php endif; ?>

                        <div class="mt-2 mb-3">
                            <?php if ($plan['slug'] === 'free'): ?>
                                <i class="bi bi-person-check" style="font-size:2.5rem;color:var(--color-primary);"></i>
                            <?php else: ?>
                                <i class="bi bi-gem" style="font-size:2.5rem;color:var(--color-accent);"></i>
                            <?php endif; ?>
                        </div>

                        <h4><?= sanitize($plan['name']) ?></h4>
                        <p class="text-muted small"><?= sanitize($plan['description']) ?></p>

                        <div class="plan-price my-3">
                            <?php if ($plan['price'] == 0): ?>
                                Free
                            <?php else: ?>
                                &#2547;<?= number_format($plan['price']) ?>
                                <small>/year</small>
                            <?php endif; ?>
                        </div>

                        <!-- Features -->
                        <ul class="plan-features">
                            <?php
                            $features = explode("\n", $plan['features'] ?? '');
                            foreach ($features as $feature):
                                $feature = trim($feature);
                                if (!empty($feature)):
                            ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <?= sanitize($feature) ?>
                            </li>
                            <?php endif; endforeach; ?>
                        </ul>

                        <?php if ($currentPlan === $plan['slug']): ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                <i class="bi bi-check-lg me-1"></i> Current Plan
                            </button>
                        <?php else: ?>
                            <form method="POST" action="">
                                <?= csrfField() ?>
                                <input type="hidden" name="plan" value="<?= sanitize($plan['slug']) ?>">
                                <button type="submit" class="btn <?= $plan['slug'] === 'premium' ? 'btn-accent' : 'btn-outline-primary-custom' ?> w-100"
                                        onclick="return confirm('Are you sure you want to switch to the <?= sanitize($plan['name']) ?> plan?')">
                                    <?php if ($plan['slug'] === 'premium'): ?>
                                        <i class="bi bi-arrow-up-circle me-1"></i> Upgrade to Premium
                                    <?php else: ?>
                                        <i class="bi bi-arrow-down-circle me-1"></i> Switch to Free
                                    <?php endif; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($plans)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-info-circle" style="font-size:3rem;"></i>
                <h5 class="mt-2">No plans available</h5>
                <p>Please check back later.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
</body>
</html>
