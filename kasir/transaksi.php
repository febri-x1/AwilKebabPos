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
require_once '../config/promo_bundling_helper.php';
require_once '../config/promo_helper.php';

nonaktifkan_promo_produk_kadaluarsa($koneksi);
nonaktifkan_promo_bundling_kadaluarsa($koneksi);
?>

<!-- Navigasi Khusus Kasir (Lebih ringkas agar area kasir luas) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">POS KEBAB - KASIR</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Petugas: <?php echo $user_session['nama_lengkap']; ?></span>
        </div>
    </div>
</nav>

<!-- Styles: cart + sidebar + layout adjustments -->
<style>
    :root {
        --cart-top: 90px;
        /* jarak dari navbar */
        --cart-right: 20px;
        --cart-width: 340px;
        --cart-collapsed-width: 64px;
        --sidebar-width: 260px;
        --sidebar-left: 20px;
        --sidebar-top: 90px;
    }

    /* Posisi keranjang: fixed di kanan atas untuk layar desktop */
    .cart-fixed {
        position: fixed;
        top: var(--cart-top);
        right: var(--cart-right);
        width: var(--cart-width);
        z-index: 1050;
        transition: width .25s ease, right .25s ease, transform .15s ease;
    }

    /* Mode collapsed (minimize) */
    .cart-fixed.collapsed {
        width: var(--cart-collapsed-width);
        overflow: hidden;
    }

    .cart-fixed.collapsed .card-body {
        display: none;
    }

    .cart-fixed .card-header .title-text {
        display: inline-block;
    }

    .cart-fixed.collapsed .card-header .title-text {
        display: none;
    }

    /* Sidebar kiri */
    .kasir-sidebar {
        position: fixed;
        top: var(--sidebar-top);
        left: var(--sidebar-left);
        width: var(--sidebar-width);
        bottom: 20px;
        z-index: 1060;
        display: block;
    }

    /* Sembunyikan tombol logout yang mungkin muncul di navbar (kasir menggunakan sidebar logout) */
    .navbar .btn-danger {
        display: none !important;
    }

    @media (max-width: 991px) {
        .kasir-sidebar {
            position: static;
            width: 100%;
            margin-bottom: 12px;
        }
    }

    /* Beri ruang di sisi kanan dan kiri agar konten tidak tertutup oleh sidebar/keranjang */
    .kasir-container {
        padding-right: calc(var(--cart-width) + 40px);
        padding-left: calc(var(--sidebar-width) + 40px);
    }

    @media (max-width: 991px) {
        .kasir-container {
            padding-right: 0;
            padding-left: 0;
        }
    }
</style>

<!-- Sidebar Kiri: Riwayat Transaksi + Logout -->
<div class="kasir-sidebar">
    <div class="card shadow-sm h-100 d-flex flex-column">
        <div class="card-header bg-secondary text-white fw-bold">Riwayat Transaksi</div>
        <div class="p-2 border-bottom bg-light">
            <div class="input-group input-group-sm">
                <input type="text" id="cari-transaksi" class="form-control" placeholder="Cari struk, tanggal, produk..." oninput="cariTransaksi()">
                <button class="btn btn-outline-secondary" type="button" onclick="resetCariTransaksi()" title="Reset pencarian">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div id="history-list" class="card-body p-2 flex-grow-1" style="overflow:auto; max-height: calc(100vh - 220px);">
            <!-- History items populated by JS -->
        </div>
        <div class="card-footer bg-light">
            <a href="../auth/logout.php?role=<?= $user_session['role'] ?><?= $tab_token ? '&tab=' . $tab_token : '' ?>" class="btn btn-danger w-100" onclick="return konfirmasiLogout()">Logout</a>
        </div>
    </div>
</div>

