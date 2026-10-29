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
    <title>Form Tambah User (Admin)</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-custom {
            max-width: 500px;
            margin: 50px auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- NAVBAR DIPANGGIL DI SINI -->
<?php require_once ('navbar copy.php');?>

<div class="container">
    <div class="card card-custom p-4">
        <h3 class="mb-4">Form Tambah User (Admin)</h3>
        <h5 class="text-secondary mb-4">Data Kredensial Akun</h5>

        <!-- Action diarahkan ke file proses_simpan.php -->
        <form action="proses_simpan.php" method="POST">
            <div class="mb-3 row align-items-center">
                <label for="username" class="col-sm-4 col-form-label">Username Akun</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: nama123" required>
                </div>
            </div>

            <div class="mb-4 row align-items-center">
                <label for="password" class="col-sm-4 col-form-label">Password Akun</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contoh: password123" required>
                </div>
            </div>

            <!-- Input tersembunyi untuk role -->
            <input type="hidden" name="role" value="admin">

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-primary">Simpan Data</button>
                <a href="tabelusers.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle (Wajib ditambahkan agar dropdown navbar berfungsi) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>