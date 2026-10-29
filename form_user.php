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
    <title>Tambah Data User</title>
    <!-- IMPORT BOOTSTRAP 5 & ICONS (Wajib agar navbar berfungsi) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        .form-container { max-width: 400px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); margin: 40px auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        /* Style Tombol Simpan */
        button { 
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
        button:hover { background-color: #0056b3; }
        
        /* Style Tombol Kembali */
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

        /* CSS untuk area form dinamis */
        .role-only { display: none; background: #f0f7ff; padding: 10px; border-left: 4px solid #007bff; border-radius: 4px; margin-top: 10px; }
    </style>
    <script>
        function tampilkanFormTambahan() {
            var role = document.getElementById("role").value;
            
            var formSiswa = document.getElementById("form_tambahan_siswa");
            var formGuru = document.getElementById("form_tambahan_guru");
            var formJk = document.getElementById("form_jk");
            
            var inputNis = document.getElementById("nis");
            var inputKelas = document.getElementById("kelas");
            var inputNip = document.getElementById("nip");
            var inputJk = document.getElementById("jenis_kelamin");

            if (role === "siswa") {
                formSiswa.style.display = "block";
                formGuru.style.display = "none";
                formJk.style.display = "block";
                
                inputNis.required = true;
                inputKelas.required = true;
                inputJk.required = true;
                inputNip.required = false;

            } else if (role === "guru") {
                formGuru.style.display = "block";
                formSiswa.style.display = "none";
                formJk.style.display = "block";
                
                inputNip.required = true;
                inputJk.required = true;
                inputNis.required = false;
                inputKelas.required = false;

            } else {
                formSiswa.style.display = "none";
                formGuru.style.display = "none";
                formJk.style.display = "none";
                
                inputNis.required = false;
                inputKelas.required = false;
                inputNip.required = false;
                inputJk.required = false;
            }
        }
    </script>
</head>
<body>

<!-- NAVBAR DIPANGGIL DI SINI SEBELUM FORM -->
<?php require_once ('navbar copy.php');?>

<div class="form-container">
    <h2>Form Isi Data Users</h2>
    <!-- Action disesuaikan ke file pemproses proses_simpan.php -->
    <form action="proses_simpan.php" method="POST">
        <div class="form-group">
            <label for="username">Username / Nama Lengkap</label>
            <input type="text" id="username" name="username" required placeholder="Contoh: malikal">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Masukkan password akun">
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" onchange="tampilkanFormTambahan()" required>
                <option value="">-- Pilih Role --</option>
                <option value="admin">Admin</option>
                <option value="guru">Guru</option>
                <option value="siswa">Siswa</option>
            </select>
        </div>

        <!-- FORM TAMBAHAN KHUSUS SISWA -->
        <div id="form_tambahan_siswa" class="role-only">
            <p style="margin-top:0; font-weight:bold; color:#007bff;">Data Tambahan Siswa:</p>
            <div class="form-group">
                <label for="nis">NIS</label>
                <input type="text" id="nis" name="nis" placeholder="Contoh: 12200341">
            </div>
            <div class="form-group">
                <label for="kelas">Kelas</label>
                <input type="text" id="kelas" name="kelas" placeholder="Contoh: XI-RPL 1">
            </div>
        </div>

        <!-- FORM TAMBAHAN KHUSUS GURU -->
        <div id="form_tambahan_guru" class="role-only">
            <p style="margin-top:0; font-weight:bold; color:#007bff;">Data Tambahan Guru:</p>
            <div class="form-group">
                <label for="nip">NIP</label>
                <input type="text" id="nip" name="nip" placeholder="Contoh: 198503302010011002">
            </div>
        </div>

        <!-- JENIS KELAMIN (DITAMPILKAN HANYA UNTUK SISWA / GURU) -->
        <div id="form_jk" class="form-group" style="display: none;">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin">
                <option value="">-- Pilih JK --</option>
                <option value="L">Laki-laki (L)</option>
                <option value="P">Perempuan (P)</option>
            </select>
        </div>

        <button type="submit" name="submit">Simpan Data</button>
        <a href="tabel%20copy.php" class="btn-secondary-custom">Kembali</a>
    </form>
</div>

<!-- Bootstrap JS Bundle (Wajib agar dropdown navbar berfungsi) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>