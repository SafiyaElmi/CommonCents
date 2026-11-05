<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "DB_HOST";
$user = "DB_USER";
$pass = "DB_PASSWORD";
$dbname = "DB_NAME";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
