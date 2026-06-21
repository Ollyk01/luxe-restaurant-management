<?php
// submit-reservation.php
session_start();
require_once 'database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

// Get and sanitize input
$first_name = isset($_POST['first_name']) ? mysqli_real_escape_string($conn, $_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? mysqli_real_escape_string($conn, $_POST['last_name']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$reservation_date = isset($_POST['reservation_date']) ? mysqli_real_escape_string($conn, $_POST['reservation_date']) : '';
$reservation_time = isset($_POST['reservation_time']) ? mysqli_real_escape_string($conn, $_POST['reservation_time']) : '';
$guests = isset($_POST['number_of_guests']) ? intval($_POST['number_of_guests']) : 0;
$occasion = isset($_POST['occasion']) ? mysqli_real_escape_string($conn, $_POST['occasion']) : '';
$special_requests = isset($_POST['special_requests']) ? mysqli_real_escape_string($conn, $_POST['special_requests']) : '';

// Combine date and time into datetime format
$reservation_datetime = $reservation_date . ' ' . $reservation_time . ':00';

// Validate
if (empty($first_name) || empty($last_name) || empty($email) || empty($reservation_date) || empty($reservation_time) || $guests < 1) {
    header('Location: booking.html?error=Please fill in all required fields');
    exit();
}

// Insert into your table (matches your exact table structure)
$sql = "INSERT INTO reservations (first_name, last_name, email, reservation_date, guests, occasion, special_requests, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    // If prepared statement fails, try direct query
    $sql_direct = "INSERT INTO reservations (first_name, last_name, email, reservation_date, guests, occasion, special_requests, status) 
                   VALUES ('$first_name', '$last_name', '$email', '$reservation_datetime', $guests, '$occasion', '$special_requests', 'pending')";
    
    if ($conn->query($sql_direct)) {
        header('Location: booking.html?success=1');
        exit();
    } else {
        header('Location: booking.html?error=' . urlencode($conn->error));
        exit();
    }
}

$stmt->bind_param("ssssiss", $first_name, $last_name, $email, $reservation_datetime, $guests, $occasion, $special_requests);

if ($stmt->execute()) {
    header('Location: booking.html?success=1');
    exit();
} else {
    header('Location: booking.html?error=' . urlencode($stmt->error));
    exit();
}

$stmt->close();
$conn->close();
?>