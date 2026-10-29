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

// Query untuk mengambil data mapel beserta nama guru pengajarnya
$sql_mapel = "SELECT 
                mapel.id AS mapel_id, 
                mapel.kode_mapel, 
                mapel.nama_mapel, 
                guru.nama AS nama_guru 
              FROM mapel 
              LEFT JOIN guru ON mapel.guru_id = guru.id 
              ORDER BY mapel.nama_mapel ASC";

$query_mapel = mysqli_query($koneksi, $sql_mapel);

// Query untuk mengambil daftar kelas yang unik dari tabel siswa
$sql_kelas = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC";
$query_kelas = mysqli_query($koneksi, $sql_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Mengajar - Sistem Informasi Sekolah</title>
    <!-- Import Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        #profilMenuDropdown.show {
            display: block;
        }
    </style>
</head>
<body>

    <!-- 1. Memanggil File Navbar Eksternal -->
    <?php require_once ('navbar.php'); ?>

    <!-- Kontainer Utama -->
    <div class="container my-5">
        <div class="text-center mb-4">
            <h1 class="fw-normal display-5">Tambah Data Mengajar</h1>
            <hr class="w-25 mx-auto border border-dark border-2 opacity-75">
        </div>

        <div class="card shadow-sm border-0 p-4 rounded-3 bg-white mx-auto" style="max-width: 600px;">
            <form action="proses_tambah_mengajar.php" method="POST">
                
                <!-- Pilih Mata Pelajaran -->
                <div class="mb-3">
                    <label for="mapel_id" class="form-label fw-medium">Mata Pelajaran</label>
                    <select name="mapel_id" id="mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php 
                        if ($query_mapel && mysqli_num_rows($query_mapel) > 0) {
                            while ($row = mysqli_fetch_assoc($query_mapel)) {
                                $guru = !empty($row['nama_guru']) ? $row['nama_guru'] : 'Belum Ada Guru';
                                echo "<option value='" . $row['mapel_id'] . "'>" 
                                     . htmlspecialchars($row['nama_mapel']) . " (" . htmlspecialchars($row['kode_mapel']) . ") - Guru: " . htmlspecialchars($guru) 
                                     . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Pilih Kelas -->
                <div class="mb-4">
                    <label for="kelas" class="form-label fw-medium">Kelas Siswa</label>
                    <select name="kelas" id="kelas" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php 
                        if ($query_kelas && mysqli_num_rows($query_kelas) > 0) {
                            while ($row_kelas = mysqli_fetch_assoc($query_kelas)) {
                                echo "<option value='" . htmlspecialchars($row_kelas['kelas']) . "'>" 
                                     . htmlspecialchars($row_kelas['kelas']) 
                                     . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-between gap-2">
                    <button type="button" onclick="history.back()" class="btn btn-secondary px-4">Batal</button>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Data</button>
                </div>

            </form>
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