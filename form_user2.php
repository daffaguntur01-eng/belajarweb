<?php
// Jalankan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']); 

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

// Ambil data user dari session
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'Admin';
$role_user = strtolower($_SESSION['role'] ?? '');

// ===================================================================
// PENGAMAN DINDING HAK AKSES (KHUSUS ADMIN)
// Jika bukan admin (misal Guru atau Siswa), tolak dan kembali ke halaman sebelumnya
// ===================================================================
if ($role_user !== 'admin') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk Admin.');
            window.history.back();
          </script>";
    exit();
}

// URL Avatar Otomatis mengikuti Nama User
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa</title>
    
    <!-- IMPORT BOOTSTRAP 5 & ICONS (Wajib ada agar Navbar tidak berantakan) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #f9f9f9; }
        .form-container { max-width: 400px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); margin: 40px auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        .btn-submit { 
            background-color: #007bff; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            margin-top: 10px; 
            font-size: 14px;
        }
        .btn-submit:hover { background-color: #0056b3; }

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

<!-- NAVBAR DIPANGGIL DI SINI -->
<?php require_once ('navbar copy.php');?>

<div class="form-container">
    <h2 class="text-center mb-4 fs-4">Form Tambah Data Siswa</h2>
    <form action="proses_simpan.php" method="POST">
        <!-- Input hidden untuk mengirim role otomatis sebagai 'siswa' -->
        <input type="hidden" name="role" value="siswa">

        <div class="form-group">
            <label for="username">Username / Nama Lengkap</label>
            <input type="text" id="username" name="username" required placeholder="Contoh: acep">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Masukkan password akun">
        </div>

        <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" name="nis" required placeholder="Contoh: 12200341">
        </div>

        <div class="form-group">
            <label for="kelas">Kelas</label>
            <input type="text" id="kelas" name="kelas" required placeholder="Contoh: XI-RPL 1">
        </div>

        <div class="form-group">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L">Laki-laki (L)</option>
                <option value="P">Perempuan (P)</option>
            </select>
        </div>

        <button type="submit" name="submit" class="btn-submit">Simpan Data</button>
        <a href="tabelusers.php" class="btn-secondary-custom">Kembali</a>
    </form>
</div>

<!-- Bootstrap JS Bundle (Dibutuhkan untuk dropdown profil navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body> 
</html>