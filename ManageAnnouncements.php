<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/connection.php";

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_announcement"])) {
    $title = trim($_POST["title"] ?? "");
    $messageText = trim($_POST["message"] ?? "");
    $status = in_array($_POST["status"] ?? "draft", ["draft", "published"], true) ? $_POST["status"] : "draft";

    if ($title === "" || $messageText === "") {
        $message = "Title and message are required.";
        $messageType = "danger";
    } else {
        $stmt = mysqli_prepare($connection, "INSERT INTO announcements (title, message, status) VALUES (?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $title, $messageText, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = "Announcement saved successfully.";
            $messageType = "success";
        } else {
            $message = "Unable to save announcement.";
            $messageType = "danger";
        }
    }
}

if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];
    if ($id > 0) {
        mysqli_query($connection, "DELETE FROM announcements WHERE id = $id");
    }
    header("Location: ManageAnnouncements.php");
    exit;
}

$announcements = [];
$result = mysqli_query($connection, "SELECT * FROM announcements ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $announcements[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements | AlumTrace</title>
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
        .grid { display:grid; grid-template-columns: 0.9fr 1.1fr; gap:20px; }
        .panel { background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 10px 30px rgba(15,81,50,0.06); padding:22px; }
        h1 { margin:0 0 20px; color:var(--green); }
        .alert { padding:12px 16px; border-radius:12px; margin-bottom:18px; font-weight:600; }
        .alert-success { background:#edfdf3; border:1px solid #bfe9cf; color:#195b3a; }
        .alert-danger { background:#fdecec; border:1px solid #f3c7c7; color:#7a1f1f; }
        .field { display:flex; flex-direction:column; gap:8px; margin-bottom:14px; }
        .field input, .field textarea, .field select { border:1px solid var(--border); border-radius:12px; padding:12px 14px; font:inherit; }
        .field textarea { min-height:120px; resize:vertical; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:none; border-radius:12px; padding:11px 16px; font-weight:700; cursor:pointer; }
        .btn-primary { background:var(--green); color:#fff; }
        .btn-danger { background:#e53935; color:#fff; }
        .list { list-style:none; margin:0; padding:0; display:grid; gap:12px; }
        .list li { padding:14px; border:1px solid var(--border); border-radius:12px; background:#fafcfb; }
        .title { font-weight:700; margin-bottom:6px; }
        .meta { color:var(--muted); font-size:0.82rem; }
        .tag { display:inline-flex; padding:5px 8px; border-radius:999px; background:var(--green-soft); color:var(--green); font-size:0.72rem; font-weight:700; }
        .row-actions { display:flex; align-items:center; gap:10px; margin-top:10px; flex-wrap:wrap; }
        @media (max-width: 900px) { .sidebar { display:none; } .content { padding:20px 16px 28px; } .grid { grid-template-columns:1fr; } }
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
            <a class="active" href="ManageAnnouncements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
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
            <h1>Announcements</h1>

            <?php if ($message !== ""): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType); ?>"><?= htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="grid">
                <div class="panel">
                    <h3 style="margin-top:0; color:var(--green);">Published posts</h3>
                    <?php if (empty($announcements)): ?>
                        <p class="meta">No announcements yet.</p>
                    <?php else: ?>
                        <ul class="list">
                            <?php foreach ($announcements as $announcement): ?>
                                <li>
                                    <div class="title"><?= htmlspecialchars($announcement["title"] ?? "Untitled"); ?></div>
                                    <div class="meta"><?= htmlspecialchars($announcement["message"] ?? ""); ?></div>
                                    <div class="row-actions">
                                        <span class="tag"><?= htmlspecialchars(strtoupper($announcement["status"] ?? "draft")); ?></span>
                                        <span class="meta"><?= htmlspecialchars($announcement["created_at"] ?? ""); ?></span>
                                        <a class="btn btn-danger" href="ManageAnnouncements.php?delete=<?= (int)($announcement["id"] ?? 0); ?>" onclick="return confirm('Delete this announcement?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="panel">
                    <h3 style="margin-top:0; color:var(--green);">New announcement</h3>
                    <form method="post">
                        <div class="field">
                            <label for="title">Title</label>
                            <input id="title" name="title" type="text" required>
                        </div>
                        <div class="field">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        <div class="field">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit" name="save_announcement"><i class="fa-solid fa-paper-plane"></i> Save Announcement</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
