<?php
// db_connect.php - Database connection file for School Record System

// Database credentials
$servername = "localhost"; // Your MySQL server name (usually localhost with XAMPP)
$username = "root";      // Your MySQL username (default for XAMPP)
$password = "";          // !! IMPORTANT: Set your MySQL root password here if you have one !!
$dbname = "school_record_system"; // Your database name

// Create connection using mysqli (improved extension for MySQL)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, stop script execution and display error (for debugging)
    // In a real application, you would log this error instead of displaying it to users
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better character support (emojis, special characters)
$conn->set_charset("utf8mb4");

// Note: In a production environment, avoid displaying detailed error messages to users.
// Also, consider more secure ways to handle database credentials (e.g., environment variables, not hardcoding).

?>
