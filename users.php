<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/auth_check.php';

// Hanya superadmin yang bisa akses
if ($_SESSION['role'] != 'superadmin') {
    header("Location: dashboard_admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Admin - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-people-fill text-primary"></i> Manajemen Admin
                    </h2>
                    <a href="users.php?action=tambah" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Tambah Admin
                    </a>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success']; ?>
                        <?php unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Tambah/Edit Admin -->
                <?php if(isset($_GET['action']) && ($_GET['action'] == 'tambah' || $_GET['action'] == 'edit')): ?>
                    <?php
                    $user_data = null;
                    if(isset($_GET['id']) && $_GET['action'] == 'edit') {
                        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
                        $query = "SELECT * FROM users WHERE id = '$id' AND role = 'admin'";
                        $result = mysqli_query($koneksi, $query);
                        $user_data = mysqli_fetch_assoc($result);
                    }
                    ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person-plus"></i> 
                                <?= isset($_GET['id']) ? 'Edit Admin' : 'Tambah Admin Baru'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="process_user.php" method="POST">
                                <?php if(isset($user_data)): ?>
                                    <input type="hidden" name="id" value="<?= $user_data['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?= isset($user_data) ? htmlspecialchars($user_data['nama_lengkap']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= isset($user_data) ? $user_data['username'] : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            Password <?= isset($user_data) ? '(Kosongkan jika tidak diubah)' : '*'; ?>
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password"
                                               <?= !isset($user_data) ? 'required' : ''; ?>>
                                        <div class="form-text">Minimal 8 karakter</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                               <?= !isset($user_data) ? 'required' : ''; ?>>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="users.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" name="<?= isset($user_data) ? 'update' : 'create'; ?>" 
                                            class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Daftar Admin -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list"></i> Daftar Admin
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Role</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC";
                                    $result = mysqli_query($koneksi, $query);
                                    
                                    if(mysqli_num_rows($result) > 0):
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['username']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['role'] == 'superadmin' ? 'success' : 'primary'; ?>">
                                                <?= $row['role']; ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="users.php?action=edit&id=<?= $row['id']; ?>" class="btn btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger" 
                                                        onclick="confirmDelete(<?= $row['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="bi bi-people"></i> Belum ada admin terdaftar
                                        </td>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus admin ini? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete(userId) {
        document.getElementById('deleteLink').href = 'process_user.php?delete=' + userId;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    </script>
</body>
</html>