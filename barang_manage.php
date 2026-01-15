<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle form actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Tambah Barang Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_barang'])) {
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $status = 'tersedia';
    
    // Handle file upload
    $foto_barang = '';
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
        $ext = pathinfo($_FILES['foto_barang']['name'], PATHINFO_EXTENSION);
        $filename = 'barang_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upload_dir = '../uploads/barang/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['foto_barang']['tmp_name'], $upload_dir . $filename)) {
            $foto_barang = $filename;
        }
    }
    
    $check = mysqli_query($koneksi, "SELECT id FROM barang WHERE kode_barang = '$kode_barang'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = 'Kode barang sudah digunakan!';
        $message_type = 'danger';
    } else {
        $query = "INSERT INTO barang (kode_barang, nama_barang, deskripsi, kategori, status, foto_barang) 
                  VALUES ('$kode_barang', '$nama_barang', '$deskripsi', '$kategori', '$status', '$foto_barang')";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Barang berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Edit Barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang'])) {
    $id = $_POST['id'];
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Handle file upload
    $foto_barang = $_POST['old_foto'];
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
        $ext = pathinfo($_FILES['foto_barang']['name'], PATHINFO_EXTENSION);
        $filename = 'barang_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upload_dir = '../uploads/barang/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Delete old photo if exists
        if ($foto_barang && file_exists($upload_dir . $foto_barang)) {
            unlink($upload_dir . $foto_barang);
        }
        
        if (move_uploaded_file($_FILES['foto_barang']['tmp_name'], $upload_dir . $filename)) {
            $foto_barang = $filename;
        }
    }
    
    $query = "UPDATE barang SET 
              kode_barang = '$kode_barang',
              nama_barang = '$nama_barang',
              deskripsi = '$deskripsi',
              kategori = '$kategori',
              status = '$status',
              foto_barang = '$foto_barang'
              WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        $message = 'Data barang berhasil diupdate!';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . mysqli_error($koneksi);
        $message_type = 'danger';
    }
}

