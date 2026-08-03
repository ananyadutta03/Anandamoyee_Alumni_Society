<?php
require_once __DIR__ . '/../../config/init.php';

if (!isLoggedIn()) {
    setFlash('danger', 'Please login first.');
    redirect(SITE_URL . '/auth/login.php');
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    setFlash('danger', 'Access denied.');
    redirect(SITE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/members/index.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    redirect(SITE_URL . '/admin/members/index.php');
}

$id   = (int)($_POST['id'] ?? 0);
$role = $_POST['role'] ?? '';

if (!$id || !in_array($role, ['user', 'admin'])) {
    setFlash('danger', 'Invalid role.');
    redirect(SITE_URL . '/admin/members/index.php');
}

// Prevent changing your own role
if ($id == $_SESSION['user_id']) {
    setFlash('danger', 'You cannot change your own role.');
    redirect(SITE_URL . '/admin/members/view.php?id=' . $id);
}

$pdo = getDBConnection();

$pdo = getDBConnection();

// Check user exists and is approved
$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect(SITE_URL . '/admin/members/index.php');
}

if ($user['status'] !== 'approved') {
    setFlash('danger', 'Only approved members can be promoted or demoted.');
    redirect(SITE_URL . '/admin/members/view.php?id=' . $id);
}

// Update role
$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$role, $id]);


setFlash('success', 'User role updated successfully.');
redirect(SITE_URL . '/admin/members/view.php?id=' . $id);