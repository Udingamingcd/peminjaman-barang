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
    <title>Data Mahasiswa - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-person-badge-fill text-success"></i> Data Mahasiswa
                    </h2>
                    <a href="mahasiswa.php?action=tambah" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Tambah Mahasiswa
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

                <!-- Form Tambah/Edit Mahasiswa -->
                <?php if(isset($_GET['action']) && ($_GET['action'] == 'tambah' || $_GET['action'] == 'edit')): ?>
                    <?php
                    $mhs_data = null;
                    if(isset($_GET['id']) && $_GET['action'] == 'edit') {
                        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
                        $query = "SELECT * FROM mahasiswa WHERE id = '$id'";
                        $result = mysqli_query($koneksi, $query);
                        $mhs_data = mysqli_fetch_assoc($result);
                    }
                    
                    // Generate tahun untuk dropdown angkatan
                    $current_year = date('Y');
                    $years = range($current_year - 10, $current_year + 5);
                    ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person-plus"></i> 
                                <?= isset($_GET['id']) ? 'Edit Mahasiswa' : 'Tambah Mahasiswa Baru'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="process_mahasiswa.php" method="POST" id="formMahasiswa">
                                <?php if(isset($mhs_data)): ?>
                                    <input type="hidden" name="id" value="<?= $mhs_data['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nim" class="form-label">NIM *</label>
                                        <input type="text" class="form-control" id="nim" name="nim" 
                                               value="<?= isset($mhs_data) ? $mhs_data['nim'] : ''; ?>" required
                                               pattern="[0-9]{8,20}" title="NIM harus berupa angka (8-20 digit)">
                                        <div class="form-text">Nomor Induk Mahasiswa (angka 8-20 digit)</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama" name="nama" 
                                               value="<?= isset($mhs_data) ? htmlspecialchars($mhs_data['nama']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="angkatan" class="form-label">Angkatan *</label>
                                        <select class="form-select" id="angkatan" name="angkatan" required>
                                            <option value="">Pilih Tahun Angkatan</option>
                                            <?php foreach($years as $year): ?>
                                            <option value="<?= $year; ?>" 
                                                <?= (isset($mhs_data) && $mhs_data['angkatan'] == $year) ? 'selected' : ''; ?>>
                                                <?= $year; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Tahun masuk mahasiswa</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                               value="<?= isset($mhs_data) ? $mhs_data['no_hp'] : ''; ?>"
                                               pattern="[0-9]{10,15}" title="Nomor HP harus 10-15 digit angka">
                                        <div class="form-text">Contoh: 081234567890</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="mahasiswa.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" name="<?= isset($mhs_data) ? 'update' : 'create'; ?>" 
                                            class="btn btn-success">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Statistik Mahasiswa -->
                <div class="row mb-4">
                    <?php
                    // Hitung total mahasiswa
                    $query_total = "SELECT COUNT(*) as total FROM mahasiswa";
                    $result_total = mysqli_query($koneksi, $query_total);
                    $total = mysqli_fetch_assoc($result_total)['total'];
                    
                    // Hitung per angkatan
                    $query_angkatan = "SELECT angkatan, COUNT(*) as jumlah 
                                       FROM mahasiswa 
                                       GROUP BY angkatan 
                                       ORDER BY angkatan DESC 
                                       LIMIT 5";
                    $result_angkatan = mysqli_query($koneksi, $query_angkatan);
                    ?>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Mahasiswa</h6>
                                        <h2 class="mb-0"><?= $total; ?></h2>
                                    </div>
                                    <i class="bi bi-people fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php while($angkatan = mysqli_fetch_assoc($result_angkatan)): ?>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1">Angkatan</h6>
                                <h4 class="mb-0"><?= $angkatan['angkatan']; ?></h4>
                                <small><?= $angkatan['jumlah']; ?> mahasiswa</small>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Filter dan Pencarian -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-filter"></i> Filter & Pencarian
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari NIM atau Nama</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="angkatan_filter" class="form-label">Filter Angkatan</label>
                                <select class="form-select" id="angkatan_filter" name="angkatan_filter">
                                    <option value="">Semua Angkatan</option>
                                    <?php
                                    $query_tahun = "SELECT DISTINCT angkatan FROM mahasiswa ORDER BY angkatan DESC";
                                    $result_tahun = mysqli_query($koneksi, $query_tahun);
                                    while($tahun = mysqli_fetch_assoc($result_tahun)):
                                    ?>
                                    <option value="<?= $tahun['angkatan']; ?>"
                                        <?= (isset($_GET['angkatan_filter']) && $_GET['angkatan_filter'] == $tahun['angkatan']) ? 'selected' : ''; ?>>
                                        <?= $tahun['angkatan']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sort" class="form-label">Urutkan</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="terbaru" <?= (isset($_GET['sort']) && $_GET['sort'] == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="terlama" <?= (isset($_GET['sort']) && $_GET['sort'] == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                                    <option value="nama_az" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nama_az') ? 'selected' : ''; ?>>Nama A-Z</option>
                                    <option value="nama_za" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nama_za') ? 'selected' : ''; ?>>Nama Z-A</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Terapkan Filter
                                    </button>
                                    <a href="mahasiswa.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Mahasiswa -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list"></i> Daftar Mahasiswa
                        </h5>
                        <div>
                            <a href="mahasiswa.php?export=csv" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-download"></i> Export CSV
                            </a>
                            <button type="button" class="btn btn-sm btn-light" onclick="printTable()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="mahasiswaTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Angkatan</th>
                                        <th>No. HP</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Total Peminjaman</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Build query dengan filter
                                    $where = [];
                                    $params = [];
                                    
                                    if(isset($_GET['search']) && !empty($_GET['search'])) {
                                        $search = mysqli_real_escape_string($koneksi, $_GET['search']);
                                        $where[] = "(nim LIKE '%$search%' OR nama LIKE '%$search%')";
                                    }
                                    
                                    if(isset($_GET['angkatan_filter']) && !empty($_GET['angkatan_filter'])) {
                                        $angkatan = mysqli_real_escape_string($koneksi, $_GET['angkatan_filter']);
                                        $where[] = "angkatan = '$angkatan'";
                                    }
                                    
                                    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
                                    
                                    // Order by
                                    $order_by = "ORDER BY created_at DESC";
                                    if(isset($_GET['sort'])) {
                                        switch($_GET['sort']) {
                                            case 'terlama':
                                                $order_by = "ORDER BY created_at ASC";
                                                break;
                                            case 'nama_az':
                                                $order_by = "ORDER BY nama ASC";
                                                break;
                                            case 'nama_za':
                                                $order_by = "ORDER BY nama DESC";
                                                break;
                                        }
                                    }
                                    
                                    $query = "SELECT m.*, 
                                             (SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = m.id) as total_peminjaman
                                             FROM mahasiswa m 
                                             $where_clause 
                                             $order_by";
                                    
                                    $result = mysqli_query($koneksi, $query);
                                    
                                    if(mysqli_num_rows($result) > 0):
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($row)):
                                            // Warna badge berdasarkan angkatan
                                            $angkatan_color = '';
                                            $current_year = date('Y');
                                            $tahun_masuk = $row['angkatan'];
                                            
                                            if($current_year - $tahun_masuk == 0) {
                                                $angkatan_color = 'bg-info'; // Angkatan baru
                                            } elseif($current_year - $tahun_masuk == 1) {
                                                $angkatan_color = 'bg-primary'; // Angkatan 2
                                            } elseif($current_year - $tahun_masuk == 2) {
                                                $angkatan_color = 'bg-success'; // Angkatan 3
                                            } elseif($current_year - $tahun_masuk == 3) {
                                                $angkatan_color = 'bg-warning'; // Angkatan 4
                                            } else {
                                                $angkatan_color = 'bg-secondary'; // Alumni
                                            }
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><code><?= $row['nim']; ?></code></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td>
                                            <span class="badge <?= $angkatan_color; ?>">
                                                <?= $row['angkatan']; ?>
                                            </span>
                                        </td>
                                        <td><?= $row['no_hp'] ?: '<span class="text-muted">-</span>'; ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <span class="badge <?= $row['total_peminjaman'] > 0 ? 'bg-info' : 'bg-light text-dark'; ?>">
                                                <?= $row['total_peminjaman']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="mahasiswa.php?action=edit&id=<?= $row['id']; ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger" 
                                                        onclick="confirmDelete(<?= $row['id']; ?>)" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="bi bi-person-x"></i> Tidak ada data mahasiswa
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php
                        // Hitung total data untuk pagination
                        $count_query = "SELECT COUNT(*) as total FROM mahasiswa $where_clause";
                        $count_result = mysqli_query($koneksi, $count_query);
                        $total_rows = mysqli_fetch_assoc($count_result)['total'];
                        $total_pages = ceil($total_rows / 10); // 10 item per halaman
                        ?>
                        
                        <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= (!isset($_GET['page']) || $_GET['page'] == 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, ($_GET['page'] ?? 1) - 1)])); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for($i = 1; $i <= min($total_pages, 5); $i++): ?>
                                <li class="page-item <?= ($_GET['page'] ?? 1) == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if($total_pages > 5): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                                        <?= $total_pages; ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <li class="page-item <?= ($_GET['page'] ?? 1) >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => min($total_pages, ($_GET['page'] ?? 1) + 1)])); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
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
                    Apakah Anda yakin ingin menghapus data mahasiswa ini? 
                    <span id="deleteDetails"></span>
                    Tindakan ini akan menghapus semua data peminjaman terkait.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#mahasiswaTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });
    
    function confirmDelete(mhsId) {
        // Get mahasiswa details via AJAX
        $.ajax({
            url: 'get_mahasiswa_details.php',
            method: 'GET',
            data: { id: mhsId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#deleteDetails').html(
                        `<br><strong>NIM:</strong> ${response.data.nim}<br>` +
                        `<strong>Nama:</strong> ${response.data.nama}<br>` +
                        `<strong>Angkatan:</strong> ${response.data.angkatan}`
                    );
                }
            }
        });
        
        document.getElementById('deleteLink').href = 'process_mahasiswa.php?delete=' + mhsId;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    
    function printTable() {
        window.print();
    }
    
    // Real-time NIM validation
    document.getElementById('nim').addEventListener('blur', function() {
        const nim = this.value;
        if(nim.length >= 8) {
            // Check if NIM already exists
            $.ajax({
                url: 'check_nim.php',
                method: 'POST',
                data: { nim: nim },
                success: function(response) {
                    if(response.exists) {
                        alert('NIM sudah terdaftar!');
                        document.getElementById('nim').focus();
                        document.getElementById('nim').classList.add('is-invalid');
                    } else {
                        document.getElementById('nim').classList.remove('is-invalid');
                    }
                }
            });
        }
    });
    </script>
</body>
</html>