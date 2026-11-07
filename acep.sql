-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2026 at 03:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `acep`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `jenis_kelamin`, `user_id`) VALUES
(1, '198501012010011001', 'Budi Santoso, M.Kom', 'L', 2),
(2, '198803152012012002', 'Siti Rahma, S.Pd', 'P', 3),
(3, '199005202015011003', 'Ahmad Rizki, S.T', 'L', 4),
(4, '199208102018012004', 'Dewi Lestari, M.Pd', 'P', 5),
(5, '19951201202001100599', 'Eko Prasetyo, S.SSSSSSS', 'P', 6),
(6, '1212', 'bu sinta', 'P', NULL),
(11, '13131313', 'acep guru', 'L', 62);

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int(11) NOT NULL,
  `kode_mapel` varchar(10) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `guru_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `kode_mapel`, `nama_mapel`, `guru_id`) VALUES
(1, 'MP001', 'Pemrograman Web dan Perangkat Bergerak', 1),
(2, 'MP002', 'Pemodelan Perangkat Lunak', 2),
(3, 'MP003', 'Basis Data', 3),
(4, 'MP004', 'Pemrograman Berorientasi Objek', 4),
(5, 'MP005', 'PKKWU', 5),
(7, 'MP008', 'Mobile Legend', 2),
(10, 'mp89', 'epep', 11),
(11, 'mtk', 'mcgg', 11);

-- --------------------------------------------------------

--
-- Table structure for table `mengajar`
--

CREATE TABLE `mengajar` (
  `id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mengajar`
--

INSERT INTO `mengajar` (`id`, `mapel_id`, `siswa_id`) VALUES
(1, 3, 3),
(2, 3, 4),
(3, 3, 21);

-- --------------------------------------------------------

--
-- Table structure for table `nilai_rapor`
--

CREATE TABLE `nilai_rapor` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `nilai_kkm` int(11) DEFAULT 75,
  `nilai_pengetahuan` int(11) DEFAULT NULL,
  `nilai_keterampilan` int(11) DEFAULT NULL,
  `Keterangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai_rapor`
--

INSERT INTO `nilai_rapor` (`id`, `siswa_id`, `mapel_id`, `nilai_kkm`, `nilai_pengetahuan`, `nilai_keterampilan`, `Keterangan`) VALUES
(1, 1, 1, 75, 54, 99, 'Tidak Tuntas'),
(2, 2, 2, 75, 78, 80, 'Tuntas'),
(4, 4, 4, 75, 70, 75, 'Tidak Tuntas'),
(7, 7, 3, 75, 56, 77, 'Tidak Tuntas'),
(8, 6, 3, 75, 90, 89, 'Tuntas'),
(9, 6, 3, 75, 90, 89, 'Tuntas'),
(10, 23, 2, 75, 76, 85, 'Tuntas'),
(11, 2, 1, 75, 89, 77, 'Tuntas'),
(12, 3, 1, 75, 89, 90, 'Tuntas'),
(13, 21, 1, 75, 7, 9, 'Tidak Tuntas'),
(14, 21, 1, 75, 7, 9, 'Tidak Tuntas'),
(15, 21, 3, 75, 55, 11, 'Tidak Tuntas'),
(16, 4, 3, 75, 54, 64, 'Tidak Tuntas'),
(19, 27, 3, 75, 88, 99, 'Tuntas');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `kelas`, `jenis_kelamin`, `user_id`) VALUES
(1, '2223001', 'Andi Wijaya', 'XII RPL 1', 'L', 7),
(2, '2223002', 'Bunga Citra', 'XII RPL 1', 'P', 8),
(3, '2223003', 'Candra Gunawan', 'XII RPL 2', 'L', 9),
(4, '2223004', 'Dina Maya', 'XII RPL 2', 'P', 10),
(6, '4544', 'acep12', 'xi rpl1', 'L', NULL),
(7, '13131313', 'PrabowoJoko', 'xi rpl1', 'L', NULL),
(15, '54545', 'qsssss', 'xi rpl1', 'P', 34),
(16, '55666', 'axeppppp', 'xi rpl1', 'L', NULL),
(21, '11113333', 'sergioo aja', 'xi rpl 4', 'P', 48),
(22, '111111', 'acep guntur permana', 'xi rpl 1', 'L', 59),
(23, '1111111111', 'acep guntur', 'xi rpl 1', 'L', 60),
(24, '1313131', 'rehan', 'XI RPL 2', 'L', 63),
(25, '2323', 'aesar', 'xi rpl 3', 'L', 64),
(26, '141414', 'nasir', 'xi rpl 4', 'L', 65),
(27, '63434', 'salwa', 'xi rpl 4', 'P', 66);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin013', 'hash_pass_1', 'admin', '2026-08-23 03:07:35'),
(2, 'guru_budi', '$2y$10$hashpassguru1', 'guru', '2026-08-23 03:12:14'),
(3, 'guru_siti', '$2y$10$hashpassguru2', 'guru', '2026-08-23 03:12:14'),
(4, 'guru_ahmad', '$2y$10$hashpassguru3', 'guru', '2026-08-23 03:12:14'),
(5, 'guru_dewi', '$2y$10$hashpassguru4', 'guru', '2026-08-23 03:12:14'),
(6, 'guru_eko', '$2y$10$hashpassguru5', 'guru', '2026-08-23 03:12:14'),
(7, 'siswa_andi', '$2y$10$hashpasssiswa1', 'siswa', '2026-08-23 03:12:14'),
(8, 'siswa_bunga', '$2y$10$hashpasssiswa2', 'siswa', '2026-08-23 03:12:14'),
(9, 'siswa_candra', '$2y$10$hashpasssiswa3', 'siswa', '2026-08-23 03:12:14'),
(10, 'siswa_dina', '$2y$10$hashpasssiswa4', 'siswa', '2026-08-23 03:12:14'),
(34, 'qsssyawww', '$2y$10$qCqumqxly9Fa7zQ5rWJEMeDDQkEJprT7IMm/uVRXlqsEwVQpuI.4W', 'siswa', '2026-09-07 03:40:33'),
(48, 'sergio', '$2y$10$qAxSxxAejlMOJvqERC23l.9Kl21YTp.SROq0gUMjQZnygIXk2x7ka', 'siswa', '2026-09-14 06:04:18'),
(54, 'ahyar12', '$2y$10$lIJkuyOOWZqb3wWLqrfxN./O6ea1t5C5DsTA17BMGBqMyJbRm1B1G', 'admin', '2026-09-29 01:25:16'),
(58, 'acep1', '$2y$10$8LnTmqNn31sJIuM5f425req5NBXDOJ1zQ7K7bnKMWmlQBwpfFXeaK', 'admin', '2026-09-30 06:19:23'),
(59, 'acep12', '$2y$10$Za2OtxH6RgNdoBoWhHZe/utFul4/KTRRfmm7wzx4WGTjsUQFXJYrO', 'siswa', '2026-09-30 06:34:09'),
(60, 'cep00', '$2y$10$1hJsRrluyY9vOZfTUUJzZ.zs.oKCafZ5nkhVR6iKXg0X6bZvDo3aK', 'siswa', '2026-09-30 06:41:26'),
(61, 'acep aja', '$2y$10$Ev483TNF0Wi8mkBX/Ed2ReaYC8OhMaXVxvp65JGXWfBHvhGUgOZn2', 'admin', '2026-10-05 05:30:36'),
(62, 'acep guru', '$2y$10$LrU5raR/nSoqlf6CE1AhXOpgPQuDbV6sSziLxN6lB4ZQ1vW0cfEw.', 'guru', '2026-10-05 05:50:37'),
(63, 'rehan', '$2y$10$v9e4KDUz6.vZEmR71qvcWe1QJlw7.emxmTZtRehgJu7Gwmdi2flZG', 'siswa', '2026-10-13 01:44:21'),
(64, 'aesar', '$2y$10$VKTzvFMAImKt3SHk.VuvSeIcRTkafkV9CKJGwD7a1mIFvlqMamhFW', 'siswa', '2026-10-14 15:10:50'),
(65, 'nasir', '$2y$10$Kqif5O/uy4Ww2AG.fmeqEe1NWm8e2gb6drjW3K7Z8lQGwrFAziihC', 'siswa', '2026-10-19 04:37:51'),
(66, 'salwa', '$2y$10$RUygERSnKl2Uyg.x1yaLQOSiZkkuWNYf9hTgSTAOQjZMzzW6u68lW', 'siswa', '2026-10-26 04:30:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mapel` (`kode_mapel`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `mengajar`
--
ALTER TABLE `mengajar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mapel_id` (`mapel_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `mapel_id` (`mapel_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `mengajar`
--
ALTER TABLE `mengajar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `mapel_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mengajar`
--
ALTER TABLE `mengajar`
  ADD CONSTRAINT `mengajar_ibfk_1` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`),
  ADD CONSTRAINT `mengajar_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  ADD CONSTRAINT `nilai_rapor_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_rapor_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
