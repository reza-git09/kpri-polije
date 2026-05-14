<?php
// 🔥 MATIKAN SIDEBAR SAAT EXPORT EXCEL
if (defined('EXPORT_MODE')) return;

// Pastikan sesi aktif
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/";
$role = $_SESSION['role'] ?? 'admin';
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$current_file = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* Reset & Sidebar Layout */
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
        color: #fff;
    }

    .sidebar-brand {
        text-align: center;
        padding: 25px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .nav-menu {
        flex: 1;
        padding: 20px 15px;
        overflow-y: auto;
    }

    .menu-header {
        font-size: 11px;
        color: #7f8c8d;
        font-weight: 700;
        margin: 20px 0 10px 10px;
        display: block;
        text-transform: uppercase;
    }

    .nav-link-custom {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        margin-bottom: 4px;
        color: #868686;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        transition: 0.3s;
    }

    .nav-link-custom:hover {
        background: rgba(255,255,255,0.08);
        color: white;
    }

    .nav-link-custom.active {
        background: #2d74da;
        color: white;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #c0392b;
        color: white;
        padding: 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
        margin-bottom: 15px;
    }

    .btn-logout:hover {
        background: #a93226;
    }

    .copyright {
        font-size: 10px;
        color: #5d6d7e;
        text-align: center;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 10px;
    }
</style>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div style="width:60px; height:60px; background:white; border-radius:12px; margin:0 auto 10px auto; display:flex; align-items:center; justify-content:center;">
            <img src="<?= $base_url ?>images/kpripolije.png" alt="Logo" style="width:40px;">
        </div>
        <h3 style="font-size:16px; margin:0;">KPRI POLIJE</h3>
        <small style="color:#9fb3c8;">LOGIN: <?= strtoupper($role) ?></small>
    </div>

    <nav class="nav-menu">
        <?php if($role !== 'manager'): ?>
        <a href="<?= $base_url ?>index.php" class="nav-link-custom <?= ($current_file=='index.php') ? 'active' : '' ?>">
            <i class="fa fa-home"></i> Dashboard
        </a>
        <?php endif; ?>

        <span class="menu-header">Data Master</span>
        <a href="<?= $base_url ?>anggota/lihat.php" class="nav-link-custom <?= ($current_dir=='anggota') ? 'active' : '' ?>">
            <i class="fa fa-users"></i> Data Anggota
        </a>
        <a href="<?= $base_url ?>simpanan/lihat.php" class="nav-link-custom <?= ($current_dir=='simpanan') ? 'active' : '' ?>">
            <i class="fa fa-wallet"></i> Simpanan
        </a>
        <a href="<?= $base_url ?>pinjaman/lihat.php" class="nav-link-custom <?= ($current_dir=='pinjaman') ? 'active' : '' ?>">
            <i class="fa fa-hand-holding-usd"></i> Pinjaman
        </a>
        <a href="<?= $base_url ?>barang/lihat.php" class="nav-link-custom <?= ($current_dir=='barang') ? 'active' : '' ?>">
            <i class="fa fa-box"></i> Stok Barang
        </a>

        <span class="menu-header">Laporan</span>
        <a href="<?= $base_url ?>laporan/lihat.php" class="nav-link-custom <?= ($current_dir=='laporan') ? 'active' : '' ?>">
            <i class="fa fa-file-alt"></i> Laporan KPRI
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= $base_url ?>user/logout.php" class="btn-logout">
            <i class="fa fa-sign-out-alt"></i> KELUAR
        </a>
        <div class="copyright">
            &copy; <?= date('Y') ?> Design by Syntax Error Polije
        </div>
    </div>
</aside>