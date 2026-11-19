<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// ===================================================================
// PENGAMAN DINDING HAK AKSES (KHUSUS SISWA)
// ===================================================================
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']) || isset($_SESSION['username']); 

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

$user_id   = $_SESSION['user_id'] ?? null;
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'Siswa';
$role_user = strtolower($_SESSION['role'] ?? 'siswa');

if ($role_user !== 'siswa') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk siswa.');
            window.history.back();
          </script>";
    exit();
}

// URL Avatar Kartun Otomatis (DiceBear)
$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);

// ===================================================================
// FILTER KELAS KHUSUS SISWA
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

// Ambil kata kunci pencarian
$keyword = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['cari']) ? trim($_GET['cari']) : '');

// ===================================================================
// QUERY DATA SISWA (Filter Kelas + Pencarian)
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
    <title>Daftar Siswa Kelas <?= htmlspecialchars($kelas_siswa ?: '-'); ?> - NIPN</title>
    <!-- Import Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --bg-body: #f4f6f9;
        }
        body {
            background-color: var(--bg-body);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* Layout Dashboard Wrapper */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Modern */
        .sidebar {
            width: 260px;
            background-color: var(--primary-blue);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar-brand {
            font-size: 1.4rem;
            font-weight: bold;
            padding: 0 25px 25px 25px;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border-top-left-radius: 30px;
            border-bottom-left-radius: 30px;
            margin-left: 10px;
        }

        .sidebar-menu li a:hover, 
        .sidebar-menu li.active a {
            color: var(--primary-blue);
            background-color: var(--bg-body);
        }

        .sidebar-menu li a i {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .sidebar-footer {
            padding: 0 25px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }
        .sidebar-footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            margin-right: 10px;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 30px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        /* Card Custom Styling */
        .card-custom {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 24px;
        }

        /* Custom Table Styling */
        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th {
            border: none;
            color: #8c98a4;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: transparent !important;
        }

        .table tbody tr {
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.01);
            transition: transform 0.2s;
        }

        .table tbody tr td {
            border-top: 1px solid #f0f2f5;
            border-bottom: 1px solid #f0f2f5;
            padding: 15px 12px;
            vertical-align: middle;
        }

        .table tbody tr td:first-child {
            border-left: 1px solid #f0f2f5;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .table tbody tr td:last-child {
            border-right: 1px solid #f0f2f5;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .sidebar-brand, .sidebar .sidebar-menu span, .sidebar .sidebar-footer {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Memanggil Sidebar Terpusat dari navbar.php -->
        <?php include 'navbar.php'; ?>

        <!-- Konten Utama -->
        <div class="main-content">
            <!-- Header Atas & Dropdown Profil -->
            <div class="content-header">
                <div>
                    <h3 class="fw-bold mb-1">Daftar Siswa Kelas <?= htmlspecialchars($kelas_siswa ?: '-'); ?></h3>
                    <p class="text-muted small mb-0">Informasi data rekan sekelas Anda</p>
                </div>
                
                <!-- Dropdown Profil Kanan Atas -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="btnProfilToggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $avatar_url; ?>" class="rounded-circle me-2 shadow-sm bg-light" width="40" height="40" alt="Avatar">
                        <div>
                            <span class="d-block fw-semibold small"><?= htmlspecialchars($nama_user); ?></span>
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: capitalize;"><?= htmlspecialchars($role_user); ?></span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="btnProfilToggle">
                        <li><a class="dropdown-item py-2" href="PROFIL.php"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><a class="dropdown-item py-2" href="pengaturan.php"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- Kartu Konten & Tabel -->
            <div class="card-custom mb-4">
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <span class="me-2 text-muted small">Tampilkan</span>
                        <select class="form-select form-select-sm w-auto me-2">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-muted small">data per halaman</span>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                        <form method="GET" action="" class="d-flex align-items-center w-100 justify-content-end">
                            <label class="me-2 text-muted small">Cari:</label>
                            <input type="text" name="cari" class="form-control form-control-sm w-auto" placeholder="Nama / Username..." value="<?= htmlspecialchars($keyword); ?>">
                        </form>
                    </div>
                </div>

                <!-- Tabel Data Siswa -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0) :
                                while ($row = mysqli_fetch_assoc($result)) : 
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                                    <td><span class="badge bg-light text-dark border text-capitalize"><?= htmlspecialchars($row['role']); ?></span></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
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

                <!-- Bagian Paginasi Bawah -->
                <div class="row align-items-center mt-4">
                    <div class="col-md-6 text-muted small">
                        Menampilkan halaman <strong>1</strong> dari <strong>1</strong>
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
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>