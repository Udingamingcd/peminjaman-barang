<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Sistem Peminjaman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-box-seam text-warning"></i> Data Barang
                    </h2>
                    <div>
                        <?php if($_SESSION['role'] == 'superadmin'): ?>
                        <a href="barang.php?action=tambah" class="btn btn-warning">
                            <i class="bi bi-plus-circle"></i> Tambah Barang
                        </a>
                        <?php endif; ?>
                    </div>
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

                <!-- Form Tambah/Edit Barang -->
                <?php if(isset($_GET['action']) && ($_GET['action'] == 'tambah' || $_GET['action'] == 'edit')): ?>
                    <?php
                    $barang_data = null;
                    if(isset($_GET['id']) && $_GET['action'] == 'edit') {
                        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
                        $query = "SELECT * FROM barang WHERE id = '$id'";
                        $result = mysqli_query($koneksi, $query);
                        $barang_data = mysqli_fetch_assoc($result);
                    }
                    ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-box-seam"></i> 
                                <?= isset($_GET['id']) ? 'Edit Barang' : 'Tambah Barang Baru'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="process_barang.php" method="POST" enctype="multipart/form-data" id="formBarang">
                                <?php if(isset($barang_data)): ?>
                                    <input type="hidden" name="id" value="<?= $barang_data['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="kode_barang" class="form-label">Kode Barang *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="kode_barang" name="kode_barang" 
                                                   value="<?= isset($barang_data) ? $barang_data['kode_barang'] : ''; ?>" required>
                                            <button type="button" class="btn btn-outline-secondary" onclick="generateKodeBarang()">
                                                <i class="bi bi-arrow-repeat"></i> Generate
                                            </button>
                                        </div>
                                        <div class="form-text">Kode unik untuk barang</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_barang" class="form-label">Nama Barang *</label>
                                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" 
                                               value="<?= isset($barang_data) ? htmlspecialchars($barang_data['nama_barang']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="kategori" class="form-label">Kategori *</label>
                                        <select class="form-select" id="kategori" name="kategori" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php
                                            $kategori_list = ['Elektronik', 'Perabotan', 'Alat Tulis', 'Olahraga', 'Audio Visual', 'Lainnya'];
                                            foreach($kategori_list as $kategori):
                                            ?>
                                            <option value="<?= $kategori; ?>" 
                                                <?= (isset($barang_data) && $barang_data['kategori'] == $kategori) ? 'selected' : ''; ?>>
                                                <?= $kategori; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="tersedia" <?= (isset($barang_data) && $barang_data['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                            <option value="sedang_dipinjam" <?= (isset($barang_data) && $barang_data['status'] == 'sedang_dipinjam') ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                                            <option value="dikembalikan" <?= (isset($barang_data) && $barang_data['status'] == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                                            <option value="hilang" <?= (isset($barang_data) && $barang_data['status'] == 'hilang') ? 'selected' : ''; ?>>Hilang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= isset($barang_data) ? htmlspecialchars($barang_data['deskripsi']) : ''; ?></textarea>
                                        <div class="form-text">Deskripsi detail barang (spesifikasi, kondisi, dll)</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="foto_barang" class="form-label">Foto Barang</label>
                                        <input type="file" class="form-control" id="foto_barang" name="foto_barang" accept="image/*">
                                        <div class="form-text">Format: JPG, PNG, maksimal 2MB</div>
                                        
                                        <?php if(isset($barang_data) && !empty($barang_data['foto_barang'])): ?>
                                        <div class="mt-2">
                                            <img src="assets/uploads/<?= $barang_data['foto_barang']; ?>" 
                                                 alt="Foto Barang" class="img-thumbnail" style="max-width: 150px;">
                                            <br>
                                            <small>Foto saat ini: <?= $barang_data['foto_barang']; ?></small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="barang.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" name="<?= isset($barang_data) ? 'update' : 'create'; ?>" 
                                            class="btn btn-warning">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Statistik Barang -->
                <div class="row mb-4">
                    <?php
                    // Hitung statistik barang
                    $query_total = "SELECT COUNT(*) as total FROM barang";
                    $result_total = mysqli_query($koneksi, $query_total);
                    $total = mysqli_fetch_assoc($result_total)['total'];
                    
                    $query_tersedia = "SELECT COUNT(*) as total FROM barang WHERE status = 'tersedia'";
                    $result_tersedia = mysqli_query($koneksi, $query_tersedia);
                    $tersedia = mysqli_fetch_assoc($result_tersedia)['total'];
                    
                    $query_dipinjam = "SELECT COUNT(*) as total FROM barang WHERE status = 'sedang_dipinjam'";
                    $result_dipinjam = mysqli_query($koneksi, $query_dipinjam);
                    $dipinjam = mysqli_fetch_assoc($result_dipinjam)['total'];
                    
                    $query_hilang = "SELECT COUNT(*) as total FROM barang WHERE status = 'hilang'";
                    $result_hilang = mysqli_query($koneksi, $query_hilang);
                    $hilang = mysqli_fetch_assoc($result_hilang)['total'];
                    ?>
                    
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Barang</h6>
                                        <h2 class="mb-0"><?= $total; ?></h2>
                                    </div>
                                    <i class="bi bi-box fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Tersedia</h6>
                                        <h2 class="mb-0"><?= $tersedia; ?></h2>
                                    </div>
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Sedang Dipinjam</h6>
                                        <h2 class="mb-0"><?= $dipinjam; ?></h2>
                                    </div>
                                    <i class="bi bi-clock-history fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Hilang/Rusak</h6>
                                        <h2 class="mb-0"><?= $hilang; ?></h2>
                                    </div>
                                    <i class="bi bi-exclamation-triangle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <div class="col-md-3">
                                <label for="search" class="form-label">Cari Kode/Nama</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="kategori_filter" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori_filter" name="kategori_filter">
                                    <option value="">Semua Kategori</option>
                                    <?php
                                    $query_kategori = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
                                    $result_kategori = mysqli_query($koneksi, $query_kategori);
                                    while($kategori = mysqli_fetch_assoc($result_kategori)):
                                    ?>
                                    <option value="<?= $kategori['kategori']; ?>"
                                        <?= (isset($_GET['kategori_filter']) && $_GET['kategori_filter'] == $kategori['kategori']) ? 'selected' : ''; ?>>
                                        <?= $kategori['kategori']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status_filter" class="form-label">Status</label>
                                <select class="form-select" id="status_filter" name="status_filter">
                                    <option value="">Semua Status</option>
                                    <option value="tersedia" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                    <option value="sedang_dipinjam" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'sedang_dipinjam') ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                                    <option value="dikembalikan" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                                    <option value="hilang" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'hilang') ? 'selected' : ''; ?>>Hilang</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sort" class="form-label">Urutkan</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="terbaru" <?= (isset($_GET['sort']) && $_GET['sort'] == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="terlama" <?= (isset($_GET['sort']) && $_GET['sort'] == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                                    <option value="nama_az" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nama_az') ? 'selected' : ''; ?>>Nama A-Z</option>
                                    <option value="nama_za" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nama_za') ? 'selected' : ''; ?>>Nama Z-A</option>
                                    <option value="kode_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'kode_asc') ? 'selected' : ''; ?>>Kode ASC</option>
                                    <option value="kode_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'kode_desc') ? 'selected' : ''; ?>>Kode DESC</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Terapkan Filter
                                    </button>
                                    <a href="barang.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                    <?php if($_SESSION['role'] == 'superadmin'): ?>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="bi bi-upload"></i> Import
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Barang -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list"></i> Daftar Barang
                        </h5>
                        <div>
                            <a href="barang.php?export=csv" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-download"></i> Export CSV
                            </a>
                            <button type="button" class="btn btn-sm btn-light" onclick="printTable()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="barangTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Status</th>
                                        <th>Foto</th>
                                        <th>Terakhir Diubah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Build query dengan filter
                                    $where = [];
                                    
                                    if(isset($_GET['search']) && !empty($_GET['search'])) {
                                        $search = mysqli_real_escape_string($koneksi, $_GET['search']);
                                        $where[] = "(kode_barang LIKE '%$search%' OR nama_barang LIKE '%$search%' OR deskripsi LIKE '%$search%')";
                                    }
                                    
                                    if(isset($_GET['kategori_filter']) && !empty($_GET['kategori_filter'])) {
                                        $kategori = mysqli_real_escape_string($koneksi, $_GET['kategori_filter']);
                                        $where[] = "kategori = '$kategori'";
                                    }
                                    
                                    if(isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
                                        $status = mysqli_real_escape_string($koneksi, $_GET['status_filter']);
                                        $where[] = "status = '$status'";
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
                                                $order_by = "ORDER BY nama_barang ASC";
                                                break;
                                            case 'nama_za':
                                                $order_by = "ORDER BY nama_barang DESC";
                                                break;
                                            case 'kode_asc':
                                                $order_by = "ORDER BY kode_barang ASC";
                                                break;
                                            case 'kode_desc':
                                                $order_by = "ORDER BY kode_barang DESC";
                                                break;
                                        }
                                    }
                                    
                                    $query = "SELECT * FROM barang $where_clause $order_by";
                                    $result = mysqli_query($koneksi, $query);
                                    
                                    if(mysqli_num_rows($result) > 0):
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)):
                                            // Warna badge berdasarkan status
                                            $status_color = '';
                                            $status_icon = '';
                                            switch($row['status']) {
                                                case 'tersedia':
                                                    $status_color = 'bg-success';
                                                    $status_icon = 'bi-check-circle';
                                                    break;
                                                case 'sedang_dipinjam':
                                                    $status_color = 'bg-warning';
                                                    $status_icon = 'bi-clock-history';
                                                    break;
                                                case 'dikembalikan':
                                                    $status_color = 'bg-info';
                                                    $status_icon = 'bi-arrow-return-left';
                                                    break;
                                                case 'hilang':
                                                    $status_color = 'bg-danger';
                                                    $status_icon = 'bi-exclamation-triangle';
                                                    break;
                                                default:
                                                    $status_color = 'bg-secondary';
                                                    $status_icon = 'bi-question-circle';
                                            }
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><code><?= $row['kode_barang']; ?></code></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['nama_barang']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($row['kategori']): ?>
                                            <span class="badge bg-primary"><?= $row['kategori']; ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['deskripsi']): ?>
                                            <small class="text-muted" title="<?= htmlspecialchars($row['deskripsi']); ?>">
                                                <?= strlen($row['deskripsi']) > 50 ? substr($row['deskripsi'], 0, 50) . '...' : $row['deskripsi']; ?>
                                            </small>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $status_color; ?>">
                                                <i class="bi <?= $status_icon; ?>"></i>
                                                <?= ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['foto_barang']) && file_exists('assets/uploads/' . $row['foto_barang'])): ?>
                                            <img src="assets/uploads/<?= $row['foto_barang']; ?>" 
                                                 alt="Foto Barang" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#imageModal"
                                                 data-src="assets/uploads/<?= $row['foto_barang']; ?>"
                                                 data-title="<?= htmlspecialchars($row['nama_barang']); ?>">
                                            <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-image"></i> No Image
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= date('d/m/Y H:i', strtotime($row['updated_at'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal"
                                                        onclick="showBarangDetail(<?= $row['id']; ?>)"
                                                        title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <?php if($_SESSION['role'] == 'superadmin'): ?>
                                                <a href="barang.php?action=edit&id=<?= $row['id']; ?>" 
                                                   class="btn btn-warning" 
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" 
                                                        class="btn btn-danger" 
                                                        onclick="confirmDeleteBarang(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama_barang']); ?>')"
                                                        title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="bi bi-box fs-1 d-block mb-2"></i>
                                            Tidak ada data barang ditemukan
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

    <!-- Detail Barang Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle"></i> Detail Barang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBarangContent">
                    <!-- Detail akan dimuat via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Foto Barang" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-upload"></i> Import Data Barang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="import_barang.php" method="POST" enctype="multipart/form-data" id="importForm">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">File Excel/CSV</label>
                            <input type="file" class="form-control" id="importFile" name="importFile" accept=".csv, .xlsx, .xls" required>
                            <div class="form-text">
                                Format file: CSV atau Excel. 
                                <a href="template_barang.csv" download class="text-decoration-none">
                                    <i class="bi bi-download"></i> Download template
                                </a>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Format kolom:</strong> kode_barang, nama_barang, kategori, deskripsi, status
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="importForm" class="btn btn-success">Import</button>
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
                    Apakah Anda yakin ingin menghapus barang ini? 
                    <span id="deleteDetails"></span>
                    Tindakan ini tidak dapat dibatalkan.
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
        $('#barangTable').DataTable({
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
    
    // Image Preview Modal
    $('#imageModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const imageSrc = button.data('src');
        const imageTitle = button.data('title');
        const modal = $(this);
        modal.find('#modalImage').attr('src', imageSrc);
        modal.find('.modal-title').text('Foto: ' + imageTitle);
    });
    
    // Generate kode barang
    function generateKodeBarang() {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 4).toUpperCase();
        const generatedCode = `BRG-${timestamp}-${random}`;
        document.getElementById('kode_barang').value = generatedCode;
        
        // Show notification
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle"></i> Kode barang berhasil digenerate!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.getElementById('kode_barang').parentElement.parentElement.appendChild(alertDiv);
        
        // Auto hide notification
        setTimeout(() => {
            if (alertDiv.parentElement) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 3000);
    }
    
    // Show barang detail
    function showBarangDetail(barangId) {
        $.ajax({
            url: 'get_barang_detail.php',
            method: 'GET',
            data: { id: barangId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    const barang = response.data;
                    
                    // Format status badge
                    let statusClass = '';
                    let statusIcon = '';
                    switch(barang.status) {
                        case 'tersedia':
                            statusClass = 'success';
                            statusIcon = 'check-circle';
                            break;
                        case 'sedang_dipinjam':
                            statusClass = 'warning';
                            statusIcon = 'clock-history';
                            break;
                        case 'dikembalikan':
                            statusClass = 'info';
                            statusIcon = 'arrow-return-left';
                            break;
                        case 'hilang':
                            statusClass = 'danger';
                            statusIcon = 'exclamation-triangle';
                            break;
                    }
                    
                    // Build detail HTML
                    let detailHTML = `
                        <div class="row">
                            <div class="col-md-4 text-center">
                                ${barang.foto_barang ? 
                                    `<img src="assets/uploads/${barang.foto_barang}" alt="${barang.nama_barang}" class="img-fluid rounded mb-3" style="max-height: 200px;">` : 
                                    `<div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                                        <i class="bi bi-image text-muted fs-1"></i>
                                    </div>`
                                }
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Kode Barang</th>
                                        <td><code>${barang.kode_barang}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <td><strong>${barang.nama_barang}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Kategori</th>
                                        <td>${barang.kategori ? `<span class="badge bg-primary">${barang.kategori}</span>` : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-${statusClass}">
                                                <i class="bi bi-${statusIcon}"></i>
                                                ${barang.status.replace('_', ' ').toUpperCase()}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <td>${barang.deskripsi ? barang.deskripsi : '-'}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Ditambahkan</th>
                                        <td>${barang.created_at}</td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Diubah</th>
                                        <td>${barang.updated_at}</td>
                                    </tr>
                                </table>
                                
                                <h6 class="mt-4">Riwayat Peminjaman Terbaru:</h6>
                                <div id="riwayatPeminjaman">
                                    <div class="text-center py-2">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <small class="text-muted">Memuat riwayat...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#detailBarangContent').html(detailHTML);
                    
                    // Load riwayat peminjaman
                    loadRiwayatPeminjaman(barangId);
                } else {
                    $('#detailBarangContent').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${response.message}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#detailBarangContent').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Gagal memuat data barang!
                    </div>
                `);
            }
        });
    }
    
    // Load riwayat peminjaman
    function loadRiwayatPeminjaman(barangId) {
        $.ajax({
            url: 'get_riwayat_barang.php',
            method: 'GET',
            data: { barang_id: barangId },
            dataType: 'json',
            success: function(response) {
                let riwayatHTML = '';
                
                if(response.success && response.data.length > 0) {
                    riwayatHTML = `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode Peminjaman</th>
                                        <th>Mahasiswa</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    response.data.forEach(function(riwayat) {
                        let statusClass = '';
                        switch(riwayat.status) {
                            case 'dipinjam': statusClass = 'warning'; break;
                            case 'dikembalikan': statusClass = 'success'; break;
                            case 'hilang': statusClass = 'danger'; break;
                            default: statusClass = 'secondary';
                        }
                        
                        riwayatHTML += `
                            <tr>
                                <td><small><code>${riwayat.kode_peminjaman}</code></small></td>
                                <td><small>${riwayat.nama_mahasiswa}</small></td>
                                <td><small>${riwayat.tanggal_pinjam}</small></td>
                                <td>
                                    <span class="badge bg-${statusClass}">${riwayat.status}</span>
                                </td>
                            </tr>
                        `;
                    });
                    
                    riwayatHTML += `
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Menampilkan ${response.data.length} riwayat terbaru</small>
                    `;
                } else {
                    riwayatHTML = `
                        <div class="alert alert-light">
                            <i class="bi bi-info-circle"></i> Belum ada riwayat peminjaman untuk barang ini.
                        </div>
                    `;
                }
                
                $('#riwayatPeminjaman').html(riwayatHTML);
            }
        });
    }
    
    // Confirm delete barang
    function confirmDeleteBarang(barangId, barangNama) {
        // Cek apakah barang sedang dipinjam
        $.ajax({
            url: 'check_barang_status.php',
            method: 'POST',
            data: { 
                id: barangId,
                action: 'delete'
            },
            success: function(response) {
                if(response.can_delete) {
                    $('#deleteDetails').html(
                        `<br><strong>Nama Barang:</strong> ${barangNama}`
                    );
                    document.getElementById('deleteLink').href = 'process_barang.php?delete=' + barangId;
                    new bootstrap.Modal(document.getElementById('deleteModal')).show();
                } else {
                    alert('Barang tidak dapat dihapus karena sedang dipinjam atau memiliki riwayat peminjaman!');
                }
            },
            error: function() {
                alert('Gagal memeriksa status barang!');
            }
        });
    }
    
    // Print table
    function printTable() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Daftar Barang - Sistem Peminjaman Barang</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .badge { padding: 2px 6px; border-radius: 3px; font-size: 12px; }
                    .bg-success { background-color: #28a745; color: white; }
                    .bg-warning { background-color: #ffc107; color: black; }
                    .bg-danger { background-color: #dc3545; color: white; }
                    .text-center { text-align: center; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>DAFTAR BARANG</h2>
                    <h3>Sistem Peminjaman Barang</h3>
                    <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}</p>
                </div>
        `);
        
        // Clone table
        const table = document.getElementById('barangTable').cloneNode(true);
        
        // Remove action column and foto column
        const rows = table.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].cells;
            if (cells.length > 0) {
                // Remove last cell (aksi)
                if (cells[cells.length - 1]) {
                    cells[cells.length - 1].remove();
                }
                // Remove foto column (6th column)
                if (cells[6]) {
                    cells[6].remove();
                }
            }
        }
        
        printWindow.document.write(table.outerHTML);
        printWindow.document.write(`
                <div class="footer">
                    <p>&copy; ${new Date().getFullYear()} Sistem Peminjaman Barang</p>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
    
    // Real-time search
    document.getElementById('search')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById('barangTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    });
    
    // Form validation
    document.getElementById('formBarang')?.addEventListener('submit', function(e) {
        const kode = document.getElementById('kode_barang').value;
        const nama = document.getElementById('nama_barang').value;
        
        if (!kode.trim()) {
            e.preventDefault();
            alert('Kode barang harus diisi!');
            document.getElementById('kode_barang').focus();
            return false;
        }
        
        if (!nama.trim()) {
            e.preventDefault();
            alert('Nama barang harus diisi!');
            document.getElementById('nama_barang').focus();
            return false;
        }
        
        // Check file size if exists
        const fileInput = document.getElementById('foto_barang');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (file.size > 2 * 1024 * 1024) { // 2MB
                e.preventDefault();
                alert('Ukuran file maksimal 2MB!');
                return false;
            }
        }
        
        return true;
    });
    </script>
</body>
</html>