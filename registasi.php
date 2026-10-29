<?php
session_start();
include 'koneksi.php';

$pesan_error = '';
$pesan_sukses = '';

if (isset($_POST['submit'])) {
    $username      = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password      = $_POST['password'];
    $nis           = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $kelas         = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $role          = 'siswa'; // Otomatis role siswa

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan_error = "Username sudah digunakan, silakan pilih username lain.";
    } else {
        // Hash password agar aman
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 1. Simpan ke tabel users terlebih dahulu
        $insert_user = mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hash', '$role')");

        if ($insert_user) {
            // Ambil ID user yang baru saja dimasukkan
            $user_id = mysqli_insert_id($koneksi);

            // 2. Simpan ke tabel siswa
            $insert_siswa = mysqli_query($koneksi, "INSERT INTO siswa (user_id, nis, nama, kelas, jenis_kelamin) VALUES ('$user_id', '$nis', '$username', '$kelas', '$jenis_kelamin')");

            if ($insert_siswa) {
                $pesan_sukses = "Registrasi berhasil! Silakan <a href='login_user.php'>Login di sini</a>.";
            } else {
                $pesan_error = "Gagal menyimpan data siswa: " . mysqli_error($koneksi);
            }
        } else {
            $pesan_error = "Gagal membuat akun user: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Siswa - Sistem Informasi Sekolah</title>
    
    <!-- IMPORT BOOTSTRAP 5 & ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .form-container { max-width: 400px; background: white; padding: 25px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); margin: 40px auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        .btn-submit { 
            background-color: #0d6efd; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            margin-top: 10px; 
            font-size: 14px;
            font-weight: 600;
        }
        .btn-submit:hover { background-color: #0b5ed7; }

        .btn-secondary-custom { 
            display: block;
            text-align: center;
            background-color: #6c757d; 
            color: white; 
            padding: 10px 15px; 
            border-radius: 4px; 
            text-decoration: none; 
            width: 100%; 
            margin-top: 10px; 
            box-sizing: border-box;
            font-size: 14px;
        }
        .btn-secondary-custom:hover { background-color: #5a6268; color: white; }
    </style>
</head>
<body>

<div class="form-container">
    <h3 class="text-center mb-4 text-primary fw-bold">Registrasi Siswa</h3>

    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger py-2" role="alert">
            <?= htmlspecialchars($pesan_error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_sukses)): ?>
        <div class="alert alert-success py-2" role="alert">
            <?= $pesan_sukses; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Username / Nama Lengkap</label>
            <input type="text" id="username" name="username" required placeholder="Contoh: Acep Guntur">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Masukkan password akun">
        </div>

        <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" name="nis" required placeholder="Contoh: 111111111">
        </div>

        <div class="form-group">
            <label for="kelas">Kelas</label>
            <input type="text" id="kelas" name="kelas" required placeholder="Contoh: XI RPL 1">
        </div>

        <div class="form-group">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L">Laki-laki (L)</option>
                <option value="P">Perempuan (P)</option>
            </select>
        </div>

        <button type="submit" name="submit" class="btn-submit">Daftar Akun</button>
        <a href="login_user.php" class="btn-secondary-custom">Sudah punya akun? Login</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body> 
</html>