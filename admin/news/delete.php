<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/news/index.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/admin/news/index.php');
}

$id = intval($_POST['id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if ($news) {
    if ($news['image']) deleteImage($news['image'], 'news');
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'News article deleted successfully.');
} else {
    setFlash('danger', 'News article not found.');
}

redirect(SITE_URL . '/admin/news/index.php');
