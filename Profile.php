<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: Login.php");
    exit;
}

if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
    header("Location: AdminDashboard.php");
    exit;
}

require_once "connection.php";

$userEmail = $_SESSION["user_email"] ?? "";

if ($userEmail === "") {
    header("Location: Login.php");
    exit;
}

$message = "";
$messageType = "";

$user = [
    "id" => "",
    "first_name" => "",
    "middle_name" => "",
    "last_name" => "",
    "student_id" => "",
    "course" => "",
    "batch_year" => "",
    "email" => $userEmail
];

$birthdate = "";
$phone = "";
$address = "";
$city = "";
$gender = "";
$civilStatus = "";
$profilePicture = "";

/*
|--------------------------------------------------------------------------
| Fetch basic user information from users table
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $connection,
    "SELECT id, first_name, middle_name, last_name, student_id, course, batch_year
     FROM users
     WHERE email = ?
     LIMIT 1"
);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $userEmail);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $user["id"] = $row["id"] ?? "";
        $user["first_name"] = $row["first_name"] ?? "";
        $user["middle_name"] = $row["middle_name"] ?? "";
        $user["last_name"] = $row["last_name"] ?? "";
        $user["student_id"] = $row["student_id"] ?? "";
        $user["course"] = $row["course"] ?? "";
        $user["batch_year"] = $row["batch_year"] ?? "";
    }

    mysqli_stmt_close($stmt);
}

/*
|--------------------------------------------------------------------------
| Ensure profile upload directory exists
|--------------------------------------------------------------------------
*/
$uploadDir = "uploads/profiles/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/*
|--------------------------------------------------------------------------
| Fetch alumni profile information
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $connection,
    "SELECT
        first_name,
        middle_name,
        last_name,
        birthdate,
        phone,
        address,
        city,
        course,
        batch_year,
        gender,
        civil_status,
        profile_picture
     FROM alumni_profiles
     WHERE email = ?
     LIMIT 1"
);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $userEmail);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row["first_name"])) {
            $user["first_name"] = $row["first_name"];
        }

        if (isset($row["middle_name"])) {
            $user["middle_name"] = $row["middle_name"];
        }

        if (!empty($row["last_name"])) {
            $user["last_name"] = $row["last_name"];
        }

        if (!empty($row["course"])) {
            $user["course"] = $row["course"];
        }

        if (!empty($row["batch_year"])) {
            $user["batch_year"] = $row["batch_year"];
        }

        $birthdate = $row["birthdate"] ?? "";
        $phone = $row["phone"] ?? "";
        $address = $row["address"] ?? "";
        $city = $row["city"] ?? "";
        $gender = $row["gender"] ?? "";
        $civilStatus = $row["civil_status"] ?? "";
        $profilePicture = $row["profile_picture"] ?? "";
    }

    mysqli_stmt_close($stmt);
}

