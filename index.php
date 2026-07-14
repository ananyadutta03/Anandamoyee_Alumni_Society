<?php
require_once __DIR__ . '/config/init.php';

$pageTitle = 'Home - ' . SITE_NAME;
$currentPage = 'home';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/pages/home.php';
include __DIR__ . '/includes/footer.php';
//test 