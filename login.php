<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/superadmin_check.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-box-seam"></i> Sistem Peminjaman Barang
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-center mb-4">Login Admin/Superadmin</h5>
                        
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $_SESSION['success']; ?>
                                <?php unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="process_login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <!-- Tombol Register Superadmin (hanya muncul jika belum ada superadmin) -->
                        <?php if(!$superadmin_exists): ?>
                            <hr>
                            <div class="text-center">
                                <p class="text-muted">Belum ada superadmin? Daftar terlebih dahulu:</p>
                                <a href="register_superadmin.php" class="btn btn-success">
                                    <i class="bi bi-person-plus"></i> Daftar Superadmin
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>&copy; <?= date('Y'); ?> Sistem Peminjaman Barang</small>
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