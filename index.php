<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, redirect ke halaman sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'superadmin') {
        header("Location: superadmin/index.php");
    } else {
        header("Location: admin/index.php");
    }
    exit();
} else {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
}
?>