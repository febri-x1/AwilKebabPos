<?php
session_start();

if (isset($_GET['role'])) {
    if ($_GET['role'] === 'admin') {
        // Jika ada token tab, hapus hanya data tab tersebut
        if (isset($_GET['tab'])) {
            $t = $_GET['tab'];
            unset($_SESSION['tabs'][$t]);
        }
        unset($_SESSION['admin']);
        // Hapus juga variabel session level-atas untuk konsistensi
        unset($_SESSION['id_user'], $_SESSION['username'], $_SESSION['nama_lengkap'], $_SESSION['role']);
    } elseif ($_GET['role'] === 'kasir') {
        if (isset($_GET['tab'])) {
            $t = $_GET['tab'];
            unset($_SESSION['tabs'][$t]);
        }
        unset($_SESSION['kasir']);
        // Hapus juga variabel session level-atas untuk konsistensi
        unset($_SESSION['id_user'], $_SESSION['username'], $_SESSION['nama_lengkap'], $_SESSION['role']);
    }
} else {
    session_unset();
    session_destroy();
}

// Kembali ke halaman login
header("Location: login.php");
exit;
