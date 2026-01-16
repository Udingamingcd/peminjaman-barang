<?php
// admin/barang.php
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

$message = '';
$message_type = '';

// Proses tambah barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_barang'])) {
    $kode_barang = clean_input($_POST['kode_barang']);
    $nama_barang = clean_input($_POST['nama_barang']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $kategori = clean_input($_POST['kategori']);
    $stok = (int)clean_input($_POST['stok']);
    $lokasi = clean_input($_POST['lokasi']);
    
    // Handle file upload
    $foto_barang = '';
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['foto_barang']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('barang_') . '.' . $file_ext;
            $upload_path = '../assets/uploads/barang/' . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_barang']['tmp_name'], $upload_path)) {
                $foto_barang = $new_filename;
            }
        }
    }
    
    // Validasi kode barang unik
    $check_query = "SELECT id FROM barang WHERE kode_barang = '$kode_barang'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "Kode barang sudah terdaftar!";
        $message_type = "danger";
    } else {
        // Insert barang baru
        $insert_query = "INSERT INTO barang (kode_barang, nama_barang, deskripsi, kategori, stok, lokasi, foto_barang) 
                        VALUES ('$kode_barang', '$nama_barang', '$deskripsi', '$kategori', '$stok', '$lokasi', '$foto_barang')";
        
        if (mysqli_query($koneksi, $insert_query)) {
            $message = "Barang berhasil ditambahkan!";
            $message_type = "success";
            log_activity($user_id, 'add_barang', "Menambahkan barang $kode_barang - $nama_barang");
        } else {
            $message = "Gagal menambahkan barang: " . mysqli_error($koneksi);
            $message_type = "danger";
        }
    }
}

// Proses edit barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang'])) {
    $id = clean_input($_POST['id']);
    $kode_barang = clean_input($_POST['kode_barang']);
    $nama_barang = clean_input($_POST['nama_barang']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $kategori = clean_input($_POST['kategori']);
    $stok = (int)clean_input($_POST['stok']);
    $lokasi = clean_input($_POST['lokasi']);
    $status = clean_input($_POST['status']);
    
    // Handle file upload jika ada
    $foto_barang = clean_input($_POST['foto_lama']);
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['foto_barang']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('barang_') . '.' . $file_ext;
            $upload_path = '../assets/uploads/barang/' . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_barang']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                if ($foto_barang && file_exists('../assets/uploads/barang/' . $foto_barang)) {
                    unlink('../assets/uploads/barang/' . $foto_barang);
                }
                $foto_barang = $new_filename;
            }
        }
    }
    
    // Update barang
    $update_query = "UPDATE barang SET 
                    kode_barang = '$kode_barang', 
                    nama_barang = '$nama_barang', 
                    deskripsi = '$deskripsi', 
                    kategori = '$kategori', 
                    stok = '$stok', 
                    lokasi = '$lokasi', 
                    foto_barang = '$foto_barang',
                    status = '$status' 
                    WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $update_query)) {
        $message = "Barang berhasil diperbarui!";
        $message_type = "success";
        log_activity($user_id, 'edit_barang', "Memperbarui barang $kode_barang - $nama_barang");
    } else {
        $message = "Gagal memperbarui barang: " . mysqli_error($koneksi);
        $message_type = "danger";
    }
}

// Proses hapus barang
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    
    // Cek apakah barang sedang dipinjam
    $check_loan = "SELECT id FROM peminjaman WHERE barang_id = '$id' AND status = 'dipinjam'";
    $result_loan = mysqli_query($koneksi, $check_loan);
    
    if (mysqli_num_rows($result_loan) > 0) {
        $message = "Tidak dapat menghapus barang yang sedang dipinjam!";
        $message_type = "danger";
    } else {
        // Hapus foto jika ada
        $foto_query = "SELECT foto_barang FROM barang WHERE id = '$id'";
        $foto_result = mysqli_query($koneksi, $foto_query);
        $foto_data = mysqli_fetch_assoc($foto_result);
        
        if ($foto_data['foto_barang'] && file_exists('../assets/uploads/barang/' . $foto_data['foto_barang'])) {
            unlink('../assets/uploads/barang/' . $foto_data['foto_barang']);
        }
        
        $delete_query = "DELETE FROM barang WHERE id = '$id'";
        
        if (mysqli_query($koneksi, $delete_query)) {
            $message = "Barang berhasil dihapus!";
            $message_type = "success";
            log_activity($user_id, 'delete_barang', "Menghapus barang dengan ID: $id");
        } else {
            $message = "Gagal menghapus barang: " . mysqli_error($koneksi);
            $message_type = "danger";
        }
    }
}

