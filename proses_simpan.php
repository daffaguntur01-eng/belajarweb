<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

include 'koneksi.php';

// Cek apakah tombol submit diklik atau metode POST digunakan
if (isset($_POST['submit']) || $_SERVER['REQUEST_METHOD'] === 'POST') {

    $uss           = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
    $role          = mysqli_real_escape_string($koneksi, $_POST['role'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

    $pw_raw        = $_POST['password'] ?? '';
    $pw            = !empty($pw_raw) ? password_hash($pw_raw, PASSWORD_DEFAULT) : '';

    // Jika diakses tanpa mengisi data yang wajib
    if (empty($uss) || empty($pw_raw) || empty($role)) {
        echo "<script>alert('Username, Password, dan Role wajib diisi!'); window.history.back();</script>";
        exit;
    }

    // Cek ketersediaan username
    $cek_username = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$uss'");
    if (mysqli_num_rows($cek_username) > 0) {
        echo "<script>alert('Username sudah terpakai, silakan gunakan username lain!'); window.history.back();</script>";
        exit;
    }

    // Insert ke tabel users
    $sql_usr = "INSERT INTO users (username, password, role, created_at) VALUES ('$uss', '$pw', '$role', NOW())";
    $query_usr = mysqli_query($koneksi, $sql_usr);

    if ($query_usr) {
        $id_terakhir = mysqli_insert_id($koneksi);

        if ($role === 'siswa') {
            $nis   = mysqli_real_escape_string($koneksi, $_POST['nis'] ?? '');
            $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas'] ?? '');

            $sql_siswa = "INSERT INTO siswa (nis, nama, kelas, jenis_kelamin, user_id) 
                          VALUES ('$nis', '$uss', '$kelas', '$jenis_kelamin', '$id_terakhir')";

            if (!mysqli_query($koneksi, $sql_siswa)) {
                echo "<script>alert('Gagal simpan ke tabel siswa: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
                exit;
            }

        } else if ($role === 'guru') {
            $nip = mysqli_real_escape_string($koneksi, $_POST['nip'] ?? '');

            $sql_guru = "INSERT INTO guru (nip, nama, jenis_kelamin, user_id) 
                         VALUES ('$nip', '$uss', '$jenis_kelamin', '$id_terakhir')";

            if (!mysqli_query($koneksi, $sql_guru)) {
                echo "<script>alert('Gagal simpan ke tabel guru: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
                exit;
            }
        }

        echo "<script>alert('Data berhasil disimpan!'); window.location.href='tabelusers.php';</script>";
        exit;

    } else {
        echo "<script>alert('Gagal simpan ke tabel users: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
        exit;
    }
} else {
    // Jika file diakses langsung lewat URL browser
    header("Location: tambah_user.php");
    exit;
}
?>