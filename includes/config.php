<?php
// includes/config.php
session_start();

// Database connection - FIXED PATH FOR RENDER
require_once __DIR__ . '/../database.php';

// Site configuration - UPDATED FOR RENDER
define('SITE_NAME', 'LUXE Restaurant');
define('SITE_URL', 'https://luxe-restaurant-management.onrender.com/');

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// Function to autogenerate employee username
function generateUsername($role) {
    $prefixes = [
        'Administrator' => 'AD',
        'Waiter' => 'WEP',
        'Kitchen Staff' => 'KD'
    ];
    
    $prefix = $prefixes[$role];
    
    // Get the last employee number for this role
    global $conn;
    $sql = "SELECT employee_number FROM users WHERE employee_number LIKE '$prefix%' ORDER BY employee_number DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['employee_number'], strlen($prefix)));
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }
    
    return $prefix . $newNumber;
}

// Function to generate order number
function generateOrderNumber() {
    global $conn;
    $sql = "SELECT order_number FROM orders ORDER BY order_id DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['order_number'], 1));
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }
    
    return 'O' . $newNumber;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user data
function getCurrentUser() {
    global $conn;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT * FROM users WHERE user_id = $user_id";
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }
    return null;
}
?>