<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: Login.php");
    exit;
}

if (($_SESSION["role"] ?? "") === "admin") {
    header("Location: AdminDashboard.php");
    exit;
}

require_once "connection.php";

$userEmail = $_SESSION["user_email"] ?? "";

if ($userEmail === "") {
    header("Location: Login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| User information
|--------------------------------------------------------------------------
*/
$firstName = $_SESSION["first_name"] ?? "";
$middleName = $_SESSION["middle_name"] ?? "";
$lastName = $_SESSION["last_name"] ?? "";

$userName = trim("$firstName $middleName $lastName");

if ($userName === "") {
    $userName = $_SESSION["user_name"] ?? "Alumni";
}

$userInitial = strtoupper(substr($userName, 0, 1));
$currentYear = date("Y");

/*
|--------------------------------------------------------------------------
| Load profile picture
|--------------------------------------------------------------------------
*/
$profilePicture = "";
$profilePictureUrl = "";
$hasProfilePicture = false;

$stmt = mysqli_prepare(
    $connection,
    "SELECT profile_picture
FROM alumni_profiles
WHERE email = ?
LIMIT 1"
);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $userEmail);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $profilePicture = trim($row["profile_picture"] ?? "");
    }

    mysqli_stmt_close($stmt);
}

if ($profilePicture !== "") {
    $normalizedPicturePath = str_replace(
        ["\\", "/"],
        DIRECTORY_SEPARATOR,
        $profilePicture
    );

    $profilePictureFile = __DIR__ .
        DIRECTORY_SEPARATOR .
        ltrim($normalizedPicturePath, DIRECTORY_SEPARATOR);

    if (is_file($profilePictureFile)) {
        $hasProfilePicture = true;

        $profilePictureUrl = str_replace(
            "\\",
            "/",
            $profilePicture
        );

        $profilePictureUrl .= "?v=" . filemtime($profilePictureFile);
    }
}

$events = [];
$message = "";
$messageType = "";

/*
|--------------------------------------------------------------------------
| Database processing & Fetching events (No hardcoded data)
|--------------------------------------------------------------------------
*/
if (isset($connection)) {
    $stmt = mysqli_prepare(
        $connection,
        "SELECT
id,
title,
event_date,
end_date,
event_time,
location,
registration_url,
contact_info,
description,
category
FROM events_announcements
WHERE active = 1
AND (
event_date >= CURDATE()
OR (
end_date IS NOT NULL
AND end_date >= CURDATE()
)
)
ORDER BY event_date ASC, id ASC"
    );

    if ($stmt) {
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = "Unable to load events and announcements.";
        $messageType = "danger";
    }
} else {
    $message = "Database connection is unavailable.";
    $messageType = "danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events & Announcements / AlumTrace</title>
    <link rel="icon" type="image/png" href="images/Badge.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/alumtrace.css">
</head>
<body>
    <a class="skip-link" href="#main">Skip to main content</a>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a class="brand-link" href="Dashboard.php">
                <img src="images/Badge.png" alt="AlumTrace logo">
                <span>AlumTrace</span>
            </a>
            <button type="button" class="icon-btn" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true" aria-label="Hide sidebar">
                <i class="fa-solid fa-angles-left"></i>
            </button>
        </div>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <ul>
                <li><a href="Dashboard.php"><i class="fa-solid fa-house"></i> <span class="nav-label">Dashboard</span></a></li>
                <li><a href="Profile.php"><i class="fa-solid fa-user-pen"></i> <span class="nav-label">My Profile</span></a></li>
                <li><a href="Employment.php"><i class="fa-solid fa-briefcase"></i> <span class="nav-label">Employment</span></a></li>
                <li><a href="Education.php"><i class="fa-solid fa-certificate"></i> <span class="nav-label">Certifications</span></a></li>
                <li><a href="Directory.php"><i class="fa-solid fa-address-book"></i> <span class="nav-label">Alumni Directory</span></a></li>
                <li><a href="Survey.php"><i class="fa-solid fa-square-poll-horizontal"></i> <span class="nav-label">Survey Forms</span></a></li>
                <li><a href="Events.php" aria-current="page"><i class="fa-solid fa-bullhorn"></i> <span class="nav-label">Events &amp; News</span></a></li>
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
                <h1>Events &amp; Announcements</h1>
            </div>
            <div class="topbar-right">
                <div class="user-meta">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
                <?php if ($hasProfilePicture): ?>
                    <img class="avatar-sm" src="<?php echo htmlspecialchars($profilePictureUrl, ENT_QUOTES, "UTF-8"); ?>" alt="Profile Picture">
                <?php else: ?>
                    <div class="avatar-sm fallback" aria-hidden="true"><?php echo htmlspecialchars($userInitial); ?></div>
                <?php endif; ?>
                <a href="Logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <main class="content" id="main">
            <?php if ($message !== ""): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="page-head">
                <h2>Events &amp; Announcements</h2>
                <p>Stay updated with alumni activities, career events, webinars, and institutional announcements.</p>
            </div>

            <?php if (empty($events)): ?>
                <div class="panel">
                    <p class="muted">There are no upcoming events or announcements at this time.</p>
                </div>
            <?php else: ?>
                <div class="event-list">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $eventDate = new DateTime($event["event_date"]);
                        $eventEndDate = !empty($event["end_date"]) ? new DateTime($event["end_date"]) : null;
                        $dateLabel = $eventDate->format("M");
                        $dayLabel = $eventDate->format("d");
                        $dateRange = $eventDate->format("F j, Y");
                        if ($eventEndDate) {
                            $dateRange .= " - " . $eventEndDate->format("F j, Y");
                        }
                        ?>
                        <article class="event-card">
                            <div class="event-date">
                                <span><?php echo htmlspecialchars($dateLabel); ?></span>
                                <strong><?php echo htmlspecialchars($dayLabel); ?></strong>
                            </div>
                            <div class="event-content">
                                <span class="tag"><?php echo htmlspecialchars($event["category"] ?: "Event"); ?></span>
                                <h3><?php echo htmlspecialchars($event["title"]); ?></h3>
                                <div class="event-meta">
                                    <?php if (!empty($event["event_time"])): ?>
                                        <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($event["event_time"]); ?></span>
                                    <?php endif; ?>
                                    <span><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($dateRange); ?></span>
                                    <?php if (!empty($event["location"])): ?>
                                        <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event["location"]); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($event["description"])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($event["description"])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($event["contact_info"])): ?>
                                    <p style="margin-top: 10px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-circle-info" style="color: var(--accent);"></i>
                                        <?php echo htmlspecialchars($event["contact_info"]); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($event["registration_url"])): ?>
                                    <div class="event-actions">
                                        <a class="btn-register" href="<?php echo htmlspecialchars($event["registration_url"]); ?>" target="_blank" rel="noopener noreferrer">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Register / Learn More
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img src="images/Badge.png" alt="AlumTrace logo">
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
                        <h4>Support &amp; Help</h4>
                        <ul>
                            <li><a href="#">Contact Administrator</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                            <li><a href="Logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
                <hr class="footer-divider">
                <div class="footer-bottom">
                    <div>&copy; 2026 AlumTrace Alumni Tracking System. All Rights Reserved.</div>
                    <nav aria-label="Site links">
                        <a href="index.php">Home</a>
                        <a href="AboutUs.php">About Us</a>
                        <a href="FAQs.php">FAQs</a>
                        <a href="DataPrivacy.php">Data Privacy</a>
                        <a href="Terms.php">Terms &amp; Conditions</a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alumtrace.js"></script>
</body>
</html>