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
// Jika bukan siswa (misal Admin atau Guru), tolak dan kembali ke halaman sebelumnya
// ===================================================================
if ($role_user !== 'siswa') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk siswa.');
            window.history.back();
          </script>";
    exit;
}

$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";

// Ambil data siswa yang sedang login
$siswa_id    = 0;
$nama_siswa  = $nama_user;
$nis_siswa   = $_SESSION['nis'] ?? '-';
$kelas_siswa = '-';

$user_id_sess = $_SESSION['user_id'] ?? 0;
$nis_sess     = $_SESSION['nis'] ?? '';
$username_sess= $_SESSION['username'] ?? '';

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
    <title>Nilai Siswa - Sistem Informasi Sekolah</title>
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
    <?php require_once 'navbar.php'; ?>

    <!-- Konten Utama: Nilai Siswa -->
    <div class="container my-5" style="max-width: 900px;">
        
        <!-- Header Profil Siswa -->
        <div class="text-center mb-4">
            <h2 class="fw-normal">Nilai Siswa</h2>
            <h4 class="fw-bold mt-2"><?= htmlspecialchars($nama_siswa); ?></h4>
            <p class="text-muted mb-1">NIS: <strong><?= htmlspecialchars($nis_siswa); ?></strong></p>
            <p class="text-muted">Kelas: <strong><?= htmlspecialchars($kelas_siswa); ?></strong></p>
        </div>

        <!-- Kartu Rata-rata Nilai -->
        <div class="card shadow-sm border-0 mb-4 text-center py-4" style="max-width: 400px; margin: 0 auto;">
            <div class="card-body">
                <span class="text-muted fs-6">Rata-rata Nilai</span>
                <h1 class="display-4 fw-normal mt-2"><?= number_format($rata_keseluruhan, 2); ?></h1>
            </div>
        </div>

        <!-- Tabel Daftar Nilai -->
        <div class="card shadow-sm border-0 p-3">
            <h5 class="mb-3 fw-bold">Daftar Nilai</h5>
            
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

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
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
                                    <td><?= htmlspecialchars($row['nama_mapel'] ?? 'Mata Pelajaran'); ?></td>
                                    <td><?= htmlspecialchars($row['nilai_kkm'] ?? '75'); ?></td>
                                    <td><?= htmlspecialchars($row['nilai_pengetahuan'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($row['nilai_keterampilan'] ?? '-'); ?></td>
                                    <td class="fw-bold"><?= number_format($row['rata_mapel'], 2); ?></td>
                                    <td><?= htmlspecialchars($row['Keterangan'] ?? '-'); ?></td>
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
            <div class="row align-items-center mt-3">
                <div class="col-md-6 text-muted small">
                    Menampilkan halaman 1 dari 1
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Pengendali Toggle Profil Dropdown Navbar -->
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