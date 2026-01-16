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

// Query untuk mendapatkan statistik dengan prepared statements
// 1. Total Barang
$query_barang = "SELECT COUNT(*) as total FROM barang";
$result_barang = $koneksi->query($query_barang);
$total_barang = $result_barang->fetch_assoc()['total'];

// 2. Barang yang sedang dipinjam
$query_dipinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'";
$result_dipinjam = $koneksi->query($query_dipinjam);
$total_dipinjam = $result_dipinjam->fetch_assoc()['total'];

// 3. Barang yang sudah dikembalikan
$query_dikembalikan = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan'";
$result_dikembalikan = $koneksi->query($query_dikembalikan);
$total_dikembalikan = $result_dikembalikan->fetch_assoc()['total'];

// 4. Total User
$query_user = "SELECT COUNT(*) as total FROM users";
$result_user = $koneksi->query($query_user);
$total_user = $result_user->fetch_assoc()['total'];

// 5. Total Mahasiswa
$query_mahasiswa = "SELECT COUNT(*) as total FROM mahasiswa";
$result_mahasiswa = $koneksi->query($query_mahasiswa);
$total_mahasiswa = $result_mahasiswa->fetch_assoc()['total'];

// 6. Barang dengan status tersedia
$query_barang_tersedia = "SELECT COUNT(*) as total FROM barang WHERE status = 'tersedia'";
$result_barang_tersedia = $koneksi->query($query_barang_tersedia);
$total_barang_tersedia = $result_barang_tersedia->fetch_assoc()['total'];

// 7. Barang dengan status hilang
$query_barang_hilang = "SELECT COUNT(*) as total FROM barang WHERE status = 'hilang'";
$result_barang_hilang = $koneksi->query($query_barang_hilang);
$total_barang_hilang = $result_barang_hilang->fetch_assoc()['total'];

// 8. Barang dengan status sedang dipinjam
$query_barang_sedang_dipinjam = "SELECT COUNT(*) as total FROM barang WHERE status = 'sedang_dipinjam'";
$result_barang_sedang_dipinjam = $koneksi->query($query_barang_sedang_dipinjam);
$total_barang_sedang_dipinjam = $result_barang_sedang_dipinjam->fetch_assoc()['total'];

// 9. Data peminjaman terbaru (5 data)
$query_peminjaman_terbaru = "
    SELECT p.*, m.nama as nama_mahasiswa, b.nama_barang, u.nama_lengkap as admin 
    FROM peminjaman p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN barang b ON p.barang_id = b.id
    JOIN users u ON p.admin_id = u.id
    ORDER BY p.created_at DESC 
    LIMIT 5
";
$result_peminjaman_terbaru = $koneksi->query($query_peminjaman_terbaru);

// 10. Statistik berdasarkan kategori barang
$query_kategori = "
    SELECT kategori, COUNT(*) as jumlah 
    FROM barang 
    WHERE kategori IS NOT NULL AND kategori != '' 
    GROUP BY kategori 
    ORDER BY jumlah DESC 
    LIMIT 5
";
$result_kategori = $koneksi->query($query_kategori);

// Prepare data for chart
$kategori_labels = [];
$kategori_data = [];
$chart_colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
while ($row = $result_kategori->fetch_assoc()) {
    $kategori_labels[] = htmlspecialchars($row['kategori']);
    $kategori_data[] = $row['jumlah'];
}

// 11. Data peminjaman bulan ini
$current_month = date('m');
$current_year = date('Y');
$query_peminjaman_bulan_ini = "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE MONTH(tanggal_pinjam) = ? AND YEAR(tanggal_pinjam) = ?
";
$stmt = $koneksi->prepare($query_peminjaman_bulan_ini);
$stmt->bind_param("ii", $current_month, $current_year);
$stmt->execute();
$result_peminjaman_bulan_ini = $stmt->get_result();
$total_peminjaman_bulan_ini = $result_peminjaman_bulan_ini->fetch_assoc()['total'];
$stmt->close();

// 12. Data mahasiswa berdasarkan angkatan
$query_angkatan = "
    SELECT angkatan, COUNT(*) as jumlah 
    FROM mahasiswa 
    GROUP BY angkatan 
    ORDER BY angkatan DESC 
    LIMIT 5
