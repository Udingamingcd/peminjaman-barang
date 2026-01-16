<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah user sudah login, jika tidak redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data user dari session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Tampilan berdasarkan role
$role_display = ($role == 'superadmin') ? 'Super Admin' : 'Admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-boxes"></i> Sistem Peminjaman Barang
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="barang.php">
                            <i class="fas fa-box"></i> Data Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="peminjaman.php">
                            <i class="fas fa-hand-holding"></i> Peminjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pengembalian.php">
                            <i class="fas fa-undo-alt"></i> Pengembalian
                        </a>
                    </li>
                    <?php if ($role == 'superadmin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Manajemen User
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= $nama_lengkap ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user"></i> Profil Saya
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <!-- Welcome Card -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- User Info -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-user-check"></i> Selamat Datang, <?= $nama_lengkap ?>!</h5>
                                    <p class="mb-0">
                                        Anda login sebagai <strong><?= $role_display ?></strong> 
                                        dengan username <strong><?= $username ?></strong>.
                                        <span class="float-end">
                                            <a href="logout.php" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </a>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Dashboard -->
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-info shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Total Barang</h5>
                                                <h2 class="mb-0">15</h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-box fa-3x"></i>
                                            </div>
                                        </div>
                                        <a href="barang.php" class="text-white stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-warning shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Dipinjam</h5>
                                                <h2 class="mb-0">3</h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-hand-holding fa-3x"></i>
                                            </div>
                                        </div>
                                        <a href="peminjaman.php" class="text-white stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-success shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Dikembalikan</h5>
                                                <h2 class="mb-0">12</h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-undo-alt fa-3x"></i>
                                            </div>
                                        </div>
                                        <a href="pengembalian.php" class="text-white stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <div class="card text-white bg-secondary shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Total User</h5>
                                                <h2 class="mb-0">5</h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-users fa-3x"></i>
                                            </div>
                                        </div>
                                        <?php if ($role == 'superadmin'): ?>
                                        <a href="users.php" class="text-white stretched-link"></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Logout Besar -->
                        <div class="row mt-5">
                            <div class="col-md-12 text-center">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-sign-out-alt"></i> Keluar dari Sistem
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Klik tombol di bawah untuk mengakhiri sesi Anda.
                                            Setelah logout, Anda harus login kembali untuk mengakses sistem.
                                        </p>
                                        <a href="logout.php" class="btn btn-danger btn-lg">
                                            <i class="fas fa-sign-out-alt"></i> Logout Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light border-top">
        <div class="container text-center">
            <span class="text-muted">
                &copy; <?= date('Y') ?> Sistem Peminjaman Barang. Login sebagai: <strong><?= $role_display ?></strong>
            </span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>