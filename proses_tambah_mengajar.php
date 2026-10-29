<?php
require_once('koneksi.php');

if (isset($_POST['submit'])) {
    $mapel_id = trim($_POST['mapel_id'] ?? '');
    $kelas    = trim($_POST['kelas'] ?? '');

    if (!empty($mapel_id) && !empty($kelas)) {
        // Ambil semua siswa berdasarkan kelas yang dipilih
        $stmt_siswa = mysqli_prepare($koneksi, "SELECT id FROM siswa WHERE kelas = ?");
        mysqli_stmt_bind_param($stmt_siswa, "s", $kelas);
        mysqli_stmt_execute($stmt_siswa);
        $siswa_query = mysqli_stmt_get_result($stmt_siswa);

        if (mysqli_num_rows($siswa_query) > 0) {
            $berhasil = 0;

            // Prepared statement untuk pengecekan dan insert
            $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM mengajar WHERE mapel_id = ? AND siswa_id = ?");
            $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO mengajar (mapel_id, siswa_id) VALUES (?, ?)");

            while ($siswa = mysqli_fetch_assoc($siswa_query)) {
                $siswa_id = $siswa['id'];

                // Cek duplikasi
                mysqli_stmt_bind_param($stmt_cek, "ii", $mapel_id, $siswa_id);
                mysqli_stmt_execute($stmt_cek);
                mysqli_stmt_store_result($stmt_cek);

                if (mysqli_stmt_num_rows($stmt_cek) == 0) {
                    mysqli_stmt_bind_param($stmt_ins, "ii", $mapel_id, $siswa_id);
                    mysqli_stmt_execute($stmt_ins);
                    $berhasil++;
                }
            }

            mysqli_stmt_close($stmt_siswa);
            mysqli_stmt_close($stmt_cek);
            mysqli_stmt_close($stmt_ins);

            echo "<script>alert('Data mengajar berhasil disimpan untuk $berhasil siswa di Kelas " . addslashes($kelas) . "!'); window.location.href='mengajar.php';</script>";
        } else {
            echo "<script>alert('Tidak ada siswa di kelas ini!'); window.location.href='tambah_mengajar.php';</script>";
        }
    } else {
        echo "<script>alert('Harap pilih Mapel dan Kelas!'); window.location.href='tambah_mengajar.php';</script>";
    }
} else {
    header("Location: mengajar.php");
    exit();
}
?>