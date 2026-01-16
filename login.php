<?php
session_start();
require_once 'config/koneksi.php'; // sekarang menggunakan $conn

// Cek apakah sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Cek apakah sudah ada superadmin di database
$check_superadmin = mysqli_query($koneksi, "SELECT id FROM users WHERE role = 'superadmin' LIMIT 1");
$superadmin_exists = mysqli_num_rows($check_superadmin) > 0;

// Proses login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cari user di database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            
            // Set Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            // Redirect ke dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">LOGIN ADMIN</h4>
                        <small>Sistem Peminjaman Barang</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       placeholder="Masukkan username">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required 
                                       placeholder="Masukkan password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <!-- Tombol Register Superadmin (hanya muncul jika belum ada superadmin) -->
                        <?php if (!$superadmin_exists): ?>
                            <hr>
                            <div class="text-center">
                                <p class="text-muted mb-2">Belum ada superadmin? Daftarkan sekarang!</p>
                                <a href="register_superadmin.php" class="btn btn-success w-100">
                                    <i class="fas fa-user-shield"></i> Daftar Superadmin Pertama
                                </a>
                                <small class="text-muted d-block mt-2">
                                    *Tombol ini hanya muncul sekali dan akan hilang setelah superadmin terdaftar
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informasi hak akses -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <strong>Role Akses:</strong><br>
                        • Superadmin: Akses penuh ke semua fitur<br>
                        • Admin: Akses terbatas (peminjaman dan pengembalian)
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>