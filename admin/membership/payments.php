<?php
$pageTitle = 'Membership Payments - Admin';
$adminPage = 'membership';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$planLabels = ['general' => 'General Member', 'executive' => 'Executive Member', 'lifetime' => 'Lifetime Member'];

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid form submission.');
        redirect(SITE_URL . '/admin/membership/payments.php');
    }

    $paymentId = intval($_POST['payment_id'] ?? 0);
    $action    = $_POST['action'] ?? '';
    $adminNote = trim($_POST['admin_note'] ?? '');

    $payStmt = $pdo->prepare("SELECT * FROM membership_payments WHERE id = ?");
    $payStmt->execute([$paymentId]);
    $payment = $payStmt->fetch();

    if ($payment) {
        if ($action === 'approve') {
            // Update payment status
            $pdo->prepare("UPDATE membership_payments SET status = 'approved', admin_note = ? WHERE id = ?")->execute([$adminNote ?: null, $paymentId]);

            // Update user plan
            if ($payment['plan_slug'] === 'lifetime') {
                $pdo->prepare("UPDATE users SET membership_plan = 'lifetime', plan_expires_at = NULL WHERE id = ?")->execute([$payment['user_id']]);
            } elseif ($payment['plan_slug'] === 'executive') {
                $expiresAt = date('Y-m-d', strtotime('+12 months'));
                $pdo->prepare("UPDATE users SET membership_plan = 'executive', plan_expires_at = ? WHERE id = ?")->execute([$expiresAt, $payment['user_id']]);
            }

            setFlash('success', 'Payment approved. Member plan updated.');
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE membership_payments SET status = 'rejected', admin_note = ? WHERE id = ?")->execute([$adminNote ?: 'Payment rejected by admin.', $paymentId]);
            setFlash('info', 'Payment rejected.');
        }
    }

    redirect(SITE_URL . '/admin/membership/payments.php');
}

// Get payments
$statusFilter = $_GET['status'] ?? 'pending';
$validStatuses = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($statusFilter, $validStatuses)) $statusFilter = 'pending';

if ($statusFilter === 'all') {
    $payments = $pdo->query("SELECT mp.*, u.name as user_name, u.email as user_email, u.batch as user_batch, pl.name as plan_name FROM membership_payments mp JOIN users u ON mp.user_id = u.id LEFT JOIN membership_plans pl ON mp.plan_slug = pl.slug ORDER BY mp.created_at DESC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT mp.*, u.name as user_name, u.email as user_email, u.batch as user_batch, pl.name as plan_name FROM membership_payments mp JOIN users u ON mp.user_id = u.id LEFT JOIN membership_plans pl ON mp.plan_slug = pl.slug WHERE mp.status = ? ORDER BY mp.created_at DESC");
    $stmt->execute([$statusFilter]);
    $payments = $stmt->fetchAll();
}

$pendingCount  = $pdo->query("SELECT COUNT(*) FROM membership_payments WHERE status = 'pending'")->fetchColumn();
$approvedCount = $pdo->query("SELECT COUNT(*) FROM membership_payments WHERE status = 'approved'")->fetchColumn();
$rejectedCount = $pdo->query("SELECT COUNT(*) FROM membership_payments WHERE status = 'rejected'")->fetchColumn();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Membership Payments</h4>
        <div class="user-info">
            <a href="<?= SITE_URL ?>/admin/membership/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Membership
            </a>
        </div>
    </div>

    <div class="admin-main">
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="?status=pending" class="btn btn-sm <?= $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                Pending (<?= $pendingCount ?>)
            </a>
            <a href="?status=approved" class="btn btn-sm <?= $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
                Approved (<?= $approvedCount ?>)
            </a>
            <a href="?status=rejected" class="btn btn-sm <?= $statusFilter === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
                Rejected (<?= $rejectedCount ?>)
            </a>
            <a href="?status=all" class="btn btn-sm <?= $statusFilter === 'all' ? 'btn-primary-custom' : 'btn-outline-primary-custom' ?>">
                All
            </a>
        </div>

        <!-- Payments Table -->
        <div class="admin-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><small><?= formatDate($p['created_at']) ?></small></td>
                            <td>
                                <strong><?= sanitize($p['user_name']) ?></strong><br>
                                <small class="text-muted"><?= sanitize($p['user_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge <?php
                                    $bc = ['executive' => 'bg-primary', 'lifetime' => 'bg-warning text-dark'];
                                    echo $bc[$p['plan_slug']] ?? 'bg-secondary';
                                ?>"><?= sanitize($p['plan_name'] ?? $p['plan_slug']) ?></span>
                            </td>
                            <td>&#2547;<?= number_format($p['amount']) ?></td>
                            <td><?= sanitize($p['transaction_id'] ?? '-') ?></td>
                            <td>
                                <?php if ($p['payment_proof']): ?>
                                    <a href="<?= UPLOAD_URL ?>payments/<?= sanitize($p['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-image"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php
                                    if ($p['status'] === 'approved') echo 'badge-approved';
                                    elseif ($p['status'] === 'pending') echo 'badge-pending';
                                    else echo 'badge-rejected';
                                ?>"><?= ucfirst($p['status']) ?></span>
                                <?php if ($p['admin_note']): ?>
                                    <br><small class="text-muted"><?= sanitize($p['admin_note']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?php if ($p['status'] === 'pending'): ?>
                                    <!-- Approve -->
                                    <form method="POST" action="" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="admin_note" value="">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this payment?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <!-- Reject -->
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-<?= $p['id'] ?>">
                                        <i class="bi bi-x-lg"></i>
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal-<?= $p['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content">
                                                <form method="POST" action="">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Reject Payment</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label">Reason (optional)</label>
                                                        <textarea name="admin_note" class="form-control" rows="2" placeholder="Reason for rejection..."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No payments found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
