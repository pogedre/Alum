<?php
declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'alum';
$port = (int) (getenv('DB_PORT') ?: 3306);

$connection = mysqli_init();
if (!mysqli_real_connect($connection, $host, $user, $pass, $name, $port)) {
    error_log('AlumTrace database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Database connection is unavailable.');
}
mysqli_set_charset($connection, 'utf8mb4');
