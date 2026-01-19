<?php
// admin/peminjaman.php
session_start();
require_once '../config/koneksi.php';

// Untuk debugging - HAPUS SETELAH PERBAIKAN
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

$message = '';
$message_type = '';

// Fungsi untuk mengecek error pada query
function execute_query($koneksi, $query, $error_message = "") {
    $result = mysqli_query($koneksi, $query);
    if (!$result) {
        throw new Exception(($error_message ? $error_message . ": " : "") . mysqli_error($koneksi));
    }
    return $result;
}

// Proses tambah peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_peminjaman'])) {
    $mahasiswa_id = clean_input($_POST['mahasiswa_id']);
    $barang_id = clean_input($_POST['barang_id']);
    $tanggal_pinjam = clean_input($_POST['tanggal_pinjam']);
    $batas_kembali = clean_input($_POST['batas_kembali']);
    $keterangan = clean_input($_POST['keterangan']);
    
    // Generate kode peminjaman
    $kode_peminjaman = 'PINJ-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Handle file upload foto bukti
    $foto_bukti_pinjam = '';
    if (isset($_FILES['foto_bukti_pinjam']) && $_FILES['foto_bukti_pinjam']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['foto_bukti_pinjam']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('bukti_pinjam_') . '.' . $file_ext;
            $upload_path = '../assets/uploads/bukti_pinjam/' . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_bukti_pinjam']['tmp_name'], $upload_path)) {
                $foto_bukti_pinjam = $new_filename;
            }
        }
    }
    
    // Validasi stok barang
    $check_stok = "SELECT stok, status FROM barang WHERE id = '$barang_id'";
    $result_stok = mysqli_query($koneksi, $check_stok);
    $barang = mysqli_fetch_assoc($result_stok);
    
    if (!$barang) {
        $message = "Barang tidak ditemukan!";
        $message_type = "danger";
    } elseif ($barang['stok'] < 1) {
        $message = "Stok barang tidak tersedia!";
        $message_type = "danger";
    } elseif ($barang['status'] != 'tersedia') {
        $message = "Barang tidak tersedia untuk dipinjam!";
        $message_type = "danger";
    } else {
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);
        
        try {
            // Insert peminjaman
            $insert_query = "INSERT INTO peminjaman 
                            (kode_peminjaman, mahasiswa_id, barang_id, admin_id, tanggal_pinjam, batas_kembali, foto_bukti_pinjam, keterangan) 
                            VALUES ('$kode_peminjaman', '$mahasiswa_id', '$barang_id', '$user_id', '$tanggal_pinjam', '$batas_kembali', '$foto_bukti_pinjam', '$keterangan')";
            
            if (!mysqli_query($koneksi, $insert_query)) {
                throw new Exception("Gagal menambahkan peminjaman: " . mysqli_error($koneksi));
            }
            
            $peminjaman_id = mysqli_insert_id($koneksi);
            
            if (!$peminjaman_id) {
                throw new Exception("Gagal mendapatkan ID peminjaman");
            }
            
            // Update status barang
            $update_barang = "UPDATE barang SET status = 'sedang_dipinjam', stok = stok - 1 WHERE id = '$barang_id'";
            if (!mysqli_query($koneksi, $update_barang)) {
                throw new Exception("Gagal update status barang: " . mysqli_error($koneksi));
            }
            
            // Tambah riwayat status
            $riwayat_query = "INSERT INTO riwayat_status (peminjaman_id, status_sebelum, status_sesudah, admin_id, keterangan) 
                             VALUES ('$peminjaman_id', 'tersedia', 'dipinjam', '$user_id', 'Peminjaman baru: $kode_peminjaman')";
            
            if (!mysqli_query($koneksi, $riwayat_query)) {
                throw new Exception("Gagal menambahkan riwayat status: " . mysqli_error($koneksi));
            }
            
            mysqli_commit($koneksi);
            
            $message = "Peminjaman berhasil ditambahkan! Kode: $kode_peminjaman";
            $message_type = "success";
            
            // Log activity
            if (function_exists('log_activity')) {
                log_activity($user_id, 'add_peminjaman', "Menambahkan peminjaman $kode_peminjaman");
            }
            
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            
            // Hapus file yang sudah diupload jika gagal
            if ($foto_bukti_pinjam && file_exists('../assets/uploads/bukti_pinjam/' . $foto_bukti_pinjam)) {
                unlink('../assets/uploads/bukti_pinjam/' . $foto_bukti_pinjam);
            }
            
            $message = $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Proses pengembalian
if (isset($_GET['kembali'])) {
    $id = clean_input($_GET['kembali']);
    
    // Validasi apakah ID peminjaman ada
    $check_query = "SELECT id, barang_id, kode_peminjaman FROM peminjaman WHERE id = '$id'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $message = "Data peminjaman tidak ditemukan!";
        $message_type = "danger";
    } else {
        $peminjaman_data = mysqli_fetch_assoc($check_result);
        $barang_id = $peminjaman_data['barang_id'];
        $kode_peminjaman = $peminjaman_data['kode_peminjaman'];
        
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);
        
        try {
            // Update status peminjaman
            $update_query = "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = CURDATE() WHERE id = '$id'";
            
            if (!mysqli_query($koneksi, $update_query)) {
                throw new Exception("Gagal update peminjaman: " . mysqli_error($koneksi));
            }
            
            // Update status barang
            $update_barang = "UPDATE barang SET status = 'tersedia', stok = stok + 1 WHERE id = '$barang_id'";
            if (!mysqli_query($koneksi, $update_barang)) {
                throw new Exception("Gagal update barang: " . mysqli_error($koneksi));
            }
            
            // Tambah riwayat status
            $riwayat_query = "INSERT INTO riwayat_status (peminjaman_id, status_sebelum, status_sesudah, admin_id, keterangan) 
                             VALUES ('$id', 'dipinjam', 'dikembalikan', '$user_id', 'Pengembalian barang: $kode_peminjaman')";
            
            if (!mysqli_query($koneksi, $riwayat_query)) {
                throw new Exception("Gagal menambahkan riwayat status: " . mysqli_error($koneksi));
            }
            
            mysqli_commit($koneksi);
            
            $message = "Pengembalian berhasil dicatat!";
            $message_type = "success";
            
            // Log activity
            if (function_exists('log_activity')) {
                log_activity($user_id, 'return_peminjaman', "Mencatat pengembalian peminjaman ID: $id");
            }
            
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $message = $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Proses update status menjadi hilang
if (isset($_GET['hilang'])) {
    $id = clean_input($_GET['hilang']);
    
    // Validasi apakah ID peminjaman ada
    $check_query = "SELECT id, barang_id, kode_peminjaman FROM peminjaman WHERE id = '$id'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $message = "Data peminjaman tidak ditemukan!";
        $message_type = "danger";
    } else {
        $peminjaman_data = mysqli_fetch_assoc($check_result);
        $barang_id = $peminjaman_data['barang_id'];
        $kode_peminjaman = $peminjaman_data['kode_peminjaman'];
        
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);
        
        try {
            $update_query = "UPDATE peminjaman SET status = 'hilang' WHERE id = '$id'";
            
            if (!mysqli_query($koneksi, $update_query)) {
                throw new Exception("Gagal update status peminjaman: " . mysqli_error($koneksi));
            }
            
            // Update status barang
            $update_barang = "UPDATE barang SET status = 'hilang' WHERE id = '$barang_id'";
            if (!mysqli_query($koneksi, $update_barang)) {
                throw new Exception("Gagal update status barang: " . mysqli_error($koneksi));
            }
            
            // Tambah riwayat status
            $riwayat_query = "INSERT INTO riwayat_status (peminjaman_id, status_sebelum, status_sesudah, admin_id, keterangan) 
                             VALUES ('$id', 'dipinjam', 'hilang', '$user_id', 'Barang hilang: $kode_peminjaman')";
            
            if (!mysqli_query($koneksi, $riwayat_query)) {
                throw new Exception("Gagal menambahkan riwayat status: " . mysqli_error($koneksi));
            }
            
            mysqli_commit($koneksi);
            
            $message = "Status peminjaman diubah menjadi hilang!";
            $message_type = "warning";
            
            // Log activity
            if (function_exists('log_activity')) {
                log_activity($user_id, 'lost_peminjaman', "Mengubah status peminjaman ID: $id menjadi hilang");
            }
            
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $message = $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Ambil data peminjaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$filter_tanggal = isset($_GET['tanggal']) ? clean_input($_GET['tanggal']) : '';

// Build query
$where = "WHERE 1=1";
if ($filter_status) {
    $where .= " AND p.status = '$filter_status'";
}
if ($search) {
    $where .= " AND (p.kode_peminjaman LIKE '%$search%' OR m.nama LIKE '%$search%' OR b.nama_barang LIKE '%$search%')";
}
if ($filter_tanggal) {
    $where .= " AND DATE(p.tanggal_pinjam) = '$filter_tanggal'";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM peminjaman p 
                JOIN mahasiswa m ON p.mahasiswa_id = m.id
                JOIN barang b ON p.barang_id = b.id
                $where";
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data peminjaman dengan join (untuk tabel)
$query = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, b.nama_barang, b.kode_barang,
          u.nama_lengkap as admin_name
          FROM peminjaman p 
          JOIN mahasiswa m ON p.mahasiswa_id = m.id
          JOIN barang b ON p.barang_id = b.id
          JOIN users u ON p.admin_id = u.id
          $where 
          ORDER BY p.created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

// Simpan data untuk modal (duplikat result)
$modal_query = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, b.nama_barang, b.kode_barang,
               u.nama_lengkap as admin_name
               FROM peminjaman p 
               JOIN mahasiswa m ON p.mahasiswa_id = m.id
               JOIN barang b ON p.barang_id = b.id
               JOIN users u ON p.admin_id = u.id
               $where 
               ORDER BY p.created_at DESC 
               LIMIT $limit OFFSET $offset";
$modal_result = mysqli_query($koneksi, $modal_query);

// Ambil statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'dipinjam' THEN 1 END) as dipinjam,
    COUNT(CASE WHEN status = 'dikembalikan' THEN 1 END) as dikembalikan,
    COUNT(CASE WHEN status = 'hilang' THEN 1 END) as hilang,
    COUNT(CASE WHEN status = 'terlambat' THEN 1 END) as terlambat
    FROM peminjaman";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Ambil data mahasiswa dan barang untuk form
$mahasiswa_query = "SELECT id, nim, nama FROM mahasiswa ORDER BY nama";
$mahasiswa_result = mysqli_query($koneksi, $mahasiswa_query);

$barang_query = "SELECT id, kode_barang, nama_barang FROM barang WHERE status = 'tersedia' AND stok > 0 ORDER BY nama_barang";
$barang_result = mysqli_query($koneksi, $barang_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Barang - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <style>
        .modal {
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal.show {
            display: block !important;
            opacity: 1 !important;
        }
        
        .modal-backdrop {
            transition: opacity 0.15s linear;
        }
        
        .modal.fade .modal-dialog {
            transform: translate(0, 0);
            transition: transform 0.3s ease-out;
        }
        
        .modal.show .modal-dialog {
            transform: none;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-dot {
            position: absolute;
            left: -25px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
        }
        .overdue {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
        }
        .soon-due {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        
        /* Fix untuk select2 dalam modal */
        .select2-container--bootstrap-5 .select2-dropdown {
            z-index: 1060 !important;
        }
        
        /* Smooth modal animation */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            transform: translate(0, -50px);
            opacity: 0;
        }
        
        .modal.show .modal-dialog {
            transform: translate(0, 0);
            opacity: 1;
        }
        
        .table-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }
        .table-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="topbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h4 class="mb-0">
                            <i class="fas fa-exchange-alt me-2"></i>Peminjaman Barang
                        </h4>
                        <small class="text-muted">Kelola transaksi peminjaman</small>
                    </div>
                    <div class="topbar-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeminjamanModal">
                            <i class="fas fa-plus me-1"></i>Tambah Peminjaman
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p>Total Peminjaman</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['dipinjam']; ?></h3>
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
                                <h3><?php echo $stats['dikembalikan']; ?></h3>
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
                                <h3><?php echo $stats['hilang'] + $stats['terlambat']; ?></h3>
                                <p>Masalah</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari kode/nama mahasiswa/barang..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="dipinjam" <?php echo $filter_status == 'dipinjam' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                                    <option value="dikembalikan" <?php echo $filter_status == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                                    <option value="hilang" <?php echo $filter_status == 'hilang' ? 'selected' : ''; ?>>Hilang</option>
                                    <option value="terlambat" <?php echo $filter_status == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control datepicker-filter" name="tanggal" 
                                       placeholder="Pilih tanggal" 
                                       value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Peminjaman Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Peminjaman</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="peminjamanTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Mahasiswa</th>
                                        <th>Barang</th>
                                        <th>Tanggal</th>
                                        <th>Batas Kembali</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while($row = mysqli_fetch_assoc($result)): 
                                            // Cek apakah terlambat
                                            $is_overdue = false;
                                            $is_soon_due = false;
                                            if ($row['status'] == 'dipinjam' && $row['batas_kembali']) {
                                                $today = new DateTime();
                                                $due_date = new DateTime($row['batas_kembali']);
                                                $interval = $today->diff($due_date);
                                                
                                                if ($today > $due_date) {
                                                    $is_overdue = true;
                                                } elseif ($interval->days <= 2 && $interval->invert == 0) {
                                                    $is_soon_due = true;
                                                }
                                            }
                                    ?>
                                    <tr class="<?php echo $is_overdue ? 'table-danger' : ($is_soon_due ? 'table-warning' : ''); ?>">
                                        <td>
                                            <span class="fw-bold"><?php echo $row['kode_peminjaman']; ?></span>
                                            <br>
                                            <small class="text-muted">Oleh: <?php echo $row['admin_name']; ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></strong>
                                            <br>
                                            <small class="text-muted">NIM: <?php echo $row['nim']; ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['nama_barang']); ?></strong>
                                            <br>
                                            <small class="text-muted">Kode: <?php echo $row['kode_barang']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?>
                                            <?php if ($row['tanggal_kembali']): ?>
                                            <br>
                                            <small class="text-success">
                                                Kembali: <?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['batas_kembali'])); ?>
                                            <?php if ($is_overdue): ?>
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Terlambat
                                            </small>
                                            <?php elseif ($is_soon_due): ?>
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-clock"></i> Segera jatuh tempo
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            if($row['status'] == 'dipinjam') $status_class = 'warning';
                                            if($row['status'] == 'dikembalikan') $status_class = 'success';
                                            if($row['status'] == 'hilang') $status_class = 'danger';
                                            if($row['status'] == 'terlambat') $status_class = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-view-detail"
                                                        data-id="<?php echo $row['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($row['status'] == 'dipinjam'): ?>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="?kembali=<?php echo $row['id']; ?>" 
                                                               onclick="return confirm('Apakah barang sudah dikembalikan?')">
                                                                <i class="fas fa-check text-success me-2"></i>Kembalikan
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-warning btn-perpanjang" href="#" 
                                                               data-id="<?php echo $row['id']; ?>">
                                                                <i class="fas fa-calendar-plus me-2"></i>Perpanjang
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="?hilang=<?php echo $row['id']; ?>" 
                                                               onclick="return confirm('Apakah barang benar-benar hilang?')">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>Barang Hilang
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; 
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">Tidak ada data peminjaman</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter_tanggal ? '&tanggal=' . $filter_tanggal : ''; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter_tanggal ? '&tanggal=' . $filter_tanggal : ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter_tanggal ? '&tanggal=' . $filter_tanggal : ''; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
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
    
    <!-- Add Peminjaman Modal -->
    <div class="modal fade" id="addPeminjamanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data" id="addPeminjamanForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Peminjaman Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mahasiswa <span class="text-danger">*</span></label>
                                <select class="form-select select2-modal" name="mahasiswa_id" id="mahasiswaSelect" required>
                                    <option value="">Pilih Mahasiswa</option>
                                    <?php 
                                    if ($mahasiswa_result && mysqli_num_rows($mahasiswa_result) > 0) {
                                        mysqli_data_seek($mahasiswa_result, 0);
                                        while($mahasiswa = mysqli_fetch_assoc($mahasiswa_result)): ?>
                                    <option value="<?php echo $mahasiswa['id']; ?>">
                                        <?php echo $mahasiswa['nim']; ?> - <?php echo htmlspecialchars($mahasiswa['nama']); ?>
                                    </option>
                                    <?php endwhile;
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barang <span class="text-danger">*</span></label>
                                <select class="form-select select2-modal" name="barang_id" id="barangSelect" required>
                                    <option value="">Pilih Barang</option>
                                    <?php 
                                    if ($barang_result && mysqli_num_rows($barang_result) > 0) {
                                        mysqli_data_seek($barang_result, 0);
                                        while($barang = mysqli_fetch_assoc($barang_result)): ?>
                                    <option value="<?php echo $barang['id']; ?>">
                                        <?php echo $barang['kode_barang']; ?> - <?php echo htmlspecialchars($barang['nama_barang']); ?>
                                    </option>
                                    <?php endwhile;
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_pinjam" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Kembali <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="batas_kembali" 
                                       value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Bukti Peminjaman <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="foto_bukti_pinjam" accept="image/*" required>
                            <small class="text-muted">Foto bukti serah terima barang (jpg, jpeg, png)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3" 
                                      placeholder="Catatan tambahan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_peminjaman" class="btn btn-primary">Simpan Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modals for detail and perpanjang (generated dynamically) -->
    <?php 
    if ($modal_result && mysqli_num_rows($modal_result) > 0) {
        mysqli_data_seek($modal_result, 0);
        while($row = mysqli_fetch_assoc($modal_result)):
            $status_class = '';
            if($row['status'] == 'dipinjam') $status_class = 'warning';
            if($row['status'] == 'dikembalikan') $status_class = 'success';
            if($row['status'] == 'hilang') $status_class = 'danger';
            if($row['status'] == 'terlambat') $status_class = 'danger';
    ?>
    
    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informasi Peminjaman</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Kode Peminjaman</th>
                                    <td><?php echo $row['kode_peminjaman']; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Batas Kembali</th>
                                    <td><?php echo date('d/m/Y', strtotime($row['batas_kembali'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Admin</th>
                                    <td><?php echo $row['admin_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Peminjam</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nama</th>
                                    <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td><?php echo $row['nim']; ?></td>
                                </tr>
                            </table>
                            
                            <h6 class="mt-3">Barang</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nama Barang</th>
                                    <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                </tr>
                                <tr>
                                    <th>Kode Barang</th>
                                    <td><?php echo $row['kode_barang']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($row['foto_bukti_pinjam']): ?>
                    <div class="mt-3">
                        <h6>Bukti Peminjaman</h6>
                        <img src="../assets/uploads/bukti_pinjam/<?php echo $row['foto_bukti_pinjam']; ?>" 
                             class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($row['keterangan']): ?>
                    <div class="mt-3">
                        <h6>Keterangan</h6>
                        <p><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Riwayat Status -->
                    <div class="mt-3">
                        <h6>Riwayat Status</h6>
                        <div class="timeline">
                            <?php
                            $riwayat_query = "SELECT * FROM riwayat_status 
                                             WHERE peminjaman_id = '{$row['id']}' 
                                             ORDER BY created_at DESC";
                            $riwayat_result = mysqli_query($koneksi, $riwayat_query);
                            if ($riwayat_result && mysqli_num_rows($riwayat_result) > 0) {
                                while($riwayat = mysqli_fetch_assoc($riwayat_result)):
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <strong><?php echo ucfirst($riwayat['status_sesudah']); ?></strong>
                                    <small class="text-muted d-block">
                                        Dari: <?php echo $riwayat['status_sebelum'] ?: '-'; ?>
                                    </small>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?>
                                        - <?php echo $riwayat['keterangan']; ?>
                                    </small>
                                </div>
                            </div>
                            <?php endwhile;
                            } else {
                                echo '<p class="text-muted">Belum ada riwayat status</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" onclick="printDetail(<?php echo $row['id']; ?>)">
                        <i class="fas fa-print me-2"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($row['status'] == 'dipinjam'): ?>
    <!-- Perpanjang Modal -->
    <div class="modal fade" id="perpanjangModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="perpanjang_peminjaman.php">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Perpanjang Peminjaman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Batas Kembali Saat Ini</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('d/m/Y', strtotime($row['batas_kembali'])); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Batas Kembali Baru</label>
                            <input type="date" class="form-control" name="batas_kembali_baru" 
                                   min="<?php echo date('Y-m-d', strtotime($row['batas_kembali'])); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan Perpanjangan</label>
                            <textarea class="form-control" name="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perpanjangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endwhile;
    } ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Datepicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Initialize datepicker for filter
        $(document).ready(function() {
            // Datepicker for filter
            $('.datepicker-filter').flatpickr({
                dateFormat: 'Y-m-d',
                allowInput: true
            });
            
            // Initialize Select2 for modal selects
            function initSelect2ForModal() {
                $('.select2-modal').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addPeminjamanModal'),
                    width: '100%'
                });
            }
            
            // Initialize Select2 when modal is shown
            $('#addPeminjamanModal').on('show.bs.modal', function() {
                setTimeout(initSelect2ForModal, 100);
            });
            
            // Close Select2 dropdown when modal is hidden
            $('#addPeminjamanModal').on('hide.bs.modal', function() {
                $('.select2-modal').select2('close');
            });
            
            // Handle detail modal button clicks
            $('.btn-view-detail').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var modal = $('#detailModal' + id);
                
                // Close any open modals first
                $('.modal').modal('hide');
                
                // Show the specific modal after a short delay
                setTimeout(function() {
                    modal.modal('show');
                }, 150);
            });
            
            // Handle perpanjang modal button clicks
            $('.btn-perpanjang').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var modal = $('#perpanjangModal' + id);
                
                // Close any open modals first
                $('.modal').modal('hide');
                
                // Show the specific modal after a short delay
                setTimeout(function() {
                    modal.modal('show');
                }, 150);
            });
            
            // Prevent modal backdrop issues
            $(document).on('show.bs.modal', '.modal', function() {
                var zIndex = 1040 + (10 * $('.modal:visible').length);
                $(this).css('z-index', zIndex);
                setTimeout(function() {
                    $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1)
                        .addClass('modal-stack');
                }, 0);
            });
            
            // Remove modal backdrop on hide
            $(document).on('hidden.bs.modal', '.modal', function() {
                $('.modal:visible').length && $(document.body).addClass('modal-open');
            });
            
            // Fix for multiple backdrop issue
            $(document).on('hidden.bs.modal', '.modal', function() {
                if($('.modal.show').length > 0) {
                    $('body').addClass('modal-open');
                }
            });
        });
        
        // Form validation for add peminjaman
        $('#addPeminjamanForm').on('submit', function(e) {
            var mahasiswa = $('#mahasiswaSelect').val();
            var barang = $('#barangSelect').val();
            var tanggalPinjam = $('input[name="tanggal_pinjam"]').val();
            var batasKembali = $('input[name="batas_kembali"]').val();
            var foto = $('input[name="foto_bukti_pinjam"]').val();
            
            if (!mahasiswa || !barang || !tanggalPinjam || !batasKembali || !foto) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return false;
            }
            
            if (new Date(batasKembali) <= new Date(tanggalPinjam)) {
                e.preventDefault();
                alert('Batas kembali harus setelah tanggal pinjam!');
                return false;
            }
            
            // Validasi ekstensi file
            var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
            if (!allowedExtensions.exec(foto)) {
                e.preventDefault();
                alert('Hanya file dengan ekstensi .jpg, .jpeg, atau .png yang diperbolehkan!');
                return false;
            }
            
            return true;
        });
        
        // Auto-check overdue
        function checkOverdue() {
            $('.overdue-check').each(function() {
                var dueDate = new Date($(this).data('due-date'));
                var today = new Date();
                
                if (today > dueDate) {
                    $(this).addClass('overdue');
                    $(this).find('.status-text').text('Terlambat');
                } else if ((dueDate - today) / (1000 * 60 * 60 * 24) <= 2) {
                    $(this).addClass('soon-due');
                }
            });
        }
        
        // Check overdue on page load
        checkOverdue();
        
        // Print detail function
        function printDetail(id) {
            var printWindow = window.open('print_peminjaman.php?id=' + id, '_blank');
            printWindow.focus();
        }
        
        // Auto-refresh table every 30 seconds for overdue checking
        setInterval(checkOverdue, 30000);
    </script>
</body>
</html>