<?php
// Reusable Topbar
$userName = trim(
    ($_SESSION["first_name"] ?? "") . " " .
    ($_SESSION["middle_name"] ?? "") . " " .
    ($_SESSION["last_name"] ?? "")
);

if ($userName === "") {
    $userName = "Alumni User";
}

$userInitial = strtoupper(substr($userName, 0, 1));

$profileImage = "";
if (!empty($profilePicture) && file_exists(__DIR__ . "/../" . $profilePicture)) {
    $profileImage = "../" . $profilePicture;
}
?>

<header class="topbar">

    <div class="topbar-left">
        <button id="sidebarToggle" class="menu-btn">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div>
            <h5 class="page-title"><?= htmlspecialchars($pageTitle ?? "Alumni Tracking System") ?></h5>
            <small class="page-subtitle">Welcome back, <?= htmlspecialchars($userName) ?></small>
        </div>
    </div>

    <div class="topbar-right">

        <div class="profile-dropdown">

            <?php if ($profileImage): ?>
                <img src="<?= htmlspecialchars($profileImage) ?>"
                     class="profile-avatar"
                     alt="Profile Picture">
            <?php else: ?>
                <div class="profile-avatar initials">
                    <?= $userInitial ?>
                </div>
            <?php endif; ?>

            <div class="profile-info">
                <strong><?= htmlspecialchars($userName) ?></strong>
                <small>Alumni User</small>
            </div>

        </div>

    </div>

</header>