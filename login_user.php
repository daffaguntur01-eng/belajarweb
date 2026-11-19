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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #f4f7f6;
        }
        .login-container {
            max-width: 1050px;
            width: 100%;
            background: #ffffff;
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .left-banner {
            padding: 0;
            overflow: hidden;
            background-color: #eaf2ff;
        }
        .left-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Membuat gambar memenuhi seluruh kotak secara proporsional tanpa garis hitam */
            object-position: center;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">

    <div class="row login-container shadow-lg border-0 g-0">
        <!-- Kolom Kiri: Menampilkan sipn.png agar pas memenuhi area -->
        <div class="col-lg-6 left-banner d-none d-lg-block">
            <img src="sipn.png" alt="Banner Login">
        </div>

        <!-- Kolom Kanan: Form Login -->
        <div class="col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
            <div class="text-center mb-4">
                <h4 class="fw-bold text-dark">Hai, selamat datang kembali</h4>
                <p class="text-muted small">Belum punya akun? <a href="registasi.php" class="text-decoration-none">Daftar sebagai Siswa</a></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 text-center small" role="alert">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label text-muted small fw-semibold">Username / NIS</label>
                    <input type="text" name="username" id="username" class="form-control py-2" required placeholder="Masukkan username atau NIS">
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label text-muted small fw-semibold">Password</label>
                        <a href="#" class="small text-decoration-none text-primary">Lupa kata sandi?</a>
                    </div>
                    <input type="password" name="password" id="password" class="form-control py-2" required placeholder="Masukkan password">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label text-muted small" for="remember">Ingat perangkat ini</label>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">Masuk</button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted" style="font-size: 0.75rem;">
                    Dengan melanjutkan, kamu menerima <a href="#" class="text-decoration-none">Syarat Penggunaan</a> dan <a href="#" class="text-decoration-none">Kebijakan Privasi</a> kami.
                </small>
            </div>
        </div>
    </div>

</body>
</html>