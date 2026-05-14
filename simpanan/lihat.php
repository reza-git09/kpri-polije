<?php 
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../user/login.php"); exit; }
include '../config/koneksi.php'; 

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$active_menu = 'simpanan';
$role_saat_ini = $_SESSION['role'] ?? '';

$query_str = "SELECT s.*, a.nama, a.alamat, a.foto 
              FROM tb_simpanan s 
              JOIN tb_anggota a ON s.id_anggota = a.id_anggota 
              ORDER BY s.id_simpanan ASC";
$query = mysqli_query($koneksi, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Simpanan - KPRI POLIJE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">
    <style>
        :root { --primary: #1a73e8; --bg-body: #f4f7fe; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-body); }
        .main-wrapper { margin-left: 260px; padding: 25px; box-sizing: border-box; }
        .top-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; height: 50px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header-logos img { height: 40px; margin-left: 10px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; padding: 16px; font-size: 11px; text-transform: uppercase; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        .btn-add { background: var(--primary); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-action { padding: 8px 12px; border-radius: 6px; color: white; border: none; cursor: pointer; text-decoration: none; font-size: 13px; margin: 2px; }
        #search { padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; width: 350px; outline: none; }
        .id-badge { background: #e2e8f0; color: #1a73e8; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .user-photo { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 10px; vertical-align: middle; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-wrapper">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;"><i class="fa fa-wallet" style="color: var(--primary); margin-right: 10px;"></i> Data Simpanan</h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <input type="text" id="search" placeholder="Cari berdasarkan Nama atau ID Simpanan..." onkeyup="filter()">
                <?php if($role_saat_ini == 'admin'): ?>
                    <a href="tambah.php" class="btn-add"><i class="fa fa-plus"></i> Tambah Simpanan</a>
                <?php endif; ?>
            </div>

            <table id="tabelSimpanan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Nama Anggota</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <?php if($role_saat_ini == 'admin'): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if(mysqli_num_rows($query) > 0):
                        while($r = mysqli_fetch_assoc($query)): 
                            $foto = !empty($r['foto']) ? $base_url . "images/anggota/" . $r['foto'] : $base_url . "images/default.png";
                            $jenis = strtolower($r['jenis_simpanan']);
                    ?>
                    <tr class="data-row">
                        <td><?= $no++; ?></td>
                        <td class="id-simpanan"><span class="id-badge"><?= $r['id_simpanan']; ?></span></td>
                        <td class="nama">
                            <img src="<?= $foto; ?>" class="user-photo">
                            <strong><?= htmlspecialchars($r['nama']); ?></strong>
                        </td>
                        <td><?= htmlspecialchars($r['jenis_simpanan']); ?></td>
                        <td style="font-weight:600; color:#000;">Rp <?= number_format($r['nominal'], 0, ',', '.'); ?></td>
                        <?php if($role_saat_ini == 'admin'): ?>
                        <td>
                            <?php if ($jenis !== 'simpanan pokok'): ?>
                                <a href="edit.php?id=<?=$r['id_simpanan']?>" class="btn-action" style="background:#f59e0b;" title="Edit"><i class="fa fa-edit"></i></a>
                            <?php endif; ?>

                            <?php if ($jenis !== 'simpanan pokok' && $jenis !== 'simpanan wajib'): ?>
                                <button onclick="hapus(<?=$r['id_simpanan']?>)" class="btn-action" style="background:#ef4444;" title="Hapus"><i class="fa fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filter() {
            let input = document.getElementById('search').value.toLowerCase().trim();
            let rows = document.querySelectorAll('.data-row');
            
            rows.forEach(row => {
                let nama = row.querySelector('.nama').textContent.toLowerCase();
                let id = row.querySelector('.id-simpanan').textContent.toLowerCase();
                
                // Cek apakah input cocok dengan Nama ATAU ID Simpanan
                if (nama.includes(input) || id.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function hapus(id) {
            Swal.fire({
                title: 'Hapus data?',
                text: "Data tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = 'hapus.php?id=' + id;
            });
        }
    </script>
</body>
</html>