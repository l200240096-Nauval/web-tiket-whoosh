<?php
session_start();

$host = 'sql105.infinityfree.com';
$db   = 'if0_41984886_tiket_kereta_db';
$user = 'if0_41984886';
$pass = 'prakpemweb999';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka)
{
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}

function is_login()
{
    return isset($_SESSION['user']);
}

function require_login()
{
    if (!is_login()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin()
{
    require_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

function flash($key, $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    return null;
}

function save_booking_history($kode)
{
    if (!isset($_SESSION['booking_history'])) {
        $_SESSION['booking_history'] = [];
    }

    array_unshift($_SESSION['booking_history'], $kode);
    $_SESSION['booking_history'] = array_values(array_unique($_SESSION['booking_history']));
}
