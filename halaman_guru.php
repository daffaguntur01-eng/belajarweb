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

// 3. Data user untuk navbar/header
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'User';
$role_user = strtolower($_SESSION['role'] ?? 'guru');

if ($role_user !== 'guru') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk guru.');
            window.history.back();
          </script>";
    exit();
}

$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);
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

        /* Card / Table Wrapper Styling */
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
            <!-- Header Atas -->
            <div class="content-header">
                <div>
                    <h3 class="fw-bold mb-1">Daftar Guru</h3>
                    <p class="text-muted small mb-0">Kelola dan lihat informasi data guru pengajar</p>
                </div>
                
                <!-- Profil Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="btnProfilToggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $avatar_url; ?>" class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                        <div>
                            <span class="d-block fw-semibold small"><?= htmlspecialchars($nama_user); ?></span>
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: capitalize;"><?= htmlspecialchars($role_user); ?></span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="btnProfilToggle">
                        <li><a class="dropdown-item py-2" href="profil.php"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><a class="dropdown-item py-2" href="ubah_password.php"><i class="bi bi-key me-2"></i> Ubah Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- PHP Logic untuk Paginasi & Hitung Total Data -->
            <?php
            $limit = 5;
            $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $start = ($page > 1) ? ($page * $limit) - $limit : 0;

            $pencarian = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
            
            $whereClause = "";
            if ($pencarian != '') {
                $whereClause = "WHERE guru.nama LIKE '%$pencarian%' OR guru.nip LIKE '%$pencarian%' OR users.username LIKE '%$pencarian%'";
            }

            $query_total = "SELECT COUNT(*) AS total FROM guru LEFT JOIN users ON guru.user_id = users.id $whereClause";
            $result_total = mysqli_query($koneksi, $query_total);
            $row_total = mysqli_fetch_assoc($result_total);
            $total_data = $row_total['total'];
            $total_pages = ceil($total_data / $limit);
            if ($total_pages < 1) $total_pages = 1;

            $query = "SELECT guru.*, users.username, users.role 
                      FROM guru 
                      LEFT JOIN users ON guru.user_id = users.id 
                      $whereClause 
                      LIMIT $start, $limit";

            $result = mysqli_query($koneksi, $query);
            $no = $start + 1;
            ?>

            <!-- Bagian Kontrol Tabel (Pencarian Halaman & Pencarian Data) -->
            <div class="card-custom mb-4">
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <span class="me-2 text-muted small">Ke Halaman</span>
                        <select id="pageJumpSelect" class="form-select form-select-sm w-auto me-2" onchange="pindahHalaman(this.value)">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <option value="<?= $i; ?>" <?= ($page == $i) ? 'selected' : ''; ?>>
                                    <?= $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <span class="text-muted small">dari <?= $total_pages; ?> halaman</span>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                        <form method="GET" action="" class="d-flex align-items-center w-100 justify-content-end">
                            <label class="me-2 text-muted small">Cari:</label>
                            <input type="text" name="cari" class="form-control form-control-sm w-auto" placeholder="Nama / NIP / Username..." value="<?= htmlspecialchars($_GET['cari'] ?? ''); ?>">
                        </form>
                    </div>
                </div>

                <!-- Tabel Data Guru -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
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
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $mapel = isset($row['mapel']) && !empty($row['mapel']) ? $row['mapel'] : '-'; 
                                    $username = $row['username'] ?? '-';
                                    $role_akun = $row['role'] ?? 'guru';
                            ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($username); ?></td>
                                        <td><?= htmlspecialchars($role_akun); ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['nip']); ?></td>
                                        <td><?= htmlspecialchars($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($row['jenis_kelamin'] === 'P' ? 'Perempuan' : $row['jenis_kelamin'])); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($mapel); ?></span></td>
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

                <!-- Bagian Paginasi Bawah -->
                <div class="row align-items-center mt-4">
                    <div class="col-md-6 text-muted small">
                        Menampilkan halaman <strong><?= $page; ?></strong> dari <strong><?= $total_pages; ?></strong> (Total <strong><?= $total_data; ?></strong> data)
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1; ?>&cari=<?= urlencode($pencarian); ?>">Sebelumnya</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?= $i; ?>&cari=<?= urlencode($pencarian); ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1; ?>&cari=<?= urlencode($pencarian); ?>">Selanjutnya</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled"><a class="page-link" href="#">Selanjutnya</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle & Skrip Navigasi Halaman -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function pindahHalaman(pageVal) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', pageVal);
            window.location.search = urlParams.toString();
        }
    </script>
</body>
</html>