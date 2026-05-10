# 🎉 PANDUAN SETUP FITUR PROMO BUNDLING

## Daftar File yang Ditambahkan:

1. **`DATABASE_BUNDLING.sql`** - Script SQL untuk membuat tabel
2. **`admin/master_bundling.php`** - Halaman admin untuk mengelola bundling
3. **`proses/promo_bundling_aksi.php`** - File proses CRUD bundling
4. **`proses/cek_bundling_ajax.php`** - File untuk deteksi bundling di transaksi

---

## LANGKAH 1: Buat Tabel di Database

Jalankan SQL berikut di **phpMyAdmin** atau **MySQL Console**:

```sql
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
```

---

## LANGKAH 2: Akses Halaman Master Bundling

1. Login sebagai **Admin**
2. Di sidebar, klik menu **"Promo Bundling"** (ikon kotak)
3. Akan dibuka halaman untuk mengelola bundling promo

---

## LANGKAH 3: Membuat Bundling Promo

**Contoh: Buy 2 Get 1 Kebab Daging + Minuman Gratis**

1. Klik tombol **"+ Tambah Bundling"**
2. Isi form:
   - **Nama Bundling**: `Buy 2 Get 1 - Kebab + Minuman`
   - **Produk Pertama**: Pilih "Kebab Daging", Jumlah: `2`
   - **Produk Kedua**: Pilih "Minuman Es Teh", Jumlah: `1`
   - **Produk Gratis**: Pilih "Kebab Ayam", Jumlah: `1`
   - **Tanggal Mulai**: Pilih tanggal awal promo
   - **Tanggal Akhir**: Pilih tanggal akhir promo
   - **Status**: Pilih "Aktif"
3. Klik **"Tambah Bundling"**

---

## LANGKAH 4: Cara Kerja Bundling di Kasir

Ketika **kasir memasukkan produk ke keranjang**:

✅ Sistem otomatis mendeteksi bundling yang cocok
✅ Jika syarat bundling terpenuhi, **produk gratis otomatis ditambahkan** ke keranjang dengan badge hijau "GRATIS"
✅ **Total harga tidak termasuk produk gratis** (harga = 0)
✅ Menampilkan notifikasi bundling yang terdeteksi

### Contoh Skenario:
- Kasir input: **2 x Kebab Daging** + **1 x Minuman Es Teh**
- Sistem mendeteksi bundling terpenuhi → otomatis tambah **1 x Kebab Ayam (Gratis)**
- Customer membayar sesuai harga tanpa produk gratis

---

## LANGKAH 5: Edit/Hapus Bundling

**Edit Bundling:**
1. Buka halaman "Promo Bundling"
2. Klik tombol **"Edit"** pada bundling yang ingin diubah
3. Ubah data sesuai kebutuhan
4. Klik **"Simpan Perubahan"**

**Hapus Bundling:**
1. Buka halaman "Promo Bundling"
2. Klik tombol **"Hapus"** pada bundling yang ingin dihapus
3. Konfirmasi penghapusan

---

## 🔧 FITUR TEKNIS

### Cara Kerja Deteksi Bundling:
1. Setiap kali **keranjang berubah**, sistem otomatis memanggil `cek_bundling_ajax.php`
2. File tersebut:
   - Mengambil semua bundling yang **aktif** dan dalam **rentang tanggal**
   - Mencocokan produk yang ada di keranjang dengan syarat bundling
   - Jika syarat terpenuhi, menghitung berapa kali bundling terpenuhi
   - Menambahkan produk gratis ke keranjang dengan subtotal = 0

### Logika Bundling:
```
Syarat bundling terpenuhi JIKA:
- Jumlah Produk1 ≥ Jumlah yang disyaratkan
- Jumlah Produk2 ≥ Jumlah yang disyaratkan
- Tanggal hari ini berada dalam rentang promo

Kali bundling = MIN(
    floor(Qty Produk1 / Jumlah Produk1),
    floor(Qty Produk2 / Jumlah Produk2)
)

Produk Gratis = Kali bundling × Jumlah Produk Gratis
```

---

## ✅ CHECKLIST IMPLEMENTASI

- [x] Tabel database dibuat
- [x] Menu "Promo Bundling" ditambahkan di navbar admin
- [x] Halaman master bundling (CRUD) dibuat
- [x] File proses CRUD bundling dibuat
- [x] Logika deteksi bundling di kasir dibuat
- [x] Produk gratis otomatis ditambahkan ke keranjang
- [x] Total harga sudah memperhitungkan produk gratis

---

## 📝 CONTOH BUNDLING LAINNYA

### Promo 1: Beli Paket Hemat
- 1 x Kebab Daging + 1 x Kebab Ayam → Free 1 x Minuman

### Promo 2: Buy 3 Get 1
- 1 x Kebab Daging + 2 x Kebab Spesial → Free 1 x Kebab Ayam

### Promo 3: Combo Keluarga
- 2 x Kebab Besar + 3 x Minuman → Free 1 x Kebab Tahu

---

## 🔒 KEAMANAN

- ✅ Query menggunakan `mysqli_real_escape_string()` untuk mencegah SQL Injection
- ✅ Cek session admin sebelum akses halaman
- ✅ Data bundling hanya bisa diubah oleh admin
- ✅ Foreign key constraints untuk integritas data

---

## 📞 SUPPORT

Jika ada pertanyaan atau masalah, silakan cek:
1. Apakah tabel `promo_bundling` sudah dibuat di database?
2. Apakah bundling statusnya "Aktif"?
3. Apakah tanggal promo masih berlaku?
4. Apakah produk tersedia di database?

---

**Fitur Promo Bundling siap digunakan! 🎉**
