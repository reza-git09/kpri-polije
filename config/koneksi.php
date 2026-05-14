<?php
// config/koneksi.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "stok_kpri"; // Pastikan nama database sudah sesuai di phpMyAdmin

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);



// Cek koneksi
if (!$koneksi) {
    // Menggunakan tampilan error yang lebih informatif
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red;'>
            <strong>Koneksi Database Gagal!</strong><br>
            Pesan Error: " . mysqli_connect_error() . "
         </div>");
}

// Set timezone agar waktu transaksi di Dashboard akurat (WIB)
date_default_timezone_set('Asia/Jakarta');

// (Opsional) Set karakter set agar simbol seperti Rp atau karakter khusus tampil benar
mysqli_set_charset($koneksi, "utf8");
?>