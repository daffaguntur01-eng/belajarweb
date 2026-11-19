<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hubungkan file koneksi
require_once 'koneksi.php';
$conn = $koneksi;

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']) || isset($_SESSION['username']); 

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

$user_id   = $_SESSION['user_id'] ?? null;
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'User';
$role_user = strtolower($_SESSION['role'] ?? 'guru');

// Ambil data user dari database
$username_session = $_SESSION['username'] ?? $_SESSION['nama'] ?? '';
if ($user_id) {
    $stmt = mysqli_prepare($conn, "SELECT id, username, role, password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, username, role, password FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username_session);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    echo "<script>alert('Data user tidak ditemukan.'); window.location.href='login_user.php';</script>";
    exit();
}

// Update role dari database jika ada
$role_user = strtolower($user['role'] ?? $role_user);

// URL Avatar Kartun Otomatis (DiceBear)
$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);

// Tentukan halaman beranda berdasarkan role
$home_page = ($role_user === 'siswa') ? 'halaman_siswa.php' : 'halaman_guru.php';

// Simpan halaman asal ke session jika belum ada
if (!isset($_SESSION['halaman_asal']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['halaman_asal'] = $_SERVER['HTTP_REFERER'] ?? $home_page;
}

$error = '';

// Proses Update Data saat Form Dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username     = trim($_POST['username'] ?? '');
    $password_lama    = $_POST['password_lama'] ?? '';
    $password_baru    = $_POST['password_baru'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_username)) {
        $error = "Username tidak boleh kosong!";
    } else {
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

        if (empty($error)) {
            $update_user_stmt = mysqli_prepare($conn, "UPDATE users SET username = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_user_stmt, "si", $new_username, $user['id']);
            mysqli_stmt_execute($update_user_stmt);
            mysqli_stmt_close($update_user_stmt);

            $_SESSION['username'] = $new_username;

            if (!empty($password_lama) || !empty($password_baru) || !empty($confirm_password)) {
                if (empty($password_lama) || empty($password_baru) || empty($confirm_password)) {
                    $error = "Jika ingin mengganti password, semua kolom password harus diisi!";
                } elseif ($password_baru !== $confirm_password) {
                    $error = "Konfirmasi password baru tidak cocok!";
                } elseif (strlen($password_baru) < 6) {
                    $error = "Password baru minimal harus 6 karakter!";
                } else {
                    if (password_verify($password_lama, $user['password'])) {
                        $hashed_new_pass = password_hash($password_baru, PASSWORD_DEFAULT);
                        $pass_upd_stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                        mysqli_stmt_bind_param($pass_upd_stmt, "si", $hashed_new_pass, $user['id']);
                        
                        if (!mysqli_stmt_execute($pass_upd_stmt)) {
                            $error = "Gagal memperbarui password.";
                        }
                        mysqli_stmt_close($pass_upd_stmt);
                    } else {
                        $error = "Password lama yang Anda masukkan salah!";
                    }
                }
            }

            if (empty($error)) {
                $redirect_target = $_SESSION['halaman_asal'];
                unset($_SESSION['halaman_asal']);
                header("Location: " . $redirect_target);
                exit();
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
    <title>Pengaturan Akun - NIPN</title>
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
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
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
        .card-custom {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 30px;
            max-width: 600px;
        }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand, .sidebar .sidebar-menu span, .sidebar .sidebar-footer { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Memanggil Sidebar Terpusat dari navbar.php -->
        <?php include 'navbar.php'; ?>

        <!-- Konten Utama -->
        <div class="main-content">
            <div class="content-header">
                <div>
                    <h3 class="fw-bold mb-1">Pengaturan Akun</h3>
                    <p class="text-muted small mb-0">Kelola informasi akun dan keamanan kata sandi Anda</p>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="btnProfilToggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $avatar_url; ?>" class="rounded-circle me-2 shadow-sm bg-light" width="40" height="40" alt="Avatar">
                        <div>
                            <span class="d-block fw-semibold small"><?= htmlspecialchars($nama_user); ?></span>
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: capitalize;"><?= htmlspecialchars($role_user); ?></span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="btnProfilToggle">
                        <li><a class="dropdown-item py-2" href="profil.php"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><a class="dropdown-item py-2 active" href="pengaturan.php"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- Card Form Pengaturan -->
            <div class="card card-custom shadow-sm border-0 rounded-4 bg-white">
                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger alert-dismissible fade show py-2 small mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $error; ?>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                <?php endif; ?>

                <form method="POST" action="">
                  <div class="mb-3">
                    <label for="username" class="form-label fw-medium small text-muted">Username</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light text-secondary"><i class="bi bi-person"></i></span>
                      <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($user['username'] ?? ''); ?>">
                    </div>
                  </div>

                  <hr class="my-4 text-muted">

                  <p class="fw-medium text-dark mb-2 small"><i class="bi bi-shield-lock me-1"></i> Ubah Password <span class="text-muted fw-normal">(Opsional, biarkan kosong jika tidak ingin mengubah)</span></p>

                  <div class="mb-3">
                    <label for="password_lama" class="form-label small text-muted">Password Saat Ini</label>
                    <input type="password" name="password_lama" id="password_lama" class="form-control" placeholder="Masukkan password lama">
                  </div>

                  <div class="mb-3">
                    <label for="password_baru" class="form-label small text-muted">Password Baru</label>
                    <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="Minimal 6 karakter">
                  </div>

                  <div class="mb-4">
                    <label for="confirm_password" class="form-label small text-muted">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Ulangi password baru">
                  </div>

                  <div class="d-flex gap-2">
                    <button type="button" onclick="history.back()" class="btn btn-outline-secondary w-50 py-2">Batal</button>
                    <button type="submit" class="btn btn-primary w-50 py-2 fw-medium shadow-sm">Simpan</button>
                  </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>