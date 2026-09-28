-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 05:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `percetakan_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_periods`
--

CREATE TABLE `accounting_periods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tahun` smallint(5) UNSIGNED NOT NULL,
  `bulan` tinyint(3) UNSIGNED NOT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `closed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounting_periods`
--

INSERT INTO `accounting_periods` (`id`, `tahun`, `bulan`, `status`, `closed_by`, `closed_at`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 2026, 9, 'Open', NULL, NULL, NULL, '2026-09-23 15:26:07', '2026-09-23 15:26:29');

-- --------------------------------------------------------

--
-- Table structure for table `approval_requests`
--

CREATE TABLE `approval_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipe_aksi` varchar(50) NOT NULL,
  `referensi_type` varchar(50) DEFAULT NULL,
  `referensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ringkasan` varchar(255) DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `alasan_pengajuan` varchar(500) DEFAULT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `decided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `catatan_keputusan` varchar(500) DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approval_requests`
--

INSERT INTO `approval_requests` (`id`, `tipe_aksi`, `referensi_type`, `referensi_id`, `payload`, `ringkasan`, `requested_by`, `alasan_pengajuan`, `status`, `decided_by`, `catatan_keputusan`, `decided_at`, `created_at`, `updated_at`) VALUES
(1, 'penyesuaian_stok_manual', 'bahan_baku', 93, '{\"id_bahan\":93,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok WALLPAPER: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 00:34:41', '2026-09-26 00:06:37', '2026-09-26 00:34:41'),
(2, 'penyesuaian_stok_manual', 'bahan_baku', 49, '{\"id_bahan\":49,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"3\",\"keterangan\":\"Recheck\"}', 'Revisi stok POWDER DTF (HOT MELT): 0 -> 3', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 00:34:38', '2026-09-26 00:07:02', '2026-09-26 00:34:38'),
(3, 'penyesuaian_stok_manual', 'bahan_baku', 138, '{\"id_bahan\":138,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"2\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR BLACK: 0 -> 2', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 03:39:53', '2026-09-26 02:10:19', '2026-09-26 03:39:53'),
(4, 'penyesuaian_stok_manual', 'bahan_baku', 137, '{\"id_bahan\":137,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR YELLOW: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 03:39:51', '2026-09-26 02:11:16', '2026-09-26 03:39:51'),
(5, 'penyesuaian_stok_manual', 'bahan_baku', 142, '{\"id_bahan\":142,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SUBLIME MAGENTA: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 03:39:50', '2026-09-26 02:11:36', '2026-09-26 03:39:50'),
(6, 'penyesuaian_stok_manual', 'bahan_baku', 143, '{\"id_bahan\":143,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SUBLIME YELLOW: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 03:39:49', '2026-09-26 02:13:01', '2026-09-26 03:39:49'),
(7, 'penyesuaian_stok_manual', 'bahan_baku', 138, '{\"id_bahan\":138,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"10\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR BLACK: 0 -> 10', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 03:39:48', '2026-09-26 02:13:32', '2026-09-26 03:39:48'),
(8, 'penyesuaian_stok_manual', 'bahan_baku', 44, '{\"id_bahan\":44,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR CYAN: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, NULL, '2026-09-26 03:39:46', '2026-09-26 02:14:11', '2026-09-26 03:39:46'),
(9, 'penyesuaian_stok_manual', 'bahan_baku', 138, '{\"id_bahan\":138,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR BLACK: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:18:54', '2026-09-26 02:18:54', '2026-09-26 02:18:54'),
(10, 'penyesuaian_stok_manual', 'bahan_baku', 95, '{\"id_bahan\":95,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok SOLVENT PEMBERSIH FLORA: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:19:06', '2026-09-26 02:19:06', '2026-09-26 02:19:06'),
(11, 'penyesuaian_stok_manual', 'bahan_baku', 44, '{\"id_bahan\":44,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR CYAN: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:19:14', '2026-09-26 02:19:14', '2026-09-26 02:19:14'),
(12, 'penyesuaian_stok_manual', 'bahan_baku', 136, '{\"id_bahan\":136,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR MAGENTA: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:19:22', '2026-09-26 02:19:22', '2026-09-26 02:19:22'),
(13, 'penyesuaian_stok_manual', 'bahan_baku', 137, '{\"id_bahan\":137,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"5\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SOLVENT FLORA OUTDOOR YELLOW: 0 -> 5', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:19:35', '2026-09-26 02:19:35', '2026-09-26 02:19:35'),
(14, 'penyesuaian_stok_manual', 'bahan_baku', 141, '{\"id_bahan\":141,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA DTF BLACK: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:20:23', '2026-09-26 02:20:23', '2026-09-26 02:20:23'),
(15, 'penyesuaian_stok_manual', 'bahan_baku', 139, '{\"id_bahan\":139,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA DTF MAGENTA: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:20:34', '2026-09-26 02:20:34', '2026-09-26 02:20:34'),
(16, 'penyesuaian_stok_manual', 'bahan_baku', 140, '{\"id_bahan\":140,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA DTF YELLOW: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:20:43', '2026-09-26 02:20:43', '2026-09-26 02:20:43'),
(17, 'penyesuaian_stok_manual', 'bahan_baku', 144, '{\"id_bahan\":144,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SUBLIME BLACK: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:20:57', '2026-09-26 02:20:57', '2026-09-26 02:20:57'),
(18, 'penyesuaian_stok_manual', 'bahan_baku', 50, '{\"id_bahan\":50,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SUBLIME CYAN: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:21:12', '2026-09-26 02:21:12', '2026-09-26 02:21:12'),
(19, 'penyesuaian_stok_manual', 'bahan_baku', 142, '{\"id_bahan\":142,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SUBLIME MAGENTA: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:21:52', '2026-09-26 02:21:52', '2026-09-26 02:21:52'),
(20, 'penyesuaian_stok_manual', 'bahan_baku', 143, '{\"id_bahan\":143,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA SUBLIME YELLOW: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:22:07', '2026-09-26 02:22:07', '2026-09-26 02:22:07'),
(21, 'penyesuaian_stok_manual', 'bahan_baku', 121, '{\"id_bahan\":121,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 BLACK: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:22:22', '2026-09-26 02:22:22', '2026-09-26 02:22:22'),
(22, 'penyesuaian_stok_manual', 'bahan_baku', 125, '{\"id_bahan\":125,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok PISAU CUTTER ROLAND G540: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:22:35', '2026-09-26 02:22:35', '2026-09-26 02:22:35'),
(23, 'penyesuaian_stok_manual', 'bahan_baku', 122, '{\"id_bahan\":122,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 LIGHT BLACK: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:22:47', '2026-09-26 02:22:47', '2026-09-26 02:22:47'),
(24, 'penyesuaian_stok_manual', 'bahan_baku', 117, '{\"id_bahan\":117,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 LIGHT CYAN: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:23:07', '2026-09-26 02:23:07', '2026-09-26 02:23:07'),
(25, 'penyesuaian_stok_manual', 'bahan_baku', 119, '{\"id_bahan\":119,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 LIGHT MAGENTA: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:23:15', '2026-09-26 02:23:15', '2026-09-26 02:23:15'),
(26, 'penyesuaian_stok_manual', 'bahan_baku', 118, '{\"id_bahan\":118,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 MAGENTA: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:23:23', '2026-09-26 02:23:23', '2026-09-26 02:23:23'),
(27, 'penyesuaian_stok_manual', 'bahan_baku', 123, '{\"id_bahan\":123,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 ORANGE: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:23:30', '2026-09-26 02:23:30', '2026-09-26 02:23:30'),
(28, 'penyesuaian_stok_manual', 'bahan_baku', 124, '{\"id_bahan\":124,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 RED: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:23:38', '2026-09-26 02:23:38', '2026-09-26 02:23:38'),
(29, 'penyesuaian_stok_manual', 'bahan_baku', 120, '{\"id_bahan\":120,\"tipe\":\"ADJUSTMENT\",\"qty\":null,\"stok_baru\":\"1\",\"keterangan\":\"Recheck\"}', 'Revisi stok TINTA ECO-SOLVENT EPSON S80670 YELLOW: 0 -> 1', 1, 'Recheck', 'Disetujui', 1, 'Disetujui otomatis (diajukan oleh Administrator)', '2026-09-26 02:23:45', '2026-09-26 02:23:45', '2026-09-26 02:23:45');

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_aplikasi` varchar(255) NOT NULL DEFAULT 'PRINT-ERP',
  `alamat` text DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `libur_kas_minggu` tinyint(1) NOT NULL DEFAULT 1,
  `format_nota` varchar(20) NOT NULL DEFAULT 'a4',
  `nomor_nota_prefix` varchar(20) NOT NULL DEFAULT 'INV',
  `nota_footer_text` text DEFAULT NULL,
  `pembulatan_nota` varchar(10) NOT NULL DEFAULT '500',
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `logo_ukuran` varchar(10) NOT NULL DEFAULT 'sedang',
  `logo_gaya` varchar(10) NOT NULL DEFAULT 'polos',
  `logo_ringkas` varchar(10) NOT NULL DEFAULT 'potong',
  `logo_ikon_path` varchar(255) DEFAULT NULL,
  `batasi_jam_kerja` tinyint(1) NOT NULL DEFAULT 0,
  `url_akses` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`id`, `nama_aplikasi`, `alamat`, `telepon`, `fax`, `email`, `website`, `libur_kas_minggu`, `format_nota`, `nomor_nota_prefix`, `nota_footer_text`, `pembulatan_nota`, `logo_path`, `created_at`, `updated_at`, `logo_ukuran`, `logo_gaya`, `logo_ringkas`, `logo_ikon_path`, `batasi_jam_kerja`, `url_akses`) VALUES
(1, 'ORION GRAFIKA', 'Jalan Jaksa Agung Suprapto No. 48 Pliyang - Madura, Sampang', '082334075550', '-', 'grafikaorion@gmail.com', 'http://oriongrafika.com', 1, 'thermal', 'ORG', 'Pembayaran ke Nomor Rekening : BCA : 896 0407 068  |  BRI : 01480 1001 350561\r\nAn. Khaironi Firdiansyah', '1000', 'company_logo/TQuRE7SCpMfI5Ryfac28qypqAk6wVD3T4phfJiXl.png', '2026-09-12 05:23:14', '2026-09-27 12:49:50', 'sedang', 'polos', 'potong', NULL, 0, 'http://192.168.1.5/percetakan-app/public');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_label` varchar(100) DEFAULT NULL,
  `event` varchar(20) NOT NULL,
  `auditable_type` varchar(100) NOT NULL,
  `auditable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `url` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_label`, `event`, `auditable_type`, `auditable_id`, `keterangan`, `old_values`, `new_values`, `url`, `ip_address`, `created_at`) VALUES
(1, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 1, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":93,\"payload\":\"{\\\"id_bahan\\\":93,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok WALLPAPER: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 07:06:37\",\"created_at\":\"2026-09-26 07:06:37\",\"id\":1}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 00:06:37'),
(2, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 2, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":49,\"payload\":\"{\\\"id_bahan\\\":49,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"3\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok POWDER DTF (HOT MELT): 0 -> 3\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 07:07:02\",\"created_at\":\"2026-09-26 07:07:02\",\"id\":2}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 00:07:02'),
(3, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 2, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T00:07:02.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 07:34:38\",\"updated_at\":\"2026-09-26 07:34:38\"}', 'http://localhost/percetakan-app/public/approval/2/approve', '127.0.0.1', '2026-09-26 00:34:38'),
(4, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 1, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T00:06:37.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 07:34:41\",\"updated_at\":\"2026-09-26 07:34:41\"}', 'http://localhost/percetakan-app/public/approval/1/approve', '127.0.0.1', '2026-09-26 00:34:41'),
(5, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 1, NULL, NULL, '{\"id_kedatangan\":\"1\",\"no_faktur\":\"JL.0068041\",\"tgl_transaksi\":\"2026-09-08\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":true,\"jatuh_tempo_hari\":null,\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-26 07:42:16\",\"created_at\":\"2026-09-26 07:42:16\",\"id_pembelian\":1}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:42:16'),
(6, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 1, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":null}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:42:16'),
(7, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 1, NULL, '{\"grand_total\":0}', '{\"grand_total\":3642509.4000000004}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:42:16'),
(8, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku JL.0068041', NULL, '{\"no_referensi\":\"JL.0068041\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":3642509.4000000004,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":3642509.4000000004}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:42:16'),
(9, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 2, NULL, NULL, '{\"id_kedatangan\":\"2\",\"no_faktur\":\"JL.0066627\",\"tgl_transaksi\":\"2026-09-26\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":true,\"jatuh_tempo_hari\":\"30\",\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-26 07:50:07\",\"created_at\":\"2026-09-26 07:50:07\",\"id_pembelian\":2}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:50:07'),
(10, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 2, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-26\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:50:07'),
(11, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 2, NULL, '{\"grand_total\":0}', '{\"grand_total\":7061020.800000001}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:50:07'),
(12, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku JL.0066627', NULL, '{\"no_referensi\":\"JL.0066627\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":7061020.800000001,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":7061020.800000001}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:50:07'),
(13, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 3, NULL, NULL, '{\"id_kedatangan\":\"3\",\"no_faktur\":\"JL.0066832\",\"tgl_transaksi\":\"2026-09-26\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":true,\"jatuh_tempo_hari\":\"30\",\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-26 07:57:48\",\"created_at\":\"2026-09-26 07:57:48\",\"id_pembelian\":3}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:57:48'),
(14, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 3, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-26\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:57:48'),
(15, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 3, NULL, '{\"grand_total\":0}', '{\"grand_total\":6831994.500000001}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:57:48'),
(16, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku JL.0066832', NULL, '{\"no_referensi\":\"JL.0066832\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":6831994.500000001,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":6831994.500000001}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 00:57:48'),
(17, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 4, NULL, NULL, '{\"id_kedatangan\":\"4\",\"no_faktur\":\"CSBSBY262767\",\"tgl_transaksi\":\"2026-09-26\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":false,\"jatuh_tempo_hari\":\"30\",\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-26 08:01:45\",\"created_at\":\"2026-09-26 08:01:45\",\"id_pembelian\":4}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:01:45'),
(18, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 4, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-26\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:01:45'),
(19, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 4, NULL, '{\"grand_total\":0}', '{\"grand_total\":10684800}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:01:45'),
(20, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku CSBSBY262767', NULL, '{\"no_referensi\":\"CSBSBY262767\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":10684800,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":10684800}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:01:45'),
(21, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 2, NULL, '{\"tgl_transaksi\":\"2026-09-26\",\"updated_at\":\"2026-09-26T00:50:07.000000Z\"}', '{\"tgl_transaksi\":\"2026-08-18\",\"updated_at\":\"2026-09-26 08:03:45\"}', 'http://localhost/percetakan-app/public/pembelian-bahan/2', '127.0.0.1', '2026-09-26 01:03:45'),
(22, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 3, NULL, '{\"tgl_transaksi\":\"2026-09-26\",\"updated_at\":\"2026-09-26T00:57:48.000000Z\"}', '{\"tgl_transaksi\":\"2026-08-20\",\"updated_at\":\"2026-09-26 08:04:02\"}', 'http://localhost/percetakan-app/public/pembelian-bahan/3', '127.0.0.1', '2026-09-26 01:04:02'),
(23, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 4, NULL, '{\"tgl_transaksi\":\"2026-09-26\",\"updated_at\":\"2026-09-26T01:01:45.000000Z\"}', '{\"tgl_transaksi\":\"2026-08-24\",\"updated_at\":\"2026-09-26 08:04:51\"}', 'http://localhost/percetakan-app/public/pembelian-bahan/4', '127.0.0.1', '2026-09-26 01:04:51'),
(24, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 5, NULL, NULL, '{\"id_kedatangan\":\"5\",\"no_faktur\":\"SO-WU\\/0926\\/00049\",\"tgl_transaksi\":\"2026-09-26\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":true,\"jatuh_tempo_hari\":30,\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-26 08:11:53\",\"created_at\":\"2026-09-26 08:11:53\",\"id_pembelian\":5}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:11:53'),
(25, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 5, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-26\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:11:53'),
(26, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 5, NULL, '{\"grand_total\":0}', '{\"grand_total\":4103998.5600000005}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:11:53'),
(27, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku SO-WU/0926/00049', NULL, '{\"no_referensi\":\"SO-WU\\/0926\\/00049\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":4103998.5600000005,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":4103998.5600000005}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-26 01:11:53'),
(28, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 5, NULL, '{\"tgl_transaksi\":\"2026-09-26\",\"updated_at\":\"2026-09-26T01:11:53.000000Z\"}', '{\"tgl_transaksi\":\"2026-09-01\",\"updated_at\":\"2026-09-26 08:12:23\"}', 'http://localhost/percetakan-app/public/pembelian-bahan/5', '127.0.0.1', '2026-09-26 01:12:23'),
(29, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 3, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":138,\"payload\":\"{\\\"id_bahan\\\":138,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"2\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR BLACK: 0 -> 2\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:10:19\",\"created_at\":\"2026-09-26 09:10:19\",\"id\":3}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:10:19'),
(30, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 4, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":137,\"payload\":\"{\\\"id_bahan\\\":137,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR YELLOW: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:11:16\",\"created_at\":\"2026-09-26 09:11:16\",\"id\":4}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:11:16'),
(31, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 5, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":142,\"payload\":\"{\\\"id_bahan\\\":142,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SUBLIME MAGENTA: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:11:36\",\"created_at\":\"2026-09-26 09:11:36\",\"id\":5}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:11:36'),
(32, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 6, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":143,\"payload\":\"{\\\"id_bahan\\\":143,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SUBLIME YELLOW: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:13:01\",\"created_at\":\"2026-09-26 09:13:01\",\"id\":6}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:13:01'),
(33, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 7, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":138,\"payload\":\"{\\\"id_bahan\\\":138,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"10\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR BLACK: 0 -> 10\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:13:32\",\"created_at\":\"2026-09-26 09:13:32\",\"id\":7}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:13:32'),
(34, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 8, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":44,\"payload\":\"{\\\"id_bahan\\\":44,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR CYAN: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:14:11\",\"created_at\":\"2026-09-26 09:14:11\",\"id\":8}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:14:11'),
(35, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 9, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":138,\"payload\":\"{\\\"id_bahan\\\":138,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR BLACK: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:18:54\",\"created_at\":\"2026-09-26 09:18:54\",\"id\":9}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:18:54'),
(36, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 9, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:18:54\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:18:54'),
(37, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 10, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":95,\"payload\":\"{\\\"id_bahan\\\":95,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok SOLVENT PEMBERSIH FLORA: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:19:06\",\"created_at\":\"2026-09-26 09:19:06\",\"id\":10}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:06'),
(38, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 10, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:19:06\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:06'),
(39, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 11, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":44,\"payload\":\"{\\\"id_bahan\\\":44,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR CYAN: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:19:14\",\"created_at\":\"2026-09-26 09:19:14\",\"id\":11}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:14'),
(40, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 11, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:19:14\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:14'),
(41, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 12, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":136,\"payload\":\"{\\\"id_bahan\\\":136,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR MAGENTA: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:19:22\",\"created_at\":\"2026-09-26 09:19:22\",\"id\":12}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:22'),
(42, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 12, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:19:22\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:22'),
(43, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 13, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":137,\"payload\":\"{\\\"id_bahan\\\":137,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"5\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SOLVENT FLORA OUTDOOR YELLOW: 0 -> 5\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:19:35\",\"created_at\":\"2026-09-26 09:19:35\",\"id\":13}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:35'),
(44, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 13, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:19:35\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:19:35'),
(45, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 14, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":141,\"payload\":\"{\\\"id_bahan\\\":141,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA DTF BLACK: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:20:23\",\"created_at\":\"2026-09-26 09:20:23\",\"id\":14}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:23'),
(46, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 14, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:20:23\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:23'),
(47, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 15, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":139,\"payload\":\"{\\\"id_bahan\\\":139,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA DTF MAGENTA: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:20:34\",\"created_at\":\"2026-09-26 09:20:34\",\"id\":15}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:34'),
(48, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 15, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:20:34\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:34'),
(49, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 16, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":140,\"payload\":\"{\\\"id_bahan\\\":140,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA DTF YELLOW: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:20:43\",\"created_at\":\"2026-09-26 09:20:43\",\"id\":16}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:43'),
(50, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 16, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:20:43\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:43'),
(51, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 17, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":144,\"payload\":\"{\\\"id_bahan\\\":144,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SUBLIME BLACK: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:20:57\",\"created_at\":\"2026-09-26 09:20:57\",\"id\":17}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:57'),
(52, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 17, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:20:57\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:20:57'),
(53, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 18, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":50,\"payload\":\"{\\\"id_bahan\\\":50,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SUBLIME CYAN: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:21:12\",\"created_at\":\"2026-09-26 09:21:12\",\"id\":18}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:21:12'),
(54, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 18, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:21:12\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:21:12'),
(55, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 19, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":142,\"payload\":\"{\\\"id_bahan\\\":142,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SUBLIME MAGENTA: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:21:52\",\"created_at\":\"2026-09-26 09:21:52\",\"id\":19}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:21:52'),
(56, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 19, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:21:52\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:21:52'),
(57, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 20, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":143,\"payload\":\"{\\\"id_bahan\\\":143,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA SUBLIME YELLOW: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:22:07\",\"created_at\":\"2026-09-26 09:22:07\",\"id\":20}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:07'),
(58, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 20, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:22:07\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:07'),
(59, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 21, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":121,\"payload\":\"{\\\"id_bahan\\\":121,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 BLACK: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:22:22\",\"created_at\":\"2026-09-26 09:22:22\",\"id\":21}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:22'),
(60, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 21, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:22:22\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:22'),
(61, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 22, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":125,\"payload\":\"{\\\"id_bahan\\\":125,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok PISAU CUTTER ROLAND G540: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:22:35\",\"created_at\":\"2026-09-26 09:22:35\",\"id\":22}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:35'),
(62, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 22, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:22:35\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:35'),
(63, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 23, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":122,\"payload\":\"{\\\"id_bahan\\\":122,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 LIGHT BLACK: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:22:47\",\"created_at\":\"2026-09-26 09:22:47\",\"id\":23}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:47'),
(64, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 23, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:22:47\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:22:47'),
(65, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 24, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":117,\"payload\":\"{\\\"id_bahan\\\":117,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 LIGHT CYAN: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:23:07\",\"created_at\":\"2026-09-26 09:23:07\",\"id\":24}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:07'),
(66, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 24, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:23:07\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:07'),
(67, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 25, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":119,\"payload\":\"{\\\"id_bahan\\\":119,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 LIGHT MAGENTA: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:23:15\",\"created_at\":\"2026-09-26 09:23:15\",\"id\":25}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:15'),
(68, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 25, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:23:15\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:15'),
(69, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 26, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":118,\"payload\":\"{\\\"id_bahan\\\":118,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 MAGENTA: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:23:23\",\"created_at\":\"2026-09-26 09:23:23\",\"id\":26}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:23'),
(70, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 26, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:23:23\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:23'),
(71, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 27, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":123,\"payload\":\"{\\\"id_bahan\\\":123,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 ORANGE: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:23:30\",\"created_at\":\"2026-09-26 09:23:30\",\"id\":27}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:30'),
(72, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 27, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:23:30\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:30'),
(73, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 28, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":124,\"payload\":\"{\\\"id_bahan\\\":124,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 RED: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:23:38\",\"created_at\":\"2026-09-26 09:23:38\",\"id\":28}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:38'),
(74, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 28, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:23:38\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:38'),
(75, 1, 'admin', 'created', 'App\\Models\\ApprovalRequest', 29, NULL, NULL, '{\"tipe_aksi\":\"penyesuaian_stok_manual\",\"referensi_type\":\"bahan_baku\",\"referensi_id\":120,\"payload\":\"{\\\"id_bahan\\\":120,\\\"tipe\\\":\\\"ADJUSTMENT\\\",\\\"qty\\\":null,\\\"stok_baru\\\":\\\"1\\\",\\\"keterangan\\\":\\\"Recheck\\\"}\",\"ringkasan\":\"Revisi stok TINTA ECO-SOLVENT EPSON S80670 YELLOW: 0 -> 1\",\"requested_by\":1,\"alasan_pengajuan\":\"Recheck\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-26 09:23:45\",\"created_at\":\"2026-09-26 09:23:45\",\"id\":29}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:45'),
(76, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 29, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"catatan_keputusan\":null,\"decided_at\":null}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"catatan_keputusan\":\"Disetujui otomatis (diajukan oleh Administrator)\",\"decided_at\":\"2026-09-26 09:23:45\"}', 'http://localhost/percetakan-app/public/kartu-stok/adjustment', '127.0.0.1', '2026-09-26 02:23:45'),
(77, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 8, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T02:14:11.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 10:39:46\",\"updated_at\":\"2026-09-26 10:39:46\"}', 'http://localhost/percetakan-app/public/approval/8/approve', '127.0.0.1', '2026-09-26 03:39:46'),
(78, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 7, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T02:13:32.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 10:39:48\",\"updated_at\":\"2026-09-26 10:39:48\"}', 'http://localhost/percetakan-app/public/approval/7/approve', '127.0.0.1', '2026-09-26 03:39:48'),
(79, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 6, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T02:13:01.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 10:39:49\",\"updated_at\":\"2026-09-26 10:39:49\"}', 'http://localhost/percetakan-app/public/approval/6/approve', '127.0.0.1', '2026-09-26 03:39:49'),
(80, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 5, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T02:11:36.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 10:39:50\",\"updated_at\":\"2026-09-26 10:39:50\"}', 'http://localhost/percetakan-app/public/approval/5/approve', '127.0.0.1', '2026-09-26 03:39:50'),
(81, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 4, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T02:11:16.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 10:39:51\",\"updated_at\":\"2026-09-26 10:39:51\"}', 'http://localhost/percetakan-app/public/approval/4/approve', '127.0.0.1', '2026-09-26 03:39:51'),
(82, 1, 'admin', 'updated', 'App\\Models\\ApprovalRequest', 3, NULL, '{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-26T02:10:19.000000Z\"}', '{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-26 10:39:53\",\"updated_at\":\"2026-09-26 10:39:53\"}', 'http://localhost/percetakan-app/public/approval/3/approve', '127.0.0.1', '2026-09-26 03:39:53'),
(83, 1, 'admin', 'created', 'App\\Models\\JerseyOrder', 21, NULL, NULL, '{\"order_number\":\"JO-20260927-001\",\"order_date\":\"2026-09-27 00:00:00\",\"id_konsumen\":16,\"customer_name\":\"Dias Kecamatan Robatal\",\"customer_phone\":\"-\",\"team_name\":\"Kecamatan Robatal\",\"deadline\":\"2026-10-02 00:00:00\",\"status\":\"baru\",\"jersey_designer_id\":\"3\",\"designer_name\":\"FILE READY\",\"notes\":null,\"total\":0,\"discount\":\"0\",\"additional_charge\":\"0\",\"deposit\":0,\"status_pembayaran\":\"Belum Bayar\",\"created_by\":1,\"updated_at\":\"2026-09-27 16:30:29\",\"created_at\":\"2026-09-27 16:30:29\",\"id\":21}', 'http://localhost/percetakan-app/public/jersey/store', '127.0.0.1', '2026-09-27 09:30:29'),
(84, 1, 'admin', 'updated', 'App\\Models\\JerseyOrder', 21, NULL, '{\"total\":\"0.00\"}', '{\"total\":2280000}', 'http://localhost/percetakan-app/public/jersey/store', '127.0.0.1', '2026-09-27 09:30:29'),
(85, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pendapatan order jersey JO-20260927-001', NULL, '{\"no_referensi\":\"JO-20260927-001\",\"lines\":[{\"kode_akun\":\"1100\",\"debet\":2280000,\"kredit\":0},{\"kode_akun\":\"4300\",\"debet\":0,\"kredit\":2280000}]}', 'http://localhost/percetakan-app/public/jersey/store', '127.0.0.1', '2026-09-27 09:30:29'),
(86, 1, 'admin', 'updated', 'bahan_baku', 147, 'Harga beli awal saat registrasi bahan', NULL, '{\"harga_rata_rata\":1900000}', 'http://localhost/percetakan-app/public/bahan-baku', '127.0.0.1', '2026-09-27 09:48:37'),
(87, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Saldo awal persediaan Maintenance Kit Epson Indoor (1 pcs x Rp 1.900.000)', NULL, '{\"no_referensi\":\"STOK-AWAL-MAIN-EPS-IN\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":1900000,\"kredit\":0},{\"kode_akun\":\"3000\",\"debet\":0,\"kredit\":1900000}]}', 'http://localhost/percetakan-app/public/bahan-baku', '127.0.0.1', '2026-09-27 09:48:37'),
(88, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 6, NULL, NULL, '{\"id_kedatangan\":\"6\",\"no_faktur\":\"SO-WU\\/0926\\/02643\",\"tgl_transaksi\":\"2026-09-21\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":true,\"jatuh_tempo_hari\":30,\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-27 16:51:05\",\"created_at\":\"2026-09-27 16:51:05\",\"id_pembelian\":6}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 09:51:05'),
(89, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 6, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-21\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 09:51:05'),
(90, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 6, NULL, '{\"grand_total\":0}', '{\"grand_total\":1899999.2100000002}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 09:51:05'),
(91, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku SO-WU/0926/02643', NULL, '{\"no_referensi\":\"SO-WU\\/0926\\/02643\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":1899999.2100000002,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":1899999.2100000002}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 09:51:05'),
(92, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 7, NULL, NULL, '{\"id_kedatangan\":\"7\",\"no_faktur\":\"FJ-26-09-00089\",\"tgl_transaksi\":\"2026-09-21\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":false,\"jatuh_tempo_hari\":30,\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-27 17:13:39\",\"created_at\":\"2026-09-27 17:13:39\",\"id_pembelian\":7}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:13:39'),
(93, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 7, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-21\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:13:39'),
(94, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 7, NULL, '{\"grand_total\":0}', '{\"grand_total\":2680000}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:13:39'),
(95, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku FJ-26-09-00089', NULL, '{\"no_referensi\":\"FJ-26-09-00089\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":2680000,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":2680000}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:13:39'),
(96, 1, 'admin', 'created', 'App\\Models\\PembelianBahan', 8, NULL, NULL, '{\"id_kedatangan\":\"8\",\"no_faktur\":\"SJ-26-09-00060\",\"tgl_transaksi\":\"2026-09-14\",\"tipe_pembelian\":\"Tempo\",\"include_pajak\":false,\"jatuh_tempo_hari\":30,\"ongkir\":0,\"biaya_layanan\":0,\"voucher\":0,\"grand_total\":0,\"updated_at\":\"2026-09-27 17:14:14\",\"created_at\":\"2026-09-27 17:14:14\",\"id_pembelian\":8}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:14:14'),
(97, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 8, NULL, '[]', '{\"status_pembayaran\":\"Hutang\",\"jatuh_tempo_tgl\":\"2026-10-14\"}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:14:14'),
(98, 1, 'admin', 'updated', 'App\\Models\\PembelianBahan', 8, NULL, '{\"grand_total\":0}', '{\"grand_total\":1292000}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:14:14'),
(99, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pembelian bahan baku SJ-26-09-00060', NULL, '{\"no_referensi\":\"SJ-26-09-00060\",\"lines\":[{\"kode_akun\":\"1200\",\"debet\":1292000,\"kredit\":0},{\"kode_akun\":\"2000\",\"debet\":0,\"kredit\":1292000}]}', 'http://localhost/percetakan-app/public/pembelian-bahan', '127.0.0.1', '2026-09-27 10:14:14'),
(100, 1, 'admin', 'updated', 'jam_kerja', NULL, 'Pengaturan jam kerja diperbarui', NULL, NULL, 'http://localhost/percetakan-app/public/pengaturan-jam-kerja', '127.0.0.1', '2026-09-27 12:27:50'),
(101, 1, 'admin', 'updated', 'jam_kerja', NULL, 'Pengaturan jam kerja diperbarui', NULL, NULL, 'http://localhost/percetakan-app/public/pengaturan-jam-kerja', '127.0.0.1', '2026-09-27 12:28:09'),
(102, 1, 'admin', 'updated', 'jam_kerja', NULL, 'Pengaturan jam kerja diperbarui', NULL, NULL, 'http://localhost/percetakan-app/public/pengaturan-jam-kerja', '127.0.0.1', '2026-09-27 12:49:50'),
(103, 1, 'admin', 'created', 'App\\Models\\JerseyOrder', 22, NULL, NULL, '{\"order_number\":\"JO-20260927-002\",\"order_date\":\"2026-09-23 00:00:00\",\"id_konsumen\":17,\"customer_name\":\"Muhammad Effendy\",\"customer_phone\":\"-\",\"team_name\":\"DPD REI Komisariat Madura\",\"deadline\":\"2026-09-28 00:00:00\",\"status\":\"baru\",\"jersey_designer_id\":\"4\",\"designer_name\":\"Administrasi\",\"notes\":null,\"total\":0,\"discount\":\"0\",\"additional_charge\":\"0\",\"deposit\":0,\"status_pembayaran\":\"Belum Bayar\",\"created_by\":1,\"updated_at\":\"2026-09-27 19:59:24\",\"created_at\":\"2026-09-27 19:59:24\",\"id\":22}', 'http://localhost/percetakan-app/public/jersey/store', '127.0.0.1', '2026-09-27 12:59:24'),
(104, 1, 'admin', 'updated', 'App\\Models\\JerseyOrder', 22, NULL, '{\"total\":\"0.00\",\"updated_at\":\"2026-09-27T12:59:24.000000Z\"}', '{\"total\":2152600,\"updated_at\":\"2026-09-27 19:59:25\"}', 'http://localhost/percetakan-app/public/jersey/store', '127.0.0.1', '2026-09-27 12:59:25'),
(105, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Pendapatan order jersey JO-20260927-002', NULL, '{\"no_referensi\":\"JO-20260927-002\",\"lines\":[{\"kode_akun\":\"1100\",\"debet\":2152600,\"kredit\":0},{\"kode_akun\":\"4300\",\"debet\":0,\"kredit\":2152600}]}', 'http://localhost/percetakan-app/public/jersey/store', '127.0.0.1', '2026-09-27 12:59:25'),
(106, 1, 'admin', 'updated', 'App\\Models\\JerseyOrder', 22, NULL, '{\"status\":\"baru\",\"updated_at\":\"2026-09-27T12:59:25.000000Z\"}', '{\"status\":\"selesai\",\"updated_at\":\"2026-09-27 20:00:58\"}', 'http://localhost/percetakan-app/public/jersey/22', '127.0.0.1', '2026-09-27 13:00:58'),
(107, 1, 'admin', 'created', 'jurnal_umum', NULL, 'HPP kain jersey JO-20260927-002', NULL, '{\"no_referensi\":\"JO-20260927-002\",\"lines\":[{\"kode_akun\":\"5000\",\"debet\":331180,\"kredit\":0},{\"kode_akun\":\"1200\",\"debet\":0,\"kredit\":331180}]}', 'http://localhost/percetakan-app/public/jersey/22', '127.0.0.1', '2026-09-27 13:00:58'),
(108, 1, 'admin', 'updated', 'produk', 2, 'BOM produk diperbarui (atau disimpan ulang tanpa perubahan)', '{\"bom\":[{\"id_bahan\":2,\"cara_hitung_pemakaian\":\"(P*L)\\/10000\"}]}', '{\"bom\":[{\"id_bahan\":\"2\",\"cara_hitung_pemakaian\":\"(P*L)\\/10000\"}]}', 'http://localhost/percetakan-app/public/produk/2', '127.0.0.1', '2026-09-27 13:57:42'),
(109, 1, 'admin', 'updated', 'produk', 3, 'BOM produk diperbarui (atau disimpan ulang tanpa perubahan)', '{\"bom\":[{\"id_bahan\":5,\"cara_hitung_pemakaian\":\"(P*L)\\/10000\"}]}', '{\"bom\":[{\"id_bahan\":\"5\",\"cara_hitung_pemakaian\":\"(P*L)\\/10000\"}]}', 'http://localhost/percetakan-app/public/produk/3', '127.0.0.1', '2026-09-27 13:58:06'),
(110, 1, 'admin', 'deleted', 'setting_harga', 85, 'Tier harga dihapus', '{\"id_harga\":85,\"id_produk\":2,\"id_golongan\":1,\"range_min\":\"1.00\",\"range_max\":\"9999999.00\",\"harga_jual\":\"20000.00\",\"id_satuan\":21,\"qty_berlaku\":1,\"id_satuan_berlaku\":21,\"created_at\":\"2026-03-24 20:42:48\",\"updated_at\":\"2026-09-12 05:51:46\"}', NULL, 'http://localhost/percetakan-app/public/setting-harga-tier/85', '127.0.0.1', '2026-09-27 13:59:29'),
(111, 1, 'admin', 'created', 'setting_harga', 2050, 'Tier harga baru untuk produk #2', NULL, '{\"id_harga\":2050,\"id_produk\":2,\"id_golongan\":1,\"range_min\":\"0.10\",\"range_max\":\"999999.00\",\"harga_jual\":\"20000.00\",\"id_satuan\":null,\"qty_berlaku\":1,\"id_satuan_berlaku\":null,\"created_at\":\"2026-09-27 20:59:38\",\"updated_at\":\"2026-09-27 20:59:38\"}', 'http://localhost/percetakan-app/public/setting-harga/2/tier', '127.0.0.1', '2026-09-27 13:59:38'),
(112, 1, 'admin', 'updated', 'setting_harga', 2, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/2/copy-golongan', '127.0.0.1', '2026-09-27 13:59:58');
INSERT INTO `audit_logs` (`id`, `user_id`, `user_label`, `event`, `auditable_type`, `auditable_id`, `keterangan`, `old_values`, `new_values`, `url`, `ip_address`, `created_at`) VALUES
(113, 1, 'admin', 'updated', 'setting_harga', 2054, 'Tier harga diperbarui', '{\"range_min\":\"0.10\",\"range_max\":\"999999.00\",\"harga_jual\":\"20000.00\"}', '{\"range_min\":\"0.1\",\"range_max\":\"999999\",\"harga_jual\":\"18000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2054', '127.0.0.1', '2026-09-27 14:00:16'),
(114, 1, 'admin', 'updated', 'setting_harga', 8, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/8/copy-golongan', '127.0.0.1', '2026-09-27 14:16:29'),
(115, 1, 'admin', 'updated', 'setting_harga', 2056, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"50000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"47000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2056', '127.0.0.1', '2026-09-27 14:16:41'),
(116, 1, 'admin', 'updated', 'setting_harga', 9, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/9/copy-golongan', '127.0.0.1', '2026-09-27 14:16:49'),
(117, 1, 'admin', 'updated', 'setting_harga', 2061, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"80000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"77000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2061', '127.0.0.1', '2026-09-27 14:16:53'),
(118, 1, 'admin', 'updated', 'setting_harga', 7, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/7/copy-golongan', '127.0.0.1', '2026-09-27 14:17:01'),
(119, 1, 'admin', 'updated', 'setting_harga', 2066, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"70000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"67000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2066', '127.0.0.1', '2026-09-27 14:17:05'),
(120, 1, 'admin', 'updated', 'setting_harga', 1915, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"70000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"67000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/1915', '127.0.0.1', '2026-09-27 14:17:21'),
(121, 1, 'admin', 'updated', 'setting_harga', 1957, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"20000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"16000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/1957', '127.0.0.1', '2026-09-27 14:17:37'),
(122, 1, 'admin', 'updated', 'setting_harga', 20, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":2,\"ke_golongan\":[1,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/20/copy-golongan', '127.0.0.1', '2026-09-27 14:17:51'),
(123, 1, 'admin', 'updated', 'setting_harga', 2071, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":\"224.00\",\"harga_jual\":\"16000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":\"224\",\"harga_jual\":\"20000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2071', '127.0.0.1', '2026-09-27 14:18:03'),
(124, 1, 'admin', 'updated', 'setting_harga', 20, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/20/copy-golongan', '127.0.0.1', '2026-09-27 14:18:05'),
(125, 1, 'admin', 'updated', 'setting_harga', 2076, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":\"224.00\",\"harga_jual\":\"20000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":\"224\",\"harga_jual\":\"16000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2076', '127.0.0.1', '2026-09-27 14:18:08'),
(126, 1, 'admin', 'updated', 'setting_harga', 18, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":10}', 'http://localhost/percetakan-app/public/setting-harga/18/copy-golongan', '127.0.0.1', '2026-09-27 14:18:28'),
(127, 1, 'admin', 'updated', 'setting_harga', 2081, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":\"154.00\",\"harga_jual\":\"20000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":\"154\",\"harga_jual\":\"16000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2081', '127.0.0.1', '2026-09-27 14:18:34'),
(128, 1, 'admin', 'updated', 'setting_harga', 2082, 'Tier harga diperbarui', '{\"range_min\":\"155.00\",\"range_max\":\"308.00\",\"harga_jual\":\"16000.00\"}', '{\"range_min\":\"155\",\"range_max\":\"308\",\"harga_jual\":\"13000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2082', '127.0.0.1', '2026-09-27 14:18:34'),
(129, 1, 'admin', 'updated', 'setting_harga', 19, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":10}', 'http://localhost/percetakan-app/public/setting-harga/19/copy-golongan', '127.0.0.1', '2026-09-27 14:18:46'),
(130, 1, 'admin', 'updated', 'setting_harga', 2092, 'Tier harga diperbarui', '{\"range_min\":\"183.00\",\"range_max\":\"366.00\",\"harga_jual\":\"15000.00\"}', '{\"range_min\":\"183\",\"range_max\":\"366\",\"harga_jual\":\"13000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2092', '127.0.0.1', '2026-09-27 14:18:53'),
(131, 1, 'admin', 'updated', 'setting_harga', 2091, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":\"182.00\",\"harga_jual\":\"20000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":\"182\",\"harga_jual\":\"16000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2091', '127.0.0.1', '2026-09-27 14:18:53'),
(132, 1, 'admin', 'updated', 'setting_harga', 88, 'Tier harga diperbarui', '{\"range_min\":\"1.00\",\"range_max\":\"999999.00\",\"harga_jual\":\"18000.00\"}', '{\"range_min\":\"1\",\"range_max\":\"999999\",\"harga_jual\":\"20000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/88', '127.0.0.1', '2026-09-27 14:19:08'),
(133, 1, 'admin', 'updated', 'setting_harga', 1, 'Harga disalin ke semua golongan lain', NULL, '{\"dari_golongan\":1,\"ke_golongan\":[2,3,4,6,7],\"jumlah_tier\":5}', 'http://localhost/percetakan-app/public/setting-harga/1/copy-golongan', '127.0.0.1', '2026-09-27 14:19:10'),
(134, 1, 'admin', 'updated', 'setting_harga', 2101, 'Tier harga diperbarui', '{\"range_min\":\"1.00\",\"range_max\":\"999999.00\",\"harga_jual\":\"20000.00\"}', '{\"range_min\":\"1\",\"range_max\":\"999999\",\"harga_jual\":\"16000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/2101', '127.0.0.1', '2026-09-27 14:19:14'),
(135, 1, 'admin', 'updated', 'setting_harga', 1963, 'Tier harga diperbarui', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"75000.00\"}', '{\"range_min\":\"0.01\",\"range_max\":null,\"harga_jual\":\"70000\"}', 'http://localhost/percetakan-app/public/setting-harga-tier/1963', '127.0.0.1', '2026-09-27 14:19:36'),
(136, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Penjualan ORG-20260927-001', NULL, '{\"no_referensi\":\"ORG-20260927-001\",\"lines\":[{\"kode_akun\":\"1100\",\"debet\":112600,\"kredit\":0},{\"kode_akun\":\"4000\",\"debet\":0,\"kredit\":112600}]}', 'http://localhost/percetakan-app/public/penjualan/store', '127.0.0.1', '2026-09-27 14:26:42'),
(137, 1, 'admin', 'created', 'jurnal_umum', NULL, 'HPP bahan baku ORG-20260927-001', NULL, '{\"no_referensi\":\"ORG-20260927-001\",\"lines\":[{\"kode_akun\":\"5000\",\"debet\":57418.36460176991,\"kredit\":0},{\"kode_akun\":\"1200\",\"debet\":0,\"kredit\":57418.36460176991}]}', 'http://localhost/percetakan-app/public/penjualan/store', '127.0.0.1', '2026-09-27 14:26:42'),
(138, 1, 'admin', 'created', 'jurnal_umum', NULL, 'Koreksi/pembalikan nota ORG-20260927-001', NULL, '{\"no_referensi\":\"ORG-20260927-001\",\"lines\":[{\"kode_akun\":\"1100\",\"debet\":0,\"kredit\":112600},{\"kode_akun\":\"4000\",\"debet\":112600,\"kredit\":0},{\"kode_akun\":\"5000\",\"debet\":0,\"kredit\":57418.36},{\"kode_akun\":\"1200\",\"debet\":57418.36,\"kredit\":0}]}', 'http://localhost/percetakan-app/public/penjualan/edit-nota/1/apply', '127.0.0.1', '2026-09-27 14:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipe` enum('daily','weekly','monthly','manual') NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `ukuran_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Sukses','Gagal') NOT NULL DEFAULT 'Sukses',
  `offsite_status` varchar(255) DEFAULT NULL,
  `pesan_error` text DEFAULT NULL,
  `offsite_error` text DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `backup_logs`
--

INSERT INTO `backup_logs` (`id`, `tipe`, `file_path`, `ukuran_bytes`, `status`, `offsite_status`, `pesan_error`, `offsite_error`, `started_at`, `finished_at`, `created_at`, `updated_at`) VALUES
(1, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_101409.sql', 189509, 'Sukses', NULL, NULL, NULL, '2026-09-24 03:14:10', '2026-09-24 03:14:10', '2026-09-24 03:14:09', '2026-09-24 03:14:10'),
(2, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_103133.sql', 233919, 'Sukses', NULL, NULL, NULL, '2026-09-24 03:31:33', '2026-09-24 03:31:33', '2026-09-24 03:31:33', '2026-09-24 03:31:33'),
(3, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_103618.sql', 234219, 'Sukses', NULL, NULL, NULL, '2026-09-24 03:36:19', '2026-09-24 03:36:19', '2026-09-24 03:36:18', '2026-09-24 03:36:19'),
(4, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_104031.sql', 235941, 'Sukses', NULL, NULL, NULL, '2026-09-24 03:40:32', '2026-09-24 03:40:32', '2026-09-24 03:40:31', '2026-09-24 03:40:32'),
(5, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_104520.sql', 238352, 'Sukses', NULL, NULL, NULL, '2026-09-24 03:45:21', '2026-09-24 03:45:21', '2026-09-24 03:45:20', '2026-09-24 03:45:21'),
(6, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_105356.sql', 238637, 'Sukses', NULL, NULL, NULL, '2026-09-24 03:53:57', '2026-09-24 03:53:57', '2026-09-24 03:53:56', '2026-09-24 03:53:57'),
(7, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_185005.sql', 242523, 'Sukses', NULL, NULL, NULL, '2026-09-24 11:50:05', '2026-09-24 11:50:05', '2026-09-24 11:50:05', '2026-09-24 11:50:05'),
(8, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_190055.sql', 249478, 'Sukses', NULL, NULL, NULL, '2026-09-24 12:00:56', '2026-09-24 12:00:56', '2026-09-24 12:00:55', '2026-09-24 12:00:56'),
(9, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260924_192313.sql', 256245, 'Sukses', NULL, NULL, NULL, '2026-09-24 12:23:14', '2026-09-24 12:23:14', '2026-09-24 12:23:13', '2026-09-24 12:23:14'),
(10, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_003543.sql', 255367, 'Sukses', NULL, NULL, NULL, '2026-09-24 17:35:44', '2026-09-24 17:35:44', '2026-09-24 17:35:43', '2026-09-24 17:35:44'),
(11, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_003833.sql', 255595, 'Sukses', NULL, NULL, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34', '2026-09-24 17:38:33', '2026-09-24 17:38:34'),
(12, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_065950.sql', 374081, 'Sukses', NULL, NULL, NULL, '2026-09-24 23:59:51', '2026-09-24 23:59:51', '2026-09-24 23:59:50', '2026-09-24 23:59:51'),
(13, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_133400.sql', 436352, 'Sukses', NULL, NULL, NULL, '2026-09-25 06:34:01', '2026-09-25 06:34:01', '2026-09-25 06:34:00', '2026-09-25 06:34:01'),
(14, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_133402.sql', 443042, 'Sukses', NULL, NULL, NULL, '2026-09-25 06:34:03', '2026-09-25 06:34:03', '2026-09-25 06:34:02', '2026-09-25 06:34:03'),
(15, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_143236.sql', 444363, 'Sukses', NULL, NULL, NULL, '2026-09-25 07:32:36', '2026-09-25 07:32:36', '2026-09-25 07:32:36', '2026-09-25 07:32:36'),
(16, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_184423.sql', 450174, 'Sukses', NULL, NULL, NULL, '2026-09-25 11:44:24', '2026-09-25 11:44:24', '2026-09-25 11:44:23', '2026-09-25 11:44:24'),
(17, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_192936.sql', 453977, 'Sukses', NULL, NULL, NULL, '2026-09-25 12:29:37', '2026-09-25 12:29:37', '2026-09-25 12:29:36', '2026-09-25 12:29:37'),
(18, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_193227.sql', 457554, 'Sukses', NULL, NULL, NULL, '2026-09-25 12:32:28', '2026-09-25 12:32:28', '2026-09-25 12:32:27', '2026-09-25 12:32:28'),
(19, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_212820.sql', 469063, 'Sukses', NULL, NULL, NULL, '2026-09-25 14:28:21', '2026-09-25 14:28:21', '2026-09-25 14:28:20', '2026-09-25 14:28:21'),
(20, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260925_214138.sql', 470233, 'Sukses', NULL, NULL, NULL, '2026-09-25 14:41:39', '2026-09-25 14:41:39', '2026-09-25 14:41:38', '2026-09-25 14:41:39'),
(21, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260926_062113.sql', 468810, 'Sukses', NULL, NULL, NULL, '2026-09-25 23:21:14', '2026-09-25 23:21:14', '2026-09-25 23:21:13', '2026-09-25 23:21:14'),
(22, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260926_064712.sql', 470417, 'Sukses', NULL, NULL, NULL, '2026-09-25 23:47:13', '2026-09-25 23:47:13', '2026-09-25 23:47:12', '2026-09-25 23:47:13'),
(23, 'manual', 'C:\\xampp\\htdocs\\percetakan-app\\storage\\app/backups\\manual\\backup_manual_20260926_080714.sql', 340560, 'Sukses', NULL, NULL, NULL, '2026-09-26 01:07:15', '2026-09-26 01:07:15', '2026-09-26 01:07:14', '2026-09-26 01:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `bag_akun`
--

CREATE TABLE `bag_akun` (
  `id_akun` bigint(20) UNSIGNED NOT NULL,
  `kode_akun` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_akun` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_akun` enum('Aset','Kewajiban','Modal','Pendapatan','Beban') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `saldo_normal` enum('Debet','Kredit') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bag_akun`
--

INSERT INTO `bag_akun` (`id_akun`, `kode_akun`, `nama_akun`, `tipe_akun`, `saldo_normal`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '1000', 'Kas', 'Aset', 'Debet', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(2, '1010', 'Bank', 'Aset', 'Debet', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(3, '1100', 'Piutang Usaha', 'Aset', 'Debet', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(4, '1200', 'Persediaan Bahan Baku', 'Aset', 'Debet', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(5, '2000', 'Hutang Usaha', 'Kewajiban', 'Kredit', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(6, '3000', 'Modal', 'Modal', 'Kredit', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(7, '4000', 'Pendapatan Penjualan', 'Pendapatan', 'Kredit', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(8, '5000', 'Harga Pokok Penjualan', 'Beban', 'Debet', 1, '2026-09-10 07:14:45', '2026-09-10 07:14:45'),
(9, '2100', 'Hutang Deposit Konsumen', 'Kewajiban', 'Kredit', 1, '2026-09-10 08:28:38', '2026-09-10 08:28:38'),
(10, '4100', 'Pendapatan Jasa', 'Pendapatan', 'Kredit', 1, '2026-09-10 08:28:38', '2026-09-10 08:28:38'),
(12, '4200', 'Pendapatan Lain-lain', 'Pendapatan', 'Kredit', 1, '2026-09-11 17:42:29', '2026-09-11 17:42:29'),
(13, '5100', 'Biaya Lain-lain', 'Beban', 'Debet', 1, '2026-09-11 17:42:29', '2026-09-11 17:42:29'),
(14, '5110', 'Beban Administrasi & Umum', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(15, '5120', 'Beban BPJS', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(16, '5130', 'Beban Internet', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(17, '5140', 'Beban Listrik & Telepon', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(18, '5150', 'Beban PDAM', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(19, '5160', 'Beban Perlengkapan Kantor', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(20, '5170', 'Beban Perlengkapan Toko', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(21, '5180', 'Biaya Marketing', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(22, '5190', 'Biaya Ongkos Kirim & Ekspedisi', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(23, '5200', 'Biaya Operasional Kantor', 'Beban', 'Debet', 1, '2026-09-18 11:39:39', '2026-09-18 11:39:39'),
(24, '4300', 'Pendapatan Order Jersey', 'Pendapatan', 'Kredit', 1, '2026-09-23 23:24:36', '2026-09-23 23:24:36'),
(25, '1005', 'Kas Kecil', 'Aset', 'Debet', 1, '2026-09-25 23:36:15', '2026-09-25 23:36:15'),
(26, '5210', 'Beban Komisi Desain', 'Beban', 'Debet', 1, '2026-09-26 00:33:52', '2026-09-26 00:33:52');

-- --------------------------------------------------------

--
-- Table structure for table `bahan_baku`
--

CREATE TABLE `bahan_baku` (
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `kode_bahan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_bahan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_mesin` bigint(20) UNSIGNED NOT NULL,
  `id_kategori` bigint(20) UNSIGNED NOT NULL,
  `id_merk` bigint(20) UNSIGNED DEFAULT NULL,
  `id_jenis_bahan` bigint(20) UNSIGNED DEFAULT NULL,
  `stok` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `stok_minimal` decimal(15,3) NOT NULL DEFAULT 0.000,
  `id_satuan_stok` bigint(20) UNSIGNED DEFAULT NULL,
  `id_satuan_hitung` bigint(20) UNSIGNED DEFAULT NULL,
  `nilai_konversi` decimal(15,3) NOT NULL DEFAULT 1.000,
  `lebar_roll` decimal(10,3) DEFAULT NULL,
  `panjang_roll` decimal(10,3) DEFAULT NULL,
  `harga_rata_rata` decimal(15,2) NOT NULL DEFAULT 0.00,
  `satuan_stok` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bahan_baku`
--

INSERT INTO `bahan_baku` (`id_bahan`, `kode_bahan`, `nama_bahan`, `id_mesin`, `id_kategori`, `id_merk`, `id_jenis_bahan`, `stok`, `stok_minimal`, `id_satuan_stok`, `id_satuan_hitung`, `nilai_konversi`, `lebar_roll`, `panjang_roll`, `harga_rata_rata`, `satuan_stok`, `status`, `created_at`, `updated_at`) VALUES
(1, 'FLS280-320', 'FRONTLITE 280gr 3.2m', 2, 3, 2, 2, 15.9885, 1.000, 17, 21, 224.000, 3.200, 70.000, 1371715.00, 'roll', 'aktif', '2026-09-11 16:25:20', '2026-09-26 01:00:50'),
(2, 'TC', 'TC Textile Import 120cm', 4, 5, 2, 2, 2.9220, 1.000, 17, 21, 271.200, 1.200, 226.000, 1848000.00, 'roll', 'aktif', '2026-09-10 21:53:06', '2026-09-25 12:34:10'),
(3, 'AC260', 'AC 260gr', 3, 4, 2, 2, 3.0000, 0.000, 20, 18, 500.000, NULL, NULL, 460000.00, 'rim', 'aktif', '2026-09-11 16:24:28', '2026-09-24 17:38:34'),
(4, 'AC210', 'AC 210gr', 3, 4, NULL, NULL, 3.0000, 0.000, 20, 18, 500.000, NULL, NULL, 370000.00, 'rim', 'aktif', '2026-09-10 15:22:30', '2026-09-24 17:38:34'),
(5, 'SB', 'SUBLIME PAPER', 6, 6, NULL, NULL, 11.0000, 0.000, 17, 21, 160.000, 1.600, 100.000, 647026.91, 'roll', 'aktif', '2026-09-11 18:21:05', '2026-09-26 01:11:06'),
(6, 'DTF', 'PET FILM DTF', 5, 7, 2, 2, 1.9470, 1.000, 17, 21, 30.000, 0.600, 50.000, 600000.00, 'roll', 'aktif', '2026-09-11 16:25:20', '2026-09-25 12:37:57'),
(7, 'ST-CML', 'STIKER CAMEL 155cm', 1, 2, 2, 2, 8.0000, 1.000, 17, 21, 77.500, 1.550, 50.000, 992986.00, 'roll', 'aktif', '2026-09-11 16:18:27', '2026-09-26 00:48:26'),
(8, 'ST-INF', 'STIKER INFLEX', 1, 2, 2, 2, 1.9277, 1.000, 17, 21, 77.500, 1.550, 50.000, 1855920.00, 'roll', 'aktif', '2026-09-11 16:18:40', '2026-09-25 14:56:27'),
(9, 'ST-EXI', 'STIKER EXILO 153cm', 1, 2, 2, 2, 3.9327, 1.000, 17, 21, 76.500, 1.530, 50.000, 1201627.02, 'roll', 'aktif', '2026-09-11 16:18:48', '2026-09-27 09:53:53'),
(10, 'CNV', 'CANVAS 90cm', 1, 2, 2, 2, 2.0000, 1.000, 17, 21, 45.000, 0.900, 50.000, 1800000.00, 'roll', 'aktif', '2026-09-11 09:10:21', '2026-09-24 17:39:52'),
(11, 'PHO', 'PHOTOPAPER 90cm', 1, 2, 2, 2, 2.0000, 1.000, 17, 21, 45.000, 0.900, 50.000, 1325300.00, 'roll', 'aktif', '2026-09-11 09:50:53', '2026-09-25 14:41:35'),
(12, 'BTX', 'BONTAX', 3, 4, 2, 2, 2.0000, 0.000, 20, 18, 500.000, NULL, NULL, 530000.00, 'rim', 'aktif', '2026-09-11 09:50:53', '2026-09-24 17:38:34'),
(13, 'PPS', 'PAPER SUBLIME', 6, 6, 2, 2, 3.9505, 1.000, 17, 14, 160.000, 1.600, 100.000, 0.00, 'roll', 'nonaktif', '2026-09-11 17:36:59', '2026-09-24 17:38:34'),
(27, 'FLS280-220', 'FRONTLITE 280gr 2.2m', 2, 3, 2, 2, 3.9442, 1.000, 17, 21, 154.000, 2.200, 70.000, 884881.89, 'roll', 'aktif', '2026-09-11 16:17:42', '2026-09-26 00:48:26'),
(28, 'FLS280-260', 'FRONTLITE 280gr 2.6m', 2, 3, 2, 2, 4.0000, 1.000, 17, 21, 182.000, 2.600, 70.000, 1235200.00, 'roll', 'aktif', '2026-09-11 16:17:53', '2026-09-26 01:00:50'),
(29, 'FLS280-160', 'FRONTLITE 280gr 1.6m', 2, 3, 2, 2, 0.9812, 1.000, 17, 21, 112.000, 1.600, 70.000, 990000.00, 'roll', 'aktif', '2026-09-11 09:57:19', '2026-09-24 17:38:34'),
(30, 'FLS280-110', 'FRONTLITE 280gr 1.1m', 2, 3, 2, 2, 2.0000, 1.000, 17, 21, 77.000, 1.100, 70.000, 940000.00, 'roll', 'aktif', '2026-09-11 16:17:31', '2026-09-24 17:38:34'),
(31, 'STT', 'STIKER TRANSPARAN 155cm (Ritrama)', 1, 2, 2, 2, 1.0000, 1.000, 17, 21, 77.500, 1.550, 50.000, 1600000.00, 'roll', 'aktif', '2026-09-12 16:48:52', '2026-09-24 17:39:52'),
(32, 'ALB', 'ALBATROS', 1, 2, 2, 2, 1.0000, 1.000, 17, 21, 45.000, 0.900, 50.000, 750000.00, 'roll', 'aktif', '2026-09-12 16:49:50', '2026-09-24 17:38:34'),
(33, 'STOV', 'STIKER ONEWAY VISION', 2, 3, 2, 2, 1.0000, 1.000, 17, 21, 63.500, 1.270, 50.000, 970000.00, 'roll', 'aktif', '2026-09-12 16:53:21', '2026-09-24 17:38:34'),
(34, 'AP120', 'AP120', 3, 4, 2, 2, 1.5000, 0.000, 20, 18, 500.000, NULL, NULL, 240000.00, 'rim', 'aktif', '2026-09-12 16:54:45', '2026-09-24 17:39:52'),
(35, 'AP150', 'AP150', 3, 4, 2, 2, 2.0000, 0.000, 20, 18, 500.000, NULL, NULL, 280000.00, 'rim', 'aktif', '2026-09-12 16:55:09', '2026-09-24 17:39:52'),
(36, 'A3VS', 'A3 VINIL SUSU', 3, 4, 2, 2, 1.0000, 0.000, 20, 18, 500.000, NULL, NULL, 640000.00, 'rim', 'aktif', '2026-09-12 16:55:48', '2026-09-25 14:42:17'),
(37, 'A3TR', 'A3  TRANSPARAN', 3, 4, 2, 2, 3.0000, 0.000, 20, 18, 500.000, NULL, NULL, 600000.00, 'rim', 'aktif', '2026-09-12 16:56:10', '2026-09-25 14:58:12'),
(38, 'A3LN', 'A3 LINEN', 3, 4, 2, 2, 2.0000, 0.000, 20, 18, 500.000, NULL, NULL, 750000.00, 'rim', 'aktif', '2026-09-12 16:57:04', '2026-09-24 17:39:52'),
(39, 'A3HL', 'A3 HOLOGRAM', 3, 4, 2, 2, 2.0000, 0.000, 20, 18, 500.000, NULL, NULL, 650000.00, 'rim', 'aktif', '2026-09-12 16:57:27', '2026-09-24 17:39:52'),
(40, 'ABP', 'AMPLOP BUKLET PUTIH', 7, 8, 2, 2, 10.0000, 0.000, 12, 11, 100.000, NULL, NULL, 23000.00, 'box', 'aktif', '2026-09-24 11:44:28', '2026-09-24 17:39:52'),
(41, 'ACD12', 'AMPLOP COKLAT DINAS 12cm', 7, 8, 2, 2, 10.0000, 0.000, 12, 11, 100.000, NULL, NULL, 27000.00, 'box', 'aktif', '2026-09-24 11:45:17', '2026-09-24 17:39:52'),
(42, 'BKN', 'BOX KARTU NAMA', 3, 4, 2, 2, 100.0000, 0.000, 11, 11, 1.000, NULL, NULL, 2000.00, 'pcs', 'aktif', '2026-09-24 11:48:05', '2026-09-24 17:38:34'),
(43, 'TNT-S80670-CY', 'TINTA ECO-SOLVENT EPSON S80670 CYAN', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-24 11:50:06', '2026-09-25 11:44:25'),
(44, 'TNT-FLORA-CY', 'TINTA SOLVENT FLORA OUTDOOR CYAN', 2, 3, NULL, NULL, 5.0000, 1.000, 9, 10, 5000.000, NULL, NULL, 780000.00, 'liter', 'aktif', '2026-09-24 11:50:06', '2026-09-25 12:29:37'),
(45, 'TNR-KONICA', 'TONER KONICA MINOLTA (per botol)', 3, 4, NULL, NULL, 1.0000, 1.000, 26, 26, 1.000, NULL, NULL, 1300000.00, 'botol', 'aktif', '2026-09-24 11:50:06', '2026-09-24 17:39:52'),
(46, 'TNT-CENTURY', 'TINTA DIRECT PRINT CENTURY STAR', 4, 5, 2, 2, 0.6760, 2.000, 9, 10, 1000.000, NULL, NULL, 286000.00, 'liter', 'aktif', '2026-09-24 11:50:06', '2026-09-25 11:28:11'),
(47, 'TNT-DTF-CY', 'TINTA DTF CYAN', 5, 7, NULL, NULL, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 245000.00, 'liter', 'aktif', '2026-09-24 11:50:06', '2026-09-25 12:32:37'),
(48, 'TNT-DTF-WHT', 'TINTA DTF WHITE', 5, 7, 2, 2, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 220000.00, 'liter', 'aktif', '2026-09-24 11:50:06', '2026-09-25 11:31:35'),
(49, 'PWD-DTF', 'POWDER DTF (HOT MELT)', 5, 7, NULL, NULL, 3.0000, 2.000, 7, 8, 1000.000, NULL, NULL, 100000.00, 'kg', 'aktif', '2026-09-24 11:50:06', '2026-09-25 12:37:37'),
(50, 'TNT-SUBLIME-CY', 'TINTA SUBLIME CYAN', 6, 6, 2, 2, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 620000.00, 'liter', 'aktif', '2026-09-24 11:50:06', '2026-09-25 12:29:37'),
(51, 'HVS75', 'HVS A3 75gsm', 3, 4, 2, 2, 3.0000, 0.000, 20, 11, 500.000, NULL, NULL, 98000.00, 'rim', 'aktif', '2026-09-24 11:51:28', '2026-09-24 17:39:52'),
(52, 'LM-A3', 'Laminasi Doff', 3, 4, 2, 2, 3.0000, 0.000, 17, 18, 200.000, NULL, NULL, 125000.00, 'roll', 'aktif', '2026-09-24 11:53:45', '2026-09-24 17:39:52'),
(53, 'LM-A3G', 'Laminasi Glossy', 3, 4, 2, 2, 2.0000, 1.000, 17, 18, 200.000, NULL, NULL, 125000.00, 'roll', 'aktif', '2026-09-24 11:56:54', '2026-09-24 17:39:52'),
(54, 'PVC', 'PVC ID Card', 3, 4, 2, 2, 2.0000, 1.000, 27, 11, 25.000, NULL, NULL, 820000.00, 'pack', 'aktif', '2026-09-24 12:00:16', '2026-09-24 23:51:23'),
(55, 'SPR', 'Spiral', 3, 4, 2, 2, 1.0000, 1.000, 17, 11, 400.000, NULL, NULL, 1350000.00, 'roll', 'aktif', '2026-09-24 12:01:14', '2026-09-24 17:39:52'),
(56, 'LNY', 'LANYARD 20mm', 6, 6, 2, 2, 5.0000, 1.000, 17, 11, 1.000, NULL, NULL, 150000.00, 'roll', 'aktif', '2026-09-24 12:19:48', '2026-09-24 17:38:34'),
(82, 'TC110', 'TC Textile Import 110cm', 4, 5, NULL, NULL, 0.0000, 1.000, 17, 21, 248.600, 1.100, 226.000, 1815000.00, 'roll', 'aktif', '2026-09-24 17:38:34', '2026-09-25 12:34:45'),
(83, 'ST-CML106', 'STIKER CAMEL 106cm', 1, 2, NULL, NULL, 0.0000, 1.000, 17, 21, 53.000, 1.060, 50.000, 986000.00, 'roll', 'nonaktif', '2026-09-24 17:38:34', '2026-09-26 01:43:26'),
(84, 'ST-CML125', 'STIKER CAMEL 125cm', 1, 2, NULL, NULL, 0.0000, 1.000, 17, 21, 62.500, 1.250, 50.000, 1145000.00, 'roll', 'nonaktif', '2026-09-24 17:38:34', '2026-09-26 01:43:31'),
(85, 'ST-EXI106', 'STIKER EXILO 106cm', 1, 2, NULL, NULL, 0.0000, 1.000, 17, 21, 53.000, 1.060, 50.000, 780000.00, 'roll', 'nonaktif', '2026-09-24 17:38:34', '2026-09-26 01:43:13'),
(86, 'ST-EXI125', 'STIKER EXILO 125cm', 1, 2, NULL, NULL, 0.0000, 1.000, 17, 21, 62.500, 1.250, 50.000, 1050000.00, 'roll', 'nonaktif', '2026-09-24 17:38:34', '2026-09-26 01:43:21'),
(87, 'LMD-DOFF', 'LAMINASI DINGIN DOFF 152cm', 1, 2, NULL, NULL, 1.0000, 1.000, 17, 21, 76.000, 1.520, 50.000, 646000.00, 'roll', 'aktif', '2026-09-24 17:38:34', '2026-09-25 14:43:17'),
(88, 'LMD-GLOSSY', 'LAMINASI DINGIN GLOSSY 152cm', 1, 2, NULL, NULL, 3.0000, 1.000, 17, 21, 76.000, 1.520, 50.000, 646000.00, 'roll', 'aktif', '2026-09-24 17:38:34', '2026-09-27 09:53:51'),
(89, 'LUSTER90', 'LUSTER 90cm', 1, 2, NULL, NULL, 0.0000, 1.000, 17, 21, 45.000, 0.900, 50.000, 1350000.00, 'roll', 'nonaktif', '2026-09-24 17:38:34', '2026-09-26 01:43:40'),
(90, 'LUSTER125', 'LUSTER 125cm', 1, 2, NULL, NULL, 1.0000, 1.000, 17, 21, 62.500, 1.250, 50.000, 1875000.00, 'roll', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(91, 'ROLLUP-STAND', 'ROLL UP (STAND)', 1, 2, NULL, NULL, 5.0000, 1.000, 11, 11, 1.000, NULL, NULL, 90000.00, 'pcs', 'aktif', '2026-09-24 17:38:34', '2026-09-24 23:46:18'),
(92, 'SUNBLAST', 'SUNBLAST', 1, 2, NULL, NULL, 1.0000, 1.000, 17, 21, 63.500, 1.270, 50.000, 1800000.00, 'roll', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(93, 'WALLPAPER', 'WALLPAPER', 1, 2, NULL, NULL, 1.0000, 1.000, 17, 21, 53.000, 1.060, 50.000, 950000.00, 'roll', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(94, 'CLEANER-EPSON', 'CLEANER EPSON S80670', 1, 2, 2, 2, 1.0000, 1.000, 26, 10, 350.000, NULL, NULL, 450000.00, 'botol', 'aktif', '2026-09-24 17:38:34', '2026-09-25 12:22:58'),
(95, 'SOLVENT-FLORA', 'SOLVENT PEMBERSIH FLORA', 2, 3, 2, 2, 5.0000, 1.000, 9, 10, 5000.000, NULL, NULL, 650000.00, 'liter', 'aktif', '2026-09-24 17:38:34', '2026-09-24 23:53:11'),
(96, 'RING', 'RING MATA AYAM', 2, 3, 2, 2, 7.0000, 1.000, 27, 11, 1000.000, NULL, NULL, 95714.29, 'pack', 'aktif', '2026-09-24 17:38:34', '2026-09-27 09:53:53'),
(97, 'RANGKA-XB', 'RANGKA XBANNER', 2, 3, 2, 2, 20.0000, 1.000, 11, 11, 1.000, NULL, NULL, 24000.00, 'pcs', 'aktif', '2026-09-24 17:38:34', '2026-09-24 23:49:25'),
(98, 'LEM-CHINA', 'LEM CHINA', 2, 3, 2, 2, 3.0000, 1.000, 27, 11, 50.000, NULL, NULL, 200000.00, 'pack', 'aktif', '2026-09-24 17:38:34', '2026-09-25 13:04:48'),
(99, 'AMP104', 'AMPLOP 104', 7, 8, NULL, NULL, 10.0000, 1.000, 28, 11, 100.000, NULL, NULL, 19000.00, 'dus', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(100, 'BUKU-REG', 'BUKU REGISTER', 7, 8, NULL, NULL, 0.0000, 1.000, 11, 11, 1.000, NULL, NULL, 25000.00, 'pcs', 'nonaktif', '2026-09-24 17:38:34', '2026-09-25 05:17:14'),
(101, 'GK44', 'GANTUNGAN KUNCI 44mm', 7, 8, NULL, NULL, 5.0000, 1.000, 27, 11, 100.000, NULL, NULL, 250000.00, 'pack', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(102, 'GK54', 'GANTUNGAN KUNCI 54mm', 7, 8, NULL, NULL, 4.0000, 1.000, 27, 11, 100.000, NULL, NULL, 280000.00, 'pack', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(103, 'HVS-A4', 'HVS A4/F4 75gsm', 7, 8, NULL, NULL, 1.0000, 1.000, 20, 18, 500.000, NULL, NULL, 48000.00, 'rim', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(104, 'PIN44', 'PIN 44mm', 7, 8, NULL, NULL, 3.0000, 1.000, 11, 11, 1.000, NULL, NULL, 1200.00, 'pcs', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(105, 'PIN55', 'PIN 55mm', 7, 8, NULL, NULL, 3.0000, 1.000, 11, 11, 1.000, NULL, NULL, 1300.00, 'pcs', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(106, 'TRIPOD', 'TRIPOD BANNER (RANGKA)', 7, 8, NULL, NULL, 1.0000, 1.000, 11, 11, 1.000, NULL, NULL, 180000.00, 'pcs', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:39:52'),
(117, 'TNT-S80670-LC', 'TINTA ECO-SOLVENT EPSON S80670 LIGHT CYAN', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(118, 'TNT-S80670-MG', 'TINTA ECO-SOLVENT EPSON S80670 MAGENTA', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(119, 'TNT-S80670-LM', 'TINTA ECO-SOLVENT EPSON S80670 LIGHT MAGENTA', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(120, 'TNT-S80670-YL', 'TINTA ECO-SOLVENT EPSON S80670 YELLOW', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(121, 'TNT-S80670-BK', 'TINTA ECO-SOLVENT EPSON S80670 BLACK', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(122, 'TNT-S80670-LK', 'TINTA ECO-SOLVENT EPSON S80670 LIGHT BLACK', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(123, 'TNT-S80670-OR', 'TINTA ECO-SOLVENT EPSON S80670 ORANGE', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(124, 'TNT-S80670-RD', 'TINTA ECO-SOLVENT EPSON S80670 RED', 1, 2, 2, 2, 1.0000, 2.000, 26, 10, 700.000, NULL, NULL, 625000.00, 'botol', 'aktif', '2026-09-25 11:44:24', '2026-09-25 11:44:24'),
(125, 'PDK-ROL-PISAU', 'PISAU CUTTER ROLAND G540', 9, 2, NULL, NULL, 1.0000, 1.000, 11, 11, 1.000, NULL, NULL, 450000.00, 'pcs', 'aktif', '2026-09-25 11:44:24', '2026-09-25 12:10:05'),
(126, 'PDK-ROL-BANTALAN', 'BANTALAN PISAU ROLAND G540', 9, 2, NULL, NULL, 0.0000, 1.000, 11, 11, 1.000, NULL, NULL, 150000.00, 'pcs', 'aktif', '2026-09-25 11:44:24', '2026-09-25 12:10:17'),
(136, 'TNT-FLORA-MG', 'TINTA SOLVENT FLORA OUTDOOR MAGENTA', 2, 3, NULL, NULL, 5.0000, 1.000, 9, 10, 5000.000, NULL, NULL, 780000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(137, 'TNT-FLORA-YL', 'TINTA SOLVENT FLORA OUTDOOR YELLOW', 2, 3, NULL, NULL, 5.0000, 1.000, 9, 10, 5000.000, NULL, NULL, 780000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(138, 'TNT-FLORA-BK', 'TINTA SOLVENT FLORA OUTDOOR BLACK', 2, 3, NULL, NULL, 2.0000, 1.000, 9, 10, 5000.000, NULL, NULL, 780000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(139, 'TNT-DTF-MG', 'TINTA DTF MAGENTA', 5, 7, NULL, NULL, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 245000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:32:51'),
(140, 'TNT-DTF-YL', 'TINTA DTF YELLOW', 5, 7, NULL, NULL, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 245000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:33:03'),
(141, 'TNT-DTF-BK', 'TINTA DTF BLACK', 5, 7, NULL, NULL, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 245000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:32:23'),
(142, 'TNT-SUBLIME-MG', 'TINTA SUBLIME MAGENTA', 6, 6, 2, 2, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 620000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(143, 'TNT-SUBLIME-YL', 'TINTA SUBLIME YELLOW', 6, 6, 2, 2, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 620000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(144, 'TNT-SUBLIME-BK', 'TINTA SUBLIME BLACK', 6, 6, 2, 2, 1.0000, 2.000, 9, 10, 1000.000, NULL, NULL, 620000.00, 'liter', 'aktif', '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(145, 'CLN-DTF', 'CLEANER INK DTF', 5, 7, 2, 2, 1.0000, 1.000, 26, 10, 1000.000, NULL, NULL, 170000.00, 'botol', 'aktif', '2026-09-25 12:36:45', '2026-09-25 12:36:45'),
(146, 'KAIN-BRAZIL-KT190', 'KAIN BRAZIL SET KT 190-200', 7, 8, NULL, NULL, 19.6400, 0.000, 7, 7, 1.000, NULL, NULL, 58000.00, 'kg', 'aktif', '2026-09-25 14:41:39', '2026-09-25 14:41:39'),
(147, 'MAIN-EPS-IN', 'Maintenance Kit Epson Indoor', 1, 2, 2, 2, 2.0000, 0.000, 11, 11, 1.000, NULL, NULL, 1805855.50, 'pcs', 'aktif', '2026-09-27 09:48:37', '2026-09-27 09:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `bank_perusahaan`
--

CREATE TABLE `bank_perusahaan` (
  `id_bank` bigint(20) UNSIGNED NOT NULL,
  `nama_bank` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_rekening` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `atas_nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_perusahaan`
--

INSERT INTO `bank_perusahaan` (`id_bank`, `nama_bank`, `nomor_rekening`, `atas_nama`) VALUES
(1, 'BTN', '00046901610002321', 'Robi Wahyudik');

-- --------------------------------------------------------

--
-- Table structure for table `bom_detail`
--

CREATE TABLE `bom_detail` (
  `id_bom` bigint(20) UNSIGNED NOT NULL,
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `cara_hitung_pemakaian` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_detail`
--

INSERT INTO `bom_detail` (`id_bom`, `id_produk`, `id_bahan`, `cara_hitung_pemakaian`, `created_at`, `updated_at`) VALUES
(28, 1, 1, '(P*L)/10000', '2026-09-11 13:27:14', '2026-09-11 13:27:14'),
(30, 5, 4, 'Q', '2026-09-11 13:42:10', '2026-09-11 13:42:10'),
(31, 4, 3, 'Q', '2026-09-11 13:42:17', '2026-09-11 13:42:17'),
(32, 18, 27, '(P*L)/10000', '2026-09-11 13:43:20', '2026-09-11 13:43:20'),
(33, 19, 28, '(P*L)/10000', '2026-09-11 13:43:42', '2026-09-11 13:43:42'),
(34, 20, 29, '(P*L)/10000', '2026-09-11 13:43:58', '2026-09-11 13:43:58'),
(36, 22, 6, '(P*L)/10000', '2026-09-12 14:13:16', '2026-09-12 14:13:16'),
(37, 23, 12, 'Q', '2026-09-12 14:14:09', '2026-09-12 14:14:09'),
(39, 9, 8, '(P*L)/10000', '2026-09-12 16:38:57', '2026-09-12 16:38:57'),
(40, 8, 9, '(P*L)/10000', '2026-09-12 16:39:21', '2026-09-12 16:39:21'),
(41, 7, 7, '(P*L)/10000', '2026-09-12 16:39:49', '2026-09-24 17:38:34'),
(42, 26, 6, 'Q*0.137', '2026-09-12 16:43:41', '2026-09-24 17:39:24'),
(43, 27, 6, 'Q*0.0686', '2026-09-12 16:44:04', '2026-09-24 17:39:24'),
(44, 28, 6, 'Q*0.0342', '2026-09-12 16:44:26', '2026-09-24 17:39:24'),
(45, 29, 6, 'Q*0.011', '2026-09-12 16:44:53', '2026-09-24 17:39:24'),
(146, 107, 3, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(147, 108, 3, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(148, 108, 53, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(149, 109, 3, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(150, 110, 3, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(151, 110, 53, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(152, 111, 3, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(153, 112, 3, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(154, 112, 53, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(155, 113, 3, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(156, 114, 3, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(157, 114, 53, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(158, 115, 3, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(159, 116, 3, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(160, 116, 53, 'Q/1', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(161, 117, 3, 'Q/20', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(162, 118, 3, 'Q/20', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(163, 118, 53, 'Q/20', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(164, 119, 3, 'Q/9', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(165, 120, 3, 'Q/9', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(166, 120, 53, 'Q/9', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(167, 121, 3, 'Q/6', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(168, 122, 3, 'Q/6', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(169, 122, 53, 'Q/6', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(170, 123, 3, 'Q/4', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(171, 124, 3, 'Q/4', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(172, 124, 53, 'Q/4', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(173, 125, 4, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(174, 126, 3, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(175, 127, 34, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(176, 128, 34, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(177, 129, 35, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(178, 130, 35, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(179, 131, 42, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(180, 132, 34, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(181, 133, 34, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(182, 134, 51, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(183, 135, 51, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(184, 136, 3, 'Q*5', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(185, 136, 42, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(186, 137, 3, 'Q*5', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(187, 137, 42, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(188, 138, 52, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(189, 139, 52, 'Q*2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(190, 140, 53, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(191, 141, 53, 'Q*2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(192, 142, 38, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(193, 143, 38, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(194, 144, 4, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(195, 144, 53, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(196, 145, 54, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(197, 146, 55, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(198, 147, 36, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(199, 148, 37, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(200, 149, 39, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(202, 151, 38, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(203, 152, 38, 'Q/2', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(204, 153, 11, 'Q*0.22', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(205, 154, 11, 'Q*0.22', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(206, 154, 88, 'Q*0.22', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(207, 155, 11, 'Q*0.264', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(208, 156, 11, 'Q*0.264', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(209, 156, 88, 'Q*0.264', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(210, 157, 11, 'Q*0.33', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(211, 158, 11, 'Q*0.33', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(212, 158, 88, 'Q*0.33', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(213, 159, 11, 'Q*0.4125', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(214, 160, 11, 'Q*0.4125', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(215, 160, 88, 'Q*0.4125', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(216, 161, 11, 'Q*0.528', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(217, 162, 11, 'Q*0.528', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(218, 162, 88, 'Q*0.528', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(219, 163, 11, 'Q*0.594', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(220, 164, 11, 'Q*0.594', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(221, 164, 88, 'Q*0.594', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(222, 165, 32, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(223, 166, 83, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(224, 167, 84, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(225, 168, 10, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(226, 169, 90, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(227, 170, 11, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(228, 171, 31, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(229, 172, 85, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(230, 173, 86, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(231, 174, 93, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(232, 175, 31, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(233, 175, 87, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(234, 176, 91, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(235, 176, 32, 'Q*0.792', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(236, 177, 91, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(237, 177, 32, 'Q*1.76', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(238, 178, 30, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(239, 179, 33, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(240, 180, 1, '(P*L)/10000', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(241, 181, 96, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(242, 182, 97, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(243, 182, 29, 'Q*1.056', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(244, 183, 106, 'Q', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(245, 183, 29, 'Q*1.056', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(246, 150, 35, 'Q', '2026-09-25 15:09:11', '2026-09-25 15:09:11'),
(247, 150, 4, 'Q', '2026-09-25 15:09:11', '2026-09-25 15:09:11'),
(248, 2, 2, '(P*L)/10000', '2026-09-27 13:57:42', '2026-09-27 13:57:42'),
(249, 3, 5, '(P*L)/10000', '2026-09-27 13:58:06', '2026-09-27 13:58:06');

-- --------------------------------------------------------

--
-- Table structure for table `bom_finishing`
--

CREATE TABLE `bom_finishing` (
  `id_bom_finishing` bigint(20) UNSIGNED NOT NULL,
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `id_finishing` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 1.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-0b3d9fbb75469a28b139c52445d7b18c', 'i:1;', 1790510278),
('laravel-cache-0b3d9fbb75469a28b139c52445d7b18c:timer', 'i:1790510278;', 1790510278),
('laravel-cache-251844a3d6aca7a55cc3448898a706a6', 'i:1;', 1790510111),
('laravel-cache-251844a3d6aca7a55cc3448898a706a6:timer', 'i:1790510111;', 1790510111),
('laravel-cache-349354142efbfd03af1faf6e2011a159', 'i:1;', 1790510206),
('laravel-cache-349354142efbfd03af1faf6e2011a159:timer', 'i:1790510206;', 1790510206),
('laravel-cache-68a45d0b380bed750569e6b08a675790', 'i:1;', 1790510800),
('laravel-cache-68a45d0b380bed750569e6b08a675790:timer', 'i:1790510800;', 1790510800),
('laravel-cache-a4711fc51e15ab4f7c96b54b6699f5ff', 'i:1;', 1790519979),
('laravel-cache-a4711fc51e15ab4f7c96b54b6699f5ff:timer', 'i:1790519979;', 1790519979),
('laravel-cache-app_settings_current', 'O:21:\"App\\Models\\AppSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"app_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:21:{s:2:\"id\";i:1;s:13:\"nama_aplikasi\";s:13:\"ORION GRAFIKA\";s:6:\"alamat\";s:59:\"Jalan Jaksa Agung Suprapto No. 48 Pliyang - Madura, Sampang\";s:7:\"telepon\";s:12:\"082334075550\";s:3:\"fax\";s:1:\"-\";s:5:\"email\";s:22:\"grafikaorion@gmail.com\";s:7:\"website\";s:23:\"http://oriongrafika.com\";s:16:\"libur_kas_minggu\";i:1;s:11:\"format_nota\";s:7:\"thermal\";s:17:\"nomor_nota_prefix\";s:3:\"ORG\";s:16:\"nota_footer_text\";s:103:\"Pembayaran ke Nomor Rekening : BCA : 896 0407 068  |  BRI : 01480 1001 350561\r\nAn. Khaironi Firdiansyah\";s:15:\"pembulatan_nota\";s:4:\"1000\";s:9:\"logo_path\";s:57:\"company_logo/TQuRE7SCpMfI5Ryfac28qypqAk6wVD3T4phfJiXl.png\";s:10:\"created_at\";s:19:\"2026-09-12 12:23:14\";s:10:\"updated_at\";s:19:\"2026-09-25 21:59:07\";s:11:\"logo_ukuran\";s:6:\"sedang\";s:9:\"logo_gaya\";s:5:\"polos\";s:12:\"logo_ringkas\";s:6:\"potong\";s:14:\"logo_ikon_path\";N;s:16:\"batasi_jam_kerja\";i:0;s:9:\"url_akses\";s:40:\"http://192.168.1.5/percetakan-app/public\";}s:11:\"\0*\0original\";a:21:{s:2:\"id\";i:1;s:13:\"nama_aplikasi\";s:13:\"ORION GRAFIKA\";s:6:\"alamat\";s:59:\"Jalan Jaksa Agung Suprapto No. 48 Pliyang - Madura, Sampang\";s:7:\"telepon\";s:12:\"082334075550\";s:3:\"fax\";s:1:\"-\";s:5:\"email\";s:22:\"grafikaorion@gmail.com\";s:7:\"website\";s:23:\"http://oriongrafika.com\";s:16:\"libur_kas_minggu\";i:1;s:11:\"format_nota\";s:7:\"thermal\";s:17:\"nomor_nota_prefix\";s:3:\"ORG\";s:16:\"nota_footer_text\";s:103:\"Pembayaran ke Nomor Rekening : BCA : 896 0407 068  |  BRI : 01480 1001 350561\r\nAn. Khaironi Firdiansyah\";s:15:\"pembulatan_nota\";s:4:\"1000\";s:9:\"logo_path\";s:57:\"company_logo/TQuRE7SCpMfI5Ryfac28qypqAk6wVD3T4phfJiXl.png\";s:10:\"created_at\";s:19:\"2026-09-12 12:23:14\";s:10:\"updated_at\";s:19:\"2026-09-25 21:59:07\";s:11:\"logo_ukuran\";s:6:\"sedang\";s:9:\"logo_gaya\";s:5:\"polos\";s:12:\"logo_ringkas\";s:6:\"potong\";s:14:\"logo_ikon_path\";N;s:16:\"batasi_jam_kerja\";i:0;s:9:\"url_akses\";s:40:\"http://192.168.1.5/percetakan-app/public\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:16:\"libur_kas_minggu\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:18:{i:0;s:13:\"nama_aplikasi\";i:1;s:6:\"alamat\";i:2;s:7:\"telepon\";i:3;s:3:\"fax\";i:4;s:5:\"email\";i:5;s:7:\"website\";i:6;s:9:\"url_akses\";i:7;s:9:\"logo_path\";i:8;s:11:\"logo_ukuran\";i:9;s:9:\"logo_gaya\";i:10;s:12:\"logo_ringkas\";i:11;s:14:\"logo_ikon_path\";i:12;s:16:\"libur_kas_minggu\";i:13;s:16:\"batasi_jam_kerja\";i:14;s:11:\"format_nota\";i:15;s:17:\"nomor_nota_prefix\";i:16;s:16:\"nota_footer_text\";i:17;s:15:\"pembulatan_nota\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}', 2105740042),
('laravel-cache-notif_user_1', 'a:2:{s:5:\"total\";i:39;s:5:\"items\";a:2:{i:0;a:5:{s:3:\"key\";s:4:\"stok\";s:5:\"label\";s:35:\"Bahan habis / di bawah stok minimal\";s:5:\"count\";i:36;s:3:\"url\";s:57:\"http://localhost/percetakan-app/public/laporan/stok-bahan\";s:4:\"tone\";s:4:\"rose\";}i:1;a:5:{s:3:\"key\";s:6:\"hutang\";s:5:\"label\";s:34:\"Faktur hutang jatuh tempo (7 hari)\";s:5:\"count\";i:3;s:3:\"url\";s:54:\"http://localhost/percetakan-app/public/pembelian-bahan\";s:4:\"tone\";s:4:\"rose\";}}}', 1790520555),
('laravel-cache-notif_user_14', 'a:2:{s:5:\"total\";i:0;s:5:\"items\";a:0:{}}', 1790510217),
('laravel-cache-notif_user_24', 'a:2:{s:5:\"total\";i:1;s:5:\"items\";a:1:{i:0;a:5:{s:3:\"key\";s:11:\"kasir-bayar\";s:5:\"label\";s:18:\"Nota belum dibayar\";s:5:\"count\";i:1;s:3:\"url\";s:58:\"http://192.168.1.17/percetakan-app/public/kasir/monitoring\";s:4:\"tone\";s:5:\"amber\";}}}', 1790520051),
('laravel-cache-notif_user_25', 'a:2:{s:5:\"total\";i:0;s:5:\"items\";a:0:{}}', 1790520564),
('laravel-cache-notif_user_4', 'a:2:{s:5:\"total\";i:0;s:5:\"items\";a:0:{}}', 1790510140),
('laravel-cache-notif_user_5', 'a:2:{s:5:\"total\";i:0;s:5:\"items\";a:0:{}}', 1790387488),
('laravel-cache-sinyal_perubahan', 'a:2:{s:1:\"v\";s:12:\"f5badf49494a\";s:8:\"approval\";i:0;}', 1790520555);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `design_proofs`
--

CREATE TABLE `design_proofs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `revisi_no` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `nama_file_asli` varchar(255) NOT NULL,
  `ukuran_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `catatan` text DEFAULT NULL,
  `status` enum('Menunggu Review','Disetujui','Revisi Diminta','Digantikan','Dibuka Kembali') NOT NULL DEFAULT 'Menunggu Review',
  `id_user_upload` bigint(20) UNSIGNED DEFAULT NULL,
  `keputusan_kanal` varchar(30) DEFAULT NULL,
  `keputusan_catatan` text DEFAULT NULL,
  `keputusan_at` timestamp NULL DEFAULT NULL,
  `id_user_keputusan` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_number_counters`
--

CREATE TABLE `document_number_counters` (
  `kode_dokumen` varchar(20) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `counter` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_number_counters`
--

INSERT INTO `document_number_counters` (`kode_dokumen`, `periode`, `counter`, `created_at`, `updated_at`) VALUES
('ORG', '20260927', 1, '2026-09-27 14:26:42', '2026-09-27 14:26:42');

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
-- Table structure for table `finishing`
--

CREATE TABLE `finishing` (
  `id_finishing` bigint(20) UNSIGNED NOT NULL,
  `nama_finishing` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga_default` decimal(15,2) DEFAULT 0.00,
  `id_satuan` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_kategori` bigint(20) UNSIGNED DEFAULT NULL,
  `id_mesin` bigint(20) UNSIGNED DEFAULT NULL,
  `metode_hitung` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finishing`
--

INSERT INTO `finishing` (`id_finishing`, `nama_finishing`, `harga_default`, `id_satuan`, `created_at`, `updated_at`, `id_kategori`, `id_mesin`, `metode_hitung`, `keterangan`) VALUES
(1, 'Kisscut', 20000.00, NULL, '2026-03-07 16:33:27', '2026-09-17 12:24:00', 2, 1, 'luas', 'Harga berlaku per meter persegi'),
(5, 'Hot Laminasi Doff/Glossy', 2000.00, NULL, '2026-09-11 07:39:08', '2026-09-18 12:41:34', 4, 3, 'quantity', 'Menggunakan range harga'),
(6, 'Mata Ayam', 700.00, NULL, '2026-09-11 13:46:04', '2026-09-17 12:24:09', 3, 2, 'quantity', 'Mata ayam (pcs)'),
(7, 'Press Hidrolik', 3000.00, NULL, '2026-09-12 16:45:44', '2026-09-17 12:27:08', 7, 5, 'quantity', 'Jasa Press dengan mesin  Hidrolik'),
(8, 'Jahit Keliling', 1500.00, NULL, '2026-09-17 12:42:27', '2026-09-18 06:33:51', 5, 4, 'keliling', 'Jahit tepi produk, dihitung dari keliling (2 x (P+L))'),
(9, 'Pemasangan Tali', 500.00, NULL, '2026-09-17 12:42:27', '2026-09-18 06:33:35', 5, 4, 'quantity', 'Penambahan jika tali lebih dari 6 pcs'),
(10, 'Laminasi Dingin Doff/Glossy', 15000.00, NULL, '2026-09-25 12:32:28', '2026-09-25 12:32:28', 2, 1, 'luas', 'Laminasi dingin indoor (Epson), harga per meter persegi. Pilih doff atau glossy di catatan produksi.');

-- --------------------------------------------------------

--
-- Table structure for table `finishing_harga`
--

CREATE TABLE `finishing_harga` (
  `id_finishing_harga` bigint(20) UNSIGNED NOT NULL,
  `id_finishing` bigint(20) UNSIGNED NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finishing_harga`
--

INSERT INTO `finishing_harga` (`id_finishing_harga`, `id_finishing`, `range_min`, `range_max`, `harga`, `created_at`, `updated_at`) VALUES
(8, 5, 1.00, 20.00, 2000.00, '2026-09-11 13:45:12', '2026-09-11 13:45:12'),
(9, 5, 21.00, 50.00, 1750.00, '2026-09-11 13:45:19', '2026-09-11 13:45:19'),
(10, 5, 51.00, 100.00, 1500.00, '2026-09-11 13:45:39', '2026-09-11 13:45:39'),
(11, 10, 0.01, 4.99, 15000.00, '2026-09-25 12:32:28', '2026-09-25 12:32:28'),
(12, 10, 5.00, 19.99, 13500.00, '2026-09-25 12:32:28', '2026-09-25 12:32:28'),
(13, 10, 20.00, NULL, 12000.00, '2026-09-25 12:32:28', '2026-09-25 12:32:28');

-- --------------------------------------------------------

--
-- Table structure for table `golongan_konsumen`
--

CREATE TABLE `golongan_konsumen` (
  `id_golongan` bigint(20) UNSIGNED NOT NULL,
  `nama_golongan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `golongan_konsumen`
--

INSERT INTO `golongan_konsumen` (`id_golongan`, `nama_golongan`, `keterangan`) VALUES
(1, 'Retail', 'Harga umum'),
(2, 'Reseller', 'Harga untuk member tetap'),
(3, 'Member Platinum', 'Harga khusus pelanggan dari owner pribadi'),
(4, 'Member Foto', 'Khusus member cetak foto saja'),
(6, 'Sponsor', 'Persetujuan dari Manajemen untuk menggunakan harga dengan golongan sponsor'),
(7, 'Asosiasi', 'Khusus Asosiasi percetakan madura');

-- --------------------------------------------------------

--
-- Table structure for table `gudang`
--

CREATE TABLE `gudang` (
  `id_gudang` bigint(20) UNSIGNED NOT NULL,
  `nama_gudang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `penanggung_jawab` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telepon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gudang`
--

INSERT INTO `gudang` (`id_gudang`, `nama_gudang`, `alamat`, `penanggung_jawab`, `telepon`) VALUES
(1, 'Gudang Utama', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `harga_promo`
--

CREATE TABLE `harga_promo` (
  `id_promo` bigint(20) UNSIGNED NOT NULL,
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `harga_promo` decimal(15,2) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `status_promo` enum('aktif','non-aktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `harga_promo`
--

INSERT INTO `harga_promo` (`id_promo`, `id_produk`, `harga_promo`, `tgl_mulai`, `tgl_selesai`, `status_promo`) VALUES
(2, 1, 13000.00, '2026-09-10', '2026-09-18', 'non-aktif'),
(3, 1, 10000.00, '2026-09-01', '2026-09-30', 'non-aktif');

-- --------------------------------------------------------

--
-- Table structure for table `harga_siap_cetak`
--

CREATE TABLE `harga_siap_cetak` (
  `id_harga_siap_cetak` bigint(20) UNSIGNED NOT NULL,
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `id_golongan` bigint(20) UNSIGNED NOT NULL,
  `range_min` decimal(12,2) NOT NULL DEFAULT 0.00,
  `range_max` decimal(12,2) DEFAULT NULL,
  `harga_jual` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jam_kerja`
--

CREATE TABLE `jam_kerja` (
  `hari` tinyint(3) UNSIGNED NOT NULL,
  `buka` tinyint(1) NOT NULL DEFAULT 1,
  `jam_mulai` time NOT NULL DEFAULT '08:00:00',
  `jam_selesai` time NOT NULL DEFAULT '17:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jam_kerja`
--

INSERT INTO `jam_kerja` (`hari`, `buka`, `jam_mulai`, `jam_selesai`) VALUES
(1, 1, '08:00:00', '17:00:00'),
(2, 1, '08:00:00', '17:00:00'),
(3, 1, '08:00:00', '17:00:00'),
(4, 1, '08:00:00', '17:00:00'),
(5, 1, '08:00:00', '17:00:00'),
(6, 1, '08:00:00', '17:00:00'),
(7, 1, '08:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `jasa`
--

CREATE TABLE `jasa` (
  `id_jasa` bigint(20) UNSIGNED NOT NULL,
  `nama_jasa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_satuan` bigint(20) UNSIGNED DEFAULT NULL,
  `harga_default` decimal(15,2) DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jasa`
--

INSERT INTO `jasa` (`id_jasa`, `nama_jasa`, `id_satuan`, `harga_default`, `keterangan`, `created_at`, `updated_at`) VALUES
(3, 'Pasang Stiker', 21, 50000.00, NULL, '2026-09-10 09:00:16', '2026-09-12 17:01:20'),
(6, 'Jasa Print', 14, 35000.00, 'Print saja (media dari pelanggan). Dokumen: JASA PRINT Rp 35.000/m.', '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(7, 'Press Hidrolik', 11, 3000.00, 'Dokumen kelompok DTF: Press Hidrolik Rp 3.000.', '2026-09-24 17:38:34', '2026-09-24 17:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `jasa_harga`
--

CREATE TABLE `jasa_harga` (
  `id_jasa_harga` bigint(20) UNSIGNED NOT NULL,
  `id_jasa` bigint(20) UNSIGNED NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_bahan`
--

CREATE TABLE `jenis_bahan` (
  `id_jenis_bahan` bigint(20) UNSIGNED NOT NULL,
  `nama_jenis` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_bahan`
--

INSERT INTO `jenis_bahan` (`id_jenis_bahan`, `nama_jenis`, `keterangan`, `created_at`, `updated_at`) VALUES
(2, 'Universal', NULL, '2026-09-10 07:53:57', '2026-09-10 07:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_categories`
--

CREATE TABLE `jersey_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_categories`
--

INSERT INTO `jersey_categories` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Jersey Futsal', 0, '2026-09-12 12:27:40', '2026-09-12 13:40:06'),
(2, 'Jersey Bola', 0, '2026-09-12 12:27:40', '2026-09-12 13:14:21'),
(3, 'Jersey Printing', 1, '2026-09-12 12:27:40', '2026-09-12 12:27:40'),
(4, 'Jersey Esports', 0, '2026-09-12 12:27:40', '2026-09-12 13:14:22'),
(5, 'Jersey Custom', 0, '2026-09-12 12:27:40', '2026-09-12 13:14:20'),
(6, 'Sportswear', 0, '2026-09-16 12:45:47', '2026-09-16 14:02:44'),
(7, 'Event/Gathering', 0, '2026-09-16 12:45:56', '2026-09-16 14:02:45'),
(8, 'Merchandise', 0, '2026-09-16 12:46:03', '2026-09-16 14:02:44'),
(9, 'Jersey Sablon DTF', 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(10, 'Jersey Bordir', 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_collar_types`
--

CREATE TABLE `jersey_collar_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_collar_types`
--

INSERT INTO `jersey_collar_types` (`id`, `name`, `price_surcharge`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Vneck', 0.00, 0, 1, '2026-09-24 03:20:13', '2026-09-24 03:20:13'),
(2, 'Oneck', 0.00, 0, 1, '2026-09-24 03:20:16', '2026-09-24 03:20:16'),
(3, 'Wangki', 15000.00, 0, 1, '2026-09-24 03:20:20', '2026-09-24 03:20:20'),
(4, 'Shanghai Zip', 20000.00, 0, 1, '2026-09-24 03:20:26', '2026-09-24 03:20:38'),
(5, 'Vneck Variasi', 5000.00, 0, 1, '2026-09-27 12:36:22', '2026-09-27 12:36:22');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_components`
--

CREATE TABLE `jersey_components` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `jersey_product_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_components`
--

INSERT INTO `jersey_components` (`id`, `name`, `jersey_product_type_id`, `jersey_category_id`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Body', NULL, NULL, 1, 1, '2026-09-12 13:58:37', '2026-09-16 14:14:39'),
(2, 'Lengan', NULL, NULL, 2, 0, '2026-09-12 13:58:37', '2026-09-18 16:55:57'),
(3, 'Rib', NULL, NULL, 3, 1, '2026-09-12 13:58:37', '2026-09-12 13:58:37'),
(4, 'Celana', 2, NULL, 4, 1, '2026-09-12 13:58:37', '2026-09-16 10:29:47'),
(5, 'Lengan panjang', 1, 6, 0, 0, '2026-09-12 14:29:38', '2026-09-16 14:15:50'),
(6, 'Rib Wangki', NULL, NULL, 0, 0, '2026-09-12 15:03:24', '2026-09-18 16:55:46'),
(7, 'Celana Panjang', 4, NULL, 5, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_designers`
--

CREATE TABLE `jersey_designers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_designers`
--

INSERT INTO `jersey_designers` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Rahman', 1, '2026-09-12 13:39:37', '2026-09-12 13:39:37'),
(2, 'Rehan', 1, '2026-09-12 13:39:40', '2026-09-12 13:39:40'),
(3, 'FILE READY', 1, '2026-09-25 06:28:21', '2026-09-25 06:28:21'),
(4, 'Administrasi', 1, '2026-09-27 12:35:37', '2026-09-27 12:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_materials`
--

CREATE TABLE `jersey_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_bahan` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_materials`
--

INSERT INTO `jersey_materials` (`id`, `name`, `is_active`, `created_at`, `updated_at`, `id_bahan`) VALUES
(1, 'Brazil', 1, '2026-09-12 14:24:04', '2026-09-25 14:41:39', 146),
(2, 'Milano', 1, '2026-09-12 14:24:10', '2026-09-12 14:24:10', NULL),
(3, 'Nike', 1, '2026-09-12 14:24:15', '2026-09-12 14:24:15', NULL),
(4, 'Billabong', 1, '2026-09-12 14:24:31', '2026-09-12 14:24:31', NULL),
(5, 'Cotton Combed', 1, '2026-09-12 14:24:40', '2026-09-12 14:24:40', NULL),
(6, 'Benzema', 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', NULL),
(7, 'Dryfit', 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', NULL),
(8, 'Jacquard', 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', NULL),
(9, 'Lacoste Pique', 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jersey_orders`
--

CREATE TABLE `jersey_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `id_konsumen` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `team_name` varchar(255) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('baru','proses','selesai','diambil') NOT NULL DEFAULT 'baru',
  `jersey_designer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `designer_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` enum('Belum Bayar','DP','Lunas') NOT NULL DEFAULT 'Belum Bayar',
  `catatan_referensi_transaksi` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_terjurnal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stok_dipotong_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_orders`
--

INSERT INTO `jersey_orders` (`id`, `order_number`, `order_date`, `id_konsumen`, `customer_name`, `customer_phone`, `team_name`, `deadline`, `status`, `jersey_designer_id`, `designer_name`, `notes`, `total`, `discount`, `additional_charge`, `deposit`, `status_pembayaran`, `catatan_referensi_transaksi`, `created_by`, `created_at`, `updated_at`, `total_terjurnal`, `stok_dipotong_at`) VALUES
(20, 'JO-20260925-001', '2026-09-25', 15, 'Ardi FPTI', '-', 'FPTI', '2026-09-26', 'baru', 3, 'FILE READY', NULL, 128640.00, 0.00, 0.00, 0.00, 'Belum Bayar', NULL, 1, '2026-09-25 06:31:28', '2026-09-25 07:35:14', 128640.00, NULL),
(21, 'JO-20260927-001', '2026-09-27', 16, 'Dias Kecamatan Robatal', '-', 'Kecamatan Robatal', '2026-10-02', 'baru', 3, 'FILE READY', NULL, 2280000.00, 0.00, 0.00, 0.00, 'Belum Bayar', NULL, 1, '2026-09-27 09:30:29', '2026-09-27 09:30:29', 2280000.00, NULL),
(22, 'JO-20260927-002', '2026-09-23', 17, 'Muhammad Effendy', '-', 'DPD REI Komisariat Madura', '2026-09-28', 'selesai', 4, 'Administrasi', NULL, 2152600.00, 0.00, 0.00, 0.00, 'Belum Bayar', NULL, 1, '2026-09-27 12:59:24', '2026-09-27 13:00:58', 2152600.00, '2026-09-27 13:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_order_items`
--

CREATE TABLE `jersey_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_order_line_id` bigint(20) UNSIGNED NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `player_number` varchar(255) DEFAULT NULL,
  `size` enum('S','M','L','XL','2XL','3XL','4XL','5XL') NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `sleeve` varchar(50) DEFAULT NULL,
  `jersey_sleeve_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `collar` varchar(255) DEFAULT NULL,
  `jersey_collar_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jersey_order_lines`
--

CREATE TABLE `jersey_order_lines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_order_id` bigint(20) UNSIGNED NOT NULL,
  `jersey_product_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_material_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_sleeve_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_collar_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `bahan` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `order_type` enum('quantity_only','player_list','mixed') NOT NULL DEFAULT 'quantity_only',
  `is_susulan` tinyint(1) NOT NULL DEFAULT 0,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `harga_satuan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `size_surcharge_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sleeve_collar_surcharge_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jersey_price_setting_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_order_lines`
--

INSERT INTO `jersey_order_lines` (`id`, `jersey_order_id`, `jersey_product_type_id`, `jersey_category_id`, `jersey_material_id`, `jersey_sleeve_type_id`, `jersey_collar_type_id`, `nama_barang`, `bahan`, `keterangan`, `thumbnail`, `order_type`, `is_susulan`, `qty`, `harga_satuan`, `subtotal`, `size_surcharge_total`, `sleeve_collar_surcharge_total`, `jersey_price_setting_id`, `created_at`, `updated_at`) VALUES
(109, 20, 1, 3, 1, 1, 1, 'Seragam FPTI', 'Brazil', NULL, 'jersey_thumbnails/PGSkzi6Y5xSRM6OfInnG6J8xRPLxBPeZNtjlSWPc.jpg', 'quantity_only', 0, 2, 64000.00, 128640.00, 640.00, 0.00, 5, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(110, 21, 1, 3, 1, 2, 1, 'Kecamatan Robatal', 'Brazil', 'Pertemuan Lengan dan pundak', 'jersey_thumbnails/YqiEoSRnHeOGsPozpNpAHDOVxijazDWlGduRbdQA.jpg', 'quantity_only', 0, 28, 80000.00, 2280000.00, 40000.00, 0.00, 5, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(111, 22, 1, 3, 1, 1, 5, 'Jersey Tim Padel', 'Brazil', NULL, 'jersey_thumbnails/HwltD7C3eoh5JVoukG4oydnp4F6LWEDI3rd0fju1.jpg', 'quantity_only', 0, 25, 79360.00, 2152600.00, 43600.00, 125000.00, 5, '2026-09-27 12:59:25', '2026-09-27 12:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_order_line_materials`
--

CREATE TABLE `jersey_order_line_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_order_line_id` bigint(20) UNSIGNED NOT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `warna` varchar(255) DEFAULT NULL,
  `penggunaan_kg` decimal(10,2) DEFAULT NULL,
  `penggunaan_meter` decimal(10,2) DEFAULT NULL,
  `penggunaan_yard` decimal(10,2) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_order_line_materials`
--

INSERT INTO `jersey_order_line_materials` (`id`, `jersey_order_line_id`, `nama_barang`, `warna`, `penggunaan_kg`, `penggunaan_meter`, `penggunaan_yard`, `keterangan`, `created_at`, `updated_at`) VALUES
(130, 108, 'Body', NULL, 0.35, 1.57, NULL, NULL, '2026-09-25 06:31:28', '2026-09-25 06:31:28'),
(131, 108, 'Rib', NULL, 0.01, 0.06, NULL, NULL, '2026-09-25 06:31:28', '2026-09-25 06:31:28'),
(132, 108, 'Lengan Pendek', NULL, 0.09, 0.40, NULL, NULL, '2026-09-25 06:31:28', '2026-09-25 06:31:28'),
(133, 108, 'Kerah Vneck', NULL, 0.02, 0.20, NULL, NULL, '2026-09-25 06:31:28', '2026-09-25 06:31:28'),
(134, 109, 'Body', NULL, 0.24, 1.07, NULL, NULL, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(135, 109, 'Rib', NULL, 0.01, 0.05, NULL, NULL, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(136, 109, 'Lengan Pendek', NULL, 0.06, 0.27, NULL, NULL, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(137, 109, 'Kerah Vneck', NULL, 0.01, 0.15, NULL, NULL, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(138, 110, 'Body', NULL, 4.93, 21.98, NULL, NULL, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(139, 110, 'Rib', NULL, 0.14, 0.84, NULL, NULL, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(140, 110, 'Lengan Panjang', NULL, 2.94, 13.10, NULL, NULL, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(141, 110, 'Kerah Vneck', NULL, 0.24, 2.80, NULL, NULL, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(142, 111, 'Body', NULL, 4.46, 19.84, NULL, NULL, '2026-09-27 12:59:25', '2026-09-27 12:59:25'),
(143, 111, 'Rib', NULL, 0.12, 0.74, NULL, NULL, '2026-09-27 12:59:25', '2026-09-27 12:59:25'),
(144, 111, 'Lengan Pendek', NULL, 1.13, 5.04, NULL, NULL, '2026-09-27 12:59:25', '2026-09-27 12:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_order_payments`
--

CREATE TABLE `jersey_order_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_order_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `metode_bayar` enum('Tunai','Transfer','QRIS','Lainnya') NOT NULL DEFAULT 'Tunai',
  `bukti_path` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `no_referensi_jurnal` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jersey_order_potong_stok`
--

CREATE TABLE `jersey_order_potong_stok` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_order_id` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(14,4) NOT NULL,
  `biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_order_potong_stok`
--

INSERT INTO `jersey_order_potong_stok` (`id`, `jersey_order_id`, `id_bahan`, `qty`, `biaya`, `created_at`, `updated_at`) VALUES
(1, 22, 146, 5.7100, 331180.00, '2026-09-27 13:00:58', '2026-09-27 13:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_order_size_quantities`
--

CREATE TABLE `jersey_order_size_quantities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_order_line_id` bigint(20) UNSIGNED NOT NULL,
  `jersey_size_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_order_size_quantities`
--

INSERT INTO `jersey_order_size_quantities` (`id`, `jersey_order_line_id`, `jersey_size_id`, `quantity`, `created_at`, `updated_at`) VALUES
(55, 108, 2, 1, '2026-09-25 06:31:28', '2026-09-25 06:31:28'),
(56, 108, 4, 1, '2026-09-25 06:31:28', '2026-09-25 06:31:28'),
(57, 109, 17, 1, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(58, 109, 19, 1, '2026-09-25 07:35:14', '2026-09-25 07:35:14'),
(59, 110, 1, 2, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(60, 110, 2, 8, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(61, 110, 3, 10, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(62, 110, 4, 4, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(63, 110, 5, 4, '2026-09-27 09:30:29', '2026-09-27 09:30:29'),
(64, 111, 15, 1, '2026-09-27 12:59:25', '2026-09-27 12:59:25'),
(65, 111, 2, 1, '2026-09-27 12:59:25', '2026-09-27 12:59:25'),
(66, 111, 3, 12, '2026-09-27 12:59:25', '2026-09-27 12:59:25'),
(67, 111, 4, 6, '2026-09-27 12:59:25', '2026-09-27 12:59:25'),
(68, 111, 5, 5, '2026-09-27 12:59:25', '2026-09-27 12:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_price_settings`
--

CREATE TABLE `jersey_price_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_product_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jersey_material_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty_min` int(10) UNSIGNED NOT NULL,
  `qty_max` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kelompok` varchar(10) NOT NULL DEFAULT 'dewasa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_price_settings`
--

INSERT INTO `jersey_price_settings` (`id`, `jersey_product_type_id`, `jersey_category_id`, `jersey_material_id`, `qty_min`, `qty_max`, `price`, `is_active`, `created_at`, `updated_at`, `kelompok`) VALUES
(5, 1, 3, 1, 1, 50, 80000.00, 1, '2026-09-12 15:05:16', '2026-09-12 15:05:16', 'dewasa'),
(6, 1, 3, 1, 51, 100, 75000.00, 1, '2026-09-12 15:05:30', '2026-09-12 15:05:30', 'dewasa'),
(7, 2, 3, 1, 1, 30, 30000.00, 1, '2026-09-12 15:06:15', '2026-09-12 15:06:15', 'dewasa'),
(8, 2, 3, 1, 31, 100, 27000.00, 1, '2026-09-12 15:06:28', '2026-09-12 15:06:28', 'dewasa'),
(9, 3, 3, 1, 1, 50, 65000.00, 1, '2026-09-12 16:34:29', '2026-09-12 16:34:29', 'dewasa'),
(10, 1, NULL, NULL, 1, 11, 90000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(11, 1, NULL, NULL, 12, 23, 85000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(12, 1, NULL, NULL, 24, 49, 80000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(13, 1, NULL, NULL, 50, NULL, 75000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(14, 3, NULL, NULL, 1, 11, 75000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(15, 3, NULL, NULL, 12, 23, 70000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(16, 3, NULL, NULL, 24, 49, 65000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(17, 3, NULL, NULL, 50, NULL, 60000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(18, 2, NULL, NULL, 1, 11, 35000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(19, 2, NULL, NULL, 12, 23, 32000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(20, 2, NULL, NULL, 24, 49, 30000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(21, 2, NULL, NULL, 50, NULL, 27000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(22, 4, NULL, NULL, 1, 11, 50000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(23, 4, NULL, NULL, 12, 23, 47000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(24, 4, NULL, NULL, 24, 49, 45000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(25, 4, NULL, NULL, 50, NULL, 42000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(26, 5, NULL, NULL, 1, 11, 105000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(27, 5, NULL, NULL, 12, 23, 98000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(28, 5, NULL, NULL, 24, 49, 92000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(29, 5, NULL, NULL, 50, NULL, 88000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(30, 6, NULL, NULL, 1, 11, 100000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(31, 6, NULL, NULL, 12, 23, 92000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(32, 6, NULL, NULL, 24, 49, 85000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(33, 6, NULL, NULL, 50, NULL, 80000.00, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58', 'dewasa'),
(63, 1, 3, 1, 1, 50, 64000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(64, 1, 3, 1, 51, 100, 60000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(65, 2, 3, 1, 1, 30, 24000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(66, 2, 3, 1, 31, 100, 21500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(67, 3, 3, 1, 1, 50, 52000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(68, 1, NULL, NULL, 1, 11, 72000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(69, 1, NULL, NULL, 12, 23, 68000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(70, 1, NULL, NULL, 24, 49, 64000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(71, 1, NULL, NULL, 50, NULL, 60000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(72, 3, NULL, NULL, 1, 11, 60000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(73, 3, NULL, NULL, 12, 23, 56000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(74, 3, NULL, NULL, 24, 49, 52000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(75, 3, NULL, NULL, 50, NULL, 48000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(76, 2, NULL, NULL, 1, 11, 28000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(77, 2, NULL, NULL, 12, 23, 25500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(78, 2, NULL, NULL, 24, 49, 24000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(79, 2, NULL, NULL, 50, NULL, 21500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(80, 4, NULL, NULL, 1, 11, 40000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(81, 4, NULL, NULL, 12, 23, 37500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(82, 4, NULL, NULL, 24, 49, 36000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(83, 4, NULL, NULL, 50, NULL, 33500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(84, 5, NULL, NULL, 1, 11, 84000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(85, 5, NULL, NULL, 12, 23, 78500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(86, 5, NULL, NULL, 24, 49, 73500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(87, 5, NULL, NULL, 50, NULL, 70500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(88, 6, NULL, NULL, 1, 11, 80000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(89, 6, NULL, NULL, 12, 23, 73500.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(90, 6, NULL, NULL, 24, 49, 68000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak'),
(91, 6, NULL, NULL, 50, NULL, 64000.00, 1, '2026-09-25 07:32:37', '2026-09-25 07:32:37', 'anak');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_product_types`
--

CREATE TABLE `jersey_product_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `has_sleeve_collar` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_product_types`
--

INSERT INTO `jersey_product_types` (`id`, `name`, `has_sleeve_collar`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Jersey', 1, 1, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(2, 'Celana Pendek', 0, 1, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(3, 'Training', 1, 0, '2026-09-24 03:22:25', '2026-09-24 09:26:48'),
(4, 'Celana Panjang', 0, 1, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(5, 'Kaos Kiper', 1, 0, '2026-09-24 03:53:58', '2026-09-27 12:57:57'),
(6, 'Polo', 1, 0, '2026-09-24 03:53:58', '2026-09-24 09:26:42'),
(7, 'Jersey Raglan', 1, 1, '2026-09-27 12:57:59', '2026-09-27 12:57:59');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_sizes`
--

CREATE TABLE `jersey_sizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kelompok` varchar(10) NOT NULL DEFAULT 'dewasa',
  `price_percent` decimal(6,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_sizes`
--

INSERT INTO `jersey_sizes` (`id`, `name`, `sort_order`, `is_active`, `price_surcharge`, `created_at`, `updated_at`, `kelompok`, `price_percent`) VALUES
(1, 'S', 11, 1, 0.00, '2026-09-12 12:27:40', '2026-09-12 13:20:20', 'dewasa', 0.00),
(2, 'M', 12, 1, 0.00, '2026-09-12 12:27:40', '2026-09-12 12:27:40', 'dewasa', 0.00),
(3, 'L', 13, 1, 0.00, '2026-09-12 12:27:40', '2026-09-12 12:27:40', 'dewasa', 0.00),
(4, 'XL', 14, 1, 0.00, '2026-09-12 12:27:40', '2026-09-12 12:27:40', 'dewasa', 0.00),
(5, '2XL', 15, 1, 10000.00, '2026-09-12 12:27:40', '2026-09-12 13:40:51', 'dewasa', 0.00),
(6, '3XL', 16, 1, 10000.00, '2026-09-12 12:27:40', '2026-09-12 13:40:57', 'dewasa', 0.00),
(7, '4XL', 17, 1, 15000.00, '2026-09-12 12:27:40', '2026-09-16 12:46:44', 'dewasa', 0.00),
(8, '5XL', 18, 1, 15000.00, '2026-09-12 12:59:58', '2026-09-16 12:46:55', 'dewasa', 0.00),
(15, 'Anak XS (4-5 th)', 1, 1, 0.00, '2026-09-25 06:34:01', '2026-09-25 07:32:37', 'anak', -10.00),
(16, 'Anak S (6-7 th)', 2, 1, 0.00, '2026-09-25 06:34:01', '2026-09-25 07:32:37', 'anak', -6.00),
(17, 'Anak M (8-9 th)', 3, 1, 0.00, '2026-09-25 06:34:01', '2026-09-25 07:32:37', 'anak', -3.00),
(18, 'Anak L (10-11 th)', 4, 1, 0.00, '2026-09-25 06:34:01', '2026-09-25 07:32:37', 'anak', 0.00),
(19, 'Anak XL (12-13 th)', 5, 1, 0.00, '2026-09-25 06:34:01', '2026-09-25 07:32:37', 'anak', 4.00),
(20, 'Anak 2XL (14-15 th)', 6, 1, 0.00, '2026-09-25 06:34:01', '2026-09-25 07:32:37', 'anak', 8.00);

-- --------------------------------------------------------

--
-- Table structure for table `jersey_size_collar_usage`
--

CREATE TABLE `jersey_size_collar_usage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_size_id` bigint(20) UNSIGNED NOT NULL,
  `jersey_collar_type_id` bigint(20) UNSIGNED NOT NULL,
  `usage_meter` decimal(8,3) DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_size_collar_usage`
--

INSERT INTO `jersey_size_collar_usage` (`id`, `jersey_size_id`, `jersey_collar_type_id`, `usage_meter`, `usage_weight_gram`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.040, 8.00, '2026-09-24 03:40:59', '2026-09-27 09:34:56'),
(2, 2, 1, 0.040, 8.00, '2026-09-24 03:40:59', '2026-09-27 09:35:32'),
(3, 3, 1, 0.040, 8.00, '2026-09-24 03:40:59', '2026-09-27 09:35:33'),
(4, 4, 1, 0.040, 10.00, '2026-09-24 03:40:59', '2026-09-27 09:35:33'),
(5, 5, 1, 0.040, 10.00, '2026-09-24 03:40:59', '2026-09-27 09:35:34'),
(6, 6, 1, 0.040, 12.00, '2026-09-24 03:40:59', '2026-09-27 09:35:35'),
(7, 7, 1, 0.040, 12.00, '2026-09-24 03:40:59', '2026-09-27 09:35:36'),
(8, 8, 1, 0.040, 12.00, '2026-09-24 03:40:59', '2026-09-27 09:35:50'),
(9, 1, 2, 0.080, 7.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(10, 2, 2, 0.080, 7.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(11, 3, 2, 0.080, 7.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(12, 4, 2, 0.080, 8.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(13, 5, 2, 0.080, 8.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(14, 6, 2, 0.080, 10.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(15, 7, 2, 0.080, 10.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(16, 8, 2, 0.080, 10.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(17, 1, 3, 0.120, 10.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(18, 2, 3, 0.120, 10.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(19, 3, 3, 0.120, 10.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(20, 4, 3, 0.120, 12.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(21, 5, 3, 0.120, 12.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(22, 6, 3, 0.120, 15.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(23, 7, 3, 0.120, 15.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(24, 8, 3, 0.120, 15.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(25, 1, 4, 0.240, 20.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(26, 2, 4, 0.240, 20.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(27, 3, 4, 0.240, 20.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(28, 4, 4, 0.240, 24.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(29, 5, 4, 0.240, 24.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(30, 6, 4, 0.240, 30.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(31, 7, 4, 0.240, 30.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(32, 8, 4, 0.240, 30.00, '2026-09-24 03:40:59', '2026-09-24 03:40:59'),
(57, 15, 1, 0.050, 4.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(58, 15, 2, 0.040, 3.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(59, 15, 3, 0.060, 5.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(60, 15, 4, 0.120, 10.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(61, 16, 1, 0.058, 4.64, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(62, 16, 2, 0.046, 4.06, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(63, 16, 3, 0.070, 5.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(64, 16, 4, 0.139, 11.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(65, 17, 1, 0.066, 5.28, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(66, 17, 2, 0.053, 4.62, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(67, 17, 3, 0.079, 6.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(68, 17, 4, 0.158, 13.20, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(69, 18, 1, 0.075, 6.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(70, 18, 2, 0.060, 5.25, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(71, 18, 3, 0.090, 7.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(72, 18, 4, 0.180, 15.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(73, 19, 1, 0.084, 6.72, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(74, 19, 2, 0.067, 5.88, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(75, 19, 3, 0.101, 8.40, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(76, 19, 4, 0.202, 16.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(77, 20, 1, 0.093, 7.44, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(78, 20, 2, 0.074, 6.51, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(79, 20, 3, 0.112, 9.30, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(80, 20, 4, 0.223, 18.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_size_component_usage`
--

CREATE TABLE `jersey_size_component_usage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_size_id` bigint(20) UNSIGNED NOT NULL,
  `jersey_component_id` bigint(20) UNSIGNED NOT NULL,
  `usage_meter` decimal(8,3) DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_size_component_usage`
--

INSERT INTO `jersey_size_component_usage` (`id`, `jersey_size_id`, `jersey_component_id`, `usage_meter`, `usage_weight_gram`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.710, 160.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(2, 2, 1, 0.740, 165.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(3, 3, 1, 0.780, 175.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(4, 4, 1, 0.830, 185.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(5, 5, 1, 0.880, 200.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(6, 6, 1, 0.930, 210.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(7, 7, 1, 0.990, 225.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(8, 8, 1, 1.050, 235.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(9, 1, 2, 0.200, 55.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(10, 2, 2, 0.240, 60.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(11, 3, 2, 0.260, 65.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(12, 4, 2, 0.285, 70.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(13, 5, 2, 0.310, 80.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(14, 6, 2, 0.320, 90.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(15, 7, 2, 0.335, 100.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(16, 8, 2, 0.350, 110.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(17, 1, 3, 0.030, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(18, 2, 3, 0.030, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(19, 3, 3, 0.030, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(20, 4, 3, 0.030, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(21, 5, 3, 0.030, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(22, 6, 3, 0.040, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(23, 7, 3, 0.040, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(24, 8, 3, 0.040, 5.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(25, 1, 4, 0.550, 125.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(26, 2, 4, 0.600, 135.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(27, 3, 4, 0.650, 145.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(28, 4, 4, 0.700, 160.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(29, 5, 4, 0.760, 170.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(30, 6, 4, 0.820, 185.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(31, 7, 4, 0.880, 200.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(32, 8, 4, 0.940, 210.00, '2026-09-24 03:22:25', '2026-09-24 03:46:25'),
(33, 1, 5, 0.500, 130.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(34, 2, 5, 0.500, 145.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(35, 3, 5, 0.600, 160.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(36, 4, 5, 0.600, 175.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(37, 5, 5, 0.650, 195.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(38, 6, 5, 0.700, 215.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(39, 7, 5, 0.750, 235.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(40, 8, 5, 0.800, 255.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(41, 1, 6, 0.120, 10.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(42, 2, 6, 0.120, 10.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(43, 3, 6, 0.120, 10.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(44, 4, 6, 0.120, 12.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(45, 5, 6, 0.120, 12.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(46, 6, 6, 0.120, 15.00, '2026-09-24 03:22:25', '2026-09-24 03:22:25'),
(47, 7, 6, 0.120, 15.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(48, 8, 6, 0.120, 15.00, '2026-09-24 03:22:25', '2026-09-24 03:31:55'),
(49, 1, 7, 0.960, 215.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(50, 2, 7, 1.050, 235.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(51, 3, 7, 1.140, 255.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(52, 4, 7, 1.220, 275.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(53, 5, 7, 1.330, 300.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(54, 6, 7, 1.440, 325.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(55, 7, 7, 1.540, 345.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(56, 8, 7, 1.650, 370.00, '2026-09-24 03:53:58', '2026-09-24 03:53:58'),
(99, 15, 1, 0.355, 80.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(100, 15, 2, 0.100, 27.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(101, 15, 3, 0.015, 2.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(102, 15, 4, 0.275, 62.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(103, 15, 5, 0.250, 65.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(104, 15, 6, 0.060, 5.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(105, 15, 7, 0.480, 107.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(106, 16, 1, 0.412, 92.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(107, 16, 2, 0.116, 31.90, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(108, 16, 3, 0.017, 2.90, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(109, 16, 4, 0.319, 72.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(110, 16, 5, 0.290, 75.40, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(111, 16, 6, 0.070, 5.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(112, 16, 7, 0.557, 124.70, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(113, 17, 1, 0.469, 105.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(114, 17, 2, 0.132, 36.30, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(115, 17, 3, 0.020, 3.30, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(116, 17, 4, 0.363, 82.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(117, 17, 5, 0.330, 85.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(118, 17, 6, 0.079, 6.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(119, 17, 7, 0.634, 141.90, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(120, 18, 1, 0.533, 120.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(121, 18, 2, 0.150, 41.25, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(122, 18, 3, 0.023, 3.75, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(123, 18, 4, 0.413, 93.75, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(124, 18, 5, 0.375, 97.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(125, 18, 6, 0.090, 7.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(126, 18, 7, 0.720, 161.25, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(127, 19, 1, 0.596, 134.40, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(128, 19, 2, 0.168, 46.20, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(129, 19, 3, 0.025, 4.20, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(130, 19, 4, 0.462, 105.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(131, 19, 5, 0.420, 109.20, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(132, 19, 6, 0.101, 8.40, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(133, 19, 7, 0.806, 180.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(134, 20, 1, 0.660, 148.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(135, 20, 2, 0.186, 51.15, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(136, 20, 3, 0.028, 4.65, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(137, 20, 4, 0.512, 116.25, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(138, 20, 5, 0.465, 120.90, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(139, 20, 6, 0.112, 9.30, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(140, 20, 7, 0.893, 199.95, '2026-09-25 06:34:01', '2026-09-25 06:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_size_sleeve_usage`
--

CREATE TABLE `jersey_size_sleeve_usage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jersey_size_id` bigint(20) UNSIGNED NOT NULL,
  `jersey_sleeve_type_id` bigint(20) UNSIGNED NOT NULL,
  `usage_meter` decimal(8,3) DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_size_sleeve_usage`
--

INSERT INTO `jersey_size_sleeve_usage` (`id`, `jersey_size_id`, `jersey_sleeve_type_id`, `usage_meter`, `usage_weight_gram`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.180, 40.00, '2026-09-16 13:48:07', '2026-09-24 03:46:25'),
(2, 2, 1, 0.190, 45.00, '2026-09-16 13:48:13', '2026-09-24 03:46:25'),
(3, 3, 1, 0.200, 45.00, '2026-09-16 13:48:20', '2026-09-24 03:46:25'),
(4, 4, 1, 0.210, 45.00, '2026-09-16 13:48:24', '2026-09-24 03:46:25'),
(5, 5, 1, 0.220, 50.00, '2026-09-16 13:48:28', '2026-09-24 03:46:25'),
(6, 6, 1, 0.240, 55.00, '2026-09-16 13:48:47', '2026-09-24 03:46:25'),
(7, 7, 1, 0.250, 55.00, '2026-09-16 13:48:53', '2026-09-24 03:46:25'),
(8, 8, 1, 0.260, 60.00, '2026-09-16 13:48:57', '2026-09-24 03:46:25'),
(9, 1, 5, 0.300, 95.00, '2026-09-16 13:49:06', '2026-09-16 13:49:09'),
(10, 2, 5, 0.330, 105.00, '2026-09-16 13:49:10', '2026-09-16 13:49:12'),
(11, 3, 5, 0.360, 125.00, '2026-09-16 13:49:13', '2026-09-16 13:49:15'),
(12, 4, 5, 0.390, 125.00, '2026-09-16 13:49:20', '2026-09-16 13:49:22'),
(13, 5, 5, 0.430, 140.00, '2026-09-16 13:49:24', '2026-09-16 13:49:26'),
(14, 6, 5, 0.470, 155.00, '2026-09-16 13:49:28', '2026-09-16 13:49:30'),
(15, 7, 5, 0.510, 170.00, '2026-09-16 13:49:31', '2026-09-16 13:49:33'),
(16, 8, 5, 0.550, 185.00, '2026-09-16 13:49:35', '2026-09-16 13:49:41'),
(17, 1, 2, 0.420, 95.00, '2026-09-16 13:49:50', '2026-09-24 03:46:25'),
(18, 2, 2, 0.440, 100.00, '2026-09-16 13:49:53', '2026-09-24 03:46:25'),
(19, 3, 2, 0.470, 105.00, '2026-09-16 13:49:56', '2026-09-24 03:46:25'),
(20, 4, 2, 0.490, 110.00, '2026-09-16 13:50:06', '2026-09-24 03:46:25'),
(21, 5, 2, 0.520, 115.00, '2026-09-16 13:50:09', '2026-09-24 03:46:25'),
(22, 6, 2, 0.550, 125.00, '2026-09-16 13:50:14', '2026-09-24 03:46:25'),
(23, 7, 2, 0.580, 130.00, '2026-09-16 13:50:19', '2026-09-24 03:46:25'),
(24, 8, 2, 0.610, 135.00, '2026-09-16 13:50:24', '2026-09-24 03:46:25'),
(25, 1, 6, 0.450, 145.00, '2026-09-16 13:50:55', '2026-09-16 13:51:02'),
(26, 2, 6, 0.490, 160.00, '2026-09-16 13:51:07', '2026-09-16 13:51:11'),
(27, 3, 6, 0.540, 178.00, '2026-09-16 13:51:15', '2026-09-16 13:51:18'),
(28, 8, 6, 0.820, 277.00, '2026-09-16 13:51:35', '2026-09-16 13:51:39'),
(29, 7, 6, 0.760, 257.00, '2026-09-16 13:51:44', '2026-09-16 13:51:50'),
(30, 6, 6, 0.700, 235.00, '2026-09-16 13:51:55', '2026-09-16 13:52:00'),
(31, 5, 6, 0.640, 215.00, '2026-09-16 13:52:04', '2026-09-16 13:52:09'),
(32, 4, 6, 0.580, 193.00, '2026-09-16 13:52:14', '2026-09-16 13:52:34'),
(57, 15, 1, 0.090, 20.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(58, 15, 2, 0.210, 47.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(59, 15, 5, 0.150, 47.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(60, 15, 6, 0.225, 72.50, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(61, 16, 1, 0.104, 23.20, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(62, 16, 2, 0.244, 55.10, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(63, 16, 5, 0.174, 55.10, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(64, 16, 6, 0.261, 84.10, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(65, 17, 1, 0.119, 26.40, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(66, 17, 2, 0.277, 62.70, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(67, 17, 5, 0.198, 62.70, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(68, 17, 6, 0.297, 95.70, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(69, 18, 1, 0.135, 30.00, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(70, 18, 2, 0.315, 71.25, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(71, 18, 5, 0.225, 71.25, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(72, 18, 6, 0.338, 108.75, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(73, 19, 1, 0.151, 33.60, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(74, 19, 2, 0.353, 79.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(75, 19, 5, 0.252, 79.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(76, 19, 6, 0.378, 121.80, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(77, 20, 1, 0.167, 37.20, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(78, 20, 2, 0.391, 88.35, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(79, 20, 5, 0.279, 88.35, '2026-09-25 06:34:01', '2026-09-25 06:34:01'),
(80, 20, 6, 0.419, 134.85, '2026-09-25 06:34:01', '2026-09-25 06:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `jersey_sleeve_types`
--

CREATE TABLE `jersey_sleeve_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jersey_sleeve_types`
--

INSERT INTO `jersey_sleeve_types` (`id`, `name`, `price_surcharge`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Pendek', 0.00, 1, 1, '2026-09-23 22:42:58', '2026-09-23 22:42:58'),
(2, 'Panjang', 0.00, 2, 1, '2026-09-23 22:42:58', '2026-09-23 22:42:58');

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
-- Table structure for table `jurnal_umum`
--

CREATE TABLE `jurnal_umum` (
  `id_jurnal` bigint(20) UNSIGNED NOT NULL,
  `tgl_jurnal` date NOT NULL,
  `no_referensi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kode_akun` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `debet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jurnal_umum`
--

INSERT INTO `jurnal_umum` (`id_jurnal`, `tgl_jurnal`, `no_referensi`, `keterangan`, `kode_akun`, `debet`, `kredit`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2026-09-26', 'JL.0068041', 'Pembelian bahan baku JL.0068041', '1200', 3642509.40, 0.00, '2026-09-26 00:42:16', '2026-09-26 00:42:16', NULL),
(2, '2026-09-26', 'JL.0068041', 'Pembelian bahan baku JL.0068041', '2000', 0.00, 3642509.40, '2026-09-26 00:42:16', '2026-09-26 00:42:16', NULL),
(3, '2026-09-26', 'JL.0066627', 'Pembelian bahan baku JL.0066627', '1200', 7061020.80, 0.00, '2026-09-26 00:50:07', '2026-09-26 00:50:07', NULL),
(4, '2026-09-26', 'JL.0066627', 'Pembelian bahan baku JL.0066627', '2000', 0.00, 7061020.80, '2026-09-26 00:50:07', '2026-09-26 00:50:07', NULL),
(5, '2026-09-26', 'JL.0066832', 'Pembelian bahan baku JL.0066832', '1200', 6831994.50, 0.00, '2026-09-26 00:57:48', '2026-09-26 00:57:48', NULL),
(6, '2026-09-26', 'JL.0066832', 'Pembelian bahan baku JL.0066832', '2000', 0.00, 6831994.50, '2026-09-26 00:57:48', '2026-09-26 00:57:48', NULL),
(7, '2026-09-26', 'CSBSBY262767', 'Pembelian bahan baku CSBSBY262767', '1200', 10684800.00, 0.00, '2026-09-26 01:01:45', '2026-09-26 01:01:45', NULL),
(8, '2026-09-26', 'CSBSBY262767', 'Pembelian bahan baku CSBSBY262767', '2000', 0.00, 10684800.00, '2026-09-26 01:01:45', '2026-09-26 01:01:45', NULL),
(9, '2026-09-26', 'SO-WU/0926/00049', 'Pembelian bahan baku SO-WU/0926/00049', '1200', 4103998.56, 0.00, '2026-09-26 01:11:53', '2026-09-26 01:11:53', NULL),
(10, '2026-09-26', 'SO-WU/0926/00049', 'Pembelian bahan baku SO-WU/0926/00049', '2000', 0.00, 4103998.56, '2026-09-26 01:11:53', '2026-09-26 01:11:53', NULL),
(11, '2026-09-27', 'JO-20260927-001', 'Pendapatan order jersey JO-20260927-001', '1100', 2280000.00, 0.00, '2026-09-27 09:30:29', '2026-09-27 09:30:29', NULL),
(12, '2026-09-27', 'JO-20260927-001', 'Pendapatan order jersey JO-20260927-001', '4300', 0.00, 2280000.00, '2026-09-27 09:30:29', '2026-09-27 09:30:29', NULL),
(13, '2026-09-27', 'STOK-AWAL-MAIN-EPS-IN', 'Saldo awal persediaan Maintenance Kit Epson Indoor (1 pcs x Rp 1.900.000)', '1200', 1900000.00, 0.00, '2026-09-27 09:48:37', '2026-09-27 09:48:37', NULL),
(14, '2026-09-27', 'STOK-AWAL-MAIN-EPS-IN', 'Saldo awal persediaan Maintenance Kit Epson Indoor (1 pcs x Rp 1.900.000)', '3000', 0.00, 1900000.00, '2026-09-27 09:48:37', '2026-09-27 09:48:37', NULL),
(15, '2026-09-27', 'SO-WU/0926/02643', 'Pembelian bahan baku SO-WU/0926/02643', '1200', 1899999.21, 0.00, '2026-09-27 09:51:05', '2026-09-27 09:51:05', NULL),
(16, '2026-09-27', 'SO-WU/0926/02643', 'Pembelian bahan baku SO-WU/0926/02643', '2000', 0.00, 1899999.21, '2026-09-27 09:51:05', '2026-09-27 09:51:05', NULL),
(17, '2026-09-27', 'FJ-26-09-00089', 'Pembelian bahan baku FJ-26-09-00089', '1200', 2680000.00, 0.00, '2026-09-27 10:13:39', '2026-09-27 10:13:39', NULL),
(18, '2026-09-27', 'FJ-26-09-00089', 'Pembelian bahan baku FJ-26-09-00089', '2000', 0.00, 2680000.00, '2026-09-27 10:13:39', '2026-09-27 10:13:39', NULL),
(19, '2026-09-27', 'SJ-26-09-00060', 'Pembelian bahan baku SJ-26-09-00060', '1200', 1292000.00, 0.00, '2026-09-27 10:14:14', '2026-09-27 10:14:14', NULL),
(20, '2026-09-27', 'SJ-26-09-00060', 'Pembelian bahan baku SJ-26-09-00060', '2000', 0.00, 1292000.00, '2026-09-27 10:14:14', '2026-09-27 10:14:14', NULL),
(21, '2026-09-27', 'JO-20260927-002', 'Pendapatan order jersey JO-20260927-002', '1100', 2152600.00, 0.00, '2026-09-27 12:59:25', '2026-09-27 12:59:25', NULL),
(22, '2026-09-27', 'JO-20260927-002', 'Pendapatan order jersey JO-20260927-002', '4300', 0.00, 2152600.00, '2026-09-27 12:59:25', '2026-09-27 12:59:25', NULL),
(23, '2026-09-27', 'JO-20260927-002', 'HPP kain jersey JO-20260927-002', '5000', 331180.00, 0.00, '2026-09-27 13:00:58', '2026-09-27 13:00:58', NULL),
(24, '2026-09-27', 'JO-20260927-002', 'HPP kain jersey JO-20260927-002', '1200', 0.00, 331180.00, '2026-09-27 13:00:58', '2026-09-27 13:00:58', NULL),
(25, '2026-09-27', 'ORG-20260927-001', 'Penjualan ORG-20260927-001', '1100', 112600.00, 0.00, '2026-09-27 14:26:42', '2026-09-27 14:26:42', NULL),
(26, '2026-09-27', 'ORG-20260927-001', 'Penjualan ORG-20260927-001', '4000', 0.00, 112600.00, '2026-09-27 14:26:42', '2026-09-27 14:26:42', NULL),
(27, '2026-09-27', 'ORG-20260927-001', 'HPP bahan baku ORG-20260927-001', '5000', 57418.36, 0.00, '2026-09-27 14:26:42', '2026-09-27 14:26:42', NULL),
(28, '2026-09-27', 'ORG-20260927-001', 'HPP bahan baku ORG-20260927-001', '1200', 0.00, 57418.36, '2026-09-27 14:26:42', '2026-09-27 14:26:42', NULL),
(29, '2026-09-27', 'ORG-20260927-001', 'Koreksi/pembalikan nota ORG-20260927-001', '1100', 0.00, 112600.00, '2026-09-27 14:46:27', '2026-09-27 14:46:27', NULL),
(30, '2026-09-27', 'ORG-20260927-001', 'Koreksi/pembalikan nota ORG-20260927-001', '4000', 112600.00, 0.00, '2026-09-27 14:46:27', '2026-09-27 14:46:27', NULL),
(31, '2026-09-27', 'ORG-20260927-001', 'Koreksi/pembalikan nota ORG-20260927-001', '5000', 0.00, 57418.36, '2026-09-27 14:46:27', '2026-09-27 14:46:27', NULL),
(32, '2026-09-27', 'ORG-20260927-001', 'Koreksi/pembalikan nota ORG-20260927-001', '1200', 57418.36, 0.00, '2026-09-27 14:46:27', '2026-09-27 14:46:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kartu_stok`
--

CREATE TABLE `kartu_stok` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `tipe` enum('IN','OUT','ADJUSTMENT') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `stok_sebelum` decimal(15,4) NOT NULL,
  `stok_sesudah` decimal(15,4) NOT NULL,
  `referensi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kartu_stok`
--

INSERT INTO `kartu_stok` (`id`, `id_bahan`, `tanggal`, `tipe`, `qty`, `stok_sebelum`, `stok_sesudah`, `referensi`, `keterangan`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-09-26 06:47:14', 'IN', 2.9885, 0.0000, 2.9885, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(2, 2, '2026-09-26 06:47:14', 'IN', 2.9220, 0.0000, 2.9220, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(3, 3, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(4, 4, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(5, 5, '2026-09-26 06:47:14', 'IN', 5.0000, 0.0000, 5.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(6, 6, '2026-09-26 06:47:14', 'IN', 1.9470, 0.0000, 1.9470, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(7, 7, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(8, 8, '2026-09-26 06:47:14', 'IN', 1.9277, 0.0000, 1.9277, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(9, 9, '2026-09-26 06:47:14', 'IN', 1.9327, 0.0000, 1.9327, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(10, 10, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(11, 11, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(12, 12, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(13, 13, '2026-09-26 06:47:14', 'IN', 3.9505, 0.0000, 3.9505, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(14, 27, '2026-09-26 06:47:14', 'IN', 1.9442, 0.0000, 1.9442, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(15, 28, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(16, 29, '2026-09-26 06:47:14', 'IN', 0.9812, 0.0000, 0.9812, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(17, 30, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(18, 31, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(19, 32, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(20, 33, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(21, 34, '2026-09-26 06:47:14', 'IN', 1.5000, 0.0000, 1.5000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(22, 35, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(23, 36, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(24, 37, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(25, 38, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(26, 39, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(27, 40, '2026-09-26 06:47:14', 'IN', 10.0000, 0.0000, 10.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(28, 41, '2026-09-26 06:47:14', 'IN', 10.0000, 0.0000, 10.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(29, 42, '2026-09-26 06:47:14', 'IN', 100.0000, 0.0000, 100.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(30, 43, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(31, 45, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(32, 46, '2026-09-26 06:47:14', 'IN', 0.6760, 0.0000, 0.6760, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(33, 47, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(34, 48, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(35, 51, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(36, 52, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(37, 53, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(38, 54, '2026-09-26 06:47:14', 'IN', 2.0000, 0.0000, 2.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(39, 55, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(40, 56, '2026-09-26 06:47:14', 'IN', 5.0000, 0.0000, 5.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(41, 87, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(42, 88, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(43, 90, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(44, 91, '2026-09-26 06:47:14', 'IN', 5.0000, 0.0000, 5.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(45, 92, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(46, 94, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(47, 96, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(48, 97, '2026-09-26 06:47:14', 'IN', 20.0000, 0.0000, 20.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(49, 98, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(50, 99, '2026-09-26 06:47:14', 'IN', 10.0000, 0.0000, 10.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(51, 101, '2026-09-26 06:47:14', 'IN', 5.0000, 0.0000, 5.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(52, 102, '2026-09-26 06:47:14', 'IN', 4.0000, 0.0000, 4.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(53, 103, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(54, 104, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(55, 105, '2026-09-26 06:47:14', 'IN', 3.0000, 0.0000, 3.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(56, 106, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(57, 145, '2026-09-26 06:47:14', 'IN', 1.0000, 0.0000, 1.0000, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(58, 146, '2026-09-26 06:47:14', 'IN', 25.3500, 0.0000, 25.3500, 'SALDO-AWAL', 'Saldo awal stok (data uji dibersihkan)', NULL, '2026-09-25 23:47:14', '2026-09-25 23:47:14'),
(59, 7, '2026-09-26 07:33:07', 'IN', 2.0000, 2.0000, 4.0000, 'JL-0068041', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. STIKER CAMEL 155cm (2 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 00:33:07', '2026-09-26 00:33:07'),
(60, 49, '2026-09-26 07:34:38', 'ADJUSTMENT', 3.0000, 0.0000, 3.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 00:34:38', '2026-09-26 00:34:38'),
(61, 93, '2026-09-26 07:34:41', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 00:34:41', '2026-09-26 00:34:41'),
(62, 7, '2026-09-26 07:48:26', 'IN', 4.0000, 4.0000, 8.0000, 'JL-00666627', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. STIKER CAMEL 155cm (4 roll)\r\n2. FRONTLITE 280gr 2.2m (2 roll)\r\n3. FRONTLITE 280gr 3.2m (3 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 00:48:26', '2026-09-26 00:48:26'),
(63, 27, '2026-09-26 07:48:26', 'IN', 2.0000, 1.9442, 3.9442, 'JL-00666627', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. STIKER CAMEL 155cm (4 roll)\r\n2. FRONTLITE 280gr 2.2m (2 roll)\r\n3. FRONTLITE 280gr 3.2m (3 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 00:48:26', '2026-09-26 00:48:26'),
(64, 1, '2026-09-26 07:48:26', 'IN', 3.0000, 2.9885, 5.9885, 'JL-00666627', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. STIKER CAMEL 155cm (4 roll)\r\n2. FRONTLITE 280gr 2.2m (2 roll)\r\n3. FRONTLITE 280gr 3.2m (3 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 00:48:26', '2026-09-26 00:48:26'),
(65, 1, '2026-09-26 07:57:11', 'IN', 5.0000, 5.9885, 10.9885, 'JL.0066832', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr 3.2m (5 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 00:57:11', '2026-09-26 00:57:11'),
(66, 1, '2026-09-26 08:00:50', 'IN', 5.0000, 10.9885, 15.9885, 'JL.00240826', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr 3.2m (5 roll)\r\n2. FRONTLITE 280gr 2.6m (2 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 01:00:50', '2026-09-26 01:00:50'),
(67, 28, '2026-09-26 08:00:50', 'IN', 2.0000, 2.0000, 4.0000, 'JL.00240826', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr 3.2m (5 roll)\r\n2. FRONTLITE 280gr 2.6m (2 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 01:00:50', '2026-09-26 01:00:50'),
(68, 5, '2026-09-26 08:11:06', 'IN', 6.0000, 5.0000, 11.0000, 'SJ-01092601', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. SUBLIME PAPER (6 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-26 01:11:06', '2026-09-26 01:11:06'),
(69, 138, '2026-09-26 09:18:54', 'ADJUSTMENT', 5.0000, 0.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:18:54', '2026-09-26 02:18:54'),
(70, 95, '2026-09-26 09:19:06', 'ADJUSTMENT', 5.0000, 0.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:19:06', '2026-09-26 02:19:06'),
(71, 44, '2026-09-26 09:19:14', 'ADJUSTMENT', 5.0000, 0.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:19:14', '2026-09-26 02:19:14'),
(72, 136, '2026-09-26 09:19:22', 'ADJUSTMENT', 5.0000, 0.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:19:22', '2026-09-26 02:19:22'),
(73, 137, '2026-09-26 09:19:35', 'ADJUSTMENT', 5.0000, 0.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:19:35', '2026-09-26 02:19:35'),
(74, 141, '2026-09-26 09:20:23', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:20:23', '2026-09-26 02:20:23'),
(75, 139, '2026-09-26 09:20:34', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:20:34', '2026-09-26 02:20:34'),
(76, 140, '2026-09-26 09:20:43', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:20:43', '2026-09-26 02:20:43'),
(77, 144, '2026-09-26 09:20:57', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:20:57', '2026-09-26 02:20:57'),
(78, 50, '2026-09-26 09:21:12', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:21:12', '2026-09-26 02:21:12'),
(79, 142, '2026-09-26 09:21:52', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:21:52', '2026-09-26 02:21:52'),
(80, 143, '2026-09-26 09:22:07', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:22:07', '2026-09-26 02:22:07'),
(81, 121, '2026-09-26 09:22:22', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:22:22', '2026-09-26 02:22:22'),
(82, 125, '2026-09-26 09:22:35', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:22:35', '2026-09-26 02:22:35'),
(83, 122, '2026-09-26 09:22:47', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:22:47', '2026-09-26 02:22:47'),
(84, 117, '2026-09-26 09:23:07', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:23:07', '2026-09-26 02:23:07'),
(85, 119, '2026-09-26 09:23:15', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:23:15', '2026-09-26 02:23:15'),
(86, 118, '2026-09-26 09:23:23', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:23:23', '2026-09-26 02:23:23'),
(87, 123, '2026-09-26 09:23:30', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:23:30', '2026-09-26 02:23:30'),
(88, 124, '2026-09-26 09:23:38', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:23:38', '2026-09-26 02:23:38'),
(89, 120, '2026-09-26 09:23:45', 'ADJUSTMENT', 1.0000, 0.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 02:23:45', '2026-09-26 02:23:45'),
(90, 44, '2026-09-26 10:39:46', 'ADJUSTMENT', 0.0000, 5.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 03:39:46', '2026-09-26 03:39:46'),
(91, 138, '2026-09-26 10:39:48', 'ADJUSTMENT', 5.0000, 5.0000, 10.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 03:39:48', '2026-09-26 03:39:48'),
(92, 143, '2026-09-26 10:39:49', 'ADJUSTMENT', 0.0000, 1.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 03:39:49', '2026-09-26 03:39:49'),
(93, 142, '2026-09-26 10:39:50', 'ADJUSTMENT', 0.0000, 1.0000, 1.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 03:39:50', '2026-09-26 03:39:50'),
(94, 137, '2026-09-26 10:39:51', 'ADJUSTMENT', 0.0000, 5.0000, 5.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 03:39:51', '2026-09-26 03:39:51'),
(95, 138, '2026-09-26 10:39:53', 'ADJUSTMENT', 8.0000, 10.0000, 2.0000, 'STOCK-OPNAME', 'Recheck', 1, '2026-09-26 03:39:53', '2026-09-26 03:39:53'),
(96, 147, '2026-09-27 16:48:37', 'IN', 1.0000, 0.0000, 1.0000, 'STOK-AWAL', 'Stok awal saat pendaftaran bahan baru', 1, '2026-09-27 09:48:37', '2026-09-27 09:48:37'),
(97, 147, '2026-09-27 16:50:14', 'IN', 1.0000, 1.0000, 2.0000, 'SJ-092602643', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. Maintenance Kit Epson Indoor (1 pcs)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-27 09:50:14', '2026-09-27 09:50:14'),
(98, 88, '2026-09-27 16:53:51', 'IN', 2.0000, 1.0000, 3.0000, 'SJ-0926/00060', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. LAMINASI DINGIN GLOSSY 152cm (2 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-27 09:53:51', '2026-09-27 09:53:51'),
(99, 96, '2026-09-27 16:53:53', 'IN', 4.0000, 3.0000, 7.0000, 'SJ-0926/00089', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. RING MATA AYAM (4 pack)\r\n2. STIKER EXILO 153cm (2 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-27 09:53:53', '2026-09-27 09:53:53'),
(100, 9, '2026-09-27 16:53:53', 'IN', 2.0000, 1.9327, 3.9327, 'SJ-0926/00089', 'Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. RING MATA AYAM (4 pack)\r\n2. STIKER EXILO 153cm (2 roll)\r\n\r\nNB : Barang Lengkap!', 1, '2026-09-27 09:53:53', '2026-09-27 09:53:53'),
(101, 146, '2026-09-27 20:00:58', 'OUT', 5.7100, 25.3500, 19.6400, 'JO-20260927-002', 'Pemakaian kain order jersey JO-20260927-002', 1, '2026-09-27 13:00:58', '2026-09-27 13:00:58'),
(102, 2, '2026-09-27 21:26:42', 'OUT', 0.0177, 2.9220, 2.9043, 'ORG-20260927-001', 'Pemakaian produksi order ORG-20260927-001', 1, '2026-09-27 14:26:42', '2026-09-27 14:26:42'),
(103, 46, '2026-09-27 21:26:42', 'OUT', 0.0864, 0.6760, 0.5896, 'ORG-20260927-001', 'Pemakaian produksi order ORG-20260927-001', 1, '2026-09-27 14:26:42', '2026-09-27 14:26:42'),
(104, 2, '2026-09-27 21:46:27', 'IN', 0.0177, 2.9043, 2.9220, 'EDIT-NOTA-3', 'Koreksi nota #ORG-20260927-001 (permintaan edit #1) - stok dikembalikan', 1, '2026-09-27 14:46:27', '2026-09-27 14:46:27'),
(105, 46, '2026-09-27 21:46:27', 'IN', 0.0864, 0.5896, 0.6760, 'EDIT-NOTA-3', 'Koreksi nota #ORG-20260927-001 (permintaan edit #1) - stok dikembalikan', 1, '2026-09-27 14:46:27', '2026-09-27 14:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` bigint(20) UNSIGNED NOT NULL,
  `nama_karyawan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bagian` enum('Admin','Desain','Produksi','Kasir','Gudang') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama_karyawan`, `bagian`, `telepon`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'Admin Utama', 'Admin', '0836587999', 1, NULL, '2026-03-24 07:13:00'),
(5, 'Maulidia', 'Desain', '083245455658', 1, '2026-02-25 14:33:59', '2026-03-06 00:43:19'),
(6, 'Rehan', 'Desain', '089978878', 1, '2026-02-25 14:36:02', '2026-02-25 14:36:02'),
(7, 'Rahman', 'Desain', '085322154545', 1, '2026-02-25 14:36:11', '2026-02-25 14:36:11'),
(8, 'Fathur Rosi', 'Produksi', '-', 1, '2026-03-06 00:43:43', '2026-03-06 00:43:43'),
(9, 'Moh. Riski', 'Produksi', '-', 1, '2026-03-06 00:44:00', '2026-03-06 00:44:00'),
(10, 'Haifa', 'Kasir', '081578892592', 1, '2026-03-24 07:14:58', '2026-03-24 07:14:58'),
(11, 'Salam', 'Produksi', '-', 1, '2026-09-25 13:14:14', '2026-09-25 13:14:14');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan_mesin`
--

CREATE TABLE `karyawan_mesin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_karyawan` bigint(20) UNSIGNED NOT NULL,
  `id_mesin` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karyawan_mesin`
--

INSERT INTO `karyawan_mesin` (`id`, `id_karyawan`, `id_mesin`, `created_at`, `updated_at`) VALUES
(2, 8, 3, NULL, NULL),
(3, 9, 5, NULL, NULL),
(4, 8, 1, NULL, NULL),
(5, 11, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kas_harian`
--

CREATE TABLE `kas_harian` (
  `id_kas` bigint(20) UNSIGNED NOT NULL,
  `tgl_kas` date NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `saldo_awal` decimal(15,2) NOT NULL,
  `total_tunai_masuk` decimal(15,2) DEFAULT 0.00,
  `total_transfer_masuk` decimal(15,2) DEFAULT 0.00,
  `total_debit_masuk` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_tunai_keluar` decimal(15,2) DEFAULT 0.00,
  `saldo_akhir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo_fisik` decimal(15,2) DEFAULT NULL,
  `selisih` decimal(15,2) DEFAULT NULL,
  `total_transfer` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_diskon` decimal(15,2) DEFAULT 0.00,
  `status_lock` enum('open','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open',
  `waktu_tutup` timestamp NULL DEFAULT NULL,
  `keterangan_closing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_rolling` enum('Rolling','Tidak Rolling') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kas_harian`
--

INSERT INTO `kas_harian` (`id_kas`, `tgl_kas`, `id_user`, `saldo_awal`, `total_tunai_masuk`, `total_transfer_masuk`, `total_debit_masuk`, `total_tunai_keluar`, `saldo_akhir`, `saldo_fisik`, `selisih`, `total_transfer`, `total_diskon`, `status_lock`, `waktu_tutup`, `keterangan_closing`, `status_rolling`, `created_at`, `updated_at`) VALUES
(1, '2026-09-27', 1, 100000.00, 0.00, 0.00, 0.00, 0.00, 100000.00, NULL, NULL, 0.00, 0.00, 'open', NULL, NULL, NULL, '2026-09-27 12:50:10', '2026-09-27 12:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `kas_kecil`
--

CREATE TABLE `kas_kecil` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` varchar(10) NOT NULL DEFAULT 'Keluar',
  `sumber` varchar(20) NOT NULL DEFAULT 'manual',
  `id_referensi` bigint(20) UNSIGNED DEFAULT NULL,
  `metode` varchar(20) NOT NULL DEFAULT 'Tunai',
  `id_kategori` bigint(20) UNSIGNED DEFAULT NULL,
  `keterangan` varchar(500) NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `bukti_path` varchar(255) DEFAULT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kas_manual`
--

CREATE TABLE `kas_manual` (
  `id_kas_manual` bigint(20) UNSIGNED NOT NULL,
  `id_kas` bigint(20) UNSIGNED NOT NULL,
  `waktu` datetime NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Masuk','Keluar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kode_kategori` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `kode_kategori`, `created_at`, `updated_at`) VALUES
(2, 'INDOOR', 'IND', NULL, NULL),
(3, 'OUTDOOR', 'OTD', NULL, NULL),
(4, 'A3', 'A3', NULL, NULL),
(5, 'CENTURY STAR', 'CEN', NULL, NULL),
(6, 'SUBLIME', 'SUB', '2026-02-25 23:30:16', '2026-02-25 23:30:16'),
(7, 'RHINO DTF', 'DTF', '2026-02-25 23:30:29', '2026-02-25 23:30:29'),
(8, 'Lain-lain', 'LL', '2026-09-24 11:35:14', '2026-09-24 11:35:14');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_finishing`
--

CREATE TABLE `kategori_finishing` (
  `id_kategori` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_kas_kecil`
--

CREATE TABLE `kategori_kas_kecil` (
  `id_kategori_kas_kecil` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `jenis` varchar(10) NOT NULL DEFAULT 'Keluar',
  `kode_akun` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_kas_kecil`
--

INSERT INTO `kategori_kas_kecil` (`id_kategori_kas_kecil`, `nama_kategori`, `jenis`, `kode_akun`, `created_at`, `updated_at`) VALUES
(6, 'Beban Administrasi & Umum', 'Keluar', '5110', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(7, 'Beban BPJS', 'Keluar', '5120', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(8, 'Beban Internet', 'Keluar', '5130', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(9, 'Beban Listrik & Telepon', 'Keluar', '5140', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(10, 'Beban PDAM', 'Keluar', '5150', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(11, 'Beban Perlengkapan Kantor', 'Keluar', '5160', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(12, 'Beban Perlengkapan Toko', 'Keluar', '5170', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(13, 'Biaya Marketing', 'Keluar', '5180', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(14, 'Biaya Ongkos Kirim & Ekspedisi', 'Keluar', '5190', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(15, 'Biaya Operasional Kantor', 'Keluar', '5200', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(16, 'Biaya Lain-lain', 'Keluar', '5100', '2026-09-23 22:42:59', '2026-09-23 22:42:59'),
(17, 'Pendapatan Lain-lain', 'Masuk', '4200', '2026-09-25 23:23:30', '2026-09-25 23:23:30'),
(18, 'Tambahan Modal / Setoran Pemilik', 'Masuk', '3000', '2026-09-25 23:23:30', '2026-09-25 23:23:30'),
(19, 'Isi dari Kas Laci', 'Masuk', '1000', '2026-09-25 23:36:15', '2026-09-25 23:36:15'),
(20, 'Komisi Desainer', 'Keluar', '5210', '2026-09-26 00:33:52', '2026-09-26 00:33:52');

-- --------------------------------------------------------

--
-- Table structure for table `kedatangan_bahan`
--

CREATE TABLE `kedatangan_bahan` (
  `id_kedatangan` bigint(20) UNSIGNED NOT NULL,
  `no_surat_jalan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_supplier` bigint(20) UNSIGNED NOT NULL,
  `id_gudang` bigint(20) UNSIGNED NOT NULL,
  `tgl_datang` date NOT NULL,
  `status` enum('Draft','Selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Draft',
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_purchase_order` bigint(20) UNSIGNED DEFAULT NULL,
  `id_platform` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_toko_online` varchar(150) DEFAULT NULL,
  `no_pesanan_online` varchar(100) DEFAULT NULL,
  `no_resi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kedatangan_bahan`
--

INSERT INTO `kedatangan_bahan` (`id_kedatangan`, `no_surat_jalan`, `id_supplier`, `id_gudang`, `tgl_datang`, `status`, `keterangan`, `created_at`, `updated_at`, `id_purchase_order`, `id_platform`, `nama_toko_online`, `no_pesanan_online`, `no_resi`) VALUES
(1, 'JL-0068041', 1, 1, '2026-09-10', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. STIKER CAMEL 155cm (2 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'JL-00666627', 1, 1, '2026-08-20', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. STIKER CAMEL 155cm (4 roll)\r\n2. FRONTLITE 280gr 2.2m (2 roll)\r\n3. FRONTLITE 280gr 3.2m (3 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'JL.0066832', 1, 1, '2026-09-26', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr 3.2m (5 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'JL.00240826', 3, 1, '2026-09-26', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr 3.2m (5 roll)\r\n2. FRONTLITE 280gr 2.6m (2 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'SJ-01092601', 9, 1, '2026-09-02', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. SUBLIME PAPER (6 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'SJ-092602643', 9, 1, '2026-09-22', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. Maintenance Kit Epson Indoor (1 pcs)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'SJ-0926/00089', 6, 1, '2026-09-22', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. RING MATA AYAM (4 pack)\r\n2. STIKER EXILO 153cm (2 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'SJ-0926/00060', 6, 1, '2026-09-15', 'Selesai', 'PENERIMAAN BARANG FISIK:\r\n1. LAMINASI DINGIN GLOSSY 152cm (2 roll)\r\n\r\nNB : Barang Lengkap!', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kedatangan_bahan_detail`
--

CREATE TABLE `kedatangan_bahan_detail` (
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_kedatangan` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `satuan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('OK','Rusak','Kurang') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kedatangan_bahan_detail`
--

INSERT INTO `kedatangan_bahan_detail` (`id_detail`, `id_kedatangan`, `id_bahan`, `qty`, `satuan`, `status`) VALUES
(1, 1, 7, 2.00, NULL, 'OK'),
(2, 2, 7, 4.00, NULL, 'OK'),
(3, 2, 27, 2.00, NULL, 'OK'),
(4, 2, 1, 3.00, NULL, 'OK'),
(5, 3, 1, 5.00, NULL, 'OK'),
(6, 4, 1, 5.00, NULL, 'OK'),
(7, 4, 28, 2.00, NULL, 'OK'),
(8, 5, 5, 6.00, NULL, 'OK'),
(9, 6, 147, 1.00, NULL, 'OK'),
(10, 7, 96, 4.00, NULL, 'OK'),
(11, 7, 9, 2.00, NULL, 'OK'),
(12, 8, 88, 2.00, NULL, 'OK');

-- --------------------------------------------------------

--
-- Table structure for table `kelas_desainer`
--

CREATE TABLE `kelas_desainer` (
  `id_kelas` bigint(20) UNSIGNED NOT NULL,
  `nama_kelas` varchar(60) NOT NULL,
  `persen_komisi` decimal(5,2) NOT NULL DEFAULT 0.00,
  `keterangan` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas_desainer`
--

INSERT INTO `kelas_desainer` (`id_kelas`, `nama_kelas`, `persen_komisi`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'Standar', 5.00, 'Kelas awal operator desain (karyawan gaji tetap)', '2026-09-26 00:33:52', '2026-09-26 01:00:50'),
(2, 'Senior', 10.00, 'Desainer berpengalaman', '2026-09-26 01:00:50', '2026-09-26 01:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `konsumen`
--

CREATE TABLE `konsumen` (
  `id_konsumen` bigint(20) UNSIGNED NOT NULL,
  `nama_konsumen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_konsumen_manual` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_golongan` bigint(20) UNSIGNED NOT NULL,
  `tipe_konsumen` enum('permanent','temporary') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'temporary',
  `tgl_registrasi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `konsumen`
--

INSERT INTO `konsumen` (`id_konsumen`, `nama_konsumen`, `nama_konsumen_manual`, `telepon`, `alamat`, `id_golongan`, `tipe_konsumen`, `tgl_registrasi`) VALUES
(1, 'Arafiq', '', '08121545487', 'Sampang', 1, 'temporary', '2026-02-25 15:10:43'),
(2, 'Budi', '', '0854547878', 'Sampang', 2, 'permanent', '2026-02-25 15:10:58'),
(3, 'Kamil', '', '08547578787', 'Sampang', 1, 'temporary', '2026-02-25 15:11:09'),
(4, 'Samir', 'Samir', '002124878', '-', 1, 'temporary', '2026-02-26 16:54:45'),
(5, 'Komar Camplong', 'Komar Camplong', '08532154542', '-', 1, 'temporary', '2026-02-27 09:54:23'),
(6, 'Kiki Pangarengan', 'Kiki Pangarengan', '083225454', NULL, 1, 'temporary', '2026-02-27 10:51:26'),
(15, 'Ardi FPTI', NULL, '-', NULL, 1, 'temporary', '2026-09-25 06:26:37'),
(16, 'Dias Kecamatan Robatal', NULL, '-', 'Sampang', 1, 'temporary', '2026-09-27 09:01:41'),
(17, 'Muhammad Effendy', NULL, '-', 'Sampang', 1, 'temporary', '2026-09-27 09:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `konsumen_deposit`
--

CREATE TABLE `konsumen_deposit` (
  `id_deposit` bigint(20) UNSIGNED NOT NULL,
  `id_konsumen` bigint(20) UNSIGNED NOT NULL,
  `tipe` enum('Setoran','Pemakaian') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Setoran',
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode_bayar` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Tunai',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_deposit` date NOT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id_menu` bigint(20) UNSIGNED NOT NULL,
  `nama_menu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url_link` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `label_kustom` varchar(100) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `jenis` varchar(10) NOT NULL DEFAULT 'standar',
  `ikon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id_menu`, `nama_menu`, `url_link`, `parent_id`, `aktif`, `label_kustom`, `urutan`, `jenis`, `ikon`) VALUES
(1, 'Master Bahan', 'bahan-baku', 0, 1, NULL, 0, 'standar', NULL),
(3, 'Transaksi Baru', 'penjualan', 0, 1, NULL, 0, 'standar', NULL),
(4, 'Pengaturan Akses', 'setting-akses', 0, 1, NULL, 0, 'standar', NULL),
(5, 'Data Karyawan', 'karyawan', 0, 1, NULL, 0, 'standar', NULL),
(6, 'Data User', 'users', 0, 1, NULL, 0, 'standar', NULL),
(7, 'Data Konsumen', 'konsumen', 0, 1, NULL, 0, 'standar', NULL),
(8, 'Golongan Harga', 'golongan', 0, 1, NULL, 0, 'standar', NULL),
(9, 'Supplier Bahan', 'supplier', 0, 1, NULL, 0, 'standar', NULL),
(10, 'Satuan & Konversi', 'satuan', 0, 1, NULL, 0, 'standar', NULL),
(11, 'Bank Perusahaan', 'bank', 0, 1, NULL, 0, 'standar', NULL),
(12, 'Golongan Konsumen', 'golongan', 0, 1, NULL, 0, 'standar', NULL),
(13, 'Produk & BOM', 'produk', 0, 1, NULL, 0, 'standar', NULL),
(14, 'Price List & Level', 'setting-harga', 0, 1, NULL, 0, 'standar', NULL),
(15, 'Kategori', 'kategori', 0, 1, NULL, 0, 'standar', NULL),
(16, 'Kasir', 'kasir', 0, 1, NULL, 0, 'standar', NULL),
(17, 'Kedatangan Bahan', 'kedatangan-bahan', 0, 1, NULL, 0, 'standar', NULL),
(18, 'Golongan', 'golongan', 0, 1, NULL, 0, 'standar', NULL),
(20, 'Pembelian Bahan', 'pembelian-bahan', 0, 1, NULL, 0, 'standar', NULL),
(21, 'Finishing Produk', 'finishing', 0, 1, NULL, 0, 'standar', NULL),
(22, 'SPK', 'spk', 0, 1, NULL, 0, 'standar', NULL),
(23, 'Produksi', 'produksi', 0, 1, NULL, 0, 'standar', NULL),
(24, 'Collecting', 'collecting', 0, 1, NULL, 0, 'standar', NULL),
(25, 'Laporan Order', 'laporan/order', 0, 1, NULL, 0, 'standar', NULL),
(26, 'Laporan Produksi', 'laporan/produksi', 0, 1, NULL, 0, 'standar', NULL),
(27, 'Aset Mesin', 'mesin', 0, 1, NULL, 0, 'standar', NULL),
(28, 'Harga Promo', 'harga-promo', 0, 1, NULL, 0, 'standar', NULL),
(29, 'Laporan Kas', 'laporan/kas', 0, 1, NULL, 0, 'standar', NULL),
(30, 'Merk', 'merk', 0, 1, NULL, 0, 'standar', NULL),
(31, 'Jenis Bahan', 'jenis-bahan', 0, 1, NULL, 0, 'standar', NULL),
(32, 'Jasa', 'jasa', 0, 1, NULL, 0, 'standar', NULL),
(33, 'Gudang', 'gudang', 0, 1, NULL, 0, 'standar', NULL),
(34, 'Deposit Konsumen', 'konsumen-deposit', 0, 1, NULL, 0, 'standar', NULL),
(35, 'Chart of Accounts', 'akun', 0, 1, NULL, 0, 'standar', NULL),
(36, 'Laporan Keuangan', 'akuntansi', 0, 1, NULL, 0, 'standar', NULL),
(37, 'Pengaturan Aplikasi', 'pengaturan-aplikasi', 0, 1, NULL, 0, 'standar', NULL),
(38, 'Order Jersey', 'jersey', 0, 1, NULL, 0, 'standar', NULL),
(39, 'Kas Kecil', 'kas-kecil', 0, 1, NULL, 0, 'standar', NULL),
(40, 'Laporan Belanja Bahan', 'laporan/belanja-bahan', 0, 1, NULL, 0, 'standar', NULL),
(41, 'Kasir Utama (Input Order)', 'penjualan-input-order', 0, 1, NULL, 0, 'standar', NULL),
(42, 'Order Jersey (Input Order)', 'jersey-input-order', 0, 1, NULL, 0, 'standar', NULL),
(43, 'Laporan Order Jersey', 'laporan/order-jersey', 0, 1, NULL, 0, 'standar', NULL),
(44, 'Laporan Piutang', 'laporan/piutang', 0, 1, NULL, 0, 'standar', NULL),
(45, 'Kategori Kas Kecil', 'kategori-kas-kecil', 0, 1, NULL, 0, 'standar', NULL),
(46, 'Laporan Stok Bahan Baku', 'laporan/stok-bahan', 0, 1, NULL, 0, 'standar', NULL),
(47, 'Laporan HPP & Margin', 'laporan/hpp', 0, 1, NULL, 0, 'standar', NULL),
(48, 'Penjualan Barang', 'penjualan-barang', 0, 1, NULL, 0, 'standar', NULL),
(49, 'Log Aktivitas', 'log-aktivitas', 0, 1, NULL, 0, 'standar', NULL),
(50, 'Approval Workflow', 'approval', 0, 1, NULL, 0, 'standar', NULL),
(51, 'Accounting Period', 'accounting-period', 0, 1, NULL, 0, 'standar', NULL),
(52, 'Backup & Recovery', 'backup', 0, 1, NULL, 0, 'standar', NULL),
(53, 'Stock Opname', 'stock-opname', 0, 1, NULL, 0, 'standar', NULL),
(54, 'Purchase Request', 'purchase-request', 0, 1, NULL, 0, 'standar', NULL),
(55, 'Purchase Order', 'purchase-order', 0, 1, NULL, 0, 'standar', NULL),
(56, 'QC & Rework', 'qc', 0, 1, NULL, 0, 'standar', NULL),
(57, 'Waste Management', 'waste', 0, 1, NULL, 0, 'standar', NULL),
(58, 'Quotation', 'quotation', 0, 1, NULL, 0, 'standar', NULL),
(60, 'Audit Bulanan Owner', 'audit-owner', 0, 1, NULL, 0, 'standar', NULL),
(61, 'Riwayat Nota', 'penjualan/riwayat', 0, 1, NULL, 0, 'standar', NULL),
(62, 'Platform Belanja Online', 'platform-belanja', 0, 1, NULL, 0, 'standar', NULL),
(63, 'Jam Kerja', 'pengaturan-jam-kerja', 0, 1, NULL, 0, 'standar', NULL),
(64, 'SPK Desain (Cetak Lengkap)', 'spk-desain', 0, 1, NULL, 0, 'standar', NULL),
(65, 'Laporan Biaya Desain', 'laporan/biaya-desain', 0, 1, NULL, 0, 'standar', NULL),
(66, 'Bagi Hasil Desainer', 'pengaturan-desainer', 0, 1, NULL, 0, 'standar', NULL),
(67, 'Info & Panduan', 'postingan', 0, 1, NULL, 0, 'standar', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `merk`
--

CREATE TABLE `merk` (
  `id_merk` bigint(20) UNSIGNED NOT NULL,
  `nama_merk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `merk`
--

INSERT INTO `merk` (`id_merk`, `nama_merk`, `keterangan`, `created_at`, `updated_at`) VALUES
(2, 'Semua Merk', NULL, '2026-09-10 07:54:12', '2026-09-10 07:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `mesin`
--

CREATE TABLE `mesin` (
  `id_mesin` bigint(20) UNSIGNED NOT NULL,
  `nama_mesin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_mesin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tampil_divisi` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mesin`
--

INSERT INTO `mesin` (`id_mesin`, `nama_mesin`, `tipe_mesin`, `tampil_divisi`) VALUES
(1, 'EPSON S80670', 'Eco-solvent', 1),
(2, 'FLORA OUTDOOR', 'Solvent', 1),
(3, 'KONICA MINOLTA', 'Laser A3', 1),
(4, 'CENTURY STAR', 'Direct Print', 1),
(5, 'RHINO DTF', 'Digital Sablon', 1),
(6, 'ESPON F7270', 'Sublime', 1),
(7, 'LAINNYA', 'Multi-print', 1),
(9, 'ROLAND G540', 'Cutting Plotter', 0);

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
(1, '2026_02_25_100910_create_master_karyawan_table', 1),
(2, '2026_02_25_100910_create_rbac_tables', 1),
(3, '2026_02_25_101844_create_sessions_table', 1),
(4, '2026_02_25_104514_create_master_produksi_tables', 1),
(5, '2026_02_25_111057_create_complex_system_tables', 1),
(6, '2026_02_25_112610_add_missing_erp_tables', 1),
(7, '2026_02_26_001742_create_cache_table', 1),
(8, '2026_02_26_125746_fix_transaksi_columns', 1),
(9, '2026_03_01_174529_add_tipe_satuan_to_satuan_table', 1),
(10, '2026_03_05_102450_create_kartu_stoks_table', 1),
(11, '2026_09_07_000001_reconcile_inventory_module_schema', 1),
(12, '2026_09_10_001021_add_spk_produksi_fields', 1),
(13, '2026_09_10_010000_add_collecting_menu', 1),
(14, '2026_09_10_020000_add_laporan_menu', 1),
(15, '2026_09_10_030000_fix_menu_access_gaps', 1),
(16, '2026_09_10_040000_add_harga_promo_menu', 1),
(17, '2026_09_10_050000_add_finishing_fields_to_transaksi_detail', 1),
(18, '2026_09_10_060000_add_bank_to_pembayaran_riwayat', 1),
(19, '2026_09_10_070000_add_laporan_kas_menu', 1),
(20, '2026_09_10_080000_drop_orphaned_kartu_stoks_and_kas_sesi_tables', 1),
(21, '2026_09_10_090000_backfill_bahan_baku_satuan_relations', 1),
(22, '2026_09_10_100000_add_harga_rata_rata_to_bahan_baku', 1),
(23, '2026_09_10_110000_add_hpp_snapshot_to_transaksi_detail', 1),
(24, '2026_09_10_120000_create_bag_akun_table', 1),
(25, '2026_09_10_130000_add_kode_akun_fk_to_jurnal_umum', 1),
(26, '2026_09_10_140000_create_merk_table', 1),
(27, '2026_09_10_150000_create_jenis_bahan_table', 1),
(28, '2026_09_10_160000_add_merk_and_jenis_bahan_to_bahan_baku', 1),
(29, '2026_09_10_170000_create_jasa_table', 1),
(30, '2026_09_10_180000_enrich_gudang_table', 1),
(31, '2026_09_10_190000_create_konsumen_deposit_table', 1),
(32, '2026_09_10_200000_register_new_master_data_menus', 1),
(33, '2026_09_10_210000_fix_gudang_id_auto_increment', 1),
(34, '2026_09_10_220000_backfill_satuan_stok_dash_values', 1),
(35, '2026_09_10_230000_widen_stock_decimal_precision', 1),
(36, '2026_09_10_240000_add_deposit_and_jasa_accounts', 1),
(37, '2026_09_10_250000_add_tipe_to_konsumen_deposit', 1),
(38, '2026_09_10_260000_add_deposit_to_pembayaran_riwayat_metode_enum', 1),
(39, '2026_09_10_270000_add_jasa_support_to_transaksi_detail', 1),
(40, '2026_09_10_280000_add_dimensi_roll_to_bahan_baku', 1),
(41, '2026_09_10_290000_register_akuntansi_menus', 1),
(42, '2026_09_10_300000_extend_order_kantor_fields', 1),
(43, '2026_09_10_310000_fix_kartu_stok_bahan_fk_to_restrict', 1),
(44, '2026_09_10_320000_split_frontlite_280_into_roll_width_variants', 1),
(45, '2026_09_11_100000_create_karyawan_mesin_table', 1),
(46, '2026_09_11_110000_expand_kasir_role_to_full_finance_scope', 1),
(47, '2026_09_11_120000_fix_operator_desain_role_permissions', 1),
(48, '2026_09_11_130000_fix_stale_menu_url_link_labels', 1),
(49, '2026_09_11_140000_create_operator_produksi_role', 1),
(50, '2026_09_11_150000_create_gudang_role', 1),
(51, '2026_09_11_160000_add_collecting_status_to_transaksi', 1),
(52, '2026_09_11_170000_fix_konsumen_deposit_fk_to_restrict', 1),
(53, '2026_09_11_180000_add_missing_fk_kedatangan_bahan_gudang', 1),
(54, '2026_09_11_190000_add_pending_status_to_transaksi_detail', 1),
(55, '2026_09_11_190100_add_status_nota_to_transaksi', 1),
(56, '2026_09_11_200000_create_finishing_harga_table', 1),
(57, '2026_09_11_200100_create_jasa_harga_table', 1),
(58, '2026_09_11_210000_add_biaya_desain_potongan_to_transaksi_detail', 1),
(59, '2026_09_11_220000_add_status_to_produk', 1),
(60, '2026_09_11_230000_create_nota_edit_request_table', 1),
(61, '2026_09_11_240000_make_range_min_max_decimal_in_harga_tables', 1),
(62, '2026_09_11_250000_add_debit_metode_bayar', 1),
(63, '2026_09_12_010000_create_kas_manual_table', 1),
(64, '2026_09_12_020000_add_status_rolling_to_kas_harian', 1),
(65, '2026_09_12_030000_restore_missing_timestamps_on_transaksi', 1),
(66, '2026_09_12_030100_restore_missing_timestamps_on_transaksi_detail', 1),
(67, '2026_09_12_040000_recreate_missing_laravel_framework_tables', 1),
(68, '2026_09_12_150000_create_app_settings_table', 1),
(69, '2026_09_12_160000_create_jersey_module_tables', 1),
(70, '2026_09_12_170000_restructure_jersey_multi_item_orders', 1),
(71, '2026_09_12_220000_fix_corrupted_default_quotes', 1),
(72, '2026_09_12_223000_add_material_to_jersey_price_settings', 1),
(73, '2026_09_13_010000_add_size_surcharge_total_to_jersey_order_lines', 1),
(74, '2026_09_13_020000_add_weight_to_jersey_size_component_usage', 1),
(75, '2026_09_13_030000_create_jersey_order_payments_table', 1),
(76, '2026_09_13_040000_add_sleeve_collar_master_data_to_jersey', 1),
(77, '2026_09_13_050000_add_is_susulan_to_jersey_order_lines', 1),
(78, '2026_09_13_060000_convert_sleeve_column_to_varchar', 1),
(79, '2026_09_13_070000_add_has_sleeve_collar_to_jersey_product_types', 1),
(80, '2026_09_12_180000_add_material_estimation_to_jersey', 1),
(81, '2026_09_16_010000_add_scope_to_jersey_components', 1),
(82, '2026_09_16_020000_add_price_surcharge_to_jersey_sleeve_collar_types', 1),
(83, '2026_09_16_030000_create_kas_kecil_table', 1),
(84, '2026_09_16_040000_scope_operator_desain_to_input_order_only', 1),
(85, '2026_09_16_180000_add_fax_website_to_app_settings', 1),
(86, '2026_09_17_090000_add_laporan_piutang_menu', 1),
(87, '2026_09_17_100000_create_kategori_kas_kecil_table', 1),
(88, '2026_09_17_100100_add_kategori_kas_kecil_menu', 1),
(89, '2026_09_17_110000_add_laporan_stok_bahan_menu', 1),
(90, '2026_09_17_120000_grant_gudang_laporan_stok_bahan', 1),
(91, '2026_09_17_130000_add_libur_kas_minggu_to_app_settings', 1),
(92, '2026_09_17_140000_add_laporan_hpp_menu', 1),
(93, '2026_09_18_090000_add_nota_settings_to_app_settings', 1),
(94, '2026_09_18_100000_add_id_bahan_to_transaksi_detail', 1),
(95, '2026_09_18_110000_add_penjualan_barang_menu', 1),
(96, '2026_09_18_120000_add_pembulatan_nota_to_app_settings', 1),
(97, '2026_09_18_130000_add_beban_usaha_accounts', 1),
(98, '2026_09_18_130100_add_kode_akun_to_kategori_kas_kecil', 1),
(99, '2026_09_23_150000_create_bom_finishing_table', 1),
(100, '2026_09_23_160000_fix_inconsistent_id_column_types', 2),
(101, '2026_09_23_170000_add_soft_deletes_to_ledger_tables', 3),
(102, '2026_09_10_045900_create_finishing_table', 1),
(103, '2026_02_25_101900_create_bank_perusahaan_table', 1),
(104, '2026_02_25_101901_create_kategori_finishing_table', 1),
(105, '2026_02_25_101902_create_worksheet_table', 1),
(106, '2026_09_10_045901_create_produk_finishing_table', 1),
(107, '2026_09_10_055000_create_pembayaran_riwayat_table', 1),
(108, '2026_09_11_000000_create_kas_harian_table', 1),
(109, '2026_02_25_111058_add_id_mesin_to_produk_table', 1),
(110, '2026_09_23_180000_create_audit_logs_table', 4),
(111, '2026_09_23_190000_add_log_aktivitas_menu', 4),
(113, '2026_09_23_200000_create_document_number_counters_table', 5),
(114, '2026_09_23_210000_create_approval_requests_table', 6),
(115, '2026_09_23_220000_add_approval_menu', 6),
(116, '2026_09_23_230000_create_accounting_periods_table', 7),
(117, '2026_09_23_240000_add_accounting_period_menu', 7),
(118, '2026_09_23_250000_create_backup_logs_table', 8),
(119, '2026_09_23_260000_add_backup_menu', 8),
(120, '2026_09_23_270000_create_stock_opnames_table', 9),
(121, '2026_09_23_280000_add_stock_opname_menu', 9),
(122, '2026_09_24_100000_create_purchase_request_order_tables', 10),
(123, '2026_09_24_110000_add_purchase_menus', 10),
(124, '2026_09_24_120000_add_purchase_order_link_to_kedatangan_bahan', 11),
(125, '2026_09_24_120000_reconcile_supplier_kontak_column', 11),
(126, '2026_09_24_130000_create_pembayaran_hutang_table', 12),
(127, '2026_09_24_130000_reconcile_pembelian_setting_harga_produk_schema', 12),
(128, '2026_09_24_140000_add_missing_columns_used_by_code', 12),
(129, '2026_09_24_140000_add_jersey_accounting_support', 13),
(130, '2026_09_24_150000_create_qc_rework_tables', 14),
(131, '2026_09_24_160000_add_qc_menu', 15),
(132, '2026_09_24_170000_create_waste_log_table', 16),
(133, '2026_09_24_180000_create_quotation_tables', 17),
(134, '2026_09_24_190000_create_design_proofs_table', 18),
(135, '2026_09_24_200000_create_pengambilan_and_order_berkas_tables', 19),
(136, '2026_09_24_150000_restore_jersey_master_estimation', 20),
(137, '2026_09_24_160000_correct_jersey_estimation_reference', 21),
(138, '2026_09_24_170000_fill_jersey_collar_estimation', 22),
(139, '2026_09_24_180000_audit_jersey_estimation_realism', 23),
(140, '2026_09_24_190000_seed_jersey_reference_master', 24),
(141, '2026_09_24_200000_register_ink_raw_materials', 25),
(142, '2026_09_24_210000_create_pemakaian_tinta_table', 26),
(143, '2026_09_24_220000_add_logo_display_options_to_app_settings', 27),
(144, '2026_09_25_100000_add_boleh_lihat_harga_to_roles', 28),
(145, '2026_09_26_100000_add_owner_role_and_audit_menu', 29),
(146, '2026_09_27_100000_permission_matrix_and_menu_editor', 30),
(147, '2026_09_28_100000_operator_desain_produksi_hanya_lihat', 31),
(148, '2026_09_29_100000_jersey_size_kelompok_percent', 32),
(149, '2026_09_30_100000_jersey_price_kelompok', 33),
(150, '2026_10_01_100000_pembelian_online', 34),
(151, '2026_10_02_100000_mesin_tampil_divisi', 35),
(152, '2026_10_03_100000_transaksi_detail_finishing', 36),
(153, '2026_10_04_100000_produk_spk_lengkap', 37),
(154, '2026_10_05_100000_jersey_material_id_bahan', 38),
(155, '2026_10_06_100000_jersey_potong_stok', 39),
(156, '2026_10_07_100000_akses_menu_lengkap_dan_url_akses', 40),
(157, '2026_10_08_100000_kas_kecil_buku_kas', 41),
(158, '2026_10_08_110000_akun_kas_kecil_terpisah', 42),
(159, '2026_10_09_100000_komisi_desain', 43),
(160, '2026_10_10_100000_kelas_desainer_persen_awal', 44),
(161, '2026_10_10_110000_pembayaran_hutang_metode_kas_kecil', 45),
(162, '2026_10_11_100000_perbaiki_jatuh_tempo_pembelian', 46),
(163, '2026_10_12_100000_postingan', 47),
(164, '2026_10_13_100000_harga_siap_cetak', 48),
(165, '2026_10_14_100000_transaksi_detail_bahan_pakai', 49),
(166, '2026_10_15_100000_index_hardening_p1', 50),
(167, '2026_10_15_110000_hapus_fk_kartu_stok_duplikat', 51),
(168, '2026_10_16_100000_backup_logs_offsite', 52);

-- --------------------------------------------------------

--
-- Table structure for table `nota_edit_request`
--

CREATE TABLE `nota_edit_request` (
  `id_request` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `id_user_requester` bigint(20) UNSIGNED NOT NULL,
  `alasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak','Selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Menunggu',
  `id_user_approver` bigint(20) UNSIGNED DEFAULT NULL,
  `catatan_approver` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_keputusan` timestamp NULL DEFAULT NULL,
  `tgl_selesai` timestamp NULL DEFAULT NULL,
  `id_transaksi_koreksi` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nota_edit_request`
--

INSERT INTO `nota_edit_request` (`id_request`, `id_transaksi`, `id_user_requester`, `alasan`, `status`, `id_user_approver`, `catatan_approver`, `tgl_keputusan`, `tgl_selesai`, `id_transaksi_koreksi`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'nota uji coba', 'Selesai', 1, NULL, '2026-09-27 14:46:23', '2026-09-27 14:46:27', NULL, '2026-09-27 14:46:13', '2026-09-27 14:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_berkas`
--

CREATE TABLE `order_berkas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `id_detail` bigint(20) UNSIGNED DEFAULT NULL,
  `kategori` enum('Production File','QC Photo','Lainnya') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `nama_file_asli` varchar(255) NOT NULL,
  `ukuran_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `catatan` varchar(255) DEFAULT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `pemakaian_tinta`
--

CREATE TABLE `pemakaian_tinta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_mesin` int(10) UNSIGNED NOT NULL,
  `id_bahan` int(10) UNSIGNED NOT NULL,
  `dasar` enum('m2','lembar') NOT NULL DEFAULT 'm2',
  `pemakaian` decimal(12,6) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pemakaian_tinta`
--

INSERT INTO `pemakaian_tinta` (`id`, `id_mesin`, `id_bahan`, `dasar`, `pemakaian`, `keterangan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 43, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(2, 2, 44, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(3, 4, 46, 'm2', 18.000000, 'ml per m2 (direct print)', 1, '2026-09-24 12:02:41', '2026-09-24 12:02:41'),
(4, 5, 47, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(5, 5, 48, 'm2', 8.000000, 'ml per m2 (white)', 1, '2026-09-24 12:02:41', '2026-09-24 12:02:41'),
(6, 5, 49, 'm2', 25.000000, 'gram per m2 (powder)', 1, '2026-09-24 12:02:41', '2026-09-24 12:02:41'),
(7, 6, 50, 'm2', 2.250000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(8, 3, 45, 'lembar', 0.000050, 'pcs toner per lembar (asumsi 1 set = 20.000 lembar)', 1, '2026-09-24 12:02:41', '2026-09-24 12:02:41'),
(17, 1, 117, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(18, 1, 118, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(19, 1, 119, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(20, 1, 120, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(21, 1, 121, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(22, 1, 122, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(23, 1, 123, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(24, 1, 124, 'm2', 1.111111, 'Estimasi awal: total 10 ml/m2 dibagi rata 9 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 11:44:25', '2026-09-25 11:44:25'),
(34, 2, 136, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(35, 2, 137, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(36, 2, 138, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(37, 5, 139, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(38, 5, 140, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(39, 5, 141, 'm2', 3.000000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(40, 6, 142, 'm2', 2.250000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(41, 6, 143, 'm2', 2.250000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37'),
(42, 6, 144, 'm2', 2.250000, 'Estimasi awal: total pemakaian dibagi rata 4 warna - koreksi dengan pemakaian nyata', 1, '2026-09-25 12:29:37', '2026-09-25 12:29:37');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_hutang`
--

CREATE TABLE `pembayaran_hutang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_pembelian` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` varchar(20) NOT NULL DEFAULT 'Tunai',
  `keterangan` varchar(255) DEFAULT NULL,
  `no_referensi_jurnal` varchar(100) DEFAULT NULL,
  `bagian_faktur` tinyint(1) NOT NULL DEFAULT 0,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `void_alasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_riwayat`
--

CREATE TABLE `pembayaran_riwayat` (
  `id_pembayaran` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` enum('Tunai','Transfer','Debit','Deposit') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_bank` bigint(20) UNSIGNED DEFAULT NULL,
  `tgl_bayar` datetime NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembelian_bahan`
--

CREATE TABLE `pembelian_bahan` (
  `id_pembelian` bigint(20) UNSIGNED NOT NULL,
  `id_kedatangan` bigint(20) UNSIGNED NOT NULL,
  `no_faktur` varchar(255) NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `tipe_pembelian` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jatuh_tempo_hari` int(11) DEFAULT NULL,
  `jatuh_tempo_tgl` date DEFAULT NULL,
  `status_pajak` varchar(50) DEFAULT NULL,
  `include_pajak` tinyint(1) DEFAULT 0,
  `grand_total` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_pembayaran` enum('Hutang','Lunas') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Hutang',
  `tgl_pelunasan` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ongkir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_layanan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `voucher` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembelian_bahan`
--

INSERT INTO `pembelian_bahan` (`id_pembelian`, `id_kedatangan`, `no_faktur`, `tgl_transaksi`, `tipe_pembelian`, `jatuh_tempo_hari`, `jatuh_tempo_tgl`, `status_pajak`, `include_pajak`, `grand_total`, `created_at`, `updated_at`, `status_pembayaran`, `tgl_pelunasan`, `deleted_at`, `ongkir`, `biaya_layanan`, `voucher`) VALUES
(1, 1, 'JL.0068041', '2026-09-08', 'Tempo', 30, '2026-10-08', NULL, 1, 3642509.40, '2026-09-26 00:42:16', '2026-09-26 00:42:16', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(2, 2, 'JL.0066627', '2026-08-18', 'Tempo', 30, '2026-09-17', NULL, 1, 7061020.80, '2026-09-26 00:50:07', '2026-09-26 01:03:45', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(3, 3, 'JL.0066832', '2026-08-20', 'Tempo', 30, '2026-09-19', NULL, 1, 6831994.50, '2026-09-26 00:57:48', '2026-09-26 01:04:02', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(4, 4, 'CSBSBY262767', '2026-08-24', 'Tempo', 30, '2026-09-23', NULL, 0, 10684800.00, '2026-09-26 01:01:45', '2026-09-26 01:04:51', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(5, 5, 'SO-WU/0926/00049', '2026-09-01', 'Tempo', 30, '2026-10-26', NULL, 1, 4103998.56, '2026-09-26 01:11:53', '2026-09-26 01:12:23', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(6, 6, 'SO-WU/0926/02643', '2026-09-21', 'Tempo', 30, '2026-10-21', NULL, 1, 1899999.21, '2026-09-27 09:51:05', '2026-09-27 09:51:05', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(7, 7, 'FJ-26-09-00089', '2026-09-21', 'Tempo', 30, '2026-10-21', NULL, 0, 2680000.00, '2026-09-27 10:13:39', '2026-09-27 10:13:39', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00),
(8, 8, 'SJ-26-09-00060', '2026-09-14', 'Tempo', 30, '2026-10-14', NULL, 0, 1292000.00, '2026-09-27 10:14:14', '2026-09-27 10:14:14', 'Hutang', NULL, NULL, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `pembelian_bahan_detail`
--

CREATE TABLE `pembelian_bahan_detail` (
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_pembelian` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) DEFAULT 0.00,
  `potongan` decimal(15,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembelian_bahan_detail`
--

INSERT INTO `pembelian_bahan_detail` (`id_detail`, `id_pembelian`, `id_bahan`, `qty`, `harga`, `diskon`, `potongan`, `subtotal`) VALUES
(1, 1, 7, 2.00, 1640770.00, 0.00, 0.00, 3281540.00),
(2, 2, 7, 4.00, 254960.00, 0.00, 0.00, 1019840.00),
(3, 2, 27, 2.00, 839370.00, 0.00, 0.00, 1678740.00),
(4, 2, 1, 3.00, 1220900.00, 0.00, 0.00, 3662700.00),
(5, 3, 1, 5.00, 1230990.00, 0.00, 0.00, 6154950.00),
(6, 4, 1, 5.00, 1612800.00, 0.00, 0.00, 8064000.00),
(7, 4, 28, 2.00, 1310400.00, 0.00, 0.00, 2620800.00),
(8, 5, 5, 6.00, 616216.00, 0.00, 0.00, 3697296.00),
(9, 6, 147, 1.00, 1711711.00, 0.00, 0.00, 1711711.00),
(10, 7, 96, 4.00, 100000.00, 0.00, 0.00, 400000.00),
(11, 7, 9, 2.00, 1140000.00, 0.00, 0.00, 2280000.00),
(12, 8, 88, 2.00, 646000.00, 0.00, 0.00, 1292000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pengambilan`
--

CREATE TABLE `pengambilan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `jenis` enum('Diambil Sendiri','Diambil Perwakilan','Dikirim') NOT NULL DEFAULT 'Diambil Sendiri',
  `nama_penerima` varchar(150) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `bukti_path` varchar(500) DEFAULT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform_belanja`
--

CREATE TABLE `platform_belanja` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_belanja`
--

INSERT INTO `platform_belanja` (`id`, `nama`, `catatan`, `aktif`, `urutan`, `created_at`, `updated_at`) VALUES
(1, 'Tokopedia', NULL, 1, 1, '2026-09-25 07:36:24', '2026-09-25 07:36:24'),
(2, 'Shopee', NULL, 1, 2, '2026-09-25 07:36:24', '2026-09-25 07:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `ringkasan` varchar(300) DEFAULT NULL,
  `isi` longtext NOT NULL,
  `gambar_sampul` varchar(255) DEFAULT NULL,
  `id_kategori` bigint(20) UNSIGNED DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'draft',
  `terbit_at` timestamp NULL DEFAULT NULL,
  `disematkan` tinyint(1) NOT NULL DEFAULT 0,
  `id_penulis` bigint(20) UNSIGNED DEFAULT NULL,
  `dibaca` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `menit_baca` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `judul`, `slug`, `ringkasan`, `isi`, `gambar_sampul`, `id_kategori`, `tags`, `status`, `terbit_at`, `disematkan`, `id_penulis`, `dibaca`, `menit_baca`, `created_at`, `updated_at`, `deleted_at`) VALUES
(8, 'Orion Grafika Sampang: Solusi Percetakan untuk Bisnis, Acara, Instansi, dan Kebutuhan Custom', 'orion-grafika-sampang-solusi-percetakan-untuk-bisnis-acara-instansi-dan-kebu', 'Orion Grafika Sampang: Dari Ide Menjadi Hasil Cetak', '<p class=\"ql-spasi-rapat\">Tidak semua kebutuhan cetak membutuhkan solusi yang sama. Ada yang membutuhkan banner untuk promosi usaha, ada yang membutuhkan stiker untuk branding produk, ada yang sedang mempersiapkan acara, membutuhkan umbul-umbul dan media promosi, atau membutuhkan berbagai produk cetak untuk sekolah, kantor, instansi, organisasi, komunitas, dan kebutuhan pribadi.</p><p class=\"ql-spasi-rapat\">Di sinilah Orion Grafika Sampang hadir sebagai salah satu pilihan percetakan dan digital printing untuk membantu mewujudkan kebutuhan tersebut.</p><h3 class=\"ql-spasi-rapat\"><strong>Berawal dari 2018 dan Terus Berkembang</strong></h3><p class=\"ql-spasi-rapat\">Orion Grafika mulai berdiri pada Maret 2018 dan sejak awal bergerak di bidang jasa percetakan dan digital printing. Dalam perkembangannya, Orion tidak hanya mengerjakan satu jenis produk, tetapi menangani berbagai kebutuhan cetak dan media promosi.</p><p class=\"ql-spasi-rapat\">Perkembangan tersebut juga terlihat dari semakin luasnya jenis produk yang ditawarkan, mulai dari media promosi hingga berbagai produk cetak dan custom.</p><p class=\"ql-spasi-rapat\">Konsep yang sejak awal dibawa Orion adalah “Fast Print Solution”, dengan perhatian pada kecepatan pengerjaan, pelayanan konsumen, dan hasil cetak.</p><h3 class=\"ql-spasi-rapat\"><strong>Bisa Cetak Apa Saja di Orion Grafika?</strong></h3><p class=\"ql-spasi-rapat\">Kebutuhan setiap pelanggan berbeda. Karena itu, layanan percetakan tidak berhenti hanya pada banner. Beberapa jenis produk yang pernah tercatat sebagai layanan Orion Grafika antara lain:</p><p class=\"ql-spasi-rapat\">📢 Media Promosi</p><ul><li class=\"ql-spasi-rapat\">Banner</li><li class=\"ql-spasi-rapat\">Spanduk</li><li class=\"ql-spasi-rapat\">Sticker</li><li class=\"ql-spasi-rapat\">Sticker outdoor</li><li class=\"ql-spasi-rapat\">Umbul-umbul</li><li class=\"ql-spasi-rapat\">Baliho/media promosi</li><li class=\"ql-spasi-rapat\">Papan nama</li><li class=\"ql-spasi-rapat\">Neonbox</li><li class=\"ql-spasi-rapat\">Media promosi lainnya</li></ul><p class=\"ql-spasi-rapat\">🏢 Kebutuhan Bisnis &amp; Branding</p><ul><li class=\"ql-spasi-rapat\">Kartu nama</li><li class=\"ql-spasi-rapat\">Brosur</li><li class=\"ql-spasi-rapat\">Nota</li><li class=\"ql-spasi-rapat\">Sticker produk</li><li class=\"ql-spasi-rapat\">Paper bag custom</li><li class=\"ql-spasi-rapat\">Papan nama</li><li class=\"ql-spasi-rapat\">Media branding usaha</li><li class=\"ql-spasi-rapat\">Berbagai kebutuhan promosi bisnis</li></ul><p class=\"ql-spasi-rapat\">🎉 Kebutuhan Acara</p><ul><li class=\"ql-spasi-rapat\">Banner acara</li><li class=\"ql-spasi-rapat\">Spanduk</li><li class=\"ql-spasi-rapat\">Umbul-umbul</li><li class=\"ql-spasi-rapat\">ID Card</li><li class=\"ql-spasi-rapat\">Piagam</li><li class=\"ql-spasi-rapat\">Kalender</li><li class=\"ql-spasi-rapat\">Undangan</li><li class=\"ql-spasi-rapat\">Merchandise dan produk pendukung acara</li></ul><p class=\"ql-spasi-rapat\">🎁 Produk Custom</p><ul><li class=\"ql-spasi-rapat\">Mug</li><li class=\"ql-spasi-rapat\">Pin</li><li class=\"ql-spasi-rapat\">Plakat akrilik</li><li class=\"ql-spasi-rapat\">Paper bag custom</li><li class=\"ql-spasi-rapat\">Produk promosi dan souvenir lainnya</li></ul><p class=\"ql-spasi-rapat\">Jenis produk yang tersedia dapat berkembang sesuai kebutuhan dan kapasitas produksi Orion Grafika. Katalog online Orion saat ini juga mencantumkan plakat akrilik, paper bag custom, banner Frontlite beberapa gramasi, serta umbul-umbul/bendera direct print.</p><h3 class=\"ql-spasi-rapat\"><strong>Bukan Sekadar Cetak</strong></h3><p class=\"ql-spasi-rapat\">Percetakan yang baik bukan hanya tentang memasukkan file ke mesin lalu mencetaknya.</p><p class=\"ql-spasi-rapat\">Ada beberapa hal yang perlu diperhatikan sebelum sebuah produk dibuat:</p><p class=\"ql-spasi-rapat\">Ukuran → bahan → desain → metode cetak → finishing → kebutuhan penggunaan.</p><p class=\"ql-spasi-rapat\">Banner untuk penggunaan indoor tentu memiliki pertimbangan berbeda dengan media yang dipasang outdoor. Sticker untuk kemasan produk juga membutuhkan pertimbangan berbeda dengan sticker untuk dekorasi.</p><p class=\"ql-spasi-rapat\">Begitu pula kebutuhan perusahaan, sekolah, organisasi, event, maupun UMKM.</p><p class=\"ql-spasi-rapat\">Karena itu, sebelum mencetak, pelanggan sebaiknya menentukan terlebih dahulu produk seperti apa yang paling sesuai dengan kebutuhan dan penggunaannya.</p><h3 class=\"ql-spasi-rapat\"><strong>Untuk UMKM: Cetak Bukan Hanya Pajangan</strong></h3><p class=\"ql-spasi-rapat\">Bagi pemilik usaha, media cetak merupakan bagian dari identitas bisnis. Sebuah banner bukan hanya berfungsi sebagai tulisan besar di depan toko.</p><p class=\"ql-spasi-rapat\">Banner harus membantu orang memahami:</p><h4 class=\"ql-spasi-rapat\">Apa yang dijual?</h4><h4 class=\"ql-spasi-rapat\">Siapa yang menjualnya?</h4><h4 class=\"ql-spasi-rapat\">Apa yang ditawarkan?</h4><h4 class=\"ql-spasi-rapat\">Bagaimana cara menghubunginya?</h4><p class=\"ql-spasi-rapat\">Begitu juga dengan sticker, paper bag, kartu nama, papan nama, dan media branding lainnya.</p><p class=\"ql-spasi-rapat\">Jika digunakan dengan konsep yang tepat, berbagai media tersebut dapat membantu sebuah usaha terlihat lebih profesional dan mudah dikenali.</p><p class=\"ql-spasi-rapat\">Orion Grafika dapat menjadi bagian dari proses tersebut — dari kebutuhan cetak sederhana sampai kebutuhan branding yang lebih lengkap.</p><h3 class=\"ql-spasi-rapat\"><strong>Untuk Acara, Sekolah, Kantor, dan Organisasi</strong></h3><p class=\"ql-spasi-rapat\">Sebuah acara sering membutuhkan banyak produk sekaligus.</p><p class=\"ql-spasi-rapat\">Mulai dari:</p><p class=\"ql-spasi-rapat\">Banner → spanduk → ID card → umbul-umbul → piagam → sticker → merchandise → media pendukung lainnya.</p><p class=\"ql-spasi-rapat\">Daripada mencari tempat berbeda untuk setiap kebutuhan, pelanggan dapat mengonsultasikan kebutuhan produksinya terlebih dahulu agar jenis produk, ukuran, bahan, dan jumlahnya dapat disesuaikan dengan kebutuhan acara.</p><p class=\"ql-spasi-rapat\">Orion Grafika juga memiliki pengalaman menangani kebutuhan berbagai pihak dan pernah tercatat dalam sumber publik sebagai salah satu pelaku digital printing di Madura yang melayani pelanggan termasuk instansi pemerintah.</p><h3 class=\"ql-spasi-rapat\"><strong>Bagaimana dengan Hasil Cetaknya?</strong></h3><p class=\"ql-spasi-rapat\">Dalam dunia printing, hasil akhir dipengaruhi oleh banyak faktor.</p><h3 class=\"ql-spasi-rapat\"><strong>Tidak hanya mesin.</strong></h3><p class=\"ql-spasi-rapat\">Kualitas file desain, resolusi gambar, jenis bahan, ukuran cetak, metode printing, tinta, proses finishing, hingga cara pemasangan dapat memengaruhi hasil akhir.</p><p class=\"ql-spasi-rapat\">Karena itu, kebutuhan cetak sebaiknya disampaikan secara lengkap sejak awal.</p><h4 class=\"ql-spasi-rapat\">Ukuran berapa?</h4><h4 class=\"ql-spasi-rapat\">Dipakai indoor atau outdoor?</h4><h4 class=\"ql-spasi-rapat\">Jumlah berapa?</h4><h4 class=\"ql-spasi-rapat\">Bahan apa yang dibutuhkan?</h4><h4 class=\"ql-spasi-rapat\">Kapan harus selesai?</h4><h4 class=\"ql-spasi-rapat\">Apakah sudah memiliki desain?</h4><p class=\"ql-spasi-rapat\">Semakin jelas kebutuhan yang disampaikan, semakin mudah proses produksi diarahkan sesuai tujuan.</p><p class=\"ql-spasi-rapat\">Punya Desain Sendiri? Bisa Dibicarakan.</p><p class=\"ql-spasi-rapat\">Sudah memiliki file desain?</p><p class=\"ql-spasi-rapat\">Kirimkan file dan sampaikan:</p><p class=\"ql-spasi-rapat\">“Saya ingin mencetak desain ini.”</p><p class=\"ql-spasi-rapat\"><strong>Belum memiliki desain?</strong></p><p class=\"ql-spasi-rapat\">Sampaikan kebutuhan dan konsep yang diinginkan terlebih dahulu.</p><p class=\"ql-spasi-rapat\">Misalnya:</p><p class=\"ql-spasi-rapat\">“Saya punya usaha makanan dan ingin membuat banner depan toko.”</p><p class=\"ql-spasi-rapat\">atau:</p><p class=\"ql-spasi-rapat\">“Saya membutuhkan banner untuk acara sekolah ukuran 3 × 1 meter.”</p><p class=\"ql-spasi-rapat\">atau:</p><p class=\"ql-spasi-rapat\">“Saya ingin membuat sticker untuk kemasan produk.”</p><p class=\"ql-spasi-rapat\">Dari informasi tersebut, kebutuhan cetak dapat ditentukan dengan lebih tepat.</p><p class=\"ql-spasi-rapat\">Mengapa Banyak Kebutuhan Cetak Bisa Berbeda?</p><p class=\"ql-spasi-rapat\">Karena setiap produk memiliki tujuan.</p><p class=\"ql-spasi-rapat\">Banner → menarik perhatian.</p><p class=\"ql-spasi-rapat\">Sticker → memperkuat identitas produk.</p><p class=\"ql-spasi-rapat\">Kartu nama → memperkenalkan bisnis dan kontak.</p><p class=\"ql-spasi-rapat\">Paper bag → membawa produk sekaligus membawa identitas brand.</p><p class=\"ql-spasi-rapat\">Papan nama → membuat lokasi usaha lebih mudah dikenali.</p><p class=\"ql-spasi-rapat\">Umbul-umbul → membangun suasana dan visibilitas acara.</p><p class=\"ql-spasi-rapat\">Plakat → menjadi bentuk penghargaan atau kenang-kenangan.</p><p class=\"ql-spasi-rapat\">Jadi, sebelum memilih produk cetak, pertanyaan terpenting bukan:</p><h3>“Mau cetak apa?”</h3><p class=\"ql-spasi-rapat\">Tetapi:</p><h3>“Apa yang ingin dicapai?”</h3><h3>Orion Grafika Sampang</h3><p class=\"ql-spasi-rapat\">Dengan pengalaman sejak 2018 dan berbagai jenis kebutuhan cetak yang pernah ditangani, Orion Grafika terus berkembang sebagai bagian dari ekosistem percetakan dan digital printing di Sampang.</p><p class=\"ql-spasi-rapat\">Saat ini Orion Grafika juga tercatat di Google dengan rating 4,5/5 dari 34 ulasan, sehingga calon pelanggan dapat melihat informasi lokasi dan ulasan pelanggan secara langsung sebelum berkunjung. Orion Grafika</p><p class=\"ql-spasi-rapat\">📍 Orion Grafika — Sampang, Madura</p><p class=\"ql-spasi-rapat\">📞 Hubungi Orion Grafika untuk kebutuhan cetak Anda.</p><p class=\"ql-spasi-rapat\">Tidak perlu bingung menentukan produk, bahan, atau ukuran.</p><h4 class=\"ql-spasi-rapat\">Ceritakan dulu kebutuhannya.</h4><h4 class=\"ql-spasi-rapat\">Kita cari solusi cetaknya.</h4><p class=\"ql-spasi-rapat\">Orion Grafika</p><p class=\"ql-spasi-rapat\">Fast Print Solution</p>', 'posts/sampul/7WBtMcV6SIa0tPMBFSOI6EbhbXY9gaTYTfcmhBbS.jpg', 1, 'Orion Grafika, Percetakan Sampang, Digital Printing Sampang, Percetakan Madura, Digital Printing Madura, Banner Sampang, Spanduk Sampang, Sticker Sampang', 'terbit', '2026-09-26 03:00:00', 1, 1, 46, 5, '2026-09-26 03:00:12', '2026-09-27 14:38:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_kategori`
--

CREATE TABLE `post_kategori` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(60) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_kategori`
--

INSERT INTO `post_kategori` (`id`, `nama`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Pengumuman', 'pengumuman', '2026-09-26 02:20:43', '2026-09-26 02:20:43'),
(2, 'SOP & Panduan', 'sop-panduan', '2026-09-26 02:20:43', '2026-09-26 02:20:43'),
(3, 'Tips Finishing', 'tips-finishing', '2026-09-26 02:20:43', '2026-09-26 02:20:43'),
(4, 'Hasil Karya', 'hasil-karya', '2026-09-26 02:20:43', '2026-09-26 02:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `nama_produk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `barcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_mesin` bigint(20) UNSIGNED DEFAULT NULL,
  `metode_hitung` enum('ukuran','quantity') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_panjang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_lebar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `spk_lengkap` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `barcode`, `id_mesin`, `metode_hitung`, `min_order`, `min_panjang`, `min_lebar`, `keterangan`, `status`, `created_at`, `updated_at`, `spk_lengkap`) VALUES
(1, 'FLS280 3.2m', NULL, 2, 'ukuran', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-11 13:27:14', '2026-09-11 13:27:14', 0),
(2, 'TC Direct Print 120cm', 'TC-CENT', 4, 'ukuran', 3.60, 120.00, 300.00, '-', 'aktif', '2026-09-11 13:27:25', '2026-09-27 13:57:42', 1),
(3, 'Sublime Paper', 'SUB-PPR', 6, 'ukuran', 0.16, 155.00, 10.00, '-', 'aktif', '2026-09-11 11:23:14', '2026-09-27 13:58:06', 0),
(4, 'AC 260gram', NULL, 3, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-11 13:42:17', '2026-09-11 13:42:17', 0),
(5, 'AC 210gram', NULL, 3, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-11 13:42:10', '2026-09-11 13:42:10', 0),
(7, 'STIKER CAMEL 155', 'CML', 1, 'ukuran', 0.31, 155.00, 20.00, 'MINIMAL CETAK MENGIKUTI ATURAN MINIMAL ORDER', 'aktif', '2026-03-03 10:10:05', '2026-09-12 16:39:49', 0),
(8, 'STIKER EXILO 153', NULL, 1, 'ukuran', 0.31, 153.00, 20.00, '-', 'aktif', '2026-03-06 01:47:06', '2026-09-12 16:39:21', 0),
(9, 'STIKER INFLEX 153', NULL, 1, 'ukuran', 0.31, 155.00, 20.00, '-', 'aktif', '2026-03-06 01:47:50', '2026-09-12 16:38:57', 0),
(18, 'FLS280 2.2m', 'FLS2.2', 2, 'ukuran', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-11 13:43:20', '2026-09-11 13:43:20', 0),
(19, 'FLS280 2.6m', NULL, 2, 'ukuran', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-11 13:43:42', '2026-09-11 13:43:42', 0),
(20, 'FLS280 1.6m', NULL, 2, 'ukuran', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-11 13:43:58', '2026-09-11 13:43:58', 0),
(22, 'DTF Print Pet Film', 'DTF-PF', 5, 'ukuran', 1.00, 100.00, 100.00, '-', 'aktif', '2026-09-12 14:13:16', '2026-09-12 14:13:16', 0),
(23, 'BONTAX', 'BTX', 3, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-12 14:14:09', '2026-09-12 14:14:09', 0),
(26, 'DTF - A3', 'DTFA3', 5, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-12 16:43:41', '2026-09-12 16:43:41', 0),
(27, 'DTF - A4', 'DTFA4', 5, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-12 16:44:04', '2026-09-12 16:44:04', 0),
(28, 'DTF - A5', 'DTFA5', 5, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-12 16:44:26', '2026-09-12 16:44:26', 0),
(29, 'LOGO 10cm', 'LOGO10', 5, 'quantity', 0.00, 0.00, 0.00, '-', 'aktif', '2026-09-12 16:44:53', '2026-09-12 16:44:53', 0),
(107, '10R (20.3 x 25.4)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(108, '10R Laminasi (20.3 x 25.4)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(109, '10Rs (20 x 30)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(110, '10Rs Laminasi (20 x 30)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(111, '11R (28 x 35)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(112, '11R Laminasi (28 x 35)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(113, '12R (30 x 40)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(114, '12R Laminasi (30 x 40)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(115, '12Rs (30 x 45)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(116, '12Rs Laminasi (30 x 45)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(117, '2R (6 x 9)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(118, '2R Laminasi (6 x 9)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(119, '3R (8.9 x 12.7)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(120, '3R Laminasi (8.9 x 12.7)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(121, '4R (10.2 x 17.8)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(122, '4R Laminasi (10.2 x 17.8)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(123, '6R (15 x 20)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(124, '6R Laminasi (15 x 20)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak). Laminasi glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(125, 'AC 210gsm 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. AC 210gram (1 muka) yang sudah ada dipakai sebagai 1 muka.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(126, 'AC 260gsm 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. AC 260gram (1 muka) yang sudah ada dipakai sebagai 1 muka.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(127, 'AP 120gsm 1 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(128, 'AP 120gsm 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(129, 'AP 150gsm 1 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(130, 'AP 150gsm 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(131, 'Box Kartu Nama', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. Dijual sebagai box kosong.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(132, 'Brosur A4 1 sisi', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: AP 120 dibagi 2 (2 brosur A4 per lembar A3+).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(133, 'Brosur A4 2 sisi', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: AP 120 dibagi 2.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(134, 'HVS A3 75gsm 1 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(135, 'HVS A3 75gsm 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(136, 'Kartu Nama 1 sisi', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. Per box (100 kartu). BOM: AC 260 x5 lembar (20 kartu per lembar A3+) + 1 Box Kartu Nama.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(137, 'Kartu Nama 2 sisi', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. Per box (100 kartu). BOM sama dengan 1 sisi (sheet yang sama dicetak bolak-balik).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(138, 'Laminasi Doff 1 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(139, 'Laminasi Doff 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: laminasi x2 (dua sisi).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(140, 'Laminasi Glossy 1 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(141, 'Laminasi Glossy 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: laminasi x2 (dua sisi).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(142, 'Linen A3+ 1 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(143, 'Linen A3+ 2 muka', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(144, 'Paperbag', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: AC 210 + laminasi glossy per paperbag (tali/lem belum dihitung).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(145, 'PVC ID Card', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(146, 'Spiral', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. Per jilid.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(147, 'Stiker Vinil Susu', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(148, 'Stiker Vinil Transparan', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(149, 'Stiker Hologram', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(150, 'Amplop Custom', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan.', 'aktif', '2026-09-24 17:38:34', '2026-09-25 15:09:11', 0),
(151, 'Amplop Linen + Isi Linen', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: linen A3+ dibagi 2 per set.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(152, 'Undangan Custom (A5 Linen)', NULL, 3, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. Contoh awal: A5 di kertas linen A3+ (2 per lembar); undangan lain dengan bahan berbeda dibuat terpisah.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(153, '16R - 40 x 50', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper luas foto +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(154, '16R Laminasi - 40 x 50', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper + laminasi dingin glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(155, '16Rs - 40 x 60', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper luas foto +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(156, '16Rs Laminasi - 40 x 60', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper + laminasi dingin glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(157, '20R - 50 x 60', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper luas foto +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(158, '20R Laminasi - 50 x 60', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper + laminasi dingin glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(159, '20Rs - 50 x 75', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper luas foto +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(160, '20Rs Laminasi - 50 x 75', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper + laminasi dingin glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(161, '24R - 60 x 80', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper luas foto +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(162, '24R Laminasi - 60 x 80', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper + laminasi dingin glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(163, '24Rs - 60 x 90', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper luas foto +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(164, '24Rs Laminasi - 60 x 90', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: Photopaper + laminasi dingin glossy.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(165, 'Albatros 90cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(166, 'Camel 106cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(167, 'Camel 126cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. Bahan: STIKER CAMEL 125cm (dokumen menulis 125, daftar harga 126).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(168, 'Canvas 90cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(169, 'Luster 125cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(170, 'Photopaper 91cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(171, 'Ritrama Transparan 155cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(172, 'Exilo 106', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(173, 'Exilo 125cm', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(174, 'Wallpaper', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(175, 'Transparan Laminasi Putih', NULL, 1, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. Asumsi BOM: stiker transparan + laminasi dingin doff (laminasi putih khusus belum terdaftar).', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(176, 'Roll Up Albatros 60x120', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: stand + Albatros 0,72 m2 +10%. Harga jual = harga beli stand Rp 180.000 (margin negatif) - mohon cek.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(177, 'Roll Up Albatros 80x200', NULL, 1, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: stand + Albatros 1,6 m2 +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(178, 'FLS280 1.1m', NULL, 2, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. FLS 280gr plus desain, lebar 1,1m.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(179, 'Oneway Vision', NULL, 2, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(180, 'FLS Polos', NULL, 2, 'ukuran', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. Tanpa cetak; bahan FLS 3,2m.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(181, 'Ring Mata Ayam', NULL, 2, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. ', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(182, 'XBanner Set', NULL, 2, 'quantity', 0.00, 0.00, 0.00, 'Harga dari dokumen Bahan Baku. BOM: rangka + FLS 1,6m 60x160cm +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0),
(183, 'Tripod Banner Set', NULL, 2, 'quantity', 0.00, 0.00, 0.00, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: rangka tripod + FLS 1,6m 60x160cm +10%.', 'aktif', '2026-09-24 17:38:34', '2026-09-24 17:38:34', 0);

-- --------------------------------------------------------

--
-- Table structure for table `produk_finishing`
--

CREATE TABLE `produk_finishing` (
  `id` int(11) NOT NULL,
  `id_produk` bigint(20) UNSIGNED DEFAULT NULL,
  `id_finishing` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `id_supplier` bigint(20) UNSIGNED NOT NULL,
  `id_purchase_request` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `tgl_pengiriman_estimasi` date DEFAULT NULL,
  `termin_pembayaran` varchar(100) DEFAULT NULL,
  `status` enum('Draft','Dipesan','Diterima Sebagian','Selesai','Dibatalkan') NOT NULL DEFAULT 'Draft',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pajak_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
  `ongkir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `id_user_pembuat` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_details`
--

CREATE TABLE `purchase_order_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_purchase_order` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `qty_diterima` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Draft','Diajukan','Disetujui','Ditolak','Diproses PO') NOT NULL DEFAULT 'Draft',
  `catatan` text DEFAULT NULL,
  `id_user_pembuat` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_details`
--

CREATE TABLE `purchase_request_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_purchase_request` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qc_inspeksi`
--

CREATE TABLE `qc_inspeksi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `qty_diperiksa` int(10) UNSIGNED NOT NULL,
  `qty_lolos` int(10) UNSIGNED NOT NULL,
  `qty_reject` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist`)),
  `hasil` enum('Lulus','Rework') NOT NULL DEFAULT 'Lulus',
  `catatan` text DEFAULT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `berlaku_sampai` date NOT NULL,
  `id_konsumen` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_pelanggan` varchar(150) NOT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `id_golongan` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Draft','Terkirim','Disetujui','Ditolak','Menjadi Order') NOT NULL DEFAULT 'Draft',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_user_pembuat` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_quotation` bigint(20) UNSIGNED NOT NULL,
  `tipe` enum('Produk','Jasa') NOT NULL,
  `id_produk` bigint(20) UNSIGNED DEFAULT NULL,
  `id_jasa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_finishing` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_item` varchar(150) NOT NULL,
  `p_ukuran` decimal(10,2) NOT NULL DEFAULT 0.00,
  `l_ukuran` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(15,2) NOT NULL,
  `qty_hitung` decimal(15,4) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `biaya_finishing` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_desain` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rework`
--

CREATE TABLE `rework` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `id_qc_inspeksi` bigint(20) UNSIGNED NOT NULL,
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `qty_rework` int(10) UNSIGNED NOT NULL,
  `status` enum('Dibuka','Selesai') NOT NULL DEFAULT 'Dibuka',
  `biaya_material` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `selesai_at` timestamp NULL DEFAULT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` bigint(20) UNSIGNED NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `boleh_lihat_harga` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `nama_role`, `boleh_lihat_harga`) VALUES
(1, 'Administrator', 1),
(2, 'Operator Desain', 0),
(3, 'Kasir', 1),
(4, 'Operator Produksi', 0),
(5, 'Gudang', 1),
(6, 'Owner', 1);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id_role` bigint(20) UNSIGNED NOT NULL,
  `id_menu` bigint(20) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `can_add` tinyint(1) NOT NULL DEFAULT 1,
  `can_edit` tinyint(1) NOT NULL DEFAULT 1,
  `can_delete` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id_role`, `id_menu`, `can_view`, `can_add`, `can_edit`, `can_delete`) VALUES
(1, 1, 1, 1, 1, 1),
(1, 3, 1, 1, 1, 1),
(1, 4, 1, 1, 1, 1),
(1, 5, 1, 1, 1, 1),
(1, 6, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1),
(1, 8, 1, 1, 1, 1),
(1, 9, 1, 1, 1, 1),
(1, 10, 1, 1, 1, 1),
(1, 11, 1, 1, 1, 1),
(1, 12, 1, 1, 1, 1),
(1, 13, 1, 1, 1, 1),
(1, 14, 1, 1, 1, 1),
(1, 15, 1, 1, 1, 1),
(1, 16, 1, 1, 1, 1),
(1, 17, 1, 1, 1, 1),
(1, 18, 1, 1, 1, 1),
(1, 20, 1, 1, 1, 1),
(1, 21, 1, 1, 1, 1),
(1, 22, 1, 1, 1, 1),
(1, 23, 1, 1, 1, 1),
(1, 24, 1, 1, 1, 1),
(1, 25, 1, 1, 1, 1),
(1, 26, 1, 1, 1, 1),
(1, 27, 1, 1, 1, 1),
(1, 28, 1, 1, 1, 1),
(1, 29, 1, 1, 1, 1),
(1, 30, 1, 1, 1, 1),
(1, 31, 1, 1, 1, 1),
(1, 32, 1, 1, 1, 1),
(1, 33, 1, 1, 1, 1),
(1, 34, 1, 1, 1, 1),
(1, 35, 1, 1, 1, 1),
(1, 36, 1, 1, 1, 1),
(1, 39, 1, 1, 1, 1),
(1, 40, 1, 1, 1, 1),
(1, 44, 1, 1, 1, 1),
(1, 45, 1, 1, 1, 1),
(1, 46, 1, 1, 1, 1),
(1, 47, 1, 1, 1, 1),
(1, 48, 1, 1, 1, 1),
(1, 49, 1, 1, 1, 1),
(1, 50, 1, 1, 1, 1),
(1, 51, 1, 1, 1, 1),
(1, 52, 1, 1, 1, 1),
(1, 53, 1, 1, 1, 1),
(1, 54, 1, 1, 1, 1),
(1, 55, 1, 1, 1, 1),
(1, 56, 1, 1, 1, 1),
(1, 57, 1, 1, 1, 1),
(1, 58, 1, 1, 1, 1),
(1, 61, 1, 1, 1, 1),
(1, 62, 1, 1, 1, 1),
(1, 65, 1, 1, 1, 1),
(1, 67, 1, 1, 1, 1),
(2, 7, 1, 1, 1, 1),
(2, 12, 1, 1, 1, 1),
(2, 23, 1, 0, 0, 0),
(2, 24, 1, 0, 0, 0),
(2, 26, 1, 1, 1, 1),
(2, 41, 1, 1, 1, 1),
(2, 42, 1, 1, 1, 1),
(2, 56, 1, 0, 0, 0),
(2, 57, 1, 0, 0, 0),
(2, 58, 1, 1, 1, 1),
(2, 61, 1, 0, 0, 0),
(2, 64, 1, 1, 1, 1),
(2, 65, 1, 0, 0, 0),
(2, 67, 1, 0, 0, 0),
(3, 3, 1, 1, 1, 1),
(3, 7, 1, 1, 1, 1),
(3, 11, 1, 1, 1, 1),
(3, 12, 1, 1, 1, 1),
(3, 16, 1, 1, 1, 1),
(3, 24, 1, 1, 1, 1),
(3, 29, 1, 1, 1, 1),
(3, 34, 1, 1, 1, 1),
(3, 36, 1, 1, 1, 1),
(3, 38, 1, 1, 1, 1),
(3, 48, 1, 1, 1, 1),
(3, 58, 1, 1, 1, 1),
(3, 61, 1, 1, 1, 1),
(3, 67, 1, 0, 0, 0),
(4, 21, 1, 1, 1, 1),
(4, 22, 1, 1, 1, 1),
(4, 23, 1, 1, 1, 1),
(4, 56, 1, 1, 1, 1),
(4, 57, 1, 1, 1, 1),
(4, 67, 1, 0, 0, 0),
(5, 1, 1, 1, 1, 1),
(5, 17, 1, 1, 1, 1),
(5, 33, 1, 1, 1, 1),
(5, 46, 1, 1, 1, 1),
(5, 53, 1, 1, 1, 1),
(5, 57, 1, 1, 1, 1),
(5, 67, 1, 0, 0, 0),
(6, 20, 1, 1, 1, 1),
(6, 25, 1, 1, 1, 1),
(6, 26, 1, 1, 1, 1),
(6, 29, 1, 1, 1, 1),
(6, 36, 1, 1, 1, 1),
(6, 39, 1, 1, 1, 1),
(6, 40, 1, 1, 1, 1),
(6, 43, 1, 1, 1, 1),
(6, 44, 1, 1, 1, 1),
(6, 46, 1, 1, 1, 1),
(6, 47, 1, 1, 1, 1),
(6, 53, 1, 1, 1, 1),
(6, 54, 1, 1, 1, 1),
(6, 55, 1, 1, 1, 1),
(6, 56, 1, 1, 1, 1),
(6, 57, 1, 1, 1, 1),
(6, 60, 1, 1, 1, 1),
(6, 65, 1, 1, 1, 1),
(6, 67, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `satuan`
--

CREATE TABLE `satuan` (
  `id_satuan` bigint(20) UNSIGNED NOT NULL,
  `nama_satuan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nilai_konversi` decimal(10,2) NOT NULL,
  `tipe_satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Barang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `satuan`
--

INSERT INTO `satuan` (`id_satuan`, `nama_satuan`, `nilai_konversi`, `tipe_satuan`) VALUES
(7, 'kg', 1.00, 'Barang'),
(8, 'gram', 1.00, 'Barang'),
(9, 'liter', 1.00, 'Barang'),
(10, 'ml', 1.00, 'Barang'),
(11, 'pcs', 1.00, 'Barang'),
(12, 'box', 1.00, 'Barang'),
(13, 'cm', 1.00, 'Panjang'),
(14, 'm', 1.00, 'Panjang'),
(17, 'roll', 1.00, 'Barang'),
(18, 'lembar', 1.00, 'Barang'),
(20, 'rim', 1.00, 'Barang'),
(21, 'm2', 1.00, 'Luas'),
(22, 'cm2', 1.00, 'Luas'),
(26, 'botol', 1.00, 'Barang'),
(27, 'pack', 1.00, 'Barang'),
(28, 'dus', 1.00, 'Barang');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('dhPRuzrthnweQIRRjQF9I3eeUUM1W0ySMdphSKYQ', 24, '192.168.1.21', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV3A3Z2J1SU5jdXlwMVJoTllJRWt2WVREZWJjVEJHcElFZmtYNG1SdyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjM6Imh0dHA6Ly8xOTIuMTY4LjEuNS9wZXJjZXRha2FuLWFwcC9wdWJsaWMva2FzaXIvY2hlY2stcGVtYmF5YXJhbiI7czo1OiJyb3V0ZSI7czo2OiJrYXNpci4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyNDt9', 1790513545),
('g1aSLJxKHNC4sXRAKkhVcBiVPagzSohp8NE1daEl', 25, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSW1jVUVvTXpSaUJzWG1KUjlRZVJ0ZmUyWllFUlBYM3pxTlYyYlhCQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3QvcGVyY2V0YWthbi1hcHAvcHVibGljL3NpbnlhbCI7czo1OiJyb3V0ZSI7czo2OiJzaW55YWwiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyNTt9', 1790520554),
('SnhPrtevfVcdAmbSWgwH9UOGgi8RRbXgNMtwjqlU', 24, '192.168.1.9', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWXU4TXZXbUxwV2IyTVFnSm9ZSVRKWllGVUt1TzZISmFJd2ROdHhrcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xOTIuMTY4LjEuMTcvcGVyY2V0YWthbi1hcHAvcHVibGljL2thc2lyL2NoZWNrLXBlbWJheWFyYW4iO3M6NToicm91dGUiO3M6Njoia2FzaXIuIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjQ7fQ==', 1790520096),
('uCCmnl5qNrh0WCD2vv8fGLcOkgcGgumV784c4iiw', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTHZwdHkwc1FvSTdrYW1sa1daSFJQVmgza1ZUUTlZdVVXVkJwR090OSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly9sb2NhbGhvc3QvcGVyY2V0YWthbi1hcHAvcHVibGljL2thc2lyL2NoZWNrLXBlbWJheWFyYW4iO3M6NToicm91dGUiO3M6Njoia2FzaXIuIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1790520551);

-- --------------------------------------------------------

--
-- Table structure for table `setting_harga`
--

CREATE TABLE `setting_harga` (
  `id_harga` bigint(20) UNSIGNED NOT NULL,
  `id_produk` bigint(20) UNSIGNED NOT NULL,
  `id_golongan` bigint(20) UNSIGNED NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) DEFAULT NULL,
  `harga_jual` decimal(15,2) NOT NULL,
  `id_satuan` bigint(20) UNSIGNED DEFAULT NULL,
  `qty_berlaku` int(11) DEFAULT 1,
  `id_satuan_berlaku` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `setting_harga`
--

INSERT INTO `setting_harga` (`id_harga`, `id_produk`, `id_golongan`, `range_min`, `range_max`, `harga_jual`, `id_satuan`, `qty_berlaku`, `id_satuan_berlaku`, `created_at`, `updated_at`) VALUES
(30, 3, 1, 1.00, 9999999.00, 30000.00, NULL, 1, NULL, '2026-03-07 20:10:14', '2026-09-11 22:51:46'),
(31, 3, 2, 1.00, 9999999.00, 30000.00, NULL, 1, NULL, '2026-03-07 20:10:14', '2026-09-11 22:51:46'),
(32, 3, 3, 1.00, 9999999.00, 30000.00, NULL, 1, NULL, '2026-03-07 20:10:14', '2026-09-11 22:51:46'),
(88, 1, 1, 1.00, 999999.00, 20000.00, 21, 1, 21, '2026-03-24 13:51:25', '2026-09-27 14:19:08'),
(126, 4, 1, 1.00, 10.00, 5000.00, 18, 1, 18, '2026-03-26 14:49:03', '2026-09-11 22:51:46'),
(127, 4, 1, 11.00, 50.00, 4500.00, 18, 1, 18, '2026-03-26 14:49:03', '2026-09-11 22:51:46'),
(143, 5, 1, 10.00, 49.00, 4850.00, NULL, 1, NULL, '2026-09-10 00:18:02', '2026-09-11 22:51:46'),
(144, 5, 1, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-10 00:18:02', '2026-09-11 22:51:46'),
(145, 5, 1, 150.00, 500.00, 4000.00, NULL, 1, NULL, '2026-09-10 00:18:02', '2026-09-11 22:51:46'),
(173, 18, 1, 0.01, 154.00, 20000.00, NULL, 1, NULL, '2026-09-11 14:19:40', '2026-09-11 22:51:46'),
(174, 18, 1, 155.00, 308.00, 16000.00, NULL, 1, NULL, '2026-09-11 14:19:56', '2026-09-12 17:03:40'),
(177, 19, 1, 0.01, 182.00, 20000.00, NULL, 1, NULL, '2026-09-11 14:20:38', '2026-09-11 22:51:46'),
(178, 19, 1, 183.00, 366.00, 15000.00, NULL, 1, NULL, '2026-09-11 14:20:53', '2026-09-11 22:51:46'),
(183, 7, 1, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-11 14:25:39', '2026-09-11 22:51:46'),
(184, 8, 1, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-11 14:25:53', '2026-09-11 22:51:46'),
(185, 9, 1, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-11 14:26:04', '2026-09-11 22:51:46'),
(191, 23, 2, 1.00, 10.00, 10000.00, NULL, 1, NULL, '2026-09-12 17:04:52', '2026-09-12 17:04:52'),
(192, 22, 1, 1.00, 60.00, 45000.00, NULL, 1, NULL, '2026-09-16 11:41:39', '2026-09-16 11:41:39'),
(193, 22, 2, 1.00, 60.00, 45000.00, NULL, 1, NULL, '2026-09-16 11:41:48', '2026-09-16 11:41:48'),
(210, 5, 2, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(211, 5, 2, 10.00, 49.00, 4850.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(212, 5, 2, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(213, 5, 2, 150.00, 500.00, 4000.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(214, 5, 3, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(215, 5, 3, 10.00, 49.00, 4850.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(216, 5, 3, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(217, 5, 3, 150.00, 500.00, 4000.00, NULL, 1, NULL, '2026-09-09 17:18:02', '2026-09-18 06:49:21'),
(218, 4, 2, 1.00, 10.00, 5000.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(219, 4, 2, 11.00, 50.00, 4500.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(220, 4, 3, 1.00, 10.00, 5000.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(221, 4, 3, 11.00, 50.00, 4500.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(222, 4, 4, 1.00, 10.00, 5000.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(223, 4, 4, 11.00, 50.00, 4500.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(224, 4, 6, 1.00, 10.00, 5000.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(225, 4, 6, 11.00, 50.00, 4500.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(226, 4, 7, 1.00, 10.00, 5000.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(227, 4, 7, 11.00, 50.00, 4500.00, NULL, 1, NULL, '2026-09-21 01:28:08', '2026-09-21 01:28:08'),
(1110, 107, 1, 1.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1111, 107, 2, 1.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1112, 107, 3, 1.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1113, 107, 4, 1.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1114, 107, 6, 1.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1115, 107, 7, 1.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1116, 108, 1, 1.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1117, 108, 2, 1.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1118, 108, 3, 1.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1119, 108, 4, 1.00, NULL, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1120, 108, 6, 1.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1121, 108, 7, 1.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1122, 109, 1, 1.00, NULL, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1123, 109, 2, 1.00, NULL, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1124, 109, 3, 1.00, NULL, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1125, 109, 4, 1.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1126, 109, 6, 1.00, NULL, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1127, 109, 7, 1.00, NULL, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1128, 110, 1, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1129, 110, 2, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1130, 110, 3, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1131, 110, 4, 1.00, NULL, 9200.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1132, 110, 6, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1133, 110, 7, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1134, 111, 1, 1.00, NULL, 12500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1135, 111, 2, 1.00, NULL, 12500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1136, 111, 3, 1.00, NULL, 12500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1137, 111, 4, 1.00, NULL, 12500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1138, 111, 6, 1.00, NULL, 12500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1139, 111, 7, 1.00, NULL, 12500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1140, 112, 1, 1.00, NULL, 19400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1141, 112, 2, 1.00, NULL, 19400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1142, 112, 3, 1.00, NULL, 19400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1143, 112, 4, 1.00, NULL, 19400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1144, 112, 6, 1.00, NULL, 19400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1145, 112, 7, 1.00, NULL, 19400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1146, 113, 1, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1147, 113, 2, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1148, 113, 3, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1149, 113, 4, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1150, 113, 6, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1151, 113, 7, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1152, 114, 1, 1.00, NULL, 23400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1153, 114, 2, 1.00, NULL, 23400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1154, 114, 3, 1.00, NULL, 23400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1155, 114, 4, 1.00, NULL, 23400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1156, 114, 6, 1.00, NULL, 23400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1157, 114, 7, 1.00, NULL, 23400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1158, 115, 1, 1.00, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1159, 115, 2, 1.00, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1160, 115, 3, 1.00, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1161, 115, 4, 1.00, NULL, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1162, 115, 6, 1.00, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1163, 115, 7, 1.00, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1164, 116, 1, 1.00, NULL, 24000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1165, 116, 2, 1.00, NULL, 24000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1166, 116, 3, 1.00, NULL, 24000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1167, 116, 4, 1.00, NULL, 19000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1168, 116, 6, 1.00, NULL, 24000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1169, 116, 7, 1.00, NULL, 24000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1170, 117, 1, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1171, 117, 2, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1172, 117, 3, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1173, 117, 4, 1.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1174, 117, 6, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1175, 117, 7, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1176, 118, 1, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1177, 118, 2, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1178, 118, 3, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1179, 118, 4, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1180, 118, 6, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1181, 118, 7, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1182, 119, 1, 1.00, NULL, 1600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1183, 119, 2, 1.00, NULL, 1600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1184, 119, 3, 1.00, NULL, 1600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1185, 119, 4, 1.00, NULL, 1200.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1186, 119, 6, 1.00, NULL, 1600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1187, 119, 7, 1.00, NULL, 1600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1188, 120, 1, 1.00, NULL, 1800.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1189, 120, 2, 1.00, NULL, 1800.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1190, 120, 3, 1.00, NULL, 1800.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1191, 120, 4, 1.00, NULL, 1800.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1192, 120, 6, 1.00, NULL, 1800.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1193, 120, 7, 1.00, NULL, 1800.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1194, 121, 1, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1195, 121, 2, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1196, 121, 3, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1197, 121, 4, 1.00, NULL, 1300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1198, 121, 6, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1199, 121, 7, 1.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1200, 122, 1, 1.00, NULL, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1201, 122, 2, 1.00, NULL, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1202, 122, 3, 1.00, NULL, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1203, 122, 4, 1.00, NULL, 2400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1204, 122, 6, 1.00, NULL, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1205, 122, 7, 1.00, NULL, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1206, 123, 1, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1207, 123, 2, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1208, 123, 3, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1209, 123, 4, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1210, 123, 6, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1211, 123, 7, 1.00, NULL, 2300.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1212, 124, 1, 1.00, NULL, 4400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1213, 124, 2, 1.00, NULL, 4400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1214, 124, 3, 1.00, NULL, 4400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1215, 124, 4, 1.00, NULL, 4400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1216, 124, 6, 1.00, NULL, 4400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1217, 124, 7, 1.00, NULL, 4400.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1218, 125, 1, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1219, 125, 1, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1220, 125, 1, 50.00, 149.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1221, 125, 1, 150.00, NULL, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1222, 125, 2, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1223, 125, 2, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1224, 125, 2, 50.00, 149.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1225, 125, 2, 150.00, NULL, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1226, 125, 3, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1227, 125, 3, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1228, 125, 3, 50.00, 149.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1229, 125, 3, 150.00, NULL, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1230, 125, 4, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1231, 125, 4, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1232, 125, 4, 50.00, 149.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1233, 125, 4, 150.00, NULL, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1234, 125, 6, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1235, 125, 6, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1236, 125, 6, 50.00, 149.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1237, 125, 6, 150.00, NULL, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1238, 125, 7, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1239, 125, 7, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1240, 125, 7, 50.00, 149.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1241, 125, 7, 150.00, NULL, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1242, 126, 1, 1.00, 10.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1243, 126, 1, 11.00, 50.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1244, 126, 1, 51.00, 149.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1245, 126, 1, 150.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1246, 126, 2, 1.00, 10.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1247, 126, 2, 11.00, 50.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1248, 126, 2, 51.00, 149.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1249, 126, 2, 150.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1250, 126, 3, 1.00, 10.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1251, 126, 3, 11.00, 50.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1252, 126, 3, 51.00, 149.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1253, 126, 3, 150.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1254, 126, 4, 1.00, 10.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1255, 126, 4, 11.00, 50.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1256, 126, 4, 51.00, 149.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1257, 126, 4, 150.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1258, 126, 6, 1.00, 10.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1259, 126, 6, 11.00, 50.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1260, 126, 6, 51.00, 149.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1261, 126, 6, 150.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1262, 126, 7, 1.00, 10.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1263, 126, 7, 11.00, 50.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1264, 126, 7, 51.00, 149.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1265, 126, 7, 150.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1266, 127, 1, 1.00, 9.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1267, 127, 1, 10.00, 49.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1268, 127, 1, 50.00, 149.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1269, 127, 1, 150.00, NULL, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1270, 127, 2, 1.00, 9.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1271, 127, 2, 10.00, 49.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1272, 127, 2, 50.00, 149.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1273, 127, 2, 150.00, NULL, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1274, 127, 3, 1.00, 9.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1275, 127, 3, 10.00, 49.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1276, 127, 3, 50.00, 149.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1277, 127, 3, 150.00, NULL, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1278, 127, 4, 1.00, 9.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1279, 127, 4, 10.00, 49.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1280, 127, 4, 50.00, 149.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1281, 127, 4, 150.00, NULL, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1282, 127, 6, 1.00, 9.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1283, 127, 6, 10.00, 49.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1284, 127, 6, 50.00, 149.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1285, 127, 6, 150.00, NULL, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1286, 127, 7, 1.00, 9.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1287, 127, 7, 10.00, 49.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1288, 127, 7, 50.00, 149.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1289, 127, 7, 150.00, NULL, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1290, 128, 1, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1291, 128, 1, 10.00, 49.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1292, 128, 1, 50.00, 149.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1293, 128, 1, 150.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1294, 128, 2, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1295, 128, 2, 10.00, 49.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1296, 128, 2, 50.00, 149.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1297, 128, 2, 150.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1298, 128, 3, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1299, 128, 3, 10.00, 49.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1300, 128, 3, 50.00, 149.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1301, 128, 3, 150.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1302, 128, 4, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1303, 128, 4, 10.00, 49.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1304, 128, 4, 50.00, 149.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1305, 128, 4, 150.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1306, 128, 6, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1307, 128, 6, 10.00, 49.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1308, 128, 6, 50.00, 149.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1309, 128, 6, 150.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1310, 128, 7, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1311, 128, 7, 10.00, 49.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1312, 128, 7, 50.00, 149.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1313, 128, 7, 150.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1314, 129, 1, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1315, 129, 1, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1316, 129, 1, 50.00, 149.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1317, 129, 1, 150.00, NULL, 3250.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1318, 129, 2, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1319, 129, 2, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1320, 129, 2, 50.00, 149.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1321, 129, 2, 150.00, NULL, 3250.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1322, 129, 3, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1323, 129, 3, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1324, 129, 3, 50.00, 149.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1325, 129, 3, 150.00, NULL, 3250.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1326, 129, 4, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1327, 129, 4, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1328, 129, 4, 50.00, 149.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1329, 129, 4, 150.00, NULL, 3250.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1330, 129, 6, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1331, 129, 6, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1332, 129, 6, 50.00, 149.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1333, 129, 6, 150.00, NULL, 3250.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1334, 129, 7, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1335, 129, 7, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1336, 129, 7, 50.00, 149.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1337, 129, 7, 150.00, NULL, 3250.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1338, 130, 1, 1.00, 9.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1339, 130, 1, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1340, 130, 1, 50.00, 149.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1341, 130, 1, 150.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1342, 130, 2, 1.00, 9.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1343, 130, 2, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1344, 130, 2, 50.00, 149.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1345, 130, 2, 150.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1346, 130, 3, 1.00, 9.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1347, 130, 3, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1348, 130, 3, 50.00, 149.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1349, 130, 3, 150.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1350, 130, 4, 1.00, 9.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1351, 130, 4, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1352, 130, 4, 50.00, 149.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1353, 130, 4, 150.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1354, 130, 6, 1.00, 9.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1355, 130, 6, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1356, 130, 6, 50.00, 149.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1357, 130, 6, 150.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1358, 130, 7, 1.00, 9.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1359, 130, 7, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1360, 130, 7, 50.00, 149.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1361, 130, 7, 150.00, NULL, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1362, 131, 1, 1.00, 24.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1363, 131, 1, 25.00, 99.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1364, 131, 1, 100.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1365, 131, 2, 1.00, 24.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1366, 131, 2, 25.00, 99.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1367, 131, 2, 100.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1368, 131, 3, 1.00, 24.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1369, 131, 3, 25.00, 99.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1370, 131, 3, 100.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1371, 131, 4, 1.00, 24.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1372, 131, 4, 25.00, 99.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1373, 131, 4, 100.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1374, 131, 6, 1.00, 24.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1375, 131, 6, 25.00, 99.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1376, 131, 6, 100.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1377, 131, 7, 1.00, 24.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1378, 131, 7, 25.00, 99.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1379, 131, 7, 100.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1380, 132, 1, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1381, 132, 1, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1382, 132, 1, 50.00, 99.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1383, 132, 1, 100.00, 499.00, 1750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1384, 132, 1, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1385, 132, 2, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1386, 132, 2, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1387, 132, 2, 50.00, 99.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1388, 132, 2, 100.00, 499.00, 1750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1389, 132, 2, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1390, 132, 3, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1391, 132, 3, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1392, 132, 3, 50.00, 99.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1393, 132, 3, 100.00, 499.00, 1750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1394, 132, 3, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1395, 132, 4, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1396, 132, 4, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1397, 132, 4, 50.00, 99.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1398, 132, 4, 100.00, 499.00, 1750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1399, 132, 4, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1400, 132, 6, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1401, 132, 6, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1402, 132, 6, 50.00, 99.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1403, 132, 6, 100.00, 499.00, 1750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1404, 132, 6, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1405, 132, 7, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1406, 132, 7, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1407, 132, 7, 50.00, 99.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1408, 132, 7, 100.00, 499.00, 1750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1409, 132, 7, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1410, 133, 1, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1411, 133, 1, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1412, 133, 1, 50.00, 99.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1413, 133, 1, 100.00, 499.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1414, 133, 1, 500.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1415, 133, 2, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1416, 133, 2, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1417, 133, 2, 50.00, 99.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1418, 133, 2, 100.00, 499.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1419, 133, 2, 500.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1420, 133, 3, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1421, 133, 3, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1422, 133, 3, 50.00, 99.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1423, 133, 3, 100.00, 499.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1424, 133, 3, 500.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1425, 133, 4, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1426, 133, 4, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1427, 133, 4, 50.00, 99.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1428, 133, 4, 100.00, 499.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1429, 133, 4, 500.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1430, 133, 6, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1431, 133, 6, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1432, 133, 6, 50.00, 99.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1433, 133, 6, 100.00, 499.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1434, 133, 6, 500.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1435, 133, 7, 1.00, 9.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1436, 133, 7, 10.00, 49.00, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1437, 133, 7, 50.00, 99.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1438, 133, 7, 100.00, 499.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1439, 133, 7, 500.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1440, 134, 1, 1.00, 9.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1441, 134, 1, 10.00, 49.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1442, 134, 1, 50.00, 149.00, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1443, 134, 1, 150.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1444, 134, 2, 1.00, 9.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1445, 134, 2, 10.00, 49.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1446, 134, 2, 50.00, 149.00, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1447, 134, 2, 150.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1448, 134, 3, 1.00, 9.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1449, 134, 3, 10.00, 49.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1450, 134, 3, 50.00, 149.00, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1451, 134, 3, 150.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1452, 134, 4, 1.00, 9.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1453, 134, 4, 10.00, 49.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1454, 134, 4, 50.00, 149.00, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1455, 134, 4, 150.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1456, 134, 6, 1.00, 9.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1457, 134, 6, 10.00, 49.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1458, 134, 6, 50.00, 149.00, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1459, 134, 6, 150.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1460, 134, 7, 1.00, 9.00, 3500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1461, 134, 7, 10.00, 49.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1462, 134, 7, 50.00, 149.00, 2750.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1463, 134, 7, 150.00, NULL, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1464, 135, 1, 1.00, 9.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1465, 135, 1, 10.00, 49.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1466, 135, 1, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1467, 135, 1, 150.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1468, 135, 2, 1.00, 9.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1469, 135, 2, 10.00, 49.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1470, 135, 2, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1471, 135, 2, 150.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1472, 135, 3, 1.00, 9.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1473, 135, 3, 10.00, 49.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1474, 135, 3, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1475, 135, 3, 150.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1476, 135, 4, 1.00, 9.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1477, 135, 4, 10.00, 49.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1478, 135, 4, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1479, 135, 4, 150.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1480, 135, 6, 1.00, 9.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1481, 135, 6, 10.00, 49.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1482, 135, 6, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1483, 135, 6, 150.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1484, 135, 7, 1.00, 9.00, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1485, 135, 7, 10.00, 49.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1486, 135, 7, 50.00, 149.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1487, 135, 7, 150.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1488, 136, 1, 1.00, 4.00, 25000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1489, 136, 1, 5.00, 9.00, 23000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1490, 136, 1, 10.00, NULL, 21000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1491, 136, 2, 1.00, 4.00, 25000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1492, 136, 2, 5.00, 9.00, 23000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1493, 136, 2, 10.00, NULL, 21000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1494, 136, 3, 1.00, 4.00, 25000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1495, 136, 3, 5.00, 9.00, 23000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1496, 136, 3, 10.00, NULL, 21000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1497, 136, 4, 1.00, 4.00, 25000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1498, 136, 4, 5.00, 9.00, 23000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1499, 136, 4, 10.00, NULL, 21000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1500, 136, 6, 1.00, 4.00, 25000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1501, 136, 6, 5.00, 9.00, 23000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1502, 136, 6, 10.00, NULL, 21000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1503, 136, 7, 1.00, 4.00, 25000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1504, 136, 7, 5.00, 9.00, 23000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1505, 136, 7, 10.00, NULL, 21000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1506, 137, 1, 1.00, 4.00, 35000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1507, 137, 1, 5.00, 9.00, 32000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1508, 137, 1, 10.00, NULL, 29000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1509, 137, 2, 1.00, 4.00, 35000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1510, 137, 2, 5.00, 9.00, 32000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1511, 137, 2, 10.00, NULL, 29000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1512, 137, 3, 1.00, 4.00, 35000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1513, 137, 3, 5.00, 9.00, 32000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1514, 137, 3, 10.00, NULL, 29000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1515, 137, 4, 1.00, 4.00, 35000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1516, 137, 4, 5.00, 9.00, 32000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1517, 137, 4, 10.00, NULL, 29000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1518, 137, 6, 1.00, 4.00, 35000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1519, 137, 6, 5.00, 9.00, 32000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1520, 137, 6, 10.00, NULL, 29000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1521, 137, 7, 1.00, 4.00, 35000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1522, 137, 7, 5.00, 9.00, 32000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1523, 137, 7, 10.00, NULL, 29000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1524, 138, 1, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1525, 138, 1, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1526, 138, 1, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1527, 138, 2, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1528, 138, 2, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1529, 138, 2, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1530, 138, 3, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1531, 138, 3, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1532, 138, 3, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1533, 138, 4, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1534, 138, 4, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1535, 138, 4, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1536, 138, 6, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1537, 138, 6, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1538, 138, 6, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1539, 138, 7, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1540, 138, 7, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1541, 138, 7, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1542, 139, 1, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1543, 139, 1, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1544, 139, 1, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1545, 139, 2, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1546, 139, 2, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1547, 139, 2, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1548, 139, 3, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1549, 139, 3, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1550, 139, 3, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1551, 139, 4, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1552, 139, 4, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1553, 139, 4, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1554, 139, 6, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1555, 139, 6, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1556, 139, 6, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1557, 139, 7, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1558, 139, 7, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1559, 139, 7, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1560, 140, 1, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1561, 140, 1, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1562, 140, 1, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1563, 140, 2, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1564, 140, 2, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1565, 140, 2, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1566, 140, 3, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1567, 140, 3, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1568, 140, 3, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1569, 140, 4, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1570, 140, 4, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1571, 140, 4, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1572, 140, 6, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1573, 140, 6, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1574, 140, 6, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1575, 140, 7, 1.00, 9.00, 3000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1576, 140, 7, 10.00, 49.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1577, 140, 7, 50.00, NULL, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1578, 141, 1, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1579, 141, 1, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1580, 141, 1, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1581, 141, 2, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1582, 141, 2, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1583, 141, 2, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1584, 141, 3, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34');
INSERT INTO `setting_harga` (`id_harga`, `id_produk`, `id_golongan`, `range_min`, `range_max`, `harga_jual`, `id_satuan`, `qty_berlaku`, `id_satuan_berlaku`, `created_at`, `updated_at`) VALUES
(1585, 141, 3, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1586, 141, 3, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1587, 141, 4, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1588, 141, 4, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1589, 141, 4, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1590, 141, 6, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1591, 141, 6, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1592, 141, 6, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1593, 141, 7, 1.00, 9.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1594, 141, 7, 10.00, 49.00, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1595, 141, 7, 50.00, NULL, 4000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1596, 142, 1, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1597, 142, 1, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1598, 142, 1, 50.00, NULL, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1599, 142, 2, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1600, 142, 2, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1601, 142, 2, 50.00, NULL, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1602, 142, 3, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1603, 142, 3, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1604, 142, 3, 50.00, NULL, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1605, 142, 4, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1606, 142, 4, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1607, 142, 4, 50.00, NULL, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1608, 142, 6, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1609, 142, 6, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1610, 142, 6, 50.00, NULL, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1611, 142, 7, 1.00, 9.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1612, 142, 7, 10.00, 49.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1613, 142, 7, 50.00, NULL, 5500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1614, 143, 1, 1.00, 9.00, 9500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1615, 143, 1, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1616, 143, 1, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1617, 143, 2, 1.00, 9.00, 9500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1618, 143, 2, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1619, 143, 2, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1620, 143, 3, 1.00, 9.00, 9500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1621, 143, 3, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1622, 143, 3, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1623, 143, 4, 1.00, 9.00, 9500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1624, 143, 4, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1625, 143, 4, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1626, 143, 6, 1.00, 9.00, 9500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1627, 143, 6, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1628, 143, 6, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1629, 143, 7, 1.00, 9.00, 9500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1630, 143, 7, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1631, 143, 7, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1632, 144, 1, 1.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1633, 144, 1, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1634, 144, 1, 100.00, 499.00, 13500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1635, 144, 1, 500.00, NULL, 12000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1636, 144, 2, 1.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1637, 144, 2, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1638, 144, 2, 100.00, 499.00, 13500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1639, 144, 2, 500.00, NULL, 12000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1640, 144, 3, 1.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1641, 144, 3, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1642, 144, 3, 100.00, 499.00, 13500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1643, 144, 3, 500.00, NULL, 12000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1644, 144, 4, 1.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1645, 144, 4, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1646, 144, 4, 100.00, 499.00, 13500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1647, 144, 4, 500.00, NULL, 12000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1648, 144, 6, 1.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1649, 144, 6, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1650, 144, 6, 100.00, 499.00, 13500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1651, 144, 6, 500.00, NULL, 12000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1652, 144, 7, 1.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1653, 144, 7, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1654, 144, 7, 100.00, 499.00, 13500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1655, 144, 7, 500.00, NULL, 12000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1680, 146, 1, 1.00, 9.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1681, 146, 1, 10.00, 49.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1682, 146, 1, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1683, 146, 2, 1.00, 9.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1684, 146, 2, 10.00, 49.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1685, 146, 2, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1686, 146, 3, 1.00, 9.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1687, 146, 3, 10.00, 49.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1688, 146, 3, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1689, 146, 4, 1.00, 9.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1690, 146, 4, 10.00, 49.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1691, 146, 4, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1692, 146, 6, 1.00, 9.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1693, 146, 6, 10.00, 49.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1694, 146, 6, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1695, 146, 7, 1.00, 9.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1696, 146, 7, 10.00, 49.00, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1697, 146, 7, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1698, 147, 1, 1.00, 9.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1699, 147, 1, 10.00, 49.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1700, 147, 1, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1701, 147, 2, 1.00, 9.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1702, 147, 2, 10.00, 49.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1703, 147, 2, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1704, 147, 3, 1.00, 9.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1705, 147, 3, 10.00, 49.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1706, 147, 3, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1707, 147, 4, 1.00, 9.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1708, 147, 4, 10.00, 49.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1709, 147, 4, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1710, 147, 6, 1.00, 9.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1711, 147, 6, 10.00, 49.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1712, 147, 6, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1713, 147, 7, 1.00, 9.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1714, 147, 7, 10.00, 49.00, 6500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1715, 147, 7, 50.00, NULL, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1716, 148, 1, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1717, 148, 1, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1718, 148, 1, 50.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1719, 148, 2, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1720, 148, 2, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1721, 148, 2, 50.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1722, 148, 3, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1723, 148, 3, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1724, 148, 3, 50.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1725, 148, 4, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1726, 148, 4, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1727, 148, 4, 50.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1728, 148, 6, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1729, 148, 6, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1730, 148, 6, 50.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1731, 148, 7, 1.00, 9.00, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1732, 148, 7, 10.00, 49.00, 7500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1733, 148, 7, 50.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1734, 149, 1, 1.00, 9.00, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1735, 149, 1, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1736, 149, 1, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1737, 149, 2, 1.00, 9.00, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1738, 149, 2, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1739, 149, 2, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1740, 149, 3, 1.00, 9.00, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1741, 149, 3, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1742, 149, 3, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1743, 149, 4, 1.00, 9.00, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1744, 149, 4, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1745, 149, 4, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1746, 149, 6, 1.00, 9.00, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1747, 149, 6, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1748, 149, 6, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1749, 149, 7, 1.00, 9.00, 10000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1750, 149, 7, 10.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1751, 149, 7, 50.00, NULL, 8500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1752, 150, 1, 1.00, 99.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1753, 150, 1, 100.00, 499.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1754, 150, 1, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1755, 150, 2, 1.00, 99.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1756, 150, 2, 100.00, 499.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1757, 150, 2, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1758, 150, 3, 1.00, 99.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1759, 150, 3, 100.00, 499.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1760, 150, 3, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1761, 150, 4, 1.00, 99.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1762, 150, 4, 100.00, 499.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1763, 150, 4, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1764, 150, 6, 1.00, 99.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1765, 150, 6, 100.00, 499.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1766, 150, 6, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1767, 150, 7, 1.00, 99.00, 2500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1768, 150, 7, 100.00, 499.00, 2000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1769, 150, 7, 500.00, NULL, 1500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1770, 151, 1, 1.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1771, 151, 1, 50.00, 99.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1772, 151, 1, 100.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1773, 151, 2, 1.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1774, 151, 2, 50.00, 99.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1775, 151, 2, 100.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1776, 151, 3, 1.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1777, 151, 3, 50.00, 99.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1778, 151, 3, 100.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1779, 151, 4, 1.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1780, 151, 4, 50.00, 99.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1781, 151, 4, 100.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1782, 151, 6, 1.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1783, 151, 6, 50.00, 99.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1784, 151, 6, 100.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1785, 151, 7, 1.00, 49.00, 9000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1786, 151, 7, 50.00, 99.00, 8000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1787, 151, 7, 100.00, NULL, 7000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1788, 152, 1, 1.00, 99.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1789, 152, 1, 100.00, 299.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1790, 152, 1, 300.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1791, 152, 2, 1.00, 99.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1792, 152, 2, 100.00, 299.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1793, 152, 2, 300.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1794, 152, 3, 1.00, 99.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1795, 152, 3, 100.00, 299.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1796, 152, 3, 300.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1797, 152, 4, 1.00, 99.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1798, 152, 4, 100.00, 299.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1799, 152, 4, 300.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1800, 152, 6, 1.00, 99.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1801, 152, 6, 100.00, 299.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1802, 152, 6, 300.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1803, 152, 7, 1.00, 99.00, 6000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1804, 152, 7, 100.00, 299.00, 5000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1805, 152, 7, 300.00, NULL, 4500.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1806, 153, 1, 1.00, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1807, 153, 2, 1.00, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1808, 153, 3, 1.00, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1809, 153, 4, 1.00, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1810, 153, 6, 1.00, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1811, 153, 7, 1.00, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1812, 154, 1, 1.00, NULL, 84000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1813, 154, 2, 1.00, NULL, 84000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1814, 154, 3, 1.00, NULL, 84000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1815, 154, 4, 1.00, NULL, 84000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1816, 154, 6, 1.00, NULL, 84000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1817, 154, 7, 1.00, NULL, 84000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1818, 155, 1, 1.00, NULL, 90000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1819, 155, 2, 1.00, NULL, 90000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1820, 155, 3, 1.00, NULL, 90000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1821, 155, 4, 1.00, NULL, 90000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1822, 155, 6, 1.00, NULL, 90000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1823, 155, 7, 1.00, NULL, 90000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1824, 156, 1, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1825, 156, 2, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1826, 156, 3, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1827, 156, 4, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1828, 156, 6, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1829, 156, 7, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1830, 157, 1, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1831, 157, 2, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1832, 157, 3, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1833, 157, 4, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1834, 157, 6, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1835, 157, 7, 1.00, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1836, 158, 1, 1.00, NULL, 120000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1837, 158, 2, 1.00, NULL, 120000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1838, 158, 3, 1.00, NULL, 120000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1839, 158, 4, 1.00, NULL, 120000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1840, 158, 6, 1.00, NULL, 120000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1841, 158, 7, 1.00, NULL, 120000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1842, 159, 1, 1.00, NULL, 135000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1843, 159, 2, 1.00, NULL, 135000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1844, 159, 3, 1.00, NULL, 135000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1845, 159, 4, 1.00, NULL, 135000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1846, 159, 6, 1.00, NULL, 135000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1847, 159, 7, 1.00, NULL, 135000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1848, 160, 1, 1.00, NULL, 155000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1849, 160, 2, 1.00, NULL, 155000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1850, 160, 3, 1.00, NULL, 155000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1851, 160, 4, 1.00, NULL, 155000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1852, 160, 6, 1.00, NULL, 155000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1853, 160, 7, 1.00, NULL, 155000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1854, 161, 1, 1.00, NULL, 175000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1855, 161, 2, 1.00, NULL, 175000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1856, 161, 3, 1.00, NULL, 175000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1857, 161, 4, 1.00, NULL, 175000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1858, 161, 6, 1.00, NULL, 175000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1859, 161, 7, 1.00, NULL, 175000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1860, 162, 1, 1.00, NULL, 190600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1861, 162, 2, 1.00, NULL, 190600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1862, 162, 3, 1.00, NULL, 190600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1863, 162, 4, 1.00, NULL, 190600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1864, 162, 6, 1.00, NULL, 190600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1865, 162, 7, 1.00, NULL, 190600.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1866, 163, 1, 1.00, NULL, 185000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1867, 163, 2, 1.00, NULL, 185000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1868, 163, 3, 1.00, NULL, 185000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1869, 163, 4, 1.00, NULL, 185000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1870, 163, 6, 1.00, NULL, 185000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1871, 163, 7, 1.00, NULL, 185000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1872, 164, 1, 1.00, NULL, 210000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1873, 164, 2, 1.00, NULL, 210000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1874, 164, 3, 1.00, NULL, 210000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1875, 164, 4, 1.00, NULL, 210000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1876, 164, 6, 1.00, NULL, 210000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1877, 164, 7, 1.00, NULL, 210000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1878, 165, 1, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1879, 165, 2, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1880, 165, 3, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1881, 165, 4, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1882, 165, 6, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1883, 165, 7, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1884, 166, 1, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1885, 166, 2, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1886, 166, 3, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1887, 166, 4, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1888, 166, 6, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1889, 166, 7, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1890, 167, 1, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1891, 167, 2, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1892, 167, 3, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1893, 167, 4, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1894, 167, 6, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1895, 167, 7, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1896, 168, 1, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1897, 168, 2, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1898, 168, 3, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1899, 168, 4, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1900, 168, 6, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1901, 168, 7, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1902, 169, 1, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1903, 169, 2, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1904, 169, 3, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1905, 169, 4, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1906, 169, 6, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1907, 169, 7, 0.01, NULL, 150000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1908, 170, 1, 0.01, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1909, 170, 2, 0.01, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1910, 170, 3, 0.01, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1911, 170, 4, 0.01, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1912, 170, 6, 0.01, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1913, 170, 7, 0.01, NULL, 100000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1914, 171, 1, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1915, 171, 2, 0.01, NULL, 67000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-27 14:17:21'),
(1916, 171, 3, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1917, 171, 4, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1918, 171, 6, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1919, 171, 7, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1920, 172, 1, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1921, 172, 2, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1922, 172, 3, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1923, 172, 4, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1924, 172, 6, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1925, 172, 7, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1926, 173, 1, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1927, 173, 2, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1928, 173, 3, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1929, 173, 4, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1930, 173, 6, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1931, 173, 7, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1932, 174, 1, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1933, 174, 2, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1934, 174, 3, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1935, 174, 4, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1936, 174, 6, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1937, 174, 7, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1938, 175, 1, 0.01, NULL, 115000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1939, 175, 2, 0.01, NULL, 115000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1940, 175, 3, 0.01, NULL, 115000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1941, 175, 4, 0.01, NULL, 115000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1942, 175, 6, 0.01, NULL, 115000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1943, 175, 7, 0.01, NULL, 115000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1944, 176, 1, 1.00, NULL, 180000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1945, 176, 2, 1.00, NULL, 180000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1946, 176, 3, 1.00, NULL, 180000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1947, 176, 4, 1.00, NULL, 180000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1948, 176, 6, 1.00, NULL, 180000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1949, 176, 7, 1.00, NULL, 180000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1950, 177, 1, 1.00, NULL, 280000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1951, 177, 2, 1.00, NULL, 280000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1952, 177, 3, 1.00, NULL, 280000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1953, 177, 4, 1.00, NULL, 280000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1954, 177, 6, 1.00, NULL, 280000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1955, 177, 7, 1.00, NULL, 280000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1956, 178, 1, 0.01, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1957, 178, 2, 0.01, NULL, 16000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-27 14:17:37'),
(1958, 178, 3, 0.01, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1959, 178, 4, 0.01, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1960, 178, 6, 0.01, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1961, 178, 7, 0.01, NULL, 20000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1962, 179, 1, 0.01, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1963, 179, 2, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-27 14:19:36'),
(1964, 179, 3, 0.01, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1965, 179, 4, 0.01, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1966, 179, 6, 0.01, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1967, 179, 7, 0.01, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1968, 180, 1, 0.01, NULL, 13000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1969, 180, 2, 0.01, NULL, 13000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1970, 180, 3, 0.01, NULL, 13000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1971, 180, 4, 0.01, NULL, 13000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1972, 180, 6, 0.01, NULL, 13000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1973, 180, 7, 0.01, NULL, 13000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1974, 181, 1, 1.00, NULL, 700.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1975, 181, 2, 1.00, NULL, 700.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1976, 181, 3, 1.00, NULL, 700.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1977, 181, 4, 1.00, NULL, 700.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1978, 181, 6, 1.00, NULL, 700.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1979, 181, 7, 1.00, NULL, 700.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1980, 182, 1, 1.00, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1981, 182, 2, 1.00, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1982, 182, 3, 1.00, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1983, 182, 4, 1.00, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1984, 182, 6, 1.00, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1985, 182, 7, 1.00, NULL, 75000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1986, 183, 1, 1.00, NULL, 300000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1987, 183, 2, 1.00, NULL, 300000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1988, 183, 3, 1.00, NULL, 300000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1989, 183, 4, 1.00, NULL, 300000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1990, 183, 6, 1.00, NULL, 300000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1991, 183, 7, 1.00, NULL, 300000.00, NULL, 1, NULL, '2026-09-24 17:38:34', '2026-09-24 17:38:34'),
(1992, 26, 1, 1.00, NULL, 35000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1993, 26, 2, 1.00, NULL, 35000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1994, 26, 3, 1.00, NULL, 35000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1995, 26, 4, 1.00, NULL, 35000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1996, 26, 6, 1.00, NULL, 35000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1997, 26, 7, 1.00, NULL, 35000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1998, 27, 1, 1.00, NULL, 25000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(1999, 27, 2, 1.00, NULL, 25000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2000, 27, 3, 1.00, NULL, 25000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2001, 27, 4, 1.00, NULL, 25000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2002, 27, 6, 1.00, NULL, 25000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2003, 27, 7, 1.00, NULL, 25000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2004, 28, 1, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2005, 28, 2, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2006, 28, 3, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2007, 28, 4, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2008, 28, 6, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2009, 28, 7, 1.00, NULL, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2010, 29, 1, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2011, 29, 2, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2012, 29, 3, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2013, 29, 4, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2014, 29, 6, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2015, 29, 7, 1.00, NULL, 10000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2016, 145, 1, 1.00, 9.00, 20000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2017, 145, 1, 10.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2018, 145, 1, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2019, 145, 1, 100.00, NULL, 13500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2020, 145, 2, 1.00, 9.00, 20000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2021, 145, 2, 10.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2022, 145, 2, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2023, 145, 2, 100.00, NULL, 13500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2024, 145, 3, 1.00, 9.00, 20000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2025, 145, 3, 10.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2026, 145, 3, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2027, 145, 3, 100.00, NULL, 13500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2028, 145, 4, 1.00, 9.00, 20000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2029, 145, 4, 10.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2030, 145, 4, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2031, 145, 4, 100.00, NULL, 13500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2032, 145, 6, 1.00, 9.00, 20000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2033, 145, 6, 10.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2034, 145, 6, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2035, 145, 6, 100.00, NULL, 13500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2036, 145, 7, 1.00, 9.00, 20000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2037, 145, 7, 10.00, 49.00, 17500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2038, 145, 7, 50.00, 99.00, 15000.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2039, 145, 7, 100.00, NULL, 13500.00, NULL, 1, NULL, '2026-09-24 17:39:24', '2026-09-24 17:39:24'),
(2040, 23, 1, 1.00, 10.00, 12000.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2041, 23, 1, 11.00, NULL, 10500.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2042, 23, 3, 1.00, 10.00, 12000.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2043, 23, 3, 11.00, NULL, 10500.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2044, 23, 4, 1.00, 10.00, 12000.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2045, 23, 4, 11.00, NULL, 10500.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2046, 23, 6, 1.00, 10.00, 12000.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2047, 23, 6, 11.00, NULL, 10500.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2048, 23, 7, 1.00, 10.00, 12000.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2049, 23, 7, 11.00, NULL, 10500.00, NULL, 1, NULL, '2026-09-24 17:39:52', '2026-09-24 17:39:52'),
(2050, 2, 1, 0.10, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 13:59:38', '2026-09-27 13:59:38'),
(2051, 2, 2, 0.10, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 13:59:58', '2026-09-27 13:59:58'),
(2052, 2, 3, 0.10, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 13:59:58', '2026-09-27 13:59:58'),
(2053, 2, 4, 0.10, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 13:59:58', '2026-09-27 13:59:58'),
(2054, 2, 6, 0.10, 999999.00, 18000.00, NULL, 1, NULL, '2026-09-27 13:59:58', '2026-09-27 14:00:16'),
(2055, 2, 7, 0.10, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 13:59:58', '2026-09-27 13:59:58'),
(2056, 8, 2, 0.01, NULL, 47000.00, NULL, 1, NULL, '2026-09-27 14:16:29', '2026-09-27 14:16:41'),
(2057, 8, 3, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-27 14:16:29', '2026-09-27 14:16:29'),
(2058, 8, 4, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-27 14:16:29', '2026-09-27 14:16:29'),
(2059, 8, 6, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-27 14:16:29', '2026-09-27 14:16:29'),
(2060, 8, 7, 0.01, NULL, 50000.00, NULL, 1, NULL, '2026-09-27 14:16:29', '2026-09-27 14:16:29'),
(2061, 9, 2, 0.01, NULL, 77000.00, NULL, 1, NULL, '2026-09-27 14:16:49', '2026-09-27 14:16:53'),
(2062, 9, 3, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-27 14:16:49', '2026-09-27 14:16:49'),
(2063, 9, 4, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-27 14:16:49', '2026-09-27 14:16:49'),
(2064, 9, 6, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-27 14:16:49', '2026-09-27 14:16:49'),
(2065, 9, 7, 0.01, NULL, 80000.00, NULL, 1, NULL, '2026-09-27 14:16:49', '2026-09-27 14:16:49'),
(2066, 7, 2, 0.01, NULL, 67000.00, NULL, 1, NULL, '2026-09-27 14:17:01', '2026-09-27 14:17:05'),
(2067, 7, 3, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-27 14:17:01', '2026-09-27 14:17:01'),
(2068, 7, 4, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-27 14:17:01', '2026-09-27 14:17:01'),
(2069, 7, 6, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-27 14:17:01', '2026-09-27 14:17:01'),
(2070, 7, 7, 0.01, NULL, 70000.00, NULL, 1, NULL, '2026-09-27 14:17:01', '2026-09-27 14:17:01'),
(2071, 20, 1, 0.01, 224.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:17:51', '2026-09-27 14:18:03'),
(2076, 20, 2, 0.01, 224.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:05', '2026-09-27 14:18:08'),
(2077, 20, 3, 0.01, 224.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:05', '2026-09-27 14:18:05'),
(2078, 20, 4, 0.01, 224.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:05', '2026-09-27 14:18:05'),
(2079, 20, 6, 0.01, 224.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:05', '2026-09-27 14:18:05'),
(2080, 20, 7, 0.01, 224.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:05', '2026-09-27 14:18:05'),
(2081, 18, 2, 0.01, 154.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:34'),
(2082, 18, 2, 155.00, 308.00, 13000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:34'),
(2083, 18, 3, 0.01, 154.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2084, 18, 3, 155.00, 308.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2085, 18, 4, 0.01, 154.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2086, 18, 4, 155.00, 308.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2087, 18, 6, 0.01, 154.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2088, 18, 6, 155.00, 308.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2089, 18, 7, 0.01, 154.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2090, 18, 7, 155.00, 308.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:28', '2026-09-27 14:18:28'),
(2091, 19, 2, 0.01, 182.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:53'),
(2092, 19, 2, 183.00, 366.00, 13000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:53'),
(2093, 19, 3, 0.01, 182.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2094, 19, 3, 183.00, 366.00, 15000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2095, 19, 4, 0.01, 182.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2096, 19, 4, 183.00, 366.00, 15000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2097, 19, 6, 0.01, 182.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2098, 19, 6, 183.00, 366.00, 15000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2099, 19, 7, 0.01, 182.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2100, 19, 7, 183.00, 366.00, 15000.00, NULL, 1, NULL, '2026-09-27 14:18:46', '2026-09-27 14:18:46'),
(2101, 1, 2, 1.00, 999999.00, 16000.00, NULL, 1, NULL, '2026-09-27 14:19:10', '2026-09-27 14:19:14'),
(2102, 1, 3, 1.00, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:19:10', '2026-09-27 14:19:10'),
(2103, 1, 4, 1.00, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:19:10', '2026-09-27 14:19:10'),
(2104, 1, 6, 1.00, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:19:10', '2026-09-27 14:19:10'),
(2105, 1, 7, 1.00, 999999.00, 20000.00, NULL, 1, NULL, '2026-09-27 14:19:10', '2026-09-27 14:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `stock_opnames`
--

CREATE TABLE `stock_opnames` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `id_gudang` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Draft','Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Draft',
  `catatan` text DEFAULT NULL,
  `id_user_pembuat` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_opname_details`
--

CREATE TABLE `stock_opname_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_stock_opname` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `stok_sistem` decimal(15,4) NOT NULL,
  `stok_fisik` decimal(15,4) DEFAULT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` bigint(20) UNSIGNED NOT NULL,
  `nama_supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kontak` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `alamat`, `kontak`) VALUES
(1, 'ALEA GRAFIKA', 'Surabaya', '082212454878'),
(2, 'CV. MANDIRI JAYA', 'Surabaya', '082124578782'),
(3, 'PT. CELLO GRAFIKA', 'Surabaya', '08212487845'),
(4, 'PT. SARI AGUNG', 'Jalan Kedung Cuwek, Surabaya', '-'),
(5, 'MAS Anas Mejuar', 'Surabaya', '-'),
(6, 'Mitra Printing Solution', 'Surabaya', '-'),
(7, 'COLORLINK', 'Surabaya', '-'),
(9, 'WUJUD UNGGUL', 'Surabaya', '-');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `nomor_nota` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_order` enum('Reguler','PO') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Reguler',
  `no_po` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_konsumen` bigint(20) UNSIGNED NOT NULL,
  `nama_konsumen_manual` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_user_kasir` bigint(20) UNSIGNED NOT NULL,
  `tgl_order` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_bayar` decimal(15,2) NOT NULL,
  `status_pembayaran` enum('Lunas','DP','Belum Bayar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status_nota` enum('Aktif','Dibatalkan') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `alasan_batal_nota` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_dibatalkan_nota` timestamp NULL DEFAULT NULL,
  `id_user_pembatal_nota` bigint(20) UNSIGNED DEFAULT NULL,
  `status_collecting` enum('Belum Diambil','Sudah Diambil') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Belum Diambil',
  `tgl_collecting` timestamp NULL DEFAULT NULL,
  `id_user_collecting` bigint(20) UNSIGNED DEFAULT NULL,
  `metode_pembayaran` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `nomor_nota`, `tipe_order`, `no_po`, `id_konsumen`, `nama_konsumen_manual`, `id_user_kasir`, `tgl_order`, `total_bayar`, `status_pembayaran`, `status_nota`, `alasan_batal_nota`, `tgl_dibatalkan_nota`, `id_user_pembatal_nota`, `status_collecting`, `tgl_collecting`, `id_user_collecting`, `metode_pembayaran`, `created_at`, `updated_at`) VALUES
(3, 'ORG-20260927-001', 'Reguler', NULL, 17, NULL, 1, '2026-09-27 14:26:42', 112600.00, 'Belum Bayar', 'Dibatalkan', 'Dikoreksi via alur Edit Nota (disetujui Administrator) - alasan kasir: nota uji coba', '2026-09-27 14:46:27', 1, 'Belum Diambil', NULL, NULL, NULL, NULL, '2026-09-27 14:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `id_produk` bigint(20) UNSIGNED DEFAULT NULL,
  `id_jasa` bigint(20) UNSIGNED DEFAULT NULL,
  `id_bahan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_finishing` bigint(20) UNSIGNED DEFAULT NULL,
  `biaya_finishing` decimal(15,2) DEFAULT 0.00,
  `biaya_cetak` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_desain` decimal(15,2) NOT NULL DEFAULT 0.00,
  `file_siap_cetak` tinyint(1) NOT NULL DEFAULT 0,
  `id_desainer` bigint(20) UNSIGNED DEFAULT NULL,
  `komisi_desain_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
  `komisi_desain` decimal(12,2) NOT NULL DEFAULT 0.00,
  `komisi_status` varchar(10) DEFAULT NULL,
  `komisi_dibayar_at` timestamp NULL DEFAULT NULL,
  `id_kas_kecil_komisi` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `p_ukuran` decimal(10,2) NOT NULL,
  `l_ukuran` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `status_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `status_urgensi` enum('Normal','Urgent','Very Urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Normal',
  `status_finishing` enum('Standar','Custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Standar',
  `qty_cetak` int(11) DEFAULT NULL,
  `catatan_produksi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hpp_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_spk` enum('Antre','Desain','Cetak','Selesai','Batal','Pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Antre',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id_detail`, `id_transaksi`, `id_produk`, `id_jasa`, `id_bahan`, `id_finishing`, `biaya_finishing`, `biaya_cetak`, `biaya_desain`, `file_siap_cetak`, `id_desainer`, `komisi_desain_persen`, `komisi_desain`, `komisi_status`, `komisi_dibayar_at`, `id_kas_kecil_komisi`, `nama_file`, `thumbnail`, `p_ukuran`, `l_ukuran`, `qty`, `status_urgent`, `status_urgensi`, `status_finishing`, `qty_cetak`, `catatan_produksi`, `subtotal`, `potongan`, `hpp_snapshot`, `harga_jual_snapshot`, `status_spk`, `created_at`, `updated_at`) VALUES
(1, 3, 2, NULL, NULL, 8, 16600.00, 96000.00, 0.00, 0, NULL, 0.00, 0.00, NULL, NULL, NULL, 'orion flags', 'order_thumbnails/cdvviVjoivmYrAnjQvyumXEevwo6tYHWK7VAZpIV.jpg', 120.00, 400.00, 1, 0, 'Normal', 'Custom', NULL, NULL, 112600.00, 0.00, 57418.36, 112600.00, 'Batal', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail_bahan_pakai`
--

CREATE TABLE `transaksi_detail_bahan_pakai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty_pakai` decimal(15,4) NOT NULL,
  `is_tinta` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi_detail_bahan_pakai`
--

INSERT INTO `transaksi_detail_bahan_pakai` (`id`, `id_detail`, `id_bahan`, `qty_pakai`, `is_tinta`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 0.0177, 0, '2026-09-27 14:26:42', '2026-09-27 14:26:42'),
(2, 1, 46, 0.0864, 1, '2026-09-27 14:26:42', '2026-09-27 14:26:42');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail_finishing`
--

CREATE TABLE `transaksi_detail_finishing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_finishing` bigint(20) UNSIGNED NOT NULL,
  `status_finishing` varchar(10) NOT NULL DEFAULT 'Standar',
  `qty_pakai` decimal(14,4) DEFAULT NULL,
  `biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi_detail_finishing`
--

INSERT INTO `transaksi_detail_finishing` (`id`, `id_detail`, `id_finishing`, `status_finishing`, `qty_pakai`, `biaya`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 1, 8, 'Custom', NULL, 15600.00, 'tali 4', '2026-09-27 14:26:42', '2026-09-27 14:26:42'),
(2, 1, 9, 'Custom', 2.0000, 1000.00, 'tambahan tali 2', '2026-09-27 14:26:42', '2026-09-27 14:26:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_karyawan` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_role` bigint(20) UNSIGNED NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_kelas_desainer` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `id_karyawan`, `username`, `password`, `id_role`, `remember_token`, `created_at`, `updated_at`, `id_kelas_desainer`) VALUES
(1, 1, 'admin', '$2y$12$G2HMSNe1XosO.y0b/Oi9BeTRrhba3WqWG8LGFBrwMdrJ0gL6ISx7m', 1, NULL, NULL, NULL, NULL),
(4, 6, 'rehan', '$2y$12$4E7JRf2FEslrjHLz2llGV.HkHrndxHcZw7BexJ.AiPdo.lpiT0xFa', 2, NULL, '2026-02-25 14:36:24', '2026-09-10 01:29:20', 1),
(5, 7, 'rahman', '$2y$12$qniJ544jbTScKyUIDGlTD.InXuVXN0JERQ/2epuOsC0Uiv7jzga0u', 2, NULL, '2026-02-25 14:36:40', '2026-02-25 14:36:40', 1),
(14, 8, 'opepson', '$2y$12$d6392M3gnUYGyL5Z5duul.uKzA2t39YUgN1PbWcXIjj87q/.fz6x2', 4, NULL, '2026-09-11 04:04:08', '2026-09-11 04:04:08', NULL),
(24, 10, 'kasir', '$2y$12$/PXh.KlE2nuYhRw2SOkwZ.Ym6clW3tfPdeKh2FRgaO9FHDOgg0pPW', 3, NULL, '2026-09-24 01:11:31', '2026-09-24 01:11:31', NULL),
(25, 11, 'opcentury', '$2y$12$G3vNbQ2NbgleNmDnGJf30eynjpCHstXbXoQZe3HQOGQOYAzf7EUHW', 4, NULL, '2026-09-25 13:15:02', '2026-09-25 13:15:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_menu` bigint(20) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_add` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waste_log`
--

CREATE TABLE `waste_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `id_bahan` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `jenis` enum('Normal','Abnormal') NOT NULL,
  `alasan` varchar(255) NOT NULL,
  `id_detail` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Diterapkan','Menunggu','Ditolak') NOT NULL DEFAULT 'Diterapkan',
  `biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worksheet`
--

CREATE TABLE `worksheet` (
  `id_worksheet` bigint(20) UNSIGNED NOT NULL,
  `nama_mesin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_periods`
--
ALTER TABLE `accounting_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounting_periods_tahun_bulan_unique` (`tahun`,`bulan`);

--
-- Indexes for table `approval_requests`
--
ALTER TABLE `approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approval_requests_tipe_aksi_status_index` (`tipe_aksi`,`status`),
  ADD KEY `approval_requests_referensi_type_referensi_id_index` (`referensi_type`,`referensi_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audit_logs_user_id_index` (`user_id`),
  ADD KEY `audit_logs_created_at_index` (`created_at`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `backup_logs_tipe_created_at_index` (`tipe`,`created_at`);

--
-- Indexes for table `bag_akun`
--
ALTER TABLE `bag_akun`
  ADD PRIMARY KEY (`id_akun`),
  ADD UNIQUE KEY `bag_akun_kode_akun_unique` (`kode_akun`);

--
-- Indexes for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  ADD PRIMARY KEY (`id_bahan`),
  ADD UNIQUE KEY `bahan_baku_kode_bahan_unique` (`kode_bahan`),
  ADD KEY `bahan_baku_id_jenis_bahan_foreign` (`id_jenis_bahan`),
  ADD KEY `bahan_baku_id_kategori_foreign` (`id_kategori`),
  ADD KEY `bahan_baku_id_merk_foreign` (`id_merk`),
  ADD KEY `bahan_baku_id_mesin_foreign` (`id_mesin`),
  ADD KEY `bahan_baku_id_satuan_hitung_foreign` (`id_satuan_hitung`),
  ADD KEY `bahan_baku_id_satuan_stok_foreign` (`id_satuan_stok`);

--
-- Indexes for table `bank_perusahaan`
--
ALTER TABLE `bank_perusahaan`
  ADD PRIMARY KEY (`id_bank`);

--
-- Indexes for table `bom_detail`
--
ALTER TABLE `bom_detail`
  ADD PRIMARY KEY (`id_bom`),
  ADD KEY `bom_detail_id_bahan_foreign` (`id_bahan`),
  ADD KEY `bom_detail_id_produk_foreign` (`id_produk`);

--
-- Indexes for table `bom_finishing`
--
ALTER TABLE `bom_finishing`
  ADD PRIMARY KEY (`id_bom_finishing`),
  ADD KEY `bom_finishing_id_finishing_foreign` (`id_finishing`),
  ADD KEY `bom_finishing_id_produk_foreign` (`id_produk`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `design_proofs`
--
ALTER TABLE `design_proofs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `design_proofs_id_detail_revisi_no_unique` (`id_detail`,`revisi_no`);

--
-- Indexes for table `document_number_counters`
--
ALTER TABLE `document_number_counters`
  ADD PRIMARY KEY (`kode_dokumen`,`periode`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `finishing`
--
ALTER TABLE `finishing`
  ADD PRIMARY KEY (`id_finishing`),
  ADD KEY `fk_finishing_mesin` (`id_mesin`);

--
-- Indexes for table `finishing_harga`
--
ALTER TABLE `finishing_harga`
  ADD PRIMARY KEY (`id_finishing_harga`),
  ADD KEY `finishing_harga_id_finishing_foreign` (`id_finishing`);

--
-- Indexes for table `golongan_konsumen`
--
ALTER TABLE `golongan_konsumen`
  ADD PRIMARY KEY (`id_golongan`);

--
-- Indexes for table `gudang`
--
ALTER TABLE `gudang`
  ADD PRIMARY KEY (`id_gudang`);

--
-- Indexes for table `harga_promo`
--
ALTER TABLE `harga_promo`
  ADD PRIMARY KEY (`id_promo`),
  ADD KEY `harga_promo_id_produk_foreign` (`id_produk`);

--
-- Indexes for table `harga_siap_cetak`
--
ALTER TABLE `harga_siap_cetak`
  ADD PRIMARY KEY (`id_harga_siap_cetak`),
  ADD KEY `harga_siap_cetak_id_produk_id_golongan_index` (`id_produk`,`id_golongan`),
  ADD KEY `harga_siap_cetak_id_produk_index` (`id_produk`);

--
-- Indexes for table `jam_kerja`
--
ALTER TABLE `jam_kerja`
  ADD PRIMARY KEY (`hari`);

--
-- Indexes for table `jasa`
--
ALTER TABLE `jasa`
  ADD PRIMARY KEY (`id_jasa`),
  ADD KEY `jasa_id_satuan_foreign` (`id_satuan`);

--
-- Indexes for table `jasa_harga`
--
ALTER TABLE `jasa_harga`
  ADD PRIMARY KEY (`id_jasa_harga`),
  ADD KEY `jasa_harga_id_jasa_foreign` (`id_jasa`);

--
-- Indexes for table `jenis_bahan`
--
ALTER TABLE `jenis_bahan`
  ADD PRIMARY KEY (`id_jenis_bahan`);

--
-- Indexes for table `jersey_categories`
--
ALTER TABLE `jersey_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_categories_name_unique` (`name`);

--
-- Indexes for table `jersey_collar_types`
--
ALTER TABLE `jersey_collar_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_collar_types_name_unique` (`name`);

--
-- Indexes for table `jersey_components`
--
ALTER TABLE `jersey_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_components_name_unique` (`name`),
  ADD KEY `jersey_components_jersey_category_id_foreign` (`jersey_category_id`),
  ADD KEY `jersey_components_jersey_product_type_id_foreign` (`jersey_product_type_id`);

--
-- Indexes for table `jersey_designers`
--
ALTER TABLE `jersey_designers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_designers_name_unique` (`name`);

--
-- Indexes for table `jersey_materials`
--
ALTER TABLE `jersey_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_materials_name_unique` (`name`),
  ADD KEY `jersey_materials_id_bahan_index` (`id_bahan`);

--
-- Indexes for table `jersey_orders`
--
ALTER TABLE `jersey_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_orders_order_number_unique` (`order_number`),
  ADD KEY `jersey_orders_created_by_foreign` (`created_by`),
  ADD KEY `jersey_orders_id_konsumen_foreign` (`id_konsumen`),
  ADD KEY `jersey_orders_jersey_designer_id_foreign` (`jersey_designer_id`);

--
-- Indexes for table `jersey_order_items`
--
ALTER TABLE `jersey_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jersey_order_items_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  ADD KEY `jersey_order_items_jersey_order_line_id_foreign` (`jersey_order_line_id`),
  ADD KEY `jersey_order_items_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`);

--
-- Indexes for table `jersey_order_lines`
--
ALTER TABLE `jersey_order_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jersey_order_lines_jersey_category_id_foreign` (`jersey_category_id`),
  ADD KEY `jersey_order_lines_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  ADD KEY `jersey_order_lines_jersey_material_id_foreign` (`jersey_material_id`),
  ADD KEY `jersey_order_lines_jersey_order_id_foreign` (`jersey_order_id`),
  ADD KEY `jersey_order_lines_jersey_price_setting_id_foreign` (`jersey_price_setting_id`),
  ADD KEY `jersey_order_lines_jersey_product_type_id_foreign` (`jersey_product_type_id`),
  ADD KEY `jersey_order_lines_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`);

--
-- Indexes for table `jersey_order_line_materials`
--
ALTER TABLE `jersey_order_line_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jersey_order_line_materials_jersey_order_line_id_foreign` (`jersey_order_line_id`);

--
-- Indexes for table `jersey_order_payments`
--
ALTER TABLE `jersey_order_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jersey_order_payments_created_by_foreign` (`created_by`),
  ADD KEY `jersey_order_payments_jersey_order_id_foreign` (`jersey_order_id`);

--
-- Indexes for table `jersey_order_potong_stok`
--
ALTER TABLE `jersey_order_potong_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jersey_order_potong_stok_jersey_order_id_index` (`jersey_order_id`);

--
-- Indexes for table `jersey_order_size_quantities`
--
ALTER TABLE `jersey_order_size_quantities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_order_line_size_unique` (`jersey_order_line_id`,`jersey_size_id`),
  ADD KEY `jersey_order_size_quantities_jersey_size_id_foreign` (`jersey_size_id`);

--
-- Indexes for table `jersey_price_settings`
--
ALTER TABLE `jersey_price_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jersey_price_settings_jersey_category_id_foreign` (`jersey_category_id`),
  ADD KEY `jersey_price_settings_jersey_material_id_foreign` (`jersey_material_id`),
  ADD KEY `jersey_price_tier_idx` (`jersey_product_type_id`,`jersey_category_id`,`qty_min`,`qty_max`);

--
-- Indexes for table `jersey_product_types`
--
ALTER TABLE `jersey_product_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_product_types_name_unique` (`name`);

--
-- Indexes for table `jersey_sizes`
--
ALTER TABLE `jersey_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_sizes_name_unique` (`name`);

--
-- Indexes for table `jersey_size_collar_usage`
--
ALTER TABLE `jersey_size_collar_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_size_collar_unique` (`jersey_size_id`,`jersey_collar_type_id`),
  ADD KEY `jersey_size_collar_usage_jersey_collar_type_id_foreign` (`jersey_collar_type_id`);

--
-- Indexes for table `jersey_size_component_usage`
--
ALTER TABLE `jersey_size_component_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_size_component_unique` (`jersey_size_id`,`jersey_component_id`),
  ADD KEY `jersey_size_component_usage_jersey_component_id_foreign` (`jersey_component_id`);

--
-- Indexes for table `jersey_size_sleeve_usage`
--
ALTER TABLE `jersey_size_sleeve_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_size_sleeve_unique` (`jersey_size_id`,`jersey_sleeve_type_id`),
  ADD KEY `jersey_size_sleeve_usage_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`);

--
-- Indexes for table `jersey_sleeve_types`
--
ALTER TABLE `jersey_sleeve_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jersey_sleeve_types_name_unique` (`name`);

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
-- Indexes for table `jurnal_umum`
--
ALTER TABLE `jurnal_umum`
  ADD PRIMARY KEY (`id_jurnal`),
  ADD KEY `jurnal_umum_kode_akun_foreign` (`kode_akun`);

--
-- Indexes for table `kartu_stok`
--
ALTER TABLE `kartu_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_id_bahan` (`id_bahan`),
  ADD KEY `kartu_stok_id_bahan_tanggal_index` (`id_bahan`,`tanggal`),
  ADD KEY `kartu_stok_id_bahan_referensi_index` (`id_bahan`,`referensi`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- Indexes for table `karyawan_mesin`
--
ALTER TABLE `karyawan_mesin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `karyawan_mesin_id_karyawan_id_mesin_unique` (`id_karyawan`,`id_mesin`),
  ADD KEY `karyawan_mesin_id_mesin_foreign` (`id_mesin`);

--
-- Indexes for table `kas_harian`
--
ALTER TABLE `kas_harian`
  ADD PRIMARY KEY (`id_kas`),
  ADD KEY `kas_harian_id_user_foreign` (`id_user`);

--
-- Indexes for table `kas_kecil`
--
ALTER TABLE `kas_kecil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kas_kecil_id_kategori_foreign` (`id_kategori`),
  ADD KEY `kas_kecil_id_user_foreign` (`id_user`);

--
-- Indexes for table `kas_manual`
--
ALTER TABLE `kas_manual`
  ADD PRIMARY KEY (`id_kas_manual`),
  ADD KEY `kas_manual_id_kas_foreign` (`id_kas`),
  ADD KEY `kas_manual_id_user_foreign` (`id_user`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `kategori_finishing`
--
ALTER TABLE `kategori_finishing`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `kategori_kas_kecil`
--
ALTER TABLE `kategori_kas_kecil`
  ADD PRIMARY KEY (`id_kategori_kas_kecil`),
  ADD UNIQUE KEY `kategori_kas_kecil_nama_kategori_unique` (`nama_kategori`);

--
-- Indexes for table `kedatangan_bahan`
--
ALTER TABLE `kedatangan_bahan`
  ADD PRIMARY KEY (`id_kedatangan`),
  ADD KEY `kedatangan_bahan_id_gudang_foreign` (`id_gudang`),
  ADD KEY `kedatangan_bahan_id_supplier_foreign` (`id_supplier`),
  ADD KEY `kedatangan_bahan_id_purchase_order_foreign` (`id_purchase_order`);

--
-- Indexes for table `kedatangan_bahan_detail`
--
ALTER TABLE `kedatangan_bahan_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `kedatangan_bahan_detail_id_bahan_foreign` (`id_bahan`),
  ADD KEY `kedatangan_bahan_detail_id_kedatangan_foreign` (`id_kedatangan`);

--
-- Indexes for table `kelas_desainer`
--
ALTER TABLE `kelas_desainer`
  ADD PRIMARY KEY (`id_kelas`),
  ADD UNIQUE KEY `kelas_desainer_nama_kelas_unique` (`nama_kelas`);

--
-- Indexes for table `konsumen`
--
ALTER TABLE `konsumen`
  ADD PRIMARY KEY (`id_konsumen`),
  ADD KEY `konsumen_id_golongan_foreign` (`id_golongan`);

--
-- Indexes for table `konsumen_deposit`
--
ALTER TABLE `konsumen_deposit`
  ADD PRIMARY KEY (`id_deposit`),
  ADD KEY `konsumen_deposit_id_konsumen_foreign` (`id_konsumen`),
  ADD KEY `konsumen_deposit_id_transaksi_foreign` (`id_transaksi`),
  ADD KEY `konsumen_deposit_id_user_foreign` (`id_user`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `merk`
--
ALTER TABLE `merk`
  ADD PRIMARY KEY (`id_merk`);

--
-- Indexes for table `mesin`
--
ALTER TABLE `mesin`
  ADD PRIMARY KEY (`id_mesin`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nota_edit_request`
--
ALTER TABLE `nota_edit_request`
  ADD PRIMARY KEY (`id_request`),
  ADD KEY `nota_edit_request_id_transaksi_foreign` (`id_transaksi`),
  ADD KEY `nota_edit_request_id_user_approver_foreign` (`id_user_approver`),
  ADD KEY `nota_edit_request_id_user_requester_foreign` (`id_user_requester`);

--
-- Indexes for table `order_berkas`
--
ALTER TABLE `order_berkas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_berkas_id_transaksi_foreign` (`id_transaksi`),
  ADD KEY `order_berkas_id_detail_foreign` (`id_detail`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pemakaian_tinta`
--
ALTER TABLE `pemakaian_tinta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pemakaian_tinta_unik` (`id_mesin`,`id_bahan`,`dasar`);

--
-- Indexes for table `pembayaran_hutang`
--
ALTER TABLE `pembayaran_hutang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayaran_hutang_id_pembelian_voided_at_index` (`id_pembelian`,`voided_at`);

--
-- Indexes for table `pembayaran_riwayat`
--
ALTER TABLE `pembayaran_riwayat`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `pembayaran_riwayat_id_bank_foreign` (`id_bank`);

--
-- Indexes for table `pembelian_bahan`
--
ALTER TABLE `pembelian_bahan`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD UNIQUE KEY `pembelian_bahan_no_faktur_unique` (`no_faktur`),
  ADD KEY `pembelian_bahan_id_kedatangan_foreign` (`id_kedatangan`);

--
-- Indexes for table `pembelian_bahan_detail`
--
ALTER TABLE `pembelian_bahan_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_bahan` (`id_bahan`),
  ADD KEY `id_pembelian` (`id_pembelian`);

--
-- Indexes for table `pengambilan`
--
ALTER TABLE `pengambilan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengambilan_id_transaksi_unique` (`id_transaksi`);

--
-- Indexes for table `platform_belanja`
--
ALTER TABLE `platform_belanja`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `platform_belanja_nama_unique` (`nama`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `posts_slug_unique` (`slug`),
  ADD KEY `posts_id_kategori_index` (`id_kategori`),
  ADD KEY `posts_status_index` (`status`),
  ADD KEY `posts_terbit_at_index` (`terbit_at`),
  ADD KEY `posts_disematkan_index` (`disematkan`);

--
-- Indexes for table `post_kategori`
--
ALTER TABLE `post_kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_kategori_nama_unique` (`nama`),
  ADD UNIQUE KEY `post_kategori_slug_unique` (`slug`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `produk_finishing`
--
ALTER TABLE `produk_finishing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_orders_nomor_unique` (`nomor`),
  ADD KEY `purchase_orders_id_purchase_request_foreign` (`id_purchase_request`),
  ADD KEY `purchase_orders_id_supplier_foreign` (`id_supplier`),
  ADD KEY `purchase_orders_id_user_pembuat_foreign` (`id_user_pembuat`);

--
-- Indexes for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_details_id_purchase_order_foreign` (`id_purchase_order`),
  ADD KEY `purchase_order_details_id_bahan_foreign` (`id_bahan`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_requests_nomor_unique` (`nomor`),
  ADD KEY `purchase_requests_id_user_pembuat_foreign` (`id_user_pembuat`);

--
-- Indexes for table `purchase_request_details`
--
ALTER TABLE `purchase_request_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_details_id_purchase_request_foreign` (`id_purchase_request`),
  ADD KEY `purchase_request_details_id_bahan_foreign` (`id_bahan`);

--
-- Indexes for table `qc_inspeksi`
--
ALTER TABLE `qc_inspeksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qc_inspeksi_nomor_unique` (`nomor`),
  ADD KEY `qc_inspeksi_id_detail_foreign` (`id_detail`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotations_nomor_unique` (`nomor`),
  ADD KEY `quotations_id_konsumen_foreign` (`id_konsumen`),
  ADD KEY `quotations_id_golongan_foreign` (`id_golongan`),
  ADD KEY `quotations_id_transaksi_foreign` (`id_transaksi`),
  ADD KEY `quotations_id_user_pembuat_foreign` (`id_user_pembuat`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_items_id_quotation_foreign` (`id_quotation`);

--
-- Indexes for table `rework`
--
ALTER TABLE `rework`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rework_nomor_unique` (`nomor`),
  ADD KEY `rework_id_qc_inspeksi_foreign` (`id_qc_inspeksi`),
  ADD KEY `rework_id_detail_foreign` (`id_detail`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id_role`,`id_menu`),
  ADD KEY `role_permissions_id_menu_foreign` (`id_menu`);

--
-- Indexes for table `satuan`
--
ALTER TABLE `satuan`
  ADD PRIMARY KEY (`id_satuan`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`),
  ADD KEY `sessions_user_id_index` (`user_id`);

--
-- Indexes for table `setting_harga`
--
ALTER TABLE `setting_harga`
  ADD PRIMARY KEY (`id_harga`),
  ADD KEY `fk_setting_harga_satuan` (`id_satuan`),
  ADD KEY `fk_setting_harga_satuan_berlaku` (`id_satuan_berlaku`),
  ADD KEY `setting_harga_id_golongan_foreign` (`id_golongan`),
  ADD KEY `setting_harga_id_produk_foreign` (`id_produk`);

--
-- Indexes for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_opnames_nomor_unique` (`nomor`),
  ADD KEY `stock_opnames_id_gudang_foreign` (`id_gudang`),
  ADD KEY `stock_opnames_id_user_pembuat_foreign` (`id_user_pembuat`);

--
-- Indexes for table `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_opname_details_id_stock_opname_id_bahan_unique` (`id_stock_opname`,`id_bahan`),
  ADD KEY `stock_opname_details_id_bahan_foreign` (`id_bahan`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `transaksi_nomor_nota_unique` (`nomor_nota`),
  ADD KEY `transaksi_id_konsumen_foreign` (`id_konsumen`),
  ADD KEY `transaksi_id_user_collecting_foreign` (`id_user_collecting`),
  ADD KEY `transaksi_id_user_kasir_foreign` (`id_user_kasir`),
  ADD KEY `transaksi_id_user_pembatal_nota_foreign` (`id_user_pembatal_nota`),
  ADD KEY `transaksi_tgl_order_status_nota_index` (`tgl_order`,`status_nota`);

--
-- Indexes for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `transaksi_detail_id_bahan_foreign` (`id_bahan`),
  ADD KEY `transaksi_detail_id_finishing_foreign` (`id_finishing`),
  ADD KEY `transaksi_detail_id_jasa_foreign` (`id_jasa`),
  ADD KEY `transaksi_detail_id_produk_foreign` (`id_produk`),
  ADD KEY `transaksi_detail_id_transaksi_foreign` (`id_transaksi`),
  ADD KEY `transaksi_detail_id_desainer_index` (`id_desainer`),
  ADD KEY `transaksi_detail_status_spk_id_detail_index` (`status_spk`,`id_detail`);

--
-- Indexes for table `transaksi_detail_bahan_pakai`
--
ALTER TABLE `transaksi_detail_bahan_pakai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_detail_bahan_pakai_id_detail_id_bahan_index` (`id_detail`,`id_bahan`),
  ADD KEY `transaksi_detail_bahan_pakai_id_detail_index` (`id_detail`);

--
-- Indexes for table `transaksi_detail_finishing`
--
ALTER TABLE `transaksi_detail_finishing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_detail_finishing_id_detail_index` (`id_detail`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_id_karyawan_foreign` (`id_karyawan`),
  ADD KEY `users_id_role_foreign` (`id_role`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id_user`,`id_menu`);

--
-- Indexes for table `waste_log`
--
ALTER TABLE `waste_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `waste_log_nomor_unique` (`nomor`),
  ADD KEY `waste_log_id_bahan_foreign` (`id_bahan`),
  ADD KEY `waste_log_id_detail_foreign` (`id_detail`),
  ADD KEY `waste_log_tanggal_jenis_index` (`tanggal`,`jenis`);

--
-- Indexes for table `worksheet`
--
ALTER TABLE `worksheet`
  ADD PRIMARY KEY (`id_worksheet`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounting_periods`
--
ALTER TABLE `accounting_periods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `approval_requests`
--
ALTER TABLE `approval_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `bag_akun`
--
ALTER TABLE `bag_akun`
  MODIFY `id_akun` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  MODIFY `id_bahan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `bank_perusahaan`
--
ALTER TABLE `bank_perusahaan`
  MODIFY `id_bank` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bom_detail`
--
ALTER TABLE `bom_detail`
  MODIFY `id_bom` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT for table `bom_finishing`
--
ALTER TABLE `bom_finishing`
  MODIFY `id_bom_finishing` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `design_proofs`
--
ALTER TABLE `design_proofs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finishing`
--
ALTER TABLE `finishing`
  MODIFY `id_finishing` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `finishing_harga`
--
ALTER TABLE `finishing_harga`
  MODIFY `id_finishing_harga` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `golongan_konsumen`
--
ALTER TABLE `golongan_konsumen`
  MODIFY `id_golongan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `gudang`
--
ALTER TABLE `gudang`
  MODIFY `id_gudang` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `harga_promo`
--
ALTER TABLE `harga_promo`
  MODIFY `id_promo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `harga_siap_cetak`
--
ALTER TABLE `harga_siap_cetak`
  MODIFY `id_harga_siap_cetak` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jasa`
--
ALTER TABLE `jasa`
  MODIFY `id_jasa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jasa_harga`
--
ALTER TABLE `jasa_harga`
  MODIFY `id_jasa_harga` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_bahan`
--
ALTER TABLE `jenis_bahan`
  MODIFY `id_jenis_bahan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jersey_categories`
--
ALTER TABLE `jersey_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jersey_collar_types`
--
ALTER TABLE `jersey_collar_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jersey_components`
--
ALTER TABLE `jersey_components`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jersey_designers`
--
ALTER TABLE `jersey_designers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jersey_materials`
--
ALTER TABLE `jersey_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jersey_orders`
--
ALTER TABLE `jersey_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `jersey_order_items`
--
ALTER TABLE `jersey_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jersey_order_lines`
--
ALTER TABLE `jersey_order_lines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `jersey_order_line_materials`
--
ALTER TABLE `jersey_order_line_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `jersey_order_payments`
--
ALTER TABLE `jersey_order_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jersey_order_potong_stok`
--
ALTER TABLE `jersey_order_potong_stok`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jersey_order_size_quantities`
--
ALTER TABLE `jersey_order_size_quantities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `jersey_price_settings`
--
ALTER TABLE `jersey_price_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `jersey_product_types`
--
ALTER TABLE `jersey_product_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jersey_sizes`
--
ALTER TABLE `jersey_sizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `jersey_size_collar_usage`
--
ALTER TABLE `jersey_size_collar_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `jersey_size_component_usage`
--
ALTER TABLE `jersey_size_component_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `jersey_size_sleeve_usage`
--
ALTER TABLE `jersey_size_sleeve_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `jersey_sleeve_types`
--
ALTER TABLE `jersey_sleeve_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jurnal_umum`
--
ALTER TABLE `jurnal_umum`
  MODIFY `id_jurnal` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `kartu_stok`
--
ALTER TABLE `kartu_stok`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id_karyawan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `karyawan_mesin`
--
ALTER TABLE `karyawan_mesin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kas_harian`
--
ALTER TABLE `kas_harian`
  MODIFY `id_kas` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kas_kecil`
--
ALTER TABLE `kas_kecil`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kas_manual`
--
ALTER TABLE `kas_manual`
  MODIFY `id_kas_manual` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kategori_finishing`
--
ALTER TABLE `kategori_finishing`
  MODIFY `id_kategori` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_kas_kecil`
--
ALTER TABLE `kategori_kas_kecil`
  MODIFY `id_kategori_kas_kecil` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `kedatangan_bahan`
--
ALTER TABLE `kedatangan_bahan`
  MODIFY `id_kedatangan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kedatangan_bahan_detail`
--
ALTER TABLE `kedatangan_bahan_detail`
  MODIFY `id_detail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kelas_desainer`
--
ALTER TABLE `kelas_desainer`
  MODIFY `id_kelas` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `konsumen`
--
ALTER TABLE `konsumen`
  MODIFY `id_konsumen` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `konsumen_deposit`
--
ALTER TABLE `konsumen_deposit`
  MODIFY `id_deposit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `merk`
--
ALTER TABLE `merk`
  MODIFY `id_merk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mesin`
--
ALTER TABLE `mesin`
  MODIFY `id_mesin` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `nota_edit_request`
--
ALTER TABLE `nota_edit_request`
  MODIFY `id_request` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_berkas`
--
ALTER TABLE `order_berkas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemakaian_tinta`
--
ALTER TABLE `pemakaian_tinta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `pembayaran_hutang`
--
ALTER TABLE `pembayaran_hutang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran_riwayat`
--
ALTER TABLE `pembayaran_riwayat`
  MODIFY `id_pembayaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembelian_bahan`
--
ALTER TABLE `pembelian_bahan`
  MODIFY `id_pembelian` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pembelian_bahan_detail`
--
ALTER TABLE `pembelian_bahan_detail`
  MODIFY `id_detail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pengambilan`
--
ALTER TABLE `pengambilan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_belanja`
--
ALTER TABLE `platform_belanja`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `post_kategori`
--
ALTER TABLE `post_kategori`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT for table `produk_finishing`
--
ALTER TABLE `produk_finishing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_request_details`
--
ALTER TABLE `purchase_request_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qc_inspeksi`
--
ALTER TABLE `qc_inspeksi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rework`
--
ALTER TABLE `rework`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id_satuan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `setting_harga`
--
ALTER TABLE `setting_harga`
  MODIFY `id_harga` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2106;

--
-- AUTO_INCREMENT for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaksi_detail_bahan_pakai`
--
ALTER TABLE `transaksi_detail_bahan_pakai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi_detail_finishing`
--
ALTER TABLE `transaksi_detail_finishing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `waste_log`
--
ALTER TABLE `waste_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `worksheet`
--
ALTER TABLE `worksheet`
  MODIFY `id_worksheet` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `design_proofs`
--
ALTER TABLE `design_proofs`
  ADD CONSTRAINT `design_proofs_id_detail_foreign` FOREIGN KEY (`id_detail`) REFERENCES `transaksi_detail` (`id_detail`) ON DELETE CASCADE;

--
-- Constraints for table `kedatangan_bahan`
--
ALTER TABLE `kedatangan_bahan`
  ADD CONSTRAINT `kedatangan_bahan_id_purchase_order_foreign` FOREIGN KEY (`id_purchase_order`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_berkas`
--
ALTER TABLE `order_berkas`
  ADD CONSTRAINT `order_berkas_id_detail_foreign` FOREIGN KEY (`id_detail`) REFERENCES `transaksi_detail` (`id_detail`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_berkas_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE;

--
-- Constraints for table `pembayaran_hutang`
--
ALTER TABLE `pembayaran_hutang`
  ADD CONSTRAINT `pembayaran_hutang_id_pembelian_foreign` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian_bahan` (`id_pembelian`) ON DELETE CASCADE;

--
-- Constraints for table `pengambilan`
--
ALTER TABLE `pengambilan`
  ADD CONSTRAINT `pengambilan_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_id_purchase_request_foreign` FOREIGN KEY (`id_purchase_request`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_id_supplier_foreign` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `purchase_orders_id_user_pembuat_foreign` FOREIGN KEY (`id_user_pembuat`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD CONSTRAINT `purchase_order_details_id_bahan_foreign` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_details_id_purchase_order_foreign` FOREIGN KEY (`id_purchase_order`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_id_user_pembuat_foreign` FOREIGN KEY (`id_user_pembuat`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_request_details`
--
ALTER TABLE `purchase_request_details`
  ADD CONSTRAINT `purchase_request_details_id_bahan_foreign` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_details_id_purchase_request_foreign` FOREIGN KEY (`id_purchase_request`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_inspeksi`
--
ALTER TABLE `qc_inspeksi`
  ADD CONSTRAINT `qc_inspeksi_id_detail_foreign` FOREIGN KEY (`id_detail`) REFERENCES `transaksi_detail` (`id_detail`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_id_golongan_foreign` FOREIGN KEY (`id_golongan`) REFERENCES `golongan_konsumen` (`id_golongan`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_id_konsumen_foreign` FOREIGN KEY (`id_konsumen`) REFERENCES `konsumen` (`id_konsumen`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_id_user_pembuat_foreign` FOREIGN KEY (`id_user_pembuat`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_id_quotation_foreign` FOREIGN KEY (`id_quotation`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rework`
--
ALTER TABLE `rework`
  ADD CONSTRAINT `rework_id_detail_foreign` FOREIGN KEY (`id_detail`) REFERENCES `transaksi_detail` (`id_detail`) ON DELETE CASCADE,
  ADD CONSTRAINT `rework_id_qc_inspeksi_foreign` FOREIGN KEY (`id_qc_inspeksi`) REFERENCES `qc_inspeksi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD CONSTRAINT `stock_opnames_id_gudang_foreign` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id_gudang`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_opnames_id_user_pembuat_foreign` FOREIGN KEY (`id_user_pembuat`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  ADD CONSTRAINT `stock_opname_details_id_bahan_foreign` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_opname_details_id_stock_opname_foreign` FOREIGN KEY (`id_stock_opname`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waste_log`
--
ALTER TABLE `waste_log`
  ADD CONSTRAINT `waste_log_id_bahan_foreign` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  ADD CONSTRAINT `waste_log_id_detail_foreign` FOREIGN KEY (`id_detail`) REFERENCES `transaksi_detail` (`id_detail`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
