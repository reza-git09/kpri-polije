<?php 
session_start();

// 🔒 1. PROTEKSI LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

include "../config/koneksi.php";

// 2. SETTING DASAR
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$active_menu = 'simpanan';
$status_update = ""; 

// 3. VALIDASI ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: lihat.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query_data = mysqli_query($koneksi, "SELECT s.*, a.nama FROM tb_simpanan s 
                                      JOIN tb_anggota a ON s.id_anggota = a.id_anggota 
                                      WHERE s.id_simpanan = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) { header("Location: lihat.php"); exit; }
$jenis = $data['jenis_simpanan'];

// 4. LOGIKA UPDATE DATA
if (isset($_POST['update'])) {
    if ($jenis == 'Pokok') {
        $status_update = "locked";
    } else {
        $nominal = mysqli_real_escape_string($koneksi, $_POST['nominal']);
        $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
        $update = mysqli_query($koneksi, "UPDATE tb_simpanan SET nominal = '$nominal', tanggal = '$tanggal' WHERE id_simpanan = '$id'");

        if ($update) { $status_update = "success"; } 
        else { $status_update = "error"; $error_msg = mysqli_error($koneksi); }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Simpanan - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f4f7fe; color: #333; }
        .main { margin-left: 260px; padding: 25px; box-sizing: border-box; transition: 0.3s; }
        @media (max-width: 768px) { .main { margin-left: 0; } }
        
        /* NAVBAR HEADER SERAGAM */
        .top-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: white; padding: 15px 25px; height: 50px;
            border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .header-logos { display: flex; align-items: center; }
        .header-logos img { height: 40px; margin-left: 10px; }
        
        /* FORM CARD */
        .form-box { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 450px; margin: 0 auto; 
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #64748b; }
        .form-control { 
            width: 100%; padding: 10px; border: 1px solid #e2e8f0; 
            border-radius: 8px; box-sizing: border-box; font-size: 14px; font-family: inherit;
        }
        
        .btn-update { 
            background: #1a73e8; color: white; border: none; 
            padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer; 
            font-size: 14px; transition: 0.3s;
        }
        .btn-update:hover { background: #1557b0; }
        .btn-update:disabled { background: #cbd5e1; }

        .btn-kembali { 
            display: block; text-align: center; padding: 12px; margin-top: 10px;
            background: #f1f5f9; color: #64748b; border-radius: 8px; 
            text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s;
        }
        .btn-kembali:hover { background: #e2e8f0; color: #334155; }
        
        .info-box { background: #fffbeb; padding: 12px; border-radius: 8px; font-size: 12px; color: #92400e; margin-bottom: 15px; border-left: 4px solid #f59e0b; }
    </style>
</head>
<body>

    <?php include '../layout/sidebar.php'; ?>

    <div class="main">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;">
                <i class="fa-solid fa-edit" style="color: #f59e0b; margin-right: 10px;"></i> Edit Transaksi
            </h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        <div class="form-box">
            <?php if($jenis == 'Pokok'): ?>
                <div class="info-box">Simpanan Pokok terkunci dan tidak dapat diubah.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Anggota</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Jenis Simpanan</label>
                    <input type="text" class="form-control" value="<?= $jenis; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal']; ?>" <?= ($jenis == 'Pokok' ? 'readonly' : ''); ?> required>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="nominal" class="form-control" value="<?= $data['nominal']; ?>" <?= ($jenis == 'Pokok' ? 'readonly' : ''); ?> required>
                </div>

                <?php if($jenis != 'Pokok'): ?>
                    <button type="submit" name="update" class="btn-update">Simpan Perubahan</button>
                <?php else: ?>
                    <button type="button" class="btn-update" disabled>Terkunci</button>
                <?php endif; ?>
                <a href="lihat.php" class="btn-kembali">Batal & Kembali</a>
            </form>
        </div>
    </div>

    <script>
    <?php if($status_update == "success"): ?>
        Swal.fire({ title: 'Berhasil!', text: 'Data diperbarui.', icon: 'success' }).then(() => { window.location = 'lihat.php'; });
    <?php elseif($status_update == "locked"): ?>
        Swal.fire({ title: 'Terkunci!', text: 'Simpanan Pokok tidak dapat diubah.', icon: 'warning' });
    <?php elseif($status_update == "error"): ?>
        Swal.fire({ title: 'Gagal!', text: '<?= $error_msg; ?>', icon: 'error' });
    <?php endif; ?>
    </script>
</body>
</html>