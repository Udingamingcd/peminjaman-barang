<?php
// admin/topbar.php
?>
<nav class="topbar">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="page-title">
                <h4 class="mb-0">
                    <?php 
                    $titles = [
                        'index.php' => '<i class="fas fa-tachometer-alt me-2"></i>Dashboard',
                        'mahasiswa.php' => '<i class="fas fa-users me-2"></i>Data Mahasiswa',
                        'barang.php' => '<i class="fas fa-box me-2"></i>Data Barang',
                        'peminjaman.php' => '<i class="fas fa-exchange-alt me-2"></i>Peminjaman',
                        'pengembalian.php' => '<i class="fas fa-undo me-2"></i>Pengembalian',
                        'laporan.php' => '<i class="fas fa-chart-bar me-2"></i>Laporan',
                        'profile.php' => '<i class="fas fa-user me-2"></i>Profil',
                        'change_password.php' => '<i class="fas fa-key me-2"></i>Ganti Password'
                    ];
                    echo $titles[basename($_SERVER['PHP_SELF'])] ?? '<i class="fas fa-home me-2"></i>Admin Panel';
                    ?>
                </h4>
                <small class="text-muted">Selamat datang kembali, <?php echo htmlspecialchars($nama_lengkap); ?>!</small>
            </div>
            <div class="topbar-right">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key me-2"></i>Ganti Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>