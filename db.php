<?php
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname   = "online-libraty";
$charset = "utf8mb4";

try {
    // Connect to MySQL server first without selecting a database
    $pdo = new PDO("mysql:host=$host;charset=$charset", $dbusername, $dbpassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Select the database
    $pdo->exec("USE `$dbname`");
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Establish MySQLi Connection (kept for backward compatibility)
$conn = new mysqli($host, $dbusername, $dbpassword);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);
?>
