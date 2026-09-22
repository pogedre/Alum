<?php

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

if ($isAdmin) {
    $dashboardLink = "AdminDashboard.php";
} else {
    $dashboardLink = "Dashboard.php";
}

$activePage = $activePage ?? "";

?>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
            <img src="images/Seal.png" alt="Logo" class="navbar-logo">
            <span>AlumTrace</span>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a
                        class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>"
                        href="index.php"
                    >
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>"
                        href="AboutUs.php"
                    >
                        About Us
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?= $activePage === 'faqs' ? 'active' : '' ?>"
                        href="FAQs.php"
                    >
                        FAQs
                    </a>
                </li>

                <?php if ($isLoggedIn): ?>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>"
                            href="<?= htmlspecialchars($dashboardLink) ?>"
                        >
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="Logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>"
                            href="Login.php"
                        >
                            Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link <?= $activePage === 'register' ? 'active' : '' ?>"
                            href="Registration.php"
                        >
                            Register
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>
</nav>

<style>
/* Global standard navbar styles */
.navbar {
    background-color: #026539 !important;
    min-height: 72px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.18);
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.navbar-brand {
    gap: 12px;
    font-size: 1.25rem;
    color: #ffffff !important;
    text-decoration: none;
}

.navbar-logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.navbar .nav-link {
    color: rgba(246, 244, 242, 0.92) !important;
    margin-left: 8px;
    transition: color 0.2s ease, background-color 0.2s ease;
    font-weight: 500;
    white-space: nowrap;
    font-size: 0.95rem;
}

.navbar .nav-link:hover,
.navbar .nav-link.active {
    color: #f6c604 !important;
}

.navbar-toggler {
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.navbar-toggler:focus {
    box-shadow: none;
}

@media (max-width: 1200px) {
    .navbar .nav-link {
        margin-left: 4px;
        font-size: 0.9rem;
    }
}

@media (max-width: 991px) {
    .navbar .nav-link {
        margin-left: 0;
        padding: 8px 0;
    }
}
</style>