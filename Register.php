<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Registration.php");
    exit;
}

require_once "connection.php";

$last_name = trim($_POST["last_name"] ?? "");
$first_name = trim($_POST["first_name"] ?? "");
$middle_name = trim($_POST["middle_name"] ?? "");
$student_id = trim($_POST["student_id"] ?? "");
$course = trim($_POST["course"] ?? "");
$batch_year = trim($_POST["batch_year"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

// Checkbox value is "1" when checked and empty when unchecked
$agree_terms = $_POST["agree_terms"] ?? "";

if (
    $last_name === "" ||
    $first_name === "" ||
    $student_id === "" ||
    $course === "" ||
    $batch_year === "" ||
    $email === "" ||
    $password === "" ||
    $confirm_password === ""
) {
    $_SESSION["register_message"] = "Please complete all required fields.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

if ($agree_terms !== "1") {
    $_SESSION["register_message"] = "You must agree to the Terms & Conditions and Data Privacy Policy.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["register_message"] = "Please enter a valid email address.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

if (strlen($password) < 8) {
    $_SESSION["register_message"] = "Password must be at least 8 characters.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION["register_message"] = "Passwords do not match.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

$check = mysqli_prepare(
    $connection,
    "SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1"
);

if (!$check) {
    $_SESSION["register_message"] = "Database error. Please try again.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

mysqli_stmt_bind_param(
    $check,
    "ss",
    $email,
    $student_id
);

mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    mysqli_stmt_close($check);

    $_SESSION["register_message"] = "An account with this email or Student ID already exists.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

mysqli_stmt_close($check);

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt = mysqli_prepare(
    $connection,
    "INSERT INTO users (
        last_name,
        first_name,
        middle_name,
        student_id,
        course,
        batch_year,
        email,
        password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    $_SESSION["register_message"] = "Database error. Please try again.";
    $_SESSION["register_message_type"] = "danger";

    header("Location: Registration.php");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "sssssiss",
    $last_name,
    $first_name,
    $middle_name,
    $student_id,
    $course,
    $batch_year,
    $email,
    $hashed_password
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    $_SESSION["login_message"] = "Registration successful! You can now log in.";
    $_SESSION["login_message_type"] = "success";

    header("Location: Login.php");
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($connection);

$_SESSION["register_message"] = "Registration failed. Please try again.";
$_SESSION["register_message_type"] = "danger";

header("Location: Registration.php");
exit;

?>