// Ambil data barang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter
$filter_kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build query
$where = "WHERE 1=1";
if ($filter_kategori) {
    $where .= " AND kategori = '$filter_kategori'";
}
if ($filter_status) {
    $where .= " AND status = '$filter_status'";
}
if ($search) {
    $where .= " AND (kode_barang LIKE '%$search%' OR nama_barang LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM barang $where";
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data barang
$query = "SELECT * FROM barang $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

// Ambil statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(stok) as total_stok,
    COUNT(CASE WHEN status = 'tersedia' THEN 1 END) as tersedia,
    COUNT(CASE WHEN status = 'sedang_dipinjam' THEN 1 END) as dipinjam,
    COUNT(CASE WHEN status = 'hilang' THEN 1 END) as hilang,
    COUNT(DISTINCT kategori) as total_kategori
    FROM barang";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Ambil kategori unik untuk filter
$kategori_query = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
$kategori_result = mysqli_query($koneksi, $kategori_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <style>
        .item-card {
            transition: transform 0.3s ease;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
        }
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .item-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .item-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        .status-tersedia { background: #28a745; color: white; }
        .status-dipinjam { background: #ffc107; color: black; }
        .status-hilang { background: #dc3545; color: white; }
        .status-rusak { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="topbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h4 class="mb-0">
                            <i class="fas fa-box me-2"></i>Data Barang
                        </h4>
                        <small class="text-muted">Kelola inventaris barang</small>
                    </div>
                    <div class="topbar-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBarangModal">
                            <i class="fas fa-plus me-1"></i>Tambah Barang
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
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p>Total Barang</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['tersedia']; ?></h3>
                                <p>Tersedia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['dipinjam']; ?></h3>
                                <p>Sedang Dipinjam</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['hilang']; ?></h3>
                                <p>Barang Hilang</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari barang..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <?php while($kategori = mysqli_fetch_assoc($kategori_result)): ?>
                                        <option value="<?php echo $kategori['kategori']; ?>" 
                                            <?php echo $filter_kategori == $kategori['kategori'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kategori['kategori']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="tersedia" <?php echo $filter_status == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                    <option value="sedang_dipinjam" <?php echo $filter_status == 'sedang_dipinjam' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                                    <option value="hilang" <?php echo $filter_status == 'hilang' ? 'selected' : ''; ?>>Hilang</option>
                                    <option value="rusak" <?php echo $filter_status == 'rusak' ? 'selected' : ''; ?>>Rusak</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                        <?php if ($filter_kategori || $filter_status || $search): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Filter aktif: 
                                <?php if ($search) echo "Pencarian: '$search' "; ?>
                                <?php if ($filter_kategori) echo "Kategori: $filter_kategori "; ?>
                                <?php if ($filter_status) echo "Status: $filter_status "; ?>
                                <a href="barang.php" class="text-danger ms-2">
                                    <i class="fas fa-times"></i> Hapus filter
                                </a>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Data Grid View -->
                <div class="row mb-4">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card item-card h-100">
                            <div class="position-relative">
                                <?php if ($row['foto_barang']): ?>
                                <img src="../assets/uploads/barang/<?php echo $row['foto_barang']; ?>" 
                                     class="item-image" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>">
                                <?php else: ?>
                                <div class="item-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-box fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <span class="item-status status-<?php echo str_replace('_', '-', $row['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['nama_barang']); ?></h5>
                                <p class="card-text text-muted">
                                    <small><?php echo substr($row['deskripsi'], 0, 100); ?>...</small>
                                </p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Kode:</small>
                                        <strong><?php echo $row['kode_barang']; ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Stok:</small>
                                        <strong><?php echo $row['stok']; ?> unit</strong>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted d-block">Kategori:</small>
                                    <span class="badge bg-info"><?php echo $row['kategori'] ?: '-'; ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detail Barang</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <?php if ($row['foto_barang']): ?>
                                            <img src="../assets/uploads/barang/<?php echo $row['foto_barang']; ?>" 
                                                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>">
                                            <?php else: ?>
                                            <div class="bg-light rounded p-5 text-center">
                                                <i class="fas fa-box fa-5x text-muted"></i>
                                                <p class="mt-3 text-muted">Tidak ada gambar</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-7">
                                            <table class="table table-sm">
                                                <tr>
                                                    <th width="30%">Kode Barang</th>
                                                    <td><?php echo $row['kode_barang']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Nama Barang</th>
                                                    <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Kategori</th>
                                                    <td><?php echo $row['kategori'] ?: '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Stok</th>
                                                    <td><?php echo $row['stok']; ?> unit</td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>
                                                        <?php 
                                                        $status_class = '';
                                                        if($row['status'] == 'tersedia') $status_class = 'success';
                                                        if($row['status'] == 'sedang_dipinjam') $status_class = 'warning';
                                                        if($row['status'] == 'hilang') $status_class = 'danger';
                                                        if($row['status'] == 'rusak') $status_class = 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Lokasi</th>
                                                    <td><?php echo $row['lokasi'] ?: '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Deskripsi</th>
                                                    <td><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Tanggal Ditambahkan</th>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <?php if ($row['status'] == 'tersedia'): ?>
                                    <a href="peminjaman.php?action=new&barang_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-exchange-alt me-2"></i>Pinjamkan
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="foto_lama" value="<?php echo $row['foto_barang']; ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Barang</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="kode_barang" 
                                                       value="<?php echo $row['kode_barang']; ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="nama_barang" 
                                                       value="<?php echo htmlspecialchars($row['nama_barang']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kategori</label>
                                                <input type="text" class="form-control" name="kategori" 
                                                       value="<?php echo htmlspecialchars($row['kategori']); ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Stok <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="stok" 
                                                       value="<?php echo $row['stok']; ?>" min="1" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="tersedia" <?php echo $row['status'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                                    <option value="sedang_dipinjam" <?php echo $row['status'] == 'sedang_dipinjam' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                                                    <option value="hilang" <?php echo $row['status'] == 'hilang' ? 'selected' : ''; ?>>Hilang</option>
                                                    <option value="rusak" <?php echo $row['status'] == 'rusak' ? 'selected' : ''; ?>>Rusak</option>
                                                    <option value="dalam_perbaikan" <?php echo $row['status'] == 'dalam_perbaikan' ? 'selected' : ''; ?>>Dalam Perbaikan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Deskripsi</label>
                                            <textarea class="form-control" name="deskripsi" rows="3"><?php echo htmlspecialchars($row['deskripsi']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Lokasi Penyimpanan</label>
                                            <input type="text" class="form-control" name="lokasi" 
                                                   value="<?php echo htmlspecialchars($row['lokasi']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Foto Barang</label>
                                            <?php if ($row['foto_barang']): ?>
                                            <div class="mb-2">
                                                <img src="../assets/uploads/barang/<?php echo $row['foto_barang']; ?>" 
                                                     class="img-thumbnail" style="height: 100px;">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="hapus_foto" id="hapusFoto<?php echo $row['id']; ?>">
                                                    <label class="form-check-label" for="hapusFoto<?php echo $row['id']; ?>">
                                                        Hapus foto saat ini
                                                    </label>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" name="foto_barang" accept="image/*">
                                            <small class="text-muted">Maksimal 2MB. Format: JPG, PNG, GIF</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_barang" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $filter_kategori ? '&kategori=' . $filter_kategori : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_kategori ? '&kategori=' . $filter_kategori : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $filter_kategori ? '&kategori=' . $filter_kategori : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
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
    
    <!-- Add Barang Modal -->
    <div class="modal fade" id="addBarangModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data" id="addBarangForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Barang Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode_barang" required 
                                       placeholder="Contoh: BRG-001">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_barang" required 
                                       placeholder="Masukkan nama barang">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <input type="text" class="form-control" name="kategori" 
                                       placeholder="Contoh: Elektronik, Buku, Alat Tulis">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok" required 
                                       min="1" value="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi detail barang"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi Penyimpanan</label>
                            <input type="text" class="form-control" name="lokasi" 
                                   placeholder="Contoh: Rak A1, Gudang Utara">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" name="foto_barang" accept="image/*">
                            <small class="text-muted">Maksimal 2MB. Format: JPG, PNG, GIF</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_barang" class="btn btn-primary">Tambah Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- QR Code Modal -->
    <div class="modal fade" id="qrcodeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode"></div>
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="printQRCode()">
                            <i class="fas fa-print me-2"></i>Cetak QR Code
                        </button>
                        <button class="btn btn-success" onclick="downloadQRCode()">
                            <i class="fas fa-download me-2"></i>Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    
    <script>
        // Generate QR Code
        function generateQRCode(text) {
            $('#qrcode').empty();
            QRCode.toCanvas(document.getElementById('qrcode'), text, function (error) {
                if (error) console.error(error);
            });
        }
        
        // Print QR Code
        function printQRCode() {
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>QR Code Barang</title>');
            printWindow.document.write('<style>body { text-align: center; padding: 20px; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h3>QR Code Barang</h3>');
            printWindow.document.write(document.getElementById('qrcode').innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
        
        // Download QR Code
        function downloadQRCode() {
            var canvas = document.querySelector('#qrcode canvas');
            var link = document.createElement('a');
            link.download = 'qrcode-barang.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
        
        // Form validation
        $('#addBarangForm').on('submit', function(e) {
            var kode = $('input[name="kode_barang"]').val();
            var nama = $('input[name="nama_barang"]').val();
            var stok = $('input[name="stok"]').val();
            
            if (!kode || !nama || !stok) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return false;
            }
            
            if (stok < 1) {
                e.preventDefault();
                alert('Stok harus minimal 1!');
                return false;
            }
        });
        
        // File size validation
        $('input[type="file"]').on('change', function() {
            var file = this.files[0];
            if (file) {
                var fileSize = file.size / 1024 / 1024; // in MB
                if (fileSize > 2) {
                    alert('Ukuran file maksimal 2MB!');
                    $(this).val('');
                }
            }
        });
    </script>
</body>
</html>