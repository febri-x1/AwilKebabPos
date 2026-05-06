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
        /* Style untuk Sidebar Admin (hanya muncul di halaman admin) */
        .sidebar-left {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #212529 0%, #343a40 100%);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            padding: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
 
        .sidebar-left .sidebar-header {
            padding: 20px;
            background-color: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
 
        .sidebar-left .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 12px 20px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
 
        .sidebar-left .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: #0d6efd;
        }
 
        .sidebar-left .nav-link.active {
            background-color: rgba(13, 110, 253, 0.2);
            color: #fff;
            border-left-color: #0d6efd;
            font-weight: 600;
        }
 
        .sidebar-left .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
 
        /* Konten utama dengan sidebar (hanya untuk admin) */
        .content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
 
        /* Untuk halaman tanpa sidebar (login, kasir) */
        body.no-sidebar .content-wrapper {
            margin-left: 0;
            width: 100%;
        }
 
        @media print {
            .sidebar-left,
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-light">