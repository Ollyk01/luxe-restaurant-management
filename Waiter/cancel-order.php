<?php
// Waiter/cancel-order.php
require_once '../includes/auth.php';
checkRole(['Waiter']);

// Check if order_id is provided
if (!isset($_POST['order_id'])) {
    header('Location: active-orders.php?error=No order specified');
    exit();
}

$order_id = intval($_POST['order_id']);
$waiter_id = $_SESSION['user_id'];

// Verify this order belongs to this waiter
$check_sql = "SELECT order_id, table_id FROM orders WHERE order_id = $order_id AND waiter_id = $waiter_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows == 0) {
    header('Location: active-orders.php?error=Order not found or does not belong to you');
    exit();
}

$order = $check_result->fetch_assoc();
$table_id = $order['table_id'];

// Update order status to Cancelled
$sql = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = $order_id";

if ($conn->query($sql)) {
    // Update table status back to available
    $conn->query("UPDATE restaurant_tables SET status = 'available' WHERE table_id = $table_id");
    
    // Optional: Insert a notification for the kitchen (if you want)
    // $conn->query("INSERT INTO notifications (order_id, message, is_read) 
    //                VALUES ($order_id, 'Order has been cancelled by waiter', 0)");
    
    header('Location: active-orders.php?success=Order cancelled successfully');
} else {
    header('Location: active-orders.php?error=Could not cancel order: ' . $conn->error);
}
exit();
?>