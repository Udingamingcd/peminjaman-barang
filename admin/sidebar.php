<?php
// admin/sidebar.php
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-boxes logo-icon"></i>
            <h3 class="logo-text">SIPEMBAR</h3>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <h6 class="mb-0"><?php echo htmlspecialchars($nama_lengkap); ?></h6>
                <small class="text-muted"><?php echo ucfirst($role); ?></small>
            </div>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mahasiswa.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="mahasiswa.php">
                    <i class="fas fa-users"></i>
                    <span>Data Mahasiswa</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'barang.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="barang.php">
                    <i class="fas fa-box"></i>
                    <span>Data Barang</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="peminjaman.php">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Peminjaman</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'pengembalian.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="pengembalian.php">
                    <i class="fas fa-undo"></i>
                    <span>Pengembalian</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="laporan.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'change_password.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="change_password.php">
                    <i class="fas fa-key"></i>
                    <span>Ganti Password</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <small class="text-muted">SIPEMBAR v2.1.0</small>
    </div>
</div>