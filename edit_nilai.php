<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['role'])) {
    header("Location: login_user.php");
    exit();
}

$user_role = strtolower($_SESSION['role']);

// PROTEKSI AKSES KETAT: Hanya role 'guru' yang boleh masuk
if ($user_role !== 'guru') {
    die("<div style='color:red; text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Akses Ditolak!</h2>
            <p>Halaman ini khusus untuk Guru.</p>
            <a href='javascript:history.back()' style='text-decoration:none; color:#0d6efd; font-weight:bold;'>Kembali ke Halaman Sebelumnya</a>
         </div>");
    exit();
}

// Data user untuk header
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Mutiara Zahra, S.Pd., Gr.';
$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);

// Tangkap ID data nilai dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>history.back();</script>";
    exit();
}

// Ambil data nilai rapor beserta data kelas dari tabel siswa
$query_nilai = mysqli_query($koneksi, "SELECT nilai_rapor.*, siswa.kelas AS kelas_siswa FROM nilai_rapor LEFT JOIN siswa ON nilai_rapor.siswa_id = siswa.id WHERE nilai_rapor.id = $id");
if (!$query_nilai || mysqli_num_rows($query_nilai) === 0) {
    die("Data nilai rapor tidak ditemukan.");
}
$data_nilai = mysqli_fetch_assoc($query_nilai);

// Simpan halaman asal (referer) ke dalam session agar tahu harus kembali ke mana setelah update
if (!isset($_SESSION['halaman_asal']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['halaman_asal'] = $_SERVER['HTTP_REFERER'] ?? 'tambah_nilai_siswa.php';
}

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id           = (int)($_POST['siswa_id'] ?? 0);
    $mapel_id           = (int)($_POST['mapel_id'] ?? 0);
    $nilai_kkm          = (int)($_POST['nilai_kkm'] ?? 75);
    $nilai_pengetahuan  = (int)($_POST['nilai_pengetahuan'] ?? 0);
    $nilai_keterampilan = (int)($_POST['nilai_keterampilan'] ?? 0);

    // Otomatis tentukan status kelulusan
    if ($nilai_pengetahuan >= $nilai_kkm && $nilai_keterampilan >= $nilai_kkm) {
        $keterangan = "Tuntas";
    } else {
        $keterangan = "Tidak Tuntas";
    }

    if ($siswa_id > 0 && $mapel_id > 0) {
        $stmt = mysqli_prepare($koneksi, "UPDATE nilai_rapor SET siswa_id = ?, mapel_id = ?, nilai_kkm = ?, nilai_pengetahuan = ?, nilai_keterampilan = ?, Keterangan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "iiiiisi", $siswa_id, $mapel_id, $nilai_kkm, $nilai_pengetahuan, $nilai_keterampilan, $keterangan, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Ambil halaman tujuan kembali dari session
        $redirect_target = $_SESSION['halaman_asal'];
        unset($_SESSION['halaman_asal']); // Bersihkan session asal

        // Redirect kembali ke halaman sebelumnya
        header("Location: " . $redirect_target);
        exit();
    }
}

// Ambil data siswa untuk dropdown
$siswa_list = [];
$resSiswa = mysqli_query($koneksi, "SELECT id, nama, kelas FROM siswa");
if ($resSiswa) {
    while ($row = mysqli_fetch_assoc($resSiswa)) {
        $siswa_list[] = $row;
    }
}

// Ambil data mata pelajaran untuk dropdown
$mapel_list = [];
$resMapel = mysqli_query($koneksi, "SELECT id, nama_mapel FROM mapel");
if (!$resMapel) {
    $resMapel = mysqli_query($koneksi, "SELECT id, nama AS nama_mapel FROM mapel");
}
if ($resMapel) {
    while ($row = mysqli_fetch_assoc($resMapel)) {
        $mapel_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Nilai Rapor - Sistem Informasi Sekolah</title>
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
          <!-- Header Atas & Profil Dropdown -->
          <div class="content-header">
              <div>
                  <h3 class="fw-bold mb-1">Edit Nilai Rapor</h3>
                  <p class="text-muted small mb-0">Perbarui data nilai pengetahuan dan keterampilan siswa</p>
              </div>
              
              <div class="dropdown">
                  <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="btnProfilToggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <img src="<?= $avatar_url; ?>" class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                      <div>
                          <span class="d-block fw-semibold small"><?= htmlspecialchars($nama_user); ?></span>
                          <span class="text-muted" style="font-size: 0.75rem; text-transform: capitalize;"><?= htmlspecialchars($user_role); ?></span>
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

          <!-- Form Edit Nilai dalam Card -->
          <div class="card shadow-sm border-0 p-4 rounded-4 bg-white" style="max-width: 650px;">
              <form method="POST" action="">
                <div class="mb-3">
                  <label class="form-label fw-medium small text-muted">Siswa</label>
                  <select name="siswa_id" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($siswa_list as $s): ?>
                      <option value="<?= $s['id'] ?>" <?= ($data_nilai['siswa_id'] == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nama'] . ' (' . $s['kelas'] . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-medium small text-muted">Mata Pelajaran</label>
                  <select name="mapel_id" class="form-select" required>
                    <option value="">-- Pilih Mapel --</option>
                    <?php foreach ($mapel_list as $m): ?>
                      <option value="<?= $m['id'] ?>" <?= ($data_nilai['mapel_id'] == $m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nama_mapel']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-medium small text-muted">Nilai KKM</label>
                  <input type="number" name="nilai_kkm" class="form-control" value="<?= htmlspecialchars($data_nilai['nilai_kkm']) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-medium small text-muted">Nilai Pengetahuan (0-100)</label>
                  <input type="number" name="nilai_pengetahuan" class="form-control" value="<?= htmlspecialchars($data_nilai['nilai_pengetahuan']) ?>" min="0" max="100" required>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-medium small text-muted">Nilai Keterampilan (0-100)</label>
                  <input type="number" name="nilai_keterampilan" class="form-control" value="<?= htmlspecialchars($data_nilai['nilai_keterampilan']) ?>" min="0" max="100" required>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2">Update Nilai</button>
                    <!-- Tombol Batal Menggunakan JavaScript History Back -->
                    <button type="button" onclick="history.back()" class="btn btn-outline-secondary px-4 py-2">Batal</button>
                </div>
              </form>
          </div>
      </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>