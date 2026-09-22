<?php
/**
 * Master UI Layout Helper
 * University Green Theme Design Blueprint
 */

function render_head($pageTitle = "Alumni Tracking System") {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | AlumTrace</title>
    
    <link rel="icon" type="image/png" href="images/Badge.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg-soft: #F4F9F5;
            --primary-green: #0F5132;
            --accent-green: #198754;
            --light-green-bg: #E8F5E9;
            --sidebar-expanded-width: 260px;
            --sidebar-collapsed-width: 78px;
            --header-height: 70px;
            --border-radius: 20px;
            --shadow-soft: 0 8px 24px rgba(15, 81, 50, 0.06);
            --shadow-hover: 0 12px 32px rgba(15, 81, 50, 0.12);
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-soft);
            color: #2D3748;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* --- SIDEBAR --- */
        #sidebar {
            width: var(--sidebar-expanded-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #FFFFFF;
            border-right: 1px solid #E2E8F0;
            z-index: 1040;
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 12px rgba(0,0,0,0.02);
        }

        body.sidebar-collapsed #sidebar {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid #F0F0F0;
            overflow: hidden;
        }

        .sidebar-brand a {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--primary-green);
            font-weight: 700;
            font-size: 1.2rem;
            white-space: nowrap;
        }

        .sidebar-brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .sidebar-nav {
            padding: 15px 10px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 6px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #4A5568;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            white-space: nowrap;
            transition: var(--transition-smooth);
        }

        .sidebar-nav a:hover {
            background-color: var(--bg-soft);
            color: var(--accent-green);
        }

        .sidebar-nav a.active {
            background-color: var(--primary-green);
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(15, 81, 50, 0.2);
        }

        .sidebar-nav a i {
            font-size: 1.25rem;
            width: 28px;
            text-align: center;
            flex-shrink: 0;
            margin-right: 12px;
        }

        body.sidebar-collapsed .sidebar-nav a {
            padding: 12px 0;
            justify-content: center;
        }

        body.sidebar-collapsed .sidebar-nav a i {
            margin-right: 0;
        }

        body.sidebar-collapsed .sidebar-text, 
        body.sidebar-collapsed .sidebar-brand-text {
            display: none !important;
        }

        /* --- HEADER --- */
        .main-wrapper {
            margin-left: var(--sidebar-expanded-width);
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        body.sidebar-collapsed .main-wrapper {
            margin-left: var(--sidebar-collapsed-width);
        }

        .topbar {
            height: var(--header-height);
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0;
            z-index: 1030;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .toggle-btn {
            background: var(--bg-soft);
            border: none;
            color: var(--primary-green);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .toggle-btn:hover {
            background-color: var(--light-green-bg);
            color: var(--primary-green);
        }

        .topbar h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-green);
            margin: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-green);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            object-fit: cover;
            border: 2px solid var(--light-green-bg);
        }

        .user-details {
            text-align: right;
            line-height: 1.2;
        }

        .user-details .name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1A202C;
        }

        .user-details .email {
            font-size: 0.78rem;
            color: #718096;
        }

        /* --- UI CARDS & FORMS --- */
        .content {
            padding: 32px 28px;
            flex-grow: 1;
        }

        .card-custom {
            background: #FFFFFF;
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow-soft);
            padding: 28px;
            margin-bottom: 24px;
            transition: var(--transition-smooth);
        }

        .card-custom:hover {
            box-shadow: var(--shadow-hover);
        }

        .card-title-custom {
            color: var(--primary-green);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--bg-soft);
            padding-bottom: 12px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 10px 16px;
            border: 1px solid #CBD5E1;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .btn-green {
            background-color: var(--primary-green);
            color: #FFFFFF;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            border: none;
            transition: var(--transition-smooth);
        }

        .btn-green:hover {
            background-color: var(--accent-green);
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        .btn-outline-green {
            border: 1.5px solid var(--primary-green);
            color: var(--primary-green);
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            background: transparent;
            transition: var(--transition-smooth);
        }

        .btn-outline-green:hover {
            background-color: var(--primary-green);
            color: #FFFFFF;
        }

        .tag-custom {
            background-color: var(--light-green-bg);
            color: var(--primary-green);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        /* --- FOOTER --- */
        .site-footer {
            background-color: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 24px 28px;
            margin-top: auto;
        }

        .footer-text {
            font-size: 0.88rem;
            color: #64748B;
            margin-bottom: 6px;
        }

        .footer-copy {
            font-size: 0.82rem;
            color: #94A3B8;
            font-weight: 500;
        }

        /* --- RESPONSIVE MOBILE OVERLAYS --- */
        @media (max-width: 991.88px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-expanded-width));
            }

            body.sidebar-mobile-show #sidebar {
                margin-left: 0;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                z-index: 1035;
            }

            body.sidebar-mobile-show .sidebar-overlay {
                display: block;
            }
        }
    </style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php
}

