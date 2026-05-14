<?php 
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../user/login.php"); exit; }
require_once '../config/koneksi.php'; 
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$role_saat_ini = $_SESSION['role'];

$id_anggota_terpilih = $_GET['id_anggota'] ?? '';

// PERUBAHAN DI SINI: Menggunakan DESC agar anggota terbaru muncul paling atas
$anggota = mysqli_query($koneksi, "SELECT * FROM tb_anggota ORDER BY id_anggota DESC");
$barang  = mysqli_query($koneksi, "SELECT * FROM tb_barang WHERE stok > 0 ORDER BY nama_barang ASC");

$status_proses = ""; 

if (isset($_POST['submit']) && $role_saat_ini == 'admin') {
    $id_anggota = mysqli_real_escape_string($koneksi, $_POST['id_anggota']);
    $id_barang  = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
    $jumlah     = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $jangka_waktu = mysqli_real_escape_string($koneksi, $_POST['jangka_waktu']);
    $jaminan    = mysqli_real_escape_string($koneksi, $_POST['jaminan']);

    $tanggal = date('Y-m-d');
    $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$jangka_waktu days"));
    
    $cek_pinjaman = mysqli_query($koneksi, "SELECT id_pinjaman FROM tb_pinjaman WHERE id_anggota = '$id_anggota' AND status = 'dipinjam'");

    if(mysqli_num_rows($cek_pinjaman) > 0){
        $status_proses = "aktif";
    } else {
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM tb_barang WHERE id_barang = '$id_barang'");
        $data = mysqli_fetch_assoc($cek_stok);
        
        if ($data && $data['stok'] >= $jumlah && $jumlah > 0) {
            $query_simpan = mysqli_query($koneksi, "INSERT INTO tb_pinjaman 
                (id_barang, id_anggota, jumlah, tanggal, status, jangka_waktu, tanggal_jatuh_tempo, jaminan) 
                VALUES ('$id_barang', '$id_anggota', '$jumlah', '$tanggal', 'dipinjam', '$jangka_waktu', '$tanggal_jatuh_tempo', '$jaminan')");

            if ($query_simpan) {
                mysqli_query($koneksi, "UPDATE tb_barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'");
                $status_proses = "success";
            } else {
                $status_proses = "error";
            }
        } else {
            $status_proses = "stok_kurang";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pinjaman - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        :root { --primary: #4a90e2; --bg: #f4f7fe; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg); }
        .main { margin-left: 260px; padding: 25px; }
        .main-wrapper { margin-left: 260px; padding: 25px; box-sizing: border-box; }
        
        .top-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: white; padding: 15px 25px; height: 50px;
            border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .header-logos img { height: 40px; margin-left: 10px; }
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 550px; margin: 0 auto; }
        .form-card h2 { margin: 0 0 20px; color: #334155; font-size: 18px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #64748b; font-size: 12px; font-weight: 600; }
        select, input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .btn-simpan { background: var(--primary); color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn-batal { display: block; text-align: center; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 13px; }
        .btn-batal:hover { color: #dc2626; }
    </style>
</head>
<body>

<?php include '../layout/sidebar.php'; ?>

<main class="main">
    <header class="top-header">
        <h3 style="margin:0; font-size: 18px; color: #334155;"><i class="fa-solid fa-cart-plus"></i> Input Transaksi</h3>
        <div class="header-logos">
            <img src="<?= $base_url; ?>images/polijeee.png">
            <img src="<?= $base_url; ?>images/kpripolije.png">
        </div>
    </header>

    <div class="form-card">
        <h2>Form Pinjaman Baru</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nama Anggota</label>
                <select name="id_anggota" required>
                    <option value="">-- Pilih Anggota (Terbaru di Atas) --</option>
                    <?php while($a = mysqli_fetch_assoc($anggota)) : ?>
                        <option value="<?= $a['id_anggota']; ?>" <?= ($a['id_anggota'] == $id_anggota_terpilih) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($a['nama']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Pilih Barang</label>
                <select name="id_barang" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php while($b = mysqli_fetch_assoc($barang)) : ?>
                        <option value="<?= $b['id_barang']; ?>">
                            <?= htmlspecialchars($b['nama_barang']); ?> (Stok: <?= $b['stok']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;"><label>Jumlah</label><input type="number" name="jumlah" min="1" required></div>
                <div class="form-group" style="flex: 1;"><label>Tempo (Hari)</label><input type="number" name="jangka_waktu" min="1" required></div>
            </div>
            <div class="form-group"><label>Jaminan</label><input type="text" name="jaminan" placeholder="Contoh: KTP" required></div>
            
            <button type="submit" name="submit" class="btn-simpan">Proses Pinjaman</button>
            <a href="lihat.php" class="btn-batal"><i class="fa-solid fa-xmark"></i> Batal & Kembali</a>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if($status_proses == "success"): ?>
        Swal.fire({ title: 'Berhasil!', text: 'Data tersimpan.', icon: 'success' }).then(() => { window.location='lihat.php'; });
    <?php elseif($status_proses == "aktif"): ?>
        Swal.fire({ title: 'Ditolak!', text: 'Anggota masih memiliki pinjaman aktif.', icon: 'warning' });
    <?php elseif($status_proses == "stok_kurang"): ?>
        Swal.fire({ title: 'Stok Habis!', text: 'Jumlah barang melebihi stok.', icon: 'error' });
    <?php endif; ?>
</script>
</body>
</html>