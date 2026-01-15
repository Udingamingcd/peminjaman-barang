<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $query = "SELECT *, 
              DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as created_at,
              DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i') as updated_at
              FROM barang WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
}
?>