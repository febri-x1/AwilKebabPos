<?php
// File temporary untuk membuat tabel promo_bundling
// Akses: http://localhost/AppsAwilKebab/create_table.php

require './config/koneksi.php';

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
    KEY `fk_produk1` (`id_produk1`),
    KEY `fk_produk2` (`id_produk2`),
    KEY `fk_produk_gratis` (`id_produk_gratis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

// Jika kunci asing belum ada, tambahkan
$alter_sql = "
ALTER TABLE `promo_bundling` 
ADD CONSTRAINT `fk_produk1` FOREIGN KEY (`id_produk1`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT `fk_produk2` FOREIGN KEY (`id_produk2`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT `fk_produk_gratis` FOREIGN KEY (`id_produk_gratis`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;
";

echo '<h2>Membuat Tabel promo_bundling...</h2>';

if (mysqli_query($koneksi, $sql)) {
    echo '<p style="color: green;"><strong>✅ Tabel berhasil dibuat!</strong></p>';
} else {
    echo '<p style="color: orange;"><strong>⚠️ Tabel sudah ada atau error: ' . mysqli_error($koneksi) . '</strong></p>';
}

// Coba tambah foreign key (jika belum ada)
@mysqli_query($koneksi, $alter_sql);

echo '<p><a href="admin/master_bundling.php">← Kembali ke Kelola Bundling</a></p>';
?>
