<?php
session_start();

// 1. Cek Sesi Login
if (!isset($_SESSION['login'])) {
    header("Location: user/login.php");
    exit;
}

// 2. Cek Role Manager
if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager') {
    header("Location: anggota/lihat.php");
    exit;
}

// 3. Koneksi Database
include 'config/koneksi.php';

// 4. Base URL Helper
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/kpri%20polije/";

/* =========================
   HELPER FUNCTIONS
========================= */

function getTotal($koneksi, $tabel)
{
    $query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM `$tabel`");
    $data  = mysqli_fetch_assoc($query);
    return $data['total'] ?? 0;
}

function getSum($koneksi, $tabel, $kolom)
{
    $query = mysqli_query($koneksi, "SELECT SUM(`$kolom`) AS total FROM `$tabel`");
    $data  = mysqli_fetch_assoc($query);
    return $data['total'] ?? 0;
}

/* =========================
   DATA PROCESSING
========================= */

$total_anggota  = getTotal($koneksi, 'tb_anggota');
$total_barang   = getTotal($koneksi, 'tb_barang');
$total_peminjam = getTotal($koneksi, 'tb_pinjaman');
$total_simpanan = getSum($koneksi, 'tb_simpanan', 'nominal');

// Query Peminjaman Terbaru (Limit 6)
$query_peminjam = mysqli_query($koneksi, "
    SELECT 
        a.nama,
        p.status
    FROM tb_pinjaman p
    JOIN tb_anggota a ON p.id_anggota = a.id_anggota
    ORDER BY p.id_pinjaman DESC
    LIMIT 6
");

// Query Data Simpanan
$query_simpanan = mysqli_query($koneksi, "
    SELECT
        a.nama,
        s.jenis_simpanan,
        s.nominal
    FROM tb_simpanan s
    JOIN tb_anggota a ON s.id_anggota = a.id_anggota
    ORDER BY s.id_simpanan DESC
");

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | KPRI POLIJE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">

    <style>
        /* =========================
            RESET & GLOBAL
         ========================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #eff6ff, #f8fafc, #eef2ff);
            color: #0f172a;
            min-height: 100vh;
        }

        .main {
            margin-left: 260px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        /* =========================
            HEADER
         ========================= */
        .top-header {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 28px;
            padding: 24px 30px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .header-icon {
            width: 68px;
            height: 68px;
            border-radius: 22px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
        }

        .top-header h2 {
            font-size: 30px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .top-header p {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
        }

        .header-logos img {
            height: 46px;
            margin-left: 12px;
            object-fit: contain;
        }

        /* =========================
            CARDS GRID
         ========================= */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 22px;
            margin-bottom: 28px;
        }

        .card {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            transition: all 0.35s ease;
            color: white;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        }

        .card::before,
        .card::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }

        .card::before {
            width: 220px;
            height: 220px;
            top: -80px;
            right: -80px;
        }

        .card::after {
            width: 140px;
            height: 140px;
            bottom: -50px;
            left: -40px;
            background: rgba(255, 255, 255, 0.05);
        }

        .card-inner {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 2;
            min-height: 150px;
        }

        .card-content span {
            display: block;
            font-size: 13px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-content h2 {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.3;
            min-height: 74px;
            display: flex;
            align-items: center;
        }

        .icon-box {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-box i {
            font-size: 24px;
            color: white;
        }

        .card-footer {
            display: block;
            text-align: center;
            text-decoration: none;
            color: white;
            font-size: 13px;
            font-weight: 500;
            padding: 14px;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(10px);
            transition: 0.3s;
            position: relative;
            z-index: 2;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .card-footer:hover {
            background: rgba(255, 255, 255, 0.20);
        }
        
        .gradient-1 { background: linear-gradient(135deg, #2563eb, #1d4ed8); }

        /* =========================
            DASHBOARD CONTENT GRID
         ========================= */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        /* Box grafik dibuat memenuhi baris baru agar seimbang */
        .box-full {
            grid-column: span 2;
        }

        .box {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 28px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-direction: column;
        }

        .box h3 {
            font-size: 21px;
            margin-bottom: 22px;
            color: #0f172a;
            border-left: 5px solid #2563eb;
            padding-left: 14px;
            font-weight: 600;
        }

        .chart-box {
            position: relative;
            height: 280px;
            width: 100%;
        }

        /* =========================
            TABLES
         ========================= */
        .scroll-box {
            max-height: 260px;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            padding: 14px;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px;
            font-size: 13px;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
        }

        table tr {
            transition: background 0.25s;
        }

        table tr:hover {
            background: #f8fbff;
        }

        .status-lunas {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .status-belum {
            background: #ede9fe;
            color: #6d28d9;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .total-box {
            margin-top: auto;
            background: linear-gradient(to right, #eff6ff, #dbeafe);
            border-radius: 18px;
            padding: 16px 18px;
            text-align: right;
            font-size: 14px;
            font-weight: 700;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 20px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* =========================
            RESPONSIVE
         ========================= */
        @media (max-width: 1400px) {
            .grid-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .box-full {
                grid-column: unset;
            }
        }

        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 18px;
            }
            .grid-container {
                grid-template-columns: 1fr;
            }
            .top-header {
                flex-direction: column;
                text-align: center;
                gap: 18px;
            }
            .header-left {
                flex-direction: column;
            }
            .header-logos {
                display: flex;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <?php include 'layout/sidebar.php'; ?>

    <main class="main">

        <header class="top-header">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <h2>Dashboard KPRI</h2>
                    <p>Sistem Informasi Stok Barang & Keuangan</p>
                </div>
            </div>

            <div class="header-logos">
                <img src="images/polijeee.png" alt="Logo Polije">
                <img src="images/kpripolije.png" alt="Logo KPRI">
            </div>
        </header>

        <div class="grid-container">
            
            <div class="card gradient-1">
                <div class="card-inner">
                    <div class="card-content">
                        <span>Total Anggota</span>
                        <h2><?= number_format($total_anggota) ?></h2>
                    </div>
                    <div class="icon-box">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <a href="anggota/lihat.php" class="card-footer">Lihat Detail</a>
            </div>

            <div class="card gradient-1">
                <div class="card-inner">
                    <div class="card-content">
                        <span>Total Simpanan</span>
                        <h2>Rp <?= number_format($total_simpanan, 0, ',', '.') ?></h2>
                    </div>
                    <div class="icon-box">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <a href="simpanan/lihat.php" class="card-footer">Lihat Detail</a>
            </div>

            <div class="card gradient-1">
                <div class="card-inner">
                    <div class="card-content">
                        <span>Total Peminjam</span>
                        <h2><?= number_format($total_peminjam) ?></h2>
                    </div>
                    <div class="icon-box">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <a href="pinjaman/lihat.php" class="card-footer">Lihat Detail</a>
            </div>

            <div class="card gradient-1">
                <div class="card-inner">
                    <div class="card-content">
                        <span>Total Barang</span>
                        <h2><?= number_format($total_barang) ?></h2>
                    </div>
                    <div class="icon-box">
                        <i class="fa-solid fa-box"></i>
                    </div>
                </div>
                <a href="barang/lihat.php" class="card-footer">Lihat Detail</a>
            </div>

            <div class="card gradient-1">
                <div class="card-inner">
                    <div class="card-content">
                        <span>Laporan</span>
                        <h2>Data KPRI</h2>
                    </div>
                    <div class="icon-box">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                </div>
                <a href="laporan/lihat.php" class="card-footer">Lihat Detail</a>
            </div>

        </div>

        <div class="dashboard-grid">

            <div class="box">
                <h3>Status Peminjaman Terbaru</h3>
                <div class="scroll-box">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Anggota</th>
                                <th style="text-align:right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_peminjam) > 0): ?>
                                <?php while ($p = mysqli_fetch_assoc($query_peminjam)): 
                                    $status = strtolower(trim($p['status']));
                                    $is_selesai = in_array($status, ['kembali', 'selesai', 'lunas']);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['nama']) ?></td>
                                        <td style="text-align:right;">
                                            <span class="<?= $is_selesai ? 'status-lunas' : 'status-belum' ?>">
                                                <?= $is_selesai ? 'Selesai' : 'Sedang Pinjam' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" style="text-align:center; color:#94a3b8;">Belum ada data peminjaman</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box">
                <h3>Riwayat Simpanan Terakhir</h3>
                <div class="scroll-box">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th style="text-align:right;">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_simpanan) > 0): ?>
                                <?php while ($d = mysqli_fetch_assoc($query_simpanan)): 
                                    $grand_total += $d['nominal'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['nama']) ?></td>
                                        <td><?= htmlspecialchars($d['jenis_simpanan']) ?></td>
                                        <td style="text-align:right; font-weight:600;">
                                            Rp <?= number_format($d['nominal'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center; color:#94a3b8;">Belum ada data simpanan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="total-box">
                    TOTAL SALDO: Rp <?= number_format($grand_total, 0, ',', '.') ?>
                </div>
            </div>

            <div class="box box-full">
                <h3>Distribusi Status Barang</h3>
                <div class="chart-box">
                    <canvas id="barangChart"></canvas>
                </div>
            </div>

        </div>

    </main>

    <script>
        // Konfigurasi Umum Chart
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: "'Poppins', sans-serif", size: 12 }
                    }
                }
            }
        };

        // Chart Barang (Doughnut Chart)
        new Chart(document.getElementById("barangChart"), {
            type: 'doughnut',
            data: {
                labels: ["Tersedia", "Dipinjam / Rusak"],
                datasets: [{
                    data: [<?= $total_barang ?>, 0],
                    backgroundColor: ['#2563eb', '#8b5cf6'],
                    hoverOffset: 10,
                    borderWidth: 0
                }]
            },
            options: commonOptions
        });
    </script>

</body>
</html>