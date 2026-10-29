<?php
// 1. Jalankan session di baris paling atas
session_start();

// 2. Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

// 3. PROTEKSI HAK AKSES: Hanya Admin yang boleh masuk
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    // LANGSUNG KE login_user.php AGAR TIDAK KENA 404
    die("<div style='color:red; text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Akses Ditolak!</h2>
            <p>Halaman ini hanya dapat diakses oleh Admin.</p>
            <a href='login_user.php' style='text-decoration:none; color:#0d6efd; font-weight:bold;'>Ke Halaman Login</a>
         </div>");
    exit();
}

// 4. JIKA FORM DISUBMIT (METHOD POST) -> PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $role     = strtolower(trim($_POST['role'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($id <= 0 || empty($role)) {
        die("Data ID atau Role tidak valid.");
    }

    // A. Update Password jika diisi
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmtPass = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtPass, "si", $passwordHash, $id);
        mysqli_stmt_execute($stmtPass);
        mysqli_stmt_close($stmtPass);
    }

    // B. Update Data Berdasarkan Role
    if ($role === 'siswa') {
        $nis           = trim($_POST['nis'] ?? '');
        $nama_siswa    = trim($_POST['nama_siswa'] ?? '');
        $kelas         = trim($_POST['kelas'] ?? '');
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');

        $stmt = mysqli_prepare($koneksi, "UPDATE siswa SET nis = ?, nama = ?, kelas = ?, jenis_kelamin = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nis, $nama_siswa, $kelas, $jenis_kelamin, $id);
        
        if (!mysqli_stmt_execute($stmt)) {
            // Fallback jika nama kolom 'nama_siswa'
            $stmtAlt = mysqli_prepare($koneksi, "UPDATE siswa SET nis = ?, nama_siswa = ?, kelas = ?, jenis_kelamin = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmtAlt, "ssssi", $nis, $nama_siswa, $kelas, $jenis_kelamin, $id);
            mysqli_stmt_execute($stmtAlt);
            mysqli_stmt_close($stmtAlt);
        } else {
            mysqli_stmt_close($stmt);
        }

    } elseif ($role === 'guru') {
        $nip           = trim($_POST['nip'] ?? '');
        $nama          = trim($_POST['nama'] ?? '');
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');

        $stmt = mysqli_prepare($koneksi, "UPDATE guru SET nip = ?, nama = ?, jenis_kelamin = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $nip, $nama, $jenis_kelamin, $id);
        
        if (!mysqli_stmt_execute($stmt)) {
            // Fallback jika nama kolom 'nama_guru'
            $stmtAlt = mysqli_prepare($koneksi, "UPDATE guru SET nip = ?, nama_guru = ?, jenis_kelamin = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmtAlt, "sssi", $nip, $nama, $jenis_kelamin, $id);
            mysqli_stmt_execute($stmtAlt);
            mysqli_stmt_close($stmtAlt);
        } else {
            mysqli_stmt_close($stmt);
        }

    } elseif ($role === 'admin') {
        $username = trim($_POST['username'] ?? '');

        $stmt = mysqli_prepare($koneksi, "UPDATE users SET username = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $username, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Redirect kembali ke tabel pengguna setelah sukses update
    header("Location: tabelusers.php");
    exit();
}

// 5. JIKA HALAMAN DIBUKA BIASA VIA LINK (METHOD GET) -> TAMPILKAN FORM EDIT
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID User tidak valid.");
}

// Ambil data user dari database berdasarkan ID
$queryUser = "SELECT users.*, 
                     siswa.nis, siswa.nama AS nama_siswa, siswa.kelas, siswa.jenis_kelamin AS jk_siswa,
                     guru.nip, guru.nama AS nama_guru, guru.jenis_kelamin AS jk_guru
              FROM users 
              LEFT JOIN siswa ON users.id = siswa.user_id 
              LEFT JOIN guru ON users.id = guru.user_id 
              WHERE users.id = ?";
$stmt = mysqli_prepare($koneksi, $queryUser);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    die("User tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
  <div class="container" style="max-width: 600px;">
    <div class="card shadow-sm p-4">
      <h4 class="mb-3">Edit User (Role: <?= strtoupper($user['role']) ?>)</h4>
      
      <form method="POST" action="edit_user.php">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <input type="hidden" name="role" value="<?= $user['role'] ?>">

        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password Baru (Kosongkan jika tidak diubah)</label>
          <input type="password" name="password" class="form-control" placeholder="Password baru">
        </div>

        <?php if ($user['role'] === 'siswa'): ?>
          <div class="mb-3">
            <label class="form-label">NIS</label>
            <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($user['nis'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" value="<?= htmlspecialchars($user['nama_siswa'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($user['kelas'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select">
              <option value="L" <?= ($user['jk_siswa'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
              <option value="P" <?= ($user['jk_siswa'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
          </div>

        <?php elseif ($user['role'] === 'guru'): ?>
          <div class="mb-3">
            <label class="form-label">NIP</label>
            <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($user['nip'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Guru</label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama_guru'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select">
              <option value="L" <?= ($user['jk_guru'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
              <option value="P" <?= ($user['jk_guru'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary w-100 mt-3">Simpan Perubahan</button>
        <a href="tabelusers.php" class="btn btn-secondary w-100 mt-2">Batal</a>
      </form>
    </div>
  </div>
</body>
</html>