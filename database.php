<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "luxe_restaurant";

// error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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