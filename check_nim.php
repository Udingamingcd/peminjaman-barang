<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

if (isset($_POST['nim'])) {
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $query = "SELECT COUNT(*) as count FROM mahasiswa WHERE nim = '$nim'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    echo json_encode(['exists' => $row['count'] > 0]);
} else {
    echo json_encode(['exists' => false]);
}
?>