<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: Registration.php'); exit; }
verify_csrf();

$lastName=trim($_POST['last_name']??''); $firstName=trim($_POST['first_name']??''); $middleName=trim($_POST['middle_name']??''); $studentId=trim($_POST['student_id']??''); $course=trim($_POST['course']??''); $batchYear=trim($_POST['batch_year']??''); $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
function register_error(string $message): never { $_SESSION['register_message']=$message; $_SESSION['register_message_type']='danger'; header('Location: Registration.php'); exit; }
if($lastName===''||$firstName===''||$studentId===''||$course===''||$batchYear===''||$email===''||$password===''||$confirm==='') register_error('Please complete all required fields.');
if(($_POST['agree_terms']??'')!=='1') register_error('You must accept the Terms & Conditions and Data Privacy Policy.');
if(!filter_var($email,FILTER_VALIDATE_EMAIL)) register_error('Please enter a valid email address.');
if(!preg_match('/^\d{4}$/',$batchYear)||((int)$batchYear<1980||(int)$batchYear>(int)date('Y'))) register_error('Please select a valid batch year.');
if(strlen($password)<8) register_error('Password must be at least 8 characters.');
if(!hash_equals($password,$confirm)) register_error('Passwords do not match.');
$stmt=mysqli_prepare($connection,'SELECT id FROM users WHERE email=? OR student_id=? LIMIT 1');if(!$stmt) register_error('Unable to check account availability.');mysqli_stmt_bind_param($stmt,'ss',$email,$studentId);mysqli_stmt_execute($stmt);mysqli_stmt_store_result($stmt);$exists=mysqli_stmt_num_rows($stmt)>0;mysqli_stmt_close($stmt);if($exists)register_error('An account with this email or Student ID already exists.');
$hash=password_hash($password,PASSWORD_DEFAULT);$stmt=mysqli_prepare($connection,'INSERT INTO users(last_name,first_name,middle_name,student_id,course,batch_year,email,password,role) VALUES(?,?,?,?,?,?,?,?,\'alumni\')');if(!$stmt)register_error('Unable to create your account.');mysqli_stmt_bind_param($stmt,'ssssssss',$lastName,$firstName,$middleName,$studentId,$course,$batchYear,$email,$hash);if(!mysqli_stmt_execute($stmt)){mysqli_stmt_close($stmt);register_error('Registration failed. Please try again.');}mysqli_stmt_close($stmt);$_SESSION['login_message']='Registration successful. You can now sign in.';$_SESSION['login_message_type']='success';header('Location: Login.php');exit;
