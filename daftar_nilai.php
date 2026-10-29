<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']) || isset($_SESSION['username']); 

// Ambil data user dari session
$nama_user = $is_logged_in ? ($_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Mutiara Zahra, S.Pd., Gr.') : 'Guest';
$role_user = $is_logged_in ? strtolower($_SESSION['role'] ?? 'guru') : 'guru';

// URL Avatar Otomatis mengikuti Nama User
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";

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
<body class="bg-light">

    <!-- Memanggil Navbar Modular -->
    <?php include 'navbar.php'; ?>

    <!-- Konten Utama: Daftar Nilai Siswa -->
    <div class="container my-5">
        <h2 class="mb-1 fw-normal">Daftar Nilai Siswa</h2>
        <p class="text-muted mb-4">Kelas: <strong><?= htmlspecialchars($kelas); ?></strong> &nbsp;|&nbsp; Mata Pelajaran: <strong><?= htmlspecialchars($nama_mapel); ?></strong></p>

        <!-- Bagian Kontrol Tabel (Filter & Pencarian) -->
        <div class="card border-0 shadow-sm p-4 rounded bg-white">
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
                        <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas); ?>">
                        <input type="hidden" name="mapel_id" value="<?= htmlspecialchars($mapel_id); ?>">
                        <label class="me-2">Cari:</label>
                        <input type="text" name="cari" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($cari); ?>">
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
                                                <!-- Tombol Tambah diarahkan ke input_nilai.php dengan membawa parameter -->
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

            <!-- Bagian Paginasi -->
            <div class="row align-items-center mt-4">
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