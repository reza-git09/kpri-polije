<?php
include 'koneksi.php';

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $tgl_exp = mysqli_real_escape_string($koneksi, $_POST['tgl_kadaluarsa']);

    // upload gambar
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        $nama_baru = time() . '_' . $gambar;
        move_uploaded_file($tmp, 'img/' . $nama_baru);

        $update_sql = "UPDATE tb_barang SET 
            nama_barang = '$nama', 
            stok = '$stok', 
            satuan = '$satuan', 
            harga = '$harga',
            tgl_kadaluarsa = '$tgl_exp', 
            nama_gambar = '$nama_baru' 
            WHERE id_barang = '$id'";
    } else {
        $update_sql = "UPDATE tb_barang SET 
            nama_barang = '$nama', 
            stok = '$stok',
            satuan = '$satuan', 
            harga = '$harga',
            tgl_kadaluarsa = '$tgl_exp' 
            WHERE id_barang = '$id'";
    }

    mysqli_query($koneksi, $update_sql);
    header("Location: stok_barang.php");
}

// ambil data
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tb_barang WHERE id_barang='$id'"));
?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $data['id_barang']; ?>">

    <label>Nama Barang</label>
    <input type="text" name="nama_barang" value="<?= $data['nama_barang']; ?>" required>

    <label>Stok</label>
    <input type="number" name="stok" value="<?= $data['stok']; ?>" required>

    <label>Satuan</label>
    <input type="text" name="satuan" value="<?= $data['satuan']; ?>" required>

    <label>Harga</label>
    <input type="number" name="harga" value="<?= $data['harga']; ?>" required>

    <label>Tanggal Kadaluarsa</label>
    <input type="date" name="tgl_kadaluarsa" value="<?= $data['tgl_kadaluarsa']; ?>" required>

    <label>Gambar</label>
    <input type="file" name="gambar">

    <button type="submit" name="update">Update</button>
</form>
