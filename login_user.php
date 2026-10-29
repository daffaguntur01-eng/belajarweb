<?php
session_start();
include 'koneksi.php';

// Jika pengguna sudah login sebelumnya, arahkan sesuai rolenya masing-masing
if (isset($_SESSION['role'])) {
    $role_redirect = strtolower($_SESSION['role']);
    if ($role_redirect === 'admin') {
        header("Location: tabelusers.php");
    } elseif ($role_redirect === 'guru') {
        header("Location: halaman_guru.php");
    } else {
        header("Location: halaman_siswa.php");
    }
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cari user berdasarkan username di tabel users
    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Cek password
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            // Set data session umum
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $role_user = strtolower($user['role']);
            $_SESSION['role'] = $role_user;

            $user_id = $user['id'];

            // Cek apakah user ini adalah data Siswa
            $query_siswa = "SELECT * FROM siswa WHERE user_id = '$user_id' LIMIT 1";
            $res_siswa = mysqli_query($koneksi, $query_siswa);

            // Cek apakah user ini adalah data Guru
            $query_guru = "SELECT * FROM guru WHERE user_id = '$user_id' LIMIT 1";
            $res_guru = mysqli_query($koneksi, $query_guru);

            if ($res_siswa && mysqli_num_rows($res_siswa) > 0) {
                $siswa = mysqli_fetch_assoc($res_siswa);
                $_SESSION['nama'] = $siswa['nama'];
                $_SESSION['nis'] = $siswa['nis'];
            } elseif ($res_guru && mysqli_num_rows($res_guru) > 0) {
                $guru = mysqli_fetch_assoc($res_guru);
                $_SESSION['nama'] = $guru['nama'];
                $_SESSION['nip'] = $guru['nip'];
            } else {
                $_SESSION['nama'] = $user['username'];
            }

            // Pengarahan dinamis berdasarkan role
            if ($role_user === 'admin') {
                header("Location: tabelusers.php");
            } elseif ($role_user === 'guru') {
                header("Location: halaman_guru.php");
            } else {
                header("Location: halaman_siswa.php");
            }
            exit;

        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow border-0 p-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center fw-bold mb-4 text-primary">Login Sistem</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2" role="alert">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label font-weight-bold">Username / NIS</label>
                <input type="text" name="username" id="username" class="form-control" required placeholder="Masukkan username">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Masukkan password">
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 py-2 mt-2 fw-semibold">Login</button>
            
            <!-- Tambahan Tautan ke Halaman Registrasi Siswa -->
            <div class="text-center mt-3">
                <small class="text-muted">Belum punya akun? <a href="registasi.php" class="text-decoration-none">Daftar sebagai Siswa</a></small>
            </div>
        </form>
    </div>

</body>
</html>