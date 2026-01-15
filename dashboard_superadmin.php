<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/auth_check.php';

// Hanya superadmin yang bisa akses
if ($_SESSION['role'] != 'superadmin') {
    header("Location: dashboard_admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Superadmin - Sistem Peminjaman Barang</title>
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
                        <i class="bi bi-speedometer2 text-primary"></i> Dashboard Superadmin
                    </h2>
                    <div>
                        <span class="badge bg-success fs-6">
                            <i class="bi bi-shield-check"></i> Superadmin
                        </span>
                    </div>
                </div>
                
                <p class="text-muted mb-4">
                    <i class="bi bi-person-fill"></i> Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>!
                    Anda memiliki akses penuh ke sistem.
                </p>
                
                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Admin</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-primary"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-primary p-3 rounded-circle">
                                        <i class="bi bi-people-fill text-white fs-4"></i>
                                    </div>
                                </div>
                                <a href="users.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-success shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Mahasiswa</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM mahasiswa";
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-success"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-success p-3 rounded-circle">
                                        <i class="bi bi-person-badge-fill text-white fs-4"></i>
                                    </div>
                                </div>
                                <a href="mahasiswa.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-warning shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Barang</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM barang";
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-warning"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-warning p-3 rounded-circle">
                                        <i class="bi bi-box-fill text-white fs-4"></i>
                                    </div>
                                </div>
                                <a href="barang.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-info shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Peminjaman Aktif</h6>
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'";
                                        $result = mysqli_query($koneksi, $query);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>
                                        <h3 class="fw-bold text-info"><?= $row['total']; ?></h3>
                                    </div>
                                    <div class="bg-info p-3 rounded-circle">
                                        <i class="bi bi-cart-check-fill text-white fs-4"></i>
                                    </div>
                                </div>
                                <a href="peminjaman.php" class="stretched-link"></a>
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
                                        <a href="users.php?action=tambah" class="btn btn-primary w-100 py-3">
                                            <i class="bi bi-person-plus fs-4"></i>
                                            <br>Tambah Admin
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="mahasiswa.php?action=tambah" class="btn btn-success w-100 py-3">
                                            <i class="bi bi-person-badge fs-4"></i>
                                            <br>Tambah Mahasiswa
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="barang.php?action=tambah" class="btn btn-warning w-100 py-3">
                                            <i class="bi bi-box-seam fs-4"></i>
                                            <br>Tambah Barang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistik Mahasiswa per Angkatan -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-bar-chart"></i> Mahasiswa per Angkatan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php
                                    $query = "SELECT angkatan, COUNT(*) as jumlah 
                                              FROM mahasiswa 
                                              GROUP BY angkatan 
                                              ORDER BY angkatan DESC";
                                    $result = mysqli_query($koneksi, $query);
                                    
                                    while($row = mysqli_fetch_assoc($result)):
                                        $percentage = ($row['jumlah'] / 100) * 100; // Adjust based on your total
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Angkatan <?= $row['angkatan']; ?></span>
                                            <span><?= $row['jumlah']; ?> mahasiswa</span>
                                        </div>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: <?= min($percentage, 100); ?>%">
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Peminjaman -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i> Peminjaman Terbaru
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
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT p.*, m.nama as nama_mahasiswa, m.angkatan, b.nama_barang 
                                                      FROM peminjaman p
                                                      JOIN mahasiswa m ON p.mahasiswa_id = m.id
                                                      JOIN barang b ON p.barang_id = b.id
                                                      ORDER BY p.created_at DESC LIMIT 5";
                                            $result = mysqli_query($koneksi, $query);
                                            
                                            if(mysqli_num_rows($result) > 0):
                                                while($row = mysqli_fetch_assoc($result)):
                                                    $status_badge = ($row['status'] == 'dipinjam') ? 
                                                        'badge bg-warning' : 
                                                        ($row['status'] == 'dikembalikan' ? 'badge bg-success' : 'badge bg-danger');
                                            ?>
                                            <tr>
                                                <td><code><?= $row['kode_peminjaman']; ?></code></td>
                                                <td><?= htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                                <td><span class="badge bg-info"><?= $row['angkatan']; ?></span></td>
                                                <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                                <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                                <td><span class="<?= $status_badge; ?>"><?= $row['status']; ?></span></td>
                                            </tr>
                                            <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="bi bi-inbox"></i> Belum ada data peminjaman
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="peminjaman.php" class="btn btn-outline-info">
                                        <i class="bi bi-arrow-right"></i> Lihat Semua Peminjaman
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- System Info -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle"></i> Informasi Sistem
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Versi Sistem</span>
                                        <span class="badge bg-primary">v1.0.0</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Total Pengguna</span>
                                        <span class="badge bg-success">
                                            <?php
                                            $query = "SELECT COUNT(*) as total FROM users";
                                            $result = mysqli_query($koneksi, $query);
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Barang Tersedia</span>
                                        <span class="badge bg-warning">
                                            <?php
                                            $query = "SELECT COUNT(*) as total FROM barang WHERE status = 'tersedia'";
                                            $result = mysqli_query($koneksi, $query);
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Barang Hilang</span>
                                        <span class="badge bg-danger">
                                            <?php
                                            $query = "SELECT COUNT(*) as total FROM barang WHERE status = 'hilang'";
                                            $result = mysqli_query($koneksi, $query);
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Latest Mahasiswa -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-people"></i> Mahasiswa Terbaru
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php
                                    $query = "SELECT * FROM mahasiswa ORDER BY created_at DESC LIMIT 5";
                                    $result = mysqli_query($koneksi, $query);
                                    
                                    while($row = mysqli_fetch_assoc($result)):
                                    ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($row['nama']); ?></strong>
                                                <br>
                                                <small class="text-muted">NIM: <?= $row['nim']; ?></small>
                                            </div>
                                            <span class="badge bg-info"><?= $row['angkatan']; ?></span>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                                <div class="text-center mt-3">
                                    <a href="mahasiswa.php" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-arrow-right"></i> Lihat Semua
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last Login -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-calendar-check"></i> Login Terakhir
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="bi bi-person-circle fs-1 text-primary"></i>
                                    <h5 class="mt-3"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></h5>
                                    <p class="text-muted"><?= $_SESSION['role']; ?></p>
                                    <hr>
                                    <p class="mb-0">
                                        <i class="bi bi-clock"></i> 
                                        <?= date('d F Y H:i:s'); ?>
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
                &copy; <?= date('Y'); ?> Sistem Peminjaman Barang - Superadmin Dashboard
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>