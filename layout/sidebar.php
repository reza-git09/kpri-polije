<?php
// 🔥 MATIKAN SIDEBAR SAAT EXPORT EXCEL
if (defined('EXPORT_MODE')) return;

// Pastikan sesi aktif
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/";
$role = $_SESSION['role'] ?? 'admin';

$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
$current_file = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    .sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: #273647;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        color: white;
    }

    .sidebar-brand {
        text-align: center;
        padding: 25px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .logo-box {
        width: 72px;
        height: 72px;
        background: white;
        border-radius: 16px;
        margin: 0 auto 14px auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-box img{
        width: 48px;
    }

    .sidebar-brand h3{
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .sidebar-brand small{
        color: #b9c6d3;
        font-size: 13px;
    }

    .nav-menu{
        flex: 1;
        padding: 20px 15px;
        overflow-y: auto;
    }

    .menu-header{
        color: #7f8c8d;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 20px 0 12px 10px;
        display:block;
    }

    .nav-link-custom{
        display:flex;
        align-items:center;
        gap:14px;
        padding:14px 16px;
        margin-bottom:8px;
        border-radius:12px;
        text-decoration:none;
        color:#bfc9d4;
        font-size:15px;
        font-weight:500;
        transition:0.3s;
    }

    .nav-link-custom i{
        width:20px;
        text-align:center;
        font-size:17px;
    }

    .nav-link-custom:hover{
        background: rgba(255,255,255,0.08);
        color:white;
    }

    .nav-link-custom.active{
        background:#2d74da;
        color:white;
        font-weight:600;
    }

    .sidebar-footer{
        padding: 20px 15px; 
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .btn-logout{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:10px;
        background:#d94336;
        color:white;
        padding: 12px 16px; /* Tebalnya pas sesuai menu aktif */
        width: 80%;         /* DIUBAH: Lebar dikurangi ke 80% supaya kurus dari samping */
        margin: 0 auto 15px auto; /* DIUBAH: Ditambahkan auto kanan-kiri agar posisinya tetap di tengah */
        border-radius:12px; 
        text-decoration:none;
        font-weight:600;
        font-size: 15px; 
        transition:0.3s;
    }

    .btn-logout:hover{
        background:#bb2d20;
    }

    .copyright{
        text-align:center;
        font-size:11px;
        color:#7f8c8d;
    }
</style>

<aside class="sidebar">

    <div class="sidebar-brand">
        <div class="logo-box">
            <img src="<?= $base_url ?>images/kpripolije.png">
        </div>

        <h3>KPRI POLIJE</h3>
        <small>LOGIN: <?= strtoupper($role) ?></small>
    </div>

    <nav class="nav-menu">

        <?php if($role !== 'manager'): ?>
        <a href="<?= $base_url ?>index.php"
           class="nav-link-custom <?= ($current_file == 'index.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>
        <?php endif; ?>

        <span class="menu-header">Data Master</span>

        <a href="<?= $base_url ?>anggota/lihat.php"
           class="nav-link-custom <?= ($current_dir == 'anggota') ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            Data Anggota
        </a>

        <a href="<?= $base_url ?>simpanan/lihat.php"
           class="nav-link-custom <?= ($current_dir == 'simpanan') ? 'active' : '' ?>">
            <i class="fa-solid fa-wallet"></i>
            Simpanan
        </a>

        <a href="<?= $base_url ?>pinjaman/lihat.php"
           class="nav-link-custom <?= ($current_dir == 'pinjaman') ? 'active' : '' ?>">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            Pinjaman
        </a>

        <a href="<?= $base_url ?>barang/lihat.php"
           class="nav-link-custom <?= ($current_dir == 'barang') ? 'active' : '' ?>">
            <i class="fa-solid fa-box"></i>
            Stok Barang
        </a>

        <span class="menu-header">Laporan</span>

        <a href="<?= $base_url ?>laporan/lihat.php"
           class="nav-link-custom <?= ($current_dir == 'laporan') ? 'active' : '' ?>">
            <i class="fa-solid fa-file-lines"></i>
            Laporan KPRI
        </a>

    </nav>

    <div class="sidebar-footer">

        <a href="<?= $base_url ?>user/logout.php" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            KELUAR
        </a>

        <div class="copyright">
            © <?= date('Y') ?> Design by Syntax Error Polije
        </div>

    </div>

</aside>