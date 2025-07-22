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
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Don't sanitize password to preserve special characters
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Validation checks
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // If no validation errors, proceed with authentication
    if (empty($errors)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Handle "Remember Me" functionality
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), "/"); // 30 days
                    // Store token in database (you may need to add a column to users table)
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user['id']]);
                }

                // Set success message
                $success = "Login successful! Redirecting...";

                // Redirect to the dashboard
                header("Location: ../frontend/index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php if (!empty($errors) || !empty($success)): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Result</title>
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
            <a href="../frontend/login.html" class="btn btn-primary">Back to Login</a>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php endif; ?>