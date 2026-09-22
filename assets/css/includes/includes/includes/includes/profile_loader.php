<?php
if (!isset($connection)) {
    require_once "connection.php";
}

$userEmail = $_SESSION["user_email"] ?? "";

$profilePicture = "";
$profilePictureUrl = "";
$hasProfilePicture = false;

$profileData = [
    "first_name"   => $_SESSION["first_name"] ?? "",
    "middle_name"  => $_SESSION["middle_name"] ?? "",
    "last_name"    => $_SESSION["last_name"] ?? "",
    "phone"        => "",
    "birthdate"    => "",
    "address"      => "",
    "city"         => "",
    "gender"       => "",
    "civil_status" => "",
    "course"       => "",
    "batch_year"   => ""
];

/* Check if alumni_profiles table exists */
$tableCheck = mysqli_query(
    $connection,
    "SHOW TABLES LIKE 'alumni_profiles'"
);

if ($tableCheck && mysqli_num_rows($tableCheck) > 0 && $userEmail !== "") {

    $stmt = mysqli_prepare(
        $connection,
        "SELECT * FROM alumni_profiles
         WHERE email = ?
         LIMIT 1"
    );

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "s", $userEmail);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            foreach ($profileData as $key => $value) {
                if (isset($row[$key])) {
                    $profileData[$key] = $row[$key];
                }
            }

            $profilePicture = $row["profile_picture"] ?? "";
        }

        mysqli_stmt_close($stmt);
    }
}

/* Check profile picture */
if (!empty($profilePicture) && file_exists(__DIR__ . "/../" . $profilePicture)) {
    $hasProfilePicture = true;
    $profilePictureUrl = $profilePicture . "?v=" . time();
}
?>