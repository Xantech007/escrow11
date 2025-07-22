<?php
// Start session
session_start();

// Include database connection
require_once '../src/conn.php';

// Initialize variables for error and success messages
$errors = [];
$success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $password = $_POST['password']; // Don't sanitize password to preserve special characters
    $confirm_password = $_POST['confirm_password'];

    // Validation checks
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already registered";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the insert query
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $hashed_password]);

            // Set success message
            $success = "Registration successful! Please login.";
            
            // Optionally, redirect to login page
            // header("Location: ../login.html");
            // exit();
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<?php if (!empty($errors) || !empty($success)): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Result</title>
    <link href="../assets/css/argon-dashboardf27d.css?v=2.0.4" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="../register.html" class="btn btn-primary">Back to Registration</a>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <a href="../login.html" class="btn btn-primary">Proceed to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php endif; ?>