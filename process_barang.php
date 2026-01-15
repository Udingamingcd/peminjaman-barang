<?php
session_start();
require_once 'config/koneksi.php';

// Cek autentikasi
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Hanya superadmin yang bisa CRUD barang
if ($_SESSION['role'] != 'superadmin') {
    header("Location: dashboard_admin.php");
    exit();
}

// CREATE BARANG
if (isset($_POST['create'])) {
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Validasi input
    $errors = [];
    
    // Cek kode barang unik
    $check_query = "SELECT * FROM barang WHERE kode_barang = '$kode_barang'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Kode barang sudah terdaftar!";
    }
    
    // Handle file upload
    $foto_barang = '';
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['foto_barang']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['foto_barang']['size'];
        $file_tmp = $_FILES['foto_barang']['tmp_name'];
        
        // Validasi ekstensi file
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
        
        // Validasi ukuran file (max 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran file maksimal 2MB.";
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = 'assets/uploads/' . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $foto_barang = $new_filename;
            } else {
                $errors[] = "Gagal mengupload file.";
            }
        }
    }
    
    // Jika ada error
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: barang.php?action=tambah");
        exit();
    }
    
    // Insert barang
    $query = "INSERT INTO barang (kode_barang, nama_barang, kategori, deskripsi, status, foto_barang) 
              VALUES ('$kode_barang', '$nama_barang', '$kategori', '$deskripsi', '$status', '$foto_barang')";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Barang berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan barang: " . mysqli_error($koneksi);
    }
    
    header("Location: barang.php");
    exit();
}

// UPDATE BARANG
if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Cek kode barang (kecuali untuk barang ini)
    $check_query = "SELECT * FROM barang WHERE kode_barang = '$kode_barang' AND id != '$id'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Kode barang sudah digunakan!";
        header("Location: barang.php?action=edit&id=$id");
        exit();
    }
    
    // Handle file upload jika ada file baru
    $foto_barang = '';
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['foto_barang']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['foto_barang']['size'];
        $file_tmp = $_FILES['foto_barang']['tmp_name'];
        
        // Validasi ekstensi file
        if (!in_array($file_ext, $allowed_ext)) {
            $_SESSION['error'] = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
            header("Location: barang.php?action=edit&id=$id");
            exit();
        }
        
        // Validasi ukuran file (max 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran file maksimal 2MB.";
            header("Location: barang.php?action=edit&id=$id");
            exit();
        }
        
        // Generate unique filename
        $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
        $upload_path = 'assets/uploads/' . $new_filename;
        
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $foto_barang = $new_filename;
            
            // Hapus foto lama jika ada
            $query_old = "SELECT foto_barang FROM barang WHERE id = '$id'";
            $result_old = mysqli_query($koneksi, $query_old);
            $old_file = mysqli_fetch_assoc($result_old)['foto_barang'];
            
            if (!empty($old_file) && file_exists('assets/uploads/' . $old_file)) {
                unlink('assets/uploads/' . $old_file);
            }
        } else {
            $_SESSION['error'] = "Gagal mengupload file.";
            header("Location: barang.php?action=edit&id=$id");
            exit();
        }
    }
    
    // Update barang
    if (!empty($foto_barang)) {
        $query = "UPDATE barang SET 
                  kode_barang = '$kode_barang', 
                  nama_barang = '$nama_barang',
                  kategori = '$kategori',
                  deskripsi = '$deskripsi',
                  status = '$status',
                  foto_barang = '$foto_barang'
                  WHERE id = '$id'";
    } else {
        $query = "UPDATE barang SET 
                  kode_barang = '$kode_barang', 
                  nama_barang = '$nama_barang',
                  kategori = '$kategori',
                  deskripsi = '$deskripsi',
                  status = '$status'
                  WHERE id = '$id'";
    }
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Barang berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui barang: " . mysqli_error($koneksi);
    }
    
    header("Location: barang.php");
    exit();
}

// DELETE BARANG
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['delete']);
    
    // Cek apakah barang memiliki data peminjaman
    $check_query = "SELECT COUNT(*) as total FROM peminjaman WHERE barang_id = '$id'";
    $check_result = mysqli_query($koneksi, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['total'] > 0) {
        $_SESSION['error'] = "Barang tidak dapat dihapus karena memiliki data peminjaman!";
        header("Location: barang.php");
        exit();
    }
    
    // Hapus foto jika ada
    $query_file = "SELECT foto_barang FROM barang WHERE id = '$id'";
    $result_file = mysqli_query($koneksi, $query_file);
    $file_row = mysqli_fetch_assoc($result_file);
    
    if (!empty($file_row['foto_barang']) && file_exists('assets/uploads/' . $file_row['foto_barang'])) {
        unlink('assets/uploads/' . $file_row['foto_barang']);
    }
    
    // Hapus barang
    $query = "DELETE FROM barang WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Barang berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus barang: " . mysqli_error($koneksi);
    }
    
    header("Location: barang.php");
    exit();
}

// Default redirect
header("Location: barang.php");
exit();
?>