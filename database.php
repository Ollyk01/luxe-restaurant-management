<?php
// database.php - FreeDB MySQL

$host = "sql.freedb.tech";
$user = "u_QukbVS";                 
$password = "XA1JMvxcHFQO";         
$database = "freedb_T9v6UuwB";      

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Function to get connection
function getConnection() {
    global $conn;
    return $conn;
}
?>