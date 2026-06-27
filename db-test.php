<?php
// db-test.php - Test database connection

echo "<h2>🔍 Database Connection Test</h2>";

$host = "sql.freedb.tech";
$user = "u_QukbVS";
$password = "XA1JMvxcHFQO";
$database = "freedb_T9v6UuwB";

echo "Host: " . $host . "<br>";
echo "User: " . $user . "<br>";
echo "Database: " . $database . "<br><br>";

// Try to connect
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("❌ Connection Failed: " . $conn->connect_error);
}

echo "✅ Connected successfully to MySQL!<br><br>";

// Show tables
$result = $conn->query("SHOW TABLES");
echo "Tables in database:<br>";
while ($row = $result->fetch_assoc()) {
    echo "- " . reset($row) . "<br>";
}

$conn->close();
?>