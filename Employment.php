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
| User name
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
| Profile picture
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

$profilePictureUrl = str_replace("\\", "/", $profilePicture);
$profilePictureUrl .= "?v=" . filemtime($profilePictureFile);
}
}

$msg = "";
$msgType = "";
$jobs = [];
$jobEdit = null;

/*
|--------------------------------------------------------------------------
| Validate date
|--------------------------------------------------------------------------
*/
function isValidDateValue(?string $date): bool
{
if ($date === null || $date === "") {
return true;
}

$dateObject = DateTime::createFromFormat("Y-m-d", $date);
$errors = DateTime::getLastErrors();

$hasErrors = is_array($errors) &&
(
$errors["warning_count"] > 0 ||
$errors["error_count"] > 0
);

return $dateObject !== false &&
!$hasErrors &&
$dateObject->format("Y-m-d") === $date;
}

/*
|--------------------------------------------------------------------------
| Create table if it does not exist
|--------------------------------------------------------------------------
*/
if (isset($connection)) {
mysqli_query(
$connection,
"CREATE TABLE IF NOT EXISTS employment_information (
id INT AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(255) NOT NULL,
employment_status VARCHAR(80) DEFAULT '',
employment_type VARCHAR(50) DEFAULT NULL,
company_name VARCHAR(180) DEFAULT '',
company_address VARCHAR(255) DEFAULT NULL,
job_title VARCHAR(180) DEFAULT '',
industry VARCHAR(150) DEFAULT '',
degree_relevance VARCHAR(50) DEFAULT NULL,
work_location VARCHAR(180) DEFAULT '',
start_date DATE NULL,
end_date DATE NULL,
is_current TINYINT(1) NOT NULL DEFAULT 0,
description TEXT,
reason_for_leaving TEXT,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

/*
|--------------------------------------------------------------------------
| Delete employment record
|--------------------------------------------------------------------------
*/
if (isset($_GET["delete_job"])) {
$jobId = (int) $_GET["delete_job"];

if ($jobId > 0) {
$stmt = mysqli_prepare(
$connection,
"DELETE FROM employment_information
WHERE id = ? AND email = ?"
);

if ($stmt) {
mysqli_stmt_bind_param($stmt, "is", $jobId, $userEmail);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
}
}

header("Location: EmploymentInformation.php");
exit;
}

/*
|--------------------------------------------------------------------------
| Save or update employment record
|--------------------------------------------------------------------------
*/
if (
$_SERVER["REQUEST_METHOD"] === "POST" &&
isset($_POST["save_job"])
) {
$jobId = (int) ($_POST["job_id"] ?? 0);

$status = trim($_POST["employment_status"] ?? "");
$employmentType = trim($_POST["employment_type"] ?? "");
$company = trim($_POST["company_name"] ?? "");
$companyAddress = trim($_POST["company_address"] ?? "");
$job = trim($_POST["job_title"] ?? "");
$industry = trim($_POST["industry"] ?? "");
$degreeRelevance = trim($_POST["degree_relevance"] ?? "");
$location = trim($_POST["work_location"] ?? "");
$start = trim($_POST["start_date"] ?? "");
$end = trim($_POST["end_date"] ?? "");
$isCurrent = isset($_POST["is_current"]) ? 1 : 0;
$description = trim($_POST["description"] ?? "");
$reasonLeaving = trim($_POST["reason_for_leaving"] ?? "");

if ($job === "" && $company === "" && $status === "") {
$msg = "Please provide at least an employment status, company, or job title.";
$msgType = "danger";
} elseif (!isValidDateValue($start)) {
$msg = "Please enter a valid employment start date.";
$msgType = "danger";
} elseif (!isValidDateValue($end)) {
$msg = "Please enter a valid employment end date.";
$msgType = "danger";
} else {
$startDate = $start === "" ? null : $start;
$endDate = $end === "" ? null : $end;

if ($isCurrent === 1) {
$endDate = null;
}

if (
$startDate !== null &&
$endDate !== null &&
$endDate < $startDate
) {
$msg = "Employment end date cannot be earlier than the start date.";
$msgType = "danger";
} else {
if ($jobId > 0) {
$stmt = mysqli_prepare(
$connection,
"UPDATE employment_information
SET
employment_status = ?,
employment_type = ?,
company_name = ?,
company_address = ?,
job_title = ?,
industry = ?,
degree_relevance = ?,
work_location = ?,
start_date = ?,
end_date = ?,
is_current = ?,
description = ?,
reason_for_leaving = ?
WHERE id = ? AND email = ?"
);

if ($stmt) {
mysqli_stmt_bind_param(
$stmt,
"ssssssssssissis",
$status,
$employmentType,
$company,
$companyAddress,
$job,
$industry,
$degreeRelevance,
$location,
$startDate,
$endDate,
$isCurrent,
$description,
$reasonLeaving,
$jobId,
$userEmail
);

if (mysqli_stmt_execute($stmt)) {
$msg = "Employment record updated successfully.";
$msgType = "success";
} else {
$msg = "Unable to update employment record.";
$msgType = "danger";
}

mysqli_stmt_close($stmt);
} else {
$msg = "Database error while preparing the update.";
$msgType = "danger";
}
} else {
$stmt = mysqli_prepare(
$connection,
"INSERT INTO employment_information (
email,
employment_status,
employment_type,
company_name,
company_address,
job_title,
industry,
degree_relevance,
work_location,
start_date,
end_date,
is_current,
description,
reason_for_leaving
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if ($stmt) {
mysqli_stmt_bind_param(
$stmt,
"ssssssssssisss",
$userEmail,
$status,
$employmentType,
$company,
$companyAddress,
$job,
$industry,
$degreeRelevance,
$location,
$startDate,
$endDate,
$isCurrent,
$description,
$reasonLeaving
);

if (mysqli_stmt_execute($stmt)) {
$msg = "Employment record saved successfully.";
$msgType = "success";
} else {
$msg = "Unable to save employment record.";
$msgType = "danger";
}

mysqli_stmt_close($stmt);
} else {
$msg = "Database error while preparing the insert.";
$msgType = "danger";
}
}
}
}
}

/*
|--------------------------------------------------------------------------
| Load record for editing
|--------------------------------------------------------------------------
*/
if (isset($_GET["edit_job"])) {
$editId = (int) $_GET["edit_job"];

if ($editId > 0) {
$stmt = mysqli_prepare(
$connection,
"SELECT
id,
employment_status,
employment_type,
company_name,
company_address,
job_title,
industry,
degree_relevance,
work_location,
start_date,
end_date,
is_current,
description,
reason_for_leaving
FROM employment_information
WHERE id = ? AND email = ?
LIMIT 1"
);

if ($stmt) {
mysqli_stmt_bind_param($stmt, "is", $editId, $userEmail);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$jobEdit = mysqli_fetch_assoc($result) ?: null;

mysqli_stmt_close($stmt);
}
}
}

/*
|--------------------------------------------------------------------------
| Load all employment records
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
$connection,
"SELECT
id,
employment_status,
employment_type,
company_name,
company_address,
job_title,
industry,
degree_relevance,
work_location,
start_date,
end_date,
is_current,
description,
reason_for_leaving
FROM employment_information
WHERE email = ?
ORDER BY is_current DESC, start_date DESC, id DESC"
);

if ($stmt) {
mysqli_stmt_bind_param($stmt, "s", $userEmail);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
$jobs[] = $row;
}

mysqli_stmt_close($stmt);
}
} else {
$msg = "Database connection is unavailable.";
$msgType = "danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Information / AlumTrace</title>
    <link rel="icon" type="image/png" href="images/Badge.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/alumtrace.css">
    <style>
        /* Employment page extras */
        .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        .checkbox-wrap input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .checkbox-wrap label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }
        .datefield {
            position: relative;
        }
        .date-native {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
            z-index: 2;
        }
        .date-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            font-family: var(--font);
            font-size: 0.92rem;
            color: var(--text);
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
            text-align: left;
        }
        .date-trigger.is-empty .dt-text {
            color: var(--text-muted);
        }
        .date-trigger:hover,
        .datefield:focus-within .date-trigger {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
        }
        .dt-icon, .dt-caret {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: var(--text-muted);
        }
        .dt-text { flex: 1; }
        .job-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .job-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            transition: var(--transition);
        }
        .job-item:hover {
            border-color: #a3cfbb;
            box-shadow: var(--shadow-sm);
        }
        .job-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .job-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--primary);
        }
        .job-meta {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.5;
        }
        .job-meta strong {
            color: var(--text);
            font-weight: 600;
        }
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }
        .job-details-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--border);
        }
    </style>
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
                <li><a href="EmploymentInformation.php" class="active" aria-current="page"><i class="fa-solid fa-briefcase"></i> <span class="nav-label">Employment</span></a></li>
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
                <h1>Employment Information</h1>
            </div>
            <div class="topbar-right">
                <div class="user-meta">
                    <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
                <?php if ($hasProfilePicture): ?>
                    <img class="avatar-sm" src="<?php echo htmlspecialchars($profilePictureUrl, ENT_QUOTES, "UTF-8"); ?>" alt="">
                <?php else: ?>
                    <div class="avatar-sm fallback" aria-hidden="true"><?php echo htmlspecialchars($userInitial); ?></div>
                <?php endif; ?>
                <a href="Logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <main class="content" id="main">
            <div class="page-head">
                <h2>Employment Information</h2>
                <p>Add and update your employment records, job history, and company information.</p>
            </div>

            <?php if ($msg !== ""): ?>
                <div class="alert alert-<?php echo htmlspecialchars($msgType); ?>" role="<?php echo $msgType === "success" ? "status" : "alert"; ?>">
                    <i class="fa-solid <?php echo $msgType === "success" ? "fa-circle-check" : "fa-circle-exclamation"; ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endif; ?>

            <div class="panel">
                <div class="panel-title">
                    <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    <span><?php echo $jobEdit ? "Edit Employment Record" : "Add Employment Record"; ?></span>
                </div>

                <form method="post">
                    <?php if ($jobEdit): ?>
                        <input type="hidden" name="job_id" value="<?php echo (int) $jobEdit["id"]; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="save_job" value="1">

                    <div class="fields cols-2">
                        <div class="field">
                            <label for="employment_status">Employment Status</label>
                            <select class="select" id="employment_status" name="employment_status">
                                <option value="">Select status</option>
                                <?php
                                $statusOptions = ["Employed", "Self-Employed", "Unemployed", "Freelancer", "Business Owner", "Student"];
                                foreach ($statusOptions as $value):
                                ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($jobEdit["employment_status"] ?? "") === $value) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="employment_type">Employment Type</label>
                            <select class="select" id="employment_type" name="employment_type">
                                <option value="">Select type</option>
                                <?php
                                $typeOptions = ["Full-time", "Part-time", "Contract", "Freelance", "Internship", "Temporary", "Self-employed"];
                                foreach ($typeOptions as $value):
                                ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($jobEdit["employment_type"] ?? "") === $value) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="company_name">Company / Organization</label>
                            <input class="input" type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($jobEdit["company_name"] ?? ""); ?>">
                        </div>

                        <div class="field">
                            <label for="company_address">Company Address</label>
                            <input class="input" type="text" id="company_address" name="company_address" value="<?php echo htmlspecialchars($jobEdit["company_address"] ?? ""); ?>">
                        </div>

                        <div class="field">
                            <label for="job_title">Job Title / Position</label>
                            <input class="input" type="text" id="job_title" name="job_title" value="<?php echo htmlspecialchars($jobEdit["job_title"] ?? ""); ?>">
                        </div>

                        <div class="field">
                            <label for="industry">Industry</label>
                            <input class="input" type="text" id="industry" name="industry" value="<?php echo htmlspecialchars($jobEdit["industry"] ?? ""); ?>">
                        </div>

                        <div class="field">
                            <label for="degree_relevance">Degree Relevance</label>
                            <select class="select" id="degree_relevance" name="degree_relevance">
                                <option value="">Select relevance</option>
                                <?php
                                $relevanceOptions = ["Very Relevant", "Relevant", "Somewhat Relevant", "Not Relevant"];
                                foreach ($relevanceOptions as $value):
                                ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($jobEdit["degree_relevance"] ?? "") === $value) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="work_location">Work Location</label>
                            <input class="input" type="text" id="work_location" name="work_location" value="<?php echo htmlspecialchars($jobEdit["work_location"] ?? ""); ?>" placeholder="City, Province, Country, Remote, or Hybrid">
                        </div>

                        <div class="field">
                            <label for="start_date">Employment Start Date</label>
                            <input class="input" type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($jobEdit["start_date"] ?? ""); ?>" max="<?php echo date("Y-m-d"); ?>">
                        </div>

                        <div class="field">
                            <label for="end_date">Employment End Date</label>
                            <input class="input" type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($jobEdit["end_date"] ?? ""); ?>">
                        </div>

                        <div class="field">
                            <label>&nbsp;</label>
                            <div class="checkbox-wrap">
                                <input type="checkbox" id="is_current" name="is_current" value="1" <?php echo !empty($jobEdit["is_current"]) ? "checked" : ""; ?>>
                                <label for="is_current">I currently work here</label>
                            </div>
                        </div>
                    </div>

                    <div class="fields" style="margin-top: 8px;">
                        <div class="field">
                            <label for="description">Career Details</label>
                            <textarea class="textarea" id="description" name="description" placeholder="Describe your role, responsibilities, or career growth"><?php echo htmlspecialchars($jobEdit["description"] ?? ""); ?></textarea>
                        </div>
                        <div class="field">
                            <label for="reason_for_leaving">Reason for Leaving</label>
                            <textarea class="textarea" id="reason_for_leaving" name="reason_for_leaving" placeholder="If this is a previous job, explain why you left"><?php echo htmlspecialchars($jobEdit["reason_for_leaving"] ?? ""); ?></textarea>
                        </div>
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            <span><?php echo $jobEdit ? "Update Record" : "Save Record"; ?></span>
                        </button>
                        <?php if ($jobEdit): ?>
                            <a href="EmploymentInformation.php" class="btn btn-ghost">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="panel" style="margin-top: 24px;">
                <div class="panel-title">
                    <i class="fa-solid fa-list" aria-hidden="true"></i>
                    <span>My Employment Records</span>
                </div>

                <?php if (empty($jobs)): ?>
                    <p class="muted">No employment records added yet.</p>
                <?php else: ?>
                    <div class="job-list">
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-item">
                                <div class="job-top">
                                    <div>
                                        <div class="job-title">
                                            <?php echo htmlspecialchars($job["job_title"] ?: "Unspecified Position"); ?>
                                        </div>
                                        <div class="job-meta">
                                            <?php echo htmlspecialchars($job["company_name"] ?: "Company not provided"); ?>
                                            <?php if (!empty($job["work_location"])): ?>
                                                &bull; <?php echo htmlspecialchars($job["work_location"]); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="actions" style="margin-top: 0;">
                                        <a class="btn btn-ghost btn-sm" href="?edit_job=<?php echo (int) $job["id"]; ?>">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
                                        </a>
                                        <a class="btn btn-danger btn-sm" href="?delete_job=<?php echo (int) $job["id"]; ?>" onclick="return confirm('Delete this employment record?')">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i> Delete
                                        </a>
                                    </div>
                                </div>

                                <div class="tag-list">
                                    <?php if (!empty($job["employment_status"])): ?>
                                        <span class="tag"><?php echo htmlspecialchars($job["employment_status"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job["employment_type"])): ?>
                                        <span class="tag"><?php echo htmlspecialchars($job["employment_type"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job["industry"])): ?>
                                        <span class="tag"><?php echo htmlspecialchars($job["industry"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job["degree_relevance"])): ?>
                                        <span class="tag"><?php echo htmlspecialchars($job["degree_relevance"]); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job["is_current"])): ?>
                                        <span class="tag">Current</span>
                                    <?php endif; ?>
                                </div>

                                <div class="job-details-group">
                                    <?php if (!empty($job["company_address"])): ?>
                                        <div class="job-meta">
                                            <strong>Company Address:</strong>
                                            <?php echo htmlspecialchars($job["company_address"]); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    $startDisplay = !empty($job["start_date"])
                                        ? date("F j, Y", strtotime($job["start_date"]))
                                        : "Unknown";
                                    $endDisplay = !empty($job["is_current"])
                                        ? "Present"
                                        : (!empty($job["end_date"]) ? date("F j, Y", strtotime($job["end_date"])) : "Unknown");
                                    ?>

                                    <?php if (!empty($job["start_date"]) || !empty($job["end_date"]) || !empty($job["is_current"])): ?>
                                        <div class="job-meta">
                                            <strong>Employment Period:</strong>
                                            <?php echo htmlspecialchars("$startDisplay - $endDisplay"); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($job["description"])): ?>
                                        <div class="job-meta">
                                            <strong>Career Details:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($job["description"])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($job["reason_for_leaving"])): ?>
                                        <div class="job-meta">
                                            <strong>Reason for Leaving:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($job["reason_for_leaving"])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
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