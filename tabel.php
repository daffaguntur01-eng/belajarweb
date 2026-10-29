<?php 
// 1. Jalankan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Memanggil file koneksi database
require_once ('koneksi.php');

// 3. Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']); 
if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

// 4. Persiapkan data user untuk navbar
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Admin';
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

$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";

// Query SQL untuk mengambil data khusus pengguna yang memiliki role 'siswa'
$sql = "SELECT 
            users.id AS user_id, 
            users.username, 
            users.role, 
            siswa.nis, 
            siswa.nama, 
            siswa.jenis_kelamin 
        FROM users 
        LEFT JOIN siswa ON users.id = siswa.user_id 
        WHERE users.role = 'siswa' 
        ORDER BY users.id DESC";

// Menjalankan perintah query ke database
$query = mysqli_query($koneksi, $sql);

// Inisialisasi array kosong untuk menyimpan baris data hasil eksekusi query
$data = []; 
if ($query && mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Sistem Informasi Sekolah</title>
    <!-- Import Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table th {
            font-weight: 600;
        }
        #profilMenuDropdown.show {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Memanggil Navigasi Utama (Navbar) -->
    <?php require_once ('navbar.php'); ?>

    <!-- Kontainer Utama Halaman -->
    <div class="container my-5">

        <!-- Header / Judul Halaman -->
        <div class="text-center mb-4">
            <h1 class="fw-normal display-5">Data Siswa</h1>
            <hr class="w-25 mx-auto border border-dark border-2 opacity-75">
        </div>

        <!-- Tombol Tambah Data Siswa -->
        <div class="mb-3 d-flex justify-content-end">
            <a href="form_user_siswa.php" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg"></i> Tambah Siswa
            </a>
        </div>

        <!-- Tabel Bootstrap untuk Menampilkan Data Siswa -->
        <div class="table-responsive bg-white shadow-sm rounded p-3">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                      <th scope="col" style="width: 60px;">No</th>
                      <th scope="col">NISN</th> 
                      <th scope="col">Nama Siswa</th>
                      <th scope="col">Jenis Kelamin</th>
                      <th scope="col" style="width: 18%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($data)) {
                        $no = 1; 
                        foreach ($data as $siswa) {
                            $namaTampil = !empty($siswa['nama']) ? $siswa['nama'] : $siswa['username'];
                    ?>
                        <tr>
                          <!-- Menampilkan Nomor Urut -->
                          <th scope="row"><?php echo $no++; ?></th>
                          
                          <!-- Menampilkan NISN -->
                          <td><?php echo htmlspecialchars($siswa['nis'] ?? '-'); ?></td>
                          
                          <!-- Menampilkan Nama Siswa atau Username -->
                          <td><?php echo htmlspecialchars($namaTampil); ?></td>

                          <!-- Menampilkan Jenis Kelamin -->
                          <td><?php echo htmlspecialchars($siswa['jenis_kelamin'] ?? '-'); ?></td>
                          
                          <!-- Kolom Tombol Aksi (Edit & Delete) -->
                          <td>
                            <a href="edit_user.php?id=<?= $siswa['user_id']; ?>&role=<?= $siswa['role']; ?>" class="btn btn-warning btn-sm text-dark px-2">Edit</a>
                            <a href="delete_user.php?id=<?= $siswa['user_id']; ?>" class="btn btn-danger btn-sm px-2" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</a>
                          </td>
                        </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Tidak ada data siswa.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Pengendali Toggle Profil Dropdown -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btnToggle = document.getElementById("btnProfilToggle");
            const menuDropdown = document.getElementById("profilMenuDropdown");

            if (btnToggle && menuDropdown) {
                btnToggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    menuDropdown.classList.toggle("show");
                });

                document.addEventListener("click", function (e) {
                    if (!btnToggle.contains(e.target) && !menuDropdown.contains(e.target)) {
                        menuDropdown.classList.remove("show");
                    }
                });
            }
        });
    </script>
</body>
</html>