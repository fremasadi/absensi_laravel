-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 12, 2025 at 07:41 PM
-- Server version: 10.11.11-MariaDB-cll-lve
-- PHP Version: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `frek8627_db_tokokita`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_jadwal` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_absen` date DEFAULT NULL,
  `waktu_masuk_time` time DEFAULT NULL,
  `waktu_keluar_time` time DEFAULT NULL,
  `durasi_terlambat` int(11) DEFAULT NULL COMMENT 'Durasi terlambat dalam menit',
  `status_kehadiran` varchar(255) DEFAULT NULL COMMENT 'Status hadir seperti Hadir, Izin, dll.',
  `keterangan` varchar(255) DEFAULT NULL,
  `selfiemasuk` varchar(255) DEFAULT NULL,
  `selfiekeluar` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `durasi_hadir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `id_user`, `id_jadwal`, `tanggal_absen`, `waktu_masuk_time`, `waktu_keluar_time`, `durasi_terlambat`, `status_kehadiran`, `keterangan`, `selfiemasuk`, `selfiekeluar`, `barcode`, `created_at`, `updated_at`, `durasi_hadir`) VALUES
(1, 2, 1, '2025-06-06', '08:11:04', '12:14:13', NULL, 'hadir', 'hadir', 'selfies/selfie_2_20250606_081104.jpg', 'selfies/selfie_2_20250606_121413.jpg', NULL, '2025-06-06 01:11:04', '2025-06-06 05:14:13', 243),
(2, 3, 2, '2025-06-06', '08:11:24', '12:14:27', NULL, 'hadir', 'hadir', 'selfies/selfie_3_20250606_081124.jpg', 'selfies/selfie_3_20250606_121427.jpg', NULL, '2025-06-06 01:11:24', '2025-06-06 05:14:27', 243),
(3, 4, 3, '2025-06-06', '13:06:17', '13:06:50', NULL, 'hadir', 'hadir', 'selfies/selfie_4_20250606_130617.jpg', 'selfies/selfie_4_20250606_130650.jpg', NULL, '2025-06-06 06:06:17', '2025-06-06 06:06:50', 1),
(4, 5, 4, '2025-06-06', '13:07:05', '17:50:44', NULL, 'hadir', 'hadir', 'selfies/selfie_5_20250606_130705.jpg', 'selfies/selfie_5_20250606_175044.jpg', NULL, '2025-06-06 06:07:05', '2025-06-06 10:50:44', 284),
(5, 6, 6, '2025-06-06', '18:00:59', '22:26:24', NULL, 'hadir', 'hadir', 'selfies/selfie_6_20250606_180059.jpg', 'selfies/selfie_6_20250606_222624.jpg', NULL, '2025-06-06 11:00:59', '2025-06-06 15:26:24', 265),
(6, 7, 5, '2025-06-06', '18:01:19', '22:26:51', NULL, 'hadir', 'hadir', 'selfies/selfie_7_20250606_180119.jpg', 'selfies/selfie_7_20250606_222651.jpg', NULL, '2025-06-06 11:01:19', '2025-06-06 15:26:51', 266),
(7, 2, 1, '2025-06-07', NULL, NULL, NULL, 'Sakit', 'Demam', NULL, NULL, NULL, '2025-06-06 17:00:04', '2025-06-06 17:00:04', 0),
(8, 3, 2, '2025-06-07', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-07 06:00:04', '2025-06-07 06:00:04', 0),
(9, 4, 3, '2025-06-07', '13:00:50', '17:53:24', NULL, 'hadir', 'hadir', 'selfies/selfie_4_20250607_130050.jpg', 'selfies/selfie_4_20250607_175324.jpg', NULL, '2025-06-07 06:00:50', '2025-06-07 10:53:24', 293),
(10, 5, 4, '2025-06-07', '13:01:09', '17:53:42', NULL, 'hadir', 'hadir', 'selfies/selfie_5_20250607_130109.jpg', 'selfies/selfie_5_20250607_175342.jpg', NULL, '2025-06-07 06:01:09', '2025-06-07 10:53:42', 293),
(11, 7, 5, '2025-06-07', '18:01:51', '22:38:22', NULL, 'hadir', 'hadir', 'selfies/selfie_7_20250607_180151.jpg', 'selfies/selfie_7_20250607_223822.jpg', NULL, '2025-06-07 11:01:51', '2025-06-07 15:38:22', 277),
(12, 6, 6, '2025-06-07', '18:02:10', '22:38:41', NULL, 'hadir', 'hadir', 'selfies/selfie_6_20250607_180210.jpg', 'selfies/selfie_6_20250607_223841.jpg', NULL, '2025-06-07 11:02:10', '2025-06-07 15:38:41', 277),
(13, 2, 1, '2025-06-08', NULL, NULL, NULL, 'Sakit', 'Demam', NULL, NULL, NULL, '2025-06-07 17:00:05', '2025-06-07 17:00:05', 0),
(14, 3, 2, '2025-06-08', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-08 06:00:04', '2025-06-08 06:00:04', 0),
(15, 4, 3, '2025-06-08', '13:01:44', '17:08:16', NULL, 'hadir', 'hadir', 'selfies/selfie_4_20250608_130144.jpg', 'selfies/selfie_4_20250608_170816.jpg', NULL, '2025-06-08 06:01:44', '2025-06-08 10:08:16', 247),
(16, 5, 4, '2025-06-08', '13:02:20', '17:08:34', NULL, 'hadir', 'hadir', 'selfies/selfie_5_20250608_130220.jpg', 'selfies/selfie_5_20250608_170834.jpg', NULL, '2025-06-08 06:02:20', '2025-06-08 10:08:34', 246),
(17, 7, 5, '2025-06-08', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-08 16:00:05', '2025-06-08 16:00:05', 0),
(18, 6, 6, '2025-06-08', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-08 16:00:05', '2025-06-08 16:00:05', 0),
(19, 2, 1, '2025-06-09', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-09 06:00:05', '2025-06-09 06:00:05', 0),
(20, 3, 2, '2025-06-09', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-09 06:00:05', '2025-06-09 06:00:05', 0),
(21, 4, 3, '2025-06-09', '13:10:41', NULL, NULL, 'hadir', 'tidak absen keluar', 'selfies/selfie_4_20250609_131041.jpg', NULL, NULL, '2025-06-09 06:10:41', '2025-06-09 11:00:04', NULL),
(22, 5, 4, '2025-06-09', '13:11:00', NULL, NULL, 'hadir', 'tidak absen keluar', 'selfies/selfie_5_20250609_131100.jpg', NULL, NULL, '2025-06-09 06:11:00', '2025-06-09 11:00:04', NULL),
(23, 7, 5, '2025-06-09', NULL, NULL, NULL, 'Sakit', 'Tipes', NULL, NULL, NULL, '2025-06-09 06:15:04', '2025-06-09 06:15:04', 0),
(24, 6, 6, '2025-06-09', NULL, NULL, NULL, 'Cuti', 'Keluar Kota', NULL, NULL, NULL, '2025-06-09 06:20:04', '2025-06-09 06:20:04', 0),
(25, 6, 6, '2025-06-10', NULL, NULL, NULL, 'Cuti', 'Keluar Kota', NULL, NULL, NULL, '2025-06-09 17:00:05', '2025-06-09 17:00:05', 0),
(26, 2, 1, '2025-06-10', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-10 06:00:04', '2025-06-10 06:00:04', 0),
(27, 3, 2, '2025-06-10', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-10 06:00:04', '2025-06-10 06:00:04', 0),
(28, 4, 3, '2025-06-10', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-10 11:00:05', '2025-06-10 11:00:05', 0),
(29, 5, 4, '2025-06-10', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-10 11:00:05', '2025-06-10 11:00:05', 0),
(30, 7, 5, '2025-06-10', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-10 16:00:05', '2025-06-10 16:00:05', 0),
(31, 2, 1, '2025-06-11', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-11 06:00:05', '2025-06-11 06:00:05', 0),
(32, 3, 2, '2025-06-11', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-11 06:00:05', '2025-06-11 06:00:05', 0),
(33, 4, 3, '2025-06-11', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-11 11:00:04', '2025-06-11 11:00:04', 0),
(34, 5, 4, '2025-06-11', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-11 11:00:04', '2025-06-11 11:00:04', 0),
(35, 7, 5, '2025-06-11', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-11 16:00:05', '2025-06-11 16:00:05', 0),
(36, 6, 6, '2025-06-11', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-11 16:00:05', '2025-06-11 16:00:05', 0),
(37, 2, 1, '2025-06-12', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-12 06:00:04', '2025-06-12 06:00:04', 0),
(38, 3, 2, '2025-06-12', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-12 06:00:04', '2025-06-12 06:00:04', 0),
(39, 4, 3, '2025-06-12', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-12 11:00:04', '2025-06-12 11:00:04', 0),
(40, 5, 4, '2025-06-12', NULL, NULL, NULL, 'tidak hadir', 'tanpa keterangan', NULL, NULL, NULL, '2025-06-12 11:00:04', '2025-06-12 11:00:04', 0),
(41, 6, 6, '2025-06-12', '18:01:34', NULL, NULL, 'hadir', 'hadir', 'selfies/selfie_6_20250612_180134.jpg', NULL, NULL, '2025-06-12 11:01:34', '2025-06-12 11:01:34', 0),
(42, 7, 5, '2025-06-12', '18:02:32', NULL, NULL, 'hadir', 'hadir', 'selfies/selfie_7_20250612_180232.jpg', NULL, NULL, '2025-06-12 11:02:32', '2025-06-12 11:02:32', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('tokokita_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1749207184),
('tokokita_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1749207184;', 1749207184),
('tokokita_cache_livewire-rate-limiter:07527bddec42c41326753645718032d6c7841890', 'i:1;', 1749566582),
('tokokita_cache_livewire-rate-limiter:07527bddec42c41326753645718032d6c7841890:timer', 'i:1749566582;', 1749566582),
('tokokita_cache_livewire-rate-limiter:7737d126ce6f64560c10077f2b7025f3db243a75', 'i:1;', 1749449729),
('tokokita_cache_livewire-rate-limiter:7737d126ce6f64560c10077f2b7025f3db243a75:timer', 'i:1749449729;', 1749449729),
('tokokita_cache_livewire-rate-limiter:9f28f2177c3f4a621b25fb44d3bb3d888ed3779f', 'i:1;', 1749528642),
('tokokita_cache_livewire-rate-limiter:9f28f2177c3f4a621b25fb44d3bb3d888ed3779f:timer', 'i:1749528642;', 1749528642),
('tokokita_cache_livewire-rate-limiter:d386a9485cae65a2f4296609ad483e19f2c2515a', 'i:1;', 1749728145),
('tokokita_cache_livewire-rate-limiter:d386a9485cae65a2f4296609ad483e19f2c2515a:timer', 'i:1749728145;', 1749728145),
('tokokita_cache_livewire-rate-limiter:f6d916cb4d34fe73c2f888f28a8acb5c2edb769a', 'i:1;', 1749223611),
('tokokita_cache_livewire-rate-limiter:f6d916cb4d34fe73c2f888f28a8acb5c2edb769a:timer', 'i:1749223611;', 1749223611);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gajis`
--

