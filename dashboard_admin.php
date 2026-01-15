<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/auth_check.php';

// Hanya admin biasa yang bisa akses
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard_superadmin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-speedometer2 text-primary"></i> Dashboard Admin
                    </h2>
                    <div>
                        <span class="badge bg-primary fs-6">
                            <i class="bi bi-person-check"></i> Admin Biasa
                        </span>
                    </div>
                </div>
                
                <p class="text-muted mb-4">
                    <i class="bi bi-person-fill"></i> Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>!
                    Anda bertugas mengelola peminjaman barang.
                </p>
                
                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Peminjaman</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM peminjaman WHERE admin_id = " . $_SESSION['user_id'];
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-primary"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-primary p-3 rounded-circle">
                                        <i class="bi bi-cart-check-fill text-white fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-warning shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Peminjaman Aktif</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM peminjaman 
                                                  WHERE admin_id = " . $_SESSION['user_id'] . " 
                                                  AND status = 'dipinjam'";
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-warning"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-warning p-3 rounded-circle">
                                        <i class="bi bi-clock-history text-white fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Pengembalian Hari Ini</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM peminjaman 
                                                  WHERE admin_id = " . $_SESSION['user_id'] . " 
                                                  AND DATE(tanggal_kembali) = CURDATE()";
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-success"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-success p-3 rounded-circle">
                                        <i class="bi bi-check-circle-fill text-white fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-lightning-charge"></i> Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <a href="peminjaman.php?action=tambah" class="btn btn-primary w-100 py-3">
                                            <i class="bi bi-cart-plus fs-4"></i>
                                            <br>Peminjaman Baru
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="pengembalian.php" class="btn btn-success w-100 py-3">
                                            <i class="bi bi-arrow-return-left fs-4"></i>
                                            <br>Pengembalian
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="barang.php" class="btn btn-warning w-100 py-3">
                                            <i class="bi bi-search fs-4"></i>
                                            <br>Cek Barang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Peminjaman Aktif Saya -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle"></i> Peminjaman Aktif Saya
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Mahasiswa</th>
                                                <th>Angkatan</th>
                                                <th>Barang</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Estimasi Kembali</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT p.*, m.nama as nama_mahasiswa, m.angkatan, b.nama_barang 
                                                      FROM peminjaman p
                                                      JOIN mahasiswa m ON p.mahasiswa_id = m.id
                                                      JOIN barang b ON p.barang_id = b.id
                                                      WHERE p.admin_id = " . $_SESSION['user_id'] . "
                                                      AND p.status = 'dipinjam'
                                                      ORDER BY p.tanggal_pinjam ASC 
                                                      LIMIT 5";
                                            $result = mysqli_query($koneksi, $query);
                                            
                                            if(mysqli_num_rows($result) > 0):
                                                while($row = mysqli_fetch_assoc($result)):
                                                    // Hitung estimasi kembali (misal: 7 hari dari pinjam)
                                                    $estimasi = date('d/m/Y', strtotime($row['tanggal_pinjam'] . ' +7 days'));
                                            ?>
                                            <tr>
                                                <td><code><?= $row['kode_peminjaman']; ?></code></td>
                                                <td><?= htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                                <td><span class="badge bg-info"><?= $row['angkatan']; ?></span></td>
                                                <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                                <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                                <td><?= $estimasi; ?></td>
                                                <td>
                                                    <a href="pengembalian.php?kode=<?= $row['kode_peminjaman']; ?>" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle"></i> Kembalikan
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="bi bi-check2-circle"></i> Tidak ada peminjaman aktif
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Panduan Cepat -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-book"></i> Panduan Cepat
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="accordionGuide">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#guide1">
                                                Cara Peminjaman
                                            </button>
                                        </h2>
                                        <div id="guide1" class="accordion-collapse collapse show" data-bs-parent="#accordionGuide">
                                            <div class="accordion-body">
                                                1. Klik "Peminjaman Baru"<br>
                                                2. Pilih mahasiswa<br>
                                                3. Pilih barang tersedia<br>
                                                4. Upload bukti foto<br>
                                                5. Simpan data
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#guide2">
                                                Cara Pengembalian
                                            </button>
                                        </h2>
                                        <div id="guide2" class="accordion-collapse collapse" data-bs-parent="#accordionGuide">
                                            <div class="accordion-body">
                                                1. Klik "Pengembalian"<br>
                                                2. Cari kode peminjaman<br>
                                                3. Upload bukti pengembalian<br>
                                                4. Konfirmasi kondisi barang<br>
                                                5. Selesaikan proses
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile Info -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-badge"></i> Informasi Admin
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="bi bi-person-circle fs-1 text-primary"></i>
                                    <h5 class="mt-3"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></h5>
                                    <p class="text-muted mb-2">Username: <?= $_SESSION['username']; ?></p>
                                    <p class="mb-3">
                                        <span class="badge bg-primary">Admin Biasa</span>
                                    </p>
                                    <hr>
                                    <p class="mb-0 text-muted">
                                        <i class="bi bi-calendar"></i> Login: <?= date('d F Y'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-3 bg-light border-top">
        <div class="container text-center">
            <small class="text-muted">
                &copy; <?= date('Y'); ?> Sistem Peminjaman Barang - Admin Dashboard
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>