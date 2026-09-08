<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define("SECURE_ACCESS", true);
define('INTERNAL_CALL', true); // Security flag for cron jobs
require_once __DIR__ . "/../function/function.php";
ProtectFile(__FILE__);

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'muhammadshaharyaraulakh@gmail.com');
define('SMTP_PASSWORD', 'rsthorskeuxvesvv');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

$host = "localhost";
$dataBase = "BookBuddy";
$db_user = "laraveluser";
$db_password = "1234";
$charset = "utf8mb4";

$dataSource = "mysql:host=$host;dbname=$dataBase;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES => FALSE
];
try {
    $connection = new PDO($dataSource, $db_user, $db_password, $options);
} catch (PDOException $e) {
    die("Connection failed" . htmlspecialchars($e->getMessage()));
}

// ─── POOR MAN'S CRON — SAFE VERSION ───────────────────────
if (mt_rand(1, 20) === 1) {
    require_once __DIR__ . '/../handlers/expireDeals.php';
}