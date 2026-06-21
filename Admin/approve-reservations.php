<?php
// Admin/approve-reservation.php
require_once '../includes/auth.php';
checkRole(['Administrator']);

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?error=No reservation ID provided');
    exit();
}

$reservation_id = intval($_GET['id']);

// Update reservation status to Confirmed
$sql = "UPDATE reservations SET status = 'Confirmed' WHERE reservation_id = $reservation_id";

if ($conn->query($sql)) {
    header('Location: dashboard.php?success=Reservation confirmed successfully');
} else {
    header('Location: dashboard.php?error=Could not confirm reservation: ' . $conn->error);
}
exit();
?>