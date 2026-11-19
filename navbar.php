<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role_user = strtolower($_SESSION['role'] ?? 'siswa');
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div>
        <!-- Logo eSchool -->
        <div class="sidebar-brand">
            <i class="bi bi-grid-fill me-2"></i> eSchool
        </div>
        
        <ul class="sidebar-menu">
            <?php if ($role_user === 'admin'): ?>
                <!-- ================= HAK AKSES: ADMIN ================= -->
                
                <!-- Menu User -->
                <li>
                    <a href="#userSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['data_user.php', 'tabelusers.php', 'tambah_user1.php', 'tambah_user2.php', 'tambah_user3.php']) ? 'true' : 'false'; ?>" class="d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-people"></i> <span>User</span></div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <ul class="collapse sidebar-submenu <?= in_array($current_page, ['data_user.php', 'tabelusers.php', 'tambah_user1.php', 'tambah_user2.php', 'tambah_user3.php']) ? 'show' : ''; ?>" id="userSubmenu">
                        <li><a href="tabelusers.php" class="<?= in_array($current_page, ['data_user.php', 'tabelusers.php']) ? 'active' : ''; ?>">Daftar User</a></li>
                        <li><a href="tambah_user1.php" class="<?= $current_page == 'tambah_user1.php' ? 'active' : ''; ?>">Tambahkan user1</a></li>
                        <li><a href="tambah_user2.php" class="<?= $current_page == 'tambah_user2.php' ? 'active' : ''; ?>">Tambahkan user2</a></li>
                        <li><a href="tambah_user3.php" class="<?= $current_page == 'tambah_user3.php' ? 'active' : ''; ?>">Tambahkan user3</a></li>
                    </ul>
                </li>

                <!-- Menu Siswa -->
                <li>
                    <a href="#siswaSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['data_siswa.php', 'tambah_siswa.php']) ? 'true' : 'false'; ?>" class="d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-mortarboard"></i> <span>Siswa</span></div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <ul class="collapse sidebar-submenu <?= in_array($current_page, ['data_siswa.php', 'tambah_siswa.php']) ? 'show' : ''; ?>" id="siswaSubmenu">
                        <li><a href="data_siswa.php" class="<?= $current_page == 'data_siswa.php' ? 'active' : ''; ?>">Daftar Siswa</a></li>
                        <li><a href="tambah_siswa.php" class="<?= $current_page == 'tambah_siswa.php' ? 'active' : ''; ?>">Tambah Siswa</a></li>
                    </ul>
                </li>

                <!-- Menu Guru -->
                <li>
                    <a href="#guruSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['data_guru.php', 'tambah_nilai.php']) ? 'true' : 'false'; ?>" class="d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-person-video3"></i> <span>Guru</span></div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <ul class="collapse sidebar-submenu <?= in_array($current_page, ['data_guru.php', 'tambah_nilai.php']) ? 'show' : ''; ?>" id="guruSubmenu">
                        <li><a href="data_guru.php" class="<?= $current_page == 'data_guru.php' ? 'active' : ''; ?>">Daftar Guru</a></li>
                        <li><a href="tambah_nilai.php" class="<?= $current_page == 'tambah_nilai.php' ? 'active' : ''; ?>">Tambah Nilai</a></li>
                    </ul>
                </li>

                <!-- Menu Mapel -->
                <li>
                    <a href="#mapelSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['data_mapel.php', 'tambah_mapel.php']) ? 'true' : 'false'; ?>" class="d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-journal-bookmark"></i> <span>Mapel</span></div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <ul class="collapse sidebar-submenu <?= in_array($current_page, ['data_mapel.php', 'tambah_mapel.php']) ? 'show' : ''; ?>" id="mapelSubmenu">
                        <li><a href="data_mapel.php" class="<?= $current_page == 'data_mapel.php' ? 'active' : ''; ?>">Daftar Mapel</a></li>
                        <li><a href="tambah_mapel.php" class="<?= $current_page == 'tambah_mapel.php' ? 'active' : ''; ?>">Tambah Mapel</a></li>
                    </ul>
                </li>

                <!-- Menu Mengajar -->
                <li>
                    <a href="#mengajarSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['data_mengajar.php', 'tambah_mengajar.php']) ? 'true' : 'false'; ?>" class="d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-briefcase"></i> <span>Mengajar</span></div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <ul class="collapse sidebar-submenu <?= in_array($current_page, ['data_mengajar.php', 'tambah_mengajar.php']) ? 'show' : ''; ?>" id="mengajarSubmenu">
                        <li><a href="data_mengajar.php" class="<?= $current_page == 'data_mengajar.php' ? 'active' : ''; ?>">Data Mengajar</a></li>
                        <li><a href="tambah_mengajar.php" class="<?= $current_page == 'tambah_mengajar.php' ? 'active' : ''; ?>">Tambah Mengajar</a></li>
                    </ul>
                </li>

                <li class="<?= $current_page == 'pengaturan.php' ? 'active' : ''; ?>">
                    <a href="pengaturan.php"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
                </li>

            <?php elseif ($role_user === 'guru'): ?>
                <!-- ================= HAK AKSES: GURU ================= -->
                <li class="<?= $current_page == 'halaman_guru.php' ? 'active' : ''; ?>">
                    <a href="halaman_guru.php"><i class="bi bi-people"></i> <span>Daftar Guru</span></a>
                </li>
                <li class="<?= $current_page == 'tambah_nilai_siswa.php' ? 'active' : ''; ?>">
                    <a href="tambah_nilai_siswa.php"><i class="bi bi-journal-plus"></i> <span>Tambah Nilai Siswa</span></a>
                </li>
                <li class="<?= in_array($current_page, ['PROFIL.php', 'pengaturan.php']) ? 'active' : ''; ?>">
                    <a href="PROFIL.php"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
                </li>

            <?php else: ?>
                <!-- ================= HAK AKSES: SISWA ================= -->
                <li class="<?= $current_page == 'halaman_siswa.php' ? 'active' : ''; ?>">
                    <a href="halaman_siswa.php"><i class="bi bi-house-door"></i> <span>Daftar Siswa</span></a>
                </li>
                <li class="<?= $current_page == 'nilai_siswa.php' ? 'active' : ''; ?>">
                    <a href="nilai_siswa.php"><i class="bi bi-journal-text"></i> <span>Nilai Siswa</span></a>
                </li>
                <li class="<?= in_array($current_page, ['PROFIL.php', 'pengaturan.php']) ? 'active' : ''; ?>">
                    <a href="PROFIL.php"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="mb-2">
            <a href="#">Bantuan</a> <a href="#">Kontak</a>
        </div>
        <small>&copy; 2026 Sistem Sekolah</small>
    </div>
</div>