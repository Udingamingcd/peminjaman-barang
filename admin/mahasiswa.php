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

// Simpan data mahasiswa untuk modal
$mahasiswa_data = [];
while($row = mysqli_fetch_assoc($result)) {
    $mahasiswa_data[$row['id']] = $row;
}

// Ambil statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    (SELECT COUNT(*) FROM peminjaman p JOIN mahasiswa m ON p.mahasiswa_id = m.id WHERE p.status = 'dipinjam') as sedang_meminjam,
    (SELECT COUNT(*) FROM mahasiswa WHERE YEAR(created_at) = YEAR(CURDATE())) as tahun_ini
    FROM mahasiswa";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Query status peminjaman untuk semua mahasiswa sekaligus
$mahasiswa_ids = array_keys($mahasiswa_data);
$status_peminjaman = [];

if (!empty($mahasiswa_ids)) {
    $ids_string = implode(',', $mahasiswa_ids);
    $status_query = "SELECT mahasiswa_id, COUNT(*) as total FROM peminjaman 
                    WHERE mahasiswa_id IN ($ids_string) AND status = 'dipinjam' 
                    GROUP BY mahasiswa_id";
    $status_result = mysqli_query($koneksi, $status_query);
    
    while($row = mysqli_fetch_assoc($status_result)) {
        $status_peminjaman[$row['mahasiswa_id']] = $row['total'] > 0;
    }
}
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
    
    <style>
        /* Container tabel mahasiswa */
        .table-scroll-container {
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 5px;
            position: relative;
            border-radius: 0.375rem;
            background: white;
        }

        #mahasiswaTable {
            min-width: 900px;
            margin-bottom: 0;
        }

        .scroll-btn {
            transition: all 0.3s ease;
            padding: 0.25rem 0.5rem;
        }

        .scroll-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .scroll-btn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Custom scrollbar untuk tabel */
        .table-scroll-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsif untuk mobile */
        @media (max-width: 768px) {
            #mahasiswaTable {
                min-width: 1000px;
            }
            
            .table-controls {
                margin-top: 10px;
                justify-content: flex-end;
            }
            
            .scroll-btn {
                padding: 0.375rem 0.75rem;
            }
        }

        /* Animasi untuk baris tabel */
        .table-hover tbody tr {
            transition: all 0.2s ease;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
            transform: translateX(2px);
        }

        /* Avatar styling */
        .avatar-sm {
            width: 32px;
            height: 32px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .avatar-lg {
            width: 80px;
            height: 80px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 32px;
            margin: 0 auto;
        }

        /* Table header styling */
        .table-scroll-header {
            border-bottom: 2px solid #dee2e6;
        }

        /* Status badge styling */
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
        
        /* Perbaikan untuk modal agar tidak glitch */
        .modal {
            -webkit-overflow-scrolling: touch;
            overflow-y: auto;
        }
        
        .modal-open {
            overflow: hidden;
        }
        
        /* Animasi fade in untuk modal */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal.fade .modal-dialog {
            animation: modalFadeIn 0.3s ease-out;
        }
        
        /* Loading spinner untuk modal */
        .modal-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }
        
        .modal-loading .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Tombol aksi di tabel */
        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
        }
        
        /* Fix untuk modal backdrop */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-backdrop.show {
            opacity: 0.5;
        }
        
        /* Mencegah scroll body saat modal terbuka */
        body.modal-open {
            padding-right: 0 !important;
            overflow: hidden;
        }
        
        /* Modal content styling */
        .modal-content {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }
        
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        
        .modal-footer {
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        
        /* Table inside modal */
        .modal-body table.table-sm tr th {
            width: 35%;
        }
        
        .modal-body table.table-sm tr td {
            width: 65%;
        }
        
        /* Input styling dalam modal */
        .modal-body .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        /* Error state untuk form */
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
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
                
                <!-- Data Table dengan Scroll Horizontal -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Mahasiswa</h5>
                        <div class="table-controls">
                            <!-- Tombol Scroll Horizontal -->
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-primary scroll-btn" id="mahasiswaScrollLeft">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary scroll-btn" id="mahasiswaScrollRight">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Container untuk scroll horizontal -->
                        <div class="table-scroll-container" id="mahasiswaTableContainer">
                            <table id="mahasiswaTable" class="table table-hover">
                                <thead class="table-scroll-header">
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="120">NIM</th>
                                        <th width="200">Nama</th>
                                        <th width="100">Angkatan</th>
                                        <th width="250">Kontak</th>
                                        <th width="150">Status</th>
                                        <th width="200">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = $offset + 1;
                                    foreach ($mahasiswa_data as $id => $row): 
                                        $is_meminjam = isset($status_peminjaman[$id]) ? $status_peminjaman[$id] : false;
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
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['nama'] ?? ''); ?></small>
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
                                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo $row['alamat'] ? substr($row['alamat'], 0, 30) . '...' : ''; ?>
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
                                                <button type="button" class="btn btn-sm btn-outline-primary view-btn" 
                                                        data-id="<?php echo $id; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning edit-btn"
                                                        data-id="<?php echo $id; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?delete=<?php echo $id; ?>" 
                                                   class="btn btn-sm btn-outline-danger delete-btn"
                                                   onclick="return confirmDelete(this, '<?php echo htmlspecialchars(addslashes($row['nama'])); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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
    <div class="modal fade" id="addMahasiswaModal" tabindex="-1" data-bs-backdrop="static">
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
                                   placeholder="Masukkan NIM mahasiswa" maxlength="20">
                            <div class="invalid-feedback">NIM wajib diisi (minimal 8 karakter)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" required 
                                   placeholder="Masukkan nama lengkap" maxlength="100">
                            <div class="invalid-feedback">Nama wajib diisi</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="angkatan" required 
                                       min="2000" max="2030" value="<?php echo date('Y'); ?>">
                                <div class="invalid-feedback">Angkatan harus antara 2000-2030</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="text" class="form-control" name="no_hp" 
                                       placeholder="08xxxxxxxxxx" maxlength="15">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="mahasiswa@email.com" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3" 
                                      placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_mahasiswa" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Tambah Mahasiswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content akan diisi oleh JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="editForm">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nim" id="editNim" required>
                            <div class="invalid-feedback">NIM wajib diisi (minimal 8 karakter)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="editNama" required>
                            <div class="invalid-feedback">Nama wajib diisi</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="angkatan" id="editAngkatan" 
                                   min="2000" max="2030" required>
                            <div class="invalid-feedback">Angkatan harus antara 2000-2030</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control" name="no_hp" id="editNoHp">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" id="editAlamat" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_mahasiswa" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                        </button>
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
    
    <script>
        // Simpan data mahasiswa untuk modal
        var mahasiswaData = <?php echo json_encode($mahasiswa_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        
        // Fungsi untuk menginisialisasi scroll horizontal
        function initializeMahasiswaScroll() {
            const tableContainer = document.getElementById('mahasiswaTableContainer');
            const scrollLeftBtn = document.getElementById('mahasiswaScrollLeft');
            const scrollRightBtn = document.getElementById('mahasiswaScrollRight');
            
            if (tableContainer && scrollLeftBtn && scrollRightBtn) {
                const scrollAmount = 300;
                
                // Scroll kiri
                scrollLeftBtn.addEventListener('click', function() {
                    tableContainer.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                    updateScrollButtons();
                });
                
                // Scroll kanan
                scrollRightBtn.addEventListener('click', function() {
                    tableContainer.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                    updateScrollButtons();
                });
                
                // Update status tombol scroll
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
                
                // Inisialisasi tombol
                updateScrollButtons();
                
                // Update tombol saat scroll
                tableContainer.addEventListener('scroll', updateScrollButtons);
                
                // Update tombol saat resize
                window.addEventListener('resize', function() {
                    setTimeout(updateScrollButtons, 100);
                });
                
                // Support touch untuk mobile
                let touchStartX = 0;
                let touchEndX = 0;
                
                tableContainer.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, {passive: true});
                
                tableContainer.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                }, {passive: true});
                
                function handleSwipe() {
                    const swipeThreshold = 50;
                    const diff = touchStartX - touchEndX;
                    
                    if (Math.abs(diff) > swipeThreshold) {
                        if (diff > 0) {
                            // Swipe kiri
                            tableContainer.scrollBy({
                                left: scrollAmount,
                                behavior: 'smooth'
                            });
                        } else {
                            // Swipe kanan
                            tableContainer.scrollBy({
                                left: -scrollAmount,
                                behavior: 'smooth'
                            });
                        }
                        updateScrollButtons();
                    }
                }
                
                // Navigasi keyboard
                document.addEventListener('keydown', function(e) {
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                    
                    if (e.key === 'ArrowLeft') {
                        scrollLeftBtn.click();
                        e.preventDefault();
                    } else if (e.key === 'ArrowRight') {
                        scrollRightBtn.click();
                        e.preventDefault();
                    }
                });
            }
        }
        
        // Fungsi untuk menampilkan modal detail
        function showViewModal(mahasiswaId) {
            const data = mahasiswaData[mahasiswaId];
            if (!data) {
                alert('Data mahasiswa tidak ditemukan');
                return;
            }
            
            // Format tanggal
            const createdAt = new Date(data.created_at);
            const formattedDate = createdAt.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const avatarLetter = data.nama.charAt(0).toUpperCase();
            
            // Buat konten modal
            const modalContent = `
                <div class="row">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <div class="avatar-lg mb-3">
                            ${avatarLetter}
                        </div>
                        <h5 class="text-primary">${escapeHtml(data.nama)}</h5>
                        <p class="text-muted">${data.nim}</p>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%" class="text-muted">NIM</th>
                                    <td class="fw-bold">${data.nim}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Nama Lengkap</th>
                                    <td>${escapeHtml(data.nama)}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Angkatan</th>
                                    <td><span class="badge bg-info">${data.angkatan}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">No. HP</th>
                                    <td>${data.no_hp || '<span class="text-muted">-</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Email</th>
                                    <td>${escapeHtml(data.email) || '<span class="text-muted">-</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Alamat</th>
                                    <td>${escapeHtml(data.alamat) || '<span class="text-muted">-</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Tanggal Daftar</th>
                                    <td>${formattedDate}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Terakhir Diupdate</th>
                                    <td>${data.updated_at ? new Date(data.updated_at).toLocaleDateString('id-ID') : '-'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            
            // Tampilkan modal
            const viewModalBody = document.getElementById('viewModalBody');
            if (viewModalBody) {
                viewModalBody.innerHTML = modalContent;
                const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
                viewModal.show();
            }
        }
        
        // Fungsi untuk menampilkan modal edit
        function showEditModal(mahasiswaId) {
            const data = mahasiswaData[mahasiswaId];
            if (!data) {
                alert('Data mahasiswa tidak ditemukan');
                return;
            }
            
            // Isi form dengan data
            document.getElementById('editId').value = data.id;
            document.getElementById('editNim').value = data.nim;
            document.getElementById('editNama').value = data.nama;
            document.getElementById('editAngkatan').value = data.angkatan;
            document.getElementById('editNoHp').value = data.no_hp || '';
            document.getElementById('editEmail').value = data.email || '';
            document.getElementById('editAlamat').value = data.alamat || '';
            
            // Reset validasi
            const form = document.getElementById('editForm');
            const inputs = form.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
            });
            
            // Tampilkan modal
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
            
            // Fokus ke input pertama
            setTimeout(() => {
                document.getElementById('editNim').focus();
            }, 300);
        }
        
        // Fungsi untuk konfirmasi hapus dengan sweet alert
        function confirmDelete(element, nama) {
            if (!confirm(`Apakah Anda yakin ingin menghapus mahasiswa "${nama}"?`)) {
                return false;
            }
            
            // Tampilkan loading
            const originalText = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            element.disabled = true;
            
            return true;
        }
        
        // Fungsi untuk escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // Fungsi untuk validasi form
        function validateForm(form) {
            let isValid = true;
            const inputs = form.querySelectorAll('[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                    
                    // Validasi khusus
                    if (input.name === 'nim' && input.value.length < 8) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    if (input.name === 'angkatan') {
                        const year = parseInt(input.value);
                        if (year < 2000 || year > 2030) {
                            input.classList.add('is-invalid');
                            isValid = false;
                        }
                    }
                }
            });
            
            return isValid;
        }
        
        // Inisialisasi saat halaman dimuat
        $(document).ready(function() {
            // Inisialisasi DataTable
            const dataTable = $('#mahasiswaTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
            
            // Inisialisasi scroll
            initializeMahasiswaScroll();
            
            // Fitur pencarian
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#mahasiswaTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Filter berdasarkan angkatan
            $('#filterAngkatan').on('change', function() {
                const value = $(this).val().toLowerCase();
                $('#mahasiswaTable tbody tr').filter(function() {
                    $(this).toggle($(this).find('td:eq(3)').text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Filter berdasarkan status
            $('#filterStatus').on('change', function() {
                const value = $(this).val();
                $('#mahasiswaTable tbody tr').filter(function() {
                    if (!value) {
                        $(this).show();
                        return;
                    }
                    
                    const statusText = $(this).find('td:eq(5)').text().toLowerCase();
                    if (value === 'meminjam') {
                        $(this).toggle(statusText.includes('sedang meminjam'));
                    } else if (value === 'tidak_meminjam') {
                        $(this).toggle(statusText.includes('tidak meminjam'));
                    }
                });
            });
            
            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#searchInput').val('');
                $('#filterAngkatan').val('');
                $('#filterStatus').val('');
                $('#mahasiswaTable tbody tr').show();
            });
            
            // Validasi form tambah
            $('#addMahasiswaForm').on('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Validasi form edit
            $('#editForm').on('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Event listener untuk tombol view
            $(document).on('click', '.view-btn', function() {
                const mahasiswaId = $(this).data('id');
                showViewModal(mahasiswaId);
            });
            
            // Event listener untuk tombol edit
            $(document).on('click', '.edit-btn', function() {
                const mahasiswaId = $(this).data('id');
                showEditModal(mahasiswaId);
            });
            
            // Event listener untuk modal hidden
            $('#viewModal, #editModal, #addMahasiswaModal').on('hidden.bs.modal', function() {
                // Reset form edit
                if (this.id === 'editModal') {
                    const form = document.getElementById('editForm');
                    form.reset();
                    const inputs = form.querySelectorAll('.form-control');
                    inputs.forEach(input => {
                        input.classList.remove('is-invalid');
                    });
                }
            });
            
            // Auto-fokus pada input pencarian
            $('#searchInput').focus();
            
            // Animasi baris tabel
            $('#mahasiswaTable tbody tr').each(function(index) {
                $(this).css('animation-delay', (index * 0.05) + 's');
                $(this).addClass('fade-in');
            });
            
            // Fix untuk modal backdrop
            $(document).on('show.bs.modal', '.modal', function() {
                // Tutup modal lain yang terbuka
                $('.modal.show').modal('hide');
                
                // Atur z-index
                const zIndex = 1040 + (10 * $('.modal:visible').length);
                $(this).css('z-index', zIndex);
                setTimeout(() => {
                    $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
                }, 0);
            });
            
            // Handle modal hidden
            $(document).on('hidden.bs.modal', '.modal', function() {
                if ($('.modal.show').length > 0) {
                    $('body').addClass('modal-open');
                }
            });
            
            // Real-time validation untuk form
            $('#addMahasiswaForm, #editForm').on('input', '.form-control[required]', function() {
                if (this.value.trim()) {
                    $(this).removeClass('is-invalid');
                } else {
                    $(this).addClass('is-invalid');
                }
            });
            
            // Format input angkatan
            $('input[name="angkatan"]').on('blur', function() {
                const year = parseInt(this.value);
                if (year < 2000) this.value = 2000;
                if (year > 2030) this.value = 2030;
            });
        });
    </script>
</body>
</html>