<?php 
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../user/login.php"); exit; }
require_once '../config/koneksi.php';

// Proteksi Aksi agar Manager tidak bisa update status
if (isset($_GET['aksi']) && $_GET['aksi'] == 'kembalikan' && isset($_GET['id'])) {
    if ($_SESSION['role'] !== 'admin') {
        echo "<script>alert('Akses Ditolak!'); window.location='lihat.php';</script>";
        exit;
    }
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    mysqli_query($koneksi, "UPDATE tb_pinjaman SET status = 'Kembali' WHERE id_pinjaman = '$id'");
    header("Location: lihat.php"); exit;
}

$role_saat_ini = $_SESSION['role'] ?? '';
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 

$query_str = "SELECT p.*, a.nama AS nama_anggota, a.foto AS foto_anggota, a.alamat, b.nama_barang 
              FROM tb_pinjaman p
              JOIN tb_anggota a ON p.id_anggota = a.id_anggota 
              JOIN tb_barang b ON p.id_barang = b.id_barang 
              ORDER BY p.id_pinjaman DESC"; 

$query = mysqli_query($koneksi, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pinjaman - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --primary: #4a90e2; --bg-body: #f4f7fe; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-body); }
        .main { margin-left: 260px; padding: 25px; box-sizing: border-box; }
        .top-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; height: 50px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header-logos img { height: 40px; margin-left: 10px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { padding: 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; }
        .search-box { position: relative; width: 300px; }
        .search-box input { width: 100%; padding: 10px 15px 10px 40px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #a0aec0; }
        .btn-add { background: var(--primary); color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; }
        table th { padding: 15px 20px; background: #f8fbff; color: #64748b; font-size: 11px; text-transform: uppercase; text-align: left; }
        table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-warning { background: #fff9e7; color: #d97706; }
        .badge-success { background: #ecfdf5; color: #059669; }
        .badge-danger { background: #fef2f2; color: #dc2626; }
        .btn-circle { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; margin: 0 2px; }
        .peminjam-container { display: flex; align-items: center; gap: 12px; }
        .foto-anggota { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer; }
        .btn-kembalikan { display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; background: #2ecc71; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; transition: 0.3s; }
        .btn-kembalikan:hover { background: #27ae60; }
    </style>
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    <main class="main">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;"><i class="fa-solid fa-chart-line" style="color: var(--primary); margin-right: 10px;"></i> Monitoring Pinjaman</h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        <div class="card">
            <div class="card-header">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" onkeyup="filterTabel()" placeholder="Cari ID, nama, barang, atau alamat...">
                </div>
                <?php if($_SESSION['role'] == 'admin'): ?>
                <a href="tambah.php" class="btn-add"><i class="fa-solid fa-plus"></i> Transaksi Baru</a>
                <?php endif; ?>
            </div>

            <table id="tabelPinjaman">
             <thead>
    <tr>
        <th>No</th><th>ID</th><th>Peminjam</th><th>Alamat</th><th>Jaminan</th><th>Detail Barang</th><th>Tgl Pinjam</th><th>Status</th>
        <?php if($_SESSION['role'] == 'admin'): ?>
            <th style="text-align:center;">Kelola</th>
        <?php endif; ?>
    </tr>
</thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query) > 0):
                        while($d = mysqli_fetch_assoc($query)): 
                            $is_selesai = (strtolower(trim($d['status'])) == 'kembali' || strtolower(trim($d['status'])) == 'selesai');
                            $terlambat = (!$is_selesai && $d['tanggal_jatuh_tempo'] < date('Y-m-d') && $d['tanggal_jatuh_tempo'] != '0000-00-00');
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><span style="font-weight:600; color:#4a90e2;"><?= $d['id_pinjaman']; ?></span></td>
                        <td>
                            <div class="peminjam-container">
                                <img src="../images/anggota/<?= htmlspecialchars($d['foto_anggota']); ?>" class="foto-anggota" onclick="bukaModal(this.src)">
                                <span><strong><?= htmlspecialchars($d['nama_anggota']); ?></strong></span>
                            </div>
                        </td>
                        <td><small><?= htmlspecialchars($d['alamat']); ?></small></td>
                        <td><?= htmlspecialchars($d['jaminan']); ?></td>
                        <td><?= htmlspecialchars($d['nama_barang']); ?><br><small>Qty: <?= $d['jumlah']; ?></small></td>
                        <td><?= date('d/m/Y', strtotime($d['tanggal'])); ?></td>
                        <td>
                            <span class="badge <?= $is_selesai ? 'badge-success' : ($terlambat ? 'badge-danger' : 'badge-warning'); ?>">
                                <?= htmlspecialchars($d['status']); ?>
                            </span>
                        </td>
                        <td align="center">
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <a href="edit.php?id=<?= $d['id_pinjaman']; ?>" class="btn-circle" style="background:#eff6ff; color:#2563eb;"><i class="fa-solid fa-pen"></i></a>
                                <?php if(!$is_selesai): ?>
                                    <a href="?aksi=kembalikan&id=<?= $d['id_pinjaman']; ?>" class="btn-kembalikan" onclick="return confirm('Yakin ingin menandai selesai?')">
                                        <i class="fa-solid fa-check" style="margin-right: 5px;"></i> Selesai
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <small style="color:#cbd5e0;">-</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="9" align="center">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        function filterTabel() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let table = document.getElementById("tabelPinjaman");
            let tr = table.getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let txtValue = tr[i].textContent || tr[i].innerText;
                tr[i].style.display = txtValue.toLowerCase().indexOf(input) > -1 ? "" : "none";
            }
        }
        function bukaModal(src) {
            // (Function logic remained unchanged)
        }
    </script>
</body>
</html>