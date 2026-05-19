<?php 
session_start();

// 🔒 1. PROTEKSI LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: ../user/login.php");
    exit;
}

require_once "../config/koneksi.php";
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$active_menu = 'pinjaman'; 

if (!isset($_GET['id'])) {
    header("Location: lihat.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// 2. QUERY DATA
$query_data = mysqli_query($koneksi, "SELECT p.*, a.nama as nama_anggota, b.nama_barang 
                                      FROM tb_pinjaman p 
                                      JOIN tb_anggota a ON p.id_anggota = a.id_anggota 
                                      JOIN tb_barang b ON p.id_barang = b.id_barang
                                      WHERE p.id_pinjaman = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    header("Location: lihat.php");
    exit;
}

$status_proses = "";

// 3. LOGIKA UPDATE (HANYA STATUS RELEVAN)
if (isset($_POST['update'])) {
    $jumlah  = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $status  = mysqli_real_escape_string($koneksi, $_POST['status']);

    // 🔒 VALIDASI AGAR JUMLAH TIDAK MINUS
    if ($jumlah < 1) {
        $jumlah = 1;
    }

    $update = mysqli_query($koneksi, "UPDATE tb_pinjaman SET 
                                      jumlah = '$jumlah', 
                                      tanggal = '$tanggal',
                                      status = '$status'
                                      WHERE id_pinjaman = '$id'");

    if ($update) {
        $status_proses = "success";
    } else {
        $status_proses = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pinjaman - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">

    <style>
        :root { --primary: #4a90e2; --bg-body: #f4f7fe; --sidebar-width: 250px; }
        body { margin:0; font-family:'Poppins', sans-serif; display:flex; background: var(--bg-body); }
        .main { margin-left: var(--sidebar-width); padding: 30px; width: calc(100% - var(--sidebar-width)); min-height: 100vh; box-sizing: border-box; }
        .form-card { background: white; padding: 40px; border-radius: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.02); max-width: 600px; margin: 0 auto; border: 1px solid #edf2f7; }
        .form-control { width: 100%; padding: 12px 15px; border: 1.5px solid #e2e8f0; border-radius: 12px; outline: none; background: #f8fafc; margin-top: 5px; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 15px; width: 100%; border-radius: 12px; font-weight: 600; cursor: pointer; margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px; }
        .label-bold { font-weight: 500; color: #4a5568; font-size: 14px; }
    </style>
</head>
<body>

    <?php include '../layout/sidebar.php'; ?>

    <div class="main">
        <div class="form-card">
            <h2 style="text-align:center; color: var(--primary);">
                <i class="fa-solid fa-pen-to-square"></i> Edit Transaksi
            </h2>
            
            <form action="" method="POST">
                
                <div style="margin-bottom: 15px;">
                    <span class="label-bold">Peminjam:</span>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_anggota']); ?>" disabled>
                </div>

                <div style="margin-bottom: 15px;">
                    <span class="label-bold">Barang:</span>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_barang']); ?>" disabled>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    
                    <div style="flex: 1;">
                        <span class="label-bold">Tanggal:</span>
                        <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal']; ?>" required>
                    </div>

                    <div style="flex: 1;">
                        <span class="label-bold">Jumlah:</span>
                        <input 
                            type="number" 
                            name="jumlah" 
                            class="form-control" 
                            value="<?= $data['jumlah']; ?>" 
                            min="1"
                            oninput="if(this.value < 1) this.value = 1"
                            required
                        >
                    </div>

                </div>

                <div style="margin-bottom: 15px;">
                    <span class="label-bold">Status Utama:</span>
                    <select name="status" class="form-control" style="border-left: 5px solid var(--primary);">
                        <option value="dipinjam" <?= (strtolower($data['status']) != 'kembali') ? 'selected' : ''; ?>>
                            Sedang Dipinjam (Aktif)
                        </option>
                        <option value="kembali" <?= (strtolower($data['status']) == 'kembali') ? 'selected' : ''; ?>>
                            Sudah Kembali (Selesai)
                        </option>
                    </select>
                    <small style="color: #94a3b8;">
                        *Status ini menentukan apakah transaksi dianggap selesai atau tidak.
                    </small>
                </div>

                <button type="submit" name="update" class="btn-save">
                    <i class="fa-solid fa-check-double"></i> Simpan Perubahan
                </button>
                
                <a href="lihat.php" style="display:block; text-align:center; margin-top:15px; color:#94a3b8; text-decoration:none; font-size:13px;">
                    Batal
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if($status_proses == "success"): ?>
            Swal.fire({
                title: 'Update Berhasil!',
                icon: 'success',
                confirmButtonColor: '#4a90e2'
            }).then(() => {
                window.location='lihat.php';
            });
        <?php endif; ?>
    </script>

</body>
</html>