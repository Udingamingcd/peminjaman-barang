<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $query = "SELECT * FROM mahasiswa WHERE id = '$id'";
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