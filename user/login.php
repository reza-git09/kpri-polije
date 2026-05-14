<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include __DIR__ . "/../config/koneksi.php";

// 🔒 Proses Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$username' AND password='$password'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        $_SESSION['login'] = true;
        $_SESSION['user']  = $data['username'];
        $_SESSION['role']  = $data['role'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];

        header("Location: ../index.php");
        exit;
    } else {
        $error = "Username atau kata sandi salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - KPRI POLIJE</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/kpripolije.png">

    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
        }

        .main-container {
            display: flex;
            width: 900px;
            height: 600px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        /* BAGIAN KIRI */
        .left-side {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            position: relative;
        }

        .left-side h1 {
            font-size: 36px;
            margin-bottom: 10px;
            font-weight: 600;
            line-height: 1.2;
        }

        .left-side p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.8;
        }

        .btn-outline {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 25px;
            border: 2px solid white;
            border-radius: 30px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            width: fit-content;
            transition: 0.3s;
            cursor: pointer;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary);
        }

        /* BAGIAN KANAN */
        .right-side {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
            position: relative;
        }

        .kop-surat-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .kop-surat-login img {
            width: 45px;
            height: auto;
        }

        .kop-header-text h3 {
            margin: 0;
            font-size: 14px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
        }

        .kop-header-text p {
            margin: 0;
            font-size: 11px;
            color: #666;
            font-weight: 400;
        }

        .right-side h2 {
            font-size: 22px;
            color: #333;
            margin: 0;
            text-align: center;
        }

        .right-side p.subtitle {
            font-size: 13px;
            color: #888;
            margin-bottom: 25px;
            text-align: center;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group {
    position: relative;
    margin-bottom: 20px;
}

/* ICON USER & LOCK */
.input-group i.fa-user,
.input-group i.fa-lock {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    z-index: 2;
}

/* INPUT */
.input-group input {
    width: 100%;
    padding: 12px 55px 12px 45px;
    background: #f0f2f5;
    border: 2px solid transparent;
    border-radius: 10px;
    outline: none;
    transition: 0.3s;
}

/* FOCUS INPUT */
.input-group input:focus {
    border-color: var(--secondary);
    background: white;
}

/* ICON MATA MASUK KE DALAM INPUT */
.toggle-password {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #2a5298;
    font-size: 15px;
    z-index: 99;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-password:hover {
    color: var(--primary);
}
        /* ICON MATA */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            font-size: 16px;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }

        .error {
            background: #fee2e2;
            color: #d63031;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #d63031;
            text-align: center;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 40px;
            border-radius: 20px;
            width: 600px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            animation: slideDown 0.4s ease-out;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
        }

        .close-btn:hover {
            color: #333;
        }

        .footer-text {
            margin-top: 20px;
            font-size: 11px;
            color: #bbb;
            text-align: center;
        }

        .copyright-dev {
            margin-top: 5px;
            font-size: 10px;
            color: #999;
            text-align: center;
            font-style: italic;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

<div class="main-container">

    <!-- KIRI -->
    <div class="left-side">
        <h1>Selamat Datang Kembali!</h1>

        <p>
            Sistem Informasi Akuntansi & Keanggotaan Koperasi Pegawai Republik Indonesia (KPRI) Politeknik Negeri Jember.
        </p>

        <div class="btn-outline" onclick="openModal()">
            Baca Selengkapnya
        </div>
    </div>

    <!-- KANAN -->
    <div class="right-side">

        <div class="kop-surat-login">
            <img src="../images/polijeee.png" alt="Logo Polije">

            <div class="kop-header-text">
                <h3>KPRI POLIJE</h3>
                <p>Politeknik Negeri Jember</p>
            </div>

            <img src="../images/kpripolije.png" alt="Logo KPRI">
        </div>

        <h2>Masuk</h2>
        <p class="subtitle">Akses dashboard sistem informasi</p>

        <?php if(isset($error)) : ?>
            <div class="error">
                <i class="fa fa-exclamation-circle"></i>
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- USERNAME -->
            <div class="input-group">
                <i class="fa fa-user"></i>

                <input type="text"
                       name="username"
                       placeholder="Nama Pengguna"
                       required>
            </div>

            <!-- PASSWORD -->
            <div class="input-group">
                <i class="fa fa-lock"></i>

                <input type="password"
                       name="password"
                       id="password"
                       placeholder="Kata Sandi"
                       required>

                <span 
                class="toggle-password"
                  onmousedown="showPassword()"
                    onmouseup="hidePassword()"
                    onmouseleave="hidePassword()">

    <i class="fa fa-eye" id="eyeIcon"></i>
</span>
            </div>

            <button type="submit" name="login" class="login-btn">
                MASUK SEKARANG
            </button>

        </form>

        <div class="footer-text">
            © <?= date('Y') ?> KPRI POLIJE. All Rights Reserved.

            <div class="copyright-dev">
                Design by Syntax Error Polije
            </div>
        </div>

    </div>
</div>

<!-- MODAL -->
<div id="infoModal" class="modal">

    <div class="modal-content">

        <span class="close-btn" onclick="closeModal()">
            &times;
        </span>

        <h2 style="color: var(--primary);">
            Tentang KPRI POLIJE
        </h2>

        <p style="text-align: justify;">
            Koperasi Pegawai Republik Indonesia (KPRI) Politeknik Negeri Jember adalah organisasi ekonomi yang bergerak untuk meningkatkan kesejahteraan seluruh anggotanya melalui pengelolaan keuangan yang amanah, transparan, dan terintegrasi secara digital.
        </p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <h3>Layanan Kami:</h3>

        <ul>
            <li><strong>Simpan Pinjam:</strong> Solusi keuangan bagi kebutuhan mendesak anggota.</li>
            <li><strong>Pertokoan:</strong> Menyediakan kebutuhan pokok dan perlengkapan harian.</li>
            <li><strong>Kredit Elektronik:</strong> Kemudahan cicilan barang elektronik & kendaraan.</li>
            <li><strong>Sistem Informasi:</strong> Akses transparan laporan keuangan secara online.</li>
        </ul>

        <div style="background: #f0f2f5; padding: 20px; border-radius: 10px; margin-top: 20px;">
            <p style="margin:0;">
                <strong>Hubungi Kami:</strong><br>
                Email: kpri@polije.ac.id | Telp: (0331) 333532
            </p>
        </div>

        <div class="copyright-dev" style="margin-top: 20px;">
            Developed by: <b>Syntax Error Polije</b>
        </div>

    </div>
</div>

<script>

    function openModal() {
        document.getElementById("infoModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("infoModal").style.display = "none";
    }

    // Tutup modal jika klik di luar area konten
    window.onclick = function(event) {
        let modal = document.getElementById("infoModal");

        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // SHOW / HIDE PASSWORD
    // TEKAN & TAHAN UNTUK MELIHAT PASSWORD
function showPassword() {

    let passwordInput = document.getElementById("password");
    let eyeIcon = document.getElementById("eyeIcon");

    passwordInput.type = "text";

    eyeIcon.classList.remove("fa-eye");
    eyeIcon.classList.add("fa-eye-slash");
}

// LEPAS UNTUK MENUTUP PASSWORD
function hidePassword() {

    let passwordInput = document.getElementById("password");
    let eyeIcon = document.getElementById("eyeIcon");

    passwordInput.type = "password";

    eyeIcon.classList.remove("fa-eye-slash");
    eyeIcon.classList.add("fa-eye");
}
    

</script>

</body>
</html>