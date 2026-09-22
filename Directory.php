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
| Keep the current username logic exactly as-is
|--------------------------------------------------------------------------
*/
$userName = trim(
    ($_SESSION["first_name"] ?? "") . " " .
    ($_SESSION["middle_name"] ?? "") . " " .
    ($_SESSION["last_name"] ?? "")
);

if ($userName === "") {
    $userName = $_SESSION["user_name"] ?? "Alumni";
}

$userInitial = strtoupper(substr($userName, 0, 1));
$currentYear = date("Y");

/*
|--------------------------------------------------------------------------
| Profile picture helper
|--------------------------------------------------------------------------
*/
function getProfilePictureData(string $picturePath): array
{
    $picturePath = trim($picturePath);

    if ($picturePath === "") {
        return [
            "exists" => false,
            "url" => ""
        ];
    }

    $normalizedPath = str_replace(
        ["\\", "/"],
        DIRECTORY_SEPARATOR,
        $picturePath
    );

    $filePath = __DIR__ .
        DIRECTORY_SEPARATOR .
        ltrim($normalizedPath, DIRECTORY_SEPARATOR);

    if (!is_file($filePath)) {
        return [
            "exists" => false,
            "url" => ""
        ];
    }

    $browserPath = str_replace("\\", "/", $picturePath);

    return [
        "exists" => true,
        "url" => $browserPath . "?v=" . filemtime($filePath)
    ];
}

/*
|--------------------------------------------------------------------------
| Load current logged-in user's profile picture
|--------------------------------------------------------------------------
*/
$userProfilePicture = "";

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
        $userProfilePicture = $row["profile_picture"] ?? "";
    }

    mysqli_stmt_close($stmt);
}

$userPictureData = getProfilePictureData($userProfilePicture);
$hasPhoto = $userPictureData["exists"];
$profilePicture = $userPictureData["url"];

$search = trim($_GET["q"] ?? "");
$profiles = [];
$message = "";

