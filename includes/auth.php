<?php
// includes/auth.php
require_once 'config.php';

/**
 * Check if user is authenticated (logged in)
 * If not, redirect to login page
 */
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Check if user has the required role
 * @param array $allowedRoles - Array of roles allowed to access the page
 * Example: checkRole(['Administrator', 'Waiter'])
 */
function checkRole($allowedRoles = []) {
    checkAuth(); // First check if logged in
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Check if current user is an Administrator
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Administrator';
}

/**
 * Check if current user is a Waiter
 * @return bool
 */
function isWaiter() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Waiter';
}

/**
 * Check if current user is Kitchen Staff
 * @return bool
 */
function isKitchen() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Kitchen Staff';
}

/**
 * Get current user's full name
 * @return string
 */
function getCurrentUserName() {
    if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    return 'User';
}

/**
 * Get current user's role
 * @return string|null
 */
function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Redirect user to their appropriate dashboard based on role
 */
function redirectToDashboard() {
    if (!isset($_SESSION['role'])) {
        header('Location: ../login.php');
        exit();
    }
    
    switch ($_SESSION['role']) {
        case 'Administrator':
            header('Location: ../admin/dashboard.php');
            break;
        case 'Waiter':
            header('Location: ../waiter/dashboard.php');
            break;
        case 'Kitchen Staff':
            header('Location: ../kitchen/dashboard.php');
            break;
        default:
            header('Location: ../login.php');
    }
    exit();
}
?>