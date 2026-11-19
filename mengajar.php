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

$query = "SELECT mengajar.*, mapel.nama_mapel, mapel.kode_mapel, guru.nama AS nama_guru 
          FROM mengajar 
          LEFT JOIN mapel ON mengajar.mapel_id = mapel.id 
          LEFT JOIN guru ON mapel.guru_id = guru.id 
          ORDER BY mengajar.id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mengajar - SIPN</title>
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
        .card-custom { background: white; border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); padding: 24px; }
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
                    <h3 class="fw-bold mb-1">Data Mengajar</h3>
                    <p class="text-muted small mb-0">Kelola jadwal dan hubungan kelas dengan mata pelajaran</p>
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

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0">Tabel Data Mengajar</h5>
                    <a href="form_mengajar.php" class="btn btn-primary btn-sm px-3"><i class="bi bi-plus-lg me-1"></i> Tambah Mengajar</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">No</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Kelas</th>
                                <th style="width: 18%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result && mysqli_num_rows($result) > 0) {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_mapel'] ?? '-'); ?> (<?= htmlspecialchars($row['kode_mapel'] ?? '-'); ?>)</td>
                                    <td><?= htmlspecialchars($row['nama_guru'] ?? '-'); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['kelas'] ?? '-'); ?></span></td>
                                    <td>
                                        <a href="edit_mengajar.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm text-dark px-2">Edit</a>
                                        <a href="delete_mengajar.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm px-2" onclick="return confirm('Yakin ingin menghapus data ini?')">Delete</a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Tidak ada data mengajar.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>