<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: Login.php");
    exit;
}
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/connection.php";

// Pull the correct session variables set by LoginProcess.php
$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$currentYear = date("Y");

// Dynamic Calendar Generation Variables
$monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$currentMonthNum = intval(date("m"));
$currentYearNum = intval(date("Y"));
$currentDayNum = intval(date("d"));
$monthName = $monthNames[$currentMonthNum - 1];

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonthNum, $currentYearNum);
$firstDayOfWeek = date("w", strtotime("$currentYearNum-$currentMonthNum-01"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlumTrace / Admin Dashboard</title>
    <!-- Google Fonts & FontAwesome for Modern Icons -->

    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #0f5132;
            --accent-green: #198754;
            --light-green-bg: #f4f9f5;
            --sidebar-width: 260px;
            --bg-color: #f8f9fa;
            --text-main: #212529;
            --text-muted: #6c757d;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --shadow-sm: 0 4px 12px rgba(0,0,0,0.03);
            --shadow-md: 0 12px 30px rgba(15, 81, 50, 0.08);
            --shadow-hover: 0 16px 36px rgba(15, 81, 50, 0.16);
            --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        aside {
            width: var(--sidebar-width);
            background-color: var(--card-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Collapsed Sidebar State */
        body.sidebar-collapsed aside {
            transform: translateX(-100%);
        }

        .sidebar-brand {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-brand-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .sidebar-brand h2 {
            font-size: 1.15rem;
            color: var(--primary-green);
            font-weight: 700;
        }

        /* Sidebar Toggle Button inside Brand */
        .btn-sidebar-toggle {
            background-color: var(--light-green-bg);
            border: 1px solid #d1e7dd;
            color: var(--primary-green);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .btn-sidebar-toggle:hover {
            background-color: var(--primary-green);
            color: white;
            transform: scale(1.05);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 6px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--light-green-bg);
            color: var(--primary-green);
        }

        .sidebar-menu a i {
            font-size: 1.1rem;
            width: 20px;
        }

        /* Main Wrapper */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body.sidebar-collapsed .main-wrapper {
            margin-left: 0;
        }

        /* Top Header with System Green Theme */
        header {
            height: 76px;
            background-color: var(--primary-green);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: 0 4px 12px rgba(15, 81, 50, 0.15);
        }

        .header-left-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Header Toggle Button */
        .btn-header-toggle {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
        }

        body.sidebar-collapsed .btn-header-toggle {
            display: flex;
        }

        .btn-header-toggle:hover {
            background-color: rgba(255, 255, 255, 0.25);
            border-color: #ffffff;
        }

        .header-title-wrapper h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ebf2ed;
            letter-spacing: -0.2px;
        }

        .user-profile-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            text-align: right;
        }

        .user-info .name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
        }

        .user-info .email {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.75);
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #ffffff;
            color: var(--primary-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .avatar-circle:hover {
            transform: scale(1.05);
        }

        .btn-logout {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        /* Content Body Layout */
        .content-body {
            padding: 32px;
            width: 100%;
            flex: 1;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #0f5132 0%, #198754 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: white;
            margin-bottom: 32px;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .welcome-banner:hover {
            box-shadow: 0 16px 35px rgba(15, 81, 50, 0.15);
        }

        .welcome-banner h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-banner p {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 800px;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            right: -30px;
            bottom: -50px;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Main Dashboard Grid Structure */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            align-items: start;
            width: 100%;
        }

        .section-header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-header h3 {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Feature Card */
        .feature-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 28px;
            text-decoration: none;
            color: var(--text-main);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .feature-card.featured {
            grid-column: span 2;
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: transparent;
            transition: var(--transition);
        }

        /* Dark Green Hover State with Gold Accent Indicator matching sample image */
        .feature-card:hover {
            background-color: #072e1c !important;
            border-color: #072e1c !important;
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            color: #ffffff !important;
        }

        .feature-card:hover::before {
            background-color: #ffc107; 
        }

        .card-top-content {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-icon-wrapper {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--light-green-bg) 0%, #e2eee5 100%);
            color: var(--primary-green);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            transition: var(--transition);
        }

        .card-badge {
            font-size: 0.72rem;
            font-weight: 600;
            background-color: #e9ecef;
            color: var(--text-muted);
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .feature-card:hover .card-badge {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }

        .feature-card.featured .card-badge {
            background-color: var(--primary-green);
            color: white;
        }

        .feature-card h4 {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 8px;
            transition: var(--transition);
        }

        .feature-card:hover h4 {
            color: #ffffff !important;
        }

        .feature-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
            transition: var(--transition);
        }

        .feature-card:hover p {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .card-footer-action {
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--accent-green);
            border-top: 1px solid var(--border-color);
            padding-top: 16px;
            transition: var(--transition);
        }

        .feature-card:hover .card-footer-action {
            border-top-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }

        .card-footer-action span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .card-footer-action i {
            transition: transform 0.2s ease;
        }

        .feature-card:hover .card-footer-action i {
            transform: translateX(6px);
            color: #ffc107 !important;
        }

        /* Right Sidebar Widgets */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
            width: 100%;
        }

        .widget-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 22px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .widget-card:hover {
            box-shadow: var(--shadow-md);
            border-color: #cbd5e1;
        }

        .widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .widget-header h4 {
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--primary-green);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .calendar-nav {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 12px;
            text-align: center;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            text-align: center;
        }

        .calendar-day-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            padding-bottom: 6px;
        }

        .calendar-cell {
            font-size: 0.8rem;
            padding: 6px 0;
            border-radius: 6px;
            color: var(--text-main);
            transition: var(--transition);
        }

        .calendar-cell:hover:not(.empty):not(.today) {
            background-color: var(--light-green-bg);
            color: var(--primary-green);
            font-weight: 600;
        }

        .calendar-cell.empty {
            color: transparent;
        }

        .calendar-cell.today {
            background-color: var(--primary-green);
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(15, 81, 50, 0.3);
        }

        .calendar-cell.has-event {
            background-color: var(--light-green-bg);
            color: var(--primary-green);
            font-weight: 600;
            border: 1px dashed var(--accent-green);
        }

        .events-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .event-item {
            display: flex;
            gap: 12px;
            padding: 10px;
            border-radius: 10px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .event-item:hover {
            border-color: var(--accent-green);
            background-color: var(--light-green-bg);
            transform: translateX(3px);
        }

        .event-date-box {
            background-color: var(--light-green-bg);
            color: var(--primary-green);
            border-radius: 8px;
            padding: 6px 10px;
            text-align: center;
            min-width: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .event-date-box .month {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .event-date-box .day {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .event-details h5 {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .event-details p {
            font-size: 0.76rem;
            color: var(--text-muted);
            line-height: 1.3;
        }

        .view-all-events {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--accent-green);
            text-decoration: none;
            transition: var(--transition);
        }

        .view-all-events:hover {
            text-decoration: underline;
            color: var(--primary-green);
        }

        /* Footer Design */
        .site-footer {
            background-color: #042416;
            color: #ffffff;
            padding: 50px 60px 30px 60px;
            margin-top: 60px;
            width: 100%;
        }

        .footer-content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .footer-brand-col {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .footer-logo-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .footer-logo-row img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .footer-logo-text h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .footer-logo-text span {
            font-size: 0.85rem;
            color: #ffc107;
            font-weight: 600;
        }

        .footer-brand-col p {
            font-size: 0.88rem;
            color: #cbd5e1;
            line-height: 1.5;
            max-width: 400px;
        }

        .footer-links-col h4 {
            font-size: 1rem;
            font-weight: 700;
            color: #ffc107;
            margin-bottom: 16px;
        }

        .footer-links-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-links-col ul li a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.88rem;
            transition: var(--transition);
        }

        .footer-links-col ul li a:hover {
            color: #ffffff;
            padding-left: 4px;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 40px 0 24px 0;
        }

        .footer-bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: #94a3b8;
        }

        .footer-bottom-links {
            display: flex;
            gap: 24px;
        }

        .footer-bottom-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-bottom-links a:hover {
            color: #ffffff;
        }

        /* Responsive Layout */
        @media(max-width: 1100px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .feature-card.featured {
                grid-column: span 1;
            }
        }

        @media(max-width: 900px) {
            aside {
                transform: translateX(-100%);
            }
            body.sidebar-collapsed aside {
                transform: translateX(-100%);
            }
            body:not(.sidebar-collapsed) aside {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0 !important;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .footer-content-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .footer-bottom-row {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .footer-bottom-links {
                justify-content: center;
                flex-wrap: wrap;
                gap: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar Component -->
    <aside>
        <div class="sidebar-brand">
            <div class="sidebar-brand-left">
                <img src="images/Seal.png" alt="KLD Logo">
                <h2>AlumTrace</h2>
            </div>
            <button type="button" class="btn-sidebar-toggle" id="sidebarToggle" title="Hide Sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="AdminDashboard.php" class="active"><i class="fa-solid fa-house"></i> Admin Dashboard</a></li>
            <li><a href="ManageAlumni.php"><i class="fa-solid fa-users-gear"></i> Manage Alumni</a></li>
            <li><a href="ManageSurveys.php"><i class="fa-solid fa-square-poll-vertical"></i> Tracer Surveys</a></li>
            <li><a href="ManageAnnouncements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
            <li><a href="Reports.php"><i class="fa-solid fa-chart-pie"></i> System Reports</a></li>
            <li><a href="AdminSettings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
        </ul>
    </aside>

    <!-- Main Container -->
    <div class="main-wrapper">
        <!-- Top Header Bar with System Green Theme -->
        <header>
            <div class="header-left-group">
                <!-- Toggle Button inside header, appears only when sidebar is collapsed -->
                <button type="button" class="btn-header-toggle" id="headerToggle" title="Show Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-title-wrapper">
                    <h1>Administrator Control Panel</h1>
                </div>
            </div>
            <div class="user-profile-section">
                <div class="user-info">
                    <div class="name"><?php echo htmlspecialchars($adminName); ?></div>
                    <div class="email"><?php echo htmlspecialchars($adminEmail); ?></div>
                </div>
                <div class="avatar-circle">
                    <?php echo htmlspecialchars($adminInitial); ?>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="content-body">
            
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2>Welcome back, Buseng!</h2>
                <p>HUY BUSENG!!!, WALA KANIN?, PUTANG INANG HAYUP NA YAN!! DAMI DAMI NAMIN NAGBABAYAD DITO! BULOK SERBISYO NIYO HAYUP!</p>
            </div>

            <!-- Main Dashboard Grid Layout -->
            <div class="dashboard-grid">
                
                <!-- Left Column: Admin Management Features -->
                <div>
                    <div class="section-header">
                        <h3>Management Controls</h3>
                    </div>

                    <div class="features-grid">
                        <!-- Featured Card 1: Manage Alumni Accounts -->
                        <a href="ManageAlumni.php" class="feature-card featured">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper">
                                        <i class="fa-solid fa-users-gear"></i>
                                    </div>
                                    <span class="card-badge">Core Management</span>
                                </div>
                                <h4>Manage Alumni Accounts</h4>
                                <p>Review registered alumni profiles, approve new registrations, update statuses, and search user database records.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Go to User Directory</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>

                        <!-- Card 2: Tracer Surveys -->
                        <a href="ManageSurveys.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper">
                                        <i class="fa-solid fa-square-poll-vertical"></i>
                                    </div>
                                    <span class="card-badge">Feedback</span>
                                </div>
                                <h4>Tracer Surveys</h4>
                                <p>Deploy feedback questions, review completed graduate survey forms, and analyze institutional metrics.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Manage Surveys</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>

                        <!-- Card 3: Announcements & Events -->
                        <a href="ManageAnnouncements.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>
                                    <span class="card-badge">Broadcast</span>
                                </div>
                                <h4>Announcements & Events</h4>
                                <p>Publish news updates, homecoming timelines, and event alerts across the alumni portal network.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Publish Post</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>

                        <!-- Card 4: System Reports -->
                        <a href="Reports.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <span class="card-badge">Analytics</span>
                                </div>
                                <h4>System Reports</h4>
                                <p>Generate employment analytics, graphical batch data breakdowns, and export professional tracking insights.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>View Reports</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>

                        <!-- Card 5: Admin Settings -->
                        <a href="AdminSettings.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper">
                                        <i class="fa-solid fa-sliders"></i>
                                    </div>
                                    <span class="card-badge">Configuration</span>
                                </div>
                                <h4>System Settings</h4>
                                <p>Configure system preferences, security parameters, database backups, and administrator credentials.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Open Settings</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Right Sidebar Column: Calendar & Upcoming Admin Tasks -->
                <div class="right-sidebar">
                    
                    <!-- Calendar Widget -->
                    <div class="widget-card">
                        <div class="widget-header">
                            <h4><i class="fa-regular fa-calendar"></i> Calendar</h4>
                        </div>
                        <div class="calendar-nav">
                            <?php echo "$monthName $currentYearNum"; ?>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-day-name">Su</div>
                            <div class="calendar-day-name">Mo</div>
                            <div class="calendar-day-name">Tu</div>
                            <div class="calendar-day-name">We</div>
                            <div class="calendar-day-name">Th</div>
                            <div class="calendar-day-name">Fr</div>
                            <div class="calendar-day-name">Sa</div>
                            
                            <?php
                            // Render empty cells for offset
                            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                echo '<div class="calendar-cell empty"></div>';
                            }
                            // Render days of the month
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $isToday = ($day == $currentDayNum) ? 'today' : '';
                                // Highlight example admin schedule dates
                                $hasEvent = ($day == 18 || $day == 25) ? 'has-event' : '';
                                echo '<div class="calendar-cell ' . $isToday . ' ' . $hasEvent . '">' . $day . '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Upcoming Admin Tasks Widget -->
                    <div class="widget-card">
                        <div class="widget-header">
                            <h4><i class="fa-solid fa-list-check"></i> Admin Schedule</h4>
                        </div>
                        <div class="events-list">
                            <div class="event-item">
                                <div class="event-date-box">
                                    <span class="month">Sep</span>
                                    <span class="day">18</span>
                                </div>
                                <div class="event-details">
                                    <h5>Annual Alumni Homecoming</h5>
                                    <p>Main University Auditorium, 9:00 AM</p>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-date-box">
                                    <span class="month">Sep</span>
                                    <span class="day">25</span>
                                </div>
                                <div class="event-details">
                                    <h5>Quarterly Survey Deadline</h5>
                                    <p>Close data collection for graduating metrics</p>
                                </div>
                            </div>
                        </div>
                        <a href="ManageAnnouncements.php" class="view-all-events">View All Admin Schedules &rarr;</a>
                    </div>

                </div>

            </div>

        </div>

        <!-- Footer Design -->
        <footer class="site-footer">
            <div class="footer-content-grid">
                <!-- Column 1: Brand Info -->
                <div class="footer-brand-col">
                    <div class="footer-logo-row">
                        <img src="images/Seal.png" alt="KLD Logo">
                        <div class="footer-logo-text">
                            <h3>AlumTrace</h3>
                            <span>Administrator Control Panel</span>
                        </div>
                    </div>
                    <p>A centralized administrative platform for monitoring alumni accounts, managing tracer survey responses, generating reports, and tracking campus events.</p>
                </div>

                <!-- Column 2: Management Links -->
                <div class="footer-links-col">
                    <h4>Admin System Links</h4>
                    <ul>
                        <li><a href="ManageAlumni.php">Manage Alumni</a></li>
                        <li><a href="ManageSurveys.php">Tracer Surveys</a></li>
                        <li><a href="ManageAnnouncements.php">Announcements</a></li>
                    </ul>
                </div>

                <!-- Column 3: Secondary Links -->
                <div class="footer-links-col">
                    <ul>
                        <li style="margin-top: 24px;"><a href="Reports.php">System Reports</a></li>
                        <li><a href="AdminSettings.php">Admin Settings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>

            <hr class="footer-divider">

            <div class="footer-bottom-row">
                <div>&copy; <?php echo $currentYear; ?> AlumTrace. All Rights Reserved.</div>
                <div class="footer-bottom-links">
                    <a href="AdminDashboard.php">Dashboard</a>
                    <a href="ManageAlumni.php">Users</a>
                    <a href="Reports.php">Reports</a>
                    <a href="AdminSettings.php">Settings</a>
                </div>
            </div>
        </footer>
    </div>
    <!-- Bootstrap 5 JavaScript Bundle CDN -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript for Sidebar Toggle Functionality -->
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const headerToggle = document.getElementById('headerToggle');
        const bodyElement = document.body;

        // Check local storage for previous preference
        if (localStorage.getItem('adminSidebarState') === 'collapsed') {
            bodyElement.classList.add('sidebar-collapsed');
        }

        function toggleSidebar() {
            bodyElement.classList.toggle('sidebar-collapsed');
            
            // Save preference to localStorage
            if (bodyElement.classList.contains('sidebar-collapsed')) {
                localStorage.setItem('adminSidebarState', 'collapsed');
            } else {
                localStorage.setItem('adminSidebarState', 'expanded');
            }
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        headerToggle.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
