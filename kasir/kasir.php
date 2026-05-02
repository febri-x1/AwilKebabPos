<!-- halaman_kasir.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-3">
    <div class="row">
        <!-- Area Kiri: Daftar Menu & Topping -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">Menu Kebab</div>
                <div class="card-body">
                    <!-- Simulasi Looping PHP dari tabel produk -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card p-3 border-primary" style="cursor: pointer;" onclick="pilihMenu(1, 'Kebab Sapi', 15000)">
                                <h6>Kebab Sapi (Medium)</h6>
                                <p class="text-success fw-bold mb-0">Rp 15.000</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning">Pilih Topping (Opsional)</div>
                <div class="card-body" id="topping-area">
                     <!-- Simulasi Looping PHP dari tabel topping -->
                     <input type="checkbox" class="topping-item" value="Keju" data-harga="3000"> Keju (Rp 3.000) <br>
                     <input type="checkbox" class="topping-item" value="Sosis" data-harga="4000"> Sosis (Rp 4.000)
                </div>
            </div>
        </div>

        <!-- Area Kanan: Keranjang Belanja -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Keranjang Pesanan</div>
                <div class="card-body">
                    <ul class="list-group mb-3" id="cart-list">
                        <!-- Item keranjang dirender oleh JS di sini -->
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <h5>Total Tagihan:</h5>
                        <h5 class="fw-bold" id="total-tagihan">Rp 0</h5>
                    </div>
                    
                    <div class="mb-3">
                        <label>Uang Masuk (Rp)</label>
                        <input type="number" class="form-control form-control-lg" id="uang-masuk" oninput="hitungKembalian()">
                    </div>
                    <div class="mb-3">
                        <label>Kembalian (Rp)</label>
                        <input type="text" class="form-control form-control-lg bg-light" id="kembalian" readonly>
                    </div>

                    <button class="btn btn-success w-100 btn-lg" onclick="prosesTransaksi()">Proses Pembayaran</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // State penyimpanan keranjang sementara
    let cart = [];
    let grandTotal = 0;

    function pilihMenu(id_produk, nama, harga) {
        // Ambil topping yang dicentang
        let toppingTerpilih = [];
        let totalHargaTopping = 0;
        
        document.querySelectorAll('.topping-item:checked').forEach(item => {
            toppingTerpilih.push(item.value);
            totalHargaTopping += parseInt(item.getAttribute('data-harga'));
            item.checked = false; // Reset checkbox setelah masuk keranjang
        });

        let subtotal = harga + totalHargaTopping;

        // Tambah ke array keranjang
        cart.push({
            id_produk: id_produk,
            nama: nama,
            topping: toppingTerpilih.join(', '),
            harga_satuan: harga,
            harga_topping: totalHargaTopping,
            subtotal: subtotal
        });

        renderCart();
    }

    function renderCart() {
        let cartList = document.getElementById('cart-list');
        cartList.innerHTML = '';
        grandTotal = 0;

        cart.forEach((item, index) => {
            grandTotal += item.subtotal;
            let toppingText = item.topping ? `<small class="text-muted">+ Topping: ${item.topping}</small><br>` : '';
            
            cartList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">${item.nama}</h6>
                        ${toppingText}
                        <small class="text-danger" onclick="hapusItem(${index})" style="cursor:pointer;">Hapus</small>
                    </div>
                    <span class="text-muted">Rp ${item.subtotal.toLocaleString('id-ID')}</span>
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
        } else {
            elemKembalian.value = 'Uang Kurang!';
            elemKembalian.classList.replace('text-success', 'text-danger');
        }
    }

    function prosesTransaksi() {
        if(cart.length === 0) return alert('Keranjang kosong!');
        let uangMasuk = parseInt(document.getElementById('uang-masuk').value) || 0;
        if(uangMasuk < grandTotal) return alert('Uang pembayaran tidak cukup!');

        // Di sini kamu akan menggunakan Fetch API/AJAX untuk mengirim data variabel 'cart' dan 'uangMasuk' ke backend PHP (misal: proses_simpan.php)
        console.log("Data dikirim ke server: ", JSON.stringify(cart));
        alert("Melakukan Request AJAX ke backend...");
    }
</script>

</body>
</html>