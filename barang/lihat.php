<?php 
// Memulai session jika belum ada session yang berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memeriksa status login, jika belum login akan dialihkan ke halaman login
if (!isset($_SESSION['login'])) { 
    header("Location: ../user/login.php"); 
    exit; 
}

// Menyertakan file koneksi database dan mengatur base URL asset
require_once '../config/koneksi.php'; 
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/kpri polije/"; 
$role_saat_ini = $_SESSION['role'] ?? 'user';

// Mengambil data seluruh barang dari database diurutkan dari yang terbaru
$query = mysqli_query($koneksi, "SELECT * FROM tb_barang ORDER BY id_barang DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stok Barang - KPRI POLIJE</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="<?= $base_url ?>images/kpripolije.png">

    <style>
        /* Pengaturan variabel warna utama dan font global */
        :root { --primary: #1a73e8; --bg-body: #f4f7fe; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-body); }
        .main-wrapper { margin-left: 260px; padding: 25px; }
        .top-header { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:15px 25px; border-radius:12px; margin-bottom:25px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
        .header-logos img { height:40px; margin-left:10px; }

        /* Komponen kartu penampung tabel */
        .card { background:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }

        /* Pengaturan layout tabel data */
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th { background:#f8fafc; padding:16px; font-size:11px; text-transform:uppercase; color:#64748b; text-align:left; }
        td { padding:16px; border-bottom:1px solid #f1f5f9; font-size:14px; color:#334155; }

        /* Desain label status dan badge ID */
        .badge-id { background: #e2e8f0; color: #1a73e8; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge { padding:5px 10px; border-radius:6px; font-size:11px; font-weight:700; }
        
        /* Tombol aksi tambah, edit, dan hapus */
        .btn-add { background:var(--primary); color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; display:flex; align-items:center; gap:8px; }
        .btn-action { 
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px; 
            color: #fff; 
            text-decoration: none; 
            border: none; 
            cursor: pointer; 
            font-size: 16px;
            transition: transform 0.1s ease, opacity 0.2s;
        }
        .btn-action:hover { opacity: 0.9; }
        .btn-action:active { transform: scale(0.95); }

        /* Input pencarian dan gambar produk */
        #search { padding:10px 15px; border:1px solid #e2e8f0; border-radius:8px; width:300px; }
        .img-barang { width:50px; height:50px; object-fit:cover; border-radius:8px; }
    </style>
</head>

<body>

<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">

    <header class="top-header">
        <h3 style="margin:0;">
            <i class="fa-solid fa-boxes-stacked" style="color:var(--primary); margin-right:10px;"></i>
            Stok Barang
        </h3>

        <div class="header-logos">
            <img src="<?= $base_url; ?>images/polijeee.png">
            <img src="<?= $base_url; ?>images/kpripolije.png">
        </div>
    </header>

    <div class="card">

        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <input type="text" id="search" placeholder="Cari Nama atau ID..." onkeyup="filter()">

            <?php if($role_saat_ini == 'admin'): ?>
                <a href="tambah.php" class="btn-add">
                    <i class="fa fa-plus"></i> Tambah Barang
                </a>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Expired</th>
                    <th>Status</th>
                    <?php if($role_saat_ini == 'admin'): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php 
                $no = 1;
                // Looping data hasil query mysql
                while($row = mysqli_fetch_assoc($query)): 
                    $stok = (int)$row['stok'];
                    $exp_date = $row['tgl_kadaluarsa'];
                ?>

                <tr class="data-row">
                    <td><?= $no++; ?></td>

                    <td class="id-barang">
                        <span class="badge-id"><?= $row['id_barang']; ?></span>
                    </td>

                    <td>
                        <?php if(!empty($row['nama_gambar'])): ?>
                            <img src="../images/barang/<?= $row['nama_gambar']; ?>" class="img-barang">
                        <?php else: ?> -
                        <?php endif; ?>
                    </td>

                    <td class="nama-barang"><?= htmlspecialchars($row['nama_barang']); ?></td>

                    <td><?= $stok; ?> <?= $row['satuan']; ?></td>

                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>

                    <td>
                        <?= ($exp_date != '0000-00-00') ? date('d/m/Y', strtotime($exp_date)) : '-'; ?>
                    </td>

                    <td>
                        <?php if($stok <= 10): ?>
                            <span class="badge" style="background:#ffe4e6;color:#e11d48;">KRITIS</span>
                        <?php elseif($stok <= 30): ?>
                            <span class="badge" style="background:#fffbeb;color:#d97706;">MENIPIS</span>
                        <?php else: ?>
                            <span class="badge" style="background:#ecfdf5;color:#059669;">AMAN</span>
                        <?php endif; ?>
                    </td>

                    <?php if($role_saat_ini == 'admin'): ?>
                    <td style="white-space:nowrap; vertical-align: middle;">
                        
                        <a href="edit.php?id=<?= $row['id_barang']; ?>" 
                           class="btn-action" 
                           style="background: #ffa502; margin-right: 8px;" 
                           title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <button onclick="hapus(<?= $row['id_barang']; ?>)" 
                                class="btn-action" 
                                style="background: #ef4444;" 
                                title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </td>
                    <?php endif; ?>
                </tr>

                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
// Fungsi untuk menyaring data tabel secara langsung di sisi client (real-time)
function filter(){
    let input = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('.data-row').forEach(row=>{
        let nama = row.querySelector('.nama-barang').textContent.toLowerCase();
        let id = row.querySelector('.id-barang').textContent.toLowerCase();

        // Menyembunyikan atau menampilkan baris berdasarkan kecocokan keyword
        row.style.display = (nama.includes(input) || id.includes(input)) ? '' : 'none';
    });
}

// Fungsi alert konfirmasi sebelum melakukan penghapusan data
function hapus(id){
    Swal.fire({
        title: 'Hapus data?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((res)=>{
        if(res.isConfirmed){
            // Mengarahkan ke file eksekusi hapus dengan membawa ID data terkait
            window.location = 'hapus.php?id=' + id;
        }
    });
}
</script>

</body>
</html>