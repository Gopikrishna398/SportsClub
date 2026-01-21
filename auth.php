<?php
// --- Final, Server-Side Redirect Authentication Backend ---

// Show all errors for easier debugging during development.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session at the very beginning.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration file.
require_once 'config.php';

// Check if an 'action' was sent.
if (!isset($_GET['action'])) {
    // If no action, redirect to login.
    header("location: index.php");
    exit;
}
$action = $_GET['action'];


// --- LOGIN ACTION ---
if ($action == 'login') {
    // We only proceed if the form was submitted with the required fields.
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['password'])) {
        
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Prepare a statement to prevent SQL injection.
        $stmt = $conn->prepare("SELECT id, username, password, role, force_password_change FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // This logic correctly handles both hashed and plain-text passwords.
            $is_password_correct = password_verify($password, $user['password']) || ($password === $user['password']);

            if ($is_password_correct) {
                // Password is correct, create the session.
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = strtolower($user['role']); // Standardize the role

                // Check if a password change is required.
                if ($user['force_password_change'] == 1) {
                    header("location: setPassword.php");
                    exit;
                }

                // Redirect to the correct dashboard based on role.
                switch ($_SESSION['role']) {
                    case 'admin':
                        header("location: adminDashboard.php");
                        break;
                    case 'volunteer':
                        header("location: volunteerDashboard.php");
                        break;
                    case 'student':
                        header("location: dashboard.php");
                        break;
                    default:
                        // This should not happen if roles in DB are correct.
                        header("location: index.php?error=invalid_role");
                        break;
                }
                exit;

            } else {
                // Incorrect password.
                header("location: index.php?error=1");
                exit;
            }
        } else {
            // Incorrect username.
            header("location: index.php?error=1");
            exit;
        }
        $stmt->close();
    }
}

// --- REGISTER ACTION ---
elseif ($action == 'register') {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = 'student'; // Force all new registrations to be students.

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            // User exists, redirect back with an error.
            header("location: register.html?error=exists");
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful!'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error during registration.'
        ]);
        exit;
    }
    }
}

// If the action is not recognized or something else goes wrong, redirect to login.
header("location: index.php");
exit;
?>