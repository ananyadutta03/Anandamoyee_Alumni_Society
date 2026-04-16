<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/committee/index.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/admin/committee/index.php');
}

$id = intval($_POST['id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT image FROM executive_committee WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if ($member) {
    if ($member['image']) deleteImage($member['image'], 'committee');
    $stmt = $pdo->prepare("DELETE FROM executive_committee WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Committee member deleted successfully.');
} else {
    setFlash('danger', 'Committee member not found.');
}

redirect(SITE_URL . '/admin/committee/index.php');
