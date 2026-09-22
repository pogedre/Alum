<?php
session_start();

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

$dashboardLink = $isAdmin ? "AdminDashboard.php" : "Dashboard.php";

$activePage = "faqs";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>
<!-- Updated Favicon with Cache Buster -->
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link rel="shortcut icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #087443;
            --primary-dark: #04532f;
            --accent-green: #eef8f2;
            --hover-green: #f2faf5;
            --text-main: #173b2a;
            --text-muted: #5e7368;
            --border-color: #e2ebe6;
            --shadow-subtle: 0 10px 30px rgba(8, 116, 67, 0.05);
            --shadow-card: 0 4px 18px rgba(0, 0, 0, 0.03);
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
            font-size: 46px;
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

        .faq-title {
            text-align: center;
            color: var(--primary-green);
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .faq-subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 35px;
        }

        .search-container {
            max-width: 580px;
            margin: 0 auto 40px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 16px 24px 16px 52px;
            font-size: 15px;
            font-weight: 500;
            border: 2px solid var(--border-color);
            border-radius: 100px;
            outline: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            background: #fcfdfe;
            color: var(--text-main);
        }

        .search-input::placeholder { color: #92a49b; }

        .search-input:focus {
            border-color: var(--primary-green);
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(8, 116, 67, 0.12);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            fill: #80968b;
            transition: fill 0.3s ease;
        }

        .search-container:focus-within .search-icon {
            fill: var(--primary-green);
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .faq-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 22px 28px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
            box-shadow: var(--shadow-card);
            position: relative;
        }

        .faq-card:hover {
            border-color: rgba(8, 116, 67, 0.3);
            background-color: var(--hover-green);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .faq-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 17px;
            font-weight: 700;
            color: var(--text-main);
            gap: 16px;
        }

        .faq-toggle-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f0f6f3;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .faq-toggle-icon svg {
            width: 18px;
            height: 18px;
            fill: var(--primary-green);
            transition: transform 0.3s ease;
        }

        .faq-body {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            margin-top: 0;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.7;
            font-weight: 450;
            transition: max-height 0.35s cubic-bezier(0, 1, 0, 1), opacity 0.3s ease, margin-top 0.3s ease;
        }

        .faq-card.active {
            border-color: var(--primary-green);
            background: var(--hover-green);
            box-shadow: 0 8px 25px rgba(8, 116, 67, 0.1);
        }

        .faq-card.active .faq-header {
            color: var(--primary-green);
        }

        .faq-card.active .faq-toggle-icon {
            background: var(--primary-green);
        }

        .faq-card.active .faq-toggle-icon svg {
            fill: #ffffff;
            transform: rotate(180deg);
        }

        .faq-card.active .faq-body {
            max-height: 500px;
            opacity: 1;
            margin-top: 14px;
            transition: max-height 0.4s ease-in, opacity 0.3s ease, margin-top 0.3s ease;
        }

        .no-results {
            text-align: center;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 500;
            padding: 40px 20px;
            display: none;
            background: #fbfdfc;
            border-radius: 16px;
            border: 1px dashed var(--border-color);
        }

        @media (max-width: 991px) {
            .nav-link {
                margin-left: 0;
                padding: 10px 0;
            }
        }

        @media (max-width: 768px) {
            .page-hero {
                min-height: 280px;
                padding-bottom: 75px;
            }

            .page-hero h1 {
                font-size: 32px;
            }

            .page-hero p { font-size: 15px; }

            .content-section { margin-top: -45px; }

            .content-card {
                padding: 30px 20px;
                border-radius: 20px;
            }

            .faq-subtitle {
                font-size: 24px;
            }

            .faq-card {
                padding: 18px 20px;
            }

            .faq-header {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<section class="page-hero">
    <div class="container">
        <h1>Frequently Asked Questions</h1>
        <p>Explore answers to common queries about the AlumTrace Alumni Tracking System</p>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <div class="content-card">
            <p class="faq-subtitle">Find answers to common questions about AlumTrace.</p>

            <div class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" id="faqSearchInput" class="search-input" placeholder="Search questions, support, or hidden keywords (e.g. security, signup, edit)...">
            </div>

            <div class="faq-list" id="faqList">
                <div class="faq-card" data-keywords="overview application features system scope definition">
                    <div class="faq-header">
                        <span>What is AlumTrace?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        AlumTrace is an Alumni Tracking System designed to maintain updated alumni profiles, employment information, education records, certifications, surveys, and alumni activities.
                    </div>
                </div>

                <div class="faq-card" data-keywords="sign up signup registration register user create profile join">
                    <div class="faq-header">
                        <span>Who can create an account?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        Alumni who are allowed to use the system can create an account through the registration page.
                    </div>
                </div>

                <div class="faq-card" data-keywords="features portal privileges access features update survey events announcements">
                    <div class="faq-header">
                        <span>What can alumni do after logging in?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        Alumni can manage their profile, employment information, education and certifications, answer surveys, view the alumni directory, and view events and announcements.
                    </div>
                </div>

                <div class="faq-card" data-keywords="network database graduate records list lookup search batch classmates">
                    <div class="faq-header">
                        <span>What is the purpose of the Alumni Directory?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        The Alumni Directory helps organize alumni information and makes it easier to maintain updated records of graduates.
                    </div>
                </div>

                <div class="faq-card" data-keywords="edit modify details personal employment status profile settings">
                    <div class="faq-header">
                        <span>Can I update my information?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        Yes. Logged-in alumni can update their profile and other information through their dashboard.
                    </div>
                </div>

                <div class="faq-card" data-keywords="administration management controls manage records reports batches system control">
                    <div class="faq-header">
                        <span>What can administrators do?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        Administrators can manage alumni records, programs and batches, surveys, reports, users, events, announcements, and system settings.
                    </div>
                </div>

                <div class="faq-card" data-keywords="security privacy data protection safe credentials confidentiality safety">
                    <div class="faq-header">
                        <span>Is my information protected?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        AlumTrace is designed to protect alumni information and limit access to authorized users and administrators.
                    </div>
                </div>

                <div class="faq-card" data-keywords="reset password passkey recovery locked account help restore lost account">
                    <div class="faq-header">
                        <span>What should I do if I forget my password?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        You can select the Forgot Password option on the Login page to begin the password recovery process.
                    </div>
                </div>

                <div class="faq-card" data-keywords="guest public browse access without login non-registered viewer landing">
                    <div class="faq-header">
                        <span>Do I need an account to view the public pages?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        No. Public pages such as Home, About Us, FAQs, Data Privacy, and Terms & Conditions can be viewed without logging in.
                    </div>
                </div>

                <div class="faq-card" data-keywords="help desk support email contact admin message technical assistance inquiry">
                    <div class="faq-header">
                        <span>How can I contact the system administrators?</span>
                        <div class="faq-toggle-icon">
                            <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    <div class="faq-body">
                        For account or system concerns, contact the designated administrators of the AlumTrace Alumni Tracking System.
                    </div>
                </div>

            </div>

            <div id="noResults" class="no-results">
                No matching questions found matching your search.
            </div>

        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.faq-card').forEach(card => {
        card.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });

    document.getElementById('faqSearchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.faq-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardText = card.innerText.toLowerCase();
            const hiddenKeywords = (card.getAttribute('data-keywords') || '').toLowerCase();

            if (cardText.includes(query) || hiddenKeywords.includes(query)) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
    });
</script>
</body>
</html>