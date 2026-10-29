<?php
$koneksi = mysqli_connect("localhost", "root", "", "acep");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tabelusers.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    die("ID user tidak valid.");
}

// Ambil data user untuk mengetahui role-nya
$queryUser = mysqli_query($koneksi, "SELECT role FROM users WHERE id = $id");

if (!$queryUser || mysqli_num_rows($queryUser) == 0) {
    die("Data user tidak ditemukan.");
}

$user = mysqli_fetch_assoc($queryUser);

// Mengubah role menjadi huruf kecil dan tanpa spasi agar cocok saat dibandingkan
$role = strtolower(trim($user['role'] ?? ''));

// ==========================================
// PROSES UPDATE DATA
// ==========================================

// UPDATE DATA SISWA
if ($role == 'siswa') {
    $nis           = mysqli_real_escape_string($koneksi, $_POST['nis'] ?? '');
    $nama_siswa    = mysqli_real_escape_string($koneksi, $_POST['nama_siswa'] ?? '');
    $kelas         = mysqli_real_escape_string($koneksi, $_POST['kelas'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

    $updateSiswa = mysqli_query(
        $koneksi,
        "UPDATE siswa SET
            nis = '$nis',
            nama = '$nama_siswa',
            kelas = '$kelas',
            jenis_kelamin = '$jenis_kelamin'
        WHERE user_id = $id"
    );

    if (!$updateSiswa) {
        die("Gagal mengubah data siswa: " . mysqli_error($koneksi));
    }
}

// UPDATE DATA GURU
elseif ($role == 'guru') {
    $nip           = mysqli_real_escape_string($koneksi, $_POST['nip'] ?? '');
    $nama          = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

    $updateGuru = mysqli_query(
        $koneksi,
        "UPDATE guru SET
            nip = '$nip',
            nama = '$nama',
            jenis_kelamin = '$jenis_kelamin'
        WHERE user_id = $id"
    );

    if (!$updateGuru) {
        die("Gagal mengubah data guru: " . mysqli_error($koneksi));
    }
}

// UPDATE DATA ADMIN
elseif ($role == 'admin') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $updateAdmin  = mysqli_query(
            $koneksi,
            "UPDATE users SET
                username = '$username',
                password = '$passwordHash'
            WHERE id = $id"
        );
    } else {
        $updateAdmin = mysqli_query(
            $koneksi,
            "UPDATE users SET
                username = '$username'
            WHERE id = $id"
        );
    }

    if (!$updateAdmin) {
        die("Gagal mengubah data admin: " . mysqli_error($koneksi));
    }
} else {
    die("Role '$role' tidak dikenali.");
}

// Redirect setelah berhasil simpan
header("Location: tabelusers.php");
exit;
?>