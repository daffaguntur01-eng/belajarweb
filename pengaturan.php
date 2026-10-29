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

$error = '';
$success = '';

// 3. Ambil data user yang sedang login saat ini
$username_session = $_SESSION['username'];

$stmt = mysqli_prepare($conn, "SELECT id, username, role FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username_session);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 4. Proses Update Data saat Form Dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username     = trim($_POST['username'] ?? '');
    $password_lama    = $_POST['password_lama'] ?? '';
    $password_baru    = $_POST['password_baru'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi dasar
    if (empty($new_username)) {
        $error = "Username tidak boleh kosong!";
    } else {
        // Cek jika username diganti, apakah username baru sudah dipakai orang lain
        if ($new_username !== $user['username']) {
            $cek_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ?");
            mysqli_stmt_bind_param($cek_stmt, "si", $new_username, $user['id']);
            mysqli_stmt_execute($cek_stmt);
            mysqli_stmt_store_result($cek_stmt);

            if (mysqli_stmt_num_rows($cek_stmt) > 0) {
                $error = "Username sudah digunakan oleh akun lain!";
            }
            mysqli_stmt_close($cek_stmt);
        }

        // Jika tidak ada error pada username, cek apakah ingin merubah password
        if (empty($error)) {
            // Update Username
            $update_user_stmt = mysqli_prepare($conn, "UPDATE users SET username = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_user_stmt, "si", $new_username, $user['id']);
            mysqli_stmt_execute($update_user_stmt);
            mysqli_stmt_close($update_user_stmt);

            // Update session jika username berubah
            $_SESSION['username'] = $new_username;
            $user['username'] = $new_username;
            $success = "Username berhasil diperbarui!";

            // Jika form password diisi, lakukan proses ubah password
            if (!empty($password_lama) || !empty($password_baru) || !empty($confirm_password)) {
                if (empty($password_lama) || empty($password_baru) || empty($confirm_password)) {
                    $error = "Semua kolom password harus diisi jika ingin mengganti password!";
                } elseif ($password_baru !== $confirm_password) {
                    $error = "Konfirmasi password baru tidak cocok!";
                } elseif (strlen($password_baru) < 6) {
                    $error = "Password baru minimal harus 6 karakter!";
                } else {
                    // Verifikasi password lama dari database
                    $pass_stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
                    mysqli_stmt_bind_param($pass_stmt, "i", $user['id']);
                    mysqli_stmt_execute($pass_stmt);
                    $pass_res = mysqli_stmt_get_result($pass_stmt);
                    $pass_data = mysqli_fetch_assoc($pass_res);
                    mysqli_stmt_close($pass_stmt);

                    if (password_verify($password_lama, $pass_data['password'])) {
                        // Hash password baru
                        $hashed_new_pass = password_hash($password_baru, PASSWORD_DEFAULT);
                        
                        $pass_upd_stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                        mysqli_stmt_bind_param($pass_upd_stmt, "si", $hashed_new_pass, $user['id']);
                        
                        if (mysqli_stmt_execute($pass_upd_stmt)) {
                            $success = "Profil dan Password berhasil diperbarui!";
                        } else {
                            $error = "Gagal memperbarui password.";
                        }
                        mysqli_stmt_close($pass_upd_stmt);
                    } else {
                        $error = "Password lama yang Anda masukkan salah!";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">

  <div class="card shadow-sm border-0 p-4 rounded-3" style="width: 100%; max-width: 450px;">
    
    <div class="d-flex align-items-center mb-3">
      <button onclick="history.back()" class="btn btn-sm btn-outline-secondary me-2">
        <i class="bi bi-arrow-left"></i>
      </button>
      <h4 class="fw-bold m-0 text-primary">Edit Profil</h4>
    </div>
    <p class="text-muted small mb-4">Ubah informasi akun dan kata sandi Anda di sini.</p>

    <!-- Notifikasi Alert -->
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show py-2 small mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $error; ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success alert-dismissible fade show py-2 small mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i><?= $success; ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <!-- Edit Username -->
      <div class="mb-3">
        <label for="username" class="form-label fw-medium">Username</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-secondary"><i class="bi bi-person"></i></span>
          <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($user['username'] ?? ''); ?>">
        </div>
      </div>

      <hr class="my-4 text-muted">

      <p class="fw-medium text-dark mb-2 small"><i class="bi bi-shield-lock me-1"></i> Ubah Password <span class="text-muted fw-normal">(Opsional)</span></p>

      <!-- Password Lama -->
      <div class="mb-3">
        <label for="password_lama" class="form-label small text-muted">Password Saat Ini</label>
        <input type="password" name="password_lama" id="password_lama" class="form-control" placeholder="Masukkan password lama">
      </div>

      <!-- Password Baru -->
      <div class="mb-3">
        <label for="password_baru" class="form-label small text-muted">Password Baru</label>
        <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="Minimal 6 karakter">
      </div>

      <!-- Konfirmasi Password Baru -->
      <div class="mb-4">
        <label for="confirm_password" class="form-label small text-muted">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Ulangi password baru">
      </div>

      <!-- Tombol Aksi -->
      <div class="d-flex gap-2">
        <button type="button" onclick="history.back()" class="btn btn-outline-secondary w-50 py-2">Batal</button>
        <button type="submit" class="btn btn-primary w-50 py-2 fw-medium shadow-sm">Simpan</button>
      </div>
    </form>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>