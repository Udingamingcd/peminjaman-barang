<?php
session_start();
require_once 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    // Cek user di database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Redirect ke dashboard berdasarkan role
            if ($user['role'] == 'superadmin') {
                header("Location: dashboard_superadmin.php");
            } else {
                header("Location: dashboard_admin.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Password salah!";
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";
    }
    
    // Jika login gagal, kembali ke login
    header("Location: login.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>