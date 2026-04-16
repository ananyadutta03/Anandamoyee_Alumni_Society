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

$id  = intval($_POST['id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    if ($user['profile_image']) deleteImage($user['profile_image'], 'members');
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$id]);
    setFlash('success', 'Member deleted successfully.');
} else {
    setFlash('danger', 'Member not found.');
}

redirect(SITE_URL . '/admin/members/index.php');
