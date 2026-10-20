<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ExploreBangla_Travel";  // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Use UTF-8
$conn->set_charset('utf8mb4');
?>
