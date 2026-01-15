<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="col-lg-2 col-md-3 sidebar d-none d-md-block">
    <div class="text-center py-4">
        <div class="mb-3">
            <i class="fas fa-boxes fa-3x text-white"></i>
        </div>
        <h5 class="text-white mb-0">Sistem Peminjaman</h5>
        <small class="text-white-50">Barang Inventaris</small>
    </div>
    
    <div class="px-3">
        <div class="user-info mb-4 p-3 bg-white rounded text-center">
            <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                 style="width: 60px; height: 60px; font-size: 24px;">
                <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
            </div>
            <h6 class="mb-0"><?= $_SESSION['nama_lengkap'] ?></h6>
            <small class="text-muted"><?= ucfirst($_SESSION['role']) ?></small>
        </div>
    </div>
    
    <nav class="nav flex-column px-3">
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <!-- Menu untuk Superadmin -->
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
        <a href="admin_manage.php" class="nav-link <?= $current_page == 'admin_manage.php' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> Kelola Admin
        </a>
        <?php endif; ?>
        
        <!-- Menu untuk semua admin -->
        <a href="barang_manage.php" class="nav-link <?= $current_page == 'barang_manage.php' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Kelola Barang
        </a>
        
        <a href="mahasiswa_manage.php" class="nav-link <?= $current_page == 'mahasiswa_manage.php' ? 'active' : '' ?>">
            <i class="fas fa-user-graduate"></i> Data Mahasiswa
        </a>
        
        <a href="peminjaman_manage.php" class="nav-link <?= $current_page == 'peminjaman_manage.php' ? 'active' : '' ?>">
            <i class="fas fa-handshake"></i> Peminjaman
        </a>
        
        <a href="pengembalian.php" class="nav-link <?= $current_page == 'pengembalian.php' ? 'active' : '' ?>">
            <i class="fas fa-undo-alt"></i> Pengembalian
        </a>
        
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
        <a href="laporan.php" class="nav-link <?= $current_page == 'laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Laporan
        </a>
        
        <div class="dropdown-divider my-3"></div>
        
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#settingsModal">
            <i class="fas fa-cogs"></i> Pengaturan Sistem
        </a>
        <?php endif; ?>
        
        <div class="mt-auto py-3">
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</div>