if (isset($connection)) {
    $sql = "
SELECT
u.id AS user_id,
u.email,
COALESCE(NULLIF(p.first_name, ''), u.first_name) AS first_name,
COALESCE(NULLIF(p.middle_name, ''), u.middle_name) AS middle_name,
COALESCE(NULLIF(p.last_name, ''), u.last_name) AS last_name,
COALESCE(NULLIF(p.course, ''), u.course) AS course,
COALESCE(NULLIF(p.batch_year, ''), u.batch_year) AS batch_year,
p.city,
p.directory_bio,
p.profile_picture,
e.company_name,
e.job_title,
e.industry,
e.work_location
FROM users u
LEFT JOIN alumni_profiles p
ON p.email = u.email
LEFT JOIN employment_information e
ON e.id = (
SELECT e2.id
FROM employment_information e2
WHERE e2.email = u.email
AND e2.is_current = 1
ORDER BY e2.start_date DESC, e2.id DESC
LIMIT 1
)
WHERE COALESCE(u.role, '') <> 'admin'
AND COALESCE(p.directory_visible, 1) = 1
AND u.email <> ?
";

    if ($search !== "") {
        $sql .= "
AND (
COALESCE(p.first_name, u.first_name) LIKE ?
OR COALESCE(p.middle_name, u.middle_name) LIKE ?
OR COALESCE(p.last_name, u.last_name) LIKE ?
OR COALESCE(p.course, u.course) LIKE ?
OR COALESCE(p.batch_year, u.batch_year) LIKE ?
OR COALESCE(p.city, '') LIKE ?
OR COALESCE(p.directory_bio, '') LIKE ?
OR COALESCE(e.company_name, '') LIKE ?
OR COALESCE(e.job_title, '') LIKE ?
OR COALESCE(e.industry, '') LIKE ?
OR COALESCE(e.work_location, '') LIKE ?
)
";
    }

    $sql .= "
ORDER BY
COALESCE(NULLIF(p.last_name, ''), u.last_name),
COALESCE(NULLIF(p.first_name, ''), u.first_name)
";

    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        if ($search !== "") {
            $like = "%" . $search . "%";

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssss",
                $userEmail,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like
            );
        } else {
            mysqli_stmt_bind_param($stmt, "s", $userEmail);
        }

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $row["full_name"] = trim(
                ($row["first_name"] ?? "") . " " .
                ($row["middle_name"] ?? "") . " " .
                ($row["last_name"] ?? "")
            );

            if ($row["full_name"] === "") {
                $row["full_name"] = "Alumni";
            }

            $row["picture_data"] = getProfilePictureData(
                $row["profile_picture"] ?? ""
            );

            $profiles[] = $row;
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = "Unable to load the alumni directory.";
    }
} else {
    $message = "Database connection is unavailable.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory / AlumTrace</title>
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
                <li><a href="AlumniProfile.php"><i class="fa-solid fa-user-pen"></i> <span class="nav-label">My Profile</span></a></li>
                <li><a href="EmploymentInformation.php"><i class="fa-solid fa-briefcase"></i> <span class="nav-label">Employment</span></a></li>
                <li><a href="EducationAndCertification.php"><i class="fa-solid fa-certificate"></i> <span class="nav-label">Certifications</span></a></li>
                <li><a href="AlumniDirectory.php" aria-current="page"><i class="fa-solid fa-address-book"></i> <span class="nav-label">Alumni Directory</span></a></li>
                <li><a href="SurveyForms.php"><i class="fa-solid fa-square-poll-horizontal"></i> <span class="nav-label">Survey Forms</span></a></li>
                <li><a href="EventAndAnnouncement.php"><i class="fa-solid fa-bullhorn"></i> <span class="nav-label">Events &amp; News</span></a></li>
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
                <h1>Alumni Directory</h1>
            </div>
            <div class="topbar-right">
                <div class="user-meta">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
                <?php if ($hasPhoto): ?>
                    <img class="avatar-sm" src="<?php echo htmlspecialchars($profilePicture); ?>" alt="">
                <?php else: ?>
                    <div class="avatar-sm fallback" aria-hidden="true"><?php echo htmlspecialchars($userInitial); ?></div>
                <?php endif; ?>
                <a href="Logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <main class="content" id="main">
            <div class="directory-hero">
                <h2>Alumni Directory</h2>
                <p>Browse registered alumni, discover professional connections, and explore available public profiles.</p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="panel">
                    <div class="muted"><?php echo htmlspecialchars($message); ?></div>
                </div>
            <?php endif; ?>

            <div class="panel">
                <div class="panel-title">
                    <i class="fa-solid fa-magnifying-glass"></i> Search Alumni
                </div>
                <form method="get" class="search-form">
                    <input class="input" style="max-width: 620px;" type="search" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, course, batch, city, company, or position">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                    <?php if ($search !== ""): ?>
                        <a class="btn btn-ghost" href="AlumniDirectory.php">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="panel" style="margin-top: 20px;">
                <div class="panel-title">
                    <i class="fa-solid fa-address-book"></i> Alumni Directory
                </div>
                <div class="directory-summary">
                    <?php echo count($profiles); ?> public profile<?php echo count($profiles) === 1 ? "" : "s"; ?> found.
                </div>

                <?php if (empty($profiles)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-user-group"></i>
                        <p>No public alumni profiles are available yet.</p>
                    </div>
                <?php else: ?>
                    <div class="directory-grid">
                        <?php foreach ($profiles as $profile): ?>
                            <?php
                            $profileName = $profile["full_name"];
                            $profileInitial = strtoupper(substr($profileName, 0, 1));
                            $profilePictureData = $profile["picture_data"];
                            ?>
                            <article class="directory-card">
                                <div class="top">
                                    <div class="directory-avatar">
                                        <?php if ($profilePictureData["exists"]): ?>
                                            <img src="<?php echo htmlspecialchars($profilePictureData["url"], ENT_QUOTES, "UTF-8"); ?>" alt="<?php echo htmlspecialchars($profileName, ENT_QUOTES, "UTF-8"); ?>">
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($profileInitial, ENT_QUOTES, "UTF-8"); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4><?php echo htmlspecialchars($profileName); ?></h4>
                                        <div class="muted"><?php echo htmlspecialchars($profile["course"] ?: "Course not provided"); ?></div>
                                    </div>
                                </div>
                                <div class="directory-tags-wrapper">
                                    <?php if (!empty($profile["batch_year"])): ?>
                                        <span class="tag"><i class="fa-solid fa-graduation-cap"></i> Batch <?php echo htmlspecialchars($profile["batch_year"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($profile["city"])): ?>
                                        <span class="tag"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($profile["city"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($profile["job_title"])): ?>
                                        <span class="tag"><i class="fa-solid fa-briefcase"></i> <?php echo htmlspecialchars($profile["job_title"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($profile["company_name"])): ?>
                                        <span class="tag"><i class="fa-solid fa-building"></i> <?php echo htmlspecialchars($profile["company_name"]); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($profile["directory_bio"])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($profile["directory_bio"])); ?></p>
                                <?php endif; ?>
                                <a class="btn-profile-action" href="mailto:<?php echo htmlspecialchars($profile["email"]); ?>">
                                    <i class="fa-solid fa-envelope"></i> Contact Alumni
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img src="images/Badge.png" alt="">
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
                            <li><a href="AlumniProfile.php">My Profile</a></li>
                            <li><a href="EmploymentInformation.php">Employment</a></li>
                            <li><a href="AlumniDirectory.php">Alumni Directory</a></li>
                            <li><a href="SurveyForms.php">Survey Forms</a></li>
                            <li><a href="EventAndAnnouncement.php">Events &amp; Announcements</a></li>
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