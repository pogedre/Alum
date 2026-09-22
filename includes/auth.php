<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function require_login(): void {
    if (($_SESSION['logged_in'] ?? false) !== true) {
        header('Location: Login.php');
        exit;
    }
}

function require_alumni(): void {
    require_login();
    if (($_SESSION['role'] ?? 'alumni') === 'admin') {
        header('Location: AdminDashboard.php');
        exit;
    }
}

function current_user_name(): string {
    $name = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['middle_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
    return $name !== '' ? $name : ($_SESSION['user_name'] ?? 'Alumni');
}
