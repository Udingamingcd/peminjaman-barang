<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['can_delete' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    
    // Cek apakah barang sedang dipinjam
    $query = "SELECT COUNT(*) as total FROM peminjaman 
              WHERE barang_id = '$id' AND status = 'dipinjam'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['total'] > 0) {
        echo json_encode(['can_delete' => false, 'message' => 'Barang sedang dipinjam']);
    } else {
        // Cek total riwayat peminjaman
        $query_total = "SELECT COUNT(*) as total FROM peminjaman WHERE barang_id = '$id'";
        $result_total = mysqli_query($koneksi, $query_total);
        $row_total = mysqli_fetch_assoc($result_total);
        
        echo json_encode([
            'can_delete' => true, 
            'total_riwayat' => $row_total['total']
        ]);
    }
} else {
    echo json_encode(['can_delete' => false, 'message' => 'ID tidak valid']);
}
?>