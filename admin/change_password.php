<?php
// admin/change_password.php
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

$message = '';
$message_type = '';

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Semua field harus diisi!";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "Password baru dan konfirmasi password tidak cocok!";
        $message_type = "danger";
    } elseif (strlen($new_password) < 6) {
        $message = "Password baru minimal 6 karakter!";
        $message_type = "danger";
    } else {
        // Cek password saat ini
        $query = "SELECT password FROM users WHERE id = '$user_id'";
        $result = mysqli_query($koneksi, $query);
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($current_password, $user['password'])) {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
            
            if (mysqli_query($koneksi, $update_query)) {
                $message = "Password berhasil diubah!";
                $message_type = "success";
            } else {
                $message = "Gagal mengubah password: " . mysqli_error($koneksi);
                $message_type = "danger";
            }
        } else {
            $message = "Password saat ini salah!";
            $message_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - SIPEMBAR</title>
    
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
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="nav-item active">
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
                            <i class="fas fa-key me-2"></i>Ganti Password
                        </h4>
                        <small class="text-muted">Perbarui password akun Anda</small>
                    </div>
                    <div class="topbar-right">
                        <a href="profile.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user me-1"></i>Profil
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Form Ganti Password</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                                        <?php echo $message; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="" id="changePasswordForm">
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password Saat Ini
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" class="form-control" name="current_password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password Baru
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" class="form-control" name="new_password" id="newPassword" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength mt-3" id="passwordStrength">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Kekuatan password:</small>
                                                <small class="fw-semibold" id="strengthText">Lemah</small>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-2"></i>Konfirmasi Password Baru
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-match mt-3" id="passwordMatch">
                                            <div class="alert alert-light border" id="matchAlert">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-info-circle me-3 text-info"></i>
                                                    <div>
                                                        <small class="fw-semibold d-block" id="matchText">Menunggu input password</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="showPasswordRules">
                                            <label class="form-check-label" for="showPasswordRules">
                                                Tampilkan aturan password
                                            </label>
                                        </div>
                                        <div class="password-rules mt-3 d-none" id="passwordRules">
                                            <small class="text-muted">
                                                <strong>Password harus:</strong>
                                            </small>
                                            <ul class="small mb-0">
                                                <li>Minimal 6 karakter</li>
                                                <li>Mengandung huruf besar dan kecil</li>
                                                <li>Mengandung angka</li>
                                                <li>Mengandung karakter khusus (!@#$%^&*)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="change_password" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Simpan Password Baru
                                        </button>
                                        <a href="profile.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Profil
                                        </a>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Untuk keamanan, disarankan untuk mengganti password secara berkala.
                                </small>
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
    <script src="../js/change_password.js"></script>
</body>
</html>