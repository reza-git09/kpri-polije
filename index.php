<?php 
session_start();
if (!isset($_SESSION['login'])) { header("Location: user/login.php"); exit; }

// PROTEKSI MANAGER:
// Jika login sebagai manager, langsung lempar ke halaman anggota
if ($_SESSION['role'] == 'manager') {
    header("Location: anggota/lihat.php");
    exit;
}

include 'config/koneksi.php'; 

// Fungsi Helper
function getTotal($koneksi, $tabel) {
    $query = mysqli_query($koneksi,"SELECT COUNT(*) as total FROM $tabel");
    $data = mysqli_fetch_assoc($query);
    return $data['total'] ?? 0;
}
function getSum($koneksi,$tabel,$kolom){
    $query = mysqli_query($koneksi,"SELECT SUM($kolom) as total FROM $tabel");
    $data = mysqli_fetch_assoc($query);
    return $data['total'] ?? 0;
}

$total_anggota  = getTotal($koneksi,'tb_anggota');
$total_barang   = getTotal($koneksi,'tb_barang');
$total_simpanan = getSum($koneksi,'tb_simpanan','nominal');
$total_peminjam = getTotal($koneksi,'tb_pinjaman');

$peminjam = mysqli_query($koneksi,"SELECT a.nama, p.status FROM tb_pinjaman p JOIN tb_anggota a ON p.id_anggota=a.id_anggota ORDER BY p.id_pinjaman DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background: #f4f7fe; }
        .main { margin-left: 260px; padding: 20px; box-sizing: border-box; }
        
        .grid-container { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 20px; }
        .card { border-radius: 6px; color: white; box-shadow: 0 2px 5px rgba(255, 255, 255, 0.1); overflow: hidden; }
        .card-inner { padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .card-footer { padding: 8px; text-align: center; background: rgba(206, 13, 13, 0.1); font-size: 12px; display: block; text-decoration: none; color: white !important; }
        .icon { font-size: 40px; opacity: 0.3; }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .box { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(204, 6, 6, 0.1); }
        .chart-box { height: 200px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table th { padding: 8px; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #edf2f7; color: #718096; }
        table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 12px; }
        
        .status-lunas { color: #28a745; font-weight: 600; } 
        .status-belum { color: #dc3545; font-weight: 600; }
        
        .top-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .header-logos img { height: 40px; margin-left: 10px; }
    </style>
</head>
<body>
    <?php include 'layout/sidebar.php'; ?>

    <main class="main">
        <header class="top-header">
            <h3 style="margin:0;"><i class="fa-solid fa-gauge" style="color: #17a2b8;"></i> Dashboard</h3>
            <div class="header-logos">
                <img src="images/polijeee.png">
                <img src="images/kpripolije.png">
            </div>
        </header>

        <div class="grid-container">
            <div class="card" style="background:#17a2b8;"><div class="card-inner"><div><h2><?= $total_anggota ?></h2>Total Anggota</div><i class="fa-solid fa-users icon"></i></div><a href="anggota/lihat.php" class="card-footer">Informasi selengkapnya</a></div>
            <div class="card" style="background:#28a745;"><div class="card-inner"><div><h2>Rp <?= number_format($total_simpanan,0,',','.') ?></h2>Total Simpanan</div><i class="fa-solid fa-wallet icon"></i></div><a href="simpanan/lihat.php" class="card-footer">Informasi selengkapnya</a></div>
            <div class="card" style="background:#ffc107;color:#333;"><div class="card-inner"><div><h2><?= $total_peminjam ?></h2>Total Peminjam</div><i class="fa-solid fa-hand-holding-dollar icon"></i></div><a href="pinjaman/lihat.php" class="card-footer">Informasi selengkapnya</a></div>
            <div class="card" style="background:#dc3545;"><div class="card-inner"><div><h2><?= $total_barang ?></h2>Total Barang</div><i class="fa-solid fa-box icon"></i></div><a href="barang/lihat.php" class="card-footer">Informasi selengkapnya</a></div>
            <div class="card" style="background:#6f42c1;"><div class="card-inner"><div><h2>Laporan</h2>Pinjaman/Simpanan</div><i class="fa-solid fa-file-invoice icon"></i></div><a href="laporan/lihat.php" class="card-footer">Informasi selengkapnya</a></div>
        </div>

        <div class="dashboard-grid">
            <div class="box"><h3>Grafik Anggota</h3><div class="chart-box"><canvas id="anggotaChart"></canvas></div></div>
            <div class="box">
                <h3>Anggota Sedang Meminjam</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr><th style="text-align: left; position: sticky; top: 0; background: white;">Nama</th><th style="text-align: right; position: sticky; top: 0; background: white;">Status</th></tr>
                        </thead>
                        <tbody>
                            <?php while($p = mysqli_fetch_assoc($peminjam)){ 
                                $is_selesai = (strtolower(trim($p['status'])) == 'kembali' || strtolower(trim($p['status'])) == 'selesai');
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nama']) ?></td>
                                <td style="text-align: right;"><?= $is_selesai ? "<span class='status-lunas'>Selesai</span>" : "<span class='status-belum'>Belum</span>" ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="box">
                <h3>Data Simpanan</h3>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #eee;">
                    <table>
                        <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                            <tr><th>Nama</th><th>Jenis</th><th style="text-align: right;">Nominal</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_total = 0;
                            $query_simpanan = mysqli_query($koneksi, "SELECT a.nama, s.jenis_simpanan, s.nominal FROM tb_simpanan s JOIN tb_anggota a ON s.id_anggota = a.id_anggota");
                            while($d = mysqli_fetch_assoc($query_simpanan)) { 
                                $grand_total += $d['nominal'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nama']) ?></td>
                                <td><?= htmlspecialchars($d['jenis_simpanan']) ?></td>
                                <td style="text-align: right;">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <table style="margin-top: 0; border-top: 2px solid #edf2f7; background: #f8fafc;">
                    <tr style="font-weight: bold;">
                        <td style="padding: 10px 10px 10px 380px; text-align: right;">TOTAL SALDO</td>
                        <td style="padding: 10px; text-align: right; color: #28a745;">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                    </tr>
                </table>
            </div>

            <div class="box"><h3>Grafik Barang</h3><div class="chart-box"><canvas id="barangChart"></canvas></div></div>
        </div>
    </main>

    <script>
        const config = { responsive: true, maintainAspectRatio: false };
        new Chart(document.getElementById("anggotaChart"),{ type:'line', data:{ labels:["Jan","Feb","Mar","Apr","Mei","Jun"], datasets:[{ label:"Anggota", data:[5,10,8,15,12,20], fill:false, borderColor:'#17a2b8' }] }, options: config });
        new Chart(document.getElementById("barangChart"),{ type:'doughnut', data:{ labels:["Barang Ada","Dipinjam"], datasets:[{ data:[70,30], backgroundColor:['#28a745','#dc3545'] }] }, options: config });
    </script>
</body>
</html>