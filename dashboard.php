<?php
session_start();
require_once 'config/koneksi.php';

// Redirect berdasarkan role
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] == 'superadmin') {
    header("Location: dashboard_superadmin.php");
} else {
    header("Location: dashboard_admin.php");
}
exit();
?>