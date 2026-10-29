<?php
// Konfigurasi database
$host = "localhost";    // Alamat server (biasanya localhost)
$user = "root";         // Username default XAMPP/phpMyAdmin
$password = "";         // Password default (kosongkan jika pakai XAMPP)
$db = "acep";  // Ganti dengan nama database Anda di phpMyAdmin

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $db);

// Periksa koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
   //echo "Koneksi berhasil!";
}
?>
