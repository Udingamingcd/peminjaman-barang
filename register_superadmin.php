<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/superadmin_check.php';

// Jika sudah ada superadmin, redirect ke login
if ($superadmin_exists) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Superadmin - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-person-plus"></i> Pendaftaran Superadmin
                        </h4>
                        <small class="fst-italic">Hanya bisa dilakukan sekali</small>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="process_register_superadmin.php" method="POST" id="registerForm">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div class="form-text">Username akan digunakan untuk login</div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Minimal 8 karakter</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Perhatian!</strong> Akun superadmin hanya bisa dibuat sekali. Pastikan data yang dimasukkan benar.
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-person-check"></i> Daftarkan Superadmin
                                </button>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>