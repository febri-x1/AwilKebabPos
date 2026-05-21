<?php
function ensure_promo_bundling_table($koneksi)
{
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'promo_bundling'");
    if ($check_table && mysqli_num_rows($check_table) > 0) {
        return;
    }

    $create_table_sql = "CREATE TABLE IF NOT EXISTS `promo_bundling` (
        `id_bundling` int(11) NOT NULL AUTO_INCREMENT,
        `nama_bundling` varchar(100) NOT NULL,
        `id_produk1` int(11) NOT NULL,
        `jumlah_produk1` int(11) NOT NULL DEFAULT 2,
        `id_produk2` int(11) NOT NULL,
        `jumlah_produk2` int(11) NOT NULL DEFAULT 2,
        `id_produk_gratis` int(11) NOT NULL,
        `jumlah_produk_gratis` int(11) NOT NULL DEFAULT 1,
        `tanggal_mulai` date NOT NULL,
        `tanggal_akhir` date NOT NULL,
        `status` enum('aktif','nonaktif') DEFAULT 'aktif',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_bundling`),
        KEY `fk_produk1` (`id_produk1`),
        KEY `fk_produk2` (`id_produk2`),
        KEY `fk_produk_gratis` (`id_produk_gratis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    mysqli_query($koneksi, $create_table_sql);
}

function nonaktifkan_promo_bundling_kadaluarsa($koneksi)
{
    ensure_promo_bundling_table($koneksi);
    $tgl_sekarang = date('Y-m-d');

    return mysqli_query($koneksi, "
        UPDATE promo_bundling
        SET status = 'nonaktif'
        WHERE status = 'aktif'
        AND tanggal_akhir < '$tgl_sekarang'
    ");
}

function promo_bundling_sudah_ada($koneksi, $nama_bundling, $id_produk1, $jumlah_produk1, $id_produk2, $jumlah_produk2, $id_produk_gratis, $jumlah_produk_gratis, $exclude_id = 0)
{
    ensure_promo_bundling_table($koneksi);

    $nama_bundling = mysqli_real_escape_string($koneksi, trim($nama_bundling));
    $id_produk1 = intval($id_produk1);
    $jumlah_produk1 = intval($jumlah_produk1);
    $id_produk2 = intval($id_produk2);
    $jumlah_produk2 = intval($jumlah_produk2);
    $id_produk_gratis = intval($id_produk_gratis);
    $jumlah_produk_gratis = intval($jumlah_produk_gratis);
    $exclude_id = intval($exclude_id);

    $exclude_sql = $exclude_id > 0 ? "AND id_bundling != '$exclude_id'" : "";
    $query = mysqli_query($koneksi, "
        SELECT id_bundling
        FROM promo_bundling
        WHERE (
            LOWER(TRIM(nama_bundling)) = LOWER('$nama_bundling')
            OR (
                id_produk_gratis = '$id_produk_gratis'
                AND jumlah_produk_gratis = '$jumlah_produk_gratis'
                AND (
                    (
                        id_produk1 = '$id_produk1'
                        AND jumlah_produk1 = '$jumlah_produk1'
                        AND id_produk2 = '$id_produk2'
                        AND jumlah_produk2 = '$jumlah_produk2'
                    )
                    OR (
                        id_produk1 = '$id_produk2'
                        AND jumlah_produk1 = '$jumlah_produk2'
                        AND id_produk2 = '$id_produk1'
                        AND jumlah_produk2 = '$jumlah_produk1'
                    )
                )
            )
        )
        $exclude_sql
        LIMIT 1
    ");

    return !$query || mysqli_num_rows($query) > 0;
}

function promo_bundling_punya_duplikat($koneksi)
{
    ensure_promo_bundling_table($koneksi);

    $query = mysqli_query($koneksi, "
        SELECT id_bundling
        FROM promo_bundling
        GROUP BY LOWER(TRIM(nama_bundling)), id_produk1, jumlah_produk1, id_produk2, jumlah_produk2, id_produk_gratis, jumlah_produk_gratis
        HAVING COUNT(*) > 1
        LIMIT 1
    ");

    return $query && mysqli_num_rows($query) > 0;
}
?>