CREATE TABLE `gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `setting_gaji_id` bigint(20) UNSIGNED NOT NULL,
  `periode_awal` date NOT NULL,
  `periode_akhir` date NOT NULL,
  `total_jam_kerja` int(11) NOT NULL DEFAULT 0,
  `total_gaji` decimal(15,2) NOT NULL,
  `status_pembayaran` varchar(255) NOT NULL DEFAULT 'belum_dibayar',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gajis`
--

INSERT INTO `gajis` (`id`, `user_id`, `setting_gaji_id`, `periode_awal`, `periode_akhir`, `total_jam_kerja`, `total_gaji`, `status_pembayaran`, `catatan`, `created_at`, `updated_at`) VALUES
(2, 2, 1, '2025-06-05', '2025-06-18', 4, 40500.00, 'belum_dibayar', NULL, '2025-06-05 09:30:05', '2025-06-06 05:14:13'),
(3, 3, 1, '2025-06-05', '2025-06-18', 4, 40500.00, 'belum_dibayar', NULL, '2025-06-05 11:00:04', '2025-06-06 05:14:27'),
(4, 4, 1, '2025-06-05', '2025-06-18', 9, 90200.00, 'belum_dibayar', NULL, '2025-06-05 11:00:04', '2025-06-08 10:10:04'),
(5, 5, 1, '2025-06-05', '2025-06-18', 14, 137200.00, 'belum_dibayar', NULL, '2025-06-05 11:00:04', '2025-06-12 12:40:05'),
(6, 6, 1, '2025-06-05', '2025-06-18', 9, 90300.00, 'belum_dibayar', NULL, '2025-06-05 11:00:04', '2025-06-07 15:40:04'),
(7, 7, 1, '2025-06-05', '2025-06-18', 9, 90500.00, 'belum_dibayar', NULL, '2025-06-05 11:05:04', '2025-06-07 15:38:22');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_shifts`
--

CREATE TABLE `jadwal_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_shift` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal_shifts`
--

