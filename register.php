<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = 'localhost';
$dbname = 'escrow_p2p';
$username = 'your_db_username'; // Replace with your MySQL username
$password = 'your_db_password'; // Replace with your MySQL password

// Response array
$response = ['success' => false, 'message' => ''];

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate inputs
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $password = $_POST['password']; // Password is not sanitized to preserve its integrity
        $confirm_password = $_POST['confirm_password'];

        // Validation checks
        if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
            $response['message'] = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
        } elseif ($password !== $confirm_password) {
            $response['message'] = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $response['message'] = 'Password must be at least 8 characters long.';
        } else {
            // Check for duplicate email
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $response['message'] = 'Email is already registered.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert user into the database
                $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $email, $phone, $hashed_password]);

                $response['success'] = true;
                $response['message'] = 'Registration successful! You can now <a href="login.html">log in</a>.';
            }
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
