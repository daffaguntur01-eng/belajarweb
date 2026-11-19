<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Ambil data user dari session
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']) || isset($_SESSION['username']);

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit;
}

$nama_user  = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'Siswa';
$role_user  = strtolower($_SESSION['role'] ?? 'siswa');

// ===================================================================
// PENGAMAN DINDING HAK AKSES (KHUSUS SISWA)
// Jika bukan siswa, tolak dan kembali ke halaman sebelumnya
// ===================================================================
if ($role_user !== 'siswa') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk siswa.');
            window.history.back();
          </script>";
    exit;
}

// URL Avatar Kartun Otomatis (DiceBear)
$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);

// Ambil data siswa yang sedang login
$siswa_id    = 0;
$nama_siswa  = $nama_user;
$nis_siswa   = $_SESSION['nis'] ?? '-';
$kelas_siswa = '-';

$user_id_sess  = $_SESSION['user_id'] ?? 0;
$nis_sess      = $_SESSION['nis'] ?? '';
$username_sess = $_SESSION['username'] ?? '';

// Query mencari data siswa lengkap
$query_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nis = '$nis_sess' OR user_id = '$user_id_sess' OR nama LIKE '%$nama_user%' LIMIT 1");
if ($query_s && mysqli_num_rows($query_s) > 0) {
    $data_s      = mysqli_fetch_assoc($query_s);
    $siswa_id    = $data_s['id'];
    $nama_siswa  = $data_s['nama'];
    $nis_siswa   = $data_s['nis'];
    $kelas_siswa = $data_s['kelas'];
}

// Ambil data nilai rapor siswa tersebut dari database
$query_nilai = mysqli_query($koneksi, "SELECT nilai_rapor.*, mapel.nama_mapel FROM nilai_rapor LEFT JOIN mapel ON nilai_rapor.mapel_id = mapel.id WHERE nilai_rapor.siswa_id = '$siswa_id'");
$nilai_list    = [];
$total_rata    = 0;
$jumlah_mapel  = 0;

if ($query_nilai) {
    while ($row = mysqli_fetch_assoc($query_nilai)) {
        $p = $row['nilai_pengetahuan'] ?? 0;
        $k = $row['nilai_keterampilan'] ?? 0;
        $rata_mapel = ($p + $k) / 2;
        
        $row['rata_mapel'] = $rata_mapel;
        $total_rata += $rata_mapel;
        $jumlah_mapel++;
        
        $nilai_list[] = $row;
    }
}

$rata_keseluruhan = ($jumlah_mapel > 0) ? ($total_rata / $jumlah_mapel) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Siswa - NIPN</title>
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

        .table th {
            font-weight: 600;
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
        
        <!-- MEMANGGIL NAVBAR/SIDEBAR TERPUSAT -->
        <?php include 'navbar.php'; ?>

        <!-- Konten Utama -->
        <div class="main-content">
            <!-- Header Atas & Dropdown Profil -->
            <div class="content-header">
                <div>
                    <h3 class="fw-bold mb-1">Nilai Siswa</h3>
                    <p class="text-muted small mb-0">Rekapitulasi hasil nilai belajar dan rapor akademik</p>
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

            <!-- Header Profil Siswa Card -->
            <div class="card-custom mb-4 text-center py-4">
                <h4 class="fw-bold mt-2 text-dark"><?= htmlspecialchars($nama_siswa); ?></h4>
                <p class="text-muted mb-1 small">NIS: <strong><?= htmlspecialchars($nis_siswa); ?></strong></p>
                <p class="text-muted small">Kelas: <strong><?= htmlspecialchars($kelas_siswa); ?></strong></p>
            </div>

            <!-- Kartu Rata-rata Nilai -->
            <div class="card card-custom shadow-sm border-0 mb-4 text-center py-4" style="max-width: 400px; margin: 0 auto;">
                <div class="card-body">
                    <span class="text-muted fs-6">Rata-rata Nilai</span>
                    <h1 class="display-4 fw-normal mt-2 text-primary"><?= number_format($rata_keseluruhan, 2); ?></h1>
                </div>
            </div>

            <!-- Tabel Daftar Nilai -->
            <div class="card-custom mb-4">
                <h5 class="mb-3 fw-bold text-dark">Daftar Nilai</h5>
                
                <div class="row mb-3 align-items-center">
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
                        <form method="GET" action="" class="d-flex align-items-center">
                            <label class="me-2 text-muted small">Cari:</label>
                            <input type="text" name="cari" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($_GET['cari'] ?? ''); ?>">
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Mata Pelajaran</th>
                                <th>KKM</th>
                                <th>Pengetahuan</th>
                                <th>Keterampilan</th>
                                <th>Rata-rata</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($nilai_list) > 0): ?>
                                <?php $no = 1; foreach ($nilai_list as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_mapel'] ?? 'Mata Pelajaran'); ?></td>
                                        <td><?= htmlspecialchars($row['nilai_kkm'] ?? '75'); ?></td>
                                        <td><?= htmlspecialchars($row['nilai_pengetahuan'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($row['nilai_keterampilan'] ?? '-'); ?></td>
                                        <td class="fw-bold text-primary"><?= number_format($row['rata_mapel'], 2); ?></td>
                                        <td>
                                            <?php 
                                                $ket = $row['Keterangan'] ?? '-';
                                                $badge_bg = (strtolower($ket) === 'tuntas' || strtolower($ket) === 'lulus') ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $badge_bg; ?> px-2 py-1"><?= htmlspecialchars($ket); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data nilai rapor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Bagian Paginasi -->
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