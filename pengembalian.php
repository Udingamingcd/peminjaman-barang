<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$peminjaman_id = $_GET['peminjaman_id'] ?? 0;
$message = '';
$message_type = '';

// Get peminjaman data
if ($peminjaman_id > 0) {
    $query = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, b.nama_barang, b.kode_barang
              FROM peminjaman p
              JOIN mahasiswa m ON p.mahasiswa_id = m.id
              JOIN barang b ON p.barang_id = b.id
              WHERE p.id = $peminjaman_id AND p.status = 'dipinjam'";
    $result = mysqli_query($koneksi, $query);
    $peminjaman = mysqli_fetch_assoc($result);
    
    if (!$peminjaman) {
        $message = 'Peminjaman tidak ditemukan atau sudah dikembalikan!';
        $message_type = 'danger';
    }
}

// Process pengembalian
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_pengembalian'])) {
    $peminjaman_id = $_POST['peminjaman_id'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $admin_id = $_SESSION['user_id'];
    
    // Handle file upload
    $foto_bukti_kembali = '';
    if (isset($_FILES['foto_bukti_kembali']) && $_FILES['foto_bukti_kembali']['error'] == 0) {
        $ext = pathinfo($_FILES['foto_bukti_kembali']['name'], PATHINFO_EXTENSION);
        $filename = 'bukti_kembali_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upload_dir = '../uploads/bukti/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['foto_bukti_kembali']['tmp_name'], $upload_dir . $filename)) {
            $foto_bukti_kembali = $filename;
        }
    }
    
    // Start transaction
    mysqli_begin_transaction($koneksi);
    
    try {
        // Update peminjaman
        $tanggal_kembali = date('Y-m-d');
        $update_peminjaman = "UPDATE peminjaman SET 
                             status = '$status',
                             tanggal_kembali = '$tanggal_kembali',
                             foto_bukti_kembali = '$foto_bukti_kembali',
                             keterangan = '$keterangan'
                             WHERE id = $peminjaman_id";
        mysqli_query($koneksi, $update_peminjaman);
        
        // Update barang status
        $barang_status = ($status == 'hilang') ? 'hilang' : 'tersedia';
        $update_barang = "UPDATE barang b
                         JOIN peminjaman p ON b.id = p.barang_id
                         SET b.status = '$barang_status'
                         WHERE p.id = $peminjaman_id";
        mysqli_query($koneksi, $update_barang);
        
        // Insert riwayat status
        $insert_riwayat = "INSERT INTO riwayat_status (peminjaman_id, status_sebelum, status_sesudah, admin_id, keterangan)
                          VALUES ($peminjaman_id, 'dipinjam', '$status', $admin_id, '$keterangan')";
        mysqli_query($koneksi, $insert_riwayat);
        
        // If barang hilang, create penggantian entry
        if ($status == 'hilang') {
            $insert_penggantian = "INSERT INTO penggantian_barang (peminjaman_id, admin_id, tanggal_penggantian, keterangan, status)
                                  VALUES ($peminjaman_id, $admin_id, '$tanggal_kembali', '$keterangan', 'menunggu')";
            mysqli_query($koneksi, $insert_penggantian);
        }
        
        // Commit transaction
        mysqli_commit($koneksi);
        
        $message = 'Pengembalian berhasil diproses!';
        $message_type = 'success';
        
        // Reset peminjaman data
        $peminjaman = null;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}
?>

<div class="col-lg-10 col-md-9 main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Pengembalian Barang</h3>
            <p class="text-muted mb-0">Proses pengembalian barang yang dipinjam</p>
        </div>
        <div>
            <a href="peminjaman_manage.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($peminjaman): ?>
    <!-- Peminjaman Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Detail Peminjaman</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Kode Peminjaman</th>
                            <td><?= $peminjaman['kode_peminjaman'] ?></td>
                        </tr>
                        <tr>
                            <th>Mahasiswa</th>
                            <td><?= $peminjaman['nama_mahasiswa'] ?> (<?= $peminjaman['nim'] ?>)</td>
                        </tr>
                        <tr>
                            <th>Barang</th>
                            <td><?= $peminjaman['nama_barang'] ?> (<?= $peminjaman['kode_barang'] ?>)</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Tanggal Pinjam</th>
                            <td><?= date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])) ?></td>
                        </tr>
                        <tr>
                            <th>Lama Pinjam</th>
                            <td>
                                <?php 
                                $days = (strtotime(date('Y-m-d')) - strtotime($peminjaman['tanggal_pinjam'])) / (60 * 60 * 24);
                                echo floor($days) . ' hari';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td><?= $peminjaman['keterangan'] ?: '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php if($peminjaman['foto_bukti_pinjam']): ?>
            <div class="mt-3">
                <h6>Foto Bukti Pinjam</h6>
                <img src="../uploads/bukti/<?= $peminjaman['foto_bukti_pinjam'] ?>" 
                     class="img-fluid rounded" style="max-height: 200px;">
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Pengembalian Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Pengembalian</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="peminjaman_id" value="<?= $peminjaman_id ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status Pengembalian</label>
                        <select class="form-control" name="status" required id="statusSelect" onchange="toggleHilangForm()">
                            <option value="dikembalikan">Dikembalikan (Barang Lengkap)</option>
                            <option value="hilang">Hilang/Rusak</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Foto Bukti Kembali</label>
                        <input type="file" class="form-control" name="foto_bukti_kembali" accept="image/*" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" 
                                  placeholder="Catatan pengembalian..."></textarea>
                    </div>
                    
                    <!-- Form tambahan untuk barang hilang -->
                    <div class="col-12 mb-3" id="hilangForm" style="display: none;">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Barang Hilang/Rusak</h6>
                            <p class="mb-2">Jika barang hilang atau rusak, sistem akan:</p>
                            <ul class="mb-0">
                                <li>Mengubah status barang menjadi "hilang"</li>
                                <li>Membuat entri penggantian barang</li>
                                <li>Mahasiswa akan dikenakan sanksi/prosedur penggantian</li>
                            </ul>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kerusakan</label>
                                <select class="form-control" name="jenis_kerusakan" id="jenisKerusakan">
                                    <option value="">Pilih Jenis Kerusakan</option>
                                    <option value="ringan">Kerusakan Ringan</option>
                                    <option value="sedang">Kerusakan Sedang</option>
                                    <option value="berat">Kerusakan Berat</option>
                                    <option value="hilang">Barang Hilang</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimasi Biaya</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="estimasi_biaya" id="estimasiBiaya" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" name="proses_pengembalian" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i> Proses Pengembalian
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function toggleHilangForm() {
        const status = document.getElementById('statusSelect').value;
        const hilangForm = document.getElementById('hilangForm');
        
        if (status === 'hilang') {
            hilangForm.style.display = 'block';
            document.getElementById('jenisKerusakan').required = true;
            document.getElementById('estimasiBiaya').required = true;
        } else {
            hilangForm.style.display = 'none';
            document.getElementById('jenisKerusakan').required = false;
            document.getElementById('estimasiBiaya').required = false;
        }
    }
    </script>
    
    <?php else: ?>
    <!-- No peminjaman selected -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-undo-alt fa-3x text-muted mb-3"></i>
            <h5 class="mb-3">Pilih Peminjaman untuk Pengembalian</h5>
            <p class="text-muted mb-4">Silakan pilih peminjaman yang akan dikembalikan dari halaman Manajemen Peminjaman</p>
            <a href="peminjaman_manage.php" class="btn btn-primary">
                <i class="fas fa-list me-2"></i> Lihat Daftar Peminjaman
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>