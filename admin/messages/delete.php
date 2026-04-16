<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/messages/index.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/admin/messages/index.php');
}

$id  = intval($_POST['id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);

setFlash('success', 'Message deleted successfully.');
redirect(SITE_URL . '/admin/messages/index.php');
