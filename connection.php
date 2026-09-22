<?php

$connection = mysqli_connect(
    "localhost",
    "root",
    "",
    "alum"
);

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>