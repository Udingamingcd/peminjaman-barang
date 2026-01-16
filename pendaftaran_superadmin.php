<?php
// pendaftaran_superadmin.php
session_start();
require_once 'config/koneksi.php';

// Cek apakah sudah ada superadmin di database
$query_superadmin = "SELECT COUNT(*) as total FROM users WHERE role = 'superadmin'";
$result_superadmin = mysqli_query($koneksi, $query_superadmin);
$row_superadmin = mysqli_fetch_assoc($result_superadmin);

// Jika sudah ada superadmin, redirect ke index.php
if ($row_superadmin['total'] > 0) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($koneksi, $_POST['confirm_password']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    
    // Validasi password
    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah terdaftar
        $check_query = "SELECT * FROM users WHERE username = '$username'";
        $check_result = mysqli_query($koneksi, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert superadmin ke database
            $insert_query = "INSERT INTO users (username, password, nama_lengkap, role) 
                            VALUES ('$username', '$hashed_password', '$nama_lengkap', 'superadmin')";
            
            if (mysqli_query($koneksi, $insert_query)) {
                $success = "Superadmin berhasil didaftarkan! Silakan login.";
            } else {
                $error = "Gagal mendaftarkan superadmin: " . mysqli_error($koneksi);
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
    <title>Pendaftaran Superadmin - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/register_superadmin.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body class="bg-light">
    <!-- Background Animation -->
    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    
    <!-- Header dengan Logo -->
    <header class="header-register">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <div class="logo-container">
                        <div class="logo-wrapper">
                            <i class="fas fa-university logo-icon"></i>
                            <div class="logo-text">
                                <h5 class="mb-0 fw-bold">UNIVERSITAS</h5>
                                <small class="text-muted">Teknologi Informasi</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="system-title">
                        <h1 class="display-6 fw-bold text-success">
                            <i class="fas fa-user-shield me-2"></i>SUPERADMIN
                        </h1>
                        <p class="text-muted mb-0">Registrasi Administrator Utama</p>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <div class="program-container">
                        <div class="program-wrapper">
                            <div class="program-text text-end">
                                <h5 class="mb-0 fw-bold">INFORMATIKA</h5>
                                <small class="text-muted">Program Studi</small>
                            </div>
                            <i class="fas fa-laptop-code program-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <div class="card shadow-lg animate__animated animate__fadeInUp register-card">
                        <div class="card-header bg-gradient-success text-white text-center py-4 position-relative">
                            <div class="header-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h2 class="fw-bold mt-3 mb-1">REGISTRASI SUPERADMIN</h2>
                            <p class="mb-0 opacity-75">Pendaftaran administrator utama sistem</p>
                        </div>
                        
                        <div class="card-body p-4 p-md-5">
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success alert-dismissible fade show animate__animated animate__bounceIn" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle me-3 fs-4"></i>
                                        <div class="flex-grow-1">
                                            <strong>Registrasi Berhasil!</strong>
                                            <div class="small"><?php echo $success; ?></div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="index.php" class="btn btn-outline-success btn-back">
                                            <i class="fas fa-sign-in-alt me-2"></i>Kembali ke Halaman Login
                                        </a>
                                    </div>
                                </div>
                            <?php elseif (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                        <div class="flex-grow-1">
                                            <strong>Registrasi Gagal!</strong>
                                            <div class="small"><?php echo $error; ?></div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (empty($success)): ?>
                                <form id="registerForm" method="POST" action="" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="form-section">
                                                <h5 class="section-title">
                                                    <i class="fas fa-id-card me-2"></i>Data Personal
                                                </h5>
                                                <div class="mb-3">
                                                    <label for="nama_lengkap" class="form-label fw-semibold">
                                                        <i class="fas fa-user me-2 text-success"></i>Nama Lengkap
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-user text-success"></i>
                                                        </span>
                                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                                               placeholder="Masukkan nama lengkap" required
                                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                                               title="Masukkan nama lengkap superadmin">
                                                        <div class="valid-feedback">
                                                            <i class="fas fa-check-circle"></i> Nama valid
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            <i class="fas fa-times-circle"></i> Harap isi nama lengkap
                                                        </div>
                                                    </div>
                                                    <div class="form-text mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Nama lengkap administrator
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <div class="form-section">
                                                <h5 class="section-title">
                                                    <i class="fas fa-user-tag me-2"></i>Data Akun
                                                </h5>
                                                <div class="mb-3">
                                                    <label for="username" class="form-label fw-semibold">
                                                        <i class="fas fa-user-tag me-2 text-success"></i>Username
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-user-tag text-success"></i>
                                                        </span>
                                                        <input type="text" class="form-control" id="username" name="username" 
                                                               placeholder="Masukkan username" required
                                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                                               title="Username harus unik dan mudah diingat">
                                                        <div class="valid-feedback">
                                                            <i class="fas fa-check-circle"></i> Username valid
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            <i class="fas fa-times-circle"></i> Harap isi username (min. 3 karakter)
                                                        </div>
                                                    </div>
                                                    <div class="form-text mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Username untuk login ke sistem
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="form-section">
                                                <h5 class="section-title">
                                                    <i class="fas fa-key me-2"></i>Keamanan Akun
                                                </h5>
                                                <div class="mb-3">
                                                    <label for="password" class="form-label fw-semibold">
                                                        <i class="fas fa-lock me-2 text-success"></i>Password
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-lock text-success"></i>
                                                        </span>
                                                        <input type="password" class="form-control" id="password" name="password" 
                                                               placeholder="Masukkan password" required
                                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                                               title="Password minimal 6 karakter">
                                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <div class="valid-feedback">
                                                            <i class="fas fa-check-circle"></i> Password valid
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            <i class="fas fa-times-circle"></i> Password minimal 6 karakter
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="password-strength mt-3" id="passwordStrength">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">Kekuatan password:</small>
                                                        <small class="fw-semibold" id="strengthText">Lemah</small>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-lightbulb me-1"></i>
                                                        Gunakan kombinasi huruf, angka, dan simbol
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <div class="form-section">
                                                <h5 class="section-title">
                                                    <i class="fas fa-lock me-2"></i>Konfirmasi
                                                </h5>
                                                <div class="mb-3">
                                                    <label for="confirm_password" class="form-label fw-semibold">
                                                        <i class="fas fa-lock me-2 text-success"></i>Konfirmasi Password
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-lock text-success"></i>
                                                        </span>
                                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                               placeholder="Ulangi password" required
                                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                                               title="Harus sama dengan password di atas">
                                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <div class="valid-feedback">
                                                            <i class="fas fa-check-circle"></i> Password cocok
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            <i class="fas fa-times-circle"></i> Password tidak cocok
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="password-match mt-3" id="passwordMatch">
                                                    <div class="alert alert-light border" id="matchAlert">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-info-circle me-3 text-info fs-5"></i>
                                                            <div>
                                                                <small class="fw-semibold d-block" id="matchText">Menunggu input password</small>
                                                                <small class="text-muted" id="matchDescription">
                                                                    Pastikan kedua password sama untuk keamanan akun
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                        <a href="index.php" class="btn btn-outline-secondary me-md-2 btn-back">
                                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                                        </a>
                                        <button type="submit" class="btn btn-success btn-lg btn-register shadow-sm" id="registerButton">
                                            <span class="button-text">
                                                <i class="fas fa-user-plus me-2"></i>Daftarkan Superadmin
                                            </span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    
                                    <div class="mt-5">
                                        <div class="alert alert-warning border-warning animate__animated animate__pulse animate__infinite animate__slower">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-exclamation-triangle fs-4 text-warning"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="alert-heading mb-2">
                                                        <i class="fas fa-shield-alt me-2"></i>Informasi Penting!
                                                    </h5>
                                                    <ul class="mb-0 ps-3">
                                                        <li class="mb-2">Halaman ini hanya dapat diakses sekali saat sistem pertama kali diinstal</li>
                                                        <li class="mb-2">Setelah superadmin terdaftar, halaman ini tidak akan dapat diakses lagi</li>
                                                        <li class="mb-2">Simpan informasi login superadmin dengan aman di tempat yang terlindungi</li>
                                                        <li>Superadmin memiliki hak akses penuh terhadap seluruh sistem</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-light text-center py-3">
                            <div class="row">
                                <div class="col-md-4 text-start">
                                    <small class="text-muted">
                                        <i class="fas fa-history me-1"></i>
                                        Waktu: <?php echo date('d/m/Y'); ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">
                                        <i class="fas fa-database me-1"></i>
                                        Sistem: <span class="text-success">Siap Registrasi</span>
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Jam: <?php echo date('H:i:s'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="registerToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-user-shield me-2"></i>
                <strong class="me-auto">Registrasi Superadmin</strong>
                <small>Penting</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Pastikan data yang diisi sudah benar sebelum submit!
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer-register mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center py-3">
                    <div class="footer-links">
                        <a href="#" class="text-muted me-3">
                            <i class="fas fa-question-circle me-1"></i>Panduan
                        </a>
                        <a href="#" class="text-muted me-3">
                            <i class="fas fa-shield-alt me-1"></i>Keamanan
                        </a>
                        <a href="#" class="text-muted me-3">
                            <i class="fas fa-file-alt me-1"></i>Kebijakan
                        </a>
                        <a href="#" class="text-muted">
                            <i class="fas fa-envelope me-1"></i>Dukungan
                        </a>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-server me-1"></i>
                            SIPEMBAR v2.1.0 | Hak Cipta &copy; <?php echo date('Y'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/register_superadmin.js"></script>
</body>
</html>