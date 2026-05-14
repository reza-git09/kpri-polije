<?php 
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../user/login.php"); exit; }

require_once '../config/koneksi.php'; 
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$role_saat_ini = $_SESSION['role'] ?? 'user';

// Ambil Data Barang
$query = mysqli_query($koneksi, "SELECT * FROM tb_barang ORDER BY id_barang DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stok Barang - KPRI POLIJE</title>
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
        .img-barang { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .btn-add { background: var(--primary); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-action { padding: 8px 12px; border-radius: 6px; color: white; border: none; cursor: pointer; text-decoration: none; font-size: 13px; }
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        #search { padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; width: 300px; outline: none; }
    </style>
</head>
<body>

    <?php include '../layout/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="top-header">
            <h3 style="margin:0; font-size: 18px; color: #334155;">
                <i class="fa-solid fa-boxes-stacked" style="color: var(--primary); margin-right: 10px;"></i> Stok Barang
            </h3>
            <div class="header-logos">
                <img src="<?= $base_url; ?>images/polijeee.png">
                <img src="<?= $base_url; ?>images/kpripolije.png">
            </div>
        </header>

        

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <input type="text" id="search" placeholder="Cari Nama atau ID Barang..." onkeyup="filter()">
                <?php if($role_saat_ini == 'admin'): ?>
                    <a href="tambah.php" class="btn-add"><i class="fa-solid fa-plus"></i> Tambah Barang</a>
                <?php endif; ?>
            </div>

            <div style="overflow-x: auto;">
                <table id="tabelBarang">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama Barang</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Kadaluwarsa</th>
                            <th>Status</th>
                            
                            <?php if($role_saat_ini == 'admin'): ?><th>Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        $today = new DateTime();
                        while($row = mysqli_fetch_assoc($query)): 
                            $exp_date = $row['tgl_kadaluarsa'] ?? '0000-00-00';
                            $stok = (int)$row['stok'];
                            
                            $is_expired = false;
                            $is_exp_nearing = false;
                            if($exp_date != '0000-00-00') {
                                $exp = new DateTime($exp_date);
                                $days_left = (int)$today->diff($exp)->format('%r%a');
                                if ($days_left < 0) $is_expired = true;
                                elseif ($days_left <= 30) $is_exp_nearing = true;
                            }
                            
                            $is_stok_kritis = ($stok <= 10);
                            $is_stok_nearing = ($stok > 10 && $stok <= 30);
                        ?>
                        <tr class="data-row">
                            <td><?= $no++; ?></td>
                            <td class="id-barang"><span class="badge" style="background:#e2e8f0; color:#475569;"><?= $row['id_barang']; ?></span></td>
                            <td>
                                <?php if(!empty($row['nama_gambar'])): ?>
                                    <img src="../images/barang/<?= $row['nama_gambar']; ?>" class="img-barang">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f8f9fa;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px dashed #e2e8f0;"><i class="fa fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="nama-barang" style="font-weight: 600;"><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td><strong><?= $stok; ?></strong> <?= htmlspecialchars($row['satuan']); ?></td>
                             <td><?php echo $row['harga']; ?></td>
                            <td>
                                <div><?= ($exp_date != '0000-00-00') ? date('d/m/Y', strtotime($exp_date)) : '-'; ?></div>
                                <?php if($is_expired): ?> <small style="color: #ef4444; font-weight: 700;">Kadaluarsa!</small>
                                <?php elseif($is_exp_nearing): ?> <small style="color: #f59e0b; font-weight: 600;">Segera Kadaluarsa</small> <?php endif; ?>
                            </td>
                            <td>
                                <?php if($is_stok_kritis): ?> <span class="badge" style="background:#fff1f2; color:#e11d48;">KRITIS</span>
                                <?php elseif($is_stok_nearing): ?> <span class="badge" style="background:#fffbeb; color:#d97706;">MENIPIS</span>
                                <?php else: ?> <span class="badge" style="background:#ecfdf5; color:#059669;">AMAN</span> <?php endif; ?>
                            </td>
                            <?php if($role_saat_ini == 'admin'): ?>
                            <td>
                                <a href="edit.php?id=<?= $row['id_barang']; ?>" class="btn-action" style="background:#f59e0b;" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <button onclick="hapus(<?= $row['id_barang']; ?>)" class="btn-action" style="background:#ef4444;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filter() {
            let input = document.getElementById('search').value.toLowerCase().trim();
            let rows = document.querySelectorAll('.data-row');
            
            rows.forEach(row => {
                let nama = row.querySelector('.nama-barang').textContent.toLowerCase();
                let id = row.querySelector('.id-barang').textContent.toLowerCase();
                
                // Cari berdasarkan nama atau ID
                row.style.display = (nama.includes(input) || id.includes(input)) ? '' : 'none';
            });
        }

        function hapus(id) {
            Swal.fire({
                title: 'Hapus data?',
                text: "Data barang ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'hapus.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>