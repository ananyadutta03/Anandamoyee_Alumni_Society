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

$id = intval($_POST['id'] ?? 0);
$pdo = getDBConnection();

// Get event to delete image
$stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if ($event) {
    if ($event['image']) {
        deleteImage($event['image'], 'events');
    }
    // Delete payment proof images for this event's registrations
    $regStmt = $pdo->prepare("SELECT payment_proof FROM event_registrations WHERE event_id = ?");
    $regStmt->execute([$id]);
    while ($reg = $regStmt->fetch()) {
        if ($reg['payment_proof']) {
            deleteImage($reg['payment_proof'], 'payments');
        }
    }
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Event deleted successfully.');
} else {
    setFlash('danger', 'Event not found.');
}

redirect(SITE_URL . '/admin/events/index.php');
