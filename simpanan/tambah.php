<?php 
session_start();

// 1. Proteksi & Koneksi
if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

include '../config/koneksi.php'; 

// 2. Variabel Dasar
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$active_menu = 'simpanan';
$status_insert = ""; 
$error_msg = "";

$anggota_query = mysqli_query($koneksi, "SELECT id_anggota, nama FROM tb_anggota ORDER BY id_anggota DESC");

// 4. Logika Simpan Data
if (isset($_POST['submit'])) {
    $id_anggota     = mysqli_real_escape_string($koneksi, $_POST['id_anggota']);
    $jenis_simpanan = mysqli_real_escape_string($koneksi, $_POST['jenis_simpanan']);
    $nominal        = mysqli_real_escape_string($koneksi, $_POST['nominal']);
    $tanggal        = mysqli_real_escape_string($koneksi, $_POST['tanggal']);

    // Validasi di sisi server (PHP)
    if ($nominal <= 0) {
        $status_insert = "invalid_nominal";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO tb_simpanan (id_anggota, jenis_simpanan, nominal, tanggal) 
                                          VALUES ('$id_anggota', '$jenis_simpanan', '$nominal', '$tanggal')");

        if ($insert) {
            $status_insert = "success";
        } else {
            $status_insert = "error";
            $error_msg = mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry Simpanan - KPRI POLIJE</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">

    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f4f7fe; color: #333; }
        .main { margin-left: 260px; padding: 25px; box-sizing: border-box; transition: 0.3s; }
        @media (max-width: 768px) { .main { margin-left: 0; } }
        .top-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: white; padding: 15px 25px; height: 50px;
            border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .header-logos img { height: 40px; margin-left: 10px; }
        .form-card { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 450px; margin: 0 auto; 
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #64748b; }
        .form-control { 
            width: 100%; padding: 10px; border: 1px solid #e2e8f0; 
            border-radius: 8px; box-sizing: border-box; font-size: 14px; font-family: inherit;
        }
        .btn-simpan { 
            background: #1a73e8; color: white; border: none; 
            padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer; 
            font-size: 14px; transition: 0.3s;
        }
        .btn-simpan:hover { background: #1557b0; }
        .btn-batal { 
            display: block; text-align: center; padding: 12px; margin-top: 10px;
            background: #f1f5f9; color: #64748b; border-radius: 8px; 
            text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s;
        }
        .btn-batal:hover { background: #e2e8f0; color: #334155; }
    </style>
</head>
<body>

    <?php include '../layout/sidebar.php'; ?>

    <div class="main">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;">
                <i class="fa-solid fa-plus-circle" style="color: #1a73e8; margin-right: 10px;"></i> Entry Simpanan
            </h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Nama Anggota</label>
                    <select name="id_anggota" class="form-control" required>
                        <option value="">-- Pilih Anggota --</option>
                        <?php while($row = mysqli_fetch_assoc($anggota_query)) : ?>
                            <option value="<?= $row['id_anggota']; ?>"><?= $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Simpanan</label>
                    <select name="jenis_simpanan" class="form-control" required>
                        <option value="Pokok">Simpanan Pokok</option>
                        <option value="Wajib">Simpanan Wajib</option>
                        <option value="Sukarela">Simpanan Sukarela</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nominal Setoran (Rp)</label>
                    <!-- Perbaikan: Tambah min="1" -->
                    <input type="number" name="nominal" class="form-control" placeholder="Contoh: 100000" min="1" required>
                </div>

                <div class="form-group">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>

                <button type="submit" name="submit" class="btn-simpan">Simpan Transaksi</button>
                <a href="lihat.php" class="btn-batal">Batal & Kembali</a>
            </form>
        </div>
    </div>

    <script>
    <?php if($status_insert == "success"): ?>
        Swal.fire({ title: 'Berhasil!', text: 'Simpanan berhasil dicatat.', icon: 'success' }).then(() => { window.location = 'lihat.php'; });
    <?php elseif($status_insert == "invalid_nominal"): ?>
        Swal.fire({ title: 'Gagal!', text: 'Nominal tidak boleh nol atau negatif.', icon: 'warning' });
    <?php elseif($status_insert == "error"): ?>
        Swal.fire({ title: 'Gagal!', text: 'Terjadi kesalahan sistem.', icon: 'error' });
    <?php endif; ?>
    </script>
</body>
</html>