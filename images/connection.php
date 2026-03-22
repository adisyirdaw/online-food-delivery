<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ella_delivery";

// Create connection
$connect = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$connect) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8 for proper character encoding
mysqli_set_charset($connect, "utf8mb4");

// Optional: Set timezone if needed
date_default_timezone_set('Africa/Addis_Ababa');

// Optional: Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for debugging, 0 for production
?>