<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-header">
        <img src="assets/img/logo.png" alt="AlumTrace Logo" class="sidebar-logo">

        <div class="sidebar-title">
            <h5>AlumTrace</h5>
            <small>Alumni Tracking System</small>
        </div>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav">

        <li>
            <a href="Dashboard.php"
               class="<?= $currentPage == 'Dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>

        <li>
            <a href="Profile.php"
               class="<?= $currentPage == 'Profile.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-user"></i>
                <span class="nav-label">My Profile</span>
            </a>
        </li>

        <li>
            <a href="Employment.php"
               class="<?= $currentPage == 'Employment.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-briefcase"></i>
                <span class="nav-label">Employment</span>
            </a>
        </li>

        <li>
            <a href="Education.php"
               class="<?= $currentPage == 'Education.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i>
                <span class="nav-label">Education</span>
            </a>
        </li>

        <li>
            <a href="Directory.php"
               class="<?= $currentPage == 'Directory.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-address-book"></i>
                <span class="nav-label">Alumni Directory</span>
            </a>
        </li>

        <li>
            <a href="Survey.php"
               class="<?= $currentPage == 'Survey.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-square-poll-horizontal"></i>
                <span class="nav-label">Survey</span>
            </a>
        </li>

        <li>
            <a href="Events.php"
               class="<?= $currentPage == 'Events.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-bullhorn"></i>
                <span class="nav-label">Events & Announcements</span>
            </a>
        </li>

    </ul>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="Logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="nav-label">Logout</span>
        </a>
    </div>

</aside>