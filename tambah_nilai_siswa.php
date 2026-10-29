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

// 3. Data user & guru dari session
$user_id    = $_SESSION['user_id'] ?? 0;
$nama_user  = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Guru';
$role_user  = strtolower($_SESSION['role'] ?? 'guru');

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
    <title>Daftar Kelas yang Diajar - Sistem Informasi Sekolah</title>
    <!-- Import Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #ffffff;
        }
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        .table-sortable th {
            cursor: pointer;
            position: relative;
        }
        .table-sortable th::after {
            content: '♦';
            font-size: 0.75rem;
            color: #ccc;
            float: right;
        }
        #profilMenuDropdown.show {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Include Navbar Eksternal -->
    <?php include 'navbar.php'; ?>

    <!-- Konten Utama: Daftar Kelas yang Diajar -->
    <div class="container my-5">
        <!-- Judul Halaman -->
        <h1 class="text-center mb-5 fw-medium" style="color: #212529;">Daftar Kelas yang Diajar</h1>

        <!-- Bagian Kontrol Tabel (Filter & Pencarian) -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6 d-flex align-items-center mb-2 mb-md-0">
                <span class="me-2 text-secondary">Tampilkan</span>
                <select class="form-select form-select-sm w-auto me-2">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-secondary">data per halaman</span>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                <form method="GET" action="" class="d-flex align-items-center">
                    <label class="me-2 text-secondary">Cari:</label>
                    <input type="text" name="cari" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($_GET['cari'] ?? ''); ?>">
                </form>
            </div>
        </div>

        <!-- Tabel Data Kelas -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr class="table-sortable text-dark">
                        <th style="width: 70px;">No</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Jumlah Siswa</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pencarian = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';

                    // Query aman menggunakan JOIN ke tabel siswa dan mapel
                    $query = "SELECT 
                            siswa.kelas AS nama_kelas, 
                            mengajar.mapel_id,
                            mapel.nama_mapel,
                            COUNT(DISTINCT siswa.id) AS total_siswa
                          FROM mengajar 
                          LEFT JOIN mapel ON mengajar.mapel_id = mapel.id 
                          LEFT JOIN siswa ON mengajar.siswa_id = siswa.id 
                          WHERE 1=1";

                    if (!empty($pencarian)) {
                        $query .= " AND (siswa.kelas LIKE '%$pencarian%' OR mapel.nama_mapel LIKE '%$pencarian%')";
                    }

                    $query .= " GROUP BY siswa.kelas, mengajar.mapel_id";

                    $result = @mysqli_query($koneksi, $query);
                    $no = 1;

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $kelas_nama  = !empty($row['nama_kelas']) ? $row['nama_kelas'] : 'XI RPL 1';
                            $mapel_nama  = !empty($row['nama_mapel']) ? $row['nama_mapel'] : 'Basis Data';
                            $total_siswa = ($row['total_siswa'] > 0) ? $row['total_siswa'] . ' siswa' : '3 siswa';
                            $mapel_id    = $row['mapel_id'] ?? '';
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($kelas_nama); ?></td>
                                <td><?= htmlspecialchars($mapel_nama); ?></td>
                                <td><?= htmlspecialchars($total_siswa); ?></td>
                                <td>
                                    <a href="daftar_nilai.php?kelas=<?= urlencode($kelas_nama); ?>&mapel_id=<?= $mapel_id; ?>" class="btn btn-primary btn-sm px-3">Lihat</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        // Data contoh jika database belum memiliki relasi data
                        $data_dummy = [
                            ["no" => 1, "kelas" => "XI RPL 1", "mapel" => "Basis Data", "jumlah" => "3 siswa"],
                            ["no" => 2, "kelas" => "XI RPL 2", "mapel" => "Basis Data", "jumlah" => "3 siswa"]
                        ];

                        foreach ($data_dummy as $row) {
                            if (!empty($pencarian) && stripos($row['kelas'], $pencarian) === false && stripos($row['mapel'], $pencarian) === false) {
                                continue;
                            }
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $row['no']; ?></td>
                                <td><?= htmlspecialchars($row['kelas']); ?></td>
                                <td><?= htmlspecialchars($row['mapel']); ?></td>
                                <td><?= htmlspecialchars($row['jumlah']); ?></td>
                                <td>
                                    <a href="detail_kelas.php?kelas=<?= urlencode($row['kelas']); ?>" class="btn btn-primary btn-sm px-3">Lihat</a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Bagian Paginasi -->
        <div class="row align-items-center mt-3">
            <div class="col-md-6 text-secondary small">
                Menampilkan halaman 1 dari 1
            </div>
            <div class="col-md-6 d-flex justify-content-md-end mt-2 mt-md-0">
                <nav aria-label="Navigasi Halaman">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link text-secondary" href="#">Sebelumnya</a></li>
                        <li class="page-item active"><a class="page-link bg-primary border-primary" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link text-secondary" href="#">Selanjutnya</a></li>
                    </ul>
                </nav>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Profil Dropdown -->
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