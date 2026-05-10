-- ===================================
-- SQL untuk membuat tabel promo_bundling
-- Jalankan query ini di phpMyAdmin atau MySQL
-- ===================================

CREATE TABLE IF NOT EXISTS `promo_bundling` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
