<?php
session_start();

// Aiven Cloud Database Credentials
$host = 'mysql-24a6bece-yasirsaha191-0c8b.h.aivencloud.com';
$port = '21413'; // Cloud databases use specific ports, not the default 3306
$db   = 'defaultdb';
$user = 'avnadmin';
$pass = 'AVNS_1g7I3lrPrOHNPEtJd0W'; // Replace with the actual password from your Aiven dashboard
$charset = 'utf8mb4';

// The DSN must now include the custom port
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    
    // Aiven requires SSL for secure connections. 
    // Download the "CA certificate" from your dashboard and specify its path here:
    // PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem',
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // For debugging in development, you can temporarily echo the exact error message
     die("Database Connection Failed: " . $e->getMessage());
}

// Helper function for logged-in check
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
