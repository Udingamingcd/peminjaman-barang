<?php
// admin/mahasiswa.php
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

// Proses tambah mahasiswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_mahasiswa'])) {
    $nim = clean_input($_POST['nim']);
    $nama = clean_input($_POST['nama']);
    $angkatan = clean_input($_POST['angkatan']);
    $no_hp = clean_input($_POST['no_hp']);
    $email = clean_input($_POST['email']);
    $alamat = clean_input($_POST['alamat']);
    
    // Validasi NIM unik
    $check_query = "SELECT id FROM mahasiswa WHERE nim = '$nim'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "NIM sudah terdaftar!";
        $message_type = "danger";
    } else {
        // Insert mahasiswa baru
        $insert_query = "INSERT INTO mahasiswa (nim, nama, angkatan, no_hp, email, alamat) 
                        VALUES ('$nim', '$nama', '$angkatan', '$no_hp', '$email', '$alamat')";
        
        if (mysqli_query($koneksi, $insert_query)) {
            $message = "Mahasiswa berhasil ditambahkan!";
            $message_type = "success";
            log_activity($user_id, 'add_mahasiswa', "Menambahkan mahasiswa $nim - $nama");
        } else {
            $message = "Gagal menambahkan mahasiswa: " . mysqli_error($koneksi);
            $message_type = "danger";
        }
    }
}

// Proses edit mahasiswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_mahasiswa'])) {
    $id = clean_input($_POST['id']);
    $nim = clean_input($_POST['nim']);
    $nama = clean_input($_POST['nama']);
    $angkatan = clean_input($_POST['angkatan']);
    $no_hp = clean_input($_POST['no_hp']);
    $email = clean_input($_POST['email']);
    $alamat = clean_input($_POST['alamat']);
    
    // Update mahasiswa
    $update_query = "UPDATE mahasiswa SET 
                    nim = '$nim', 
                    nama = '$nama', 
                    angkatan = '$angkatan', 
                    no_hp = '$no_hp', 
                    email = '$email', 
                    alamat = '$alamat' 
                    WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $update_query)) {
        $message = "Mahasiswa berhasil diperbarui!";
        $message_type = "success";
        log_activity($user_id, 'edit_mahasiswa', "Memperbarui data mahasiswa $nim - $nama");
    } else {
        $message = "Gagal memperbarui mahasiswa: " . mysqli_error($koneksi);
        $message_type = "danger";
    }
}

// Proses hapus mahasiswa
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    
    // Cek apakah mahasiswa memiliki peminjaman aktif
    $check_loan = "SELECT id FROM peminjaman WHERE mahasiswa_id = '$id' AND status = 'dipinjam'";
    $result_loan = mysqli_query($koneksi, $check_loan);
    
    if (mysqli_num_rows($result_loan) > 0) {
        $message = "Tidak dapat menghapus mahasiswa yang masih memiliki peminjaman aktif!";
        $message_type = "danger";
    } else {
        $delete_query = "DELETE FROM mahasiswa WHERE id = '$id'";
        
        if (mysqli_query($koneksi, $delete_query)) {
            $message = "Mahasiswa berhasil dihapus!";
            $message_type = "success";
            log_activity($user_id, 'delete_mahasiswa', "Menghapus mahasiswa dengan ID: $id");
        } else {
            $message = "Gagal menghapus mahasiswa: " . mysqli_error($koneksi);
            $message_type = "danger";
        }
    }
}

// Ambil data mahasiswa dengan pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM mahasiswa";
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data mahasiswa
$query = "SELECT * FROM mahasiswa ORDER BY angkatan DESC, nama ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

