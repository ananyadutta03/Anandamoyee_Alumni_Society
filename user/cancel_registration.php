<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/user/my_registrations.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/user/my_registrations.php');
}

$id  = intval($_POST['id'] ?? 0);
$pdo = getDBConnection();

// Only allow cancelling own pending registrations
$stmt = $pdo->prepare("SELECT * FROM event_registrations WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->execute([$id, $_SESSION['user_id']]);
$reg = $stmt->fetch();

if ($reg) {
    // Delete payment proof image
    if ($reg['payment_proof']) {
        deleteImage($reg['payment_proof'], 'payments');
    }

    $delStmt = $pdo->prepare("DELETE FROM event_registrations WHERE id = ?");
    $delStmt->execute([$id]);
    setFlash('success', 'Registration cancelled successfully.');
} else {
    setFlash('danger', 'Registration not found or cannot be cancelled.');
}

redirect(SITE_URL . '/user/my_registrations.php');
