<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']) || isset($_SESSION['username']); 
if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'Admin';
$role_user = strtolower($_SESSION['role'] ?? '');

if ($role_user !== 'admin') {
    echo "<script>alert('Akses Ditolak! Halaman ini khusus untuk Admin.'); window.history.back();</script>";
    exit();
}

$avatar_url = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($nama_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tambah User (Admin) - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary-blue: #0d6efd; --bg-body: #f4f6f9; }
        body { background-color: var(--bg-body); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--primary-blue); color: white; display: flex; flex-direction: column; justify-content: space-between; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 20px 0; overflow-y: auto; }
        .sidebar-brand { font-size: 1.4rem; font-weight: bold; padding: 0 25px 25px 25px; letter-spacing: 0.5px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: rgba(255, 255, 255, 0.8); text-decoration: none; font-weight: 500; transition: all 0.3s ease; border-top-left-radius: 30px; border-bottom-left-radius: 30px; margin-left: 10px; }
        .sidebar-menu li a:hover, .sidebar-menu li.active > a { color: var(--primary-blue); background-color: var(--bg-body); }
        .sidebar-menu li a i { margin-right: 12px; font-size: 1.2rem; }
        .sidebar-submenu { list-style: none; padding-left: 0; margin: 5px 0 5px 20px; }
        .sidebar-submenu li a { padding: 8px 20px; font-size: 0.9rem; border-radius: 20px; color: rgba(255, 255, 255, 0.7); }
        .sidebar-submenu li a:hover, .sidebar-submenu li a.active { color: #ffffff; background-color: rgba(255, 255, 255, 0.1); }
        .sidebar-footer { padding: 20px 25px 0 25px; font-size: 0.85rem; color: rgba(255, 255, 255, 0.7); }
        .sidebar-footer a { color: rgba(255, 255, 255, 0.8); text-decoration: none; margin-right: 10px; }
        .main-content { margin-left: 260px; flex-grow: 1; padding: 30px; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card-custom { background: white; border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); padding: 30px; max-width: 600px; }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand, .sidebar .sidebar-menu span, .sidebar .sidebar-footer { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'navbar.php'; ?>
        <div class="main-content">
            <div class="content-header">
                <div>
                    <h3 class="fw-bold mb-1">Form Tambah User (Admin)</h3>
                    <p class="text-muted small mb-0">Tambahkan kredensial akun administrator baru</p>
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
                        <li><a class="dropdown-item py-2" href="pengaturan.php"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <div class="card card-custom shadow-sm border-0 rounded-4 bg-white">
                <h5 class="text-secondary mb-4 small fw-bold text-uppercase tracking-wider">Data Kredensial Akun</h5>
                <form action="proses_simpan.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-medium text-muted">Username Akun</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: nama123" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-medium text-muted">Password Akun</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contoh: password123" required>
                        </div>
                    </div>
                    <input type="hidden" name="role" value="admin">
                    <div class="d-flex gap-2">
                        <button type="button" onclick="history.back()" class="btn btn-outline-secondary w-50 py-2">Batal</button>
                        <button type="submit" name="submit" class="btn btn-primary w-50 py-2 fw-medium shadow-sm">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>