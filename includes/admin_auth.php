<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (
    ($_SESSION["logged_in"] ?? false) !== true ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: Login.php");
    exit;
}

$adminName = $_SESSION["user_name"] ?? "Administrator";
$adminEmail = $_SESSION["user_email"] ?? "";
$adminInitial = strtoupper(substr($adminName, 0, 1));
