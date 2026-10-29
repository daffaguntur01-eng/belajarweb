<?php
session_start();
require_once 'navbar.php';
require_once 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['role'])) {
    header("Location: login_user.php");
    exit();
}

$user_role = strtolower($_SESSION['role']);

// Tangkap parameter kelas atau mapel jika ada di URL untuk kebutuhan tombol kembali / redirect
$current_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// QUERY TAMPILAN DATA NILAI (Menampilkan SEMUA data untuk SEMUA role)
$query = "SELECT nilai_rapor.*, siswa.nama AS nama_siswa, mapel.nama_mapel 
          FROM nilai_rapor 
          LEFT JOIN siswa ON nilai_rapor.siswa_id = siswa.id
          LEFT JOIN mapel ON nilai_rapor.mapel_id = mapel.id";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Nilai Rapor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Daftar Nilai Rapor Siswa</h2>
      <div>
        <!-- TOMBOL "+ TAMBAH NILAI" HANYA MUNCUL JIKA ROLE GURU -->
        <?php if ($user_role === 'guru'): ?>
          <a href="input_nilai.php" class="btn btn-primary btn-sm me-2">+ Tambah Nilai</a>
        <?php endif; ?>
        <a href="tabelusers.php" class="btn btn-secondary btn-sm">Kembali</a>
      </div>
    </div>

    <!-- TABEL NILAI -->
    <div class="card shadow-sm p-3">
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nama Siswa</th>
              <th>Mata Pelajaran</th>
              <th>KKM</th>
              <th>Pengetahuan</th>
              <th>Keterampilan</th>
              <th>Keterangan</th>
              <?php if ($user_role === 'guru'): ?>
                <th class="text-center" style="width: 120px;">Aksi</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['nama_siswa'] ?? 'Siswa ID: ' . $row['siswa_id']) ?></td>
                  <td><?= htmlspecialchars($row['nama_mapel'] ?? 'Mapel ID: ' . $row['mapel_id']) ?></td>
                  <td><?= $row['nilai_kkm'] ?></td>
                  <td><?= $row['nilai_pengetahuan'] ?></td>
                  <td><?= $row['nilai_keterampilan'] ?></td>
                  <td>
                    <span class="badge <?= ($row['Keterangan'] ?? '') === 'Tuntas' ? 'bg-success' : 'bg-danger' ?>">
                      <?= htmlspecialchars($row['Keterangan'] ?? '-') ?>
                    </span>
                  </td>
                  <?php if ($user_role === 'guru'): ?>
                    <td class="text-center">
                      <a href="edit_nilai.php?id=<?= $row['id']; ?>&kelas=<?= urlencode($current_kelas); ?>" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= ($user_role === 'guru') ? '8' : '7' ?>" class="text-center text-muted">Belum ada data nilai.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>