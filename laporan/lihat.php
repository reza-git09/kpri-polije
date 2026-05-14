<?php 
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

include '../config/koneksi.php'; 

$active_menu = 'laporan';
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri%20polije/";

$jenis_laporan = $_GET['jenis_laporan'] ?? 'pinjaman'; 
$filter_type   = $_GET['filter_type'] ?? 'semua';
$tgl_pilih     = $_GET['tgl'] ?? date('Y-m-d');
$bulan_pilih   = $_GET['bulan'] ?? date('m');
$tahun_pilih   = $_GET['tahun'] ?? date('Y');

// ================= QUERY DATA =================
if ($jenis_laporan == 'pinjaman') {
    $judul_h4 = "Laporan Pinjaman Barang";
    $query_str = "SELECT p.*, p.status AS status_pinjam, a.nama, a.id_anggota, b.nama_barang 
                  FROM tb_pinjaman p 
                  JOIN tb_anggota a ON p.id_anggota = a.id_anggota 
                  JOIN tb_barang b ON p.id_barang = b.id_barang 
                  WHERE 1=1";
    $date_field = "p.tanggal";
} else {
    $judul_h4 = "Laporan Simpanan Anggota";
    $query_str = "SELECT s.*, a.nama, a.id_anggota 
                  FROM tb_simpanan s 
                  JOIN tb_anggota a ON s.id_anggota = a.id_anggota 
                  WHERE 1=1";
    $date_field = "s.tanggal";
}

if ($filter_type == 'harian') {
    $query_str .= " AND $date_field = '$tgl_pilih'";
} elseif ($filter_type == 'bulanan') {
    $query_str .= " AND MONTH($date_field) = '$bulan_pilih' AND YEAR($date_field) = '$tahun_pilih'";
}

$query_str .= " ORDER BY $date_field DESC";
$data_laporan = mysqli_query($koneksi, $query_str);

