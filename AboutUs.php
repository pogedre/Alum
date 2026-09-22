<?php
session_start();

$developers = [
    [
        "name" => "Janeer Castillo",
        "role" => "Backend Developer",
        "initials" => "JC",
        "image" => "profile/janeer.jpg",
        "description" => "Responsible for backend development, PHP functionality, database integration, and server-side features."
    ],
    [
        "name" => "Maria Christzyll Dandan",
        "role" => "Lead & Frontend Developer",
        "initials" => "MD",
        "image" => "profile/dandan.jpg",
        "description" => "Responsible for creating responsive and user-friendly website interfaces using HTML, CSS, Bootstrap, and JavaScript."
    ],
    [
        "name" => "Yo Morales",
        "role" => "Frontend Developer",
        "initials" => "YM",
        "image" => "profile/yo.jpg",
        "description" => "Responsible for frontend implementation, page layouts, interactive elements, and responsive design."
    ],
    [
        "name" => "John Errol Osorio",
        "role" => "Database Administrator",
        "initials" => "JO",
        "image" => "profile/errol.jpg",
        "description" => "Responsible for database design, implementation, and maintenance."
    ],
    [
        "name" => "Ahljoice Gliponeo",
        "role" => "UI / UX Designer",
        "initials" => "AG",
        "image" => "profile/aj.jpg",
        "description" => "Responsible for the visual design, user experience, interface layout, and overall usability of the system."
    ]
];

$activePage = 'about';
$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$isAdmin = $isLoggedIn && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
$dashboardLink = $isAdmin ? "AdminDashboard.php" : "Dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us</title>

    <!-- Updated Favicon with Cache Buster -->
    <link rel="icon" type="image/png" href="images/Seal.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --green: #087443;
            --dark-green: #04532f;
            --deep-green: #023b22;
            --gold: #f2c300;
            --dark-gold: #d8a900;
            --light: #f4f8f5;
            --text: #173b2a;
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
            background: var(--light);
            font-family: "Poppins", sans-serif;
            color: var(--text);
        }

        .hero-section {
            min-height: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background-image:
                linear-gradient(
                    rgba(0, 91, 54, 0.72),
                    rgba(0, 91, 54, 0.72)
                ),
                url("images/school-building.png");
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            color: white;
            overflow: hidden;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(
                    135deg,
                    rgba(8, 116, 67, 0.35),
                    rgba(2, 59, 34, 0.30)
                );
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 50px 20px;
        }

        .hero-section h1 {
            font-size: 65px;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        .hero-section p {
            font-size: 22px;
            margin: 0;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .system-section {
            background: var(--light);
        }

        .section-heading {
            color: var(--green);
        }

        .info-box {
            transition: 0.3s;
        }

        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .developer-section {
            background: white;
        }

        .developer-card {
            border: none;
            border-radius: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .developer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.15) !important;
        }

        .profile-avatar {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--dark-green));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            margin: 0 auto;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .role-badge {
            font-size: 0.85rem;
            background-color: var(--green) !important;
        }

        .section-title {
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: "";
            position: absolute;
            width: 60px;
            height: 4px;
            background: var(--gold);
            border-radius: 10px;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
        }

        .btn-success {
            background: var(--green);
            border-color: var(--green);
        }

        .btn-success:hover {
            background: var(--dark-green);
            border-color: var(--dark-green);
        }

        .btn-outline-success {
            color: var(--green);
            border-color: var(--green);
        }

        .btn-outline-success:hover {
            background: var(--green);
            border-color: var(--green);
            color: white;
        }

        @media (max-width: 991px) {
            .hero-section {
                min-height: 380px;
            }

            .hero-section h1 {
                font-size: 48px;
            }

            .hero-section p {
                font-size: 19px;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 350px;
            }

            .hero-section h1 {
                font-size: 42px;
            }

            .hero-section p {
                font-size: 18px;
            }

            .profile-avatar {
                width: 170px;
                height: 170px;
            }
        }

        @media (max-width: 500px) {
            .hero-section {
                min-height: 320px;
            }

            .hero-section h1 {
                font-size: 34px;
            }

            .hero-section p {
                font-size: 16px;
            }

            .profile-avatar {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>

<body>

<?php require_once "includes/navbar.php";?>

<header class="hero-section">
    <div class="hero-content">
        <h1>About Us</h1>
        <p>Alumni Tracking System</p>
    </div>
</header>

<section class="py-5 system-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="fw-bold section-heading mb-3">Our System</h2>
                        <p class="text-secondary">
                            The Alumni Tracking System is designed to maintain
                            updated alumni profiles and employment information
                            for graduating alumni. It provides a centralized
                            platform where alumni can update their information,
                            answer surveys, and stay informed about alumni activities.
                        </p>

                        <h2 class="fw-bold section-heading mt-5 mb-3">Our Purpose</h2>
                        <p class="text-secondary">
                            The system helps administrators understand graduate
                            outcomes by organizing information about employment
                            status, industries, locations, education,
                            certifications, and other relevant alumni information.
                        </p>

                        <div class="row g-4 mt-2">
                            <div class="col-md-4">
                                <div class="info-box p-4 bg-light rounded-4 h-100 text-center">
                                    <h5 class="fw-bold">Alumni</h5>
                                    <p class="text-secondary mb-0">
                                        Update profiles, employment details,
                                        education, and certifications.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-box p-4 bg-light rounded-4 h-100 text-center">
                                    <h5 class="fw-bold">Administrators</h5>
                                    <p class="text-secondary mb-0">
                                        Manage alumni records, programs,
                                        batches, surveys, and reports.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-box p-4 bg-light rounded-4 h-100 text-center">
                                    <h5 class="fw-bold">Community</h5>
                                    <p class="text-secondary mb-0">
                                        Support communication through
                                        directories, events, and announcements.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 developer-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title fw-bold">Meet the Developers</h2>
            <p class="text-secondary mt-3">The team behind the Alumni Tracking System</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($developers as $developer):?>
                <div class="col-md-6 col-lg-4">
                    <div class="card developer-card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="profile-avatar mb-4">
                                <?php if (!empty($developer["image"])):?>
                                    <img src="<?= htmlspecialchars($developer["image"]) ?>" alt="<?= htmlspecialchars($developer["name"]) ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars($developer["initials"]) ?>
                                <?php endif; ?>
                            </div>

                            <h4 class="fw-bold mb-2">
                                <?= htmlspecialchars($developer["name"]) ?>
                            </h4>

                            <span class="badge rounded-pill px-3 py-2 role-badge">
                                <?= htmlspecialchars($developer["role"]) ?>
                            </span>

                            <p class="text-secondary mt-4 mb-0">
                                <?= htmlspecialchars($developer["description"]) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Thank You</h2>
        <p class="text-secondary mb-4">Thank you for visiting our Alumni Tracking System.</p>

        <a href="index.php" class="btn btn-success px-4">Back to Home</a>

        <?php if (!$isLoggedIn): ?>
            <a href="Login.php" class="btn btn-outline-success px-4 ms-2">Login</a>
        <?php else: ?>
            <a href="<?= htmlspecialchars($dashboardLink) ?>" class="btn btn-outline-success px-4 ms-2">Dashboard</a>
        <?php endif; ?>
    </div>
</section>

<?php require_once "includes/footer.php";?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>