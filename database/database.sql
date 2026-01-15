-- Jalankan di phpMyAdmin (localhost/phpmyadmin)
CREATE DATABASE IF NOT EXISTS sistem_peminjaman_barang;
USE sistem_peminjaman_barang;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel mahasiswa (peminjam) - DITAMBAHKAN KOLOM ANGKATAN
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    angkatan YEAR NOT NULL, -- Tahun angkatan
    no_hp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel barang
CREATE TABLE IF NOT EXISTS barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_barang VARCHAR(20) UNIQUE NOT NULL,
    nama_barang VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(50),
    status ENUM('tersedia', 'sedang_dipinjam', 'dikembalikan', 'hilang') DEFAULT 'tersedia',
    foto_barang VARCHAR(255),
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
    foto_bukti_pinjam VARCHAR(255) NOT NULL,
    foto_bukti_kembali VARCHAR(255),
    status ENUM('dipinjam', 'dikembalikan', 'hilang') DEFAULT 'dipinjam',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

