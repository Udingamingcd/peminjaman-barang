<?php
// config/koneksi.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sistem_peminjaman_barang';

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    // Log error instead of displaying to user
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    
    // Show user-friendly error page
    if (!defined('API_REQUEST')) {
        header('Location: ../error/database.php');
        exit();
    } else {
        die(json_encode(['error' => 'Database connection failed']));
    }
}

mysqli_set_charset($koneksi, "utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Function to sanitize input
function clean_input($data) {
    global $koneksi;
    if ($data === null) {
        return '';
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = mysqli_real_escape_string($koneksi, $data);
    return $data;
}

// Function untuk mendapatkan nilai dengan default
function get_value($value, $default = '') {
    return $value !== null ? $value : $default;
}

// Function to log activity
function log_activity($admin_id, $action, $description) {
    global $koneksi;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $query = "INSERT INTO activity_log (admin_id, action, description, ip_address, user_agent) 
              VALUES ('$admin_id', '$action', '$description', '$ip_address', '$user_agent')";
    
    return mysqli_query($koneksi, $query);
}

// Function to check login status
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Function to check role
function check_role($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $required_role) {
        header("Location: ../unauthorized.php");
        exit();
    }
}

// Function to get setting value
function get_setting($key) {
    global $koneksi;
    
    $query = "SELECT setting_value FROM system_settings WHERE setting_key = '$key'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    
    return null;
}

// Function to update setting
function update_setting($key, $value) {
    global $koneksi;
    
    $query = "UPDATE system_settings SET setting_value = '$value', updated_at = NOW() WHERE setting_key = '$key'";
    return mysqli_query($koneksi, $query);
}

// Function to generate random code
function generate_code($prefix, $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $code;
}

// Function to format date
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Function to format currency
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Auto update last login
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $update_query = "UPDATE users SET last_login = NOW() WHERE id = '$user_id'";
    mysqli_query($koneksi, $update_query);
}
?>