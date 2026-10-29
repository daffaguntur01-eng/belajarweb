<?php 
// 1. Jalankan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hubungkan koneksi database
require_once('koneksi.php');

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

// 5. LOGIKA PROSES SIMPAN (AKSI)
if (isset($_POST['submit'])) {
    // Escape input untuk mencegah SQL Injection
    $kode_mapel = mysqli_real_escape_string($koneksi, trim($_POST['kode_mapel']));
    $nama_mapel = mysqli_real_escape_string($koneksi, trim($_POST['nama_mapel']));
    $guru_id    = mysqli_real_escape_string($koneksi, $_POST['guru_id']);

    // Cek apakah kode_mapel sudah ada di database
    $cek_kode = mysqli_query($koneksi, "SELECT kode_mapel FROM mapel WHERE kode_mapel = '$kode_mapel'");

    if ($cek_kode && mysqli_num_rows($cek_kode) > 0) {
        echo "<script>
                alert('Gagal! Kode Mapel \"$kode_mapel\" sudah digunakan. Gunakan kode lain.');
                window.history.back();
              </script>";
        exit();
    } else {
        try {
            $sql = "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES ('$kode_mapel', '$nama_mapel', '$guru_id')";
            $query = mysqli_query($koneksi, $sql);

            if ($query) {
                echo "<script>
                        alert('Data mata pelajaran berhasil disimpan!');
                        window.location.href = 'mapel.php'; 
                      </script>";
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            echo "<script>
                    alert('Gagal menyimpan data: " . addslashes($e->getMessage()) . "');
                    window.history.back();
                  </script>";
            exit();
        }
    }
}

// Ambil data guru untuk pilihan dropdown Guru Pengampu
$query_guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Mapel</title>
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

<!-- Panggil Navbar di dalam body -->
<?php require_once('navbar.php'); ?>

<div class="container my-5 d-flex justify-content-center">
    <div class="card shadow-sm border-0 rounded-3 p-4" style="max-width: 450px; width: 100%; bg-white;">
        <h3 class="fw-bold mb-4 text-center">Form Tambah Mapel</h3>
        
        <form action="" method="POST">
            <div class="mb-3">
                <label for="kode_mapel" class="form-label fw-semibold">Kode Mapel</label>
                <input type="text" id="kode_mapel" name="kode_mapel" class="form-control" required placeholder="Contoh: MP001">
            </div>

            <div class="mb-3">
                <label for="nama_mapel" class="form-label fw-semibold">Nama Mata Pelajaran</label>
                <input type="text" id="nama_mapel" name="nama_mapel" class="form-control" required placeholder="Contoh: Pemrograman Web">
            </div>

            <div class="mb-4">
                <label for="guru_id" class="form-label fw-semibold">Guru Mapel</label>
                <select id="guru_id" name="guru_id" class="form-select" required>
                    <option value="">-- Pilih Guru --</option>
                    <?php 
                    if ($query_guru && mysqli_num_rows($query_guru) > 0) {
                        while ($guru = mysqli_fetch_assoc($query_guru)) {
                            echo "<option value='".htmlspecialchars($guru['id'])."'>".htmlspecialchars($guru['nama'])."</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <button type="submit" name="submit" class="btn btn-primary w-100 fw-medium py-2 mb-2">Simpan Data</button>
            <a href="mapel.php" class="btn btn-secondary w-100 fw-medium py-2">Kembali</a>
        </form>
    </div>
</div>

<!-- Import Bootstrap 5 JS Bundle -->
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