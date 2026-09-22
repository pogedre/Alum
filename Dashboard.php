<?php
// Session check or user data initialization can go here
session_start();
// Example placeholder data if not already set in session
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
$userInitial = strtoupper(substr($userName, 0, 1));
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
    <title>AlumTrace / User Dashboard</title>
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/alumtrace.css">
</head>
<body>

    <a class="skip-link" href="#main">Skip to main content</a>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a class="brand-link" href="Dashboard.php">
                <img src="images/Seal.png" alt="AlumTrace logo">
                <span>AlumTrace</span>
            </a>
            <button type="button" class="icon-btn" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true" aria-label="Hide sidebar">
                <i class="fa-solid fa-angles-left"></i>
            </button>
        </div>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <ul>
                <li><a href="Dashboard.php" aria-current="page" class="active"><i class="fa-solid fa-house"></i> <span class="nav-label">Dashboard</span></a></li>
                <li><a href="Profile.php"><i class="fa-solid fa-user-pen"></i> <span class="nav-label">My Profile</span></a></li>
                <li><a href="Employment.php"><i class="fa-solid fa-briefcase"></i> <span class="nav-label">Employment</span></a></li>
                <li><a href="Education.php"><i class="fa-solid fa-certificate"></i> <span class="nav-label">Certifications</span></a></li>
                <li><a href="Directory.php"><i class="fa-solid fa-address-book"></i> <span class="nav-label">Alumni Directory</span></a></li>
                <li><a href="Survey.php"><i class="fa-solid fa-square-poll-horizontal"></i> <span class="nav-label">Survey Forms</span></a></li>
                <li><a href="Events.php"><i class="fa-solid fa-bullhorn"></i> <span class="nav-label">Events &amp; News</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="icon-btn header-toggle" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true" aria-label="Show sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            <div class="topbar-right">
                <div class="user-meta">
                    <div class="name"><?php echo htmlspecialchars($userName ?? 'Alumni'); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail ?? ''); ?></div>
                </div>
                <div class="avatar-sm fallback" aria-hidden="true"><?php echo htmlspecialchars($userInitial ?: 'A'); ?></div>
                <a href="logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <main class="content" id="main">
            <div class="welcome-banner">
                <h2>Welcome back, <?php echo htmlspecialchars($userName ?? 'Alumni'); ?>!</h2>
                <p>Manage your professional records, update career milestones, and stay connected with the institutional network seamlessly.</p>
            </div>

            <div class="dashboard-grid">
                <div>
                    <div class="section-header">
                        <h3>System Features</h3>
                    </div>
                    <div class="features-grid">
                        <a href="Profile.php" class="feature-card featured">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper"><i class="fa-solid fa-id-card"></i></div>
                                    <span class="card-badge">Primary Record</span>
                                </div>
                                <h4>Alumni Profile</h4>
                                <p>View and update your personal contact details, residential address, profile credentials, and academic background records.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Manage Profile Details</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                        <a href="Employment.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper"><i class="fa-solid fa-briefcase"></i></div>
                                    <span class="card-badge">Career</span>
                                </div>
                                <h4>Employment Information</h4>
                                <p>Add and update your current employment status, company info, and professional career growth.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Update Employment</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                        <a href="Education.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper"><i class="fa-solid fa-graduation-cap"></i></div>
                                    <span class="card-badge">Credentials</span>
                                </div>
                                <h4>Education &amp; Certifications</h4>
                                <p>Manage post-graduate educational background, professional licenses, and special certifications.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>View Certifications</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                        <a href="Directory.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper"><i class="fa-solid fa-users-viewfinder"></i></div>
                                    <span class="card-badge">Network</span>
                                </div>
                                <h4>Alumni Directory</h4>
                                <p>Browse registered batchmates, network with peers, and check available professional profiles.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Explore Directory</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                        <a href="Survey.php" class="feature-card">
                            <div>
                                <div class="card-top-content">
                                    <div class="card-icon-wrapper"><i class="fa-solid fa-clipboard-list"></i></div>
                                    <span class="card-badge">Feedback</span>
                                </div>
                                <h4>Survey Forms</h4>
                                <p>Answer tracer surveys, employment feedback forms, and provide updated graduate metrics.</p>
                            </div>
                            <div class="card-footer-action">
                                <span>Take Survey</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="right-sidebar">
                    <div class="widget-card">
                        <div class="widget-header">
                            <h4><i class="fa-regular fa-calendar"></i> Calendar</h4>
                        </div>
                        <div class="calendar-nav"><?php echo "$monthName $currentYearNum"; ?></div>
                        <div class="calendar-grid">
                            <div class="calendar-day-name">Su</div>
                            <div class="calendar-day-name">Mo</div>
                            <div class="calendar-day-name">Tu</div>
                            <div class="calendar-day-name">We</div>
                            <div class="calendar-day-name">Th</div>
                            <div class="calendar-day-name">Fr</div>
                            <div class="calendar-day-name">Sa</div>
                            <?php
                            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                echo '<div class="calendar-cell empty"></div>';
                            }
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $isToday = ($day == $currentDayNum) ? 'today' : '';
                                $hasEvent = ($day == 18 || $day == 25) ? 'has-event' : '';
                                echo '<div class="calendar-cell ' . $isToday . ' ' . $hasEvent . '">' . $day . '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="widget-card">
                        <div class="widget-header">
                            <h4><i class="fa-solid fa-bullhorn"></i> Upcoming Events</h4>
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
                                    <h5>Career Webinar &amp; Networking</h5>
                                    <p>Online Zoom Conference session</p>
                                </div>
                            </div>
                        </div>
                        <a href="Events.php" class="view-all-events">View All Events &amp; Announcements &rarr;</a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img src="images/Seal.png" alt="AlumTrace">
                            <div>
                                <h3>AlumTrace</h3>
                                <span>Alumni Tracking System</span>
                            </div>
                        </div>
                        <p>AlumTrace helps alumni manage their profile, employment, education, surveys, and events in one easy and secure system.</p>
                    </div>
                    <div class="footer-col">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="Dashboard.php">Dashboard</a></li>
                            <li><a href="Profile.php">My Profile</a></li>
                            <li><a href="Employment.php">Employment</a></li>
                            <li><a href="Directory.php">Alumni Directory</a></li>
                            <li><a href="Survey.php">Survey Forms</a></li>
                            <li><a href="Events.php">Events &amp; Announcements</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Support</h4>
                        <ul>
                            <li><a href="#">Contact Administrator</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
                <hr class="footer-divider">
                <div class="footer-bottom">
                    <div>&copy; 2026 AlumTrace Alumni Tracking System. All Rights Reserved.</div>
                    <nav aria-label="Site links">
                        <a href="index.php">Home</a>
                        <a href="about.php">About Us</a>
                        <a href="faqs.php">FAQs</a>
                        <a href="privacy.php">Data Privacy</a>
                        <a href="terms.php">Terms &amp; Conditions</a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alumtrace.js"></script>
</body>
</html>