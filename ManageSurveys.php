<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/connection.php";

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS survey_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type VARCHAR(30) NOT NULL DEFAULT 'text',
    options TEXT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS survey_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    answer TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_survey"])) {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    if ($title === "") {
        $message = "Survey title is required.";
        $messageType = "danger";
    } else {
        $stmt = mysqli_prepare($connection, "INSERT INTO surveys (title, description, active) VALUES (?, ?, 1)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $title, $description);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = "Survey created successfully.";
            $messageType = "success";
        } else {
            $message = "Unable to create survey.";
            $messageType = "danger";
        }
    }
}

$surveys = [];
$surveySql = "
    SELECT s.id, s.title, s.description, s.active, s.created_at,
           COUNT(q.id) AS question_count,
           COUNT(DISTINCT r.email) AS response_count
    FROM surveys s
    LEFT JOIN survey_questions q ON q.survey_id = s.id
    LEFT JOIN survey_responses r ON r.survey_id = s.id
    GROUP BY s.id, s.title, s.description, s.active, s.created_at
    ORDER BY s.created_at DESC
";

$result = mysqli_query($connection, $surveySql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $surveys[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys | AlumTrace</title>
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
        .grid { display:grid; grid-template-columns: 1.2fr 0.8fr; gap:20px; }
        .panel { background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 10px 30px rgba(15,81,50,0.06); padding:22px; }
        h1 { margin:0 0 20px; color:var(--green); }
        .alert { padding:12px 16px; border-radius:12px; margin-bottom:18px; font-weight:600; }
        .alert-success { background:#edfdf3; border:1px solid #bfe9cf; color:#195b3a; }
        .alert-danger { background:#fdecec; border:1px solid #f3c7c7; color:#7a1f1f; }
        .field { display:flex; flex-direction:column; gap:8px; margin-bottom:14px; }
        .field input, .field textarea, .field select { border:1px solid var(--border); border-radius:12px; padding:12px 14px; font:inherit; }
        .field textarea { min-height:110px; resize:vertical; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:none; border-radius:12px; padding:11px 16px; font-weight:700; cursor:pointer; }
        .btn-primary { background:var(--green); color:#fff; }
        .list { list-style:none; margin:0; padding:0; display:grid; gap:12px; }
        .list li { padding:14px; border:1px solid var(--border); border-radius:12px; background:#fafcfb; }
        .title { font-weight:700; margin-bottom:6px; }
        .meta { color:var(--muted); font-size:0.82rem; }
        .tag { display:inline-flex; padding:5px 8px; border-radius:999px; background:var(--green-soft); color:var(--green); font-size:0.72rem; font-weight:700; }
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
            <a class="active" href="ManageSurveys.php"><i class="fa-solid fa-square-poll-horizontal"></i> Tracer Surveys</a>
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
            <h1>Tracer Surveys</h1>

            <?php if ($message !== ""): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType); ?>"><?= htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="grid">
                <div class="panel">
                    <h3 style="margin-top:0; color:var(--green);">Survey list</h3>
                    <?php if (empty($surveys)): ?>
                        <p class="meta">No surveys created yet.</p>
                    <?php else: ?>
                        <ul class="list">
                            <?php foreach ($surveys as $survey): ?>
                                <li>
                                    <div class="title"><?= htmlspecialchars($survey["title"] ?? "Untitled survey"); ?></div>
                                    <div class="meta"><?= htmlspecialchars($survey["description"] ?? "No description"); ?></div>
                                    <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span class="tag"><?= (int)($survey["active"] ?? 1) ? "Active" : "Inactive"; ?></span>
                                        <span class="meta"><?= (int)($survey["question_count"] ?? 0); ?> questions</span>
                                        <span class="meta"><?= (int)($survey["response_count"] ?? 0); ?> responses</span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="panel">
                    <h3 style="margin-top:0; color:var(--green);">Create survey</h3>
                    <form method="post">
                        <div class="field">
                            <label for="title">Title</label>
                            <input id="title" name="title" type="text" placeholder="e.g. Alumni Tracer Survey" required>
                        </div>
                        <div class="field">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Brief description of the survey"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit" name="create_survey"><i class="fa-solid fa-plus"></i> Create Survey</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
