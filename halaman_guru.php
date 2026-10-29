<?php
// 1. Jalankan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// 2. Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']); 

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

// 3. Data user untuk navbar
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'User';
$role_user = strtolower($_SESSION['role'] ?? 'guru');

// ===================================================================
// PENGAMAN DINDING HAK AKSES (KHUSUS GURU)
// Jika bukan guru (misal Admin atau Siswa), tolak dan kembali ke halaman sebelumnya
// ===================================================================
if ($role_user !== 'guru') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk guru.');
            window.history.back();
          </script>";
    exit();
}

$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guru - Sistem Informasi Sekolah</title>
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

    <!-- Panggil Navbar Eksternal -->
    <?php include 'navbar.php'; ?>

    <!-- Konten Utama: Tabel Daftar Guru -->
    <div class="container my-5">
        <h2 class="text-center mb-5 fw-normal">Daftar Guru</h2>

        <!-- Bagian Kontrol Tabel (Filter & Pencarian) -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6 d-flex align-items-center">
                <span class="me-2">Tampilkan</span>
                <select class="form-select form-select-sm w-auto me-2">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>data per halaman</span>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                <form method="GET" action="" class="d-flex align-items-center">
                    <label class="me-2">Cari:</label>
                    <input type="text" name="cari" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($_GET['cari'] ?? ''); ?>">
                </form>
            </div>
        </div>

        <!-- Tabel Data Guru (Terhubung ke Database) -->
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Jenis Kelamin</th>
                        <th>Mapel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pencarian = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
                    
                    if ($pencarian != '') {
                        $query = "SELECT guru.*, users.username, users.role 
                                  FROM guru 
                                  LEFT JOIN users ON guru.user_id = users.id 
                                  WHERE guru.nama LIKE '%$pencarian%' OR guru.nip LIKE '%$pencarian%' OR users.username LIKE '%$pencarian%'";
                    } else {
                        $query = "SELECT guru.*, users.username, users.role 
                                  FROM guru 
                                  LEFT JOIN users ON guru.user_id = users.id";
                    }

                    $result = mysqli_query($koneksi, $query);
                    $no = 1;

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $mapel = isset($row['mapel']) ? $row['mapel'] : '-'; 
                            $username = $row['username'] ?? '-';
                            $role_akun = $row['role'] ?? 'guru';
                    ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($username); ?></td>
                                <td><?= htmlspecialchars($role_akun); ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['nip']); ?></td>
                                <td><?= htmlspecialchars($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($row['jenis_kelamin'] === 'P' ? 'Perempuan' : $row['jenis_kelamin'])); ?></td>
                                <td><?= htmlspecialchars($mapel); ?></td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data guru yang ditemukan.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Bagian Paginasi -->
        <div class="row align-items-center mt-3">
            <div class="col-md-6 text-muted small">
                Menampilkan data dari database
            </div>
            <div class="col-md-6 d-flex justify-content-md-end">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">Selanjutnya</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Skrip Dropdown Profil -->
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