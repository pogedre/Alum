<?php
session_start();

require_once "connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if (empty($email) || empty($password)) {
    $_SESSION["login_message"] = "Please enter your email and password.";
    $_SESSION["login_message_type"] = "danger";
    header("Location: Login.php");
    exit;
}

$stmt = mysqli_prepare(
    $connection,
    "SELECT id, last_name, first_name, middle_name, student_id, course, batch_year, email, password, role FROM users WHERE email = ? LIMIT 1"
);

if (!$stmt) {
    $_SESSION["login_message"] = "Database error: " . mysqli_error($connection);
    $_SESSION["login_message_type"] = "danger";
    header("Location: Login.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user["password"])) {
        
        // 1. Clear old session data completely to avoid cross-account data leaks
        $_SESSION = array();

        // 2. Prevent session fixation attacks
        session_regenerate_id(true);

        $userName = trim(
            $user["first_name"] . " " .
            ($user["middle_name"] ? $user["middle_name"] . " " : "") .
            $user["last_name"]
        );

        // 3. Assign session variables (including keys matching Dashboard.php)
        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $userName;       // Used by Dashboard.php
        $_SESSION["user_email"] = $user["email"]; // Used by Dashboard.php
        $_SESSION["full_name"] = $userName;
        $_SESSION["last_name"] = $user["last_name"];
        $_SESSION["first_name"] = $user["first_name"];
        $_SESSION["middle_name"] = $user["middle_name"];
        $_SESSION["student_id"] = $user["student_id"];
        $_SESSION["course"] = $user["course"];
        $_SESSION["batch_year"] = $user["batch_year"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"] ?? "alumni";

        mysqli_stmt_close($stmt);

        if ($user["role"] === "admin") {
            header("Location: AdminDashboard.php");
        } else {
            header("Location: Dashboard.php");
        }
        exit;
    }
}

mysqli_stmt_close($stmt);

$_SESSION["login_message"] = "Invalid email or password.";
$_SESSION["login_message_type"] = "danger";

header("Location: Login.php");
exit;
?>