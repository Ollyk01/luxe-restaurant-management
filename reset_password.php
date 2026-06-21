<?php
// reset_password.php
require_once 'database.php';

$correct_hash = password_hash('Admin@123', PASSWORD_DEFAULT);

echo "New hash for 'Admin@123': " . $correct_hash . "<br><br>";

$sql = "UPDATE users SET password = '$correct_hash'";
if ($conn->query($sql)) {
    echo "All passwords updated to: Admin@123<br>";
    echo "New hash: " . $correct_hash . "<br><br>";
    echo "<strong>Try logging in now with:</strong><br>";
    echo "Username: AD001, WEP001, or KD001<br>";
    echo "Password: Admin@123";
} else {
    echo "❌ Error: " . $conn->error;
}
?>