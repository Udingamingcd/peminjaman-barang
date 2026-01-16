<?php
// admin/index.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Ambil data statistik
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM mahasiswa) as total_mahasiswa,
    (SELECT COUNT(*) FROM barang WHERE status = 'tersedia') as barang_tersedia,
    (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam') as sedang_dipinjam,
    (SELECT COUNT(*) FROM peminjaman WHERE status = 'dikembalikan' AND DATE(created_at) = CURDATE()) as dikembalikan_hari_ini";
$result_stats = mysqli_query($koneksi, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil data peminjaman terbaru
$query_peminjaman = "SELECT p.*, m.nama as nama_mahasiswa, b.nama_barang 
                     FROM peminjaman p
                     JOIN mahasiswa m ON p.mahasiswa_id = m.id
                     JOIN barang b ON p.barang_id = b.id
                     ORDER BY p.created_at DESC LIMIT 5";
$result_peminjaman = mysqli_query($koneksi, $query_peminjaman);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <i class="fas fa-boxes logo-icon"></i>
                <h3 class="logo-text">SIPEMBAR</h3>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h6 class="mb-0"><?php echo htmlspecialchars($nama_lengkap); ?></h6>
                    <small class="text-muted"><?php echo ucfirst($role); ?></small>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item active">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="mahasiswa.php">
                        <i class="fas fa-users"></i>
                        <span>Data Mahasiswa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="barang.php">
                        <i class="fas fa-box"></i>
                        <span>Data Barang</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="peminjaman.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Peminjaman</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pengembalian.php">
                        <i class="fas fa-undo"></i>
                        <span>Pengembalian</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="laporan.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="change_password.php">
                        <i class="fas fa-key"></i>
                        <span>Ganti Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <small class="text-muted">SIPEMBAR v2.1.0</small>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="topbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </h4>
                        <small class="text-muted">Selamat datang kembali, <?php echo htmlspecialchars($nama_lengkap); ?>!</small>
                    </div>
                    <div class="topbar-right">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key me-2"></i>Ganti Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_mahasiswa']; ?></h3>
                                <p>Total Mahasiswa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['barang_tersedia']; ?></h3>
                                <p>Barang Tersedia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['sedang_dipinjam']; ?></h3>
                                <p>Sedang Dipinjam</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['dikembalikan_hari_ini']; ?></h3>
                                <p>Dikembalikan Hari Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="peminjaman.php?action=new" class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-2"></i>Tambah Peminjaman
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="pengembalian.php" class="btn btn-success w-100">
                                            <i class="fas fa-check me-2"></i>Proses Pengembalian
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="barang.php?action=new" class="btn btn-info w-100">
                                            <i class="fas fa-box me-2"></i>Tambah Barang
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="laporan.php" class="btn btn-warning w-100">
                                            <i class="fas fa-print me-2"></i>Cetak Laporan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Peminjaman Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Mahasiswa</th>
                                                <th>Barang</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = mysqli_fetch_assoc($result_peminjaman)): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?php echo $row['kode_peminjaman']; ?></span></td>
                                                <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $status_class = '';
                                                    if($row['status'] == 'dipinjam') $status_class = 'warning';
                                                    if($row['status'] == 'dikembalikan') $status_class = 'success';
                                                    if($row['status'] == 'hilang') $status_class = 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Hari Ini</h5>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="display-4"><?php echo date('d'); ?></h2>
                                <h5><?php echo date('F Y'); ?></h5>
                                <p class="text-muted"><?php echo date('l'); ?></p>
                                <hr>
                                <div class="text-start">
                                    <p><i class="fas fa-check-circle text-success me-2"></i> <?php echo $stats['dikembalikan_hari_ini']; ?> barang dikembalikan</p>
                                    <p><i class="fas fa-clock text-warning me-2"></i> <?php echo $stats['sedang_dipinjam']; ?> barang masih dipinjam</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <small class="text-muted">
                            SIPEMBAR &copy; <?php echo date('Y'); ?> - Sistem Peminjaman Barang Universitas Teknologi Informasi
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../js/admin.js"></script>
</body>
</html>