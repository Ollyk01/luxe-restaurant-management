<?php
// authenticate.php
session_start();
require_once "database.php";

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>LOGIN DEBUG</h2>";

// Check if POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "Not a POST request<br>";
    exit();
}

echo "POST request received<br><br>";

// Get input
$username = isset($_POST['username']) ? $_POST['username'] : 'NOT SET';
$password = isset($_POST['password']) ? $_POST['password'] : 'NOT SET';

echo "Username entered: '" . $username . "'<br>";
echo "Password entered: '" . $password . "'<br><br>";

// Check if empty
if (empty($username) || empty($password)) {
    echo "Fields are empty!<br>";
    exit();
}

// Query database
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No user found with username: '" . $username . "'<br>";
    
    // Show all users
    echo "<br>📋 All users in database:<br>";
    $all = $conn->query("SELECT username FROM users");
    while ($row = $all->fetch_assoc()) {
        echo "- '" . $row['username'] . "'<br>";
    }
    exit();
}

$user = $result->fetch_assoc();

echo "User found!<br>";
echo "Username: " . $user['username'] . "<br>";
echo "Role: " . $user['role'] . "<br>";
echo "Status: " . $user['status'] . "<br>";
echo "Password hash: " . $user['password'] . "<br><br>";

// Verify password
$password_verify = password_verify($password, $user['password']);
echo "Password verify: " . ($password_verify ? "MATCHES" : "DOES NOT MATCH") . "<br><br>";

if ($password_verify && $user['status'] == 'active') {
    echo "LOGIN SUCCESSFUL! <br>";
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['employee_number'] = $user['employee_number'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
  
    header("Location: Kitchen/dashboard.php");
    exit();
} else {
    echo "Login failed<br>";
    if (!$password_verify) echo "Reason: Password does not match<br>";
    if ($user['status'] != 'active') echo "Reason: Account is inactive<br>";
}
?>