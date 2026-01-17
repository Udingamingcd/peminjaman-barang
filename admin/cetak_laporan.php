<?php
// admin/cetak_laporan.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil parameter filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';

// Query data untuk cetak dengan join tabel users untuk admin
$where_conditions = [];
$params = [];

if ($filter_bulan) {
    $where_conditions[] = "MONTH(p.tanggal_pinjam) = ?";
    $params[] = $filter_bulan;
}

if ($filter_tahun) {
    $where_conditions[] = "YEAR(p.tanggal_pinjam) = ?";
    $params[] = $filter_tahun;
}

if ($filter_jenis != 'semua') {
    $where_conditions[] = "p.status = ?";
    $params[] = $filter_jenis;
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

$query = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, 
         b.nama_barang, b.kode_barang,
         u.nama_lengkap as admin_peminjam
         FROM peminjaman p
         JOIN mahasiswa m ON p.mahasiswa_id = m.id
         JOIN barang b ON p.barang_id = b.id
         JOIN users u ON p.admin_id = u.id
         $where_clause
         ORDER BY p.tanggal_pinjam DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($koneksi, $query);
}

// Hitung statistik
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as dikembalikan,
    SUM(CASE WHEN status = 'hilang' THEN 1 ELSE 0 END) as hilang
    FROM peminjaman p $where_clause";
    
$result_stats = mysqli_query($koneksi, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - SIPEMBAR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
        }
        .header h2, .header h4 {
            margin: 5px 0;
        }
        .info-cetak {
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        /* Container untuk scroll horizontal saat preview */
        @media screen {
            .table-container {
                overflow-x: auto;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
            }
        }
        
        @media print {
            .table-container {
                overflow-x: visible;
            }
            .no-print {
                display: none;
            }
            table {
                page-break-inside: avoid;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            min-width: 1000px; /* Lebar minimum untuk mencetak semua kolom */
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: normal;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Politeknik Negeri Padang</h2>
        <h4>SISTEM PEMINJAMAN BARANG (SIPEMBAR)</h4>
        <h3>LAPORAN PEMINJAMAN BARANG</h3>
    </div>
    
    <div class="info-cetak">
        <p><strong>Periode:</strong> 
            <?php 
            if($filter_bulan && $filter_tahun) {
                echo date('F Y', strtotime($filter_tahun.'-'.$filter_bulan.'-01'));
            } elseif($filter_tahun) {
                echo 'Tahun ' . $filter_tahun;
            } else {
                echo 'Semua Periode';
            }
            ?>
        </p>
        <p><strong>Status:</strong> <?php echo $filter_jenis == 'semua' ? 'Semua Status' : ucfirst($filter_jenis); ?></p>
        <p><strong>Tanggal Cetak:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Admin:</strong> <?php echo $_SESSION['nama_lengkap']; ?></p>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Barang</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Teknisi yang Memproses</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['kode_peminjaman']; ?></td>
                    <td><?php echo $row['nim']; ?></td>
                    <td><?php echo $row['nama_mahasiswa']; ?></td>
                    <td><?php echo $row['nama_barang']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                    <td><?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                            if($row['status'] == 'dipinjam') echo 'badge-warning';
                            elseif($row['status'] == 'dikembalikan') echo 'badge-success';
                            elseif($row['status'] == 'hilang') echo 'badge-danger';
                            else echo 'badge-info';
                            ?>
                        ">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <?php echo $row['admin_peminjam']; ?>
                        </span>
                    </td>
                    <td><?php echo $row['kondisi'] ? ucfirst($row['kondisi']) : '-'; ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="summary">
        <h4>Ringkasan:</h4>
        <p>Total Transaksi: <?php echo $stats['total']; ?></p>
        <p>Sudah Dikembalikan: <?php echo $stats['dikembalikan']; ?></p>
        <p>Barang Hilang: <?php echo $stats['hilang']; ?></p>
        <p><strong>Laporan ini mencakup informasi admin yang memproses setiap peminjaman</strong></p>
    </div>
    
    <div class="footer">
        <p>SIPEMBAR - Sistem Peminjaman Barang</p>
        <p>Politeknik Negeri Padang &copy; <?php echo date('Y'); ?></p>
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Cetak</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        <button onclick="togglePreview()" class="btn btn-info">Preview Tabel</button>
    </div>

    <script>
    function togglePreview() {
        const container = document.querySelector('.table-container');
        if (container.style.maxHeight) {
            container.style.maxHeight = null;
            container.style.overflow = 'visible';
        } else {
            container.style.maxHeight = '500px';
            container.style.overflow = 'auto';
        }
    }
    
    // Auto-close window after printing
    window.onafterprint = function() {
        setTimeout(function() {
            window.close();
        }, 1000);
    };
    </script>
</body>
</html>