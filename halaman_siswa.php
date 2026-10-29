<?php
session_start();
include 'koneksi.php';

// ===================================================================
// PENGAMAN DINDING HAK AKSES (KHUSUS SISWA)
// ===================================================================
// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['nis']) && !isset($_SESSION['nama'])) {
    header("Location: login_user.php");
    exit;
}

// Ambil data user dari session
$user_id   = $_SESSION['user_id'] ?? null;
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'Siswa';
$role_user = strtolower($_SESSION['role'] ?? 'siswa');

// 2. KEMBALIKAN KE HALAMAN SEBELUMNYA JIKA BUKAN SISWA
if ($role_user !== 'siswa') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk siswa.');
            window.history.back();
          </script>";
    exit;
}

// URL Avatar Otomatis mengikuti Nama User
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";

// ===================================================================
// FILTER KELAS KHUSUS SISWA
// Ambil data kelas siswa yang sedang login berdasarkan user_id
// ===================================================================
$kelas_siswa = '';
if ($user_id) {
    $stmt_kelas = mysqli_prepare($koneksi, "SELECT kelas FROM siswa WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt_kelas, "i", $user_id);
    mysqli_stmt_execute($stmt_kelas);
    $res_kelas = mysqli_stmt_get_result($stmt_kelas);
    if ($row_k = mysqli_fetch_assoc($res_kelas)) {
        $kelas_siswa = $row_k['kelas'];
    }
}

// Ambil kata kunci pencarian jika ada
$keyword = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['cari']) ? trim($_GET['cari']) : '');

// ===================================================================
// QUERY DATA SISWA (Filter Kelas + Pencarian Kata Kunci)
// ===================================================================
if (!empty($kelas_siswa)) {
    if (!empty($keyword)) {
        $query = "SELECT 
                    siswa.id,
                    siswa.nama,
                    siswa.kelas,
                    users.username,
                    IFNULL(users.role, 'siswa') AS role
                  FROM siswa
                  LEFT JOIN users ON siswa.user_id = users.id
                  WHERE siswa.kelas = ? AND (siswa.nama LIKE ? OR users.username LIKE ?)";
        
        $stmt = mysqli_prepare($koneksi, $query);
        $search_param = "%" . $keyword . "%";
        mysqli_stmt_bind_param($stmt, "sss", $kelas_siswa, $search_param, $search_param);
    } else {
        $query = "SELECT 
                    siswa.id,
                    siswa.nama,
                    siswa.kelas,
                    users.username,
                    IFNULL(users.role, 'siswa') AS role
                  FROM siswa
                  LEFT JOIN users ON siswa.user_id = users.id
                  WHERE siswa.kelas = ?";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $kelas_siswa);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa Kelas <?= htmlspecialchars($kelas_siswa ?: '-'); ?></title>
    <!-- Import Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #ffffff;
        }
        .table th {
            font-weight: 600;
        }
        #profilMenuDropdown.show {
            display: block;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Header / Navbar -->
    <header class="bg-white border-bottom border-light-subtle sticky-top">
        <div class="container-xl d-flex align-items-center justify-content-between" style="height: 64px;">
            
            <!-- Bagian Kiri: Logo & Menu Navigasi -->
            <div class="d-flex align-items-center gap-4 w-100">
                
                <!-- Logo -->
                <a class="text-primary fw-bold fs-5 text-decoration-none" href="halaman_siswa.php">
                    Navbar
                </a>

                <!-- Navigasi Desktop (Khusus Siswa) -->
                <nav aria-label="Global" class="d-none d-md-block w-100">
                    <ul class="d-flex align-items-center gap-4 mb-0 list-unstyled">
                        <li>
                            <a class="text-dark text-decoration-none fw-medium" href="halaman_siswa.php">Home</a>
                        </li>
                        <li>
                            <a class="text-dark text-decoration-none fw-medium" href="nilai_siswa.php">Nilai Siswa</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Bagian Kanan: Search & Profil -->
            <div class="d-flex align-items-center gap-3 ms-4">
                <!-- Form Pencarian Navbar -->
                <form class="d-none d-lg-flex align-items-center gap-2" action="" method="GET">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search" value="<?= htmlspecialchars($keyword); ?>" style="width: 180px;">
                    <button type="submit" class="btn btn-outline-success btn-sm text-nowrap">Search</button>
                </form>

                <!-- Dropdown Profil -->
                <div class="dropdown position-relative">
                    <button class="btn btn-light d-flex align-items-center gap-2 border-0 px-3 py-2 rounded-pill shadow-sm" type="button" id="btnProfilToggle" style="background-color: #f3f4f6; cursor: pointer;">
                        <img src="<?= $avatar_url; ?>" alt="User Avatar" class="rounded-circle" width="32" height="32" style="pointer-events: none;">
                        <span class="d-none d-sm-inline fw-medium text-dark text-nowrap" style="font-size: 0.875rem; pointer-events: none;">
                            <?= htmlspecialchars($nama_user); ?> (<?= htmlspecialchars($role_user); ?>)
                        </span>
                        <i class="bi bi-chevron-down text-secondary" style="font-size: 0.75rem; pointer-events: none;"></i>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" id="profilMenuDropdown" style="position: absolute; right: 0; left: auto;">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-dark" style="font-size: 0.875rem;"><?= htmlspecialchars($nama_user); ?></div>
                            <small class="text-muted text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($role_user); ?></small>
                        </li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2 py-2 mt-1" href="PROFIL.php"><i class="bi bi-person text-secondary fs-5"></i><span>Profil</span></a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="pengaturan.php"><i class="bi bi-gear text-secondary fs-5"></i><span>Pengaturan</span></a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right fs-5"></i><span>Log Out</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama: Daftar Siswa -->
    <div class="container my-5">
        <h1 class="text-center mb-4 fw-normal">
            Daftar Siswa Kelas <?= htmlspecialchars($kelas_siswa ?: '-'); ?>
        </h1>

        <!-- Controls: Tampilkan & Cari -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6 d-flex align-items-center mb-2 mb-md-0">
                <span class="me-2">Tampilkan</span>
                <select class="form-select form-select-sm w-auto me-2">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>data per halaman</span>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                <form action="" method="GET" class="d-flex align-items-center">
                    <label class="me-2">Cari:</label>
                    <input type="text" name="cari" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($keyword); ?>">
                </form>
            </div>
        </div>

        <!-- Tabel Data Siswa dari Database -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle bg-white shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 5%;">No</th>
                        <th scope="col">Username</th>
                        <th scope="col">Role</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result && mysqli_num_rows($result) > 0) :
                        while ($row = mysqli_fetch_assoc($result)) : 
                    ?>
                        <tr>
                            <th scope="row"><?= $no++; ?></th>
                            <td><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['role']); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['kelas']); ?></td>
                        </tr>
                    <?php 
                        endwhile; 
                    else : 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Data siswa tidak ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="row align-items-center mt-3">
            <div class="col-md-6 text-muted mb-2 mb-md-0">
                Menampilkan halaman 1 dari 1
            </div>
            <div class="col-md-6 d-flex justify-content-md-end">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                        <li class="page-item active" aria-current="page"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">Selanjutnya</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Skrip Kontrol Klik Profil -->
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