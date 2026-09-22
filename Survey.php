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

$message = "";
$messageType = "";
$survey = null;
$questions = [];
$alreadySubmitted = false;

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

/*
|--------------------------------------------------------------------------
| Convert stored options into an array
|--------------------------------------------------------------------------
*/
function getQuestionOptions(?string $options): array
{
    if (!$options) {
        return [];
    }

    $items = preg_split("/[\r\n,]+/", $options);
    $items = array_map("trim", $items);
    $items = array_filter($items);

    return array_values(array_unique($items));
}

/*
|--------------------------------------------------------------------------
| Database and survey processing
|--------------------------------------------------------------------------
*/
if (isset($connection)) {
    mysqli_query(
        $connection,
        "CREATE TABLE IF NOT EXISTS surveys (
id INT AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(200) NOT NULL,
description TEXT NULL,
active TINYINT(1) NOT NULL DEFAULT 1,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    mysqli_query(
        $connection,
        "CREATE TABLE IF NOT EXISTS survey_questions (
id INT AUTO_INCREMENT PRIMARY KEY,
survey_id INT NOT NULL,
question TEXT NOT NULL,
question_type VARCHAR(30) NOT NULL DEFAULT 'text',
options TEXT NULL,
is_required TINYINT(1) NOT NULL DEFAULT 1,
sort_order INT NOT NULL DEFAULT 0,
INDEX idx_questions_survey (survey_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    mysqli_query(
        $connection,
        "CREATE TABLE IF NOT EXISTS survey_responses (
id INT AUTO_INCREMENT PRIMARY KEY,
survey_id INT NOT NULL,
question_id INT NOT NULL,
email VARCHAR(255) NOT NULL,
answer TEXT NULL,
submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
INDEX idx_responses_survey_email (survey_id, email),
INDEX idx_responses_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    /*
    |--------------------------------------------------------------------------
    | Create a default survey if none exists
    |--------------------------------------------------------------------------
    */
    $countResult = mysqli_query(
        $connection,
        "SELECT COUNT(*) AS total FROM surveys"
    );

    $surveyCount = 0;

    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $surveyCount = (int) ($countRow["total"] ?? 0);
    }

    if ($surveyCount === 0) {
        $defaultTitle = "Alumni Tracer Survey";
        $defaultDescription =
            "Please provide updated information about your education, employment, and alumni experience.";

        $stmt = mysqli_prepare(
            $connection,
            "INSERT INTO surveys (title, description, active)
VALUES (?, ?, 1)"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $defaultTitle,
                $defaultDescription
            );

            mysqli_stmt_execute($stmt);
            $surveyId = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);

            $defaultQuestions = [
                [
                    "What is your current employment status?",
                    "choice",
                    "Employed\nSelf-Employed\nUnemployed\nFreelancer\nBusiness Owner",
                    1
                ],
                [
                    "How relevant is your degree to your current work?",
                    "choice",
                    "Very Relevant\nRelevant\nSomewhat Relevant\nNot Relevant",
                    2
                ],
                [
                    "What suggestions can you provide to improve the alumni program?",
                    "textarea",
                    "",
                    3
                ]
            ];

            foreach ($defaultQuestions as $questionData) {
                $stmt = mysqli_prepare(
                    $connection,
                    "INSERT INTO survey_questions
(survey_id, question, question_type, options, is_required, sort_order)
VALUES (?, ?, ?, ?, 1, ?)"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "isssi",
                        $surveyId,
                        $questionData[0],
                        $questionData[1],
                        $questionData[2],
                        $questionData[3]
                    );

                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Load active survey
    |--------------------------------------------------------------------------
    */
    $stmt = mysqli_prepare(
        $connection,
        "SELECT id, title, description
FROM surveys
WHERE active = 1
ORDER BY id DESC
LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $survey = mysqli_fetch_assoc($result) ?: null;

        mysqli_stmt_close($stmt);
    }

    if ($survey) {
        $surveyId = (int) $survey["id"];

        /*
        |--------------------------------------------------------------------------
        | Check previous submission
        |--------------------------------------------------------------------------
        */
        $stmt = mysqli_prepare(
            $connection,
            "SELECT COUNT(*) AS total
FROM survey_responses
WHERE survey_id = ? AND email = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "is", $surveyId, $userEmail);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            $alreadySubmitted = ((int) ($row["total"] ?? 0)) > 0;

            mysqli_stmt_close($stmt);
        }

        /*
        |--------------------------------------------------------------------------
        | Load questions
        |--------------------------------------------------------------------------
        */
        $stmt = mysqli_prepare(
            $connection,
            "SELECT
id,
question,
question_type,
options,
is_required,
sort_order
FROM survey_questions
WHERE survey_id = ?
ORDER BY sort_order ASC, id ASC"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $surveyId);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                $row["options_array"] = getQuestionOptions(
                    $row["options"] ?? ""
                );

                $questions[] = $row;
            }

            mysqli_stmt_close($stmt);
        }

        /*
        |--------------------------------------------------------------------------
        | Submit survey
        |--------------------------------------------------------------------------
        */
        if (
            $_SERVER["REQUEST_METHOD"] === "POST" &&
            !$alreadySubmitted
        ) {
            $answers = [];
            $invalidAnswer = false;

            foreach ($questions as $question) {
                $questionId = (int) $question["id"];

                $answer = trim(
                    $_POST["question_" . $questionId] ?? ""
                );

                if (
                    (int) $question["is_required"] === 1 &&
                    $answer === ""
                ) {
                    $invalidAnswer = true;
                    break;
                }

                if ($question["question_type"] === "choice") {
                    $allowedOptions = $question["options_array"];

                    if (
                        $answer !== "" &&
                        !in_array($answer, $allowedOptions, true)
                    ) {
                        $invalidAnswer = true;
                        break;
                    }
                }

                if ($question["question_type"] === "boolean") {
                    if (
                        $answer !== "" &&
                        !in_array($answer, ["Yes", "No"], true)
                    ) {
                        $invalidAnswer = true;
                        break;
                    }
                }

                $answers[$questionId] = $answer;
            }

            if (empty($questions)) {
                $message = "This survey has no questions yet.";
                $messageType = "danger";
            } elseif ($invalidAnswer) {
                $message = "Please answer all required questions correctly.";
                $messageType = "danger";
            } else {
                mysqli_begin_transaction($connection);

                try {
                    foreach ($questions as $question) {
                        $questionId = (int) $question["id"];
                        $answer = $answers[$questionId] ?? "";

                        $stmt = mysqli_prepare(
                            $connection,
                            "INSERT INTO survey_responses
(survey_id, question_id, email, answer)
VALUES (?, ?, ?, ?)"
                        );

                        if (!$stmt) {
                            throw new Exception(
                                "Unable to prepare response query."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $stmt,
                            "iiss",
                            $surveyId,
                            $questionId,
                            $userEmail,
                            $answer
                        );

                        if (!mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            throw new Exception(
                                "Unable to save response."
                            );
                        }

                        mysqli_stmt_close($stmt);
                    }

                    mysqli_commit($connection);

                    $message =
                        "Thank you. Your survey response has been submitted successfully.";

                    $messageType = "success";
                    $alreadySubmitted = true;
                } catch (Throwable $error) {
                    mysqli_rollback($connection);

                    $message =
                        "Your response could not be submitted. Please try again.";

                    $messageType = "danger";
                }
            }
        }
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
    <title>Survey Forms / AlumTrace</title>
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
                <li><a href="AlumniDirectory.php"><i class="fa-solid fa-address-book"></i> <span class="nav-label">Alumni Directory</span></a></li>
                <li><a href="SurveyForms.php" aria-current="page"><i class="fa-solid fa-square-poll-horizontal"></i> <span class="nav-label">Survey Forms</span></a></li>
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
                <h1>Survey Forms</h1>
            </div>
            <div class="topbar-right">
                <div class="user-meta">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
                <?php if ($hasPhoto): ?>
                    <img class="avatar-sm" src="<?php echo htmlspecialchars($userPictureData["url"]); ?>" alt="">
                <?php else: ?>
                    <div class="avatar-sm fallback" aria-hidden="true"><?php echo htmlspecialchars($userInitial); ?></div>
                <?php endif; ?>
                <a href="Logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <main class="content" id="main">
            <div class="hero-banner">
                <h2>Survey Forms</h2>
                <p>Answer tracer surveys, provide employment feedback, and help improve alumni programs.</p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="<?php echo $messageType === "success" ? "status" : "alert"; ?>">
                    <i class="fa-solid <?php echo $messageType === "success" ? "fa-circle-check" : "fa-circle-exclamation"; ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="panel">
                <div class="card-title">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Available Survey
                </div>

                <?php if (!$survey): ?>
                    <div class="muted">No active survey is available at this time.</div>
                <?php elseif ($alreadySubmitted): ?>
                    <div class="alert alert-success" style="margin-bottom: 0;">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span>You have already completed this survey. Thank you for your participation.</span>
                    </div>
                <?php else: ?>
                    <div class="survey-intro">
                        <h3><?php echo htmlspecialchars($survey["title"]); ?></h3>
                        <?php if (!empty($survey["description"])): ?>
                            <p class="muted" style="margin-top: 4px;"><?php echo htmlspecialchars($survey["description"]); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($questions)): ?>
                        <div class="muted">This survey does not contain any questions yet.</div>
                    <?php else: ?>
                        <form method="post" id="surveyForm">
                            <?php foreach ($questions as $index => $question): ?>
                                <?php
                                $questionId = (int) $question["id"];
                                $questionType = $question["question_type"] ?? "text";
                                $options = $question["options_array"] ?? [];
                                $isRequired = (int) ($question["is_required"] ?? 1) === 1;
                                ?>
                                <div class="survey-question">
                                    <h4>
                                        <?php echo ($index + 1) . ". "; ?>
                                        <?php echo htmlspecialchars($question["question"]); ?>
                                        <?php if ($isRequired): ?>
                                            <span class="required-mark" title="Required">*</span>
                                        <?php endif; ?>
                                    </h4>

                                    <?php if ($questionType === "choice" && !empty($options)): ?>
                                        <div class="radio-row">
                                            <?php foreach ($options as $option): ?>
                                                <label class="radio-option">
                                                    <input type="radio" name="question_<?php echo $questionId; ?>" value="<?php echo htmlspecialchars($option); ?>" <?php echo $isRequired ? "required" : ""; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($questionType === "boolean"): ?>
                                        <div class="radio-row">
                                            <label class="radio-option">
                                                <input type="radio" name="question_<?php echo $questionId; ?>" value="Yes" <?php echo $isRequired ? "required" : ""; ?>>
                                                Yes
                                            </label>
                                            <label class="radio-option">
                                                <input type="radio" name="question_<?php echo $questionId; ?>" value="No" <?php echo $isRequired ? "required" : ""; ?>>
                                                No
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <textarea class="textarea" name="question_<?php echo $questionId; ?>" placeholder="Type your answer here..." <?php echo $isRequired ? "required" : ""; ?>></textarea>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                <span>Submit Survey</span>
                            </button>
                        </form>
                    <?php endif; ?>
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