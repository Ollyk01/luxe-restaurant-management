<?php
// includes/functions.php

// Function to mark order as ready and create notification
function markOrderReady($order_id) {
    global $conn;
    
    // Update order status to Ready
    $sql = "UPDATE orders SET order_status = 'Ready' WHERE order_id = $order_id";
    if ($conn->query($sql)) {
        // Get order details
        $order_sql = "SELECT order_number, waiter_id FROM orders WHERE order_id = $order_id";
        $order_result = $conn->query($order_sql);
        $order = $order_result->fetch_assoc();
        
        // Insert notification
        $notification_sql = "INSERT INTO notifications (order_id, waiter_id, order_number, message, is_read, created_at) 
                             VALUES ($order_id, {$order['waiter_id']}, '{$order['order_number']}', 'Order {$order['order_number']} is ready for collection', 0, NOW())";
        $conn->query($notification_sql);
        
        return true;
    }
    return false;
}

// Function to get unread notifications for a waiter
function getWaiterNotifications($waiter_id) {
    global $conn;
    $sql = "SELECT * FROM notifications 
            WHERE waiter_id = $waiter_id 
            AND is_read = 0 
            ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    return $notifications;
}

// Function to mark notification as read
function markNotificationRead($notification_id) {
    global $conn;
    $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = $notification_id";
    return $conn->query($sql);
}
?>