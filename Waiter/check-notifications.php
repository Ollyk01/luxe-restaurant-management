<?php
// Waiter/check-notifications.php
session_start();
require_once '../database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$waiter_id = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) as count FROM notifications 
        WHERE waiter_id = $waiter_id AND is_read = 0";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['count']]);
} else {
    echo json_encode(['count' => 0]);
}
?>