<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/connection.php";

$message = "";
$messageType = "";

$search = trim($_GET["q"] ?? "");
$users = [];

$sql = "
    SELECT u.id, u.email, u.first_name, u.middle_name, u.last_name, u.student_id, u.course, u.batch_year,
           p.city, p.profile_picture
    FROM users u
    LEFT JOIN alumni_profiles p ON p.email = u.email
    WHERE COALESCE(u.role, '') <> 'admin'
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (
        u.email LIKE ? OR u.first_name LIKE ? OR u.middle_name LIKE ? OR u.last_name LIKE ? OR
        u.student_id LIKE ? OR u.course LIKE ? OR u.batch_year LIKE ? OR COALESCE(p.city, '') LIKE ?
    )";
    $like = "%" . $search . "%";
    $params = [$like, $like, $like, $like, $like, $like, $like, $like];
    $types = "ssssssss";
}

$sql .= " ORDER BY u.last_name ASC, u.first_name ASC, u.id ASC";

$stmt = mysqli_prepare($connection, $sql);
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $row["full_name"] = trim(($row["first_name"] ?? "") . " " . ($row["middle_name"] ?? "") . " " . ($row["last_name"] ?? ""));
        if ($row["full_name"] === "") {
            $row["full_name"] = "Alumni";
        }
        $users[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    $message = "Unable to load alumni records.";
    $messageType = "danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Alumni | AlumTrace</title>
    <link rel="icon" type="image/png" href="images/Seal.png?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --green: #0f5132; --green-2: #198754; --green-soft: #eaf6ef; --text:#1f2d24; --muted:#536760; --bg:#f4f7f5; --card:#fff; --border:#e7ece9;
        }
        * { box-sizing: border-box; }
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
        .panel { background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 10px 30px rgba(15,81,50,0.06); padding:22px; }
        .header-row { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:20px; }
        h1 { margin:0; color:var(--green); }
        .toolbar { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .search { width:320px; border:1px solid var(--border); background:#fff; border-radius:12px; padding:11px 14px; font:inherit; }
        .btn { display:inline-flex; align-items:center; gap:8px; border:none; cursor:pointer; border-radius:12px; padding:11px 16px; font-weight:700; }
        .btn-primary { background:var(--green); color:#fff; }
        .btn-secondary { background:#f4f7f5; color:var(--green); }
        .alert { margin-bottom:18px; padding:12px 16px; border-radius:12px; font-weight:600; }
        .alert-danger { background:#fdecec; color:#7a1f1f; border:1px solid #f3c7c7; }
        .table-wrap { overflow:auto; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { text-align:left; padding:12px 10px; border-bottom:1px solid var(--border); vertical-align:middle; }
        th { background:#f7faf8; color:var(--green); font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; }
        .pill { display:inline-flex; padding:5px 8px; border-radius:999px; background:var(--green-soft); color:var(--green); font-size:0.72rem; font-weight:700; }
        .muted { color:var(--muted); }
        @media (max-width: 900px) {
            .sidebar { display:none; }
            .content { padding:20px 16px 28px; }
            .header-row { flex-direction:column; align-items:flex-start; }
            .search { width:100%; }
        }
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
            <a class="active" href="ManageAlumni.php"><i class="fa-solid fa-users"></i> Manage Alumni</a>
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
                <div class="avatar"><?= htmlspecialchars(strtoupper(substr($adminInitial, 0, 1))); ?></div>
                <a class="logout" href="Logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </header>

        <div class="content">
            <div class="header-row">
                <h1>Manage Alumni</h1>
                <div class="toolbar">
                    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap;">
                        <input class="search" type="text" name="q" value="<?= htmlspecialchars($search); ?>" placeholder="Search alumni...">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                        <?php if ($search !== ""): ?>
                            <a class="btn btn-secondary" href="ManageAlumni.php">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php if ($message !== ""): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType); ?>"><?= htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="panel">
                <div class="muted" style="margin-bottom:12px;">Showing <?= count($users); ?> alumni record(s)</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Student ID</th>
                                <th>Course</th>
                                <th>Batch</th>
                                <th>City</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="muted" style="padding:18px; text-align:center;">No alumni found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user["full_name"]); ?></td>
                                        <td><?= htmlspecialchars($user["email"] ?? "-"); ?></td>
                                        <td><?= htmlspecialchars($user["student_id"] ?? "-"); ?></td>
                                        <td><?= htmlspecialchars($user["course"] ?? "-"); ?></td>
                                        <td><?= htmlspecialchars($user["batch_year"] ?? "-"); ?></td>
                                        <td><?= htmlspecialchars($user["city"] ?? "-"); ?></td>
                                        <td><span class="pill">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
