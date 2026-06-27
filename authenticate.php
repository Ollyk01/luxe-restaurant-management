<?php
// authenticate.php
session_start();
require_once "database.php";

// Check if POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: login.php');
    exit();
}

// Get input
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Check if empty
if (empty($username) || empty($password)) {
    header('Location: login.php?error=Please enter username and password');
    exit();
}

// Query database
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: login.php?error=Invalid username or password');
    exit();
}

$user = $result->fetch_assoc();

// Verify password
$password_verify = password_verify($password, $user['password']);

if ($password_verify && $user['status'] == 'active') {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['employee_number'] = $user['employee_number'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    // Redirect based on role
    switch ($user['role']) {
        case 'Administrator':
            header("Location: Admin/dashboard.php");
            break;
        case 'Waiter':
            header("Location: Waiter/dashboard.php");
            break;
        case 'Kitchen Staff':
            header("Location: Kitchen/dashboard.php");
            break;
        default:
            header('Location: login.php?error=Invalid role assigned');
    }
    exit();
} else {
    if ($user['status'] != 'active') {
        header('Location: login.php?error=Account is inactive');
    } else {
        header('Location: login.php?error=Invalid username or password');
    }
    exit();
}
?>