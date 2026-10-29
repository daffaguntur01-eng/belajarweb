<?php
session_start();

// 1. Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login_user.php");
    exit();
}

// 2. Hubungkan file koneksi
require_once 'koneksi.php';
$conn = $koneksi;

// 3. Ambil data user yang sedang login dari database
$username_session = $_SESSION['username'];

$stmt = mysqli_prepare($conn, "SELECT id, username, role, created_at FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username_session);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">

  <div class="card shadow-sm border-0 p-4 rounded-3" style="width: 100%; max-width: 420px;">
    
    <!-- Header Profil -->
    <div class="text-center mb-4">
      <!-- Icon Avatar -->
      <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2.2rem;">
        <i class="bi bi-person-fill"></i>
      </div>
      
      <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['username'] ?? '-'); ?></h4>
      
      <!-- Badge Role -->
      <?php
        $role = $user['role'] ?? 'siswa';
        $badge_color = 'bg-secondary';
        if ($role === 'admin') {
            $badge_color = 'bg-danger';
        } elseif ($role === 'guru') {
            $badge_color = 'bg-warning text-dark';
        } elseif ($role === 'siswa') {
            $badge_color = 'bg-success';
        }
      ?>
      <span class="badge <?= $badge_color; ?> px-3 py-2 fs-6 text-capitalize mt-1">
        <i class="bi bi-shield-check me-1"></i><?= htmlspecialchars($role); ?>
      </span>
    </div>

    <!-- Rincian Informasi User -->
    <div class="bg-light p-3 rounded-3 border mb-4">
      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <span class="text-muted small"><i class="bi bi-hash me-1"></i>ID Pengguna</span>
        <span class="fw-semibold text-dark">#<?= $user['id'] ?? '-'; ?></span>
      </div>

      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <span class="text-muted small"><i class="bi bi-person me-1"></i>Username</span>
        <span class="fw-semibold text-dark"><?= htmlspecialchars($user['username'] ?? '-'); ?></span>
      </div>

      <div class="d-flex justify-content-between align-items-center py-2">
        <span class="text-muted small"><i class="bi bi-calendar-check me-1"></i>Terdaftar Pada</span>
        <span class="fw-semibold text-dark">
          <?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-'; ?>
        </span>
      </div>
    </div>

    <!-- Tombol Kembali -->
    <div>
      <button onclick="history.back()" class="btn btn-outline-secondary w-100 py-2 fw-medium shadow-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Sebelumnya
      </button>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>