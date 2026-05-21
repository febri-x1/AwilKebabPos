-- SQL migration: tambahkan kolom allow_topping pada tabel produk
-- Jalankan di phpMyAdmin atau mysql client jika ingin tanpa PHP runner

ALTER TABLE produk
ADD COLUMN allow_topping ENUM('ya','tidak') DEFAULT 'ya';
