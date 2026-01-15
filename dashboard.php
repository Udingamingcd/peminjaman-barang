<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Query untuk statistik
$query_stats = "
    SELECT 
        (SELECT COUNT(*) FROM barang) as total_barang,
        (SELECT COUNT(*) FROM mahasiswa) as total_mahasiswa,
        (SELECT COUNT(*) FROM peminjaman) as total_peminjaman,
        (SELECT COUNT(*) FROM users) as total_users
";
$result_stats = mysqli_query($koneksi, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Query untuk status barang
$query_status = "SELECT status, COUNT(*) as jumlah FROM barang GROUP BY status";
$result_status = mysqli_query($koneksi, $query_status);
$status_counts = [];
while($row = mysqli_fetch_assoc($result_status)) {
    $status_counts[$row['status']] = $row['jumlah'];
}

// Query untuk peminjaman aktif
$query_peminjaman_aktif = "
    SELECT p.*, m.nama as nama_mahasiswa, b.nama_barang, b.kode_barang
    FROM peminjaman p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN barang b ON p.barang_id = b.id
    WHERE p.status = 'dipinjam'
    ORDER BY p.tanggal_pinjam DESC
    LIMIT 5
";
$result_peminjaman_aktif = mysqli_query($koneksi, $query_peminjaman_aktif);

// Query untuk barang yang hampir habis (jika ada stok)
$query_barang_populer = "
    SELECT b.*, COUNT(p.id) as jumlah_pinjam
    FROM barang b
    LEFT JOIN peminjaman p ON b.id = p.barang_id
    GROUP BY b.id
    ORDER BY jumlah_pinjam DESC
    LIMIT 5
";
$result_barang_populer = mysqli_query($koneksi, $query_barang_populer);
?>

<!-- Main Content -->
<div class="col-lg-10 col-md-9 main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Dashboard</h3>
            <p class="text-muted mb-0">Selamat datang, <?= $_SESSION['nama_lengkap'] ?>!</p>
        </div>
        <div>
            <span class="badge bg-primary"><?= date('d F Y') ?></span>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card tersedia">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Barang</h6>
                            <h3 id="total-barang"><?= $stats['total_barang'] ?></h3>
                        </div>
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-box text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-primary"><?= $status_counts['tersedia'] ?? 0 ?> Tersedia</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card dipinjam">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Mahasiswa</h6>
                            <h3 id="total-mahasiswa"><?= $stats['total_mahasiswa'] ?></h3>
                        </div>
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-user-graduate text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-warning">Peminjam Aktif</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card dikembalikan">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Peminjaman</h6>
                            <h3 id="total-peminjaman"><?= $stats['total_peminjaman'] ?></h3>
                        </div>
                        <div class="icon-circle bg-success">
                            <i class="fas fa-handshake text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success">Aktif: <?= mysqli_num_rows($result_peminjaman_aktif) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card hilang">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Admin</h6>
                            <h3 id="total-admin"><?= $stats['total_users'] ?></h3>
                        </div>
                        <div class="icon-circle bg-danger">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-danger"><?= $_SESSION['role'] == 'superadmin' ? 'Superadmin' : 'Admin' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Tables -->
    <div class="row">
        <!-- Peminjaman Aktif -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock text-warning me-2"></i> Peminjaman Aktif
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(mysqli_num_rows($result_peminjaman_aktif) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Peminjam</th>
                                    <th>Barang</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result_peminjaman_aktif)): ?>
                                <tr>
                                    <td><span class="badge bg-warning"><?= $row['kode_peminjaman'] ?></span></td>
                                    <td><?= $row['nama_mahasiswa'] ?></td>
                                    <td><?= $row['nama_barang'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Tidak ada peminjaman aktif</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Barang Populer -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star text-primary me-2"></i> Barang Paling Sering Dipinjam
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(mysqli_num_rows($result_barang_populer) > 0): ?>
                    <div class="list-group">
                        <?php while($row = mysqli_fetch_assoc($result_barang_populer)): ?>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= $row['nama_barang'] ?></h6>
                                    <small class="text-muted"><?= $row['kode_barang'] ?></small>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?= $row['jumlah_pinjam'] ?>x</span>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: <?= min($row['jumlah_pinjam'] * 20, 100) ?>%"></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                        <p class="text-muted">Belum ada data peminjaman</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-success me-2"></i> Akses Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="peminjaman_manage.php?action=new" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-2"></i> Peminjaman Baru
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="barang_manage.php?action=new" class="btn btn-success w-100">
                                <i class="fas fa-box me-2"></i> Tambah Barang
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="mahasiswa_manage.php?action=new" class="btn btn-info w-100">
                                <i class="fas fa-user-plus me-2"></i> Tambah Mahasiswa
                            </a>
                        </div>
                        <?php if ($_SESSION['role'] == 'superadmin'): ?>
                        <div class="col-md-3 mb-3">
                            <a href="admin_manage.php?action=new" class="btn btn-warning w-100">
                                <i class="fas fa-user-cog me-2"></i> Tambah Admin
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>