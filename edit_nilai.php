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
    $_SESSION['halaman_asal'] = $_SERVER['HTTP_REFERER'] ?? 'nilai_siswa.php';
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
  <title>Edit Nilai Rapor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

  <!-- Panggil Navbar -->
  <?php include 'navbar.php'; ?>

  <div class="container" style="max-width: 600px; margin-top: 40px; margin-bottom: 40px;">
    <div class="card shadow-sm p-4">
      <h4 class="mb-3">Form Edit Nilai Rapor</h4>
      
      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label">Siswa</label>
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
          <label class="form-label">Mata Pelajaran</label>
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
          <label class="form-label">Nilai KKM</label>
          <input type="number" name="nilai_kkm" class="form-control" value="<?= htmlspecialchars($data_nilai['nilai_kkm']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nilai Pengetahuan (0-100)</label>
          <input type="number" name="nilai_pengetahuan" class="form-control" value="<?= htmlspecialchars($data_nilai['nilai_pengetahuan']) ?>" min="0" max="100" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nilai Keterampilan (0-100)</label>
          <input type="number" name="nilai_keterampilan" class="form-control" value="<?= htmlspecialchars($data_nilai['nilai_keterampilan']) ?>" min="0" max="100" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">Update Nilai</button>
        <!-- Tombol Batal Menggunakan JavaScript History Back -->
        <button type="button" onclick="history.back()" class="btn btn-secondary w-100 mt-2">Batal</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>