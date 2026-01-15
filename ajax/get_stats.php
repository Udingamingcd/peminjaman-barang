<?php
// ajax/get_stats.php
require_once '../config/koneksi.php';

$response = ['success' => false];

try {
    $query_stats = "
        SELECT 
            (SELECT COUNT(*) FROM barang) as total_barang,
            (SELECT COUNT(*) FROM mahasiswa) as total_mahasiswa,
            (SELECT COUNT(*) FROM peminjaman) as total_peminjaman,
            (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admin
    ";
    $result_stats = mysqli_query($koneksi, $query_stats);
    $stats = mysqli_fetch_assoc($result_stats);
    
    $response['success'] = true;
    $response['total_barang'] = $stats['total_barang'];
    $response['total_mahasiswa'] = $stats['total_mahasiswa'];
    $response['total_peminjaman'] = $stats['total_peminjaman'];
    $response['total_admin'] = $stats['total_admin'];
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>