<?php
// Database configuration
$host = 'sql101.infinityfree.com';
$dbname = 'if0_39527570_escrow';
$username = 'if0_39527570'; // Replace with your MySQL username
$password = 'lqGMSJIMWjz'; // Replace with your MySQL password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO attributes for error handling and security
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use real prepared statements
    
} catch (PDOException $e) {
    // Log error (in production, log to a file instead of displaying)
    error_log("Database connection failed: " . $e->getMessage());
    // Display user-friendly message
    die("Connection failed. Please try again later.");
}
?>
