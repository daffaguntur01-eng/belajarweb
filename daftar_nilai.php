<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']) || isset($_SESSION['username']); 

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

// Ambil data user dari session
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Mutiara Zahra, S.Pd., Gr.';
$role_user = strtolower($_SESSION['role'] ?? 'guru');

if ($role_user !== 'guru') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk guru.');
            window.history.back();
          </script>";
    exit();
}

// URL Avatar Otomatis mengikuti Nama User
$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);

// Ambil parameter dari URL (Kelas dan Mapel ID)
$kelas = $_GET['kelas'] ?? 'XII RPL 1';
$mapel_id = $_GET['mapel_id'] ?? 1;
$cari = $_GET['cari'] ?? '';

try {
    // Ambil nama mata pelajaran berdasarkan mapel_id dari tabel mapel
    $stmtMapel = mysqli_prepare($koneksi, "SELECT nama_mapel FROM mapel WHERE id = ?");
    mysqli_stmt_bind_param($stmtMapel, "i", $mapel_id);
    mysqli_stmt_execute($stmtMapel);
    $resMapel = mysqli_stmt_get_result($stmtMapel);
    $mapel = mysqli_fetch_assoc($resMapel);
    $nama_mapel = $mapel['nama_mapel'] ?? 'Pemrograman Web dan Perangkat Bergerak';
} catch (Exception $e) {
    $nama_mapel = 'Pemrograman Web dan Perangkat Bergerak';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nilai Siswa - Sistem Informasi Sekolah</title>
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
        <!-- Sidebar Kiri -->
        <div class="sidebar">
            <div>
                <div class="sidebar-brand">
                    <i class="bi bi-grid-fill me-2"></i> NIPN
                </div>
                <ul class="sidebar-menu">
                    <li><a href="halaman_guru.php"><i class="bi bi-people"></i> <span>Daftar Guru</span></a></li>
                    <li class="active"><a href="tambah_nilai_siswa.php"><i class="bi bi-journal-plus"></i> <span>Tambah Nilai Siswa</span></a></li>
                    <li><a href="PROFIL.php"><i class="bi bi-gear"></i> <span>Pengaturan</span></a></li>
                </ul>
            </div>
            <div class="sidebar-footer">
                <div class="mb-2">
                    <a href="#">Bantuan</a> <a href="#">Kontak</a>
                </div>
                <small>&copy; 2026 Sistem Sekolah</small>
            </div>
        </div>

        <!-- Konten Utama -->
        <div class="main-content">
            <!-- Header Atas & Profil Dropdown -->
            <div class="content-header">
                <div>
                    <h3 class="fw-bold mb-1">Daftar Nilai Siswa</h3>
                    <p class="text-muted small mb-0">Kelas: <strong><?= htmlspecialchars($kelas); ?></strong> &nbsp;|&nbsp; Mata Pelajaran: <strong><?= htmlspecialchars($nama_mapel); ?></strong></p>
                </div>
                
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

            <!-- Konten Utama: Tabel Daftar Nilai Siswa -->
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
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
                            <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas); ?>">
                            <input type="hidden" name="mapel_id" value="<?= htmlspecialchars($mapel_id); ?>">
                            <label class="me-2 text-muted small">Cari:</label>
                            <input type="text" name="cari" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($cari); ?>" placeholder="Nama siswa...">
                        </form>
                    </div>
                </div>

                <!-- Tabel Daftar Nilai -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">No <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th>Nama Siswa <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th style="width: 80px; text-align: center;">KKM <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th style="width: 120px; text-align: center;">Pengetahuan <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th style="width: 120px; text-align: center;">Keterampilan <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th style="width: 110px; text-align: center;">Nilai Akhir <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th>Keterangan <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                                <th style="width: 140px; text-align: center;">Aksi <span class="text-muted small">&#x25B4;&#x25BE;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Ambil data siswa berdasarkan kelas dari tabel 'siswa'
                                if (!empty($cari)) {
                                    $stmtSiswa = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE kelas = ? AND nama LIKE ?");
                                    $cariParam = "%$cari%";
                                    mysqli_stmt_bind_param($stmtSiswa, "ss", $kelas, $cariParam);
                                } else {
                                    $stmtSiswa = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE kelas = ?");
                                    mysqli_stmt_bind_param($stmtSiswa, "s", $kelas);
                                }
                                mysqli_stmt_execute($stmtSiswa);
                                $resSiswa = mysqli_stmt_get_result($stmtSiswa);

                                if (mysqli_num_rows($resSiswa) > 0) {
                                    $no = 1;
                                    while ($siswa = mysqli_fetch_assoc($resSiswa)) {
                                        $siswa_id = $siswa['id'] ?? null;
                                        $nama_siswa = $siswa['nama'] ?? 'Siswa';

                                        // Ambil data nilai dari tabel 'nilai_rapor' berdasarkan siswa_id dan mapel_id
                                        $stmtNilai = mysqli_prepare($koneksi, "SELECT * FROM nilai_rapor WHERE siswa_id = ? AND mapel_id = ?");
                                        mysqli_stmt_bind_param($stmtNilai, "ii", $siswa_id, $mapel_id);
                                        mysqli_stmt_execute($stmtNilai);
                                        $resNilai = mysqli_stmt_get_result($stmtNilai);
                                        $nilaiData = mysqli_fetch_assoc($resNilai);

                                        $ada_nilai = ($nilaiData !== null);
                                        $kkm = $nilaiData['nilai_kkm'] ?? 75;
                                        $pengetahuan = $nilaiData['nilai_pengetahuan'] ?? '-';
                                        $keterampilan = $nilaiData['nilai_keterampilan'] ?? '-';
                                        
                                        // Hitung rata-rata Nilai Akhir jika data nilai tersedia
                                        if ($ada_nilai && is_numeric($pengetahuan) && is_numeric($keterampilan)) {
                                            $nilai_akhir = round(($pengetahuan + $keterampilan) / 2);
                                        } else {
                                            $nilai_akhir = '-';
                                        }
                                        
                                        $keterangan = $nilaiData['Keterangan'] ?? '-';
                            ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-medium"><?= htmlspecialchars($nama_siswa); ?></td>
                                            <td class="text-center"><?= $ada_nilai ? $kkm : '-'; ?></td>
                                            <td class="text-center">
                                                <?php if ($ada_nilai && is_numeric($pengetahuan)): ?>
                                                    <span class="badge bg-primary px-3 py-2"><?= $pengetahuan; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ada_nilai && is_numeric($keterampilan)): ?>
                                                    <span class="badge bg-primary px-3 py-2"><?= $keterampilan; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ada_nilai && is_numeric($nilai_akhir)): ?>
                                                    <span class="badge bg-success px-3 py-2"><?= $nilai_akhir; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($keterangan); ?></td>
                                            <td class="text-center">
                                                <?php if ($ada_nilai): ?>
                                                    <a href="edit_nilai.php?id=<?= $nilaiData['id']; ?>&mapel_id=<?= $mapel_id; ?>" class="btn btn-warning btn-sm text-dark px-2 py-1 fw-medium">Edit</a>
                                                    <a href="hapus_nilai.php?id=<?= $nilaiData['id']; ?>&mapel_id=<?= $mapel_id; ?>" class="btn btn-danger btn-sm px-2 py-1 fw-medium" onclick="return confirm('Yakin ingin menghapus nilai ini?')">Hapus</a>
                                                <?php else: ?>
                                                    <a href="input_nilai.php?siswa_id=<?= $siswa_id; ?>&kelas=<?= urlencode($kelas); ?>&mapel_id=<?= $mapel_id; ?>" class="btn btn-primary btn-sm px-3 py-1">Tambah</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                            <?php 
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data siswa di kelas ini.</td></tr>';
                                }
                            } catch (Exception $e) {
                                echo '<tr><td colspan="8" class="text-center py-4 text-danger">Gagal memuat data dari database.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Bagian Paginasi & Tombol Kembali di Bawah -->
                <div class="row align-items-center mt-4">
                    <div class="col-md-6 text-muted small">
                        Menampilkan halaman 1 dari 1
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                        <!-- Tombol Kembali di Bagian Bawah -->
                        <a href="tambah_nilai_siswa.php" class="btn btn-outline-secondary btn-sm me-3 px-3 shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>

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