<div class="container-fluid kasir-container">
    <div class="row">
        <!-- Area Kiri: Daftar Menu & Topping -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Pilih Menu Kebab</span>
                    <input type="text" id="cari-menu" class="form-control form-control-sm w-50" placeholder="Cari menu..." onkeyup="filterMenu()">
                </div>
                <div class="card-body">
                    <div class="row" id="daftar-menu">
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
                            <div class="col-md-3 mb-3 menu-item" data-nama="<?= htmlspecialchars(strtolower($p['nama_produk'])); ?>">
                                <div class="card p-2 border-primary h-100" style="cursor: pointer;" onclick="pilihMenu(<?= (int)$p['id_produk']; ?>, <?= htmlspecialchars(json_encode($p['nama_produk']), ENT_QUOTES, 'UTF-8'); ?>, <?= (float)$harga_final; ?>)">
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

                <div id="topping-area" class="d-none">
                    <?php
                    $query_topping = mysqli_query($koneksi, "SELECT * FROM topping ORDER BY id_topping DESC");
                    while ($t = mysqli_fetch_assoc($query_topping)):
                    ?>
                        <div class="topping-source"
                            data-nama="<?= htmlspecialchars($t['nama_topping'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-harga="<?= (int)$t['harga_tambahan']; ?>"></div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Area Kanan: Keranjang Belanja -->
            <div class="col-md-4 cart-fixed">
                <div class="card shadow-sm border-dark">
                    <div class="card-header bg-dark text-white fw-bold d-flex align-items-center justify-content-between">
                        <span class="title-text">Keranjang Pesanan</span>
                        <div>
                            <button id="toggle-cart" class="btn btn-sm btn-light" title="Sembunyikan / Tampilkan"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
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

                        <div class="d-flex gap-2">
                            <button class="btn btn-danger w-25 btn-lg fw-bold" onclick="kosongkanKeranjang()" title="Kosongkan Keranjang"><i class="fas fa-trash"></i></button>
                            <button class="btn btn-success w-75 btn-lg fw-bold" onclick="prosesTransaksi()" id="btn-proses">Proses Pembayaran</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const TAB_TOKEN = '<?= $tab_token ?? '' ?>';
        let cart = [];
        let grandTotal = 0;
        let searchTimer = null;

        function konfirmasiLogout() {
            if (cart.length > 0) {
                return confirm('Keranjang masih berisi transaksi. Yakin ingin logout? Transaksi yang belum diproses akan hilang.');
            }

            return confirm('Yakin ingin logout dari akun kasir?');
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function tambahTabToken(url) {
            if (!TAB_TOKEN) return url;
            return url + (url.includes('?') ? '&' : '?') + 'tab=' + encodeURIComponent(TAB_TOKEN);
        }

        function pilihBundling(id_bundling, nama_bundling, id_produk1, qty1, id_produk2, qty2, id_produk_gratis, qty_gratis, nama_produk_gratis) {
            // Ambil harga produk dari DOM (cari di menu produk)
            // Karena kita tidak punya info harga di bundling, kita perlu fetch dari server

            fetch('../proses/get_bundling_info.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
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
            window.__selectedProduct = {
                id: id_produk,
                nama: nama,
                harga: harga
            };

            const namaProduk = document.getElementById('modal-topping-produk-nama');
            const hargaProduk = document.getElementById('modal-topping-produk-harga');
            const listTopping = document.getElementById('modal-topping-list');

            namaProduk.innerText = nama;
            hargaProduk.innerText = `Harga menu: Rp ${Number(harga).toLocaleString('id-ID')}`;
            listTopping.innerHTML = '';

            const toppingSources = document.querySelectorAll('.topping-source');
            if (!toppingSources.length) {
                listTopping.innerHTML = '<p class="text-muted small mb-0">Belum ada topping tersedia.</p>';
            } else {
                toppingSources.forEach((item, index) => {
                    const namaTopping = item.getAttribute('data-nama') || '';
                    const hargaTopping = parseInt(item.getAttribute('data-harga')) || 0;
                    const toppingId = `modal-top-${index}`;

                    listTopping.innerHTML += `
                        <div class="form-check border rounded p-2 ps-5 mb-2">
                            <input class="form-check-input modal-topping-item" type="checkbox" id="${toppingId}" value="${escapeHtml(namaTopping)}" data-harga="${hargaTopping}">
                            <label class="form-check-label w-100" for="${toppingId}">
                                <span class="fw-bold">${escapeHtml(namaTopping)}</span>
                                <span class="text-muted small d-block">+Rp ${hargaTopping.toLocaleString('id-ID')}</span>
                            </label>
                        </div>
                    `;
                });
            }

            const toppingModal = new bootstrap.Modal(document.getElementById('modalTopping'));
            toppingModal.show();
        }

        function renderCart() {
            let cartList = document.getElementById('cart-list');
            cartList.innerHTML = '';
            grandTotal = 0;

            cart.forEach((item, index) => {
                grandTotal += item.subtotal;
                let toppingText = item.topping ? `<br><small class="text-muted">+ ${item.topping}</small>` : '';
                let badgeGratis = item.keterangan ? `<br><span class="badge bg-success">${item.keterangan}</span>` : '';

                let qtyControls = item.keterangan && item.keterangan.includes('GRATIS') ?
                    `<span class="badge bg-secondary">x${item.jumlah}</span>` :
                    `<button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ubahQty(${index}, -1)">-</button>
                 <span class="mx-2 fw-bold">${item.jumlah}</span>
                 <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ubahQty(${index}, 1)">+</button>`;

                cartList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">${item.nama}</h6>
                        <div class="mt-1 d-flex align-items-center">${qtyControls}</div>
                        ${toppingText}
                        ${badgeGratis}
                        <br><small class="text-danger mt-1 d-inline-block" onclick="hapusItem(${index})" style="cursor:pointer;"><i class="fas fa-trash"></i> Hapus</small>
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

                            let qtyControls = item.keterangan && item.keterangan.includes('GRATIS') ?
                                `<span class="badge bg-secondary">x${item.jumlah}</span>` :
                                `<button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ubahQty(${index}, -1)">-</button>
                         <span class="mx-2 fw-bold">${item.jumlah}</span>
                         <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ubahQty(${index}, 1)">+</button>`;

                            cartList.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0">${item.nama}</h6>
                                <div class="mt-1 d-flex align-items-center">${qtyControls}</div>
                                ${toppingText}
                                ${badgeGratis}
                                <br><small class="text-danger mt-1 d-inline-block" onclick="hapusItem(${index})" style="cursor:pointer;"><i class="fas fa-trash"></i> Hapus</small>
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

        function filterMenu() {
            let input = document.getElementById('cari-menu').value.toLowerCase();
            let items = document.querySelectorAll('.menu-item');
            items.forEach(item => {
                let nama = item.getAttribute('data-nama');
                if (nama.includes(input)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function ubahQty(index, delta) {
            let item = cart[index];
            if (item.keterangan && item.keterangan.includes('GRATIS')) return; // Produk gratis tidak bisa diubah manual

            let newQty = item.jumlah + delta;
            if (newQty > 0) {
                item.jumlah = newQty;
                item.subtotal = item.harga_satuan * newQty;
                renderCart();
            } else if (newQty === 0) {
                hapusItem(index);
            }
        }

        function kosongkanKeranjang() {
            if (cart.length > 0 && confirm('Yakin ingin membatalkan transaksi ini dan mengosongkan keranjang?')) {
                cart = [];
                document.getElementById('uang-masuk').value = '';
                renderCart();
            }
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
                        // Simpan ringkasan transaksi ke localStorage sebagai riwayat
                        try {
                            let history = JSON.parse(localStorage.getItem('kasir_history') || '[]');
                            history.unshift({
                                nota: data.nomor_struk,
                                total: grandTotal,
                                items: cart,
                                waktu: new Date().toISOString()
                            });
                            history = history.slice(0, 50); // batasi 50 item
                            localStorage.setItem('kasir_history', JSON.stringify(history));
                        } catch (e) {
                            console.warn('Gagal simpan history:', e);
                        }
                        renderHistory();
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

        // Toggle cart collapse (minimize)
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('toggle-cart');
            const cartElem = document.querySelector('.cart-fixed');
            if (toggle && cartElem) {
                toggle.addEventListener('click', function(e) {
                    cartElem.classList.toggle('collapsed');
                    // ganti ikon
                    const icon = toggle.querySelector('i');
                    if (cartElem.classList.contains('collapsed')) {
                        icon.classList.remove('fa-chevron-right');
                        icon.classList.add('fa-chevron-left');
                    } else {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-chevron-right');
                    }
                });
            }
            // Render history on load
            renderHistory();
        });

        function renderHistory() {
            const container = document.getElementById('history-list');
            if (!container) return;
            const inputCari = document.getElementById('cari-transaksi');
            if (inputCari && inputCari.value.trim() !== '') {
                cariTransaksi(true);
                return;
            }

            let history = [];
            try {
                history = JSON.parse(localStorage.getItem('kasir_history') || '[]');
            } catch (e) {
                history = [];
            }
            if (!history.length) {
                container.innerHTML = '<p class="text-muted small">Belum ada riwayat transaksi.</p>';
                return;
            }
            container.innerHTML = '';
            history.forEach(h => {
                const waktu = new Date(h.waktu).toLocaleString('id-ID');
                const itemCount = Array.isArray(h.items) ? h.items.reduce((s, i) => s + (i.jumlah || 0), 0) : 0;
                const el = document.createElement('div');
                el.className = 'mb-2 border-bottom pb-2';
                el.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <div><strong>${escapeHtml(h.nota)}</strong><br><small class="text-muted">${escapeHtml(waktu)}</small></div>
                        <div class="text-end">
                            <div class="fw-bold">Rp ${Number(h.total).toLocaleString('id-ID')}</div>
                            <small class="text-muted">${itemCount} item</small>
                        </div>
                    </div>
                    <div class="mt-1 d-flex gap-1">
                        <a class="btn btn-sm btn-outline-primary" href="${tambahTabToken('struk.php?nota=' + encodeURIComponent(h.nota))}" target="_blank">Lihat</a>
                    </div>
                `;
                container.appendChild(el);
            });
        }

        function cariTransaksi(immediate = false) {
            const input = document.getElementById('cari-transaksi');
            const container = document.getElementById('history-list');
            if (!input || !container) return;

            const keyword = input.value.trim();
            clearTimeout(searchTimer);

            if (keyword === '') {
                renderHistory();
                return;
            }

            const runSearch = () => {
                container.innerHTML = '<p class="text-muted small">Mencari transaksi...</p>';

                fetch(tambahTabToken('../proses/search_transaksi_kasir.php?q=' + encodeURIComponent(keyword)))
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'sukses') {
                            container.innerHTML = `<p class="text-danger small">${escapeHtml(data.pesan || 'Gagal mencari transaksi.')}</p>`;
                            return;
                        }

                        renderHasilTransaksi(data.transaksi || [], keyword);
                    })
                    .catch(error => {
                        console.error('Error cari transaksi:', error);
                        container.innerHTML = '<p class="text-danger small">Terjadi kesalahan saat mencari transaksi.</p>';
                    });
            };

            if (immediate) {
                runSearch();
            } else {
                searchTimer = setTimeout(runSearch, 300);
            }
        }

        function resetCariTransaksi() {
            const input = document.getElementById('cari-transaksi');
            if (input) input.value = '';
            renderHistory();
        }

        function renderHasilTransaksi(transaksi, keyword) {
            const container = document.getElementById('history-list');
            if (!container) return;

            if (!transaksi.length) {
                container.innerHTML = `<p class="text-muted small">Transaksi "${escapeHtml(keyword)}" tidak ditemukan.</p>`;
                return;
            }

            container.innerHTML = '';
            transaksi.forEach(item => {
                const waktu = new Date(item.tanggal_transaksi.replace(' ', 'T')).toLocaleString('id-ID');
                const el = document.createElement('div');
                el.className = 'mb-2 border-bottom pb-2';
                el.innerHTML = `
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <strong>${escapeHtml(item.nomor_struk)}</strong><br>
                            <small class="text-muted">${escapeHtml(waktu)}</small><br>
                            <small class="text-muted">Kasir: ${escapeHtml(item.nama_kasir || '-')}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">Rp ${Number(item.total_bayar).toLocaleString('id-ID')}</div>
                            <small class="text-muted">${Number(item.jumlah_item || 0)} item</small>
                        </div>
                    </div>
                    <div class="mt-1 d-flex gap-1">
                        <a class="btn btn-sm btn-outline-primary" href="${tambahTabToken('struk.php?nota=' + encodeURIComponent(item.nomor_struk))}" target="_blank">Lihat</a>
                    </div>
                `;
                container.appendChild(el);
            });
        }

    </script>

    <!-- Modal Pilih Topping per Produk -->
    <div class="modal fade" id="modalTopping" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Pilih Topping</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <strong id="modal-topping-produk-nama"></strong>
                        <div class="small text-muted" id="modal-topping-produk-harga"></div>
                    </div>
                    <p class="small text-muted mb-2">Centang topping jika pelanggan ingin tambahan. Kosongkan jika tanpa topping.</p>
                    <div id="modal-topping-list" style="max-height: 300px; overflow:auto;">
                        <!-- toppings will be injected here -->
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-primary" onclick="confirmAddWithTopping(true)">Tanpa Topping</button>
                    <button type="button" class="btn btn-primary" onclick="confirmAddWithTopping()">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmAddWithTopping(tanpaTopping = false) {
            const prod = window.__selectedProduct || null;
            if (!prod) return;
            let toppingTerpilih = [];
            let totalHargaTopping = 0;

            if (tanpaTopping) {
                document.querySelectorAll('.modal-topping-item:checked').forEach(item => {
                    item.checked = false;
                });
            } else {
                document.querySelectorAll('.modal-topping-item:checked').forEach(item => {
                    toppingTerpilih.push(item.value);
                    totalHargaTopping += parseInt(item.getAttribute('data-harga')) || 0;
                });
            }

            const subtotal = prod.harga + totalHargaTopping;
            const catatanTopping = toppingTerpilih.join(', ');

            let indexSama = cart.findIndex(item => item.id_produk === prod.id && item.topping === catatanTopping);
            if (indexSama !== -1) {
                cart[indexSama].jumlah += 1;
                cart[indexSama].subtotal += subtotal;
            } else {
                cart.push({
                    id_produk: prod.id,
                    nama: prod.nama,
                    topping: catatanTopping,
                    harga_satuan: prod.harga + totalHargaTopping,
                    jumlah: 1,
                    subtotal: subtotal
                });
            }

            // tutup modal
            const toppingModalEl = document.getElementById('modalTopping');
            const toppingModal = bootstrap.Modal.getInstance(toppingModalEl);
            if (toppingModal) toppingModal.hide();

            renderCart();
        }
    </script>

    <?php include '../includes/footer.php'; ?>
