<?php
// --- Database Configuration ---

// Database credentials. Replace with your actual database details.
define('DB_SERVER', 'sql304.byetcluster.com');
define('DB_USERNAME', 'if0_40766996'); // Default XAMPP username
define('DB_PASSWORD', 'Chittuluri12');     // Default XAMPP password is empty
define('DB_NAME', 'if0_40766996_sportshub1');

// --- Create Connection ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// --- Check Connection ---
if ($conn->connect_error) {
    // Stop execution and display an error message if connection fails.
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to utf8mb4 for full Unicode support.
$conn->set_charset("utf8mb4");

?>
