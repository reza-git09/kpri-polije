<?php 
session_start();

// 1. Proteksi Login & Koneksi
if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

include "../config/koneksi.php";

// 2. Variabel Penampung Status
$status = "";
$message = "";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 3. Proteksi: Cek jenis simpanan sebelum hapus
    $cek_query = mysqli_query($koneksi, "SELECT jenis_simpanan FROM tb_simpanan WHERE id_simpanan = '$id'");
    $data = mysqli_fetch_assoc($cek_query);

    if (!$data) {
        $status = "not_found";
    } elseif ($data['jenis_simpanan'] == 'Sukarela') {
        // Hanya izinkan hapus jika jenisnya Sukarela
        $delete = mysqli_query($koneksi, "DELETE FROM tb_simpanan WHERE id_simpanan = '$id'");
        
        if ($delete) {
            $status = "success";
        } else {
            $status = "error";
            $message = mysqli_error($koneksi);
        }
    } else {
        // Jika mencoba menghapus Pokok atau Wajib
        $status = "forbidden";
    }
} else {
    header("Location: lihat.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Processing...</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef2f7; }
    </style>
</head>
<body>

<script>
    <?php if($status == "success"): ?>
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data simpanan sukarela berhasil dihapus.',
            icon: 'success',
            confirmButtonColor: '#4a86e8'
        }).then(() => { window.location = 'lihat.php'; });

    <?php elseif($status == "forbidden"): ?>
        Swal.fire({
            title: 'Gagal Hapus!',
            text: 'Simpanan Pokok dan Wajib bersifat permanen dan tidak boleh dihapus.',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        }).then(() => { window.location = 'lihat.php'; });

    <?php elseif($status == "not_found"): ?>
        Swal.fire({
            title: 'Data Tidak Ada!',
            text: 'ID simpanan tidak ditemukan di database.',
            icon: 'warning'
        }).then(() => { window.location = 'lihat.php'; });

    <?php else: ?>
        Swal.fire({
            title: 'Error!',
            text: 'Terjadi kesalahan: <?= $message; ?>',
            icon: 'error'
        }).then(() => { window.location = 'lihat.php'; });
    <?php endif; ?>
</script>

</body>
</html>