// Ambil statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    (SELECT COUNT(*) FROM peminjaman p JOIN mahasiswa m ON p.mahasiswa_id = m.id WHERE p.status = 'dipinjam') as sedang_meminjam,
    (SELECT COUNT(*) FROM mahasiswa WHERE YEAR(created_at) = YEAR(CURDATE())) as tahun_ini
    FROM mahasiswa";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
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
                            <i class="fas fa-users me-2"></i>Data Mahasiswa
                        </h4>
                        <small class="text-muted">Kelola data mahasiswa peminjam</small>
                    </div>
                    <div class="topbar-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMahasiswaModal">
                            <i class="fas fa-user-plus me-1"></i>Tambah Mahasiswa
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
                    <div class="col-md-4">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p>Total Mahasiswa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['sedang_meminjam']; ?></h3>
                                <p>Sedang Meminjam</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['tahun_ini']; ?></h3>
                                <p>Tambah Tahun Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Cari mahasiswa...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterAngkatan">
                                    <option value="">Semua Angkatan</option>
                                    <?php
                                    $angkatan_query = "SELECT DISTINCT angkatan FROM mahasiswa ORDER BY angkatan DESC";
                                    $angkatan_result = mysqli_query($koneksi, $angkatan_query);
                                    while($row = mysqli_fetch_assoc($angkatan_result)) {
                                        echo "<option value='{$row['angkatan']}'>{$row['angkatan']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="meminjam">Sedang Meminjam</option>
                                    <option value="tidak_meminjam">Tidak Meminjam</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-secondary w-100" id="resetFilter">
                                    <i class="fas fa-redo me-2"></i>Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Data Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Mahasiswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="mahasiswaTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Angkatan</th>
                                        <th>Kontak</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = $offset + 1;
                                    while($row = mysqli_fetch_assoc($result)): 
                                        // Cek status peminjaman
                                        $status_query = "SELECT COUNT(*) as total FROM peminjaman WHERE mahasiswa_id = '{$row['id']}' AND status = 'dipinjam'";
                                        $status_result = mysqli_query($koneksi, $status_query);
                                        $status_data = mysqli_fetch_assoc($status_result);
                                        $is_meminjam = $status_data['total'] > 0;
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <span class="fw-bold"><?php echo $row['nim']; ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($row['nama']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $row['angkatan']; ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="d-block">
                                                    <i class="fas fa-phone me-2"></i><?php echo $row['no_hp']; ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo substr($row['alamat'], 0, 30) . '...'; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($is_meminjam): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-exchange-alt me-1"></i>Sedang Meminjam
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Tidak Meminjam
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
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
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                            
                                            <!-- View Modal -->
                                            <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Mahasiswa</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-4 text-center">
                                                                    <div class="avatar-lg mb-3">
                                                                        <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                                                    </div>
                                                                    <h5><?php echo htmlspecialchars($row['nama']); ?></h5>
                                                                    <p class="text-muted"><?php echo $row['nim']; ?></p>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <table class="table table-sm">
                                                                        <tr>
                                                                            <th width="30%">NIM</th>
                                                                            <td><?php echo $row['nim']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Nama Lengkap</th>
                                                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Angkatan</th>
                                                                            <td><?php echo $row['angkatan']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>No. HP</th>
                                                                            <td><?php echo $row['no_hp']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Email</th>
                                                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Alamat</th>
                                                                            <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Tanggal Daftar</th>
                                                                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Mahasiswa</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">NIM</label>
                                                                    <input type="text" class="form-control" name="nim" 
                                                                           value="<?php echo $row['nim']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nama Lengkap</label>
                                                                    <input type="text" class="form-control" name="nama" 
                                                                           value="<?php echo htmlspecialchars($row['nama']); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Angkatan</label>
                                                                    <input type="number" class="form-control" name="angkatan" 
                                                                           value="<?php echo $row['angkatan']; ?>" min="2000" max="2030" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">No. HP</label>
                                                                    <input type="text" class="form-control" name="no_hp" 
                                                                           value="<?php echo $row['no_hp']; ?>">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Email</label>
                                                                    <input type="email" class="form-control" name="email" 
                                                                           value="<?php echo htmlspecialchars($row['email']); ?>">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Alamat</label>
                                                                    <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($row['alamat']); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="edit_mahasiswa" class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Menampilkan <?php echo $offset + 1; ?> sampai <?php echo min($offset + $limit, $total_data); ?> dari <?php echo $total_data; ?> data
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
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
    
    <!-- Add Mahasiswa Modal -->
    <div class="modal fade" id="addMahasiswaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="addMahasiswaForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Mahasiswa Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nim" required 
                                   placeholder="Masukkan NIM mahasiswa">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" required 
                                   placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="angkatan" required 
                                       min="2000" max="2030" value="<?php echo date('Y'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="text" class="form-control" name="no_hp" 
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="mahasiswa@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3" 
                                      placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_mahasiswa" class="btn btn-primary">Tambah Mahasiswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="import_mahasiswa.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Format file harus Excel (.xlsx) dengan kolom: NIM, Nama, Angkatan, No_HP, Email, Alamat
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel</label>
                            <input type="file" class="form-control" name="file" accept=".xlsx,.xls" required>
                        </div>
                        <div class="text-center">
                            <a href="template_mahasiswa.xlsx" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-2"></i>Download Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#mahasiswaTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
            
            // Search functionality
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#mahasiswaTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Filter by angkatan
            $('#filterAngkatan').on('change', function() {
                var value = $(this).val().toLowerCase();
                $('#mahasiswaTable tbody tr').filter(function() {
                    $(this).toggle($(this).find('td:eq(3)').text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#searchInput').val('');
                $('#filterAngkatan').val('');
                $('#filterStatus').val('');
                $('#mahasiswaTable tbody tr').show();
            });
            
            // Form validation
            $('#addMahasiswaForm').on('submit', function(e) {
                var nim = $('input[name="nim"]').val();
                var nama = $('input[name="nama"]').val();
                var angkatan = $('input[name="angkatan"]').val();
                
                if (!nim || !nama || !angkatan) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi!');
                    return false;
                }
                
                if (nim.length < 8) {
                    e.preventDefault();
                    alert('NIM harus minimal 8 karakter!');
                    return false;
                }
                
                if (angkatan < 2000 || angkatan > 2030) {
                    e.preventDefault();
                    alert('Angkatan harus antara 2000-2030!');
                    return false;
                }
            });
        });
    </script>
</body>
</html>