function render_navigation($activePage, $userName, $userEmail, $userInitial, $hasProfilePicture = false, $profilePictureUrl = "") {
    $navItems = [
        'Dashboard.php' => ['icon' => 'fa-house', 'label' => 'Dashboard'],
        'Profile.php' => ['icon' => 'fa-user-pen', 'label' => 'My Profile'],
        'Employment.php' => ['icon' => 'fa-briefcase', 'label' => 'Employment'],
        'Education.php' => ['icon' => 'fa-certificate', 'label' => 'Education'],
        'Directory.php' => ['icon' => 'fa-address-book', 'label' => 'Directory'],
        'Survey.php' => ['icon' => 'fa-square-poll-horizontal', 'label' => 'Survey Forms'],
        'Events.php' => ['icon' => 'fa-bullhorn', 'label' => 'Events & News'],
    ];

    $currentPageTitle = "Dashboard";
    foreach ($navItems as $url => $item) {
        if ($activePage === $url || ($activePage === 'AlumniProfile.php' && $url === 'Profile.php') || ($activePage === 'EmploymentInformation.php' && $url === 'Employment.php') || ($activePage === 'EducationAndCertification.php' && $url === 'Education.php') || ($activePage === 'AlumniDirectory.php' && $url === 'Directory.php') || ($activePage === 'SurveyForms.php' && $url === 'Survey.php') || ($activePage === 'EventAndAnnouncement.php' && $url === 'Events.php')) {
            $currentPageTitle = $item['label'];
            break;
        }
    }
?>
    <!-- Sidebar -->
    <aside id="sidebar">
        <div class="sidebar-brand">
            <a href="Dashboard.php">
                <img src="images/Badge.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                <span class="sidebar-brand-text">AlumTrace</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <?php foreach ($navItems as $file => $meta): 
                    $isActive = ($activePage === $file) || 
                                ($file === 'Profile.php' && $activePage === 'AlumniProfile.php') ||
                                ($file === 'Employment.php' && $activePage === 'EmploymentInformation.php') ||
                                ($file === 'Education.php' && $activePage === 'EducationAndCertification.php') ||
                                ($file === 'Directory.php' && $activePage === 'AlumniDirectory.php') ||
                                ($file === 'Survey.php' && $activePage === 'SurveyForms.php') ||
                                ($file === 'Events.php' && $activePage === 'EventAndAnnouncement.php');
                ?>
                    <li>
                        <a href="<?php echo $file; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
                            <i class="fa-solid <?php echo $meta['icon']; ?>"></i>
                            <span class="sidebar-text"><?php echo $meta['label']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <!-- Main Section Wrapper -->
    <div class="main-wrapper">
        <!-- Sticky Header -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1><?php echo htmlspecialchars($currentPageTitle); ?></h1>
            </div>
            <div class="topbar-right">
                <div class="user-details d-none d-sm-block">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
                <?php if ($hasProfilePicture && !empty($profilePictureUrl)): ?>
                    <img src="<?php echo htmlspecialchars($profilePictureUrl); ?>" class="user-avatar" alt="Avatar">
                <?php else: ?>
                    <div class="user-avatar"><?php echo htmlspecialchars($userInitial); ?></div>
                <?php endif; ?>
                <a href="Logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-2">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="d-none d-md-inline">Logout</span>
                </a>
            </div>
        </header>
<?php
}

function render_footer() {
?>
        <!-- Footer -->
        <footer class="site-footer text-center text-md-start">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="footer-text m-0">
                        AlumTrace helps alumni manage their profile, employment, education, surveys, and events in one easy and secure system.
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <span class="footer-copy">&copy; 2026 AlumTrace Alumni Tracking System. All Rights Reserved.</span>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarOverlay = document.getElementById("sidebarOverlay");
            const body = document.body;

            // Handle Responsive & Desktop Sidebar Toggles
            sidebarToggle.addEventListener("click", function () {
                if (window.innerWidth < 992) {
                    body.classList.toggle("sidebar-mobile-show");
                } else {
                    body.classList.toggle("sidebar-collapsed");
                }
            });

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener("click", function () {
                    body.classList.remove("sidebar-mobile-show");
                });
            }
        });
    </script>
</body>
</html>
<?php
}