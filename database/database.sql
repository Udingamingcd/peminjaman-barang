-- Jalankan di phpMyAdmin (localhost/phpmyadmin)
Drop DATABASE IF EXISTS sistem_peminjaman_barang;
CREATE DATABASE IF NOT EXISTS sistem_peminjaman_barang;
USE sistem_peminjaman_barang;

-- Tabel users (DIPERBARUI)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('superadmin', 'admin') DEFAULT 'admin',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel activity_log (BARU)
CREATE TABLE IF NOT EXISTS activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Tabel mahasiswa (peminjam) - DITAMBAHKAN KOLOM ANGKATAN
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    angkatan YEAR NOT NULL, -- Tahun angkatan
    no_hp VARCHAR(15),
    email VARCHAR(100),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel barang
CREATE TABLE IF NOT EXISTS barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_barang VARCHAR(20) UNIQUE NOT NULL,
    nama_barang VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(50),
    stok INT DEFAULT 1,
    status ENUM('tersedia', 'sedang_dipinjam', 'dikembalikan', 'hilang', 'rusak', 'dalam_perbaikan') DEFAULT 'tersedia',
    foto_barang VARCHAR(255),
    lokasi VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_peminjaman VARCHAR(20) UNIQUE NOT NULL,
    mahasiswa_id INT NOT NULL,
    barang_id INT NOT NULL,
    admin_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE,
    batas_kembali DATE NOT NULL,
    foto_bukti_pinjam VARCHAR(255) NOT NULL,
    foto_bukti_kembali VARCHAR(255),
    status ENUM('dipinjam', 'dikembalikan', 'hilang', 'terlambat') DEFAULT 'dipinjam',
    keterangan TEXT,
    denda DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Tabel riwayat_status
CREATE TABLE IF NOT EXISTS riwayat_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT NOT NULL,
    status_sebelum VARCHAR(50),
    status_sesudah VARCHAR(50),
    admin_id INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Tabel penggantian_barang_hilang
CREATE TABLE IF NOT EXISTS penggantian_barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT NOT NULL,
    barang_baru_id INT,
    admin_id INT NOT NULL,
    tanggal_penggantian DATE NOT NULL,
    keterangan TEXT,
    status ENUM('menunggu', 'dikonfirmasi', 'ditolak') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id),
    FOREIGN KEY (barang_baru_id) REFERENCES barang(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Tabel settings (BARU)
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'SIPEMBAR', 'Nama Sistem'),
('site_description', 'Sistem Peminjaman Barang Universitas', 'Deskripsi Sistem'),
('max_loan_days', '7', 'Maksimal hari peminjaman'),
('late_fee_per_day', '5000', 'Denda keterlambatan per hari'),
('admin_email', 'admin@university.ac.id', 'Email admin utama'),
('maintenance_mode', '0', 'Mode maintenance (0=off, 1=on)');

-- Buat index untuk performa
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_mahasiswa_nim ON mahasiswa(nim);
CREATE INDEX idx_barang_kode ON barang(kode_barang);
CREATE INDEX idx_peminjaman_kode ON peminjaman(kode_peminjaman);
CREATE INDEX idx_peminjaman_status ON peminjaman(status);
CREATE INDEX idx_peminjaman_mahasiswa ON peminjaman(mahasiswa_id);
CREATE INDEX idx_peminjaman_barang ON peminjaman(barang_id);
CREATE INDEX idx_activity_log_admin ON activity_log(admin_id);
CREATE INDEX idx_activity_log_created ON activity_log(created_at DESC);