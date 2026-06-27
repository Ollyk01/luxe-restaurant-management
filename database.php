<?php

$host = "sql.freedb.tech";
$user = "u_QukbVS";
$password = "XA1JMvxcHFQO";
$database = "freedb_T9v6UuwB";


error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = new PDO("pgsql:host=$host;dbname=$database", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to PostgreSQL!";
} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}

// Function to get connection
function getConnection() {
    global $conn;
    return $conn;
}
?>