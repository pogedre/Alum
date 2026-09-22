<?php
session_start();

$message = $_SESSION["register_message"] ?? "";
$messageType = $_SESSION["register_message_type"] ?? "";

unset($_SESSION["register_message"]);
unset($_SESSION["register_message_type"]);

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

$dashboardLink = $isAdmin ? "AdminDashboard.php" : "Dashboard.php";
$activePage = "register";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register / Sign Up</title>

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
            --text-dark: #173b2a;
            --muted: #66756d;
            --white: #ffffff;
        }

        * {
            font-family: "Poppins", sans-serif;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(
                135deg,
                rgba(2, 59, 34, 0.88),
                rgba(8, 116, 67, 0.82)
            ), url("images/school-building.png") no-repeat center center fixed;
            background-size: cover;
            color: var(--text-dark);
        }

        .register-page {
            min-height: calc(100vh - 72px);
            padding: 60px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .register-container {
            width: 100%;
            max-width: 700px;
            position: relative;
            z-index: 2;
        }

        .register-card {
            border: none;
            border-radius: 22px;
            overflow: hidden;
            background: white;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
        }

        .register-header {
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

        .register-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0 0 8px;
        }

        .register-header p {
            margin: 0;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .register-body {
            padding: 35px 45px 30px;
            background: white;
        }

        .registration-title {
            color: var(--school-green);
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 28px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .form-control {
            min-height: 50px;
            border-radius: 10px;
            border: 1px solid #d8dedb;
            padding: 12px 14px;
            font-size: 14px;
            color: var(--text-dark);
        }

        .form-control:focus {
            border-color: var(--school-green);
            box-shadow: 0 0 0 0.2rem rgba(8, 116, 67, 0.15);
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 18px;
        }

        .name-field {
            width: 100%;
        }

        .middle-name-wrapper {
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 18px;
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

        .password-help {
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 5px;
        }

        .agreement-check {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 8px 0 18px;
            color: var(--muted);
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .agreement-check .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 4px;
            flex-shrink: 0;
            cursor: pointer;
        }

        .agreement-check .form-check-input:checked {
            background-color: var(--school-green);
            border-color: var(--school-green);
        }

        .agreement-check .form-check-input.is-invalid { border-color:#dc3545; }

        .agreement-check label {
            cursor: pointer;
        }

        .agreement-link {
            padding: 0;
            border: none;
            background: transparent;
            color: var(--school-green);
            font-family: inherit;
            font-size: inherit;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
        }

        .agreement-link:hover {
            color: var(--dark-green);
        }

        .register-btn {
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

        .register-btn:hover {
            background: var(--dark-green);
            transform: translateY(-1px);
        }

        .login-link, .back-link {
            color: var(--school-green);
            font-weight: 700;
            text-decoration: none;
        }

        .login-link:hover, .back-link:hover {
            color: var(--dark-green);
            text-decoration: underline;
        }

        .back-link {
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 22px;
        }

        .bottom-links {
            margin-top: 20px;
        }

        .back-home {
            margin-top: 13px;
        }

        .modal-content {
            border: none;
            border-radius: 18px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--school-green), var(--dark-green));
            color: white;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 700;
        }

        .modal-body {
            color: var(--text-dark);
            font-size: 0.92rem;
            line-height: 1.75;
            max-height: 65vh;
            overflow-y: auto;
        }

        .modal-body h5 {
            color: var(--school-green);
            font-size: 1rem;
            font-weight: 700;
            margin-top: 22px;
            margin-bottom: 8px;
        }

        .modal-body h5:first-child { margin-top: 0; }

        .modal-body p {
            color: var(--text-dark);
            font-size: 0.92rem;
            line-height: 1.75;
            margin-bottom: 18px;
        }

        .modal-footer {
            border-top: 1px solid #e5ebe7;
        }

        .modal-close-btn {
            background-color: var(--school-green);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 9px 22px;
            font-weight: 600;
        }

        .modal-close-btn:hover {
            background-color: var(--dark-green);
            color: white;
        }

        @media (max-width: 576px) {
            .register-page { padding: 30px 15px; }
            .register-header { padding: 30px 20px; }
            .register-header h1 { font-size: 1.8rem; }
            .school-logo { width: 75px; height: 75px; }
            .register-body { padding: 25px 20px; }
            .registration-title { font-size: 1.5rem; }
            .name-row {
                grid-template-columns: 1fr;
                gap: 18px;
            }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<main class="register-page">
    <div class="register-container">
        <div class="card register-card">

            <div class="register-header">
                <img src="images/Seal.png" alt="School Logo" class="school-logo">
                <h1>Create Account</h1>
                <p>Register for the Alumni Tracking System</p>
            </div>

            <div class="register-body">

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType === "success" ? "success" : "danger" ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <h2 class="registration-title">Registration</h2>

                <form action="Register.php" method="POST" id="registerForm" novalidate>

                    <div class="name-row">
                        <div class="name-field">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter your last name" required>
                        </div>

                        <div class="name-field">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter your first name" required>
                        </div>
                    </div>

                    <div class="middle-name-wrapper">
                        <label for="middleName" class="form-label">Middle Name (Optional)</label>
                        <input type="text" class="form-control" id="middleName" name="middle_name" placeholder="Enter your middle name">
                    </div>

                    <div class="form-group">
                        <label for="studentId" class="form-label">Student ID Number</label>
                        <input type="text" class="form-control" id="studentId" name="student_id" placeholder="Enter your student ID number" required>
                    </div>

                    <div class="form-group">
                        <label for="course" class="form-label">Program / Course</label>
                        <select class="form-control" id="course" name="course" required>
                            <option value="" selected disabled>Select your program or course</option>
                            <option value="BSCE">BS in Civil Engineering</option>
                            <option value="BSCS">BS in Computer Science</option>
                            <option value="BSIS">BS in Information Systems</option>
                            <option value="BSSW">BS in Social Workers</option>
                            <option value="BSN">BS in Nursing</option>
                            <option value="BSP">BS in Psychology</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="batchYear" class="form-label">Batch / Year Graduated</label>
                        <select class="form-control" id="batchYear" name="batch_year" required>
                            <option value="" selected disabled>Select year graduated</option>
                            <?php
                            $currentYear = date("Y");
                            for ($year = $currentYear; $year >= 1980; $year--) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" minlength="8" required>
                            <button type="button" class="show-password" id="togglePassword">Show</button>
                        </div>
                        <div class="password-help">Password must be at least 8 characters long.</div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" minlength="8" required>
                            <button type="button" class="show-password" id="toggleConfirmPassword">Show</button>
                        </div>
                    </div>

                    <div class="agreement-check">
                        <input type="checkbox" class="form-check-input" id="agreeTerms" name="agree_terms" value="1" required>

                        <label for="agreeTerms">
                            I agree to the
                            <button type="button" class="agreement-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</button>
                            and
                            <button type="button" class="agreement-link" data-bs-toggle="modal" data-bs-target="#privacyModal">Data Privacy Policy</button>.
                        </label>
                    </div>

                    <button type="submit" class="register-btn">Register</button>

                </form>

                <div class="text-center bottom-links">
                    <span class="text-secondary">Already have an account?</span>
                    <a href="Login.php" class="login-link">Login here</a>
                </div>

                <div class="text-center back-home">
                    <a href="index.php" class="back-link">Back to Home</a>
                </div>

            </div>
        </div>
    </div>
</main>

<!-- Terms & Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Acceptance of Terms & Institutional Commitment</h5>
                <p>By creating an account, accessing, or otherwise interacting with the AlumTrace platform, you formally acknowledge, accept, and agree to be bound by these comprehensive Terms and Conditions. This agreement establishes a formal understanding between you and the institution regarding your ethical use of digital school records. If you do not completely agree with any part of these guidelines, you must immediately discontinue your use of the portal.</p>

                <h5>2. User Account Registration & Data Accuracy</h5>
                <p>Users are completely and solely responsible for providing truthful, current, and verifiable information during the account registration process. You are required to maintain strict confidentiality over your username and password credentials. Any actions, updates, or submissions executed through your profile session will be legally attributed directly to you, making account security an absolute priority.</p>

                <h5>3. Acceptable Use and Platform Boundaries</h5>
                <p>The AlumTrace system is strictly designated for legitimate alumni tracking, professional networking, career updates, and authorized institutional communications. Any unauthorized data scraping, malicious penetration testing, automated extraction, or attempts to disrupt or alter server infrastructure are strictly prohibited and will result in immediate legal and administrative repercussions.</p>

                <h5>4. Maintenance of Alumni Records & Profiles</h5>
                <p>Registered graduates and alumni are strongly encouraged to keep their employment records, contact details, and academic milestones up to date. Maintaining accurate data ensures that the university can effectively generate analytical reports, offer relevant placement assistance, and build a thriving, robust professional community network.</p>

                <h5>5. Administrative Oversight & Elevated Privileges</h5>
                <p>Designated institution administrators possess specialized elevated permissions to audit member profiles, approve pending registration requests, monitor network activity, and manage database configurations. These administrative controls are strictly implemented to preserve directory integrity, protect user privacy, and ensure alignment with institutional standards.</p>

                <h5>6. Account Security Protocols & Incident Reporting</h5>
                <p>Sharing your login credentials, password, or security tokens with third parties is strictly forbidden under any circumstances. If you detect or suspect any unauthorized access, suspicious activity, or compromise of your account safety, you are obligated to notify the system administrator immediately to prevent security breaches.</p>

                <h5>7. Modifications to Features and Platform Policies</h5>
                <p>AlumTrace reserves the absolute right to modify, enhance, expand, or temporarily suspend any module, feature, or aspect of the website—as well as update these terms—at any time without prior individual notice. Continued utilization of the portal following any adjustments constitutes your active acceptance of those revised policies.</p>

                <h5>8. Policy Violations & Termination of Access</h5>
                <p>The platform management team retains full authority to suspend, restrict, or permanently terminate any user account found to be in violation of these terms, or if the user engages in fraudulent, harassing, or harmful conduct. Terminated accounts lose all access to institutional networks and historical data profiles permanently.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Data Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Data Privacy Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Our Commitment to Privacy</h5>
                <p>AlumTrace is committed to protecting the personal information provided by alumni while using the Alumni Tracking System.</p>

                <h5>Information We Collect</h5>
                <p>The system may collect information needed to maintain alumni records, including names, email addresses, educational information, employment information, certifications, and survey responses.</p>

                <h5>How We Use Information</h5>
                <p>Information collected through AlumTrace is used to maintain accurate alumni records, support alumni activities, generate reports, and help administrators understand graduate outcomes.</p>

                <h5>Protection of Information</h5>
                <p>Access to account information is restricted through user authentication and administrator controls. Users are responsible for keeping their login credentials confidential.</p>

                <h5>Information Sharing</h5>
                <p>Alumni information should only be accessed and used for legitimate purposes related to the Alumni Tracking System and its operations.</p>

                <h5>User Responsibility</h5>
                <p>Users should provide accurate information and should immediately report concerns regarding unauthorized access or incorrect information.</p>

                <h5>Privacy Updates</h5>
                <p>This privacy information may be updated when system policies or requirements change. Users are encouraged to review this page periodically.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const togglePassword = document.getElementById("togglePassword");
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const registerForm = document.getElementById("registerForm");

    function setupPasswordToggle(button, inputField) {
        button.addEventListener("click", function () {
            const isPassword = inputField.type === "password";
            inputField.type = isPassword ? "text" : "password";
            button.textContent = isPassword ? "Hide" : "Show";
        });
    }

    setupPasswordToggle(togglePassword, passwordInput);
    setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);

    registerForm.addEventListener("submit", function (event) {
        const requiredFields = this.querySelectorAll("[required]");
        let valid = true;

        requiredFields.forEach(function (field) {
            if (field.type === "checkbox") {
                if (!field.checked) {
                    valid = false;
                    field.classList.add("is-invalid");
                } else {
                    field.classList.remove("is-invalid");
                }
            } else if (!field.value.trim()) {
                valid = false;
                field.classList.add("is-invalid");
            } else {
                field.classList.remove("is-invalid");
            }
        });

        if (!valid) {
            event.preventDefault();
            alert("Please complete all required fields and agree to the Terms & Conditions and Data Privacy Policy.");
            return;
        }

        if (passwordInput.value.length < 6) {
            event.preventDefault();
            alert("Password must be at least 6 characters.");
            passwordInput.focus();
            return;
        }

        if (passwordInput.value !== confirmPasswordInput.value) {
            event.preventDefault();
            alert("Passwords do not match.");
            confirmPasswordInput.focus();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>