<?php
// superadmin/index.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan role superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Ambil data statistik lengkap
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'superadmin') as total_superadmin,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admin,
    (SELECT COUNT(*) FROM mahasiswa) as total_mahasiswa,
    (SELECT COUNT(*) FROM barang) as total_barang,
    (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam') as sedang_dipinjam,
    (SELECT COUNT(*) FROM peminjaman WHERE status = 'dikembalikan') as total_dikembalikan,
    (SELECT COUNT(*) FROM peminjaman WHERE status = 'hilang') as total_hilang,
    (SELECT COUNT(*) FROM peminjaman WHERE DATE(created_at) = CURDATE()) as peminjaman_hari_ini";
$result_stats = mysqli_query($koneksi, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil data aktivitas terbaru
$query_activities = "SELECT 
    u.nama_lengkap as admin,
    a.action,
    a.description,
    a.created_at
    FROM activity_log a
    JOIN users u ON a.admin_id = u.id
    ORDER BY a.created_at DESC LIMIT 10";
$result_activities = mysqli_query($koneksi, $query_activities);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Superadmin - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/superadmin.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <i class="fas fa-user-shield logo-icon"></i>
                <h3 class="logo-text">SuperAdmin</h3>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="user-details">
                    <h6 class="mb-0"><?php echo htmlspecialchars($nama_lengkap); ?></h6>
                    <small class="text-muted text-warning"><?php echo ucfirst($role); ?></small>
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
                    <a class="nav-link" href="manage_users.php">
                        <i class="fas fa-users-cog"></i>
                        <span>Kelola User</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_mahasiswa.php">
                        <i class="fas fa-user-graduate"></i>
                        <span>Kelola Mahasiswa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_barang.php">
                        <i class="fas fa-boxes"></i>
                        <span>Kelola Barang</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_peminjaman.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Kelola Peminjaman</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="laporan.php">
                        <i class="fas fa-chart-pie"></i>
                        <span>Laporan Lengkap</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cogs"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="backup.php">
                        <i class="fas fa-database"></i>
                        <span>Backup Database</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activity_log.php">
                        <i class="fas fa-history"></i>
                        <span>Log Aktivitas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/change_password.php">
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
            <small class="text-muted">SuperAdmin Panel v2.1.0</small>
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
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard Superadmin
                        </h4>
                        <small class="text-muted">Kontrol penuh sistem - <?php echo htmlspecialchars($nama_lengkap); ?></small>
                    </div>
                    <div class="topbar-right">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-crown text-warning"></i> Superadmin
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../admin/profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="../admin/change_password.php"><i class="fas fa-key me-2"></i>Ganti Password</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cogs me-2"></i>Pengaturan</a></li>
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
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_superadmin']; ?></h3>
                                <p>Superadmin</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_admin']; ?></h3>
                                <p>Admin</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_mahasiswa']; ?></h3>
                                <p>Mahasiswa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_barang']; ?></h3>
                                <p>Total Barang</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['peminjaman_hari_ini']; ?></h3>
                                <p>Peminjaman Hari Ini</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-secondary">
                            <div class="stat-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['sedang_dipinjam']; ?></h3>
                                <p>Sedang Dipinjam</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-dark">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_dikembalikan']; ?></h3>
                                <p>Total Dikembalikan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-purple">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_hilang']; ?></h3>
                                <p>Barang Hilang</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Administrator Tools</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="manage_users.php?action=new" class="btn btn-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Tambah User
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="backup.php" class="btn btn-success w-100">
                                            <i class="fas fa-database me-2"></i>Backup Database
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="settings.php" class="btn btn-warning w-100">
                                            <i class="fas fa-cogs me-2"></i>Pengaturan Sistem
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="laporan.php" class="btn btn-info w-100">
                                            <i class="fas fa-file-export me-2"></i>Export Data
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
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <?php while($row = mysqli_fetch_assoc($result_activities)): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-icon">
                                            <?php 
                                            $action_icon = 'fa-info-circle';
                                            if(strpos($row['action'], 'add') !== false) $action_icon = 'fa-plus-circle';
                                            if(strpos($row['action'], 'edit') !== false) $action_icon = 'fa-edit';
                                            if(strpos($row['action'], 'delete') !== false) $action_icon = 'fa-trash-alt';
                                            ?>
                                            <i class="fas <?php echo $action_icon; ?>"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h6><?php echo htmlspecialchars($row['description']); ?></h6>
                                            <small class="text-muted">
                                                Oleh: <?php echo htmlspecialchars($row['admin']); ?> | 
                                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Sistem Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="system-status">
                                    <div class="status-item">
                                        <span class="status-indicator active"></span>
                                        <span>Database</span>
                                        <span class="ms-auto text-success">Online</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-indicator active"></span>
                                        <span>Web Server</span>
                                        <span class="ms-auto text-success">Running</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-indicator active"></span>
                                        <span>Backup</span>
                                        <span class="ms-auto text-success">Aktif</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-indicator active"></span>
                                        <span>Keamanan</span>
                                        <span class="ms-auto text-success">Tinggi</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <h6 class="mb-2">Penggunaan Sistem</h6>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 65%">65%</div>
                                    </div>
                                    <small class="text-muted">65% dari kapasitas maksimal</small>
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
                            SuperAdmin Panel &copy; <?php echo date('Y'); ?> - Sistem Peminjaman Barang
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../js/superadmin.js"></script>
</body>
</html>