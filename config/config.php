<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define("SECURE_ACCESS", true);
define('INTERNAL_CALL', true); // Security flag for cron jobs
require_once __DIR__ . "/../function/function.php";
ProtectFile(__FILE__);

// ─── Resend REST API (Production Email) ───────────────────────────────────────
define('RESEND_API_KEY',    getenv('RESEND_API_KEY')    ?: '');
define('RESEND_FROM_EMAIL', getenv('RESEND_FROM_EMAIL') ?: 'BookBuddy <onboarding@resend.dev>');

// ─── SMTP Fallback (Local Dev only — not used in production) ──────────────────
define('SMTP_HOST',     getenv('SMTP_HOST')     ?: 'smtp.gmail.com');
define('SMTP_USER',     getenv('SMTP_USER')     ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_PORT',     (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_SECURE',   getenv('SMTP_SECURE')   ?: 'tls');

// ─── Database Configuration (Railway MySQL / Docker / Local Fallback) ─────────
$host       = getenv('MYSQLHOST')     ?: (getenv('DB_HOST') ?: 'localhost');
$dataBase   = getenv('MYSQLDATABASE') ?: (getenv('DB_NAME') ?: 'BookBuddy');
$db_user    = getenv('MYSQLUSER')     ?: (getenv('DB_USER') ?: 'root');
$db_password = getenv('MYSQLPASSWORD') !== false
    ? getenv('MYSQLPASSWORD')
    : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$port       = getenv('MYSQLPORT')     ?: (getenv('DB_PORT') ?: '3306');
$charset    = 'utf8mb4';

// ─── Railway DATABASE_URL / MYSQL_URL override ────────────────────────────────
if ($dbUrl = (getenv('MYSQL_URL') ?: getenv('DATABASE_URL'))) {
    $parsed = parse_url($dbUrl);
    if (!empty($parsed['host']))  $host        = $parsed['host'];
    if (!empty($parsed['port']))  $port         = $parsed['port'];
    if (!empty($parsed['user']))  $db_user      = urldecode($parsed['user']);
    if (isset($parsed['pass']))   $db_password  = urldecode($parsed['pass']);
    if (!empty($parsed['path']))  $dataBase     = ltrim(urldecode($parsed['path']), '/');
}

// ─── PDO Connection ───────────────────────────────────────────────────────────
$dataSource = "mysql:host=$host;port=$port;dbname=$dataBase;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $connection = new PDO($dataSource, $db_user, $db_password, $options);
} catch (PDOException $e) {
    die('Connection failed: ' . htmlspecialchars($e->getMessage()));
}

// ─── Randomly expire deals (1 in 20 chance per request) ──────────────────────
if (mt_rand(1, 20) === 1) {
    require_once __DIR__ . '/../handlers/expireDeals.php';
}