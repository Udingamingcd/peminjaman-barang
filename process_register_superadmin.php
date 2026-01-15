<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/superadmin_check.php';

// Jika sudah ada superadmin, redirect ke login
if ($superadmin_exists) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    $errors = [];
    
    // Cek apakah username sudah ada
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Username sudah digunakan!";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter!";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak sama!";
    }
    
    // Jika ada error
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: register_superadmin.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert superadmin ke database
    $query = "INSERT INTO users (username, password, nama_lengkap, role) 
              VALUES ('$username', '$hashed_password', '$nama_lengkap', 'superadmin')";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Superadmin berhasil didaftarkan! Silakan login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Terjadi kesalahan: " . mysqli_error($koneksi);
        header("Location: register_superadmin.php");
        exit();
    }
} else {
    header("Location: register_superadmin.php");
    exit();
}
?>