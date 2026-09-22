<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/profile_loader.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$items = [
    'Dashboard.php' => ['fa-house', 'Dashboard'],
    'Profile.php' => ['fa-user-pen', 'My Profile'],
    'Employment.php' => ['fa-briefcase', 'Employment'],
    'Education.php' => ['fa-certificate', 'Education'],
    'Directory.php' => ['fa-address-book', 'Directory'],
    'Survey.php' => ['fa-square-poll-horizontal', 'Survey Forms'],
    'Events.php' => ['fa-bullhorn', 'Events & News'],
];
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand"><a class="brand-link" href="Dashboard.php"><img src="images/Badge.png" alt="AlumTrace logo"><span>AlumTrace</span></a><button class="icon-btn" type="button" data-sidebar-toggle aria-label="Toggle sidebar"><i class="fa-solid fa-bars"></i></button></div>
  <nav class="sidebar-nav" aria-label="Main navigation"><ul>
  <?php foreach ($items as $url => [$icon, $label]): ?><li><a href="<?= htmlspecialchars($url) ?>" class="<?= $currentPage === $url ? 'active' : '' ?>" <?= $currentPage === $url ? 'aria-current="page"' : '' ?>><i class="fa-solid <?= $icon ?>"></i><span class="nav-label"><?= htmlspecialchars($label) ?></span></a></li><?php endforeach; ?>
  </ul></nav>
  <div class="sidebar-footer"><a href="Logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i><span class="nav-label">Logout</span></a></div>
</aside><div class="sidebar-backdrop" id="sidebarBackdrop"></div>
