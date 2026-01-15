<?php
// Navbar untuk semua dashboard
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-box-seam"></i> Peminjaman Barang
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" 
                       href="dashboard.php">Dashboard</a>
                </li>
                
                <?php if($_SESSION['role'] == 'superadmin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" 
                       href="users.php">Manajemen Admin</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'mahasiswa.php' ? 'active' : '' ?>" 
                       href="mahasiswa.php">Data Mahasiswa</a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'barang.php' ? 'active' : '' ?>" 
                       href="barang.php">Data Barang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'active' : '' ?>" 
                       href="peminjaman.php">Peminjaman</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'pengembalian.php' ? 'active' : '' ?>" 
                       href="pengembalian.php">Pengembalian</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']); ?>
                        <small class="badge bg-light text-dark ms-1"><?= $_SESSION['role']; ?></small>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person"></i> Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>