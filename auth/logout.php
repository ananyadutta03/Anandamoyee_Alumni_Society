<?php
require_once __DIR__ . '/../config/init.php';

$_SESSION = [];
session_destroy();

setFlash('success', 'You have been logged out successfully.');
redirect(SITE_URL . '/index.php');
