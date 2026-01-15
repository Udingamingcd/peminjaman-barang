<?php
session_start();
require_once 'config/koneksi.php';

// Hanya superadmin yang bisa akses
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

// CREATE MAHASISWA
if (isset($_POST['create'])) {
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    // Validasi input
    $errors = [];
    
    // Validasi NIM (harus angka)
    if (!preg_match('/^[0-9]{8,20}$/', $nim)) {
        $errors[] = "NIM harus berupa angka (8-20 digit)!";
    }
    
    // Validasi angkatan (tahun valid)
    $current_year = date('Y');
    if ($angkatan < 2000 || $angkatan > $current_year + 5) {
        $errors[] = "Angkatan harus antara 2000 dan " . ($current_year + 5);
    }
    
    // Validasi no hp jika diisi
    if (!empty($no_hp) && !preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $errors[] = "No. HP harus berupa angka (10-15 digit)!";
    }
    
    // Cek NIM unik
    $check_query = "SELECT * FROM mahasiswa WHERE nim = '$nim'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "NIM sudah terdaftar!";
    }
    
    // Jika ada error
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: mahasiswa.php?action=tambah");
        exit();
    }
    
    // Insert mahasiswa
    $query = "INSERT INTO mahasiswa (nim, nama, angkatan, no_hp) 
              VALUES ('$nim', '$nama', '$angkatan', '$no_hp')";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Mahasiswa berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan mahasiswa: " . mysqli_error($koneksi);
    }
    
    header("Location: mahasiswa.php");
    exit();
}

// UPDATE MAHASISWA
if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    // Validasi input
    $errors = [];
    
    // Validasi NIM (harus angka)
    if (!preg_match('/^[0-9]{8,20}$/', $nim)) {
        $errors[] = "NIM harus berupa angka (8-20 digit)!";
    }
    
    // Validasi angkatan (tahun valid)
    $current_year = date('Y');
    if ($angkatan < 2000 || $angkatan > $current_year + 5) {
        $errors[] = "Angkatan harus antara 2000 dan " . ($current_year + 5);
    }
    
    // Validasi no hp jika diisi
    if (!empty($no_hp) && !preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $errors[] = "No. HP harus berupa angka (10-15 digit)!";
    }
    
    // Cek NIM (kecuali untuk mahasiswa ini)
    $check_query = "SELECT * FROM mahasiswa WHERE nim = '$nim' AND id != '$id'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "NIM sudah digunakan!";
    }
    
    // Jika ada error
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: mahasiswa.php?action=edit&id=$id");
        exit();
    }
    
    // Update mahasiswa
    $query = "UPDATE mahasiswa SET 
              nim = '$nim', 
              nama = '$nama',
              angkatan = '$angkatan',
              no_hp = '$no_hp'
              WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Mahasiswa berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui mahasiswa: " . mysqli_error($koneksi);
    }
    
    header("Location: mahasiswa.php");
    exit();
}

// DELETE MAHASISWA
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['delete']);
    
    // Cek apakah mahasiswa memiliki data peminjaman
    $check_query = "SELECT COUNT(*) as total FROM peminjaman WHERE mahasiswa_id = '$id'";
    $check_result = mysqli_query($koneksi, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['total'] > 0) {
        $_SESSION['error'] = "Mahasiswa tidak dapat dihapus karena memiliki data peminjaman!";
        header("Location: mahasiswa.php");
        exit();
    }
    
    $query = "DELETE FROM mahasiswa WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Mahasiswa berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus mahasiswa: " . mysqli_error($koneksi);
    }
    
    header("Location: mahasiswa.php");
    exit();
}

// Default redirect
header("Location: mahasiswa.php");
exit();
?>