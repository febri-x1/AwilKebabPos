<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
require '../config/koneksi.php';
?>

<div class="content-wrapper" style="margin-left: 250px; width: calc(100% - 250px); padding: 20px; min-height: 100vh; background-color: #f8f9fa;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Kelola Data Topping</h3>
        <!-- Tombol Pemicu Modal Tambah -->
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
            + Tambah Topping
        </button>
    </div>

    <!-- Alert Notifikasi -->
    <?php if(isset($_GET['pesan'])): ?>
        <?php if($_GET['pesan'] == 'sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data topping berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif($_GET['pesan'] == 'gagal'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Gagal memproses data topping!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>Nama Topping</th>
                        <th>Harga Tambahan</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $query = mysqli_query($koneksi, "SELECT * FROM topping ORDER BY id_topping DESC");
                    while($row = mysqli_fetch_assoc($query)):
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= $row['nama_topping']; ?></td>
                        <td>Rp <?= number_format($row['harga_tambahan'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <!-- Tombol Edit memicu modal spesifik berdasarkan ID -->
                            <button href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_topping']; ?>">Edit</button>
                            
                            <!-- Tombol Hapus memanggil form POST -->
                            <form action="../proses/topping_aksi.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus topping ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_topping" value="<?= $row['id_topping']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit Topping -->
                    <div class="modal fade" id="modalEdit<?= $row['id_topping']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title fw-bold">Edit Topping</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="../proses/topping_aksi.php" method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="aksi" value="edit">
                                        <input type="hidden" name="id_topping" value="<?= $row['id_topping']; ?>">
                                        
                                        <div class="mb-3">
                                            <label>Nama Topping</label>
                                            <input type="text" name="nama_topping" class="form-control" value="<?= $row['nama_topping']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Harga Tambahan (Rp)</label>
                                            <input type="number" name="harga_tambahan" class="form-control" value="<?= $row['harga_tambahan']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-warning fw-bold w-100">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Topping -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Topping Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../proses/topping_aksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label>Nama Topping</label>
                        <input type="text" name="nama_topping" class="form-control" placeholder="Contoh: Ekstra Keju" required>
                    </div>
                    <div class="mb-3">
                        <label>Harga Tambahan (Rp)</label>
                        <input type="number" name="harga_tambahan" class="form-control" placeholder="Contoh: 3000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold w-100">Simpan Topping</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>