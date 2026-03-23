<?php
$dbHost = '35.212.172.254';
$dbName = 'project_information_db';
$dbUser = 'group_login';
$dbPass = 'group_project123';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
