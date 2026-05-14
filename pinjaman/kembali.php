<?php 
session_start();
require_once '../config/koneksi.php';

// 🔒 1. Proteksi Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}

// 2. Ambil ID Pinjaman dari URL
if (!isset($_GET['id'])) {
    header("Location: lihat.php");
    exit;
}

$id_pinjaman = mysqli_real_escape_string($koneksi, $_GET['id']);
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 

// 3. Ambil data pinjaman untuk konfirmasi
$query = mysqli_query($koneksi, "SELECT p.*, b.nama_barang, b.id_barang, a.nama AS nama_peminjam
                                 FROM tb_pinjaman p 
                                 JOIN tb_barang b ON p.id_barang = b.id_barang 
                                 JOIN tb_anggota a ON p.id_anggota = a.id_anggota
                                 WHERE p.id_pinjaman = '$id_pinjaman'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: lihat.php");
    exit;
}

// Cek jika sudah pernah dikembalikan (Mencegah double stok)
$status_sekarang = strtolower(trim($data['status']));
if ($status_sekarang == 'kembali' || $status_sekarang == 'selesai') {
    echo "<script>alert('Barang ini sudah dikembalikan sebelumnya!'); window.location='lihat.php';</script>";
    exit;
}

$status_proses = ""; 

// 4. Proses Update saat tombol diklik
if (isset($_POST['konfirmasi_kembali'])) {
    $id_barang = $data['id_barang'];
    $jumlah_pinjam = $data['jumlah'];
    $tgl_sekarang = date('Y-m-d H:i:s');

    mysqli_begin_transaction($koneksi);

    try {
        // A. Update status & tanggal kembali
        mysqli_query($koneksi, "UPDATE tb_pinjaman SET status = 'kembali', tanggal_kembali = '$tgl_sekarang' WHERE id_pinjaman = '$id_pinjaman'");

        // B. Tambah stok barang otomatis
        mysqli_query($koneksi, "UPDATE tb_barang SET stok = stok + $jumlah_pinjam WHERE id_barang = '$id_barang'");

        mysqli_commit($koneksi);
        $status_proses = "success";
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $status_proses = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pengembalian - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="<?= $base_url; ?>images/kpripolije.png">
    
    <style>
        :root { --primary: #4a90e2; --success: #10b981; --bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        
        .container { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); width: 100%; max-width: 420px; text-align: center; border: 1px solid #edf2f7; position: relative; overflow: hidden; }
        
        /* Dekorasi Header */
        .header-accent { position: absolute; top: 0; left: 0; width: 100%; height: 8px; background: var(--success); }

        .icon-box { width: 90px; height: 90px; background: #ecfdf5; color: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 10px auto 25px; font-size: 40px; animation: pulse 2s infinite; }
        
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); }
        }

        h3 { color: #1e293b; margin: 0 0 10px; font-weight: 600; }
        .subtitle { color: #64748b; font-size: 14px; margin-bottom: 30px; line-height: 1.6; }

        .info-box { background: #f8fafc; padding: 25px; border-radius: 20px; text-align: left; margin-bottom: 30px; border: 1px solid #e2e8f0; }
        .info-item { display: flex; flex-direction: column; margin-bottom: 15px; }
        .info-item:last-child { margin-bottom: 0; }
        .info-label { color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .info-value { color: #334155; font-weight: 600; font-size: 15px; }

        .btn-confirm { background: var(--success); color: white; border: none; width: 100%; padding: 16px; border-radius: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); }
        .btn-confirm:hover { background: #059669; transform: translateY(-3px); box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.4); }
        
        .btn-back { display: inline-block; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; }
        .btn-back:hover { color: #ef4444; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-accent"></div>
    <div class="icon-box">
        <i class="fa-solid fa-box-open"></i>
    </div>
    <h3>Konfirmasi Stok</h3>
    <p class="subtitle">Pastikan barang yang diterima kembali dalam kondisi baik dan jumlahnya sesuai.</p>

    <div class="info-box">
        <div class="info-item">
            <span class="info-label">Peminjam</span>
            <span class="info-value"><?= htmlspecialchars($data['nama_peminjam']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Barang Kembali</span>
            <span class="info-value"><?= htmlspecialchars($data['nama_barang']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Jumlah Unit</span>
            <span class="info-value" style="color: var(--success); font-size: 18px;">+ <?= $data['jumlah']; ?> Unit</span>
        </div>
    </div>

    <form method="POST">
        <button type="submit" name="konfirmasi_kembali" class="btn-confirm">
            <i class="fa-solid fa-circle-check"></i> Selesaikan Pengembalian
        </button>
        <a href="lihat.php" class="btn-back">
            <i class="fa-solid fa-xmark"></i> Batalkan & Kembali
        </a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if($status_proses == "success"): ?>
        Swal.fire({
            title: 'Berhasil Kembali!',
            text: 'Stok barang otomatis bertambah dan transaksi selesai.',
            icon: 'success',
            confirmButtonColor: '#10b981'
        }).then(() => { window.location='lihat.php'; });
    <?php elseif($status_proses == "error"): ?>
        Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan sistem saat update stok.',
            icon: 'error'
        });
    <?php endif; ?>
</script>

</body>
</html>