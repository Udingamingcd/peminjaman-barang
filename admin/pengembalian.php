<?php
// admin/pengembalian.php
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

// Proses pengembalian
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_return'])) {
    $peminjaman_id = clean_input($_POST['peminjaman_id']);
    $tanggal_kembali = clean_input($_POST['tanggal_kembali']);
    $status_kembali = clean_input($_POST['status_kembali']);
    $keterangan = clean_input($_POST['keterangan']);
    $denda = clean_input($_POST['denda']);
    
    // Handle file upload foto bukti kembali
    $foto_bukti_kembali = '';
    if (isset($_FILES['foto_bukti_kembali']) && $_FILES['foto_bukti_kembali']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['foto_bukti_kembali']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('bukti_kembali_') . '.' . $file_ext;
            $upload_path = '../assets/uploads/bukti_kembali/' . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_bukti_kembali']['tmp_name'], $upload_path)) {
                $foto_bukti_kembali = $new_filename;
            }
        }
    }
    
    // Hitung denda jika terlambat
    if (empty($denda)) {
        $denda = 0;
        
        // Ambil data peminjaman untuk hitung keterlambatan
        $peminjaman_query = "SELECT batas_kembali FROM peminjaman WHERE id = '$peminjaman_id'";
        $peminjaman_result = mysqli_query($koneksi, $peminjaman_query);
        $peminjaman = mysqli_fetch_assoc($peminjaman_result);
        
        $batas_kembali = new DateTime($peminjaman['batas_kembali']);
        $tanggal_kembali_dt = new DateTime($tanggal_kembali);
        
        if ($tanggal_kembali_dt > $batas_kembali) {
            $selisih = $batas_kembali->diff($tanggal_kembali_dt);
            $hari_terlambat = $selisih->days;
            $denda_per_hari = get_setting('late_fee_per_day') ?: 5000;
            $denda = $hari_terlambat * $denda_per_hari;
            
            // Update status menjadi terlambat
            $status_kembali = 'terlambat';
        }
    }
    
    // Update peminjaman
    $update_query = "UPDATE peminjaman SET 
                    status = '$status_kembali', 
                    tanggal_kembali = '$tanggal_kembali', 
                    foto_bukti_kembali = '$foto_bukti_kembali', 
                    keterangan = '$keterangan',
                    denda = '$denda'
                    WHERE id = '$peminjaman_id'";
    
    if (mysqli_query($koneksi, $update_query)) {
        // Update status barang berdasarkan kondisi
        $barang_query = "SELECT barang_id FROM peminjaman WHERE id = '$peminjaman_id'";
        $barang_result = mysqli_query($koneksi, $barang_query);
        $barang = mysqli_fetch_assoc($barang_result);
        
        if ($status_kembali == 'dikembalikan') {
            $update_barang = "UPDATE barang SET status = 'tersedia', stok = stok + 1 WHERE id = '{$barang['barang_id']}'";
        } elseif ($status_kembali == 'rusak') {
            $update_barang = "UPDATE barang SET status = 'rusak' WHERE id = '{$barang['barang_id']}'";
        } elseif ($status_kembali == 'hilang') {
            $update_barang = "UPDATE barang SET status = 'hilang' WHERE id = '{$barang['barang_id']}'";
        } else {
            $update_barang = "UPDATE barang SET status = 'tersedia', stok = stok + 1 WHERE id = '{$barang['barang_id']}'";
        }
        
        mysqli_query($koneksi, $update_barang);
        
        // Tambah riwayat status
        $riwayat_query = "INSERT INTO riwayat_status (peminjaman_id, status_sebelum, status_sesudah, admin_id, keterangan) 
                         VALUES ('$peminjaman_id', 'dipinjam', '$status_kembali', '$user_id', 'Pengembalian barang')";
        mysqli_query($koneksi, $riwayat_query);
        
        $message = "Pengembalian berhasil diproses!";
        $message_type = "success";
        log_activity($user_id, 'process_return', "Memproses pengembalian peminjaman ID: $peminjaman_id");
    } else {
        $message = "Gagal memproses pengembalian: " . mysqli_error($koneksi);
        $message_type = "danger";
    }
}

// Ambil data peminjaman aktif untuk pengembalian
$query = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, b.nama_barang, b.kode_barang,
          DATEDIFF(CURDATE(), p.batas_kembali) as keterlambatan
          FROM peminjaman p 
          JOIN mahasiswa m ON p.mahasiswa_id = m.id
          JOIN barang b ON p.barang_id = b.id
          WHERE p.status = 'dipinjam'
          ORDER BY p.batas_kembali ASC";
$result = mysqli_query($koneksi, $query);

