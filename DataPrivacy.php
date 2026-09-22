<?php
session_start();

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

if ($isAdmin) {
    $dashboardLink = "AdminDashboard.php";
} else {
    $dashboardLink = "Dashboard.php";
}

$activePage = "privacy";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy</title>
    
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #087443;
            --primary-dark: #04532f;
            --accent-green: #eef8f2;
            --text-main: #173b2a;
            --text-muted: #5e7368;
            --border-color: #e2ebe6;
            --shadow-subtle: 0 10px 30px rgba(8, 116, 67, 0.05);
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.03);
            --shadow-hover: 0 12px 30px rgba(8, 116, 67, 0.12);
        }

        * {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #f4f7f5;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .page-hero {
            position: relative;
            min-height: 340px;
            background: linear-gradient(135deg, rgba(8, 116, 67, 0.92), rgba(3, 56, 32, 0.95)),
                        url("images/school-building.png") center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #ffffff;
            padding: 50px 20px 90px;
        }

        .page-hero h1 {
            font-size: 48px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .page-hero p {
            font-size: 17px;
            opacity: 0.92;
            font-weight: 400;
            max-width: 550px;
            margin: 0 auto;
        }

        .content-section {
            padding: 0 0 90px;
            margin-top: -65px;
            position: relative;
            z-index: 10;
        }

        .content-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 50px;
            border: 1px solid rgba(226, 235, 230, 0.8);
            box-shadow: var(--shadow-subtle);
        }

        .privacy-title {
            text-align: center;
            color: var(--primary-green);
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .privacy-subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 40px;
        }

        .privacy-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .privacy-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 28px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: var(--shadow-card);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .privacy-card:hover {
            border-color: rgba(8, 116, 67, 0.3);
            background-color: #f8fbf9;
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .privacy-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .privacy-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--accent-green);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .privacy-icon-box svg {
            width: 22px;
            height: 22px;
            fill: var(--primary-green);
        }

        .privacy-card h3 {
            color: var(--text-main);
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .privacy-card p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
            font-weight: 450;
        }

        @media (max-width: 991px) {
            .privacy-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-hero {
                min-height: 280px;
                padding-bottom: 75px;
            }

            .page-hero h1 {
                font-size: 34px;
            }

            .page-hero p {
                font-size: 15px;
            }

            .content-section {
                margin-top: -45px;
            }

            .content-card {
                padding: 30px 20px;
                border-radius: 20px;
            }

            .privacy-title {
                font-size: 24px;
            }

            .privacy-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>
    <?php require_once "includes/navbar.php"; ?>

<section class="page-hero">
    <div class="container">
        <h1>Data Privacy</h1>
        <p>Your information and privacy matter to us</p>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <div class="content-card">
            <h2 class="privacy-title">Privacy Policy & Transparency</h2>
            <p class="privacy-subtitle">Review how AlumTrace protects, utilizes, and manages your personal data.</p>

            <div class="privacy-grid">

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-5.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8s0 0 0 0z"/></svg>
                        </div>
                        <h3>Our Commitment to Privacy</h3>
                    </div>
                    <p>AlumTrace is committed to protecting the personal information provided by alumni while using the Alumni Tracking System.</p>
                </div>

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        </div>
                        <h3>Information We Collect</h3>
                    </div>
                    <p>The system may collect information needed to maintain alumni records, including names, email addresses, educational information, employment information, certifications, and survey responses.</p>
                </div>

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-2.33 4.67-3.5 7-3.5s7 1.17 7 3.5V19z"/></svg>
                        </div>
                        <h3>How We Use Information</h3>
                    </div>
                    <p>Information collected through AlumTrace is used to maintain accurate alumni records, support alumni activities, generate reports, and help administrators understand graduate outcomes.</p>
                </div>

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                        </div>
                        <h3>Protection of Information</h3>
                    </div>
                    <p>Access to account information is restricted through user authentication and administrator controls. Users are responsible for keeping their login credentials confidential.</p>
                </div>

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
                        </div>
                        <h3>Information Sharing</h3>
                    </div>
                    <p>Alumni information should only be accessed and used for legitimate purposes related to the Alumni Tracking System and its operations.</p>
                </div>

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        </div>
                        <h3>User Responsibility</h3>
                    </div>
                    <p>Users should provide accurate information and should immediately report concerns regarding unauthorized access or incorrect information.</p>
                </div>

                <div class="privacy-card">
                    <div class="privacy-card-header">
                        <div class="privacy-icon-box">
                            <svg viewBox="0 0 24 24"><path d="M21 10.12h-6.78l2.74-2.82c-2.73-2.7-7.15-2.8-9.88-.1-2.73 2.71-2.73 7.08 0 9.79 2.73 2.71 7.15 2.71 9.88 0 1.36-1.35 2.04-3.13 2.04-4.91h2c0 2.29-.87 4.58-2.61 6.32-3.49 3.47-9.15 3.47-12.64 0-3.49-3.47-3.49-9.1 0-12.57 3.49-3.47 9.15-3.47 12.64 0L21 3v7.12z"/></svg>
                        </div>
                        <h3>Privacy Updates</h3>
                    </div>
                    <p>This privacy information may be updated when system policies or requirements change. Users are encouraged to review this page periodically.</p>
                </div>

            </div>

        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>