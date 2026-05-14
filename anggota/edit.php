<?php 
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../user/login.php"); exit; }

include "../config/koneksi.php";
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 

$status_update = "";
$error_msg = "";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $query = mysqli_query($koneksi, "SELECT * FROM tb_anggota WHERE id_anggota = '$id'");
    $data = mysqli_fetch_assoc($query);
    if (!$data) { header("Location: anggota.php"); exit; }
}

if (isset($_POST['update'])) {
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp  = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $foto_lama = $data['foto'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $ekstensi = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_foto_baru = 'anggota_' . time() . '.' . $ekstensi;
        $folder = '../images/anggota/';
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $nama_foto_baru)) {
            if ($foto_lama != 'default.png' && file_exists($folder . $foto_lama)) { unlink($folder . $foto_lama); }
            $foto_lama = $nama_foto_baru;
        }
    }

    $sql = "UPDATE tb_anggota SET nama='$nama', alamat='$alamat', no_hp='$no_hp', foto='$foto_lama' WHERE id_anggota='$id'";
    if (mysqli_query($koneksi, $sql)) { $status_update = "success"; } else { $status_update = "error"; $error_msg = mysqli_error($koneksi); }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anggota - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f4f7fe; color: #333; }
        .main-wrapper { margin-left: 260px; padding: 25px; box-sizing: border-box; transition: 0.3s; }
        @media (max-width: 768px) { .main-wrapper { margin-left: 0; } }

        /* NAVBAR HEADER SERAGAM */
        .top-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: white; padding: 15px 25px; height: 50px;
            border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .header-logos { display: flex; align-items: center; }
        .header-logos img { height: 40px; margin-left: 10px; }
        
        .content-container { display: flex; gap: 20px; align-items: flex-start; max-width: 900px; margin: 0 auto; }
        .form-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); flex: 2; }
        .photo-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); flex: 1; text-align: center; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #64748b; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-size: 14px; font-family: inherit; }
        
        .btn-save { background: #1a73e8; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer; margin-bottom: 10px; font-size: 14px; transition: 0.3s; }
        .btn-save:hover { background: #1557b0; }
        .btn-cancel { display: block; text-align: center; padding: 12px; background: #f1f5f9; color: #64748b; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
        
        .foto-preview { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #f4f7fe; }
    </style>
</head>
<body>

    <?php include '../layout/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;">
                <i class="fa-solid fa-user-edit" style="color: #f59e0b; margin-right: 10px;"></i> Edit Anggota
            </h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        <form method="POST" enctype="multipart/form-data">
            <div class="content-container">
                <div class="form-card">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" required><?= htmlspecialchars($data['alamat']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <input type="number" name="no_hp" value="<?= htmlspecialchars($data['no_hp']); ?>" required>
                    </div>
                    
                    <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
                    <a href="lihat.php" class="btn-cancel">Batal & Kembali</a>
                </div>

                <div class="photo-card">
                    <label>Foto Saat Ini</label>
                    <img src="<?= $base_url . 'images/anggota/' . $data['foto']; ?>" class="foto-preview">
                    <input type="file" name="foto" accept="image/*">
                    <small style="color: #94a3b8; font-size: 11px; margin-top: 10px; display: block;">Ganti foto (Opsional)</small>
                </div>
            </div>
        </form>
    </div>

    <script>
    <?php if($status_update == "success"): ?>
        Swal.fire({ title: 'Berhasil!', text: 'Data telah diperbarui.', icon: 'success' }).then(() => { window.location = 'lihat.php'; });
    <?php elseif($status_update == "error"): ?>
        Swal.fire({ title: 'Gagal!', text: '<?= $error_msg; ?>', icon: 'error' });
    <?php endif; ?>
    </script>
</body>
</html>