// ================= LOGIKA EXPORT EXCEL =================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_KPRI_Polije.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr><th colspan='".($jenis_laporan == 'pinjaman' ? '7' : '6')."' style='font-size:18px; font-weight:bold;'>$judul_h4</th></tr>";
    echo "<tr><th colspan='".($jenis_laporan == 'pinjaman' ? '7' : '6')."'>KPRI POLIJE</th></tr>";
    echo "<tr></tr>"; // Baris kosong

    if ($jenis_laporan == 'pinjaman') {
        echo "<tr style='background-color: #f2f2f2;'>
                <th>No</th>
                <th>ID Anggota</th>
                <th>Tanggal Pinjam</th>
                <th>Nama Anggota</th>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Status</th>
              </tr>";
    } else {
        echo "<tr style='background-color: #f2f2f2;'>
                <th>No</th>
                <th>ID Anggota</th>
                <th>Tanggal</th>
                <th>Nama Anggota</th>
                <th>Jenis Simpanan</th>
                <th>Nominal</th>
              </tr>";
    }

    $no = 1;
    mysqli_data_seek($data_laporan, 0); // Reset pointer data
    while ($row = mysqli_fetch_assoc($data_laporan)) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . $row['id_anggota'] . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
        echo "<td>" . $row['nama'] . "</td>";
        
        if ($jenis_laporan == 'pinjaman') {
            echo "<td>" . $row['nama_barang'] . "</td>";
            echo "<td>" . $row['jumlah'] . " Unit</td>";
            echo "<td>" . $row['status_pinjam'] . "</td>";
        } else {
            echo "<td>" . $row['jenis_simpanan'] . "</td>";
            echo "<td>" . $row['nominal'] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    exit; // Berhenti di sini agar HTML di bawah tidak masuk ke file Excel
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #2563eb;
            --bg-body: #f1f5f9;
            --sidebar-dark: #2c3e50;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-body); display: flex; }

        .sidebar { width: 260px; background: var(--sidebar-dark); min-height: 100vh; color: white; position: fixed; }
        .main-content { flex: 1; margin-left: 260px; padding: 30px; width: calc(100% - 260px); }

        .header-panel {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 20px;
            padding: 25px 35px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        }

        .header-title h2 { font-size: 26px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
        .header-title p { font-size: 14px; opacity: 0.9; margin-top: 4px; }
        .header-logos { display: flex; gap: 10px; align-items: center; background: rgba(255,255,255,0.15); padding: 8px 15px; border-radius: 12px; }
        .header-logos img { height: 35px; object-fit: contain; }

        .content-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .select-custom { padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0; outline: none; font-family: 'Poppins'; }
        
        .btn-group { display: flex; gap: 12px; }
        .btn { padding: 10px 22px; border-radius: 12px; font-weight: 600; cursor: pointer; border: none; display: flex; align-items: center; gap: 8px; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .btn-print { background: #2563eb; color: white; }
        .btn-excel { background: #10b981; color: white; }

        table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: 10px; }
        thead th { background: #f8fafc; color: #64748b; padding: 15px; text-align: left; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f1f5f9; }
        tbody tr { background: white; transition: 0.3s; }
        tbody td { padding: 18px 15px; color: #334155; font-size: 14px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        tbody td:first-child { border-left: 1px solid #f1f5f9; border-radius: 12px 0 0 12px; }
        tbody td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 12px 12px 0; }

        .no-index { background: #eff6ff; color: #2563eb; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-danger { background: #fee2e2; color: #ef4444; }
        .status-success { background: #dcfce7; color: #10b981; }

        /* Styles for printing */
        @media print {
            @page { size: landscape; margin: 1cm; }
            body { background: white !important; display: block !important; }
            .sidebar, .btn-group, .select-custom, .fa-chart-line, form, #search-box { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .header-panel { width: 100% !important; background: #2563eb !important; -webkit-print-color-adjust: exact; color: white !important; border-radius: 10px !important; margin-bottom: 20px !important; }
            .content-card { box-shadow: none !important; padding: 0 !important; width: 100% !important; }
            table { width: 100% !important; border-collapse: collapse !important; border-spacing: 0 !important; }
            thead th { background: #f1f5f9 !important; color: black !important; border: 1px solid #ddd !important; font-size: 12px !important; }
            tbody td { border: 1px solid #ddd !important; border-radius: 0 !important; padding: 10px !important; font-size: 12px !important; }
            .no-index { background: none !important; color: black !important; padding: 0; }
            .status-badge { border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <?php include '../layout/sidebar.php'; ?>
</div>

<div class="main-content">
    <div class="header-panel">
        <div class="header-title">
            <h2><i class="fas fa-chart-line"></i> <?= $judul_h4; ?></h2>
            <p>Sistem Informasi Koperasi KPRI POLIJE</p>
        </div>
        <div class="header-logos">
            <img src="<?= $base_url; ?>images/polijeee.png" alt="Logo 1">
            <img src="<?= $base_url; ?>images/kpripolije.png" alt="Logo 2">
        </div>
    </div>

    <div class="content-card">
        <div class="action-bar">
            <form method="GET">
                <select name="jenis_laporan" class="select-custom" onchange="this.form.submit()">
                    <option value="pinjaman" <?= $jenis_laporan == 'pinjaman' ? 'selected' : ''; ?>>Laporan Pinjaman</option>
                    <option value="simpanan" <?= $jenis_laporan == 'simpanan' ? 'selected' : ''; ?>>Laporan Simpanan</option>
                </select>
                <!-- Menjaga filter tetap aktif saat ganti jenis laporan -->
                <input type="hidden" name="filter_type" value="<?= $filter_type; ?>">
            </form>

            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
                <!-- Link URL yang membawa parameter filter saat ini -->
                <a href="?<?= $_SERVER['QUERY_STRING']; ?>&export=excel" class="btn btn-excel">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <table>
            <thead>
                <?php if($jenis_laporan == 'pinjaman'): ?>
                    <tr>
                        <th>No</th>
                        <th>ID Anggota</th>
                        <th>Tanggal Pinjam</th>
                        <th>Nama Anggota</th>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>No</th>
                        <th>ID Anggota</th>
                        <th>Tanggal</th>
                        <th>Nama Anggota</th>
                        <th>Jenis Simpanan</th>
                        <th>Nominal</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if(mysqli_num_rows($data_laporan) > 0):
                    while($row = mysqli_fetch_assoc($data_laporan)): 
                ?>
                <tr>
                    <td><span class="no-index"><?= $no++; ?></span></td>
                    <td><strong><?= $row['id_anggota']; ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    
                    <?php if($jenis_laporan == 'pinjaman'): ?>
                        <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?= $row['jumlah']; ?> Unit</td>
                        <td>
                            <?php 
                            $s = strtolower(trim($row['status_pinjam']));
                            $class = ($s == 'dipinjam') ? 'status-danger' : 'status-success';
                            ?>
                            <span class="status-badge <?= $class; ?>"><?= $row['status_pinjam']; ?></span>
                        </td>
                    <?php else: ?>
                        <td><?= $row['jenis_simpanan']; ?></td>
                        <td style="font-weight: 600; color: #10b981;">Rp <?= number_format($row['nominal'],0,',','.'); ?></td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="<?= ($jenis_laporan == 'pinjaman' ? '7' : '6'); ?>" style="text-align: center; padding: 40px; color: #94a3b8;">Data tidak ditemukan.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>