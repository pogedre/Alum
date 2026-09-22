<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (($_SESSION['logged_in'] ?? false) === true) {
    header('Location: ' . (($_SESSION['role'] ?? '') === 'admin' ? 'AdminDashboard.php' : 'Dashboard.php'));
    exit;
}
$message = $_SESSION['login_message'] ?? '';
$messageType = $_SESSION['login_message_type'] ?? 'danger';
unset($_SESSION['login_message'], $_SESSION['login_message_type']);
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login | AlumTrace</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><link rel="stylesheet" href="assets/css/alumtrace.css"></head><body class="auth-page"><div class="auth-shell"><section class="auth-brand"><img src="images/Badge.png" alt="AlumTrace logo"><span>AlumTrace</span><p>Reconnect with your alumni community and keep your journey visible.</p></section><section class="auth-card"><div class="auth-card-head"><span class="eyebrow">Welcome back</span><h1>Sign in to AlumTrace</h1><p>Use your registered email and password to continue.</p></div><?php if($message):?><div class="alert alert-<?=htmlspecialchars($messageType)?>" role="alert"><?=htmlspecialchars($message)?></div><?php endif;?><form method="post" action="LoginProcess.php" class="auth-form"><?=csrf_field()?><div class="field"><label for="email">Email address</label><div class="input-icon"><i class="fa-solid fa-envelope"></i><input id="email" name="email" type="email" autocomplete="email" required></div></div><div class="field"><label for="password">Password</label><div class="input-icon"><i class="fa-solid fa-lock"></i><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle data-target="password">Show</button></div></div><div class="auth-options"><a href="ForgotPassword.php">Forgot password?</a></div><button class="btn btn-primary auth-submit" type="submit"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign in</button></form><p class="auth-switch">New to AlumTrace? <a href="Registration.php">Create an account</a></p><a class="auth-back" href="index.php">Back to home</a></section></div><script src="assets/js/alumtrace.js"></script></body></html>
