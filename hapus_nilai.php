<?php
session_start();
require_once 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['role'])) {
    header("Location: login_user.php");
    exit();
}

// Proteksi Role Guru
if (strtolower($_SESSION['role']) !== 'guru') {
    die("<script>alert('Akses ditolak! Hanya guru yang boleh menghapus data.'); window.history.back();</script>");
}

// Tangkap ID nilai yang akan dihapus dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Jalankan query DELETE berdasarkan ID nilai_rapor
    $stmt = mysqli_prepare($koneksi, "DELETE FROM nilai_rapor WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        // Kembali ke halaman sebelumnya secara otomatis
        echo "<script>
                alert('Data nilai berhasil dihapus!');
                if (document.referrer) {
                    window.location.href = document.referrer;
                } else {
                    window.location.href = 'nilai_siswa.php';
                }
              </script>";
        exit();
    } else {
        mysqli_stmt_close($stmt);
        echo "<script>
                alert('Gagal menghapus data nilai!');
                window.history.back();
              </script>";
        exit();
    }
} else {
    // Jika ID tidak valid, kembali ke halaman sebelumnya
    echo "<script>
            window.history.back();
          </script>";
    exit();
}
?>