";
$result_angkatan = $koneksi->query($query_angkatan);

// 13. Data aktivitas terbaru
$query_aktivitas_terbaru = "
    SELECT 
        'peminjaman' as tipe,
        p.kode_peminjaman as kode,
        CONCAT('Peminjaman oleh ', m.nama) as deskripsi,
        p.created_at,
        u.nama_lengkap as admin
    FROM peminjaman p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON p.admin_id = u.id
    UNION ALL
    SELECT 
        'pengembalian' as tipe,
        p.kode_peminjaman as kode,
        CONCAT('Pengembalian oleh ', m.nama) as deskripsi,
        p.updated_at as created_at,
        u.nama_lengkap as admin
    FROM peminjaman p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON p.admin_id = u.id
    WHERE p.status = 'dikembalikan'
    ORDER BY created_at DESC 
    LIMIT 10
";
$result_aktivitas_terbaru = $koneksi->query($query_aktivitas_terbaru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Peminjaman Barang</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-boxes me-2"></i>Sistem Peminjaman
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="barang.php">
                            <i class="fas fa-box me-1"></i> Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="peminjaman.php">
                            <i class="fas fa-hand-holding me-1"></i> Peminjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pengembalian.php">
                            <i class="fas fa-undo-alt me-1"></i> Pengembalian
                        </a>
                    </li>
                    <?php if ($role == 'superadmin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users-cog me-1"></i> Manajemen User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan.php">
                            <i class="fas fa-chart-bar me-1"></i> Laporan
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Notification Bell -->
                <div class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if ($total_dipinjam > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $total_dipinjam ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationDropdown">
                        <div class="dropdown-header">
                            <i class="fas fa-bell me-2"></i> Notifikasi
                        </div>
                        <div class="dropdown-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if ($total_dipinjam > 0): ?>
                            <a href="peminjaman.php" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-warning me-3">
                                        <i class="fas fa-exclamation text-white"></i>
                                    </div>
                                    <div>
                                        <div class="small"><?= $total_dipinjam ?> barang sedang dipinjam</div>
                                        <div class="text-muted">Perlu pemantauan</div>
                                    </div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if ($total_barang_hilang > 0): ?>
                            <a href="barang.php?status=hilang" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-danger me-3">
                                        <i class="fas fa-exclamation-triangle text-white"></i>
                                    </div>
                                    <div>
                                        <div class="small"><?= $total_barang_hilang ?> barang hilang</div>
                                        <div class="text-muted">Perlu tindakan</div>
                                    </div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <a href="peminjaman.php" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-info me-3">
                                        <i class="fas fa-info-circle text-white"></i>
                                    </div>
                                    <div>
                                        <div class="small"><?= $total_peminjaman_bulan_ini ?> peminjaman bulan ini</div>
                                        <div class="text-muted"><?= date('F Y') ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="dropdown-footer text-center">
                            <a href="notifikasi.php" class="text-primary">Lihat semua notifikasi</a>
                        </div>
                    </div>
                </div>
                
                <!-- User Dropdown -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="avatar-sm me-2">
                                <div class="avatar-title bg-light text-primary rounded-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="d-none d-md-block">
                                <div class="fw-semibold"><?= htmlspecialchars($nama_lengkap) ?></div>
                                <small class="text-light"><?= $role_display ?></small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-circle me-2"></i> Profil Saya
                            </a></li>
                            <li><a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i> Pengaturan
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5 pt-4">
        <div class="row">
            <!-- Welcome Card -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm welcome-card" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="fw-bold text-primary mb-1">
                                    <i class="fas fa-hand-wave me-2"></i>Selamat Datang, <?= htmlspecialchars($nama_lengkap) ?>!
                                </h4>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-user-tag me-1"></i> Anda login sebagai <span class="badge bg-primary"><?= $role_display ?></span> 
                                    <i class="fas fa-user me-1 ms-3"></i> Username: <strong><?= htmlspecialchars($username) ?></strong>
                                    <i class="fas fa-clock me-1 ms-3"></i> Terakhir login: <?= date('d/m/Y H:i') ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary me-2" onclick="refreshDashboard()" data-bs-toggle="tooltip" title="Refresh Dashboard">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                    <button class="btn btn-outline-secondary me-2" onclick="Dashboard.exportPDF()" data-bs-toggle="tooltip" title="Export PDF">
                                        <i class="fas fa-file-pdf me-1"></i> PDF
                                    </button>
                                    <a href="logout.php" class="btn btn-danger" data-bs-toggle="tooltip" title="Keluar dari sistem">
                                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Utama -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-secondary">
                        <i class="fas fa-chart-line me-2"></i>Statistik Sistem
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleViewMode()" data-bs-toggle="tooltip" title="Ubah Tampilan">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="Dashboard.showStatsModal()" data-bs-toggle="tooltip" title="Detail Statistik">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistik Row 1 -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-primary border-4 shadow-sm h-100" data-aos="fade-up">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-box text-primary me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Total Barang</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-primary" id="total-barang"><?= $total_barang ?></h2>
                                <small class="text-muted"><i class="fas fa-check-circle text-success me-1"></i><?= $total_barang_tersedia ?> tersedia</small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-primary" data-bs-toggle="tooltip" title="Barang Tersedia">
                                    <i class="fas fa-boxes text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 6px;">
                                <?php $percent = $total_barang > 0 ? ($total_barang_tersedia / $total_barang * 100) : 0; ?>
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%" 
                                     data-bs-toggle="tooltip" title="<?= round($percent) ?>% Tersedia"></div>
                            </div>
                        </div>
                    </div>
                    <a href="barang.php" class="stretched-link" data-bs-toggle="tooltip" title="Klik untuk lihat detail barang"></a>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-warning border-4 shadow-sm h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-hand-holding text-warning me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Sedang Dipinjam</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-warning" id="total-dipinjam"><?= $total_dipinjam ?></h2>
                                <small class="text-muted"><i class="fas fa-clock text-warning me-1"></i><?= $total_barang_sedang_dipinjam ?> barang</small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-warning" data-bs-toggle="tooltip" title="Barang Dipinjam">
                                    <i class="fas fa-hand-holding-heart text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 6px;">
                                <?php $percent = $total_barang > 0 ? ($total_barang_sedang_dipinjam / $total_barang * 100) : 0; ?>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $percent ?>%" 
                                     data-bs-toggle="tooltip" title="<?= round($percent) ?>% Dipinjam"></div>
                            </div>
                        </div>
                    </div>
                    <a href="peminjaman.php" class="stretched-link" data-bs-toggle="tooltip" title="Klik untuk lihat peminjaman"></a>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-success border-4 shadow-sm h-100" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Telah Dikembalikan</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-success" id="total-dikembalikan"><?= $total_dikembalikan ?></h2>
                                <small class="text-muted"><i class="fas fa-calendar-alt text-success me-1"></i><?= $total_peminjaman_bulan_ini ?> bulan ini</small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-success" data-bs-toggle="tooltip" title="Barang Dikembalikan">
                                    <i class="fas fa-check-double text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 6px;">
                                <?php $total_pinjam = $total_dipinjam + $total_dikembalikan; ?>
                                <?php $percent = $total_pinjam > 0 ? ($total_dikembalikan / $total_pinjam * 100) : 0; ?>
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%" 
                                     data-bs-toggle="tooltip" title="<?= round($percent) ?>% Dikembalikan"></div>
                            </div>
                        </div>
                    </div>
                    <a href="pengembalian.php" class="stretched-link" data-bs-toggle="tooltip" title="Klik untuk lihat pengembalian"></a>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-danger border-4 shadow-sm h-100" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Barang Hilang</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-danger" id="total-barang-hilang"><?= $total_barang_hilang ?></h2>
                                <small class="text-muted"><i class="fas fa-exclamation text-danger me-1"></i>Perlu tindakan</small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-danger" data-bs-toggle="tooltip" title="Barang Hilang">
                                    <i class="fas fa-search-minus text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 6px;">
                                <?php $percent = $total_barang > 0 ? ($total_barang_hilang / $total_barang * 100) : 0; ?>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $percent ?>%" 
                                     data-bs-toggle="tooltip" title="<?= round($percent) ?>% Hilang"></div>
                            </div>
                        </div>
                    </div>
                    <a href="barang.php?status=hilang" class="stretched-link" data-bs-toggle="tooltip" title="Klik untuk lihat barang hilang"></a>
                </div>
            </div>
        </div>

        <!-- Statistik Row 2 -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-info border-4 shadow-sm h-100" data-aos="fade-up">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-graduate text-info me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Total Mahasiswa</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-info" id="total-mahasiswa"><?= $total_mahasiswa ?></h2>
                                <small class="text-muted"><i class="fas fa-id-card text-info me-1"></i>Peminjam terdaftar</small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-info" data-bs-toggle="tooltip" title="Mahasiswa Terdaftar">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="mahasiswa.php" class="stretched-link" data-bs-toggle="tooltip" title="Klik untuk lihat data mahasiswa"></a>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-secondary border-4 shadow-sm h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-users-cog text-secondary me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Total User</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-secondary" id="total-user"><?= $total_user ?></h2>
                                <small class="text-muted"><i class="fas fa-user-shield text-secondary me-1"></i>Admin & Super Admin</small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-secondary" data-bs-toggle="tooltip" title="User Sistem">
                                    <i class="fas fa-user-tie text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($role == 'superadmin'): ?>
                    <a href="users.php" class="stretched-link" data-bs-toggle="tooltip" title="Klik untuk kelola user"></a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-purple border-4 shadow-sm h-100" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-chart-pie text-purple me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Distribusi Status</h6>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="fas fa-circle text-success me-1"></i>Tersedia</span>
                                    <span><?= $total_barang_tersedia ?></span>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="fas fa-circle text-warning me-1"></i>Dipinjam</span>
                                    <span><?= $total_barang_sedang_dipinjam ?></span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span><i class="fas fa-circle text-danger me-1"></i>Hilang</span>
                                    <span><?= $total_barang_hilang ?></span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-purple" data-bs-toggle="tooltip" title="Distribusi Status">
                                    <i class="fas fa-chart-bar text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-start border-teal border-4 shadow-sm h-100" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar-alt text-teal me-2"></i>
                                    <h6 class="text-uppercase text-muted mb-0">Bulan Ini</h6>
                                </div>
                                <h2 class="mb-0 fw-bold text-teal"><?= $total_peminjaman_bulan_ini ?></h2>
                                <small class="text-muted"><i class="fas fa-chart-line text-teal me-1"></i>Peminjaman <?= date('F Y') ?></small>
                            </div>
                            <div class="col-auto">
                                <div class="icon-circle bg-teal" data-bs-toggle="tooltip" title="Peminjaman Bulan Ini">
                                    <i class="fas fa-calendar-check text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between small">
                                <span>Target: 50</span>
                                <span><?= round(($total_peminjaman_bulan_ini / 50) * 100) ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <?php $percent = ($total_peminjaman_bulan_ini / 50) * 100; ?>
                                <div class="progress-bar bg-teal" role="progressbar" style="width: <?= min($percent, 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <!-- Chart Distribusi Kategori -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100" data-aos="fade-right">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-chart-pie me-2"></i>Distribusi Kategori Barang
                        </h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeChartType('doughnut')" data-bs-toggle="tooltip" title="Donut Chart">
                                <i class="fas fa-donate"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="changeChartType('pie')" data-bs-toggle="tooltip" title="Pie Chart">
                                <i class="fas fa-chart-pie"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="changeChartType('bar')" data-bs-toggle="tooltip" title="Bar Chart">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body position-relative">
                        <canvas id="categoryChart" height="250"></canvas>
                        <div class="chart-overlay" id="chartLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Status Peminjaman -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100" data-aos="fade-left">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-chart-bar me-2"></i>Status Peminjaman
                        </h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadChart()" data-bs-toggle="tooltip" title="Download Chart">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peminjaman Terbaru & Aktivitas -->
        <div class="row mb-4">
            <!-- Peminjaman Terbaru -->
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm h-100" data-aos="fade-up">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-history me-2"></i>Peminjaman Terbaru
                        </h6>
                        <div>
                            <a href="tambah_peminjaman.php" class="btn btn-sm btn-success me-2" data-bs-toggle="tooltip" title="Tambah Peminjaman Baru">
                                <i class="fas fa-plus me-1"></i> Baru
                            </a>
                            <a href="peminjaman.php" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Semua Peminjaman">
                                Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($result_peminjaman_terbaru->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-top-0">
                                                <i class="fas fa-hashtag me-1"></i>Kode
                                            </th>
                                            <th class="border-top-0">
                                                <i class="fas fa-user-graduate me-1"></i>Mahasiswa
                                            </th>
                                            <th class="border-top-0">
                                                <i class="fas fa-box me-1"></i>Barang
                                            </th>
                                            <th class="border-top-0">
                                                <i class="fas fa-calendar me-1"></i>Tanggal
                                            </th>
                                            <th class="border-top-0">
                                                <i class="fas fa-info-circle me-1"></i>Status
                                            </th>
                                            <th class="border-top-0">
                                                <i class="fas fa-user-cog me-1"></i>Admin
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result_peminjaman_terbaru->fetch_assoc()): ?>
                                        <tr class="clickable-row" onclick="showPeminjamanDetail(<?= $row['id'] ?>)" style="cursor: pointer;">
                                            <td>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-hashtag me-1"></i><?= htmlspecialchars($row['kode_peminjaman']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-user me-1 text-muted"></i>
                                                <?= htmlspecialchars($row['nama_mahasiswa']) ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-box me-1 text-muted"></i>
                                                <?= htmlspecialchars($row['nama_barang']) ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                <?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $badge_class = 'bg-secondary';
                                                $icon = 'fa-question';
                                                if ($row['status'] == 'dipinjam') {
                                                    $badge_class = 'bg-warning';
                                                    $icon = 'fa-hand-holding';
                                                }
                                                if ($row['status'] == 'dikembalikan') {
                                                    $badge_class = 'bg-success';
                                                    $icon = 'fa-check';
                                                }
                                                if ($row['status'] == 'hilang') {
                                                    $badge_class = 'bg-danger';
                                                    $icon = 'fa-exclamation-triangle';
                                                }
                                                ?>
                                                <span class="badge <?= $badge_class ?>">
                                                    <i class="fas <?= $icon ?> me-1"></i><?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-user-cog me-1 text-muted"></i>
                                                <?= htmlspecialchars($row['admin']) ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Belum ada data peminjaman</p>
                                <a href="tambah_peminjaman.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-1"></i> Tambah Peminjaman Pertama
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Aktivitas Terbaru -->
            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-bell me-2"></i>Aktivitas Terbaru
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="activity-feed">
                            <?php if ($result_aktivitas_terbaru->num_rows > 0): ?>
                                <?php while ($row = $result_aktivitas_terbaru->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php if ($row['tipe'] == 'peminjaman'): ?>
                                            <i class="fas fa-hand-holding bg-primary"></i>
                                        <?php else: ?>
                                            <i class="fas fa-undo-alt bg-success"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?= $row['deskripsi'] ?></div>
                                        <div class="activity-info">
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?= date('H:i', strtotime($row['created_at'])) ?> | 
                                                <?= htmlspecialchars($row['admin']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-bell-slash fa-2x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Belum ada aktivitas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-bolt me-2"></i>Akses Cepat
                        </h6>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleQuickActions()" data-bs-toggle="tooltip" title="Sembunyikan/Tampilkan">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="card-body" id="quickActions">
                        <div class="row g-3">
                            <div class="col-sm-6 col-md-4 col-lg-2">
                                <a href="tambah_barang.php" class="btn btn-outline-primary w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center quick-action-btn" data-bs-toggle="tooltip" title="Tambah Barang Baru">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                    <span>Tambah Barang</span>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-lg-2">
                                <a href="tambah_peminjaman.php" class="btn btn-outline-success w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center quick-action-btn" data-bs-toggle="tooltip" title="Buat Peminjaman Baru">
                                    <i class="fas fa-hand-holding fa-2x mb-2"></i>
                                    <span>Peminjaman Baru</span>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-lg-2">
                                <a href="pengembalian.php" class="btn btn-outline-warning w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center quick-action-btn" data-bs-toggle="tooltip" title="Proses Pengembalian">
                                    <i class="fas fa-undo-alt fa-2x mb-2"></i>
                                    <span>Pengembalian</span>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-lg-2">
                                <a href="tambah_mahasiswa.php" class="btn btn-outline-info w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center quick-action-btn" data-bs-toggle="tooltip" title="Tambah Data Mahasiswa">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <span>Tambah Mahasiswa</span>
                                </a>
                            </div>
                            <?php if ($role == 'superadmin'): ?>
                            <div class="col-sm-6 col-md-4 col-lg-2">
                                <a href="tambah_user.php" class="btn btn-outline-secondary w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center quick-action-btn" data-bs-toggle="tooltip" title="Tambah User Baru">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <span>Tambah User</span>
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="col-sm-6 col-md-4 col-lg-2">
                                <a href="laporan.php" class="btn btn-outline-dark w-100 h-100 py-3 d-flex flex-column align-items-center justify-content-center quick-action-btn" data-bs-toggle="tooltip" title="Lihat Laporan">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                    <span>Laporan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips & Shortcuts -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-lightbulb me-2"></i>Tips & Shortcuts
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="d-flex">
                                    <div class="icon-circle-sm bg-primary me-3">
                                        <i class="fas fa-keyboard text-white"></i>
                                    </div>
                                    <div>
                                        <h6>Keyboard Shortcuts</h6>
                                        <small class="text-muted">Ctrl+R = Refresh, Ctrl+P = Print, Ctrl+F = Cari</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex">
                                    <div class="icon-circle-sm bg-success me-3">
                                        <i class="fas fa-mouse-pointer text-white"></i>
                                    </div>
                                    <div>
                                        <h6>Klik Dua Kali</h6>
                                        <small class="text-muted">Klik dua kali pada baris tabel untuk melihat detail</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex">
                                    <div class="icon-circle-sm bg-info me-3">
                                        <i class="fas fa-search text-white"></i>
                                    </div>
                                    <div>
                                        <h6>Cari Cepat</h6>
                                        <small class="text-muted">Gunakan search bar di setiap halaman untuk pencarian cepat</small>
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
    <footer class="footer mt-auto py-3 bg-white border-top">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-muted">
                    <i class="fas fa-copyright me-1"></i> <?= date('Y') ?> Sistem Peminjaman Barang
                    <span class="ms-3">
                        <i class="fas fa-database me-1"></i> Total Data: <?= $total_barang + $total_mahasiswa + $total_user ?>
                    </span>
                </div>
                <div class="col-md-6 text-end text-muted">
                    <i class="fas fa-user-shield me-1"></i> <?= $role_display ?> | 
                    <i class="fas fa-clock me-1 ms-2"></i> <span id="currentTime"><?= date('d/m/Y H:i:s') ?></span>
                    <span class="ms-3" id="liveStats">
                        <i class="fas fa-sync-alt fa-spin me-1"></i> Memperbarui...
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal untuk Detail Peminjaman -->
    <div class="modal fade" id="peminjamanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Detail Peminjaman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="peminjamanDetail">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printDetail()">
                        <i class="fas fa-print me-1"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Statistik Detail -->
    <div class="modal fade" id="statsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-bar me-2"></i>Detail Statistik
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="detailedChart" height="300"></canvas>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Metrik</th>
                                        <th>Nilai</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-box me-1"></i> Total Barang</td>
                                        <td><?= $total_barang ?></td>
                                        <td>100%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-check-circle me-1"></i> Tersedia</td>
                                        <td><?= $total_barang_tersedia ?></td>
                                        <td><?= round(($total_barang_tersedia / $total_barang) * 100, 1) ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-hand-holding me-1"></i> Sedang Dipinjam</td>
                                        <td><?= $total_barang_sedang_dipinjam ?></td>
                                        <td><?= round(($total_barang_sedang_dipinjam / $total_barang) * 100, 1) ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-exclamation-triangle me-1"></i> Hilang</td>
                                        <td><?= $total_barang_hilang ?></td>
                                        <td><?= round(($total_barang_hilang / $total_barang) * 100, 1) ?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/script.js"></script>
    <script src="js/dashboard.js"></script>
    
    <script>
        // Inisialisasi AOS
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Chart data from PHP
        const kategoriData = {
            labels: <?= json_encode($kategori_labels) ?>,
            datasets: [{
                data: <?= json_encode($kategori_data) ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                borderWidth: 1
            }]
        };
        
        const statusData = {
            labels: ['Dipinjam', 'Dikembalikan', 'Hilang'],
            datasets: [{
                label: 'Jumlah',
                data: [<?= $total_dipinjam ?>, <?= $total_dikembalikan ?>, <?= $total_barang_hilang ?>],
                backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
                borderWidth: 1
            }]
        };
    </script>
</body>
</html>