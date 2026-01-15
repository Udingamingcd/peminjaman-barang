<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle form actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Tambah Mahasiswa Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_mahasiswa'])) {
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    $check = mysqli_query($koneksi, "SELECT id FROM mahasiswa WHERE nim = '$nim'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = 'NIM sudah terdaftar!';
        $message_type = 'danger';
    } else {
        $query = "INSERT INTO mahasiswa (nim, nama, angkatan, no_hp) 
                  VALUES ('$nim', '$nama', '$angkatan', '$no_hp')";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Mahasiswa berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Edit Mahasiswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_mahasiswa'])) {
    $id = $_POST['id'];
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    // Check if NIM already exists (except current)
    $check = mysqli_query($koneksi, "SELECT id FROM mahasiswa WHERE nim = '$nim' AND id != $id");
    
    if (mysqli_num_rows($check) > 0) {
        $message = 'NIM sudah digunakan oleh mahasiswa lain!';
        $message_type = 'danger';
    } else {
        $query = "UPDATE mahasiswa SET 
                  nim = '$nim',
                  nama = '$nama',
                  angkatan = '$angkatan',
                  no_hp = '$no_hp'
                  WHERE id = $id";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Data mahasiswa berhasil diupdate!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Hapus Mahasiswa
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Cek apakah mahasiswa memiliki peminjaman aktif
    $check = mysqli_query($koneksi, "SELECT id FROM peminjaman WHERE mahasiswa_id = $id AND status = 'dipinjam'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = 'Mahasiswa tidak bisa dihapus karena memiliki peminjaman aktif!';
        $message_type = 'danger';
    } else {
        $query = "DELETE FROM mahasiswa WHERE id = $id";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Mahasiswa berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Get mahasiswa data for edit
$mahasiswa_data = null;
if ($action == 'edit' && $id > 0) {
    $query = "SELECT * FROM mahasiswa WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    $mahasiswa_data = mysqli_fetch_assoc($result);
}

// Get all mahasiswa with filter
$filter_angkatan = $_GET['angkatan'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT m.*, 
          (SELECT COUNT(*) FROM peminjaman p WHERE p.mahasiswa_id = m.id AND p.status = 'dipinjam') as peminjaman_aktif
          FROM mahasiswa m WHERE 1=1";

if ($filter_angkatan) {
    $query .= " AND angkatan = '$filter_angkatan'";
}
if ($search) {
    $query .= " AND (nim LIKE '%$search%' OR nama LIKE '%$search%')";
}
$query .= " ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);

// Get angkatan for filter
$query_angkatan = "SELECT DISTINCT angkatan FROM mahasiswa ORDER BY angkatan DESC";
$result_angkatan = mysqli_query($koneksi, $query_angkatan);
?>

<div class="col-lg-10 col-md-9 main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Data Mahasiswa</h3>
            <p class="text-muted mb-0">Kelola data mahasiswa peminjam</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMahasiswaModal">
                <i class="fas fa-user-plus me-2"></i> Tambah Mahasiswa
            </button>
        </div>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Filter dan Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label>Cari NIM/Nama</label>
                    <input type="text" class="form-control" name="search" value="<?= $search ?>" 
                           placeholder="Cari NIM atau nama...">
                </div>
                <div class="col-md-4">
                    <label>Angkatan</label>
                    <select name="angkatan" class="form-select">
                        <option value="">Semua Angkatan</option>
                        <?php while($angk = mysqli_fetch_assoc($result_angkatan)): ?>
                        <option value="<?= $angk['angkatan'] ?>" <?= $filter_angkatan == $angk['angkatan'] ? 'selected' : '' ?>>
                            <?= $angk['angkatan'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Cari</button>
                    <a href="mahasiswa_manage.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Mahasiswa List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Mahasiswa</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Angkatan</th>
                            <th>No. HP</th>
                            <th>Peminjaman Aktif</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong><?= $row['nim'] ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= $row['nama'] ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $row['angkatan'] ?></span>
                            </td>
                            <td><?= $row['no_hp'] ?: '-' ?></td>
                            <td>
                                <?php if($row['peminjaman_aktif'] > 0): ?>
                                <span class="badge bg-warning"><?= $row['peminjaman_aktif'] ?> aktif</span>
                                <?php else: ?>
                                <span class="badge bg-success">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" data-bs-target="#viewMahasiswaModal"
                                        onclick="viewMahasiswa(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" data-bs-target="#editMahasiswaModal"
                                        onclick="setEditMahasiswa(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Mahasiswa Modal -->
<div class="modal fade" id="addMahasiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mahasiswa Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" class="form-control" name="nim" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Angkatan</label>
                        <select class="form-control" name="angkatan" required>
                            <option value="">Pilih Angkatan</option>
                            <?php for($year = date('Y'); $year >= 2010; $year--): ?>
                            <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" name="no_hp">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_mahasiswa" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Mahasiswa Modal -->
<div class="modal fade" id="editMahasiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" class="form-control" name="nim" id="edit_nim" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Angkatan</label>
                        <select class="form-control" name="angkatan" id="edit_angkatan" required>
                            <option value="">Pilih Angkatan</option>
                            <?php for($year = date('Y'); $year >= 2010; $year--): ?>
                            <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" name="no_hp" id="edit_no_hp">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_mahasiswa" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Mahasiswa Modal -->
<div class="modal fade" id="viewMahasiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                         style="width: 80px; height: 80px; font-size: 32px;" id="view_avatar">
                    </div>
                </div>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">NIM</th>
                        <td id="view_nim"></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td id="view_nama"></td>
                    </tr>
                    <tr>
                        <th>Angkatan</th>
                        <td id="view_angkatan"></td>
                    </tr>
                    <tr>
                        <th>No. HP</th>
                        <td id="view_no_hp"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="view_status"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Daftar</th>
                        <td id="view_created"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function setEditMahasiswa(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nim').value = data.nim;
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_angkatan').value = data.angkatan;
    document.getElementById('edit_no_hp').value = data.no_hp || '';
}

function viewMahasiswa(data) {
    document.getElementById('view_avatar').textContent = data.nama.charAt(0).toUpperCase();
    document.getElementById('view_nim').textContent = data.nim;
    document.getElementById('view_nama').textContent = data.nama;
    document.getElementById('view_angkatan').textContent = data.angkatan;
    document.getElementById('view_no_hp').textContent = data.no_hp || '-';
    document.getElementById('view_created').textContent = new Date(data.created_at).toLocaleDateString('id-ID');
    
    // Status berdasarkan peminjaman aktif
    const statusElement = document.getElementById('view_status');
    if (data.peminjaman_aktif > 0) {
        statusElement.innerHTML = `<span class="badge bg-warning">Memiliki ${data.peminjaman_aktif} peminjaman aktif</span>`;
    } else {
        statusElement.innerHTML = `<span class="badge bg-success">Tidak ada peminjaman aktif</span>`;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>