// Hapus Barang
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Cek apakah barang sedang dipinjam
    $check = mysqli_query($koneksi, "SELECT id FROM peminjaman WHERE barang_id = $id AND status = 'dipinjam'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = 'Barang tidak bisa dihapus karena sedang dipinjam!';
        $message_type = 'danger';
    } else {
        // Get photo filename
        $result = mysqli_query($koneksi, "SELECT foto_barang FROM barang WHERE id = $id");
        $row = mysqli_fetch_assoc($result);
        
        // Delete photo if exists
        if ($row['foto_barang']) {
            $file_path = '../uploads/barang/' . $row['foto_barang'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $query = "DELETE FROM barang WHERE id = $id";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Barang berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Get barang data for edit
$barang_data = null;
if ($action == 'edit' && $id > 0) {
    $query = "SELECT * FROM barang WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    $barang_data = mysqli_fetch_assoc($result);
}

// Get all barang with filter
$filter_kategori = $_GET['kategori'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT * FROM barang WHERE 1=1";
if ($filter_kategori) {
    $query .= " AND kategori = '$filter_kategori'";
}
if ($filter_status) {
    $query .= " AND status = '$filter_status'";
}
$query .= " ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);

// Get categories for filter
$query_categories = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL ORDER BY kategori";
$result_categories = mysqli_query($koneksi, $query_categories);
?>

<div class="col-lg-10 col-md-9 main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Kelola Barang</h3>
            <p class="text-muted mb-0">Kelola inventaris barang peminjaman</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBarangModal">
                <i class="fas fa-plus me-2"></i> Tambah Barang
            </button>
        </div>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label>Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php while($cat = mysqli_fetch_assoc($result_categories)): ?>
                        <option value="<?= $cat['kategori'] ?>" <?= $filter_kategori == $cat['kategori'] ? 'selected' : '' ?>>
                            <?= $cat['kategori'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="tersedia" <?= $filter_status == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="sedang_dipinjam" <?= $filter_status == 'sedang_dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                        <option value="dikembalikan" <?= $filter_status == 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                        <option value="hilang" <?= $filter_status == 'hilang' ? 'selected' : '' ?>>Hilang</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="barang_manage.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Barang List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Barang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Tanggal Ditambahkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong><?= $row['kode_barang'] ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($row['foto_barang']): ?>
                                    <img src="../uploads/barang/<?= $row['foto_barang'] ?>" 
                                         class="rounded me-3" width="40" height="40" 
                                         style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= $row['nama_barang'] ?></strong>
                                        <?php if($row['deskripsi']): ?>
                                        <div class="text-muted small"><?= substr($row['deskripsi'], 0, 50) ?>...</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= $row['kategori'] ?: '-' ?></td>
                            <td>
                                <?php 
                                $status_badge = [
                                    'tersedia' => 'success',
                                    'sedang_dipinjam' => 'warning',
                                    'dikembalikan' => 'info',
                                    'hilang' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $status_badge[$row['status']] ?>">
                                    <?= str_replace('_', ' ', ucfirst($row['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" data-bs-target="#viewBarangModal"
                                        onclick="viewBarang(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" data-bs-target="#editBarangModal"
                                        onclick="setEditBarang(<?= htmlspecialchars(json_encode($row)) ?>)">
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

<!-- Add Barang Modal -->
<div class="modal fade" id="addBarangModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Barang</label>
                            <input type="text" class="form-control" name="kode_barang" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" name="nama_barang" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" name="kategori" list="kategoriList">
                            <datalist id="kategoriList">
                                <option value="Elektronik">
                                <option value="Furniture">
                                <option value="Alat Tulis">
                                <option value="Olahraga">
                                <option value="Multimedia">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" name="foto_barang" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_barang" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Barang Modal -->
<div class="modal fade" id="editBarangModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="old_foto" id="edit_old_foto">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Barang</label>
                            <input type="text" class="form-control" name="kode_barang" id="edit_kode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" name="nama_barang" id="edit_nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" name="kategori" id="edit_kategori">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="edit_status">
                                <option value="tersedia">Tersedia</option>
                                <option value="sedang_dipinjam">Sedang Dipinjam</option>
                                <option value="dikembalikan">Dikembalikan</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" name="foto_barang" accept="image/*">
                            <div class="form-text mt-2">
                                <img id="edit_foto_preview" src="" style="max-width: 100px; display: none;">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_barang" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Barang Modal -->
<div class="modal fade" id="viewBarangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="view_foto" src="" class="img-fluid rounded" 
                         style="max-height: 200px; display: none;">
                    <div id="view_no_foto" class="bg-light rounded p-5 text-center">
                        <i class="fas fa-box fa-3x text-muted"></i>
                        <p class="mt-2">Tidak ada foto</p>
                    </div>
                </div>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Kode Barang</th>
                        <td id="view_kode"></td>
                    </tr>
                    <tr>
                        <th>Nama Barang</th>
                        <td id="view_nama"></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td id="view_kategori"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="view_status"></td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td id="view_deskripsi"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Ditambahkan</th>
                        <td id="view_created"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function setEditBarang(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_kode').value = data.kode_barang;
    document.getElementById('edit_nama').value = data.nama_barang;
    document.getElementById('edit_kategori').value = data.kategori || '';
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_deskripsi').value = data.deskripsi || '';
    document.getElementById('edit_old_foto').value = data.foto_barang || '';
    
    // Show preview of existing photo
    const preview = document.getElementById('edit_foto_preview');
    if (data.foto_barang) {
        preview.src = '../uploads/barang/' + data.foto_barang;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function viewBarang(data) {
    document.getElementById('view_kode').textContent = data.kode_barang;
    document.getElementById('view_nama').textContent = data.nama_barang;
    document.getElementById('view_kategori').textContent = data.kategori || '-';
    document.getElementById('view_deskripsi').textContent = data.deskripsi || '-';
    document.getElementById('view_created').textContent = new Date(data.created_at).toLocaleDateString('id-ID');
    
    // Status dengan badge
    const statusText = data.status.replace('_', ' ');
    const statusClass = {
        'tersedia': 'success',
        'sedang_dipinjam': 'warning',
        'dikembalikan': 'info',
        'hilang': 'danger'
    };
    document.getElementById('view_status').innerHTML = 
        `<span class="badge bg-${statusClass[data.status]}">${statusText}</span>`;
    
    // Foto
    const fotoImg = document.getElementById('view_foto');
    const noFotoDiv = document.getElementById('view_no_foto');
    
    if (data.foto_barang) {
        fotoImg.src = '../uploads/barang/' + data.foto_barang;
        fotoImg.style.display = 'block';
        noFotoDiv.style.display = 'none';
    } else {
        fotoImg.style.display = 'none';
        noFotoDiv.style.display = 'block';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>