<?php

/**
 * Redirect to a URL and exit
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Sanitize output to prevent XSS
 */
function sanitize(?string $input): string {
    return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Allow limited safe HTML tags (for rich text bio content)
 */
function sanitizeHtml(string $input): string {
    $allowed = '<strong><b><em><i><u><a><p><br><ul><ol><li><span>';
    $clean   = strip_tags($input, $allowed);
    // Force target=_blank and rel on links, coerce href scheme to http/https/mailto
    $clean = preg_replace_callback('/<a\s+([^>]*)>/i', function ($m) {
        if (preg_match('/href\s*=\s*"([^"]*)"/i', $m[1], $h)) {
            $href = $h[1];
            if (!preg_match('/^(https?:|mailto:)/i', $href)) $href = 'http://' . ltrim($href, '/');
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" target="_blank" rel="noopener">';
        }
        return '<a>';
    }, $clean);
    return $clean;
}

/**
 * Convert a string to URL-safe slug
 */
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    // Broken session: user_id exists but user_name is missing — clear only login keys
    if (!isset($_SESSION['user_name'])) {
        unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_role']);
        return false;
    }
    return true;
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user data from session
 */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

/**
 * Set a flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Upload an image file
 * Returns filename on success, false on failure
 */
function uploadImage(array $file, string $folder): string|false {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize      = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $maxSize) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts) || !in_array($file['type'], $allowedTypes)) {
        return false;
    }

    $newName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $destDir = UPLOAD_PATH . $folder . '/';

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
        return $newName;
    }
    return false;
}

/**
 * Delete an uploaded image
 */
function deleteImage(string $filename, string $folder): bool {
    $path = UPLOAD_PATH . $folder . '/' . $filename;
    if (file_exists($path)) {
        return unlink($path);
    }
    return false;
}

/**
 * Human-readable relative time
 */
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Truncate text with ellipsis
 */
function truncateText(string $text, int $length = 150): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Generate or retrieve CSRF token
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF hidden input field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Get pagination data
 */
function paginate(int $total, int $perPage, int $currentPage): array {
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
    ];
}

/**
 * Format date for display
 */
function formatDate(string $date, string $format = 'M d, Y'): string {
    return date($format, strtotime($date));
}

/**
 * Check if current user has a premium membership plan
 */
function isPremium(): bool {
    if (!isLoggedIn()) return false;
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT membership_plan, plan_expires_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['membership_plan'] !== 'premium') return false;
    if ($user['plan_expires_at'] && strtotime($user['plan_expires_at']) < time()) return false;
    return true;
}
