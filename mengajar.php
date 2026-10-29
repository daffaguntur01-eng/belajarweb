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

// Query JOIN untuk mengumpulkan data mata pelajaran, pengajar (guru), dan siswa
$sql = "SELECT 
            mengajar.id AS mengajar_id,
            mapel.kode_mapel,
            mapel.nama_mapel,
            guru.nama AS nama_guru,
            siswa.nis,
            siswa.nama AS nama_siswa,
            siswa.kelas
        FROM mengajar
        INNER JOIN mapel ON mengajar.mapel_id = mapel.id
        LEFT JOIN guru ON mapel.guru_id = guru.id
        INNER JOIN siswa ON mengajar.siswa_id = siswa.id
        ORDER BY mengajar.id DESC";

$query = mysqli_query($koneksi, $sql);
$data = []; 

if ($query && mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mengajar - Sistem Informasi Sekolah</title>
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
        <!-- Header Judul -->
        <div class="text-center mb-4">
            <h1 class="fw-normal display-5">Data Mengajar</h1>
            <hr class="w-25 mx-auto border border-dark border-2 opacity-75">
        </div>

        <!-- Tombol Tambah Data -->
        <div class="mb-3 d-flex justify-content-end">
            <a href="tambah_mengajar.php" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg"></i> Tambah Data Mengajar
            </a>
        </div>

        <!-- Tabel Data Mengajar -->
        <div class="table-responsive bg-white p-3 rounded shadow-sm">
            <table class="table table-striped table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col" style="width: 60px;">No</th>
                  <th scope="col">Mata Pelajaran</th> 
                  <th scope="col">Guru Pengajar</th>
                  <th scope="col">Siswa</th>
                  <th scope="col">Kelas</th>
                  <th scope="col" style="width: 15%;">Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php
              if (!empty($data)) {
                  $no = 1; 
                  foreach ($data as $row) {
                ?>
                <tr>
                  <th scope="row"><?php echo $no++; ?></th>
                  
                  <td>
                    <strong><?php echo htmlspecialchars($row['nama_mapel']); ?></strong>
                    <br><small class="text-muted">Kode: <?php echo htmlspecialchars($row['kode_mapel']); ?></small>
                  </td>
                  
                  <td>
                    <?php echo htmlspecialchars($row['nama_guru'] ?? 'Belum ada guru'); ?>
                  </td>

                  <td>
                    <strong><?php echo htmlspecialchars($row['nama_siswa']); ?></strong>
                    <br><small class="text-muted">NIS: <?php echo htmlspecialchars($row['nis']); ?></small>
                  </td>

                  <td>
                    <span class="badge bg-secondary">
                        <?php echo htmlspecialchars($row['kelas']); ?>
                    </span>
                  </td>
                  
                  <td>
                    <a href="edit_mengajar.php?id=<?= $row['mengajar_id']; ?>" class="btn btn-warning btn-sm text-dark px-2">Edit</a>
                    <a href="delete_mengajar.php?id=<?php echo $row['mengajar_id']; ?>" class="btn btn-danger btn-sm px-2" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</a>
                  </td>
                </tr>
              <?php 
                  } 
              } else {
                  echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Tidak ada data mengajar.</td></tr>";
              }
              ?>
              </tbody>
            </table>
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