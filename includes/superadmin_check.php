<?php
require_once 'config/koneksi.php';

// Cek apakah sudah ada superadmin di database
$query = "SELECT COUNT(*) as count FROM users WHERE role = 'superadmin'";
$result = mysqli_query($koneksi, $query);
$row = mysqli_fetch_assoc($result);

$superadmin_exists = ($row['count'] > 0);
?>