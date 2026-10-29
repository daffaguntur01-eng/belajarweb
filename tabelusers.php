<?php
// Jalankan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']); 

if (!$is_logged_in) {
    header("Location: login_user.php");
    exit();
}

// Ambil data user dari session
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'Admin';
$role_user = strtolower($_SESSION['role'] ?? '');

// ===================================================================
// PENGAMAN DINDING HAK AKSES (KHUSUS ADMIN)
// Jika bukan admin (misal Guru atau Siswa), tolak dan kembali ke halaman sebelumnya
// ===================================================================
if ($role_user !== 'admin') {
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk Admin.');
            window.history.back();
          </script>";
    exit();
}

// URL Avatar Otomatis mengikuti Nama User
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";

// Mengambil data user dari database
$query = "SELECT * FROM users";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Sistem Informasi Sekolah</title>
    <!-- Import Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table th {
            font-weight: 600;
        }
        #profilMenuDropdown.show {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Memanggil Navbar Modular Utama -->
    <?php require_once 'navbar.php'; ?>

    <!-- Konten Utama: Data User -->
    <div class="container my-5">
        <h2 class="mb-4">Data User</h2>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama / Username</th>
                                <th>Role</th>
                                <th>Waktu Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['nama'] ?? $row['username']); ?></div>
                                        <?php if (!empty($row['username'])): ?>
                                            <small class="text-muted">@<?= htmlspecialchars($row['username']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $role = strtolower($row['role'] ?? 'siswa');
                                        $badge_bg = 'bg-secondary';
                                        if ($role === 'admin') $badge_bg = 'bg-danger';
                                        elseif ($role === 'guru') $badge_bg = 'bg-primary';
                                        elseif ($role === 'siswa') $badge_bg = 'bg-success';
                                        ?>
                                        <span class="badge <?= $badge_bg; ?> text-capitalize"><?= htmlspecialchars($role); ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['created_at'] ?? '-'); ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm text-dark px-2">Edit</a>
                                        <a href="hapus_user.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm px-2" onclick="return confirm('Yakin ingin menghapus user ini?')">Delete</a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data user.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Skrip Inisialisasi Dropdown Profil Navbar -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btnToggle = document.getElementById("btnProfilToggle");
            const menuDropdown = document.getElementById("profilMenuDropdown");

            if (btnToggle && menuDropdown) {
                btnToggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    menuDropdown.classList.toggle("show");
                });

                document.addEventListener("click", function (e) {
                    if (!btnToggle.contains(e.target) && !menuDropdown.contains(e.target)) {
                        menuDropdown.classList.remove("show");
                    }
                });
            }
        });
    </script>
</body>
</html>