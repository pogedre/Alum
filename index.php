<?php
session_start();

$activePage = 'home';
$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
$dashboardLink = $isAdmin ? "AdminDashboard.php" : "Dashboard.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>

    <!-- Updated Favicon with Cache Buster -->
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

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: #f4f8f5;
            color: var(--text-dark);
        }

        .hero {
            position: relative;
            min-height: 650px;
            background-image:
                linear-gradient(
                    rgba(0, 77, 45, 0.78),
                    rgba(8, 116, 67, 0.72)
                ),
                url("images/school-building.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            display: flex;
            align-items: center;
        }

        .hero h1 {
            font-size: 58px;
            font-weight: 800;
            line-height: 1.08;
            margin-bottom: 20px;
            color: white;
        }

        .hero p {
            font-size: 18px;
            line-height: 1.8;
            max-width: 720px;
            color: rgba(255, 255, 255, 0.96);
            font-weight: 400;
        }

        .hero-buttons {
            margin-top: 30px;
        }

        .hero-login {
            background: white;
            color: var(--school-green);
            border: 2px solid white;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 8px;
            transition: 0.25s;
        }

        .hero-login:hover {
            background: var(--gold);
            color: var(--deep-green);
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        .hero-register {
            background: transparent;
            color: white;
            border: 2px solid white;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 8px;
            transition: 0.25s;
            margin-left: 10px;
        }

        .hero-register:hover {
            background: var(--gold);
            color: var(--deep-green);
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        @media (max-width: 991px) {
            .hero {
                padding: 75px 0;
                min-height: auto;
            }

            .hero h1 {
                font-size: 45px;
            }
        }

        @media (max-width: 576px) {
            .hero h1 {
                font-size: 38px;
            }

            .hero p {
                font-size: 16px;
            }

            .hero-register {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<header class="hero">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>
                    Alumni Tracking <br> System
                </h1>
                <p>
                    Maintain updated alumni profiles and employment
                    information for graduating alumni. Alumni can update
                    their information and answer surveys, while
                    administrators can analyze employment status,
                    industries, locations, and other graduate outcomes.
                </p>

                <?php if (!$isLoggedIn): ?>
                    <div class="hero-buttons">
                        <a href="Login.php" class="btn hero-login">Login</a>
                        <a href="Registration.php" class="btn hero-register">Register</a>
                    </div>
                <?php else: ?>
                    <div class="hero-buttons">
                        <a href="<?= htmlspecialchars($dashboardLink) ?>" class="btn hero-login">Go to Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<?php require_once "includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>