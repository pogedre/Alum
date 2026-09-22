<?php
session_start();

$message = $_SESSION["forgot_message"] ?? "";
$messageType = $_SESSION["forgot_message_type"] ?? "";

unset($_SESSION["forgot_message"]);
unset($_SESSION["forgot_message_type"]);

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

$dashboardLink = $isAdmin ? "AdminDashboard.php" : "Dashboard.php";
$activePage = "login";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>

    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --school-green: #087443;
            --dark-green: #04532f;
            --deep-green: #023b22;
            --light-green: #eef8f2;
            --gold: #f2c300;
            --dark-gold: #d8a900;
            --text-dark: #173b2a;
            --muted: #66756d;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(rgba(8, 116, 67, 0.88), rgba(4, 83, 47, 0.88)), 
                    url('images/school-building.png') no-repeat center center fixed;
            background-size: cover;
            font-family: "Poppins", sans-serif;
            color: var(--text-dark);
        }

        .forgot-section {
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px 15px 60px;
        }

        .forgot-card {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            padding: 45px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
        }

        .forgot-card h1 {
            color: var(--school-green);
            font-weight: 800;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .forgot-card .subtitle {
            text-align: center;
            color: var(--muted);
            margin-bottom: 30px;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 7px;
        }

        .form-control {
            height: 50px;
            border: 1px solid #d8dedb;
            border-radius: 10px;
            padding: 12px 14px;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--school-green);
            box-shadow: 0 0 0 0.2rem rgba(8, 116, 67, 0.15);
        }

        .btn-submit {
            width: 100%;
            height: 50px;
            background: var(--school-green);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 700;
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            transition: 0.3s;
            margin-top: 5px;
        }

        .btn-submit:hover {
            background: var(--dark-green);
            transform: translateY(-1px);
        }

        .back-login {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--school-green);
            text-decoration: none;
            font-weight: 600;
        }

        .back-login:hover {
            color: var(--dark-green);
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 22px;
        }

        @media (max-width: 600px) {
            .forgot-card {
                padding: 30px 22px;
            }
            .forgot-card h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<section class="forgot-section">
    <div class="forgot-card">

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType === "success" ? "success" : "danger" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <h1>Forgot Password?</h1>

        <p class="subtitle">
            Enter your registered email address and we will help you recover your account.
        </p>

        <form action="ForgotPasswordProcess.php" method="POST" novalidate>
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    required
                >
            </div>

            <button type="submit" class="btn-submit">
                Continue
            </button>
        </form>

        <a href="Login.php" class="back-login">
            Back to Login
        </a>

    </div>
</section>

<?php require_once "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>