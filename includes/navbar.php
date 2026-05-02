<!-- includes/navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">AWIL KEBAB</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../admin/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        Data Master
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../admin/master_produk.php">Produk</a></li>
                        <li><a class="dropdown-item" href="../admin/master_topping.php">Topping</a></li>
                        <li><a class="dropdown-item" href="../admin/master_user.php">Pengguna</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/pengeluaran.php">Pengeluaran</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/laporan.php">Laporan</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                Halo, <?php echo $_SESSION['nama_lengkap']; ?> (<?php echo ucfirst($_SESSION['role']); ?>)
            </span>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
        </div>
    </div>
</nav>