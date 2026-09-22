<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/connection.php";

$stats = [
    "alumni" => 0,
    "profiles" => 0,
    "surveys" => 0,
    "announcements" => 0,
];

$alumniQuery = mysqli_query($connection, "SELECT COUNT(*) AS total FROM users WHERE COALESCE(role, '') <> 'admin'");
if ($alumniQuery) {
    $row = mysqli_fetch_assoc($alumniQuery);
    $stats["alumni"] = (int) ($row["total"] ?? 0);
}

$profilesQuery = mysqli_query($connection, "SELECT COUNT(*) AS total FROM alumni_profiles");
if ($profilesQuery) {
    $row = mysqli_fetch_assoc($profilesQuery);
    $stats["profiles"] = (int) ($row["total"] ?? 0);
}

$surveysQuery = mysqli_query($connection, "SELECT COUNT(*) AS total FROM surveys");
if ($surveysQuery) {
    $row = mysqli_fetch_assoc($surveysQuery);
    $stats["surveys"] = (int) ($row["total"] ?? 0);
}

$announcementsQuery = mysqli_query($connection, "SELECT COUNT(*) AS total FROM announcements");
if ($announcementsQuery) {
    $row = mysqli_fetch_assoc($announcementsQuery);
    $stats["announcements"] = (int) ($row["total"] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AlumTrace</title>
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --green: #0f5132;
            --green-2: #198754;
            --green-soft: #e9f7ef;
            --text: #1f2d24;
            --muted: #5a6c62;
            --card: #ffffff;
            --bg: #f4f7f5;
            --border: #e6ece8;
            --shadow: 0 10px 30px rgba(15,81,50,0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        a { text-decoration: none; color: inherit; }
        .admin-shell { display: flex; min-height: 100vh; }
        .sidebar {
            width: 250px; background: #fff; border-right: 1px solid var(--border);
            padding: 18px 12px; position: sticky; top: 0; height: 100vh;
        }
        .brand {
            display: flex; align-items: center; gap: 12px; padding: 10px 12px 18px;
            border-bottom: 1px solid var(--border); margin-bottom: 16px;
        }
        .brand img { width: 36px; height: 36px; }
        .brand-title { font-weight: 700; color: var(--green); }
        .nav { display: flex; flex-direction: column; gap: 8px; }
        .nav a {
            display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px;
            color: var(--muted); font-weight: 600;
        }
        .nav a.active, .nav a:hover {
            background: var(--green-soft); color: var(--green); box-shadow: inset 0 0 0 1px rgba(15,81,50,0.04);
        }
        .main { flex: 1; }
        .topbar {
            height: 74px; display: flex; align-items: center; justify-content: space-between; padding: 0 32px;
            background: linear-gradient(135deg, var(--green), var(--green-2)); color: white;
            box-shadow: 0 8px 20px rgba(15,81,50,0.12);
        }
        .topbar .user { display: flex; align-items: center; gap: 14px; }
        .topbar .avatar {
            width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .topbar .meta { text-align: right; }
        .topbar .meta .name { font-weight: 700; }
        .topbar .meta .email { opacity: 0.8; font-size: 0.8rem; }
        .logout {
            display: inline-flex; align-items: center; gap: 8px; color: white; padding: 9px 14px; border-radius: 10px;
            background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.2);
        }
        .content { padding: 28px 30px 40px; }
        .banner {
            background: linear-gradient(135deg, var(--green), var(--green-2)); color: white; border-radius: 18px;
            padding: 28px 32px; box-shadow: var(--shadow); margin-bottom: 28px;
        }
        .banner h1 { margin: 0 0 8px; font-size: 2rem; }
        .banner p { margin: 0; opacity: 0.9; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(180px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .stat-card {
            background: var(--card); padding: 22px; border: 1px solid var(--border); border-radius: 18px; box-shadow: var(--shadow);
        }
        .stat-card .label { color: var(--muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; }
        .stat-card .value { font-size: 2rem; font-weight: 700; margin-top: 12px; color: var(--green); }
        .grid {
            display: grid; grid-template-columns: repeat(2, minmax(240px, 1fr)); gap: 18px;
        }
        .panel {
            background: var(--card); border: 1px solid var(--border); border-radius: 18px; box-shadow: var(--shadow); padding: 22px;
        }
        .panel h3 { margin: 0 0 16px; color: var(--green); }
        .quick-links { display: grid; gap: 12px; }
        .quick-links a {
            display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; border-radius: 12px;
            background: #f9fbfa; border: 1px solid var(--border); color: var(--text); font-weight: 600;
        }
        .quick-links a:hover { background: var(--green-soft); }
        .status-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; }
        .status-list li { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .status-list li:last-child { border-bottom: none; }
        .badge {
            display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: var(--green-soft); color: var(--green);
        }
        @media (max-width: 900px) {
            .sidebar { display: none; }
            .stats, .grid { grid-template-columns: 1fr; }
            .topbar { padding: 0 18px; }
            .content { padding: 20px 18px 30px; }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar">
            <div class="brand">
                <img src="images/Seal.png" alt="AlumTrace Logo">
                <div class="brand-title">AlumTrace</div>
            </div>
            <nav class="nav" aria-label="Admin sidebar">
                <a class="active" href="AdminDashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="ManageAlumni.php"><i class="fa-solid fa-users"></i> Manage Alumni</a>
                <a href="ManageSurveys.php"><i class="fa-solid fa-square-poll-horizontal"></i> Tracer Surveys</a>
                <a href="ManageAnnouncements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
                <a href="Reports.php"><i class="fa-solid fa-chart-column"></i> Reports</a>
                <a href="AdminSettings.php"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
        </aside>

        <main class="main">
            <header class="topbar">
                <div></div>
                <div class="user">
                    <div class="meta">
                        <div class="name"><?= htmlspecialchars($adminName); ?></div>
                        <div class="email"><?= htmlspecialchars($adminEmail); ?></div>
                    </div>
                    <div class="avatar"><?= htmlspecialchars(substr($adminInitial, 0, 1)); ?></div>
                    <a class="logout" href="Logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </header>

            <div class="content">
                <div class="banner">
                    <h1>Admin Dashboard</h1>
                    <p>Monitor alumni accounts, survey activity, announcements, and system performance in one place.</p>
                </div>

                <section class="stats">
                    <div class="stat-card">
                        <div class="label">Total alumni</div>
                        <div class="value"><?= number_format($stats["alumni"]); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Profile records</div>
                        <div class="value"><?= number_format($stats["profiles"]); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Survey forms</div>
                        <div class="value"><?= number_format($stats["surveys"]); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Announcements</div>
                        <div class="value"><?= number_format($stats["announcements"]); ?></div>
                    </div>
                </section>

                <section class="grid">
                    <div class="panel">
                        <h3>Quick actions</h3>
                        <div class="quick-links">
                            <a href="ManageAlumni.php"><span><i class="fa-solid fa-users"></i> Manage Alumni</span><i class="fa-solid fa-arrow-right"></i></a>
                            <a href="ManageSurveys.php"><span><i class="fa-solid fa-square-poll-horizontal"></i> Tracer Surveys</span><i class="fa-solid fa-arrow-right"></i></a>
                            <a href="ManageAnnouncements.php"><span><i class="fa-solid fa-bullhorn"></i> Announcements</span><i class="fa-solid fa-arrow-right"></i></a>
                            <a href="Reports.php"><span><i class="fa-solid fa-chart-column"></i> Reports</span><i class="fa-solid fa-arrow-right"></i></a>
                            <a href="AdminSettings.php"><span><i class="fa-solid fa-gear"></i> Settings</span><i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="panel">
                        <h3>System snapshot</h3>
                        <ul class="status-list">
                            <li><span>Database</span><span class="badge">Connected</span></li>
                            <li><span>Admin access</span><span class="badge">Active</span></li>
                            <li><span>Pending review</span><span class="badge"><?= number_format(max(0, $stats["alumni"] - $stats["profiles"])); ?></span></li>
                            <li><span>Site status</span><span class="badge">Online</span></li>
                        </ul>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