INSERT INTO `jadwal_shifts` (`id`, `id_user`, `id_shift`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2025-06-05 11:06:02', '2025-06-05 11:06:44'),
(2, 3, 1, 1, '2025-06-05 11:07:50', '2025-06-05 11:08:04'),
(3, 4, 2, 1, '2025-06-05 11:09:50', '2025-06-05 11:09:55'),
(4, 5, 2, 1, '2025-06-05 16:19:55', '2025-06-05 16:19:55'),
(5, 7, 3, 1, '2025-06-05 16:20:16', '2025-06-05 16:21:39'),
(6, 6, 3, 1, '2025-06-05 16:20:29', '2025-06-05 16:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_16_170123_add_details_to_users_table', 1),
(5, '2025_01_16_183810_create_shifts_table', 1),
(6, '2025_01_16_185800_create_jadwal_shifts_table', 1),
(7, '2025_01_18_084839_create_absensi_table', 1),
(8, '2025_02_01_030401_add_barcode_to_absensis_table', 1),
(9, '2025_02_03_223630_create_setting_gajis_table', 1),
(10, '2025_02_03_230048_create_gajis_table', 1),
(11, '2025_02_13_224627_create_personal_access_tokens_table', 1),
(12, '2025_02_14_182623_create_permintaan_izins_table', 1),
(13, '2025_03_01_183851_remove_gaji_per_jam_from_gajis_table', 1),
(14, '2025_03_22_011953_add_imageselfie_to_absensi_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_izins`
--

CREATE TABLE `permintaan_izins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jenis_izin` varchar(255) NOT NULL,
  `alasan` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permintaan_izins`
--

INSERT INTO `permintaan_izins` (`id`, `user_id`, `tanggal_mulai`, `tanggal_selesai`, `jenis_izin`, `alasan`, `image`, `status`, `created_at`, `updated_at`) VALUES
(4, 7, '2025-06-09', '2025-06-09', 'Sakit', 'Tipes', '1749449589.jpg', 1, '2025-06-09 06:13:09', '2025-06-09 06:13:22'),
(5, 6, '2025-06-09', '2025-06-10', 'Cuti', 'Keluar Kota', '1749449728.png', 1, '2025-06-09 06:15:28', '2025-06-09 06:16:00'),
(6, 2, '2025-06-10', '2025-06-27', 'Sakit', 'sakit', NULL, 0, '2025-06-10 10:08:21', '2025-06-10 10:08:21');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `setting_gajis`
--

CREATE TABLE `setting_gajis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `gaji_per_jam` decimal(10,2) NOT NULL DEFAULT 10000.00,
  `periode_gaji` int(11) NOT NULL DEFAULT 14,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `setting_gajis`
--

INSERT INTO `setting_gajis` (`id`, `name`, `gaji_per_jam`, `periode_gaji`, `created_at`, `updated_at`) VALUES
(1, 'Setting Gaji', 10000.00, 14, '2025-06-05 09:24:31', '2025-06-05 09:24:31');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 'Shift Pagi', '08:00:00', '13:00:00', '2025-06-05 11:03:22', '2025-06-05 11:03:22'),
(2, 'Shift Siang', '13:00:00', '18:00:00', '2025-06-05 11:04:02', '2025-06-05 11:04:02'),
(3, 'Shift Malam', '18:00:00', '23:00:00', '2025-06-05 11:04:31', '2025-06-05 11:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `image` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `image`, `phone`, `address`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin', '01JWZVKGJ2GAAQC8Q38P6WNRFT.jpg', '12345678', 'Malang', NULL, '$2y$12$gURWeGV1zpzBNvHITNaUoOUFxBreOK/SBRRQv1R6eUTCG.GRVePtC', NULL, '2025-06-05 09:21:45', '2025-06-05 10:53:48'),
(2, 'Nadia', 'nadia@gmail.com', 'user', '01JWZVNFYFXZXZFGP5KKDCGAFN.jpg', '085707533439', 'Lamongan', NULL, '$2y$12$DUq2c8eocSbJw9hIEYIVvOxk1juaehRulfxOTeBEtBiKZ59CmLW6y', NULL, '2025-06-05 09:26:23', '2025-06-05 10:54:53'),
(3, 'Viola', 'viola@gmail.com', 'user', '01JWZVQK7713ZBY6QVDC8PMNR7.jpg', '089323879211', 'Kediri', NULL, '$2y$12$OjtZt9zISUqis4qEMp6.jehrUeh4AGQSTihDzJTFLTgDBS7QsHMC2', NULL, '2025-06-05 10:56:02', '2025-06-05 10:56:02'),
(4, 'Pipit', 'pipit@gmail.com', 'user', '01JWZVS5D863ET1N7HNK5NQFZP.jpg', '081563768762', 'Blitar', NULL, '$2y$12$334nPoA0/pbV.oCO0C9JXenhZlbP8h64gTZ9ue2hPJGy8HIZWwvOW', NULL, '2025-06-05 10:56:53', '2025-06-05 10:56:53'),
(5, 'Valensia', 'valen@gmail.com', 'user', '01JWZVV1F92KNRHMCH6FP24RX7.jpg', '081768322980', 'Mojo', NULL, '$2y$12$eNIUSXxrRGHt7tUIpZpr..I1I90dqvuC7kHnMcuF2O0vMfZ8T2.1.', NULL, '2025-06-05 10:57:54', '2025-06-05 10:57:54'),
(6, 'Mutiara', 'muti@gmail.com', 'user', '01JWZVXFZRS8NT4R835X8YYD6F.jpg', '085678654344', 'Dhoho', NULL, '$2y$12$z9xRqmqEPfUIs9DlKENuI.g/e8FQpSufYsSTPSqnDWkN/R.D3e1tO', NULL, '2025-06-05 10:59:15', '2025-06-05 10:59:15'),
(7, 'Eka', 'eka@gmail.com', 'user', '01JWZW0QFP021TBTH67NB519QX.png', '089767343234', 'Pojok', NULL, '$2y$12$ozg2D9sBpJyp/Tm.vRxdcO0gU94SLwkEwsg4Za.H06rdJo8KQU8Ke', NULL, '2025-06-05 11:01:01', '2025-06-05 11:01:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `absensi_id_user_foreign` (`id_user`),
  ADD KEY `absensi_id_jadwal_foreign` (`id_jadwal`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `gajis`
--
ALTER TABLE `gajis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gajis_user_id_foreign` (`user_id`),
  ADD KEY `gajis_setting_gaji_id_foreign` (`setting_gaji_id`);

--
-- Indexes for table `jadwal_shifts`
--
ALTER TABLE `jadwal_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jadwal_shifts_id_user_foreign` (`id_user`),
  ADD KEY `jadwal_shifts_id_shift_foreign` (`id_shift`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permintaan_izins`
--
ALTER TABLE `permintaan_izins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permintaan_izins_user_id_foreign` (`user_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `setting_gajis`
--
ALTER TABLE `setting_gajis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gajis`
--
ALTER TABLE `gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jadwal_shifts`
--
ALTER TABLE `jadwal_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `permintaan_izins`
--
ALTER TABLE `permintaan_izins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `setting_gajis`
--
ALTER TABLE `setting_gajis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_id_jadwal_foreign` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_shifts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `absensi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gajis`
--
ALTER TABLE `gajis`
  ADD CONSTRAINT `gajis_setting_gaji_id_foreign` FOREIGN KEY (`setting_gaji_id`) REFERENCES `setting_gajis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gajis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_shifts`
--
ALTER TABLE `jadwal_shifts`
  ADD CONSTRAINT `jadwal_shifts_id_shift_foreign` FOREIGN KEY (`id_shift`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_shifts_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permintaan_izins`
--
ALTER TABLE `permintaan_izins`
  ADD CONSTRAINT `permintaan_izins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
