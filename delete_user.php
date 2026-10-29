<?php
// Memanggil file koneksi database agar script bisa berkomunikasi dengan MySQL
require_once "koneksi copy.php";

// Memeriksa apakah parameter 'id' dikirim melalui URL ($_GET) dan nilainya tidak kosong
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    // Mengamankan nilai ID dari Karakter Spesial (SQL Injection) sebelum diproses ke query MySQL
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Menyusun query DELETE dengan LEFT JOIN untuk menghapus data secara serentak
    // Data di tabel 'users', 'siswa', dan 'guru' yang terikat dengan user_id tersebut akan ikut terhapus
    $sql = "DELETE users, siswa, guru 
            FROM users 
            LEFT JOIN siswa ON siswa.user_id = users.id 
            LEFT JOIN guru ON guru.user_id = users.id 
            WHERE users.id = '$id'";

    // Menjalankan query ke database MySQL
    if (mysqli_query($koneksi, $sql)) {
        // Jika penghapusan data berhasil, tampilkan notifikasi alert dan alihkan halaman kembali ke 'tabel copy.php'
        echo "<script>alert('user berhasil di hapus'); window.location.href='tabelusers.php';</script>";
    } else {
        // Jika penghapusan data gagal (misal ada error constraint/query), tampilkan pesan gagal dan kembalikan ke 'tabel copy.php'
        echo "<script>alert('gagal untuk menghapus data user'); window.location.href='tabelusers.php';</script>";
    }

} else {
    // Jika tidak ada parameter 'id' di URL, berikan notifikasi alert bahwa ID tidak ditemukan dan kembalikan ke 'tabel copy.php'
    echo "<script>alert('ID tidak ditemukan!'); window.location.href='tabelusers.php';</script>";
}
?>