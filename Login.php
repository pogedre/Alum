<?php
session_start();

// If user is already logged in, redirect them immediately to their dashboard
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $redirectDashboard = (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") ? "AdminDashboard.php" : "Dashboard.php";
    header("Location: " . $redirectDashboard);
    exit;
}

$message = $_SESSION["login_message"] ?? "";
$messageType = $_SESSION["login_message_type"] ?? "";

unset($_SESSION["login_message"]);
unset($_SESSION["login_message_type"]);

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

$dashboardLink = $isAdmin ? "AdminDashboard.php" : "Dashboard.php";
$activePage = "login";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign In</title>
    
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
            font-family: "Poppins", sans-serif;
        }

        body {
            margin: 0;
            background: #f4f8f5;
            color: var(--text-dark);
        }

        .login-section {
            min-height: calc(100vh - 72px);
            padding: 60px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(
                135deg,
                rgba(2, 59, 34, 0.88),
                rgba(8, 116, 67, 0.82)
            ), url("images/school-building.png") no-repeat center center;
            background-size: cover;
            position: relative;
        }

        .login-container {
            width: 100%;
            max-width: 580px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            border: none;
            border-radius: 22px;
            overflow: hidden;
            background: white;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, var(--school-green), var(--dark-green));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .school-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            padding: 5px;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-header p {
            margin-bottom: 0;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .login-body {
            padding: 35px;
            background: white;
        }

        .login-title {
            color: var(--school-green);
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 28px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-control {
            min-height: 50px;
            border-radius: 10px;
            border: 1px solid #d8dedb;
            padding: 12px 14px;
            color: var(--text-dark);
        }

        .form-control:focus {
            border-color: var(--school-green);
            box-shadow: 0 0 0 0.2rem rgba(8, 116, 67, 0.15);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 75px;
        }

        .show-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--school-green);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 5px;
        }

        .show-password:hover {
            color: var(--dark-green);
        }

        .forgot-link {
            display: block;
            text-align: right;
            margin-top: 8px;
            color: var(--school-green);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .forgot-link:hover {
            color: var(--dark-gold);
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            min-height: 50px;
            border: none;
            border-radius: 10px;
            background: var(--school-green);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            transition: 0.3s;
            margin-top: 8px;
        }

        .login-btn:hover {
            background: var(--dark-green);
            transform: translateY(-1px);
        }

        .register-text {
            color: var(--muted);
        }

        .register-link, .back-link {
            color: var(--school-green);
            font-weight: 700;
            text-decoration: none;
        }

        .register-link:hover, .back-link:hover {
            color: var(--dark-green);
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .login-section { padding: 30px 15px; }
            .login-body { padding: 25px 20px; }
            .login-header { padding: 30px 20px; }
            .login-header h1 { font-size: 1.8rem; }
            .school-logo { width: 75px; height: 75px; }
            .login-title { font-size: 1.5rem; }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<section class="login-section">
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <img src="images/Seal.png" alt="School Logo" class="school-logo">
                <h1>Welcome</h1>
                <p>Sign in to the Alumni Tracking System</p>
            </div>

            <div class="login-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType === "success" ? "success" : "danger" ?>" role="alert">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <h2 class="login-title">Login</h2>

                <form action="LoginProcess.php" method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" autocomplete="email" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="show-password" id="togglePassword" aria-label="Toggle password visibility">Show</button>
                        </div>
                        <a href="ForgotPassword.php" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <div class="text-center mt-4">
                    <span class="register-text">Don't have an account?</span>
                    <a href="Registration.php" class="register-link">Create Account</a>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="back-link">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>

<script>
    const passwordInput = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword");

    togglePassword.addEventListener("click", function () {
        const isPassword = passwordInput.type === "password";
        passwordInput.type = isPassword ? "text" : "password";
        this.textContent = isPassword ? "Hide" : "Show";
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>