<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$pageTitle = 'Membership Plans - ' . SITE_NAME;
$userPage = 'membership';

$pdo = getDBConnection();

// Auto-downgrade: if executive and expired, move to general
$pdo->exec("UPDATE users SET membership_plan = 'general', plan_expires_at = NULL WHERE membership_plan = 'executive' AND plan_expires_at IS NOT NULL AND plan_expires_at < CURDATE()");

// Get current user
$stmt = $pdo->prepare("SELECT membership_plan, plan_expires_at, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$currentPlan = $user['membership_plan'] ?? 'general';

// Get all plans
$plans = $pdo->query("SELECT * FROM membership_plans WHERE is_active = 1 ORDER BY price ASC")->fetchAll();

// Check if user has a pending payment
$pendingStmt = $pdo->prepare("SELECT * FROM membership_payments WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$pendingStmt->execute([$_SESSION['user_id']]);
$pendingPayment = $pendingStmt->fetch();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/user/membership.php');
    }

    $planSlug      = $_POST['plan'] ?? '';
    $transactionId = trim($_POST['transaction_id'] ?? '');
    $paymentProof  = null;
    $errors        = [];

    // Validate plan
    $planStmt = $pdo->prepare("SELECT * FROM membership_plans WHERE slug = ? AND is_active = 1");
    $planStmt->execute([$planSlug]);
    $selectedPlan = $planStmt->fetch();

    if (!$selectedPlan) {
        $errors[] = 'Invalid plan selected.';
    }

    if ($pendingPayment) {
        $errors[] = 'You already have a pending payment. Please wait for admin approval.';
    }

    if ($planSlug === $currentPlan && $currentPlan !== 'executive') {
        $errors[] = 'You are already on this plan.';
    }

    // For paid plans, require payment proof
    if (empty($errors) && $selectedPlan['price'] > 0) {
        if (empty($_FILES['payment_proof']['name'])) {
            $errors[] = 'Payment proof screenshot is required.';
        } else {
            $paymentProof = uploadImage($_FILES['payment_proof'], 'payments');
            if (!$paymentProof) {
                $errors[] = 'Invalid image file. Allowed: jpg, jpeg, png, gif, webp (max 5MB).';
            }
        }
    }

    if (empty($errors)) {
        if ($selectedPlan['price'] == 0) {
            // General member — switch directly
            $stmt = $pdo->prepare("UPDATE users SET membership_plan = 'general', plan_expires_at = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            setFlash('success', 'You have switched to General Member.');
        } else {
            // Paid plan — submit payment for admin approval
            $insertStmt = $pdo->prepare("INSERT INTO membership_payments (user_id, plan_slug, amount, transaction_id, payment_proof) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->execute([$_SESSION['user_id'], $planSlug, $selectedPlan['price'], $transactionId ?: null, $paymentProof]);
            setFlash('success', 'Payment submitted! Please wait for admin verification and approval.');
        }
        redirect(SITE_URL . '/user/membership.php');
    } else {
        setFlash('danger', implode('<br>', $errors));
    }
}

// Plan labels
$planLabels = ['general' => 'General Member', 'executive' => 'Executive Member', 'lifetime' => 'Lifetime Member'];
$planIcons  = ['general' => 'bi-person-check', 'executive' => 'bi-star', 'lifetime' => 'bi-gem'];
$planColors = ['general' => 'secondary', 'executive' => 'primary', 'lifetime' => 'warning'];
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
            padding: 30px 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            background: var(--color-white);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .plan-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-5px); }
        .plan-card.active { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.15); }
        .plan-card.featured { border-color: var(--color-accent); }
        .plan-card .plan-badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            padding: 4px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;
        }
        .plan-card .plan-price { font-size: 2.2rem; font-weight: 700; color: var(--color-dark); font-family: var(--font-heading); }
        .plan-card .plan-price small { font-size: 0.85rem; font-weight: 400; color: var(--color-text-muted); }
        .plan-features { list-style: none; padding: 0; text-align: left; margin: 15px 0; flex-grow: 1; }
        .plan-features li { padding: 7px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.85rem; }
        .plan-features li i { margin-right: 8px; width: 18px; }
        .plan-features li .bi-check-circle-fill { color: #28a745; }
        .plan-note { font-size: 0.78rem; color: var(--color-text-muted); margin-top: 8px; }
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
            <div class="alert alert-<?= $planColors[$currentPlan] ?? 'secondary' ?> d-flex align-items-center mb-4">
                <i class="bi <?= $planIcons[$currentPlan] ?? 'bi-person' ?> me-2" style="font-size:1.2rem;"></i>
                <div>
                    Your current plan: <strong><?= $planLabels[$currentPlan] ?? ucfirst($currentPlan) ?></strong>
                    <?php if ($currentPlan === 'executive' && $user['plan_expires_at']): ?>
                        &bull; Expires: <strong><?= formatDate($user['plan_expires_at']) ?></strong>
                    <?php elseif ($currentPlan === 'lifetime'): ?>
                        &bull; Valid forever
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Payment Notice -->
            <?php if ($pendingPayment): ?>
            <div class="alert alert-warning mb-4">
                <i class="bi bi-hourglass-split me-2"></i>
                <strong>Payment Pending:</strong> You have submitted a payment for <strong><?= $planLabels[$pendingPayment['plan_slug']] ?? $pendingPayment['plan_slug'] ?></strong> (&#2547;<?= number_format($pendingPayment['amount']) ?>). Waiting for admin approval.
            </div>
            <?php endif; ?>

            <!-- Plan Cards -->
            <div class="row g-4 justify-content-center">
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="plan-card <?= $currentPlan === $plan['slug'] ? 'active' : '' ?> <?= $plan['slug'] === 'lifetime' ? 'featured' : '' ?>">

                        <?php if ($currentPlan === $plan['slug']): ?>
                            <span class="plan-badge bg-primary text-white">Current Plan</span>
                        <?php elseif ($plan['slug'] === 'lifetime'): ?>
                            <span class="plan-badge" style="background:var(--color-accent);color:#fff;">Best Value</span>
                        <?php endif; ?>

                        <div class="mt-2 mb-3">
                            <i class="bi <?= $planIcons[$plan['slug']] ?? 'bi-person' ?>" style="font-size:2.5rem;color:var(--color-<?= $planColors[$plan['slug']] ?? 'primary' ?>);"></i>
                        </div>

                        <h4><?= sanitize($plan['name']) ?></h4>
                        <p class="text-muted small"><?= sanitize($plan['description']) ?></p>

                        <div class="plan-price my-3">
                            <?php if ($plan['price'] == 0): ?>
                                Free
                            <?php elseif ($plan['slug'] === 'lifetime'): ?>
                                &#2547;<?= number_format($plan['price']) ?>
                                <small>/one-time</small>
                            <?php else: ?>
                                &#2547;<?= number_format($plan['price']) ?>
                                <small>/year</small>
                            <?php endif; ?>
                        </div>

                        <?php if ($plan['slug'] === 'executive'): ?>
                            <p class="plan-note"><i class="bi bi-info-circle me-1"></i>Free for the 1st year. &#2547;<?= number_format($plan['price']) ?>/year after renewal.</p>
                        <?php endif; ?>

                        <ul class="plan-features">
                            <?php
                            $features = explode("\n", $plan['features'] ?? '');
                            foreach ($features as $feature):
                                $feature = trim($feature);
                                if (!empty($feature)):
                            ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?= sanitize($feature) ?></li>
                            <?php endif; endforeach; ?>
                        </ul>

                        <?php if ($currentPlan === $plan['slug'] && $plan['slug'] !== 'executive'): ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                <i class="bi bi-check-lg me-1"></i> Current Plan
                            </button>
                        <?php elseif ($currentPlan === 'lifetime'): ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                <i class="bi bi-lock me-1"></i> You have Lifetime
                            </button>
                        <?php elseif ($pendingPayment): ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                <i class="bi bi-hourglass me-1"></i> Payment Pending
                            </button>
                        <?php elseif ($plan['price'] == 0): ?>
                            <form method="POST" action="">
                                <?= csrfField() ?>
                                <input type="hidden" name="plan" value="<?= sanitize($plan['slug']) ?>">
                                <button type="submit" class="btn btn-outline-primary-custom w-100"
                                    onclick="return confirm('Switch to General Member (Free)?')">
                                    <i class="bi bi-arrow-down-circle me-1"></i> Switch to General
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn <?= $plan['slug'] === 'lifetime' ? 'btn-accent' : 'btn-primary-custom' ?> w-100"
                                data-bs-toggle="modal" data-bs-target="#paymentModal-<?= $plan['slug'] ?>">
                                <?php if ($currentPlan === 'executive' && $plan['slug'] === 'executive'): ?>
                                    <i class="bi bi-arrow-repeat me-1"></i> Renew Membership
                                <?php else: ?>
                                    <i class="bi bi-arrow-up-circle me-1"></i> Upgrade
                                <?php endif; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Modal for paid plans -->
                <?php if ($plan['price'] > 0): ?>
                <div class="modal fade" id="paymentModal-<?= $plan['slug'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Payment for <?= sanitize($plan['name']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <input type="hidden" name="plan" value="<?= sanitize($plan['slug']) ?>">
                                <div class="modal-body">
                                    <div class="alert alert-info mb-3">
                                        <strong>Amount: &#2547;<?= number_format($plan['price']) ?></strong>
                                        <?= $plan['slug'] === 'lifetime' ? ' (One-time payment)' : ' (Per year)' ?>
                                    </div>

                                    <?php if ($plan['payment_instructions']): ?>
                                    <div class="alert alert-warning mb-3">
                                        <h6 class="alert-heading mb-1"><i class="bi bi-info-circle me-1"></i> Payment Instructions</h6>
                                        <p class="mb-0 small"><?= nl2br(sanitize($plan['payment_instructions'])) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label class="form-label">Transaction ID</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="Enter bKash/Nagad transaction ID">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Payment Proof (Screenshot) *</label>
                                        <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                                        <small class="text-muted">Upload screenshot of payment. Max 5MB.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="bi bi-send me-1"></i> Submit Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php endforeach; ?>
            </div>

            <?php if (empty($plans)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-info-circle" style="font-size:3rem;"></i>
                <h5 class="mt-2">No plans available</h5>
            </div>
            <?php endif; ?>

            <!-- Payment History -->
            <?php
            $historyStmt = $pdo->prepare("SELECT mp.*, pl.name as plan_name FROM membership_payments mp LEFT JOIN membership_plans pl ON mp.plan_slug = pl.slug WHERE mp.user_id = ? ORDER BY mp.created_at DESC LIMIT 10");
            $historyStmt->execute([$_SESSION['user_id']]);
            $history = $historyStmt->fetchAll();
            ?>
            <?php if (!empty($history)): ?>
            <div class="mt-5">
                <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Payment History</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Date</th><th>Plan</th><th>Amount</th><th>Transaction ID</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= formatDate($h['created_at']) ?></td>
                                <td><?= sanitize($h['plan_name'] ?? $h['plan_slug']) ?></td>
                                <td>&#2547;<?= number_format($h['amount']) ?></td>
                                <td><?= sanitize($h['transaction_id'] ?? '-') ?></td>
                                <td>
                                    <span class="badge <?php
                                        if ($h['status'] === 'approved') echo 'badge-approved';
                                        elseif ($h['status'] === 'pending') echo 'badge-pending';
                                        else echo 'badge-rejected';
                                    ?>"><?= ucfirst($h['status']) ?></span>
                                    <?php if ($h['admin_note']): ?>
                                        <br><small class="text-muted"><?= sanitize($h['admin_note']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
<script>
    setTimeout(function() {
        document.querySelectorAll('.flash-message .alert').forEach(function(el) {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 300);
        });
    }, 5000);
</script>
</body>
</html>
