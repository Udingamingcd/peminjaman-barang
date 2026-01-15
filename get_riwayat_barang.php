<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['barang_id'])) {
    $barang_id = mysqli_real_escape_string($koneksi, $_GET['barang_id']);
    
    $query = "SELECT p.kode_peminjaman, m.nama as nama_mahasiswa, 
              DATE_FORMAT(p.tanggal_pinjam, '%d/%m/%Y') as tanggal_pinjam,
              p.status
              FROM peminjaman p
              JOIN mahasiswa m ON p.mahasiswa_id = m.id
              WHERE p.barang_id = '$barang_id'
              ORDER BY p.created_at DESC
              LIMIT 10";
    
    $result = mysqli_query($koneksi, $query);
    $data = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID barang tidak valid']);
}
?>