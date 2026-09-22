<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Login.php');
    exit;
}
verify_csrf();

$email = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_message'] = 'Please enter your email and password.';
    $_SESSION['login_message_type'] = 'danger';
    header('Location: Login.php');
    exit;
}

$stmt = mysqli_prepare($connection, 'SELECT id,last_name,first_name,middle_name,student_id,course,batch_year,email,password,role FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['login_message'] = 'Unable to process login.';
    $_SESSION['login_message_type'] = 'danger';
    header('Location: Login.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, (string) $user['password'])) {
    $_SESSION['login_message'] = 'Invalid email or password.';
    $_SESSION['login_message_type'] = 'danger';
    header('Location: Login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']);
$_SESSION['user_email'] = $user['email'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['middle_name'] = $user['middle_name'];
$_SESSION['last_name'] = $user['last_name'];
$_SESSION['student_id'] = $user['student_id'];
$_SESSION['course'] = $user['course'];
$_SESSION['batch_year'] = $user['batch_year'];
$_SESSION['role'] = $user['role'] ?: 'alumni';
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

header('Location: ' . ($_SESSION['role'] === 'admin' ? 'AdminDashboard.php' : 'Dashboard.php'));
exit;
