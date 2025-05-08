<?php
// Databaze
$host = 'localhost';
$db   = 'social_web_app';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?> 