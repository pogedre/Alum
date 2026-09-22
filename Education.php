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

$firstName = $_SESSION["first_name"] ?? "";
$middleName = $_SESSION["middle_name"] ?? "";
$lastName = $_SESSION["last_name"] ?? "";

$userName = trim("$firstName $middleName $lastName");

if ($userName === "") {
    $userName = $_SESSION["user_name"] ?? "Alumni";
}

$userInitial = strtoupper(substr($userName, 0, 1));
$currentYear = date("Y");

$message = "";
$messageType = "";
$records = [];
$recordEdit = null;

$uploadDir = "uploads/credentials/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

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
        $profilePictureUrl = str_replace("\\", "/", $profilePicture);
        $profilePictureUrl .= "?v=" . filemtime($profilePictureFile);
    }
}

/*
|--------------------------------------------------------------------------
| Date validation
|--------------------------------------------------------------------------
*/
function validDate(?string $date): bool
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
| Delete uploaded document safely
|--------------------------------------------------------------------------
*/
function deleteDocument(string $documentPath): void
{
    if ($documentPath === "") {
        return;
    }

    $normalizedPath = str_replace(
        ["\\", "/"],
        DIRECTORY_SEPARATOR,
        $documentPath
    );

    $absolutePath = __DIR__ .
        DIRECTORY_SEPARATOR .
        ltrim($normalizedPath, DIRECTORY_SEPARATOR);

    if (is_file($absolutePath)) {
        unlink($absolutePath);
    }
}

