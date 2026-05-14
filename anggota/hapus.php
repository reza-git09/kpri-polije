<?php
session_start();
require_once '../config/koneksi.php';

// 🔒 1. Proteksi Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}

$status_hapus = "";

// 2. Ambil ID dari URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Gunakan transaksi agar jika satu gagal, semua batal (opsional tapi disarankan)
    mysqli_begin_transaction($koneksi);

    try {
        // A. Hapus data di tabel-tabel yang bergantung pada id_anggota ini
        mysqli_query($koneksi, "DELETE FROM tb_simpanan WHERE id_anggota = '$id'");
        mysqli_query($koneksi, "DELETE FROM tb_pinjaman WHERE id_anggota = '$id'");
        
        // B. Baru hapus data anggotanya
        $query = mysqli_query($koneksi, "DELETE FROM tb_anggota WHERE id_anggota = '$id'");

        mysqli_commit($koneksi);
        $status_hapus = "success";
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $status_hapus = "error";
        $error_msg = $e->getMessage();
    }
} else {
    header("location:lihat.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Anggota - KPRI POLIJE</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fe; }
    </style>
</head>
<body>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    <?php if($status_hapus == "success"): ?>
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data anggota dan semua riwayatnya telah dibersihkan.',
            icon: 'success',
            confirmButtonColor: '#4a86e8',
            confirmButtonText: 'Kembali'
        }).then((result) => {
            window.location = 'lihat.php';
        });
    <?php elseif($status_hapus == "error"): ?>
        Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan sistem saat menghapus data.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            window.location = 'lihat.php';
        });
    <?php endif; ?>
    </script>

</body>
</html>