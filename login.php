<?php
// login.php
session_start();
require_once 'config/koneksi.php';

// Cek apakah sudah login, jika ya redirect ke halaman sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'superadmin') {
        header("Location: superadmin/index.php");
    } else {
        header("Location: admin/index.php");
    }
    exit();
}

// Cek apakah sudah ada superadmin di database
$query_superadmin = "SELECT COUNT(*) as total FROM users WHERE role = 'superadmin'";
$result_superadmin = mysqli_query($koneksi, $query_superadmin);
$row_superadmin = mysqli_fetch_assoc($result_superadmin);
$superadmin_exists = $row_superadmin['total'] > 0;

// Proses login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect berdasarkan role
            if ($user['role'] == 'superadmin') {
                header("Location: superadmin/index.php");
            } else {
                header("Location: admin/index.php");
            }
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Peminjaman Barang - Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/login.css">
    
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
    <header class="header-login">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <div class="logo-container">
                        <div class="logo-wrapper">
                            <?php
                            $logo_pnp = "assets/images/logo kampus.png";
                            if (file_exists($logo_pnp)):
                            ?>
                                <img src="<?php echo $logo_pnp; ?>" alt="Logo Politeknik Negeri Padang" class="logo-img">
                            <?php else: ?>
                                <div class="logo-fallback">
                                    <i class="fas fa-university"></i>
                                </div>
                            <?php endif; ?>
                            <div class="logo-text">
                                <h5 class="mb-0 fw-bold">Politeknik Negeri Padang</h5>
                                <small class="text-muted">Teknologi Informasi</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="system-title">
                        <h1 class="display-6 fw-bold text-primary">
                            <?php
                            $logo_sipembar = "assets/images/logo-sipembar.png";
                            if (file_exists($logo_sipembar)):
                            ?>
                                <img src="<?php echo $logo_sipembar; ?>" alt="SIPEMBAR Logo" class="system-logo me-2">
                            <?php else: ?>
                                <i class="fas fa-boxes me-2"></i>
                            <?php endif; ?>
                            SIPEMBAR
                        </h1>
                        <p class="text-muted mb-0">Sistem Peminjaman Barang</p>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <div class="program-container">
                        <div class="program-wrapper">
                            <div class="program-text text-end">
                                <h5 class="mb-0 fw-bold">Sistem Informasi</h5>
                                <small class="text-muted">Program Studi</small>
                            </div>
                            <?php
                            $logo_informatika = "assets/images/logo SI.png";
                            if (file_exists($logo_informatika)):
                            ?>
                                <img src="<?php echo $logo_informatika; ?>" alt="Logo Informatika" class="program-img">
                            <?php else: ?>
                                <div class="program-fallback">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-lg animate__animated animate__fadeInUp login-card">
                        <div class="card-header bg-gradient-primary text-white text-center py-4 position-relative">
                            <div class="header-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <h2 class="fw-bold mt-3 mb-1">LOGIN SYSTEM</h2>
                            <p class="mb-0 opacity-75">Masukkan kredensial Anda untuk mengakses sistem</p>
                        </div>
                        
                        <div class="card-body p-4 p-md-5">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                        <div class="flex-grow-1">
                                            <strong>Login Gagal!</strong>
                                            <div class="small"><?php echo $error; ?></div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <form id="loginForm" method="POST" action="" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="username" class="form-label fw-semibold">
                                        <i class="fas fa-user-circle me-2 text-primary"></i>Username
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-lg" id="username" name="username" 
                                               placeholder="Masukkan username" required
                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                               title="Masukkan username yang terdaftar">
                                        <div class="valid-feedback">
                                            <i class="fas fa-check-circle"></i> Username valid
                                        </div>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-times-circle"></i> Harap isi username
                                        </div>
                                    </div>
                                    <div class="form-text mt-1">
                                        <i class="fas fa-info-circle me-1"></i>Gunakan username yang diberikan
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="fas fa-key me-2 text-primary"></i>Password
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                               placeholder="Masukkan password" required
                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                               title="Masukkan password yang sesuai">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="valid-feedback">
                                            <i class="fas fa-check-circle"></i> Password valid
                                        </div>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-times-circle"></i> Harap isi password
                                        </div>
                                    </div>
                                    <div class="password-strength mt-3 d-none" id="passwordStrength">
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
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">
                                            <i class="fas fa-remember me-1"></i>Ingat saya
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg btn-login shadow-sm" id="loginButton">
                                        <span class="button-text">
                                            <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                
                                <?php if (!$superadmin_exists): ?>
                                    <div class="text-center mb-4 animate__animated animate__pulse animate__infinite animate__slower">
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-info-circle me-1"></i>Belum ada superadmin terdaftar?
                                        </p>
                                        <a href="pendaftaran_superadmin.php" class="btn btn-outline-success btn-sm btn-register">
                                            <i class="fas fa-user-shield me-2"></i>Daftarkan Superadmin
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="separator my-4">
                                    <span class="bg-light px-3 text-muted">atau</span>
                                </div>
                                
                                <div class="text-center">
                                    <div class="system-info mb-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <i class="fas fa-shield-alt text-primary me-2"></i>
                                            <small class="text-muted">Sistem terenkripsi dengan teknologi keamanan tinggi</small>
                                        </div>
                                    </div>
                                    <div class="system-stats">
                                        <div class="d-flex justify-content-center gap-4">
                                            <div class="text-center">
                                                <div class="stat-icon text-primary">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="stat-value fw-bold" id="userCount">0</div>
                                                <small class="text-muted">Pengguna</small>
                                            </div>
                                            <div class="text-center">
                                                <div class="stat-icon text-success">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="stat-value fw-bold" id="itemCount">0</div>
                                                <small class="text-muted">Barang</small>
                                            </div>
                                            <div class="text-center">
                                                <div class="stat-icon text-warning">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </div>
                                                <div class="stat-value fw-bold" id="loanCount">0</div>
                                                <small class="text-muted">Peminjaman</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="card-footer bg-light text-center py-3">
                            <small class="text-muted">
                                <i class="fas fa-copyright me-1"></i>
                                SIPEMBAR &copy; <?php echo date('Y'); ?> - Universitas Teknologi Informasi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="welcomeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-primary text-white">
                <i class="fas fa-bell me-2"></i>
                <strong class="me-auto">SIPEMBAR Notification</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Selamat datang di Sistem Peminjaman Barang Universitas!
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer-login mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center py-3">
                    <div class="footer-links">
                        <a href="#" class="text-muted me-3">
                            <i class="fas fa-question-circle me-1"></i>Bantuan
                        </a>
                        <a href="#" class="text-muted me-3">
                            <i class="fas fa-file-alt me-1"></i>Kebijakan Privasi
                        </a>
                        <a href="#" class="text-muted">
                            <i class="fas fa-envelope me-1"></i>Kontak
                        </a>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-server me-1"></i>
                            Versi 2.1.0 | Status: <span class="text-success">Online</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/login.js"></script>
</body>
</html>