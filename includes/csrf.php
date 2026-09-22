<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    require_once __DIR__ . '/auth.php';
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid request token.');
    }
}
