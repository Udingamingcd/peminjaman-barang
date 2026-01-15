<?php
/**
 * Koneksi ke Database Sistem Peminjaman Barang
 * File ini digunakan untuk menghubungkan aplikasi PHP dengan database MySQL.
 */

$host = 'localhost'; // Host database (biasanya localhost)
$username = 'root'; // Username database
$password = ''; // Password database (kosong default XAMPP/WAMP)
$database = 'sistem_peminjaman_barang'; // Nama database sesuai script

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8 untuk menangani karakter khusus
mysqli_set_charset($koneksi, "utf8");

// Pesan sukses (opsional, bisa dihapus di production)
// echo "Koneksi database berhasil!";
?>