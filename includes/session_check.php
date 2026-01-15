<?php
session_start();

// Cek session login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Load koneksi database
require_once __DIR__ . '/../config/koneksi.php';
?>
