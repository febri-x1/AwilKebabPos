<?php
// File ini membantu membuat tabel promo_bundling secara otomatis
// Akses: http://localhost/AppsAwilKebab/setup_bundling.php

session_start();
require './config/session_config.php';
require './config/koneksi.php';

// Cek apakah user sudah login dan merupakan admin
$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    echo '<div class="alert alert-danger">❌ Akses ditolak. Hanya admin yang bisa mengakses halaman ini.</div>';
    echo '<p><a href="auth/login.php">← Kembali ke Login</a></p>';
    exit;
}

$pesan = '';
$status = '';

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    $sql = "CREATE TABLE IF NOT EXISTS `promo_bundling` (
        `id_bundling` int(11) NOT NULL AUTO_INCREMENT,
        `nama_bundling` varchar(100) NOT NULL COMMENT 'Nama bundling, misal: Buy 2 Get 1',
        `id_produk1` int(11) NOT NULL COMMENT 'Produk pertama yang harus dibeli',
        `jumlah_produk1` int(11) NOT NULL DEFAULT 2 COMMENT 'Jumlah produk pertama yang harus dibeli',
        `id_produk2` int(11) NOT NULL COMMENT 'Produk kedua yang harus dibeli',
        `jumlah_produk2` int(11) NOT NULL DEFAULT 2 COMMENT 'Jumlah produk kedua yang harus dibeli',
        `id_produk_gratis` int(11) NOT NULL COMMENT 'Produk yang gratis',
        `jumlah_produk_gratis` int(11) NOT NULL DEFAULT 1 COMMENT 'Jumlah produk gratis',
        `tanggal_mulai` date NOT NULL,
        `tanggal_akhir` date NOT NULL,
        `status` enum('aktif','nonaktif') DEFAULT 'aktif',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_bundling`),
        FOREIGN KEY (`id_produk1`) REFERENCES `produk`(`id_produk`),
        FOREIGN KEY (`id_produk2`) REFERENCES `produk`(`id_produk`),
        FOREIGN KEY (`id_produk_gratis`) REFERENCES `produk`(`id_produk`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if (mysqli_query($koneksi, $sql)) {
        $status = 'sukses';
        $pesan = '✅ Tabel promo_bundling berhasil dibuat!';
    } else {
        $status = 'gagal';
        $pesan = '❌ Error: ' . mysqli_error($koneksi);
    }
}

// Cek apakah tabel sudah ada
$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'promo_bundling'");
$table_exists = mysqli_num_rows($check_table) > 0;

include './includes/header.php';
?>

<div style="margin: 40px auto; max-width: 600px; padding: 20px;">
    <div class="card shadow-lg border-0">
        <div class="card-body p-5 text-center">
            <h2 class="mb-3 fw-bold">🎉 Setup Promo Bundling</h2>
            
            <?php if ($status === 'sukses'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($status === 'gagal'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="alert <?= $table_exists ? 'alert-success' : 'alert-warning' ?>" role="alert">
                <strong>Status Tabel:</strong><br>
                <?php if ($table_exists): ?>
                    ✅ Tabel `promo_bundling` sudah ada di database
                <?php else: ?>
                    ⚠️ Tabel `promo_bundling` belum dibuat
                <?php endif; ?>
            </div>

            <?php if (!$table_exists || isset($_POST['reset'])): ?>
                <form method="POST">
                    <p class="text-muted mb-4">Klik tombol di bawah untuk membuat tabel promo_bundling secara otomatis.</p>
                    <button type="submit" name="setup" value="1" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="fas fa-database"></i> Buat Tabel Sekarang
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <strong>✅ Setup Sudah Selesai!</strong><br>
                    Anda sekarang bisa menggunakan fitur Promo Bundling.
                </div>
                <p class="mb-3">
                    <a href="admin/dashboard.php" class="btn btn-success fw-bold">
                        <i class="fas fa-arrow-right"></i> Ke Dashboard Admin
                    </a>
                </p>
                <p>
                    <a href="admin/master_bundling.php" class="btn btn-primary fw-bold">
                        <i class="fas fa-cog"></i> Kelola Bundling Promo
                    </a>
                </p>

                <hr>
                <p class="text-muted small">
                    Lihat file <strong>SETUP_BUNDLING.md</strong> untuk panduan lengkap.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include './includes/footer.php';
?>
