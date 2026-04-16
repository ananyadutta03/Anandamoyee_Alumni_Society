<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/members/index.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/admin/members/index.php');
}

$id     = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$pdo    = getDBConnection();

if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'user'");
    $stmt->execute([$id]);
    setFlash('success', 'Member approved successfully.');
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'user'");
    $stmt->execute([$id]);
    setFlash('warning', 'Member rejected.');
} else {
    setFlash('danger', 'Invalid action.');
}

redirect(SITE_URL . '/admin/members/index.php');
