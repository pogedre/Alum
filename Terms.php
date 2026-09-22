<?php
session_start();

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

if ($isAdmin) {
    $dashboardLink = "AdminDashboard.php";
} else {
    $dashboardLink = "Dashboard.php";
}

$activePage = "terms";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions</title>

    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #087443;
            --primary-dark: #04532f;
            --deep-green-gradient: linear-gradient(135deg, #075c35 0%, #043c22 100%);
            --text-main: #112c1f;
            --text-muted: #4a6356;
            --border-color: #d6e8df;
            --shadow-subtle: 0 20px 40px rgba(8, 116, 67, 0.06);
            --shadow-card: 0 10px 25px rgba(4, 60, 34, 0.12);
        }

        * {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #f8faf9;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .page-hero {
            position: relative;
            min-height: 380px;
            background: linear-gradient(135deg, rgba(8, 116, 67, 0.94), rgba(3, 56, 32, 0.97)),
                        url("images/school-building.png") center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #ffffff;
            padding: 60px 20px 110px;
        }

        .page-hero h1 {
            font-size: 44px;
            font-weight: 800;
            letter-spacing: -1.2px;
            margin-bottom: 12px;
        }

        .page-hero p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
            max-width: 500px;
            margin: 0 auto;
        }

        .content-section {
            padding: 0 0 100px;
            margin-top: -75px;
            position: relative;
            z-index: 10;
        }

        .content-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 50px;
            border: 1px solid rgba(226, 235, 230, 0.9);
            box-shadow: var(--shadow-subtle);
            max-width: 900px;
            margin: 0 auto;
        }

        .terms-header-wrapper {
            text-align: center;
            margin-bottom: 35px;
        }

        .terms-title {
            color: var(--primary-green);
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.8px;
            margin-bottom: 8px;
        }

        .terms-subtitle {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .terms-container {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .term-item {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .term-title-box {
            background: var(--deep-green-gradient);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 6px solid #031100;
            border-radius: 14px;
            padding: 16px 22px;
            box-shadow: var(--shadow-card);
            transition: all 0.25s ease;
        }

        .term-title-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(4, 60, 34, 0.18);
        }

        .term-title-box h4 {
            color: #ffffff;
            font-size: 17px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.2px;
        }

        .term-text {
            color: var(--text-muted);
            font-size: 14.5px;
            line-height: 1.8;
            margin: 0;
            padding-left: 10px;
            text-align: justify;
        }

        #extraTerms {
            display: none;
            flex-direction: column;
            gap: 28px;
        }

        .expand-btn-wrapper {
            text-align: center;
            margin-top: 15px;
            padding-top: 25px;
            border-top: 1px dashed var(--border-color);
        }

        .btn-toggle-terms {
            background-color: var(--primary-green);
            color: #ffffff;
            border: none;
            padding: 12px 32px;
            font-size: 14.5px;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(8, 116, 67, 0.2);
        }

        .btn-toggle-terms:hover {
            background-color: var(--primary-dark);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(8, 116, 67, 0.3);
        }

        @media (max-width: 768px) {
            .page-hero {
                min-height: 300px;
                padding-bottom: 85px;
            }
            .page-hero h1 {
                font-size: 32px;
            }
            .content-section {
                margin-top: -50px;
            }
            .content-card {
                padding: 30px 20px;
                border-radius: 20px;
            }
            .terms-title {
                font-size: 24px;
            }
            .term-title-box {
                padding: 14px 18px;
            }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<section class="page-hero">
    <div class="container">
        <h1>Terms & Conditions</h1>
        <p>Please review our terms and guidelines before using the AlumTrace portal</p>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <div class="content-card">

            <div class="terms-header-wrapper">
                <h2 class="terms-title">Terms of Service & Usage Guidelines</h2>
                <p class="terms-subtitle">Understand your rights, rules, and obligations when navigating and maintaining your profile within the AlumTrace platform.</p>
            </div>

            <div class="terms-container">
                <div class="term-item">
                    <div class="term-title-box">
                        <h4>1. Acceptance of Terms & Institutional Commitment</h4>
                    </div>
                    <p class="term-text">By creating an account, accessing, or otherwise interacting with the AlumTrace platform, you formally acknowledge, accept, and agree to be bound by these comprehensive Terms and Conditions. This agreement establishes a formal understanding between you and the institution regarding your ethical use of digital school records. If you do not completely agree with any part of these guidelines, you must immediately discontinue your use of the portal.</p>
                </div>

                <div class="term-item">
                    <div class="term-title-box">
                        <h4>2. User Account Registration & Data Accuracy</h4>
                    </div>
                    <p class="term-text">Users are completely and solely responsible for providing truthful, current, and verifiable information during the account registration process. You are required to maintain strict confidentiality over your username and password credentials. Any actions, updates, or submissions executed through your profile session will be legally attributed directly to you, making account security an absolute priority.</p>
                </div>

                <div class="term-item">
                    <div class="term-title-box">
                        <h4>3. Acceptable Use and Platform Boundaries</h4>
                    </div>
                    <p class="term-text">The AlumTrace system is strictly designated for legitimate alumni tracking, professional networking, career updates, and authorized institutional communications. Any unauthorized data scraping, malicious penetration testing, automated extraction, or attempts to disrupt or alter server infrastructure are strictly prohibited and will result in immediate legal and administrative repercussions.</p>
                </div>

                <div class="term-item">
                    <div class="term-title-box">
                        <h4>4. Maintenance of Alumni Records & Profiles</h4>
                    </div>
                    <p class="term-text">Registered graduates and alumni are strongly encouraged to keep their employment records, contact details, and academic milestones up to date. Maintaining accurate data ensures that the university can effectively generate analytical reports, offer relevant placement assistance, and build a thriving, robust professional community network.</p>
                </div>

                <div id="extraTerms">
                    <div class="term-item">
                        <div class="term-title-box">
                            <h4>5. Administrative Oversight & Elevated Privileges</h4>
                        </div>
                        <p class="term-text">Designated institution administrators possess specialized elevated permissions to audit member profiles, approve pending registration requests, monitor network activity, and manage database configurations. These administrative controls are strictly implemented to preserve directory integrity, protect user privacy, and ensure alignment with institutional standards.</p>
                    </div>

                    <div class="term-item">
                        <div class="term-title-box">
                            <h4>6. Account Security Protocols & Incident Reporting</h4>
                        </div>
                        <p class="term-text">Sharing your login credentials, password, or security tokens with third parties is strictly forbidden under any circumstances. If you detect or suspect any unauthorized access, suspicious activity, or compromise of your account safety, you are obligated to notify the system administrator immediately to prevent security breaches.</p>
                    </div>

                    <div class="term-item">
                        <div class="term-title-box">
                            <h4>7. Modifications to Features and Platform Policies</h4>
                        </div>
                        <p class="term-text">AlumTrace reserves the absolute right to modify, enhance, expand, or temporarily suspend any module, feature, or aspect of the website—as well as update these terms—at any time without prior individual notice. Continued utilization of the portal following any adjustments constitutes your active acceptance of those revised policies.</p>
                    </div>

                    <div class="term-item">
                        <div class="term-title-box">
                            <h4>8. Policy Violations & Termination of Access</h4>
                        </div>
                        <p class="term-text">The platform management team retains full authority to suspend, restrict, or permanently terminate any user account found to be in violation of these terms, or if the user engages in fraudulent, harassing, or harmful conduct. Terminated accounts lose all access to institutional networks and historical data profiles permanently.</p>
                    </div>
                </div>

                <div class="expand-btn-wrapper">
                    <button type="button" id="toggleTermsBtn" class="btn-toggle-terms">Show All Terms & Conditions ↓</button>
                </div>

            </div>

        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>

<script>
    const toggleBtn = document.getElementById('toggleTermsBtn');
    const extraTerms = document.getElementById('extraTerms');

    toggleBtn.addEventListener('click', function() {
        if (extraTerms.style.display === 'flex') {
            extraTerms.style.display = 'none';
            toggleBtn.textContent = 'Show All Terms & Conditions ↓';
        } else {
            extraTerms.style.display = 'flex';
            toggleBtn.textContent = 'Show Less ↑';
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>