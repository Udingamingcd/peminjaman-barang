<?php
// admin/laporan.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Ambil data untuk filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';

// Query laporan peminjaman dengan filter
$where_conditions = [];
$params = [];

if ($filter_bulan) {
    $where_conditions[] = "MONTH(p.tanggal_pinjam) = ?";
    $params[] = $filter_bulan;
}

if ($filter_tahun) {
    $where_conditions[] = "YEAR(p.tanggal_pinjam) = ?";
    $params[] = $filter_tahun;
}

if ($filter_jenis != 'semua') {
    $where_conditions[] = "p.status = ?";
    $params[] = $filter_jenis;
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Query data laporan dengan join tabel users untuk admin
$query_laporan = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, 
                 b.nama_barang, b.kode_barang,
                 u.nama_lengkap as admin_peminjam
                 FROM peminjaman p
                 JOIN mahasiswa m ON p.mahasiswa_id = m.id
                 JOIN barang b ON p.barang_id = b.id
                 JOIN users u ON p.admin_id = u.id
                 $where_clause
                 ORDER BY p.tanggal_pinjam DESC";
                 
if (!empty($params)) {
    $stmt = mysqli_prepare($koneksi, $query_laporan);
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
    mysqli_stmt_execute($stmt);
    $result_laporan = mysqli_stmt_get_result($stmt);
} else {
    $result_laporan = mysqli_query($koneksi, $query_laporan);
}

// Hitung statistik
$query_stats = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_dipinjam,
    SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as sudah_dikembalikan,
    SUM(CASE WHEN status = 'hilang' THEN 1 ELSE 0 END) as barang_hilang
    FROM peminjaman p $where_clause";
    
if (!empty($params)) {
    $stmt_stats = mysqli_prepare($koneksi, $query_stats);
    mysqli_stmt_bind_param($stmt_stats, str_repeat('s', count($params)), ...$params);
    mysqli_stmt_execute($stmt_stats);
    $result_stats = mysqli_stmt_get_result($stmt_stats);
} else {
    $result_stats = mysqli_query($koneksi, $query_stats);
}

$stats = mysqli_fetch_assoc($result_stats);

// Generate tanggal untuk filter
$bulan_list = [];
for ($i = 1; $i <= 12; $i++) {
    $bulan_list[$i] = date('F', mktime(0, 0, 0, $i, 1));
}

$tahun_list = [];
for ($i = date('Y'); $i >= date('Y') - 5; $i--) {
    $tahun_list[] = $i;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <style>
        .table-actions {
            white-space: nowrap;
        }
        .filter-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        /* Scroll horizontal untuk tabel */
        .table-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .table-wrapper {
            overflow-x: auto;
            position: relative;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .table-scroll-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .scroll-btn {
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .scroll-btn:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }
        
        .scroll-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .scroll-indicator {
            font-size: 12px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-fixed {
            min-width: 1200px; /* Lebar minimum untuk scroll */
        }
        
        @media (max-width: 768px) {
            .table-scroll-controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .scroll-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <?php include 'topbar.php'; ?>
        
        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Page Title -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="page-title"><i class="fas fa-chart-bar me-2"></i>Laporan Peminjaman</h3>
                        <p class="text-muted mb-0">Laporan dan statistik peminjaman barang</p>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="cetakLaporan()">
                            <i class="fas fa-print me-2"></i>Cetak Laporan
                        </button>
                        <button class="btn btn-primary" onclick="exportExcel()">
                            <i class="fas fa-file-excel me-2"></i>Export Excel
                        </button>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="bulan" class="form-label">Bulan</label>
                                    <select class="form-select" id="bulan" name="bulan">
                                        <option value="">Semua Bulan</option>
                                        <?php foreach($bulan_list as $key => $nama_bulan): ?>
                                            <option value="<?php echo $key; ?>" <?php echo $filter_bulan == $key ? 'selected' : ''; ?>>
                                                <?php echo $nama_bulan; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <select class="form-select" id="tahun" name="tahun">
                                        <option value="">Semua Tahun</option>
                                        <?php foreach($tahun_list as $tahun): ?>
                                            <option value="<?php echo $tahun; ?>" <?php echo $filter_tahun == $tahun ? 'selected' : ''; ?>>
                                                <?php echo $tahun; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="jenis" class="form-label">Status</label>
                                    <select class="form-select" id="jenis" name="jenis">
                                        <option value="semua">Semua Status</option>
                                        <option value="dipinjam" <?php echo $filter_jenis == 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                                        <option value="dikembalikan" <?php echo $filter_jenis == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                                        <option value="hilang" <?php echo $filter_jenis == 'hilang' ? 'selected' : ''; ?>>Hilang</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_transaksi'] ?? 0; ?></h3>
                                <p>Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['sedang_dipinjam'] ?? 0; ?></h3>
                                <p>Sedang Dipinjam</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['sudah_dikembalikan'] ?? 0; ?></h3>
                                <p>Sudah Dikembalikan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['barang_hilang'] ?? 0; ?></h3>
                                <p>Barang Hilang</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kontrol Scroll -->
                <div class="table-scroll-controls">
                    <div>
                        <button id="scrollLeft" class="scroll-btn" title="Scroll ke kiri">
                            <i class="fas fa-chevron-left"></i> Kiri
                        </button>
                    </div>
                    <div class="scroll-indicator">
                        <span>Gunakan tombol atau geser tabel untuk melihat semua kolom</span>
                    </div>
                    <div>
                        <button id="scrollRight" class="scroll-btn" title="Scroll ke kanan">
                            Kanan <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Laporan Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Data Laporan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <div class="table-wrapper" id="laporanTableContainer">
                                <table class="table table-hover table-fixed" id="laporanTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Peminjaman</th>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Barang</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Status</th>
                                            <th>Teknisi yang Memproses</th>
                                            <th>Kondisi</th>
                                            <th>Denda</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result_laporan)): 
                                            $denda = 0;
                                            if ($row['status'] == 'dikembalikan' && $row['kondisi'] == 'rusak') {
                                                $denda = 50000; // Contoh denda
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $row['kode_peminjaman']; ?></span></td>
                                            <td><?php echo $row['nim']; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                            <td>
                                                <?php 
                                                if($row['tanggal_kembali']) {
                                                    echo date('d/m/Y', strtotime($row['tanggal_kembali']));
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                if($row['status'] == 'dipinjam') $status_class = 'warning';
                                                if($row['status'] == 'dikembalikan') $status_class = 'success';
                                                if($row['status'] == 'hilang') $status_class = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info" title="Teknisi yang memproses peminjaman">
                                                    <i class="fas fa-user-shield me-1"></i>
                                                    <?php echo htmlspecialchars($row['admin_peminjam']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                if($row['kondisi']) {
                                                    $kondisi_class = '';
                                                    if($row['kondisi'] == 'baik') $kondisi_class = 'success';
                                                    if($row['kondisi'] == 'rusak') $kondisi_class = 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $kondisi_class; ?>">
                                                        <?php echo ucfirst($row['kondisi']); ?>
                                                    </span>
                                                    <?php
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if($denda > 0) {
                                                    echo 'Rp ' . number_format($denda, 0, ',', '.');
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php if(mysqli_num_rows($result_laporan) == 0): ?>
                                        <tr>
                                            <td colspan="11" class="text-center">Tidak ada data</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
                            SIPEMBAR &copy; <?php echo date('Y'); ?> - Sistem Peminjaman Barang Universitas Teknologi Informasi
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../js/admin.js"></script>
    
    <script>
    function cetakLaporan() {
        window.open('cetak_laporan.php?' + new URLSearchParams(window.location.search).toString(), '_blank');
    }
    
    function exportExcel() {
        // Simulate Excel export
        alert('Fitur export Excel akan segera tersedia!');
    }
    
    // Fungsi untuk tombol scroll kustom (tanpa inisialisasi DataTable di sini)
    $(document).ready(function() {
        // DataTable akan diinisialisasi oleh admin.js
        // Kita hanya perlu setup tombol scroll
        
        const tableContainer = document.getElementById('laporanTableContainer');
        const scrollLeftBtn = document.getElementById('scrollLeft');
        const scrollRightBtn = document.getElementById('scrollRight');
        
        if (tableContainer && scrollLeftBtn && scrollRightBtn) {
            const scrollAmount = 300;
            
            // Scroll left function
            scrollLeftBtn.addEventListener('click', function() {
                tableContainer.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
                updateScrollButtons();
            });
            
            // Scroll right function
            scrollRightBtn.addEventListener('click', function() {
                tableContainer.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
                updateScrollButtons();
            });
            
            // Update scroll buttons visibility
            function updateScrollButtons() {
                const scrollLeft = tableContainer.scrollLeft;
                const maxScrollLeft = tableContainer.scrollWidth - tableContainer.clientWidth;
                
                if (scrollLeft <= 10) {
                    scrollLeftBtn.style.opacity = '0.5';
                    scrollLeftBtn.disabled = true;
                } else {
                    scrollLeftBtn.style.opacity = '1';
                    scrollLeftBtn.disabled = false;
                }
                
                if (scrollLeft >= maxScrollLeft - 10) {
                    scrollRightBtn.style.opacity = '0.5';
                    scrollRightBtn.disabled = true;
                } else {
                    scrollRightBtn.style.opacity = '1';
                    scrollRightBtn.disabled = false;
                }
            }
            
            // Initialize scroll buttons
            updateScrollButtons();
            
            // Listen for scroll events
            tableContainer.addEventListener('scroll', updateScrollButtons);
            
            // Handle window resize
            window.addEventListener('resize', function() {
                setTimeout(updateScrollButtons, 100);
            });
        }
    });
    </script>
</body>
</html>