/*
|--------------------------------------------------------------------------
| Save profile information
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["first_name"] ?? "");
    $middleName = trim($_POST["middle_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $birthdate = trim($_POST["birthdate"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $batchYear = trim($_POST["batch_year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $civilStatus = trim($_POST["civil_status"] ?? "");

    if ($firstName === "" || $lastName === "") {
        $message = "First name and last name are required.";
        $messageType = "danger";
    }

    /*
    |--------------------------------------------------------------------------
    | Validate birthdate
    |--------------------------------------------------------------------------
    */
    if ($message === "" && $birthdate !== "") {
        $birthdateObject = DateTime::createFromFormat("Y-m-d", $birthdate);
        $dateErrors = DateTime::getLastErrors();

        $hasDateErrors = is_array($dateErrors) &&
            (
                $dateErrors["warning_count"] > 0 ||
                $dateErrors["error_count"] > 0
            );

        if (
            !$birthdateObject ||
            $hasDateErrors ||
            $birthdateObject->format("Y-m-d") !== $birthdate
        ) {
            $message = "Please enter a valid birthdate.";
            $messageType = "danger";
        } elseif ($birthdateObject > new DateTime("today")) {
            $message = "Birthdate cannot be in the future.";
            $messageType = "danger";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle profile picture upload
    |--------------------------------------------------------------------------
    */
    if (
        $message === "" &&
        isset($_FILES["profile_picture"]) &&
        $_FILES["profile_picture"]["error"] === UPLOAD_ERR_OK
    ) {
        $fileTmpPath = $_FILES["profile_picture"]["tmp_name"];
        $fileName = $_FILES["profile_picture"]["name"];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            $message = "Only JPG, JPEG, PNG, and WEBP images are allowed.";
            $messageType = "danger";
        } else {
            $newFileName = bin2hex(random_bytes(16)) . "." . $fileExtension;
            $destinationPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destinationPath)) {
                if (!empty($profilePicture) && file_exists($profilePicture)) {
                    unlink($profilePicture);
                }

                $profilePicture = $destinationPath;
            } else {
                $message = "Unable to upload the profile picture.";
                $messageType = "danger";
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Insert or update alumni profile
    |--------------------------------------------------------------------------
    */
    if ($message === "") {
        $stmt = mysqli_prepare(
            $connection,
            "INSERT INTO alumni_profiles (
                email,
                first_name,
                middle_name,
                last_name,
                birthdate,
                phone,
                address,
                city,
                course,
                batch_year,
                gender,
                civil_status,
                profile_picture
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                first_name = VALUES(first_name),
                middle_name = VALUES(middle_name),
                last_name = VALUES(last_name),
                birthdate = VALUES(birthdate),
                phone = VALUES(phone),
                address = VALUES(address),
                city = VALUES(city),
                course = VALUES(course),
                batch_year = VALUES(batch_year),
                gender = VALUES(gender),
                civil_status = VALUES(civil_status),
                profile_picture = IF(
                    VALUES(profile_picture) = '',
                    profile_picture,
                    VALUES(profile_picture)
                )"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssss",
                $userEmail,
                $firstName,
                $middleName,
                $lastName,
                $birthdate,
                $phone,
                $address,
                $city,
                $course,
                $batchYear,
                $gender,
                $civilStatus,
                $profilePicture
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Profile updated successfully!";
                $messageType = "success";

                $user["first_name"] = $firstName;
                $user["middle_name"] = $middleName;
                $user["last_name"] = $lastName;
                $user["course"] = $course;
                $user["batch_year"] = $batchYear;
            } else {
                $message = "Unable to save your profile information.";
                $messageType = "danger";
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = "Database error. Please try again.";
            $messageType = "danger";
        }
    }
}

$fullName = trim(
    $user["first_name"] . " " .
    ($user["middle_name"] !== "" ? $user["middle_name"] . " " : "") .
    $user["last_name"]
);

if ($fullName === "") {
    $fullName = "Alumni";
}

$userName = $fullName;
$userInitial = strtoupper(substr($userName, 0, 1));
$currentYear = date("Y");
?>

<?php
/*
|--------------------------------------------------------------------------
| Presentation helpers (display only)
|--------------------------------------------------------------------------
*/
$hasPhoto = !empty($profilePicture) && file_exists($profilePicture);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Profile / AlumTrace</title>

        <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
        

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
                <li><a href="AlumniProfile.php" aria-current="page"><i class="fa-solid fa-user-pen"></i> <span class="nav-label">My Profile</span></a></li>
                <li><a href="EmploymentInformation.php"><i class="fa-solid fa-briefcase"></i> <span class="nav-label">Employment</span></a></li>
                <li><a href="EducationAndCertification.php"><i class="fa-solid fa-certificate"></i> <span class="nav-label">Certifications</span></a></li>
                <li><a href="AlumniDirectory.php"><i class="fa-solid fa-address-book"></i> <span class="nav-label">Alumni Directory</span></a></li>
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
                <h1>My Profile</h1>
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
            <?php if ($message !== ""): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="<?php echo $messageType === "success" ? "status" : "alert"; ?>">
                    <i class="fa-solid <?php echo $messageType === "success" ? "fa-circle-check" : "fa-circle-exclamation"; ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <form id="profileForm" class="profile-layout" method="post" enctype="multipart/form-data" data-has-photo="<?php echo $hasPhoto ? "1" : "0"; ?>">
                <section class="id-card" aria-label="Profile summary">
                    <div class="id-identity">
                        <div class="id-photo">
                            <?php if ($hasPhoto): ?>
                                <img id="photoImg" src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Your profile photo">
                                <div id="photoFallback" class="id-initial" hidden><?php echo htmlspecialchars($userInitial); ?></div>
                            <?php else: ?>
                                <img id="photoImg" alt="Your profile photo" hidden>
                                <div id="photoFallback" class="id-initial"><?php echo htmlspecialchars($userInitial); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="id-who">
                            <h2 class="id-name" id="cardName"><?php echo htmlspecialchars($fullName); ?></h2>
                            <p class="id-email"><?php echo htmlspecialchars($userEmail); ?></p>
                        </div>
                    </div>
                    <div class="id-actions">
                        <input class="sr-only" type="file" id="profilePictureInput" name="profile_picture" accept="image/jpeg, image/png, image/webp">
                        <label class="photo-btn" for="profilePictureInput">
                            <i class="fa-solid fa-camera" aria-hidden="true"></i> Change photo
                        </label>
                        <p class="photo-note" id="fileNameDisplay" aria-live="polite">JPG, PNG, or WEBP</p>
                    </div>
                    <dl class="id-facts">
                        <div class="id-fact">
                            <dt>Course</dt>
                            <dd id="cardCourse"><?php echo htmlspecialchars($user["course"]); ?></dd>
                        </div>
                        <div class="id-fact">
                            <dt>Batch year</dt>
                            <dd id="cardBatch"><?php echo htmlspecialchars($user["batch_year"]); ?></dd>
                        </div>
                        <?php if ($user["student_id"] !== ""): ?>
                            <div class="id-fact">
                                <dt>Student ID</dt>
                                <dd><?php echo htmlspecialchars($user["student_id"]); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <div class="progress">
                        <div class="progress-head">
                            <span id="progressLabel">Profile completeness</span>
                            <strong id="progressPct">0%</strong>
                        </div>
                        <div class="progress-track" role="progressbar" aria-labelledby="progressLabel" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="progressTrack">
                            <span class="progress-fill" id="progressFill"></span>
                        </div>
                        <p class="progress-hint" id="progressHint"></p>
                    </div>
                </section>

                <div class="form-col">
                    <div class="panel">
                        <section class="section" aria-labelledby="sec-personal">
                            <div class="section-head">
                                <h3 id="sec-personal">Personal details</h3>
                                <p>Your name, birthdate, and background.</p>
                            </div>
                            <div class="fields cols-3">
                                <div class="field">
                                    <label for="first_name">First name <span class="req">(required)</span></label>
                                    <input class="input" type="text" id="first_name" name="first_name" autocomplete="given-name" value="<?php echo htmlspecialchars($user["first_name"]); ?>" required>
                                </div>
                                <div class="field">
                                    <label for="middle_name">Middle name</label>
                                    <input class="input" type="text" id="middle_name" name="middle_name" autocomplete="additional-name" value="<?php echo htmlspecialchars($user["middle_name"]); ?>">
                                </div>
                                <div class="field">
                                    <label for="last_name">Last name <span class="req">(required)</span></label>
                                    <input class="input" type="text" id="last_name" name="last_name" autocomplete="family-name" value="<?php echo htmlspecialchars($user["last_name"]); ?>" required>
                                </div>
                            </div>
                            <div class="fields cols-3">
                                <div class="field">
                                    <label for="birthdateTrigger">Birthdate</label>
                                    <div class="date-picker" id="birthdatePicker">
                                        <input type="hidden" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($birthdate); ?>" autocomplete="bday">
                                        <button type="button" class="date-picker-trigger" id="birthdateTrigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="birthdateDropdown">
                                            <span class="dp-value is-placeholder" id="birthdateDisplay">Select date</span>
                                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                        </button>
                                        <div class="date-picker-dropdown" id="birthdateDropdown" role="dialog" aria-label="Choose birthdate" hidden>
                                            <div class="dp-header">
                                                <button type="button" class="dp-nav" id="dpPrev" aria-label="Previous month"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                                                <button type="button" class="dp-title" id="dpTitle">Month Year</button>
                                                <button type="button" class="dp-nav" id="dpNext" aria-label="Next month"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                                            </div>
                                            <div class="dp-weekdays" aria-hidden="true"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div>
                                            <div class="dp-days" id="dpDays"></div>
                                            <div class="dp-years" id="dpYears" hidden></div>
                                            <div class="dp-footer">
                                                <button type="button" id="dpClear">Clear</button>
                                                <button type="button" id="dpToday">Today</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="gender">Gender</label>
                                    <select class="select" id="gender" name="gender">
                                        <option value="">Select gender</option>
                                        <option value="Male" <?php echo $gender === "Male" ? "selected" : ""; ?>>Male</option>
                                        <option value="Female" <?php echo $gender === "Female" ? "selected" : ""; ?>>Female</option>
                                        <option value="Prefer not to say" <?php echo $gender === "Prefer not to say" ? "selected" : ""; ?>>Prefer not to say</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="civil_status">Civil status</label>
                                    <select class="select" id="civil_status" name="civil_status">
                                        <option value="">Select civil status</option>
                                        <option value="Single" <?php echo $civilStatus === "Single" ? "selected" : ""; ?>>Single</option>
                                        <option value="Married" <?php echo $civilStatus === "Married" ? "selected" : ""; ?>>Married</option>
                                        <option value="Widowed" <?php echo $civilStatus === "Widowed" ? "selected" : ""; ?>>Widowed</option>
                                        <option value="Separated" <?php echo $civilStatus === "Separated" ? "selected" : ""; ?>>Separated</option>
                                        <option value="Divorced" <?php echo $civilStatus === "Divorced" ? "selected" : ""; ?>>Divorced</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="section" aria-labelledby="sec-contact">
                            <div class="section-head">
                                <h3 id="sec-contact">Contact</h3>
                                <p>How we can reach you.</p>
                            </div>
                            <div class="fields cols-2">
                                <div class="field">
                                    <label for="email">Email address</label>
                                    <div class="input-wrap">
                                        <input class="input" type="email" id="email" value="<?php echo htmlspecialchars($user["email"]); ?>" aria-describedby="emailHelp" readonly>
                                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    </div>
                                    <p class="help" id="emailHelp">Your email can't be changed here.</p>
                                </div>
                                <div class="field">
                                    <label for="phone">Contact number</label>
                                    <input class="input" type="tel" id="phone" name="phone" autocomplete="tel" value="<?php echo htmlspecialchars($phone); ?>" placeholder="09XXXXXXXXX">
                                </div>
                            </div>
                        </section>

                        <section class="section" aria-labelledby="sec-academic">
                            <div class="section-head">
                                <h3 id="sec-academic">Academic background</h3>
                                <p>Your program and batch year.</p>
                            </div>
                            <div class="fields cols-2">
                                <div class="field">
                                    <label for="course">Course / Program</label>
                                    <input class="input" type="text" id="course" name="course" value="<?php echo htmlspecialchars($user["course"]); ?>">
                                </div>
                                <div class="field">
                                    <label for="batch_year">Batch year</label>
                                    <input class="input" type="text" id="batch_year" name="batch_year" value="<?php echo htmlspecialchars($user["batch_year"]); ?>">
                                </div>
                            </div>
                        </section>

                        <section class="section" aria-labelledby="sec-address">
                            <div class="section-head">
                                <h3 id="sec-address">Address</h3>
                                <p>Your city and full address.</p>
                            </div>
                            <div class="fields cols-2">
                                <div class="field">
                                    <label for="city">City</label>
                                    <input class="input" type="text" id="city" name="city" autocomplete="address-level2" value="<?php echo htmlspecialchars($city); ?>" placeholder="Enter your city">
                                </div>
                            </div>
                            <div class="fields">
                                <div class="field">
                                    <label for="address">Complete address</label>
                                    <textarea class="textarea" id="address" name="address" autocomplete="street-address" placeholder="House No., Street, Barangay, City, Province"><?php echo htmlspecialchars($address); ?></textarea>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="save-bar" id="saveBar">
                        <div class="save-status" aria-live="polite">
                            <span class="dot" aria-hidden="true"></span>
                            <span id="saveStatus">No changes yet</span>
                        </div>
                        <div class="save-actions">
                            <a href="Dashboard.php" class="btn btn-ghost">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                                <span>Save changes</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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