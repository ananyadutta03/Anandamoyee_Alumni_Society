<?php

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to access this page.');
        redirect(SITE_URL . '/auth/login.php');
    }
}

/**
 * Require user to be admin, redirect to home if not
 */
function requireAdmin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to access this page.');
        redirect(SITE_URL . '/auth/login.php');
    }
    if (!isAdmin()) {
        setFlash('danger', 'Access denied. Admin privileges required.');
        redirect(SITE_URL . '/index.php');
    }
}
