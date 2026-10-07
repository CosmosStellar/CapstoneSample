<?php
// CapstoneSample_db.php - include this in every PHP page that needs the database
$host = 'localhost';
$db   = 'capstonesample_db';
$user = 'root';     // XAMPP/WAMP default
$pass = '';         // default is empty on XAMPP/WAMP

date_default_timezone_set('Asia/Manila');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+08:00'",  // so "today" matches PH time
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// short helper for safely printing text inside HTML
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }