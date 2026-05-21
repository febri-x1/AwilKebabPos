<?php
function nonaktifkan_promo_produk_kadaluarsa($koneksi)
{
    $tgl_sekarang = date('Y-m-d');

    return mysqli_query($koneksi, "
        UPDATE promo
        SET status = 'nonaktif'
        WHERE status = 'aktif'
        AND tanggal_akhir < '$tgl_sekarang'
    ");
}

function pastikan_promo_produk_unik($koneksi)
{
    $index = mysqli_query($koneksi, "
        SHOW INDEX FROM promo
        WHERE Key_name = 'uniq_promo_id_produk'
    ");
    if ($index && mysqli_num_rows($index) > 0) {
        return true;
    }

    $duplikat = mysqli_query($koneksi, "
        SELECT id_produk
        FROM promo
        GROUP BY id_produk
        HAVING COUNT(*) > 1
        LIMIT 1
    ");
    if ($duplikat && mysqli_num_rows($duplikat) > 0) {
        return false;
    }

    return mysqli_query($koneksi, "
        ALTER TABLE promo
        ADD UNIQUE KEY uniq_promo_id_produk (id_produk)
    ");
}
?>
