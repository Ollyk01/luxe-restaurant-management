<?php
// Admin/decline-reservation.php
require_once '../includes/auth.php';
checkRole(['Administrator']);

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?error=No reservation ID provided');
    exit();
}

$reservation_id = intval($_GET['id']);

$sql = "UPDATE reservations SET status = 'Declined' WHERE reservation_id = $reservation_id";

if ($conn->query($sql)) {
    header('Location: dashboard.php?success=Reservation declined');
} else {
    header('Location: dashboard.php?error=Could not decline reservation');
}
exit();
?>