<?php
// Fallback pencegah error jika dipanggil tanpa header atau session belum ada
if (!isset($is_logged_in)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['nis']) || isset($_SESSION['nama']);
    $nama_user = $is_logged_in ? ($_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['nis'] ?? 'User') : 'Guest';
    $role_user = $is_logged_in ? strtolower($_SESSION['role'] ?? 'siswa') : '';
    $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0D6EFD&color=fff";
}
?>

<!-- Header / Navbar -->
<header class="bg-white border-bottom border-light-subtle sticky-top">
    <div class="container-xl d-flex align-items-center justify-content-between" style="height: 64px;">
        
        <!-- Bagian Kiri: Logo & Menu Navigasi -->
        <div class="d-flex align-items-center gap-4">
            
            <!-- Logo (Disesuaikan berdasarkan Role) -->
            <a class="text-primary fw-bold fs-5 text-decoration-none flex-shrink-0" href="
                <?php if ($is_logged_in): ?>
                    <?php if ($role_user === 'admin'): ?>tabelusers.php
                    <?php elseif ($role_user === 'guru'): ?>halaman_guru.php
                    <?php else: ?>halaman_siswa.php
                    <?php endif; ?>
                <?php else: ?>halaman_siswa.php
                <?php endif; ?>">
                Navbar
            </a>

            <!-- Navigasi Desktop -->
            <nav aria-label="Global" class="d-none d-md-block">
                <ul class="d-flex align-items-center gap-3 mb-0 list-unstyled">
                    
                    <!-- Tombol Home -->
                    <li>
                        <?php if ($is_logged_in): ?>
                            <?php if ($role_user === 'admin'): ?>
                                <a class="text-dark text-decoration-none fw-medium px-2 py-1" href="tabelusers.php">Home</a>
                            <?php elseif ($role_user === 'guru'): ?>
                                <a class="text-dark text-decoration-none fw-medium px-2 py-1" href="halaman_guru.php">Home</a>
                            <?php else: ?>
                                <a class="text-dark text-decoration-none fw-medium px-2 py-1" href="halaman_siswa.php">Home</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="text-dark text-decoration-none fw-medium px-2 py-1" href="halaman_siswa.php">Home</a>
                        <?php endif; ?>
                    </li>

                    <!-- ================= 1. MENU UNTUK ADMIN ================= -->
                    <?php if ($is_logged_in && $role_user === 'admin'): ?>
                        
                        <li class="nav-item dropdown">
                            <a class="text-dark text-decoration-none dropdown-toggle fw-medium px-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">User</a>
                            <ul class="dropdown-menu shadow-sm border-0">
                                <li><a class="dropdown-item" href="tabelusers.php">Daftar User</a></li>
                                <li><a class="dropdown-item" href="form_user1.php">Tambahkan user1</a></li>
                                <li><a class="dropdown-item" href="form_user2.php">Tambahkan user2</a></li>
                                <li><a class="dropdown-item" href="form_user.php">Tambahkan user3</a></li>
                            </ul>
                        </li>

                        <!-- Menu Siswa (Sesuai Permintaan) -->
                        <li class="nav-item dropdown">
                            <a class="text-dark text-decoration-none dropdown-toggle fw-medium px-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Siswa</a>
                            <ul class="dropdown-menu shadow-sm border-0">
                                <li><a class="dropdown-item" href="tabel.php">Daftar Siswa</a></li>
                                <li><a class="dropdown-item" href="form_user2.php ">Tambah Siswa</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="text-dark text-decoration-none dropdown-toggle fw-medium px-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Guru</a>
                            <ul class="dropdown-menu shadow-sm border-0">
                                <li><a class="dropdown-item" href="input_nilai.php">Tambah Nilai</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="text-dark text-decoration-none dropdown-toggle fw-medium px-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Mapel</a>
                            <ul class="dropdown-menu shadow-sm border-0">
                                <li><a class="dropdown-item" href="mapel.php">Daftar Mapel</a></li>
                                <li><a class="dropdown-item" href="form_mapel.php">Tambah Mapel</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="text-dark text-decoration-none dropdown-toggle fw-medium px-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Mengajar</a>
                            <ul class="dropdown-menu shadow-sm border-0">
                                <li><a class="dropdown-item" href="mengajar.php">Data Mengajar</a></li>
                                <li><a class="dropdown-item" href="form_mengajar.php">Tambah Mengajar</a></li>
                            </ul>
                        </li>

                    <!-- ================= 2. MENU UNTUK GURU ================= -->
                    <?php elseif ($is_logged_in && $role_user === 'guru'): ?>
                        
                        <li><a class="text-dark text-decoration-none fw-medium px-2 py-1" href="tambah_nilai_siswa.php">Nilai Siswa</a></li>

                    <!-- ================= 3. MENU UNTUK SISWA ================= -->
                    <?php elseif ($is_logged_in && ($role_user === 'siswa' || $role_user === 'user' || empty($role_user))): ?>
                        
                        <li><a class="text-dark text-decoration-none fw-medium px-2 py-1" href="nilai_siswa.php">Nilai Siswa</a></li>

                    <!-- ================= 4. MENU BELUM LOGIN (TAMU) ================= -->
                    <?php else: ?>
                        <li><a class="text-secondary text-decoration-none fw-medium px-2 py-1" href="login_user.php">Login</a></li>
                        <li><a class="text-secondary text-decoration-none fw-medium px-2 py-1" href="tabel.php">Siswa</a></li>
                        <li><a class="text-secondary text-decoration-none fw-medium px-2 py-1" href="mapel.php">Mapel</a></li>
                    <?php endif; ?>

                </ul>
            </nav>
        </div>

        <!-- Bagian Kanan: Search & Profil -->
        <div class="d-flex align-items-center gap-3">
            
            <!-- Form Pencarian -->
            <form class="d-none d-lg-flex align-items-center gap-2" action="search.php" method="GET">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search" style="width: 180px;">
                <button type="submit" class="btn btn-outline-success btn-sm text-nowrap">Search</button>
            </form>

            <?php if ($is_logged_in): ?>
                <!-- Dropdown Profil -->
                <div class="dropdown position-relative">
                    <button class="btn btn-light d-flex align-items-center gap-2 border-0 px-3 py-2 rounded-pill shadow-sm" type="button" id="btnProfilToggle" style="background-color: #f3f4f6; cursor: pointer;">
                        <img src="<?= $avatar_url; ?>" alt="User Avatar" class="rounded-circle" width="32" height="32" style="pointer-events: none;">
                        <span class="d-none d-sm-inline fw-medium text-dark text-nowrap" style="font-size: 0.875rem; pointer-events: none;">
                            <?= htmlspecialchars($nama_user); ?> (<?= htmlspecialchars($role_user ?: 'siswa'); ?>)
                        </span>
                        <i class="bi bi-chevron-down text-secondary" style="font-size: 0.75rem; pointer-events: none;"></i>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" id="profilMenuDropdown" style="position: absolute; right: 0; left: auto;">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-dark" style="font-size: 0.875rem;"><?= htmlspecialchars($nama_user); ?></div>
                            <small class="text-muted text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($role_user ?: 'siswa'); ?></small>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 mt-1" href="PROFIL.php">
                                <i class="bi bi-person text-secondary fs-5"></i>
                                <span>Profil</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="pengaturan.php">
                                <i class="bi bi-gear text-secondary fs-5"></i>
                                <span>Pengaturan</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right fs-5"></i>
                                <span>Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login_user.php" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 rounded-pill text-nowrap fw-medium" style="font-size: 0.875rem;">
                    <i class="bi bi-box-arrow-in-right fs-6"></i>
                    <span>Login</span>
                </a>
            <?php endif; ?>

        </div>
    </div>
</header>