// Ambil statistik
$stats_query = "SELECT 
    COUNT(*) as total_dipinjam,
    COUNT(CASE WHEN DATEDIFF(CURDATE(), batas_kembali) > 0 THEN 1 END) as terlambat,
    COUNT(CASE WHEN DATEDIFF(CURDATE(), batas_kembali) <= 2 AND DATEDIFF(CURDATE(), batas_kembali) >= 0 THEN 1 END) as hampir_jatuh_tempo,
    SUM(CASE WHEN DATEDIFF(CURDATE(), batas_kembali) > 0 THEN DATEDIFF(CURDATE(), batas_kembali) * 5000 ELSE 0 END) as potensi_denda
    FROM peminjaman 
    WHERE status = 'dipinjam'";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Barang - SIPEMBAR</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <style>
        .overdue-card {
            border-left: 4px solid #dc3545;
            animation: pulse 2s infinite;
        }
        .soon-due-card {
            border-left: 4px solid #ffc107;
        }
        .normal-card {
            border-left: 4px solid #28a745;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        .denda-badge {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
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
                            <i class="fas fa-undo me-2"></i>Pengembalian Barang
                        </h4>
                        <small class="text-muted">Proses pengembalian barang yang dipinjam</small>
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
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_dipinjam']; ?></h3>
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
                                <h3><?php echo $stats['terlambat']; ?></h3>
                                <p>Terlambat</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['hampir_jatuh_tempo']; ?></h3>
                                <p>Hampir Jatuh Tempo</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-card-dark">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                          <div class="stat-info">
                            <h3>Rp <?php echo number_format($stats['potensi_denda'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Potensi Denda</p>
                        </div>
                        </div>
                    </div>
                </div>
                
                <!-- Peminjaman Aktif -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Peminjaman Aktif
                            <span class="badge bg-warning ms-2"><?php echo mysqli_num_rows($result); ?> Data</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Mahasiswa</th>
                                        <th>Barang</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Batas Kembali</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)): 
                                        $is_overdue = $row['keterlambatan'] > 0;
                                        $is_soon_due = $row['keterlambatan'] <= 2 && $row['keterlambatan'] >= 0;
                                        $card_class = $is_overdue ? 'overdue-card' : ($is_soon_due ? 'soon-due-card' : 'normal-card');
                                    ?>
                                    <tr class="<?php echo $is_overdue ? 'table-danger' : ($is_soon_due ? 'table-warning' : ''); ?>">
                                        <td>
                                            <span class="fw-bold"><?php echo $row['kode_peminjaman']; ?></span>
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
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['batas_kembali'])); ?>
                                            <?php if ($is_overdue): ?>
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Terlambat <?php echo $row['keterlambatan']; ?> hari
                                            </small>
                                            <?php elseif ($is_soon_due): ?>
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-clock"></i> Segera jatuh tempo
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">Sedang Dipinjam</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal" data-bs-target="#returnModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-undo me-1"></i>Proses Kembali
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Return Modal -->
                                    <div class="modal fade" id="returnModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form method="POST" action="" enctype="multipart/form-data">
                                                    <input type="hidden" name="peminjaman_id" value="<?php echo $row['id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Proses Pengembalian</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Kode Peminjaman</label>
                                                                <input type="text" class="form-control" 
                                                                       value="<?php echo $row['kode_peminjaman']; ?>" disabled>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" name="tanggal_kembali" 
                                                                       value="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Mahasiswa</label>
                                                                <input type="text" class="form-control" 
                                                                       value="<?php echo htmlspecialchars($row['nama_mahasiswa']); ?> (<?php echo $row['nim']; ?>)" disabled>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Barang</label>
                                                                <input type="text" class="form-control" 
                                                                       value="<?php echo htmlspecialchars($row['nama_barang']); ?> (<?php echo $row['kode_barang']; ?>)" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status Barang <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="status_kembali" required>
                                                                <option value="dikembalikan">Dikembalikan (Baik)</option>
                                                                <option value="rusak">Dikembalikan (Rusak)</option>
                                                                <option value="hilang">Hilang</option>
                                                                <option value="terlambat">Terlambat</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <?php if ($row['keterlambatan'] > 0): 
                                                            $denda_per_hari = get_setting('late_fee_per_day') ?: 5000;
                                                            $total_denda = $row['keterlambatan'] * $denda_per_hari;
                                                        ?>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            Peminjaman terlambat <?php echo $row['keterlambatan']; ?> hari.
                                                            Denda: Rp <?php echo number_format($denda_per_hari); ?> x <?php echo $row['keterlambatan']; ?> = 
                                                            <strong>Rp <?php echo number_format($total_denda); ?></strong>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Denda (Rp)</label>
                                                            <input type="number" class="form-control" name="denda" 
                                                                   value="<?php echo $total_denda; ?>" min="0" step="500">
                                                        </div>
                                                        <?php else: ?>
                                                        <input type="hidden" name="denda" value="0">
                                                        <?php endif; ?>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Foto Bukti Pengembalian <span class="text-danger">*</span></label>
                                                            <input type="file" class="form-control" name="foto_bukti_kembali" accept="image/*" required>
                                                            <small class="text-muted">Foto bukti serah terima barang kembali</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Keterangan</label>
                                                            <textarea class="form-control" name="keterangan" rows="3" 
                                                                      placeholder="Catatan kondisi barang, dll."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="process_return" class="btn btn-success">Proses Pengembalian</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5>Tidak ada peminjaman aktif</h5>
                            <p class="text-muted">Semua barang telah dikembalikan</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Riwayat Pengembalian Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Riwayat Pengembalian Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $riwayat_query = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, b.nama_barang, b.kode_barang
                                          FROM peminjaman p 
                                          JOIN mahasiswa m ON p.mahasiswa_id = m.id
                                          JOIN barang b ON p.barang_id = b.id
                                          WHERE p.status IN ('dikembalikan', 'rusak', 'hilang', 'terlambat')
                                          ORDER BY p.tanggal_kembali DESC 
                                          LIMIT 10";
                        $riwayat_result = mysqli_query($koneksi, $riwayat_query);
                        
                        if (mysqli_num_rows($riwayat_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal Kembali</th>
                                        <th>Kode</th>
                                        <th>Mahasiswa</th>
                                        <th>Barang</th>
                                        <th>Status</th>
                                        <th>Denda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($riwayat = mysqli_fetch_assoc($riwayat_result)): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($riwayat['tanggal_kembali'])); ?></td>
                                        <td>
                                            <small class="text-muted"><?php echo $riwayat['kode_peminjaman']; ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($riwayat['nama_mahasiswa']); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($riwayat['nama_barang']); ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            if($riwayat['status'] == 'dikembalikan') $status_class = 'success';
                                            if($riwayat['status'] == 'rusak') $status_class = 'warning';
                                            if($riwayat['status'] == 'hilang') $status_class = 'danger';
                                            if($riwayat['status'] == 'terlambat') $status_class = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($riwayat['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($riwayat['denda'] > 0): ?>
                                            <span class="denda-badge">
                                                Rp <?php echo number_format($riwayat['denda']); ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted">Belum ada riwayat pengembalian</p>
                        </div>
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
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Auto calculate denda
        $('select[name="status_kembali"]').on('change', function() {
            var status = $(this).val();
            var modal = $(this).closest('.modal');
            
            if (status === 'hilang') {
                // Tampilkan form penggantian barang
                if (!modal.find('#penggantianForm').length) {
                    var penggantianForm = `
                        <div class="alert alert-danger" id="penggantianForm">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Penggantian Barang Hilang</h6>
                            <div class="mb-2">
                                <label class="form-label">Keterangan Kehilangan</label>
                                <textarea class="form-control" name="keterangan_hilang" rows="2" required></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tanggal Penggantian</label>
                                <input type="date" class="form-control" name="tanggal_penggantian" required>
                            </div>
                        </div>
                    `;
                    modal.find('.modal-body').append(penggantianForm);
                }
            } else {
                modal.find('#penggantianForm').remove();
            }
        });
        
        // Form validation
        $('form').on('submit', function(e) {
            var status = $(this).find('select[name="status_kembali"]').val();
            var foto = $(this).find('input[name="foto_bukti_kembali"]').val();
            
            if (!foto) {
                e.preventDefault();
                alert('Foto bukti pengembalian wajib diisi!');
                return false;
            }
            
            if (status === 'hilang') {
                var keterangan = $(this).find('textarea[name="keterangan_hilang"]').val();
                if (!keterangan) {
                    e.preventDefault();
                    alert('Keterangan kehilangan wajib diisi!');
                    return false;
                }
            }
        });
        
        // Auto set denda for overdue returns
        $(document).ready(function() {
            $('.modal').on('show.bs.modal', function() {
                var dendaInput = $(this).find('input[name="denda"]');
                if (dendaInput.length && !dendaInput.val()) {
                    // Calculate denda based on keterlambatan
                    var overdueDays = $(this).find('.alert-warning').text().match(/\d+/);
                    if (overdueDays) {
                        var dendaPerHari = 5000; // Default
                        var totalDenda = overdueDays[0] * dendaPerHari;
                        dendaInput.val(totalDenda);
                    }
                }
            });
        });
        
        // Print return receipt
        function printReturnReceipt(id) {
            window.open('print_pengembalian.php?id=' + id, '_blank');
        }
    </script>
</body>
</html>