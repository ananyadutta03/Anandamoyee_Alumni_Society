<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/events/index.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/admin/events/index.php');
}

$id      = intval($_POST['id'] ?? 0);
$eventId = intval($_POST['event_id'] ?? 0);
$action  = $_POST['action'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');
$pdo     = getDBConnection();

if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE event_registrations SET status = 'approved', admin_remarks = ? WHERE id = ?");
    $stmt->execute([$remarks ?: null, $id]);
    setFlash('success', 'Registration approved.');
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare("UPDATE event_registrations SET status = 'rejected', admin_remarks = ? WHERE id = ?");
    $stmt->execute([$remarks ?: null, $id]);
    setFlash('warning', 'Registration rejected.');
} else {
    setFlash('danger', 'Invalid action.');
}

redirect(SITE_URL . '/admin/events/registrations.php?id=' . $eventId);
