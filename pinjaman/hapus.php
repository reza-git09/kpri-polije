<?php 
session_start();
require_once '../config/koneksi.php';

// 🔒 1. Proteksi Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}

// 2. Cek apakah ada ID yang dikirim
if (isset($_GET['id'])) {
    $id_pinjaman = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 3. Jalankan Query Hapus
    $query = mysqli_query($koneksi, "DELETE FROM tb_pinjaman WHERE id_pinjaman = '$id_pinjaman'");

    // Kita butuh sedikit HTML/JS untuk menampilkan SweetAlert
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        
        <style>
            body { font-family: 'Poppins', sans-serif; background: #f4f7fe; }
        </style>
    </head>
    <body>";

    if ($query) {
        echo "<script>
            Swal.fire({
                title: 'Terhapus!',
                text: 'Data transaksi telah dihapus dari sistem.',
                icon: 'success',
                confirmButtonColor: '#4a90e2'
            }).then(() => {
                window.location='lihat.php';
            });
        </script>";
    } else {
        $error = mysqli_error($koneksi);
        echo "<script>
            Swal.fire({
                title: 'Gagal Hapus!',
                text: 'Terjadi kesalahan: $error',
                icon: 'error'
            }).then(() => {
                window.location='lihat.php';
            });
        </script>";
    }
    echo "</body></html>";
} else {
    header("Location: lihat.php");
    exit;
}
?>