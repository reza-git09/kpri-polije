<?php 
session_start();
if (!isset($_SESSION['login'])) { 
    header("Location: ../user/login.php"); 
    exit; 
}

require_once '../config/koneksi.php'; 
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 

$status_proses = "";
$error_msg = "";

if (isset($_POST['submit'])) {

    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    
    // VALIDASI STOK AGAR TIDAK BISA MINUS
    $stok = (int) $_POST['stok'];

    if ($stok < 0) {
        $status_proses = "error";
        $error_msg = "Stok tidak boleh minus!";
    }

    $satuan  = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $tgl_exp = mysqli_real_escape_string($koneksi, $_POST['tgl_kadaluarsa']); 
    $harga   = mysqli_real_escape_string($koneksi, $_POST['harga']); 

    $gambar_nama = $_FILES['gambar']['name'];
    $nama_file_db = ""; 

    if(!empty($gambar_nama)){
        $folder_tujuan = "../images/barang/";

        if (!is_dir($folder_tujuan)) { 
            mkdir($folder_tujuan, 0777, true); 
        }

        $ekstensi  = strtolower(pathinfo($gambar_nama, PATHINFO_EXTENSION));
        $nama_baru = time() . "_" . str_replace(' ', '_', strtolower($nama)) . "." . $ekstensi;

        if(in_array($ekstensi, ['jpg', 'jpeg', 'png'])){

            if(move_uploaded_file($_FILES['gambar']['tmp_name'], $folder_tujuan . $nama_baru)){
                $nama_file_db = $nama_baru;
            }

        }
    }

    // JALANKAN QUERY JIKA TIDAK ADA ERROR
    if ($status_proses != "error") {

        $sql = "INSERT INTO tb_barang 
        (nama_barang, stok, satuan, tgl_kadaluarsa, nama_gambar, harga) 
        VALUES 
        ('$nama', '$stok', '$satuan', '$tgl_exp', '$nama_file_db', '$harga')";

        if (mysqli_query($koneksi, $sql)) { 
            $status_proses = "success"; 
        } else { 
            $status_proses = "error"; 
            $error_msg = mysqli_error($koneksi); 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang - KPRI POLIJE</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">

    <style>
        :root { 
            --primary: #4a90e2; 
            --bg-body: #f4f7fe; 
            --success: #22c55e; 
        }

        body { 
            margin: 0; 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg-body); 
        }

        .main { 
            margin-left: 260px; 
            padding: 25px; 
            box-sizing: border-box; 
        }
        
        /* Navbar Header */
        .top-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: white; 
            padding: 15px 25px; 
            height: 50px;
            border-radius: 12px; 
            margin-bottom: 25px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }

        .header-logos img { 
            height: 40px; 
            margin-left: 10px; 
        }

        /* Form */
        .form-card { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            max-width: 500px; 
            margin: 0 auto; 
        }

        .form-group { 
            margin-bottom: 15px; 
        }

        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 500; 
            color: #64748b; 
            font-size: 12px; 
        }

        .form-control { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            box-sizing: border-box; 
        }

        .btn-save { 
            background: var(--success); 
            color: white; 
            border: none; 
            padding: 10px; 
            width: 100%; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
        }

        .btn-back { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            color: #718096; 
            text-decoration: none; 
            font-size: 13px; 
        }

        #preview { 
            width: 80px; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 8px; 
            margin-top: 10px; 
            display: none; 
            border: 1px solid #e2e8f0; 
        }
    </style>
</head>

<body>

<?php include '../layout/sidebar.php'; ?>

<main class="main">

    <header class="top-header">

        <h3 style="margin:0; font-size: 18px; color: #334155;">
            <i class="fa-solid fa-boxes-stacked" style="color: var(--primary); margin-right: 10px;"></i> 
            Tambah Barang
        </h3>

        <div class="header-logos">
            <img src="<?= $base_url; ?>images/polijeee.png">
            <img src="<?= $base_url; ?>images/kpripolije.png">
        </div>

    </header>

    <div class="form-card">

        <form action="" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" required>
            </div>

            <div style="display:flex; gap:10px;">

                <div class="form-group" style="flex:1;">
                    <label>Stok</label>

                    <!-- MINIMAL 0 -->
                    <input 
                        type="number" 
                        name="stok" 
                        class="form-control" 
                        min="0" 
                        required
                    >
                </div>

                <div class="form-group" style="flex:1;">
                    <label>Satuan</label>

                    <select name="satuan" class="form-control" required>
                        <option value="kg">Kilogram</option>
                        <option value="Liter">Liter</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                    </select>
                </div>

            </div>

            <div class="form-group">
                <label>Tanggal Kadaluarsa</label>
                <input type="date" name="tgl_kadaluarsa" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="text" name="harga" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Foto Produk</label>

                <input type="file" name="gambar" id="inputGambar" class="form-control">

                <img id="preview">
            </div>

            <button type="submit" name="submit" class="btn-save">
                Simpan Data
            </button>

            <a href="lihat.php" class="btn-back">
                Kembali
            </a>

        </form>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    const inputGambar = document.getElementById('inputGambar');

    inputGambar.onchange = evt => {

        const [file] = inputGambar.files;

        if (file) {

            const preview = document.getElementById('preview');

            preview.src = URL.createObjectURL(file);

            preview.style.display = 'block';
        }
    }

    <?php if($status_proses === "success"): ?>

        Swal.fire(
            'Berhasil!', 
            'Data tersimpan', 
            'success'
        ).then(() => { 
            window.location='lihat.php'; 
        });

    <?php elseif($status_proses === "error"): ?>

        Swal.fire(
            'Gagal!', 
            '<?= addslashes($error_msg); ?>', 
            'error'
        );

    <?php endif; ?>

</script>

</body>
</html>