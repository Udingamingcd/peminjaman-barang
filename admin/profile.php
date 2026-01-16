<?php
// admin/profile.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Ambil data user dari database
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

$message = '';
$message_type = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap_baru = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    
    $update_query = "UPDATE users SET nama_lengkap = '$nama_lengkap_baru', email = '$email' WHERE id = '$user_id'";
    
    if (mysqli_query($koneksi, $update_query)) {
        $_SESSION['nama_lengkap'] = $nama_lengkap_baru;
        $message = "Profil berhasil diperbarui!";
        $message_type = "success";
        
        // Refresh data user
        $result = mysqli_query($koneksi, $query);
        $user = mysqli_fetch_assoc($result);
        $nama_lengkap = $nama_lengkap_baru;
    } else {
        $message = "Gagal memperbarui profil: " . mysqli_error($koneksi);
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
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
                <li class="nav-item">
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
                <li class="nav-item active">
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
                            <i class="fas fa-user me-2"></i>Profil Saya
                        </h4>
                        <small class="text-muted">Kelola informasi akun Anda</small>
                    </div>
                    <div class="topbar-right">
                        <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Informasi Profil</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                                        <?php echo $message; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-12 mb-4 text-center">
                                            <div class="profile-avatar mb-3">
                                                <div class="avatar-circle">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <h5 class="mt-3"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h5>
                                                <p class="text-muted mb-0"><?php echo ucfirst($user['role']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-user me-2"></i>Username
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user-tag"></i>
                                                </span>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                            </div>
                                            <small class="form-text text-muted">Username tidak dapat diubah</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-user-circle me-2"></i>Nama Lengkap
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <input type="text" class="form-control" name="nama_lengkap" 
                                                       value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-at"></i>
                                                </span>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-user-tag me-2"></i>Role
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
                                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-calendar-alt me-2"></i>Tanggal Bergabung
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <input type="text" class="form-control" 
                                                       value="<?php echo date('d/m/Y', strtotime($user['created_at'])); ?>" disabled>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-user-shield me-2"></i>Status Akun
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                </span>
                                                <input type="text" class="form-control" value="Aktif" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                                </a>
                                                <button type="submit" name="update_profile" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Akun</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <i class="fas fa-key text-primary"></i>
                                            <div>
                                                <small class="text-muted">ID Akun</small>
                                                <p class="mb-0 fw-bold"><?php echo $user['id']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <i class="fas fa-history text-info"></i>
                                            <div>
                                                <small class="text-muted">Terakhir Login</small>
                                                <p class="mb-0 fw-bold">
                                                    <?php echo isset($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Belum tercatat'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Untuk keamanan, pastikan informasi akun Anda tetap rahasia.
                                    </small>
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
                            SIPEMBAR &copy; <?php echo date('Y'); ?> - Sistem Peminjaman Barang
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