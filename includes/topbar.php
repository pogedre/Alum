<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'AlumTrace';
$name = current_user_name();
$email = $_SESSION['user_email'] ?? '';
$initial = strtoupper(substr($name, 0, 1));
?><header class="topbar"><div class="topbar-left"><button class="header-toggle" type="button" data-sidebar-toggle aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button><h1><?= htmlspecialchars($pageTitle) ?></h1></div><div class="topbar-right"><div class="user-meta"><strong><?= htmlspecialchars($name) ?></strong><small><?= htmlspecialchars($email) ?></small></div><div class="avatar-sm fallback"><?= htmlspecialchars($initial) ?></div><a href="Logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></div></header>
