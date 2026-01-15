<?php
session_start();
require_once 'config/koneksi.php';

// Hanya superadmin yang bisa import
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['importFile'])) {
    $errors = [];
    $success_count = 0;
    $error_count = 0;
    
    $file = $_FILES['importFile'];
    
    // Validasi file
    $allowed_ext = ['csv', 'xlsx', 'xls'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['error'] = "Format file tidak didukung. Gunakan CSV, XLSX, atau XLS.";
        header("Location: barang.php");
        exit();
    }
    
    // Untuk file CSV
    if ($file_ext == 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        $row = 0;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;
            
            // Skip header row
            if ($row == 1) continue;
            
            // Validasi data
            if (count($data) < 5) {
                $errors[] = "Baris $row: Format data tidak lengkap";
                $error_count++;
                continue;
            }
            
            $kode_barang = mysqli_real_escape_string($koneksi, $data[0]);
            $nama_barang = mysqli_real_escape_string($koneksi, $data[1]);
            $kategori = mysqli_real_escape_string($koneksi, $data[2]);
            $deskripsi = mysqli_real_escape_string($koneksi, $data[3]);
            $status = mysqli_real_escape_string($koneksi, $data[4]);
            
            // Validasi status
            $valid_status = ['tersedia', 'sedang_dipinjam', 'dikembalikan', 'hilang'];
            if (!in_array($status, $valid_status)) {
                $status = 'tersedia';
            }
            
            // Cek kode barang unik
            $check_query = "SELECT COUNT(*) as count FROM barang WHERE kode_barang = '$kode_barang'";
            $check_result = mysqli_query($koneksi, $check_query);
            $check_row = mysqli_fetch_assoc($check_result);
            
            if ($check_row['count'] > 0) {
                $errors[] = "Baris $row: Kode barang '$kode_barang' sudah ada";
                $error_count++;
                continue;
            }
            
            // Insert data
            $query = "INSERT INTO barang (kode_barang, nama_barang, kategori, deskripsi, status) 
                      VALUES ('$kode_barang', '$nama_barang', '$kategori', '$deskripsi', '$status')";
            
            if (mysqli_query($koneksi, $query)) {
                $success_count++;
            } else {
                $errors[] = "Baris $row: " . mysqli_error($koneksi);
                $error_count++;
            }
        }
        
        fclose($handle);
    }
    
    // Set session message
    $message = "Import selesai: $success_count data berhasil diimport, $error_count gagal.";
    
    if (!empty($errors)) {
        $message .= "<br>Detail error:<br>" . implode("<br>", array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $message .= "<br>... dan " . (count($errors) - 10) . " error lainnya.";
        }
    }
    
    $_SESSION[$error_count > 0 ? 'error' : 'success'] = $message;
}

header("Location: barang.php");
exit();
?>