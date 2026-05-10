<?php
session_start();
require '../config/session_config.php';

// Halaman ini bisa diakses Kasir (dan Admin jika menggantikan) — memakai sesi per-tab
$user_session = get_tab_session();
if (!$user_session) {
    header("Location: ../auth/login.php");
    exit;
}

// Dapatkan token tab untuk digunakan di JS (jika ada)
$tab_token = get_tab_token();

include '../includes/header.php';
require '../config/koneksi.php';
?>

<!-- Navigasi Khusus Kasir (Lebih ringkas agar area kasir luas) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">POS KEBAB - KASIR</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Petugas: <?php echo $user_session['nama_lengkap']; ?></span>
            <?php if ($user_session['role'] == 'admin'): ?>
            <?php endif; ?>
            <a href="../auth/logout.php?role=<?= $user_session['role'] ?><?= $tab_token ? '&tab=' . $tab_token : '' ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Area Kiri: Daftar Menu & Topping -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white fw-bold">Pilih Menu Kebab</div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Hanya tampilkan produk yang stoknya Tersedia, dan gabungkan dengan promo aktif (jika ada)
                        $tgl_sekarang = date('Y-m-d');
                        $query_produk = mysqli_query($koneksi, "
                            SELECT p.*, pr.id_promo, pr.diskon_persen, pr.diskon_nominal 
                            FROM produk p 
                            LEFT JOIN promo pr ON p.id_produk = pr.id_produk 
                                               AND pr.status = 'aktif' 
                                               AND '$tgl_sekarang' >= pr.tanggal_mulai 
                                               AND '$tgl_sekarang' <= pr.tanggal_akhir
                            WHERE p.status_stok = 'Tersedia' 
                            ORDER BY p.id_produk DESC
                        ");

                        while ($p = mysqli_fetch_assoc($query_produk)):
                            $harga_asli = $p['harga_jual'];
                            $harga_final = $harga_asli;
                            $ada_promo = false;

                            // Jika ada promo, hitung harga final
                            if (!empty($p['id_promo'])) {
                                $ada_promo = true;
                                if (!empty($p['diskon_persen'])) {
                                    $harga_final = $harga_asli - ($harga_asli * $p['diskon_persen'] / 100);
                                } elseif (!empty($p['diskon_nominal'])) {
                                    $harga_final = $harga_asli - $p['diskon_nominal'];
                                }
                                // Pastikan harga tidak minus
                                if ($harga_final < 0) $harga_final = 0;
                            }
                        ?>
                            <div class="col-md-3 mb-3">
                                <div class="card p-2 border-primary h-100" style="cursor: pointer;" onclick="pilihMenu(<?= $p['id_produk']; ?>, '<?= $p['nama_produk']; ?>', <?= $harga_final; ?>)">
                                    <h6 class="mb-1">
                                        <?= $p['nama_produk']; ?>
                                        <?php if ($ada_promo): ?>
                                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">PROMO</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted"><?= $p['kategori']; ?></small>
                                    <div class="mt-auto pt-2">
                                        <?php if ($ada_promo): ?>
                                            <small class="text-decoration-line-through text-muted d-block" style="font-size: 0.75rem;">Rp <?= number_format($harga_asli, 0, ',', '.'); ?></small>
                                        <?php endif; ?>
                                        <p class="text-success fw-bold mb-0">Rp <?= number_format($harga_final, 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- SECTION BUNDLING PROMO -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fas fa-gift"></i> Paket Bundling (Hemat!)
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Tampilkan bundling yang aktif
                        $tgl_sekarang = date('Y-m-d');
                        $query_bundling = mysqli_query($koneksi, "
                            SELECT 
                                b.*,
                                p1.nama_produk AS nama_produk1,
                                p1.harga_jual AS harga_produk1,
                                p2.nama_produk AS nama_produk2,
                                p2.harga_jual AS harga_produk2,
                                p3.nama_produk AS nama_produk_gratis,
                                p3.harga_jual AS harga_produk_gratis
                            FROM promo_bundling b
                            LEFT JOIN produk p1 ON b.id_produk1 = p1.id_produk
                            LEFT JOIN produk p2 ON b.id_produk2 = p2.id_produk
                            LEFT JOIN produk p3 ON b.id_produk_gratis = p3.id_produk
                            WHERE b.status = 'aktif' 
                            AND '$tgl_sekarang' >= b.tanggal_mulai 
                            AND '$tgl_sekarang' <= b.tanggal_akhir
                            ORDER BY b.id_bundling DESC
                        ");

                        if (mysqli_num_rows($query_bundling) > 0) {
                            while ($b = mysqli_fetch_assoc($query_bundling)):
                                // Hitung harga bundling
                                $total_harga_bundling = ($b['harga_produk1'] * $b['jumlah_produk1']) + 
                                                       ($b['harga_produk2'] * $b['jumlah_produk2']);
                                $harga_gratis = $b['harga_produk_gratis'] * $b['jumlah_produk_gratis'];
                                $hemat = $harga_gratis;
                        ?>
                            <div class="col-md-4 mb-3">
                                <div class="card p-3 border-success h-100" style="cursor: pointer; border-width: 2px;" 
                                     onclick="pilihBundling(<?= $b['id_bundling']; ?>, '<?= addslashes($b['nama_bundling']); ?>', <?= $b['id_produk1']; ?>, <?= $b['jumlah_produk1']; ?>, <?= $b['id_produk2']; ?>, <?= $b['jumlah_produk2']; ?>, <?= $b['id_produk_gratis']; ?>, <?= $b['jumlah_produk_gratis']; ?>, '<?= addslashes($b['nama_produk_gratis']); ?>')">
                                    <h6 class="mb-2 text-success fw-bold">
                                        <i class="fas fa-star"></i> <?= htmlspecialchars($b['nama_bundling']); ?>
                                    </h6>
                                    <small class="text-muted d-block mb-2">
                                        • <?= $b['jumlah_produk1']; ?>x <?= htmlspecialchars($b['nama_produk1']); ?><br>
                                        • <?= $b['jumlah_produk2']; ?>x <?= htmlspecialchars($b['nama_produk2']); ?><br>
                                        <span class="badge bg-success">FREE <?= $b['jumlah_produk_gratis']; ?>x <?= htmlspecialchars($b['nama_produk_gratis']); ?></span>
                                    </small>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-danger">
                                            <i class="fas fa-save"></i> Hemat Rp <?= number_format($hemat, 0, ',', '.'); ?>
                                        </small>
                                    </div>
                                    <p class="fw-bold text-success mb-0" style="font-size: 1.1rem;">
                                        Rp <?= number_format($total_harga_bundling, 0, ',', '.'); ?>
                                    </p>
                                </div>
                            <?php 
                            endwhile;
                        } else {
                            echo '<div class="col-12"><p class="text-muted text-center"><i class="fas fa-inbox"></i> Tidak ada paket bundling aktif saat ini</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning fw-bold">Pilih Topping (Opsional - Centang sebelum pilih menu)</div>
                <div class="card-body" id="topping-area">
                    <div class="row">
                        <?php
                        $query_topping = mysqli_query($koneksi, "SELECT * FROM topping ORDER BY id_topping DESC");
                        while ($t = mysqli_fetch_assoc($query_topping)):
                        ?>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input topping-item" type="checkbox" value="<?= $t['nama_topping']; ?>" data-harga="<?= $t['harga_tambahan']; ?>" id="top<?= $t['id_topping']; ?>">
                                    <label class="form-check-label" for="top<?= $t['id_topping']; ?>">
                                        <?= $t['nama_topping']; ?> (+Rp <?= number_format($t['harga_tambahan'], 0, ',', '.'); ?>)
                                    </label>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Area Kanan: Keranjang Belanja -->
        <div class="col-md-4">
            <div class="card shadow-sm border-dark">
                <div class="card-header bg-dark text-white fw-bold">Keranjang Pesanan</div>
                <div class="card-body">
                    <ul class="list-group mb-3" id="cart-list" style="max-height: 300px; overflow-y: auto;">
                        <!-- Item keranjang dirender oleh JS -->
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <h5>Total Tagihan:</h5>
                        <h4 class="fw-bold text-primary" id="total-tagihan">Rp 0</h4>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Uang Masuk (Rp)</label>
                        <input type="number" class="form-control form-control-lg" id="uang-masuk" oninput="hitungKembalian()" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Kembalian (Rp)</label>
                        <input type="text" class="form-control form-control-lg bg-light fw-bold" id="kembalian" readonly value="Rp 0">
                    </div>

                    <button class="btn btn-success w-100 btn-lg fw-bold" onclick="prosesTransaksi()" id="btn-proses">Proses Pembayaran</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const TAB_TOKEN = '<?= $tab_token ?? '' ?>';
    let cart = [];
    let grandTotal = 0;

    function pilihBundling(id_bundling, nama_bundling, id_produk1, qty1, id_produk2, qty2, id_produk_gratis, qty_gratis, nama_produk_gratis) {
        // Ambil harga produk dari DOM (cari di menu produk)
        // Karena kita tidak punya info harga di bundling, kita perlu fetch dari server
        
        fetch('../proses/get_bundling_info.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_bundling=' + id_bundling
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'sukses') {
                const bundlingData = data.bundling;
                
                // Tambahkan produk 1
                cart.push({
                    id_produk: bundlingData.id_produk1,
                    nama: bundlingData.nama_produk1,
                    topping: '',
                    harga_satuan: bundlingData.harga_produk1,
                    jumlah: bundlingData.jumlah_produk1,
                    subtotal: bundlingData.harga_produk1 * bundlingData.jumlah_produk1,
                    bundling: nama_bundling
                });
                
                // Tambahkan produk 2
                cart.push({
                    id_produk: bundlingData.id_produk2,
                    nama: bundlingData.nama_produk2,
                    topping: '',
                    harga_satuan: bundlingData.harga_produk2,
                    jumlah: bundlingData.jumlah_produk2,
                    subtotal: bundlingData.harga_produk2 * bundlingData.jumlah_produk2,
                    bundling: nama_bundling
                });
                
                // Tambahkan produk gratis
                cart.push({
                    id_produk: bundlingData.id_produk_gratis,
                    nama: bundlingData.nama_produk_gratis,
                    topping: '',
                    harga_satuan: 0,
                    jumlah: bundlingData.jumlah_produk_gratis,
                    subtotal: 0,
                    bundling: nama_bundling + ' (FREE)',
                    keterangan: 'GRATIS - ' + nama_bundling
                });
                
                renderCart();
                alert('✅ Paket ' + nama_bundling + ' ditambahkan ke keranjang!');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function pilihMenu(id_produk, nama, harga) {
        let toppingTerpilih = [];
        let totalHargaTopping = 0;

        // Ambil topping yang dicentang
        document.querySelectorAll('.topping-item:checked').forEach(item => {
            toppingTerpilih.push(item.value);
            totalHargaTopping += parseInt(item.getAttribute('data-harga'));
            item.checked = false; // Reset checkbox
        });

        let subtotal = harga + totalHargaTopping;
        let catatanTopping = toppingTerpilih.join(', ');

        // Cek apakah item dengan produk & topping yang sama persis sudah ada di keranjang
        let indexSama = cart.findIndex(item => item.id_produk === id_produk && item.topping === catatanTopping);

        if (indexSama !== -1) {
            // Jika ada, tambah jumlahnya (qty)
            cart[indexSama].jumlah += 1;
            cart[indexSama].subtotal += subtotal;
        } else {
            // Jika belum, masukkan sebagai item baru
            cart.push({
                id_produk: id_produk,
                nama: nama,
                topping: catatanTopping,
                harga_satuan: harga + totalHargaTopping, // Harga satuan sudah termasuk topping
                jumlah: 1,
                subtotal: subtotal
            });
        }
        renderCart();
    }

    function renderCart() {
        let cartList = document.getElementById('cart-list');
        cartList.innerHTML = '';
        grandTotal = 0;

        cart.forEach((item, index) => {
            grandTotal += item.subtotal;
            let toppingText = item.topping ? `<br><small class="text-muted">+ ${item.topping}</small>` : '';
            let badgeGratis = item.keterangan ? `<br><span class="badge bg-success">${item.keterangan}</span>` : '';

            cartList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">${item.nama} x${item.jumlah}</h6>
                        ${toppingText}
                        ${badgeGratis}
                        <br><small class="text-danger" onclick="hapusItem(${index})" style="cursor:pointer;">Hapus</small>
                    </div>
                    <span class="text-muted fw-bold">Rp ${item.subtotal.toLocaleString('id-ID')}</span>
                </li>
            `;
        });

        // Cek bundling otomatis
        cekBundling();

        document.getElementById('total-tagihan').innerText = `Rp ${grandTotal.toLocaleString('id-ID')}`;
        hitungKembalian();
    }

    function cekBundling() {
        // FormData untuk mengirim keranjang ke server
        let formData = new FormData();
        formData.append('keranjang', JSON.stringify(cart));

        fetch('../proses/cek_bundling_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'sukses' && data.bundling_terdeteksi.length > 0) {
                // Hapus produk gratis lama jika ada
                cart = cart.filter(item => !item.keterangan || !item.keterangan.includes('GRATIS'));

                // Tampilkan notifikasi bundling
                let notif = 'Bundling Terdeteksi: ';
                data.bundling_terdeteksi.forEach(b => {
                    notif += `\n✓ ${b.nama_bundling} (${b.kali_bundling}x)`;
                });
                console.log(notif);

                // Tambahkan produk gratis ke keranjang
                data.produk_gratis.forEach(produk => {
                    cart.push({
                        id_produk: produk.id_produk,
                        nama: produk.nama_produk,
                        topping: '',
                        harga_satuan: 0,
                        jumlah: produk.jumlah,
                        subtotal: 0,
                        keterangan: produk.keterangan
                    });
                });

                // Re-render keranjang
                let cartList = document.getElementById('cart-list');
                cartList.innerHTML = '';
                grandTotal = 0;

                cart.forEach((item, index) => {
                    grandTotal += item.subtotal;
                    let toppingText = item.topping ? `<br><small class="text-muted">+ ${item.topping}</small>` : '';
                    let badgeGratis = item.keterangan ? `<br><span class="badge bg-success">${item.keterangan}</span>` : '';

                    cartList.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0">${item.nama} x${item.jumlah}</h6>
                                ${toppingText}
                                ${badgeGratis}
                                <br><small class="text-danger" onclick="hapusItem(${index})" style="cursor:pointer;">Hapus</small>
                            </div>
                            <span class="text-muted fw-bold">Rp ${item.subtotal.toLocaleString('id-ID')}</span>
                        </li>
                    `;
                });

                document.getElementById('total-tagihan').innerText = `Rp ${grandTotal.toLocaleString('id-ID')}`;
                hitungKembalian();

                // Tampilkan alert bundling
                alert('🎉 ' + notif);
            }
        })
        .catch(error => console.error('Error cek bundling:', error));
    }

    function hapusItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function hitungKembalian() {
        let uangMasuk = parseInt(document.getElementById('uang-masuk').value) || 0;
        let kembalian = uangMasuk - grandTotal;

        let elemKembalian = document.getElementById('kembalian');
        if (kembalian >= 0 && uangMasuk > 0) {
            elemKembalian.value = `Rp ${kembalian.toLocaleString('id-ID')}`;
            elemKembalian.classList.replace('text-danger', 'text-success');
        } else if (uangMasuk > 0 && kembalian < 0) {
            elemKembalian.value = 'Uang Kurang!';
            elemKembalian.classList.add('text-danger');
            elemKembalian.classList.remove('text-success');
        } else {
            elemKembalian.value = 'Rp 0';
            elemKembalian.classList.remove('text-danger', 'text-success');
        }
    }

    function prosesTransaksi() {
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        let uangMasuk = parseInt(document.getElementById('uang-masuk').value) || 0;
        if (uangMasuk < grandTotal) return alert('Uang pembayaran tidak mencukupi!');

        let kembalian = uangMasuk - grandTotal;

        // Ubah tombol jadi loading agar tidak di-klik 2 kali
        let btnProses = document.getElementById('btn-proses');
        btnProses.innerText = 'Memproses...';
        btnProses.disabled = true;

        // Susun data yang akan dikirim ke backend
        let payload = {
            total_bayar: grandTotal,
            uang_masuk: uangMasuk,
            kembalian: kembalian,
            keranjang: cart
        };

        // Kirim data via AJAX (Fetch API)
        let fetchUrl = '../proses/transaksi_ajax.php' + (TAB_TOKEN ? '?tab=' + TAB_TOKEN : '');
        fetch(fetchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sukses') {
                    alert('Transaksi Berhasil!');
                    // Arahkan ke halaman cetak struk membawa nomor struk
                    window.location.href = `struk.php?nota=${data.nomor_struk}`;
                } else {
                    alert('Gagal: ' + data.pesan);
                    btnProses.innerText = 'Proses Pembayaran';
                    btnProses.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan.');
                btnProses.innerText = 'Proses Pembayaran';
                btnProses.disabled = false;
            });
    }
</script>

<?php include '../includes/footer.php'; ?>