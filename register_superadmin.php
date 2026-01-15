<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah sudah ada superadmin
$check_superadmin = mysqli_query($koneksi, "SELECT id FROM users WHERE role = 'superadmin' LIMIT 1");
if (mysqli_num_rows($check_superadmin) > 0) {
    // Jika sudah ada superadmin, redirect ke login
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    
    // Validasi
    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek apakah username sudah ada
        $check_username = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert superadmin
            $query = "INSERT INTO users (username, password, nama_lengkap, role) 
                      VALUES ('$username', '$hashed_password', '$nama_lengkap', 'superadmin')";
            
            if (mysqli_query($koneksi, $query)) {
                $success = 'Superadmin berhasil didaftarkan! Anda akan dialihkan ke halaman login.';
                
                // Redirect setelah 3 detik
                header("refresh:3;url=login.php");
            } else {
                $error = 'Terjadi kesalahan: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Superadmin - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0">PENDAFTARAN SUPERADMIN</h4>
                        <small>Hanya bisa dilakukan sekali</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="registerForm">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                       required placeholder="Masukkan nama lengkap">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       placeholder="Buat username unik">
                                <small class="text-muted">Username ini akan digunakan untuk login</small>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required placeholder="Minimal 6 karakter">
                                <div class="mt-2">
                                    <small class="text-muted">Kekuatan password: <span id="passwordStrength">-</span></small>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="passwordStrengthBar" role="progressbar"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required placeholder="Ketik ulang password">
                                <small class="text-muted" id="passwordMatch"></small>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Peringatan:</strong> Superadmin memiliki akses penuh ke sistem. 
                                Simpan baik-baik username dan password Anda. Tombol ini hanya muncul sekali!
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-user-shield"></i> Daftarkan Superadmin
                                </button>
                                <a href="login.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
</body>
</html>