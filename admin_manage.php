<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Cek akses (hanya superadmin)
if ($_SESSION['role'] != 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

// Handle form actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Tambah Admin Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role = 'admin'; // Default role untuk admin baru
    
    $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = 'Username sudah digunakan!';
        $message_type = 'danger';
    } else {
        $query = "INSERT INTO users (username, password, nama_lengkap, role) 
                  VALUES ('$username', '$password', '$nama_lengkap', '$role')";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Admin berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Edit Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_admin'])) {
    $id = $_POST['id'];
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    
    // Jika password diisi, update password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET 
                  username = '$username', 
                  password = '$password',
                  nama_lengkap = '$nama_lengkap'
                  WHERE id = $id";
    } else {
        $query = "UPDATE users SET 
                  username = '$username',
                  nama_lengkap = '$nama_lengkap'
                  WHERE id = $id";
    }
    
    if (mysqli_query($koneksi, $query)) {
        $message = 'Data admin berhasil diupdate!';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . mysqli_error($koneksi);
        $message_type = 'danger';
    }
}

// Hapus Admin
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Cegah menghapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $message = 'Tidak bisa menghapus akun sendiri!';
        $message_type = 'danger';
    } else {
        $query = "DELETE FROM users WHERE id = $id AND role = 'admin'";
        
        if (mysqli_query($koneksi, $query)) {
            $message = 'Admin berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}

// Get admin data for edit
$admin_data = null;
if ($action == 'edit' && $id > 0) {
    $query = "SELECT * FROM users WHERE id = $id AND role = 'admin'";
    $result = mysqli_query($koneksi, $query);
    $admin_data = mysqli_fetch_assoc($result);
}

// Get all admins (except superadmin)
$query = "SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);
?>

<div class="col-lg-10 col-md-9 main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Kelola Admin</h3>
            <p class="text-muted mb-0">Kelola akun admin sistem</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="fas fa-user-plus me-2"></i> Tambah Admin
        </button>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Admin List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Admin</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Tanggal Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td><?= $row['nama_lengkap'] ?></td>
                            <td>
                                <span class="badge bg-<?= $row['role'] == 'superadmin' ? 'danger' : 'primary' ?>">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" data-bs-target="#editAdminModal"
                                        onclick="setEditData(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-delete"
                                   onclick="return confirm('Hapus admin ini?')">
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

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_admin" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" name="password">
                        <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_admin" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setEditData(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama_lengkap;
    document.getElementById('edit_username').value = data.username;
}
</script>

<?php require_once 'includes/footer.php'; ?>