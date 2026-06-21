<?php
// Waiter/submit-order.php
error_reporting(0); // Turn off error reporting to prevent HTML output
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once '../database.php';


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get the JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    $table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
    $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
    $special_notes = isset($_POST['special_instructions']) ? $_POST['special_instructions'] : '';
    $allergies = isset($_POST['allergies']) ? $_POST['allergies'] : '';
} else {
    $table_id = isset($data['table_id']) ? intval($data['table_id']) : 0;
    $items = isset($data['items']) ? $data['items'] : [];
    $special_notes = isset($data['special_instructions']) ? $data['special_instructions'] : '';
    $allergies = isset($data['allergies']) ? $data['allergies'] : '';
}

// Validate
if ($table_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a table']);
    exit();
}

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Please add items to the order']);
    exit();
}

// Calculate total
$total = 0;
foreach ($items as $item) {
    $total += floatval($item['price']);
}

// Generate order number - using simple method
$order_number = 'O' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

$waiter_id = $_SESSION['user_id'];
$order_status = 'Being Prepared';

$sql = "INSERT INTO orders (order_number, waiter_id, table_id, total_amount, order_status, special_notes) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siidss", $order_number, $waiter_id, $table_id, $total, $order_status, $special_notes);

if ($stmt->execute()) {
    $order_id = $conn->insert_id;
    
    // Insert each item
    foreach ($items as $item) {
        $item_name = $conn->real_escape_string($item['name']);
        $price = floatval($item['price']);
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $cooking_preference = isset($item['preference']) ? $conn->real_escape_string($item['preference']) : '';
        $subtotal = $price * $quantity;
        
        // Get menu_item_id from database
        $menu_sql = "SELECT item_id FROM menu_items WHERE item_name = '$item_name' LIMIT 1";
        $menu_result = $conn->query($menu_sql);
        $menu_item_id = 0;
        if ($menu_result && $menu_result->num_rows > 0) {
            $menu_row = $menu_result->fetch_assoc();
            $menu_item_id = $menu_row['item_id'];
        }
        
        $item_sql = "INSERT INTO order_items (order_id, menu_item_id, quantity, cooking_preference, special_notes, allergy_notes, subtotal) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $item_stmt = $conn->prepare($item_sql);
        $item_stmt->bind_param("iiisssd", $order_id, $menu_item_id, $quantity, $cooking_preference, $special_notes, $allergies, $subtotal);
        $item_stmt->execute();
    }
    
    // Update table status
    $conn->query("UPDATE restaurant_tables SET status = 'occupied' WHERE table_id = $table_id");
    
    echo json_encode([
        'success' => true,
        'message' => 'Order submitted successfully',
        'order_number' => $order_number,
        'order_id' => $order_id,
        'total' => number_format($total, 2)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>