<?php
session_start();
require_once '../config/koneksi.php';

// 🔒 Proteksi Login
if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

$status_update = "";

// Ambil ID
if (!isset($_GET['id'])) {
    header("Location: lihat.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data barang
$ambil_data = mysqli_query($koneksi, "SELECT * FROM tb_barang WHERE id_barang = '$id'");
$data = mysqli_fetch_array($ambil_data);

if (!$data) {
    header("Location: lihat.php");
    exit;
}

// Proses Update
if (isset($_POST['update'])) {
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $harga   = mysqli_real_escape_string($koneksi, $_POST['harga']); 
    $stok    = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $satuan  = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $tgl_exp = mysqli_real_escape_string($koneksi, $_POST['tgl_kadaluarsa']);
    
    $gambar_nama = $_FILES['gambar']['name'];
    $gambar_tmp  = $_FILES['gambar']['tmp_name'];
    
    if (!empty($gambar_nama)) {
        $ekstensi = strtolower(pathinfo($gambar_nama, PATHINFO_EXTENSION));
        $nama_baru = time() . "_" . str_replace(' ', '_', strtolower($nama)) . "." . $ekstensi;
        $tujuan = "../images/barang/" . $nama_baru;
        
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ekstensi, $allowed)) {
            if (!empty($data['nama_gambar']) && file_exists("../images/barang/" . $data['nama_gambar'])) {
                unlink("../images/barang/" . $data['nama_gambar']);
            }
            move_uploaded_file($gambar_tmp, $tujuan);

            $update_sql = "UPDATE tb_barang SET 
                nama_barang = '$nama',
                harga = '$harga',
                stok = '$stok',
                satuan = '$satuan', 
                tgl_kadaluarsa = '$tgl_exp',
                nama_gambar = '$nama_baru' 
                WHERE id_barang = '$id'";
        }
    } else {
        $update_sql = "UPDATE tb_barang SET 
            nama_barang = '$nama',
            harga = '$harga',
            stok = '$stok',
            satuan = '$satuan', 
            tgl_kadaluarsa = '$tgl_exp' 
            WHERE id_barang = '$id'";
    }

    if (mysqli_query($koneksi, $update_sql)) {
        $status_update = "success";
    } else {
        $status_update = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - KPRI POLIJE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            margin: 0;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .card-edit {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 35px;
            max-width: 900px;
            margin: auto;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #dbe2ea;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
        }

        .btn-simpan {
            background: #10b981;
            border: none;
            border-radius: 12px;
            padding: 14px;
            color: white;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-simpan:hover {
            background: #059669;
        }

        .btn-batal {
            text-decoration: none;
            color: #64748b;
            font-size: 14px;
            margin-top: 10px;
        }

        .preview-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        @media(max-width: 768px){
            .main-content{
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<?php include '../layout/sidebar.php'; ?>

<div class="main-content">
    <div class="card-edit">
        <h4 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Barang</h4>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label">Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($data['nama_barang']); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control" value="<?= $data['harga']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?= $data['stok']; ?>" min="0" oninput="if(this.value < 0) this.value = 0" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Satuan</label>
                    <select name="satuan" class="form-select" required>
                        <option value="pcs" <?= ($data['satuan'] == 'pcs') ? 'selected' : ''; ?>>pcs</option>
                        <option value="kg" <?= ($data['satuan'] == 'kg') ? 'selected' : ''; ?>>kg</option>
                        <option value="box" <?= ($data['satuan'] == 'box') ? 'selected' : ''; ?>>box</option>
                        <option value="liter" <?= ($data['satuan'] == 'liter') ? 'selected' : ''; ?>>liter</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kadaluarsa</label>
                <input type="date" name="tgl_kadaluarsa" class="form-control" value="<?= $data['tgl_kadaluarsa']; ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Produk</label><br>
                <div class="mb-2">
                    <?php if(!empty($data['nama_gambar'])): ?>
                        <img src="../images/barang/<?= $data['nama_gambar']; ?>" class="preview-img">
                    <?php else: ?>
                        <div class="preview-img bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="gambar" class="form-control">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="update" class="btn btn-simpan">Simpan Perubahan</button>
                <a href="lihat.php" class="btn btn-batal text-center">Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if($status_update === "success"): ?>
Swal.fire({
    title: 'Berhasil!',
    text: 'Data berhasil diupdate',
    icon: 'success',
    confirmButtonColor: '#10b981'
}).then(() => {
    window.location = 'lihat.php';
});
<?php elseif($status_update === "error"): ?>
Swal.fire({
    title: 'Gagal!',
    text: 'Terjadi kesalahan saat mengupdate data',
    icon: 'error'
});
<?php endif; ?>
</script>

</body>
</html>