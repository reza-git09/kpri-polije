<?php 
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
include "../config/koneksi.php";

$status_simpan = "";
$error_msg = "";

if (isset($_POST['submit'])) {
    // Baris 15 aman selama $_POST['nama'] dikirim dari form dengan benar
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp  = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    $foto = 'default.png'; 

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_file = $_FILES['foto']['name'];
        $tmp_file  = $_FILES['foto']['tmp_name'];
        $ekstensi  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $nama_foto_baru = 'anggota_' . time() . '.' . $ekstensi;
        $folder = '../images/anggota/';
        
        if (!file_exists($folder)) { mkdir($folder, 0777, true); }
        
        if (move_uploaded_file($tmp_file, $folder . $nama_foto_baru)) {
            $foto = $nama_foto_baru;
        }
    }

    $query = mysqli_query($koneksi, "INSERT INTO tb_anggota (nama, alamat, no_hp, foto) VALUES ('$nama', '$alamat', '$no_hp', '$foto')");

    if ($query) {
        $status_simpan = "success";
    } else {
        $status_simpan = "error";
        $error_msg = mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f4f7fe; }
        .main-wrapper { margin-left: 260px; padding: 25px; box-sizing: border-box; }
        
        .top-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: white; padding: 15px 25px; height: 50px;
            border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .header-logos img { height: 40px; margin-left: 10px; }
        
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 450px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #64748b; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .btn-save { background: #1a73e8; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer; }
        .btn-cancel { display: block; text-align: center; margin-top: 10px; color: #64748b; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;"><i class="fa-solid fa-user-plus"></i> Tambah Anggota</h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2" placeholder="Masukkan alamat" required></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp</label>
                    <input 
                        type="text" 
                        name="no_hp" 
                        maxlength="12"
                        pattern="[0-9]{12}"
                        placeholder="Masukkan Nomer Telepon"
                        title="Nomor WhatsApp harus 12 digit angka"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>Foto Anggota</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
                <button type="submit" name="submit" class="btn-save">Simpan Data</button>
                <a href="lihat.php" class="btn-cancel">Batal & Kembali</a>
            </form>
        </div>
    </div>

    <script>
    <?php if($status_simpan == "success"): ?>
        Swal.fire({ title: 'Berhasil!', text: 'Anggota telah ditambahkan.', icon: 'success' })
        .then(() => { window.location = 'lihat.php'; });
    <?php elseif($status_simpan == "error"): ?>
        Swal.fire({ title: 'Gagal!', text: '<?= addslashes($error_msg); ?>', icon: 'error' });
    <?php endif; ?>
    </script>
</body>
</html>