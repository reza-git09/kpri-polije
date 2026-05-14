<?php
session_start();

// 🔒 1. PROTEKSI LOGIN & ROLE
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}

require_once '../config/koneksi.php';

// 2. AMBIL & AMANKAN ID
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : null;

// Mulai Output buffering untuk menangani template pop-up
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    \<link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fe; }
    </style>
</head>
<body>

<?php
if ($id) {
    // 3. CEK DATA & GAMBAR
    $cek_gambar = mysqli_query($koneksi, "SELECT nama_gambar FROM tb_barang WHERE id_barang = '$id'");
    $data = mysqli_fetch_assoc($cek_gambar);

    if ($data) {
        $nama_file = $data['nama_gambar'];
        $path_file = "../images/barang/" . $nama_file;

        // Hapus file fisik
        if (!empty($nama_file) && file_exists($path_file)) {
            unlink($path_file);
        }

        // 4. HAPUS DARI DATABASE
        $query = mysqli_query($koneksi, "DELETE FROM tb_barang WHERE id_barang = '$id'");

        if ($query) {
            echo "<script>
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'Data barang dan foto telah berhasil dihapus.',
                    icon: 'success',
                    confirmButtonColor: '#4a90e2'
                }).then(() => {
                    window.location='lihat.php';
                });
            </script>";
        } else {
            $error = addslashes(mysqli_error($koneksi));
            echo "<script>
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan: $error',
                    icon: 'error'
                }).then(() => {
                    window.location='lihat.php';
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Tidak Ditemukan!',
                text: 'Data barang tidak ada di database.',
                icon: 'warning'
            }).then(() => {
                window.location='lihat.php';
            });
        </script>";
    }
} else {
    echo "<script>window.location='lihat.php';</script>";
}
?>

</body>
</html>