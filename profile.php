<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/auth_check.php';

// Ambil data user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Update profile jika ada POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Cek username jika berubah
        if ($username != $user['username']) {
            $check_query = "SELECT * FROM users WHERE username = '$username' AND id != '$user_id'";
            $check_result = mysqli_query($koneksi, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                $_SESSION['error'] = "Username sudah digunakan!";
                header("Location: profile.php");
                exit();
            }
        }
        
        // Jika ada password baru
        if (!empty($new_password)) {
            // Verifikasi password lama
            if (!password_verify($current_password, $user['password'])) {
                $_SESSION['error'] = "Password lama salah!";
                header("Location: profile.php");
                exit();
            }
            
            if (strlen($new_password) < 8) {
                $_SESSION['error'] = "Password baru minimal 8 karakter!";
                header("Location: profile.php");
                exit();
            }
            
            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "Password baru dan konfirmasi tidak sama!";
                header("Location: profile.php");
                exit();
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET 
                      username = '$username', 
                      nama_lengkap = '$nama_lengkap',
                      password = '$hashed_password'
                      WHERE id = '$user_id'";
        } else {
            $query = "UPDATE users SET 
                      username = '$username', 
                      nama_lengkap = '$nama_lengkap'
                      WHERE id = '$user_id'";
        }
        
        if (mysqli_query($koneksi, $query)) {
            // Update session
            $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            
            $_SESSION['success'] = "Profile berhasil diperbarui!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal memperbarui profile: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i> Profile Pengguna
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $_SESSION['success']; ?>
                                <?php unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row mb-4">
                                <div class="col-md-4 text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-person-circle display-1 text-primary"></i>
                                    </div>
                                    <h5><?= htmlspecialchars($user['nama_lengkap']); ?></h5>
                                    <p class="text-muted">@<?= $user['username']; ?></p>
                                    <span class="badge bg-<?= $user['role'] == 'superadmin' ? 'success' : 'primary'; ?>">
                                        <?= $user['role']; ?>
                                    </span>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= $user['username']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?= $user['role']; ?>" readonly disabled>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">
                                <i class="bi bi-key"></i> Ubah Password
                                <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <div class="form-text">Minimal 8 karakter</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                                </a>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted">
                        <small>
                            <i class="bi bi-calendar"></i> Terdaftar sejak: 
                            <?= date('d F Y', strtotime($user['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>