<?php
session_start();

// Jika sudah login, arahkan ke dashboard masing-masing sesuai role
if (isset($_SESSION['admin'])) {
    header("Location: ../admin/dashboard.php");
    exit;
} elseif (isset($_SESSION['kasir'])) {
    header("Location: ../kasir/transaksi.php");
    exit;
}

// Memanggil layout header (naik 1 folder ke root, lalu masuk ke folder includes)
include '../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-secondary">
    <div class="card shadow-lg" style="width: 25rem;">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4 fw-bold">Awil Kebab</h3>
            
            <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'gagal'): ?>
                <div class="alert alert-danger text-center">Username atau Password salah!</div>
            <?php elseif(isset($_GET['pesan']) && $_GET['pesan'] == 'password_berhasil_diubah'): ?>
                <div class="alert alert-success text-center">Password berhasil diubah! Silakan login kembali.</div>
            <?php elseif(isset($_GET['pesan']) && $_GET['pesan'] == 'timeout'): ?>
                <div class="alert alert-warning text-center">Sesi Anda telah berakhir. Silakan login kembali.</div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Masuk</button>
            </form>
        </div>
    </div>
</div>

<?php 
// Memanggil layout footer
include '../includes/footer.php'; 
?>