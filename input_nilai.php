<?php
session_start();
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

// Tangkap parameter dari URL (jika ada)
$selected_siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$selected_mapel_id = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
$current_kelas       = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Simpan halaman asal (HTTP_REFERER) ke session
if (!isset($_SESSION['halaman_asal']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $_SESSION['halaman_asal'] = $_SERVER['HTTP_REFERER'];
    } else {
        $_SESSION['halaman_asal'] = "nilai_siswa.php?kelas=" . urlencode($current_kelas) . "&mapel_id=" . $selected_mapel_id;
    }
}

// PROSES SIMPAN DATA
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
        $stmt = mysqli_prepare($koneksi, "INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_kkm, nilai_pengetahuan, nilai_keterampilan, Keterangan) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiiiis", $siswa_id, $mapel_id, $nilai_kkm, $nilai_pengetahuan, $nilai_keterampilan, $keterangan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Ambil halaman tujuan kembali dari session
        $redirect_target = $_SESSION['halaman_asal'];
        unset($_SESSION['halaman_asal']); // Bersihkan variabel session asal

        // Redirect kembali ke halaman sebelumnya
        header("Location: " . $redirect_target);
        exit();
    }
}

// Ambil data siswa untuk dropdown
$siswa_list = [];
$resSiswa = mysqli_query($koneksi, "SELECT id, nama, kelas FROM siswa ORDER BY nama ASC");
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
  <title>Tambah Nilai Rapor</title>
  <!-- Import Bootstrap 5 CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

  <!-- Panggil Navbar Eksternal -->
  <?php include 'navbar.php'; ?>

  <div class="container" style="max-width: 600px; margin-top: 40px; margin-bottom: 40px;">
    <div class="card shadow-sm p-4 border-0 rounded-3 bg-white">
      <h4 class="mb-3 fw-semibold text-center">Form Input Nilai Rapor</h4>
      
      <form method="POST" action="">
        <!-- Pilih Siswa -->
        <div class="mb-3">
          <label class="form-label fw-medium">Siswa</label>
          <select name="siswa_id" class="form-select" required>
            <option value="">-- Pilih Siswa --</option>
            <?php foreach ($siswa_list as $s): ?>
              <option value="<?= $s['id'] ?>" <?= ($selected_siswa_id == $s['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['nama'] . ' (' . $s['kelas'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Pilih Mata Pelajaran -->
        <div class="mb-3">
          <label class="form-label fw-medium">Mata Pelajaran</label>
          <select name="mapel_id" class="form-select" required>
            <option value="">-- Pilih Mapel --</option>
            <?php foreach ($mapel_list as $m): ?>
              <option value="<?= $m['id'] ?>" <?= ($selected_mapel_id == $m['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nama_mapel']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Input KKM -->
        <div class="mb-3">
          <label class="form-label fw-medium">Nilai KKM</label>
          <input type="number" name="nilai_kkm" class="form-control" value="75" required>
        </div>

        <!-- Input Nilai Pengetahuan -->
        <div class="mb-3">
          <label class="form-label fw-medium">Nilai Pengetahuan (0-100)</label>
          <input type="number" name="nilai_pengetahuan" class="form-control" placeholder="Contoh: 85" min="0" max="100" required>
        </div>

        <!-- Input Nilai Keterampilan -->
        <div class="mb-3">
          <label class="form-label fw-medium">Nilai Keterampilan (0-100)</label>
          <input type="number" name="nilai_keterampilan" class="form-control" placeholder="Contoh: 90" min="0" max="100" required>
        </div>

        <!-- Tombol Aksi -->
        <button type="submit" class="btn btn-primary w-100 mt-3 py-2 fw-medium">Simpan Nilai</button>
        <button type="button" onclick="history.back()" class="btn btn-secondary w-100 mt-2 py-2 fw-medium">Batal</button>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>