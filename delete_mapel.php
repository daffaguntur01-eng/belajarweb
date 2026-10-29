<?php
// Memanggil file koneksi database
require_once "koneksi.php";

// Memeriksa apakah parameter 'id' dikirim melalui URL ($_GET) dan nilainya tidak kosong
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    // Mengamankan nilai ID dari Karakter Spesial (SQL Injection)
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Menyusun query DELETE khusus untuk tabel 'mapel'
    $sql = "DELETE FROM mapel WHERE id = '$id'";

    // Menjalankan query ke database MySQL
    if (mysqli_query($koneksi, $sql)) {
        // Jika penghapusan data berhasil, alihkan kembali ke halaman 'mapel.php'
        echo "<script>alert('Mata pelajaran berhasil dihapus'); window.location.href='mapel.php';</script>";
    } else {
        // Jika penghapusan data gagal
        echo "<script>alert('Gagal menghapus data mata pelajaran'); window.location.href='mapel.php';</script>";
    }

} else {
    // Jika tidak ada parameter 'id' di URL
    echo "<script>alert('ID mata pelajaran tidak ditemukan!'); window.location.href='mapel.php';</script>";
}
?>