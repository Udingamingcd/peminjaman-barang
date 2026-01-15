<?php
// error.php
$error_code = $_GET['code'] ?? '404';
$error_messages = [
    '403' => [
        'title' => 'Akses Ditolak',
        'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
        'icon' => 'fas fa-ban'
    ],
    '404' => [
        'title' => 'Halaman Tidak Ditemukan',
        'message' => 'Halaman yang Anda cari tidak ditemukan atau telah dipindahkan.',
        'icon' => 'fas fa-exclamation-circle'
    ],
    '500' => [
        'title' => 'Kesalahan Server',
        'message' => 'Terjadi kesalahan internal pada server. Silakan coba lagi nanti.',
        'icon' => 'fas fa-server'
    ]
];

$error = $error_messages[$error_code] ?? $error_messages['404'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= $error_code ?> - Sistem Peminjaman Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            max-width: 600px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 10px;
        }
        
        .error-title {
            font-size: 24px;
            color: #495057;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .btn-back {
            background: #4361ee;
            color: white;
            padding: 10px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #3f37c9;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="<?= $error['icon'] ?>"></i>
        </div>
        <div class="error-code"><?= $error_code ?></div>
        <div class="error-title"><?= $error['title'] ?></div>
        <div class="error-message"><?= $error['message'] ?></div>
        <div class="mt-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-back">
                    <i class="fas fa-sign-in-alt me-2"></i>Kembali ke Login
                </a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-outline-primary ms-2">
                <i class="fas fa-home me-2"></i>Home
            </a>
        </div>
        
        <?php if($error_code == '500'): ?>
        <div class="mt-4 text-start">
            <details>
                <summary class="text-muted">Informasi Teknis</summary>
                <div class="mt-2 p-3 bg-light rounded">
                    <small>
                        <strong>Waktu:</strong> <?= date('d/m/Y H:i:s') ?><br>
                        <strong>IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?><br>
                        <strong>Browser:</strong> <?= $_SERVER['HTTP_USER_AGENT'] ?? 'Tidak diketahui' ?>
                    </small>
                </div>
            </details>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>