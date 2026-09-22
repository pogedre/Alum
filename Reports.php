<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/connection.php";

$summary = [
    'alumni' => 0,
    'profiles' => 0,
    'surveys' => 0,
    'responses' => 0,
    'announcements' => 0,
];

$queries = [
    'alumni' => "SELECT COUNT(*) AS total FROM users WHERE COALESCE(role, '') <> 'admin'",
    'profiles' => "SELECT COUNT(*) AS total FROM alumni_profiles",
    'surveys' => "SELECT COUNT(*) AS total FROM surveys",
    'responses' => "SELECT COUNT(*) AS total FROM survey_responses",
    'announcements' => "SELECT COUNT(*) AS total FROM announcements",
];

foreach ($queries as $key => $sql) {
    $result = mysqli_query($connection, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $summary[$key] = (int) ($row['total'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | AlumTrace</title>
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --green:#0f5132; --green-2:#198754; --green-soft:#eaf6ef; --text:#1f2d24; --muted:#536760; --bg:#f4f7f5; --card:#fff; --border:#e7ece9; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); }
        a { text-decoration:none; color:inherit; }
        .layout { display:flex; min-height:100vh; }
        .sidebar { width:250px; background:#fff; border-right:1px solid var(--border); padding:18px 12px; }
        .brand { display:flex; align-items:center; gap:12px; padding:10px 12px 18px; border-bottom:1px solid var(--border); }
        .brand img { width:36px; height:36px; }
        .brand strong { color:var(--green); font-size:1.1rem; }
        .nav { display:flex; flex-direction:column; gap:8px; margin-top:16px; }
        .nav a { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; color:var(--muted); font-weight:600; }
        .nav a.active, .nav a:hover { background:var(--green-soft); color:var(--green); }
        .main { flex:1; }
        .topbar { height:74px; background:linear-gradient(135deg,var(--green),var(--green-2)); color:#fff; display:flex; align-items:center; justify-content:space-between; padding:0 30px; }
        .user { display:flex; align-items:center; gap:14px; }
        .meta { text-align:right; }
        .meta .name { font-weight:700; }
        .meta .email { opacity:0.8; font-size:0.8rem; }
        .avatar { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.18); font-weight:700; }
        .logout { display:inline-flex; align-items:center; gap:8px; padding:9px 14px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#fff; }
        .content { padding:24px 30px 40px; }
        h1 { margin:0 0 22px; color:var(--green); }
        .stats { display:grid; grid-template-columns: repeat(5, minmax(150px, 1fr)); gap:16px; }
        .stat { background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 10px 30px rgba(15,81,50,0.06); padding:22px; }
        .stat .label { color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-size:0.74rem; }
        .stat .value { color:var(--green); font-size:2rem; font-weight:700; margin-top:12px; }
        .panel { background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 10px 30px rgba(15,81,50,0.06); padding:22px; margin-top:22px; }
        .panel h3 { margin:0 0 14px; color:var(--green); }
        .list { list-style:none; margin:0; padding:0; display:grid; gap:10px; }
        .list li { display:flex; justify-content:space-between; border-bottom:1px solid var(--border); padding:12px 0; }
        .list li:last-child { border-bottom:none; }
        .badge { display:inline-flex; padding:5px 8px; border-radius:999px; background:var(--green-soft); color:var(--green); font-size:0.7rem; font-weight:700; }
        @media (max-width: 900px) { .sidebar { display:none; } .content { padding:20px 16px 28px; } .stats { grid-template-columns: repeat(2, minmax(120px, 1fr)); } }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <img src="images/Seal.png" alt="AlumTrace">
            <strong>AlumTrace</strong>
        </div>
        <nav class="nav">
            <a href="AdminDashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="ManageAlumni.php"><i class="fa-solid fa-users"></i> Manage Alumni</a>
            <a href="ManageSurveys.php"><i class="fa-solid fa-square-poll-horizontal"></i> Tracer Surveys</a>
            <a href="ManageAnnouncements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
            <a class="active" href="Reports.php"><i class="fa-solid fa-chart-column"></i> Reports</a>
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
                <div class="avatar"><?= htmlspecialchars(strtoupper(substr($adminInitial, 0, 1))); ?></div>
                <a class="logout" href="Logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </header>

        <div class="content">
            <h1>System Reports</h1>

            <div class="stats">
                <div class="stat">
                    <div class="label">Alumni</div>
                    <div class="value"><?= number_format($summary['alumni']); ?></div>
                </div>
                <div class="stat">
                    <div class="label">Profiles</div>
                    <div class="value"><?= number_format($summary['profiles']); ?></div>
                </div>
                <div class="stat">
                    <div class="label">Surveys</div>
                    <div class="value"><?= number_format($summary['surveys']); ?></div>
                </div>
                <div class="stat">
                    <div class="label">Responses</div>
                    <div class="value"><?= number_format($summary['responses']); ?></div>
                </div>
                <div class="stat">
                    <div class="label">Announcements</div>
                    <div class="value"><?= number_format($summary['announcements']); ?></div>
                </div>
            </div>

            <div class="panel">
                <h3>Overview</h3>
                <ul class="list">
                    <li><span>Alumni with profile data</span><span class="badge"><?= number_format($summary['profiles']); ?></span></li>
                    <li><span>Survey participation</span><span class="badge"><?= number_format($summary['responses']); ?></span></li>
                    <li><span>Published announcements</span><span class="badge"><?= number_format($summary['announcements']); ?></span></li>
                    <li><span>Ready for review</span><span class="badge"><?= number_format(max(0, $summary['alumni'] - $summary['profiles'])); ?></span></li>
                </ul>
            </div>
        </div>
    </main>
</div>
</body>
</html>
