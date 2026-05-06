<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awil Kebab</title>
    <!-- Menggunakan CDN Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        display: flex;
        min-height: 100vh;
    }
    /* Memberikan ruang agar konten utama tidak tertutup sidebar */
    .main-content {
        margin-left: 280px; /* Lebar yang sama dengan sidebar */
        width: 100%;
        padding: 20px;
    }
    .sidebar-left {
        width: 250px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background-color: #212529; /* Warna gelap sesuai gambar */
        z-index: 1050; /* Pastikan lebih tinggi dari elemen tabel/kartu */
        display: flex;
        flex-direction: column;
        padding: 20px 0;
    }

    /* Penyesuaian Konten Utama */
    .content-wrapper {
        margin-left: 250px; /* Harus sama dengan lebar sidebar */
        width: calc(100% - 250px);
        padding: 30px;
        background-color: #f8f9fa; /* Warna background area kerja */
        min-height: 100vh;
}
</style>
</head>
<body class="bg-light">