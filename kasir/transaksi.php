<?php
session_start();
// Halaman ini bisa diakses Kasir (dan Admin jika sewaktu-waktu harus menggantikan)
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/header.php';
require '../config/koneksi.php';
?>

<!-- Navigasi Khusus Kasir (Lebih ringkas agar area kasir luas) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">POS KEBAB - KASIR</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Petugas: <?php echo $_SESSION['nama_lengkap']; ?></span>
            <?php if($_SESSION['role'] == 'admin'): ?>
            <?php endif; ?>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
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
                        // Hanya tampilkan produk yang stoknya Tersedia
                        $query_produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia' ORDER BY id_produk DESC");
                        while($p = mysqli_fetch_assoc($query_produk)):
                        ?>
                        <div class="col-md-3 mb-3">
                            <div class="card p-2 border-primary h-100" style="cursor: pointer;" onclick="pilihMenu(<?= $p['id_produk']; ?>, '<?= $p['nama_produk']; ?>', <?= $p['harga_jual']; ?>)">
                                <h6 class="mb-1"><?= $p['nama_produk']; ?></h6>
                                <small class="text-muted"><?= $p['kategori']; ?></small>
                                <p class="text-success fw-bold mb-0 mt-auto">Rp <?= number_format($p['harga_jual'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning fw-bold">Pilih Topping (Opsional - Centang sebelum pilih menu)</div>
                <div class="card-body" id="topping-area">
                    <div class="row">
                        <?php
                        $query_topping = mysqli_query($koneksi, "SELECT * FROM topping ORDER BY id_topping DESC");
                        while($t = mysqli_fetch_assoc($query_topping)):
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
    let cart = [];
    let grandTotal = 0;

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
            
            cartList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">${item.nama} x${item.jumlah}</h6>
                        ${toppingText}
                        <br><small class="text-danger" onclick="hapusItem(${index})" style="cursor:pointer;">Hapus</small>
                    </div>
                    <span class="text-muted fw-bold">Rp ${item.subtotal.toLocaleString('id-ID')}</span>
                </li>
            `;
        });

        document.getElementById('total-tagihan').innerText = `Rp ${grandTotal.toLocaleString('id-ID')}`;
        hitungKembalian();
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
        if(cart.length === 0) return alert('Keranjang masih kosong!');
        
        let uangMasuk = parseInt(document.getElementById('uang-masuk').value) || 0;
        if(uangMasuk < grandTotal) return alert('Uang pembayaran tidak mencukupi!');

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
        fetch('../proses/transaksi_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'sukses') {
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