/*
|--------------------------------------------------------------------------
| Create table
|--------------------------------------------------------------------------
*/
if (isset($connection)) {
    mysqli_query(
        $connection,
        "CREATE TABLE IF NOT EXISTS education_certifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            record_type ENUM('Education','Certification') NOT NULL,
            education_level VARCHAR(80) DEFAULT NULL,
            school_or_issuer VARCHAR(180) NOT NULL,
            school_location VARCHAR(180) DEFAULT NULL,
            title VARCHAR(180) NOT NULL,
            field_of_study VARCHAR(180) DEFAULT NULL,
            start_year VARCHAR(10) DEFAULT NULL,
            year_completed VARCHAR(10) DEFAULT '',
            completion_status VARCHAR(50) DEFAULT NULL,
            date_issued DATE NULL,
            expiration_date DATE NULL,
            credential_number VARCHAR(120) DEFAULT NULL,
            credential_status VARCHAR(50) DEFAULT NULL,
            credential_url VARCHAR(500) DEFAULT NULL,
            document_path VARCHAR(255) DEFAULT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    /*
    |--------------------------------------------------------------------------
    | Delete record
    |--------------------------------------------------------------------------
    */
    if (isset($_GET["delete"])) {
        $recordId = (int) $_GET["delete"];
        $oldDocument = "";

        if ($recordId > 0) {
            $stmt = mysqli_prepare(
                $connection,
                "SELECT document_path
                 FROM education_certifications
                 WHERE id = ? AND email = ?
                 LIMIT 1"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "is", $recordId, $userEmail);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $oldRow = mysqli_fetch_assoc($result);

                $oldDocument = $oldRow["document_path"] ?? "";

                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare(
                $connection,
                "DELETE FROM education_certifications
                 WHERE id = ? AND email = ?"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "is", $recordId, $userEmail);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            deleteDocument($oldDocument);
        }

        header("Location: EducationAndCertification.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Save or update record
    |--------------------------------------------------------------------------
    */
    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["save_record"])
    ) {
        $recordId = (int) ($_POST["record_id"] ?? 0);

        $recordType = $_POST["record_type"] ?? "Education";
        $educationLevel = trim($_POST["education_level"] ?? "");
        $schoolIssuer = trim($_POST["school_or_issuer"] ?? "");
        $schoolLocation = trim($_POST["school_location"] ?? "");
        $title = trim($_POST["title"] ?? "");
        $fieldOfStudy = trim($_POST["field_of_study"] ?? "");
        $startYear = trim($_POST["start_year"] ?? "");
        $yearCompleted = trim($_POST["year_completed"] ?? "");
        $completionStatus = trim($_POST["completion_status"] ?? "");
        $dateIssued = trim($_POST["date_issued"] ?? "");
        $expirationDate = trim($_POST["expiration_date"] ?? "");
        $credentialNumber = trim($_POST["credential_number"] ?? "");
        $credentialStatus = trim($_POST["credential_status"] ?? "");
        $credentialUrl = trim($_POST["credential_url"] ?? "");
        $description = trim($_POST["description"] ?? "");

        if (!in_array($recordType, ["Education", "Certification"], true)) {
            $recordType = "Education";
        }

        if ($schoolIssuer === "" || $title === "") {
            $message = "School or issuer and title are required.";
            $messageType = "danger";
        } elseif (!validDate($dateIssued)) {
            $message = "Please enter a valid issue date.";
            $messageType = "danger";
        } elseif (!validDate($expirationDate)) {
            $message = "Please enter a valid expiration date.";
            $messageType = "danger";
        } elseif (
            $dateIssued !== "" &&
            $expirationDate !== "" &&
            $expirationDate < $dateIssued
        ) {
            $message = "Expiration date cannot be earlier than the issue date.";
            $messageType = "danger";
        } elseif (
            $startYear !== "" &&
            !preg_match("/^[0-9]{4}$/", $startYear)
        ) {
            $message = "Start year must contain four digits.";
            $messageType = "danger";
        } elseif (
            $yearCompleted !== "" &&
            !preg_match("/^[0-9]{4}$/", $yearCompleted)
        ) {
            $message = "Year completed must contain four digits.";
            $messageType = "danger";
        } else {
            $dateIssued = $dateIssued === "" ? null : $dateIssued;
            $expirationDate = $expirationDate === "" ? null : $expirationDate;
            $documentPath = "";

            /*
            |----------------------------------------------------------------------
            | Keep existing document when editing
            |----------------------------------------------------------------------
            */
            if ($recordId > 0) {
                $stmt = mysqli_prepare(
                    $connection,
                    "SELECT document_path
                     FROM education_certifications
                     WHERE id = ? AND email = ?
                     LIMIT 1"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "is", $recordId, $userEmail);
                    mysqli_stmt_execute($stmt);

                    $result = mysqli_stmt_get_result($stmt);
                    $existing = mysqli_fetch_assoc($result);

                    $documentPath = $existing["document_path"] ?? "";

                    mysqli_stmt_close($stmt);
                }
            }

            /*
            |----------------------------------------------------------------------
            | Upload document
            |----------------------------------------------------------------------
            */
            if (
                isset($_FILES["document_file"]) &&
                $_FILES["document_file"]["error"] === UPLOAD_ERR_OK
            ) {
                $temporaryPath = $_FILES["document_file"]["tmp_name"];
                $originalName = $_FILES["document_file"]["name"];
                $extension = strtolower(
                    pathinfo($originalName, PATHINFO_EXTENSION)
                );

                $allowedExtensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp",
                    "pdf"
                ];

                if (!in_array($extension, $allowedExtensions, true)) {
                    $message = "Only JPG, JPEG, PNG, WEBP, and PDF files are allowed.";
                    $messageType = "danger";
                } elseif ($_FILES["document_file"]["size"] > 5 * 1024 * 1024) {
                    $message = "The uploaded document must not exceed 5 MB.";
                    $messageType = "danger";
                } else {
                    $newFileName = bin2hex(random_bytes(16)) . "." . $extension;
                    $newDocumentPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($temporaryPath, $newDocumentPath)) {
                        deleteDocument($documentPath);
                        $documentPath = $newDocumentPath;
                    } else {
                        $message = "Unable to upload the document.";
                        $messageType = "danger";
                    }
                }
            }

            if ($message === "") {
                if ($recordId > 0) {
                    $stmt = mysqli_prepare(
                        $connection,
                        "UPDATE education_certifications
                         SET
                            record_type = ?,
                            education_level = ?,
                            school_or_issuer = ?,
                            school_location = ?,
                            title = ?,
                            field_of_study = ?,
                            start_year = ?,
                            year_completed = ?,
                            completion_status = ?,
                            date_issued = ?,
                            expiration_date = ?,
                            credential_number = ?,
                            credential_status = ?,
                            credential_url = ?,
                            document_path = ?,
                            description = ?
                         WHERE id = ? AND email = ?"
                    );

                    if ($stmt) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssssssssssssssssis",
                            $recordType,
                            $educationLevel,
                            $schoolIssuer,
                            $schoolLocation,
                            $title,
                            $fieldOfStudy,
                            $startYear,
                            $yearCompleted,
                            $completionStatus,
                            $dateIssued,
                            $expirationDate,
                            $credentialNumber,
                            $credentialStatus,
                            $credentialUrl,
                            $documentPath,
                            $description,
                            $recordId,
                            $userEmail
                        );

                        if (mysqli_stmt_execute($stmt)) {
                            $message = "Record updated successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Unable to update the record.";
                            $messageType = "danger";
                        }

                        mysqli_stmt_close($stmt);
                    } else {
                        $message = "Unable to prepare the update query.";
                        $messageType = "danger";
                    }
                } else {
                    $stmt = mysqli_prepare(
                        $connection,
                        "INSERT INTO education_certifications (
                            email,
                            record_type,
                            education_level,
                            school_or_issuer,
                            school_location,
                            title,
                            field_of_study,
                            start_year,
                            year_completed,
                            completion_status,
                            date_issued,
                            expiration_date,
                            credential_number,
                            credential_status,
                            credential_url,
                            document_path,
                            description
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    if ($stmt) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssssssssssssssss",
                            $userEmail,
                            $recordType,
                            $educationLevel,
                            $schoolIssuer,
                            $schoolLocation,
                            $title,
                            $fieldOfStudy,
                            $startYear,
                            $yearCompleted,
                            $completionStatus,
                            $dateIssued,
                            $expirationDate,
                            $credentialNumber,
                            $credentialStatus,
                            $credentialUrl,
                            $documentPath,
                            $description
                        );

                        if (mysqli_stmt_execute($stmt)) {
                            $message = "Record added successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Unable to add the record.";
                            $messageType = "danger";
                        }

                        mysqli_stmt_close($stmt);
                    } else {
                        $message = "Unable to prepare the insert query.";
                        $messageType = "danger";
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
    if (isset($_GET["edit"])) {
        $editId = (int) $_GET["edit"];

        if ($editId > 0) {
            $stmt = mysqli_prepare(
                $connection,
                "SELECT
                    id,
                    record_type,
                    education_level,
                    school_or_issuer,
                    school_location,
                    title,
                    field_of_study,
                    start_year,
                    year_completed,
                    completion_status,
                    date_issued,
                    expiration_date,
                    credential_number,
                    credential_status,
                    credential_url,
                    document_path,
                    description
                 FROM education_certifications
                 WHERE id = ? AND email = ?
                 LIMIT 1"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "is", $editId, $userEmail);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $recordEdit = mysqli_fetch_assoc($result) ?: null;

                mysqli_stmt_close($stmt);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch all records
    |--------------------------------------------------------------------------
    */
    $stmt = mysqli_prepare(
        $connection,
        "SELECT
            id,
            record_type,
            education_level,
            school_or_issuer,
            school_location,
            title,
            field_of_study,
            start_year,
            year_completed,
            completion_status,
            date_issued,
            expiration_date,
            credential_number,
            credential_status,
            credential_url,
            document_path,
            description
         FROM education_certifications
         WHERE email = ?
         ORDER BY year_completed DESC, date_issued DESC, id DESC"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $userEmail);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $records[] = $row;
        }

        mysqli_stmt_close($stmt);
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
    <title>Education &amp; Certifications / AlumTrace</title>
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
                <img src="images/Badge.png" alt="AlumTrace Logo">
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
                <li><a href="EducationAndCertification.php" aria-current="page" class="active"><i class="fa-solid fa-certificate"></i> <span class="nav-label">Certifications</span></a></li>
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
                <h1>Education &amp; Certifications</h1>
            </div>
            <div class="topbar-right">
                <div class="user-meta">
                    <div class="name"><?php echo htmlspecialchars($userName, ENT_QUOTES, "UTF-8"); ?></div>
                    <div class="email"><?php echo htmlspecialchars($userEmail, ENT_QUOTES, "UTF-8"); ?></div>
                </div>
                <?php if ($hasProfilePicture): ?>
                    <img src="<?php echo htmlspecialchars($profilePictureUrl, ENT_QUOTES, "UTF-8"); ?>" alt="Profile Picture" class="avatar-sm">
                <?php else: ?>
                    <div class="avatar-sm fallback" aria-hidden="true"><?php echo htmlspecialchars($userInitial, ENT_QUOTES, "UTF-8"); ?></div>
                <?php endif; ?>
                <a href="Logout.php" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

        <main class="content" id="main">
            <div class="page-head">
                <h2>Education &amp; Certifications</h2>
                <p>Manage your educational background, professional licenses, certificates, and supporting documents.</p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, "UTF-8"); ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-title">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <?php echo $recordEdit ? "Edit Record" : "Add Education or Certification"; ?>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="save_record" value="1">
                    <?php if ($recordEdit): ?>
                        <input type="hidden" name="record_id" value="<?php echo (int) $recordEdit["id"]; ?>">
                    <?php endif; ?>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="record_type">Record Type</label>
                            <select class="form-select" id="record_type" name="record_type">
                                <option value="Education" <?php echo (($recordEdit["record_type"] ?? "Education") === "Education") ? "selected" : ""; ?>>Education</option>
                                <option value="Certification" <?php echo (($recordEdit["record_type"] ?? "") === "Certification") ? "selected" : ""; ?>>Certification</option>
                            </select>
                        </div>
                        <div class="form-group education-field">
                            <label for="education_level">Education Level</label>
                            <select class="form-select" id="education_level" name="education_level">
                                <option value="">Select level</option>
                                <?php
                                $educationLevels = ["Senior High School", "Vocational/Technical", "Associate Degree", "Bachelor's Degree", "Master's Degree", "Doctorate"];
                                foreach ($educationLevels as $value):
                                ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, "UTF-8"); ?>" <?php echo (($recordEdit["education_level"] ?? "") === $value) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($value, ENT_QUOTES, "UTF-8"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="school_or_issuer">School / Institution / Issuer</label>
                            <input class="form-control" type="text" id="school_or_issuer" name="school_or_issuer" value="<?php echo htmlspecialchars($recordEdit["school_or_issuer"] ?? "", ENT_QUOTES, "UTF-8"); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="school_location">School / Issuer Location</label>
                            <input class="form-control" type="text" id="school_location" name="school_location" placeholder="City, Province, Country" value="<?php echo htmlspecialchars($recordEdit["school_location"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group">
                            <label for="title">Degree / Certification Title</label>
                            <input class="form-control" type="text" id="title" name="title" value="<?php echo htmlspecialchars($recordEdit["title"] ?? "", ENT_QUOTES, "UTF-8"); ?>" required>
                        </div>
                        <div class="form-group education-field">
                            <label for="field_of_study">Field of Study / Major</label>
                            <input class="form-control" type="text" id="field_of_study" name="field_of_study" value="<?php echo htmlspecialchars($recordEdit["field_of_study"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group education-field">
                            <label for="start_year">Start Year</label>
                            <input class="form-control" type="text" id="start_year" name="start_year" maxlength="4" placeholder="YYYY" value="<?php echo htmlspecialchars($recordEdit["start_year"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group education-field">
                            <label for="year_completed">Year Completed</label>
                            <input class="form-control" type="text" id="year_completed" name="year_completed" maxlength="4" placeholder="YYYY" value="<?php echo htmlspecialchars($recordEdit["year_completed"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group education-field">
                            <label for="completion_status">Completion Status</label>
                            <select class="form-select" id="completion_status" name="completion_status">
                                <option value="">Select status</option>
                                <?php foreach (["Ongoing", "Completed", "Graduated", "Not Completed"] as $value): ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, "UTF-8"); ?>" <?php echo (($recordEdit["completion_status"] ?? "") === $value) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($value, ENT_QUOTES, "UTF-8"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group certification-field">
                            <label for="date_issued">Date Issued</label>
                            <input class="form-control" type="date" id="date_issued" name="date_issued" value="<?php echo htmlspecialchars($recordEdit["date_issued"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group certification-field">
                            <label for="expiration_date">Expiration Date</label>
                            <input class="form-control" type="date" id="expiration_date" name="expiration_date" value="<?php echo htmlspecialchars($recordEdit["expiration_date"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group certification-field">
                            <label for="credential_number">Credential / License Number</label>
                            <input class="form-control" type="text" id="credential_number" name="credential_number" value="<?php echo htmlspecialchars($recordEdit["credential_number"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group certification-field">
                            <label for="credential_status">Credential Status</label>
                            <select class="form-select" id="credential_status" name="credential_status">
                                <option value="">Select status</option>
                                <?php foreach (["Active", "Expired", "Pending", "Revoked"] as $value): ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, "UTF-8"); ?>" <?php echo (($recordEdit["credential_status"] ?? "") === $value) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($value, ENT_QUOTES, "UTF-8"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group certification-field">
                            <label for="credential_url">Verification URL</label>
                            <input class="form-control" type="url" id="credential_url" name="credential_url" placeholder="https://example.com/verify" value="<?php echo htmlspecialchars($recordEdit["credential_url"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                        </div>
                        <div class="form-group">
                            <label for="document_file">Certificate / Diploma File</label>
                            <input class="form-control" type="file" id="document_file" name="document_file" accept=".jpg,.jpeg,.png,.webp,.pdf">
                            <div class="muted">Maximum 5 MB. Accepted: JPG, PNG, WEBP, PDF.</div>
                        </div>
                    </div>

                    <?php if (!empty($recordEdit["document_path"])): ?>
                        <div class="form-group">
                            <label>Current Document</label>
                            <div>
                                <a class="document-link" href="<?php echo htmlspecialchars($recordEdit["document_path"], ENT_QUOTES, "UTF-8"); ?>" target="_blank">
                                    <i class="fa-solid fa-file"></i> View current document
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-textarea" id="description" name="description" placeholder="Add relevant details, achievements, honors, or notes"><?php echo htmlspecialchars($recordEdit["description"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <?php echo $recordEdit ? "Update Record" : "Add Record"; ?>
                        </button>
                        <?php if ($recordEdit): ?>
                            <a href="EducationAndCertification.php" class="btn btn-ghost">
                                <i class="fa-solid fa-xmark"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div class="card-title">
                    <i class="fa-solid fa-list"></i> My Education &amp; Certifications
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Institution / Issuer</th>
                                <th>Title</th>
                                <th>Details</th>
                                <th>Document</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="6" class="muted">No records have been added yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><span class="tag"><?php echo htmlspecialchars($record["record_type"], ENT_QUOTES, "UTF-8"); ?></span></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($record["school_or_issuer"], ENT_QUOTES, "UTF-8"); ?></strong>
                                            <?php if (!empty($record["school_location"])): ?>
                                                <div class="muted"><?php echo htmlspecialchars($record["school_location"], ENT_QUOTES, "UTF-8"); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($record["title"], ENT_QUOTES, "UTF-8"); ?></strong>
                                            <?php if (!empty($record["field_of_study"])): ?>
                                                <div class="muted"><?php echo htmlspecialchars($record["field_of_study"], ENT_QUOTES, "UTF-8"); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($record["education_level"])): ?>
                                                <span class="tag"><?php echo htmlspecialchars($record["education_level"], ENT_QUOTES, "UTF-8"); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($record["completion_status"])): ?>
                                                <span class="tag"><?php echo htmlspecialchars($record["completion_status"], ENT_QUOTES, "UTF-8"); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($record["credential_status"])): ?>
                                                <span class="tag"><?php echo htmlspecialchars($record["credential_status"], ENT_QUOTES, "UTF-8"); ?></span>
                                            <?php endif; ?>
                                            <div class="muted">
                                                <?php if (!empty($record["start_year"])): ?>Start: <?php echo htmlspecialchars($record["start_year"], ENT_QUOTES, "UTF-8"); ?><br><?php endif; ?>
                                                <?php if (!empty($record["year_completed"])): ?>Completed: <?php echo htmlspecialchars($record["year_completed"], ENT_QUOTES, "UTF-8"); ?><br><?php endif; ?>
                                                <?php if (!empty($record["date_issued"])): ?>Issued: <?php echo htmlspecialchars(date("F j, Y", strtotime($record["date_issued"])), ENT_QUOTES, "UTF-8"); ?><br><?php endif; ?>
                                                <?php if (!empty($record["expiration_date"])): ?>Expires: <?php echo htmlspecialchars(date("F j, Y", strtotime($record["expiration_date"])), ENT_QUOTES, "UTF-8"); ?><br><?php endif; ?>
                                                <?php if (!empty($record["credential_number"])): ?>Credential No.: <?php echo htmlspecialchars($record["credential_number"], ENT_QUOTES, "UTF-8"); ?><?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($record["document_path"])): ?>
                                                <a class="document-link" href="<?php echo htmlspecialchars($record["document_path"], ENT_QUOTES, "UTF-8"); ?>" target="_blank">
                                                    <i class="fa-solid fa-file"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions" style="margin-top: 0;">
                                                <a class="btn btn-ghost btn-sm" href="?edit=<?php echo (int) $record["id"]; ?>">
                                                    <i class="fa-solid fa-pen"></i> Edit
                                                </a>
                                                <a class="btn btn-danger btn-sm" href="?delete=<?php echo (int) $record["id"]; ?>" onclick="return confirm('Delete this record?')">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if (!empty($record["description"])): ?>
                                        <tr>
                                            <td></td>
                                            <td colspan="5" class="muted">
                                                <strong>Description:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($record["description"], ENT_QUOTES, "UTF-8")); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img src="images/Badge.png" alt="AlumTrace Logo">
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