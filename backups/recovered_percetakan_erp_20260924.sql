-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: percetakan_erp
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounting_periods`
--

DROP TABLE IF EXISTS `accounting_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tahun` smallint(5) unsigned NOT NULL,
  `bulan` tinyint(3) unsigned NOT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_periods_tahun_bulan_unique` (`tahun`,`bulan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_periods`
--

LOCK TABLES `accounting_periods` WRITE;
/*!40000 ALTER TABLE `accounting_periods` DISABLE KEYS */;
INSERT INTO `accounting_periods` VALUES (1,2026,9,'Open',NULL,NULL,NULL,'2026-09-23 15:26:07','2026-09-23 15:26:29');
/*!40000 ALTER TABLE `accounting_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;

--
-- Table structure for table `approval_requests`
--

DROP TABLE IF EXISTS `approval_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipe_aksi` varchar(50) NOT NULL,
  `referensi_type` varchar(50) DEFAULT NULL,
  `referensi_id` bigint(20) unsigned DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ringkasan` varchar(255) DEFAULT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `alasan_pengajuan` varchar(500) DEFAULT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `decided_by` bigint(20) unsigned DEFAULT NULL,
  `catatan_keputusan` varchar(500) DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_requests_tipe_aksi_status_index` (`tipe_aksi`,`status`),
  KEY `approval_requests_referensi_type_referensi_id_index` (`referensi_type`,`referensi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_requests`
--

LOCK TABLES `approval_requests` WRITE;
/*!40000 ALTER TABLE `approval_requests` DISABLE KEYS */;
INSERT INTO `approval_requests` VALUES (2,'stock_opname','stock_opnames',2,'[]','Stock Opname SO-20260923-002 (2 bahan selisih)',1,'Hasil hitung fisik 23 Sep 2026','Disetujui',1,NULL,'2026-09-23 16:23:51','2026-09-23 16:22:28','2026-09-23 16:23:51');
/*!40000 ALTER TABLE `approval_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_label` varchar(100) DEFAULT NULL,
  `event` varchar(20) NOT NULL,
  `auditable_type` varchar(100) NOT NULL,
  `auditable_id` bigint(20) unsigned DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `url` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,15,'_browsercheck','created','App\\Models\\AccountingPeriod',1,NULL,NULL,'{\"tahun\":2026,\"bulan\":9,\"status\":\"Closed\",\"closed_by\":15,\"closed_at\":\"2026-09-23 22:26:07\",\"catatan\":null,\"updated_at\":\"2026-09-23 22:26:07\",\"created_at\":\"2026-09-23 22:26:07\",\"id\":1}','http://localhost:8765/accounting-period/tutup','127.0.0.1','2026-09-23 15:26:07'),(2,NULL,NULL,'updated','App\\Models\\AccountingPeriod',1,NULL,'{\"status\":\"Closed\",\"closed_by\":15,\"closed_at\":\"2026-09-23T15:26:07.000000Z\",\"updated_at\":\"2026-09-23T15:26:07.000000Z\"}','{\"status\":\"Open\",\"closed_by\":null,\"closed_at\":null,\"updated_at\":\"2026-09-23 22:26:29\"}','http://localhost','127.0.0.1','2026-09-23 15:26:29'),(8,1,'admin','created','App\\Models\\StockOpname',2,NULL,NULL,'{\"nomor\":\"SO-20260923-002\",\"id_gudang\":\"1\",\"tanggal\":\"2026-09-23 00:00:00\",\"status\":\"Draft\",\"catatan\":\"Opname manual\",\"id_user_pembuat\":1,\"updated_at\":\"2026-09-23 23:21:02\",\"created_at\":\"2026-09-23 23:21:02\",\"id\":2}','http://localhost/percetakan-app/public/stock-opname','::1','2026-09-23 16:21:02'),(9,1,'admin','created','App\\Models\\ApprovalRequest',2,NULL,NULL,'{\"tipe_aksi\":\"stock_opname\",\"referensi_type\":\"stock_opnames\",\"referensi_id\":2,\"payload\":\"[]\",\"ringkasan\":\"Stock Opname SO-20260923-002 (2 bahan selisih)\",\"requested_by\":1,\"alasan_pengajuan\":\"Hasil hitung fisik 23 Sep 2026\",\"status\":\"Menunggu\",\"updated_at\":\"2026-09-23 23:22:28\",\"created_at\":\"2026-09-23 23:22:28\",\"id\":2}','http://localhost/percetakan-app/public/stock-opname/2/ajukan','::1','2026-09-23 16:22:28'),(10,1,'admin','updated','App\\Models\\StockOpname',2,NULL,'{\"status\":\"Draft\",\"updated_at\":\"2026-09-23T16:21:02.000000Z\"}','{\"status\":\"Diajukan\",\"updated_at\":\"2026-09-23 23:22:28\"}','http://localhost/percetakan-app/public/stock-opname/2/ajukan','::1','2026-09-23 16:22:28'),(11,1,'admin','updated','App\\Models\\StockOpname',2,NULL,'{\"status\":\"Diajukan\",\"updated_at\":\"2026-09-23T16:22:28.000000Z\"}','{\"status\":\"Disetujui\",\"updated_at\":\"2026-09-23 23:23:51\"}','http://localhost/percetakan-app/public/approval/2/approve','::1','2026-09-23 16:23:51'),(12,1,'admin','updated','App\\Models\\ApprovalRequest',2,NULL,'{\"status\":\"Menunggu\",\"decided_by\":null,\"decided_at\":null,\"updated_at\":\"2026-09-23T16:22:28.000000Z\"}','{\"status\":\"Disetujui\",\"decided_by\":1,\"decided_at\":\"2026-09-23 23:23:51\",\"updated_at\":\"2026-09-23 23:23:51\"}','http://localhost/percetakan-app/public/approval/2/approve','::1','2026-09-23 16:23:51');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipe` enum('daily','weekly','monthly','manual') NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `ukuran_bytes` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Sukses','Gagal') NOT NULL DEFAULT 'Sukses',
  `pesan_error` text DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_logs_tipe_created_at_index` (`tipe`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bag_akun`
--

DROP TABLE IF EXISTS `bag_akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bag_akun` (
  `id_akun` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_akun` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_akun` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_akun` enum('Aset','Kewajiban','Modal','Pendapatan','Beban') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `saldo_normal` enum('Debet','Kredit') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_akun`),
  UNIQUE KEY `bag_akun_kode_akun_unique` (`kode_akun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bag_akun`
--

LOCK TABLES `bag_akun` WRITE;
/*!40000 ALTER TABLE `bag_akun` DISABLE KEYS */;
INSERT INTO `bag_akun` VALUES (1,'1000','Kas','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(2,'1010','Bank','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(3,'1100','Piutang Usaha','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(4,'1200','Persediaan Bahan Baku','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(5,'2000','Hutang Usaha','Kewajiban','Kredit',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(6,'3000','Modal','Modal','Kredit',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(7,'4000','Pendapatan Penjualan','Pendapatan','Kredit',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(8,'5000','Harga Pokok Penjualan','Beban','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(9,'2100','Hutang Deposit Konsumen','Kewajiban','Kredit',1,'2026-09-10 08:28:38','2026-09-10 08:28:38'),(10,'4100','Pendapatan Jasa','Pendapatan','Kredit',1,'2026-09-10 08:28:38','2026-09-10 08:28:38'),(12,'4200','Pendapatan Lain-lain','Pendapatan','Kredit',1,'2026-09-11 17:42:29','2026-09-11 17:42:29'),(13,'5100','Biaya Lain-lain','Beban','Debet',1,'2026-09-11 17:42:29','2026-09-11 17:42:29'),(14,'5110','Beban Administrasi & Umum','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(15,'5120','Beban BPJS','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(16,'5130','Beban Internet','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(17,'5140','Beban Listrik & Telepon','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(18,'5150','Beban PDAM','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(19,'5160','Beban Perlengkapan Kantor','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(20,'5170','Beban Perlengkapan Toko','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(21,'5180','Biaya Marketing','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(22,'5190','Biaya Ongkos Kirim & Ekspedisi','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39'),(23,'5200','Biaya Operasional Kantor','Beban','Debet',1,'2026-09-18 11:39:39','2026-09-18 11:39:39');
/*!40000 ALTER TABLE `bag_akun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bahan_baku`
--

DROP TABLE IF EXISTS `bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bahan_baku` (
  `id_bahan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_bahan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_bahan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_mesin` bigint(20) unsigned NOT NULL,
  `id_kategori` bigint(20) unsigned NOT NULL,
  `id_merk` bigint(20) unsigned DEFAULT NULL,
  `id_jenis_bahan` bigint(20) unsigned DEFAULT NULL,
  `stok` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `stok_minimal` decimal(15,3) NOT NULL DEFAULT 0.000,
  `id_satuan_stok` bigint(20) unsigned DEFAULT NULL,
  `id_satuan_hitung` bigint(20) unsigned DEFAULT NULL,
  `nilai_konversi` decimal(15,3) NOT NULL DEFAULT 1.000,
  `lebar_roll` decimal(10,3) DEFAULT NULL,
  `panjang_roll` decimal(10,3) DEFAULT NULL,
  `harga_rata_rata` decimal(15,2) NOT NULL DEFAULT 0.00,
  `satuan_stok` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_bahan`),
  UNIQUE KEY `bahan_baku_kode_bahan_unique` (`kode_bahan`),
  KEY `bahan_baku_id_jenis_bahan_foreign` (`id_jenis_bahan`),
  KEY `bahan_baku_id_kategori_foreign` (`id_kategori`),
  KEY `bahan_baku_id_merk_foreign` (`id_merk`),
  KEY `bahan_baku_id_mesin_foreign` (`id_mesin`),
  KEY `bahan_baku_id_satuan_hitung_foreign` (`id_satuan_hitung`),
  KEY `bahan_baku_id_satuan_stok_foreign` (`id_satuan_stok`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bahan_baku`
--

LOCK TABLES `bahan_baku` WRITE;
/*!40000 ALTER TABLE `bahan_baku` DISABLE KEYS */;
INSERT INTO `bahan_baku` VALUES (1,'FLS280-320','FRONTLITE 280gr 3.2m',2,3,2,2,3.0000,1.000,17,21,224.000,3.200,70.000,1384000.00,'roll','aktif','2026-09-11 16:25:20',NULL),(2,'TC','TC Textile Import',4,5,2,2,1.0000,1.000,17,21,271.200,1.200,226.000,0.00,'roll','aktif','2026-09-10 21:53:06',NULL),(3,'AC260','AC 260gr',3,4,2,2,237.0000,0.000,20,18,500.000,NULL,NULL,281057.64,'rim','aktif','2026-09-11 16:24:28',NULL),(4,'AC210','AC 210gr',3,4,NULL,NULL,250.0000,0.000,20,18,500.000,NULL,NULL,265000.00,'rim','aktif','2026-09-10 15:22:30',NULL),(5,'SB','SUBLIME PAPER',6,6,NULL,NULL,5.0000,0.000,17,21,1.000,NULL,NULL,0.00,'Roll','nonaktif','2026-09-11 18:21:05',NULL),(6,'DTF','PET FILM DTF',5,7,2,2,2.0000,1.000,17,21,62.000,NULL,NULL,640000.00,'roll','aktif','2026-09-11 16:25:20',NULL),(7,'ST-CML','STIKER CAMEL',1,2,2,2,2.0000,1.000,17,21,77.500,1.550,50.000,0.00,'roll','aktif','2026-09-11 16:18:27',NULL),(8,'ST-INF','STIKER INFLEX',1,2,2,2,2.0000,1.000,17,21,77.500,1.550,50.000,0.00,'roll','aktif','2026-09-11 16:18:40',NULL),(9,'ST-EXI','STIKER EXILO',1,2,2,2,2.0000,1.000,17,21,76.500,1.530,50.000,0.00,'roll','aktif','2026-09-11 16:18:48',NULL),(10,'CNV','CANVAS',1,2,2,2,2.0000,1.000,17,21,60.000,1.200,50.000,3500000.00,'roll','aktif','2026-09-11 09:10:21',NULL),(11,'PHO','PHOTOPAPER',1,2,2,2,2.0000,1.000,17,21,45.000,0.900,50.000,650000.00,'roll','aktif','2026-09-11 09:50:53',NULL),(12,'BTX','BONTAX',3,4,2,2,245.0000,0.000,20,18,500.000,NULL,NULL,395000.00,'rim','aktif','2026-09-11 09:50:53',NULL),(13,'PPS','PAPER SUBLIME',6,6,2,2,4.0000,1.000,17,14,160.000,1.600,100.000,0.00,'roll','aktif','2026-09-11 17:36:59',NULL),(27,'FLS280-220','FRONTLITE 280gr 2.2m',2,3,2,2,2.0000,1.000,17,21,154.000,2.200,70.000,0.00,'roll','aktif','2026-09-11 16:17:42',NULL),(28,'FLS280-260','FRONTLITE 280gr 2.6m',2,3,2,2,2.0000,1.000,17,21,182.000,2.600,70.000,0.00,'roll','aktif','2026-09-11 16:17:53',NULL),(29,'FLS280-160','FRONTLITE 280gr 1.6m',2,3,2,2,1.0000,1.000,17,21,112.000,1.600,70.000,0.00,'roll','aktif','2026-09-11 09:57:19',NULL),(30,'FLS280-110','FRONTLITE 280gr 1.1m',2,3,2,2,2.0000,1.000,17,21,77.000,1.100,70.000,0.00,'roll','aktif','2026-09-11 16:17:31',NULL),(31,'STT','STIKER TRANSPARAN',1,2,2,2,1.0000,1.000,17,14,77.500,1.550,50.000,0.00,'roll','aktif','2026-09-12 16:48:52',NULL),(32,'ALB','ALBATROS',1,2,2,2,1.0000,1.000,17,21,45.000,0.900,50.000,0.00,'roll','aktif','2026-09-12 16:49:50',NULL),(33,'STOV','STIKER ONEWAY VISION',2,3,2,2,1.0000,1.000,17,21,63.500,1.270,50.000,0.00,'roll','aktif','2026-09-12 16:53:21',NULL),(34,'AP120','AP120',3,4,2,2,320.0000,0.000,20,18,1.000,NULL,NULL,0.00,'rim','aktif','2026-09-12 16:54:45',NULL),(35,'AP150','AP150',3,4,2,2,315.0000,0.000,17,18,1.000,NULL,NULL,0.00,'roll','aktif','2026-09-12 16:55:09',NULL),(36,'A3VS','A3 VINIL SUSU',3,4,2,2,750.0000,0.000,20,18,1.000,NULL,NULL,0.00,'rim','aktif','2026-09-12 16:55:48',NULL),(37,'A3TR','A3  TRANSPARAN',3,4,2,2,400.0000,0.000,20,18,1.000,NULL,NULL,0.00,'rim','aktif','2026-09-12 16:56:10',NULL),(38,'A3LN','A3 LINEN',3,4,2,2,250.0000,0.000,20,18,1.000,NULL,NULL,0.00,'rim','aktif','2026-09-12 16:57:04',NULL),(39,'A3HL','A3 HOLOGRAM',3,4,2,2,350.0000,0.000,20,18,1.000,NULL,NULL,0.00,'rim','aktif','2026-09-12 16:57:27',NULL);
/*!40000 ALTER TABLE `bahan_baku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_perusahaan`
--

DROP TABLE IF EXISTS `bank_perusahaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_perusahaan` (
  `id_bank` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_bank` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_rekening` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `atas_nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_bank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_perusahaan`
--

LOCK TABLES `bank_perusahaan` WRITE;
/*!40000 ALTER TABLE `bank_perusahaan` DISABLE KEYS */;
INSERT INTO `bank_perusahaan` VALUES (1,'BTN','00046901610002321','Robi Wahyudik');
/*!40000 ALTER TABLE `bank_perusahaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bom_detail`
--

DROP TABLE IF EXISTS `bom_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bom_detail` (
  `id_bom` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `cara_hitung_pemakaian` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_bom`),
  KEY `bom_detail_id_bahan_foreign` (`id_bahan`),
  KEY `bom_detail_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bom_detail`
--

LOCK TABLES `bom_detail` WRITE;
/*!40000 ALTER TABLE `bom_detail` DISABLE KEYS */;
INSERT INTO `bom_detail` VALUES (20,3,13,'(P*L)/10000','2026-09-11 11:23:14','2026-09-11 11:23:14'),(28,1,1,'(P*L)/10000','2026-09-11 13:27:14','2026-09-11 13:27:14'),(29,2,2,'(P*L)/10000','2026-09-11 13:27:25','2026-09-11 13:27:25'),(30,5,4,'Q','2026-09-11 13:42:10','2026-09-11 13:42:10'),(31,4,3,'Q','2026-09-11 13:42:17','2026-09-11 13:42:17'),(32,18,27,'(P*L)/10000','2026-09-11 13:43:20','2026-09-11 13:43:20'),(33,19,28,'(P*L)/10000','2026-09-11 13:43:42','2026-09-11 13:43:42'),(34,20,29,'(P*L)/10000','2026-09-11 13:43:58','2026-09-11 13:43:58'),(36,22,6,'(P*L)/10000','2026-09-12 14:13:16','2026-09-12 14:13:16'),(37,23,12,'Q','2026-09-12 14:14:09','2026-09-12 14:14:09'),(39,9,8,'(P*L)/10000','2026-09-12 16:38:57','2026-09-12 16:38:57'),(40,8,9,'(P*L)/10000','2026-09-12 16:39:21','2026-09-12 16:39:21'),(41,7,9,'(P*L)/10000','2026-09-12 16:39:49','2026-09-12 16:39:49'),(42,26,6,'Q','2026-09-12 16:43:41','2026-09-12 16:43:41'),(43,27,6,'Q','2026-09-12 16:44:04','2026-09-12 16:44:04'),(44,28,6,'Q','2026-09-12 16:44:26','2026-09-12 16:44:26'),(45,29,6,'Q','2026-09-12 16:44:53','2026-09-12 16:44:53');
/*!40000 ALTER TABLE `bom_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bom_finishing`
--

DROP TABLE IF EXISTS `bom_finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bom_finishing` (
  `id_bom_finishing` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `id_finishing` bigint(20) unsigned NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 1.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_bom_finishing`),
  KEY `bom_finishing_id_finishing_foreign` (`id_finishing`),
  KEY `bom_finishing_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bom_finishing`
--

LOCK TABLES `bom_finishing` WRITE;
/*!40000 ALTER TABLE `bom_finishing` DISABLE KEYS */;
/*!40000 ALTER TABLE `bom_finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-app_settings_current','O:21:\"App\\Models\\AppSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"app_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:1;s:13:\"nama_aplikasi\";s:13:\"ORION GRAFIKA\";s:6:\"alamat\";s:59:\"Jalan Jaksa Agung Suprapto No. 48 Pliyang - Madura, Sampang\";s:7:\"telepon\";s:12:\"082334075550\";s:3:\"fax\";s:1:\"-\";s:5:\"email\";s:22:\"grafikaorion@gmail.com\";s:7:\"website\";s:23:\"http://oriongrafika.com\";s:16:\"libur_kas_minggu\";i:1;s:11:\"format_nota\";s:7:\"thermal\";s:17:\"nomor_nota_prefix\";s:3:\"ORG\";s:16:\"nota_footer_text\";s:103:\"Pembayaran ke Nomor Rekening : BCA : 896 0407 068  |  BRI : 01480 1001 350561\r\nAn. Khaironi Firdiansyah\";s:15:\"pembulatan_nota\";s:4:\"1000\";s:9:\"logo_path\";s:57:\"company_logo/TQuRE7SCpMfI5Ryfac28qypqAk6wVD3T4phfJiXl.png\";s:10:\"created_at\";s:19:\"2026-09-12 19:23:14\";s:10:\"updated_at\";s:19:\"2026-09-21 11:06:22\";}s:11:\"\0*\0original\";a:15:{s:2:\"id\";i:1;s:13:\"nama_aplikasi\";s:13:\"ORION GRAFIKA\";s:6:\"alamat\";s:59:\"Jalan Jaksa Agung Suprapto No. 48 Pliyang - Madura, Sampang\";s:7:\"telepon\";s:12:\"082334075550\";s:3:\"fax\";s:1:\"-\";s:5:\"email\";s:22:\"grafikaorion@gmail.com\";s:7:\"website\";s:23:\"http://oriongrafika.com\";s:16:\"libur_kas_minggu\";i:1;s:11:\"format_nota\";s:7:\"thermal\";s:17:\"nomor_nota_prefix\";s:3:\"ORG\";s:16:\"nota_footer_text\";s:103:\"Pembayaran ke Nomor Rekening : BCA : 896 0407 068  |  BRI : 01480 1001 350561\r\nAn. Khaironi Firdiansyah\";s:15:\"pembulatan_nota\";s:4:\"1000\";s:9:\"logo_path\";s:57:\"company_logo/TQuRE7SCpMfI5Ryfac28qypqAk6wVD3T4phfJiXl.png\";s:10:\"created_at\";s:19:\"2026-09-12 19:23:14\";s:10:\"updated_at\";s:19:\"2026-09-21 11:06:22\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:16:\"libur_kas_minggu\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:12:{i:0;s:13:\"nama_aplikasi\";i:1;s:6:\"alamat\";i:2;s:7:\"telepon\";i:3;s:3:\"fax\";i:4;s:5:\"email\";i:5;s:7:\"website\";i:6;s:9:\"logo_path\";i:7;s:16:\"libur_kas_minggu\";i:8;s:11:\"format_nota\";i:9;s:17:\"nomor_nota_prefix\";i:10;s:16:\"nota_footer_text\";i:11;s:15:\"pembulatan_nota\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2105323582);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_number_counters`
--

DROP TABLE IF EXISTS `document_number_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_number_counters` (
  `kode_dokumen` varchar(20) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `counter` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`kode_dokumen`,`periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_number_counters`
--

LOCK TABLES `document_number_counters` WRITE;
/*!40000 ALTER TABLE `document_number_counters` DISABLE KEYS */;
INSERT INTO `document_number_counters` VALUES ('SO','20260923',2,'2026-09-23 15:56:17','2026-09-23 16:21:02');
/*!40000 ALTER TABLE `document_number_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finishing`
--

DROP TABLE IF EXISTS `finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finishing` (
  `id_finishing` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_finishing` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga_default` decimal(15,2) DEFAULT 0.00,
  `id_satuan` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_kategori` bigint(20) unsigned DEFAULT NULL,
  `id_mesin` bigint(20) unsigned DEFAULT NULL,
  `metode_hitung` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_finishing`),
  KEY `fk_finishing_mesin` (`id_mesin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finishing`
--

LOCK TABLES `finishing` WRITE;
/*!40000 ALTER TABLE `finishing` DISABLE KEYS */;
INSERT INTO `finishing` VALUES (1,'Kisscut',20000.00,NULL,'2026-03-07 16:33:27','2026-09-17 12:24:00',2,1,'luas','Harga berlaku per meter persegi'),(5,'Hot Laminasi Doff/Glossy',2000.00,NULL,'2026-09-11 07:39:08','2026-09-18 12:41:34',4,3,'quantity','Menggunakan range harga'),(6,'Mata Ayam',700.00,NULL,'2026-09-11 13:46:04','2026-09-17 12:24:09',3,2,'quantity','Mata ayam (pcs)'),(7,'Press Hidrolik',3000.00,NULL,'2026-09-12 16:45:44','2026-09-17 12:27:08',7,5,'quantity','Jasa Press dengan mesin  Hidrolik'),(8,'Jahit Keliling',1500.00,NULL,'2026-09-17 12:42:27','2026-09-18 06:33:51',5,4,'keliling','Jahit tepi produk, dihitung dari keliling (2 x (P+L))'),(9,'Pemasangan Tali',500.00,NULL,'2026-09-17 12:42:27','2026-09-18 06:33:35',5,4,'quantity','Penambahan jika tali lebih dari 6 pcs');
/*!40000 ALTER TABLE `finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finishing_harga`
--

DROP TABLE IF EXISTS `finishing_harga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finishing_harga` (
  `id_finishing_harga` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_finishing` bigint(20) unsigned NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_finishing_harga`),
  KEY `finishing_harga_id_finishing_foreign` (`id_finishing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finishing_harga`
--

LOCK TABLES `finishing_harga` WRITE;
/*!40000 ALTER TABLE `finishing_harga` DISABLE KEYS */;
INSERT INTO `finishing_harga` VALUES (8,5,1.00,20.00,2000.00,'2026-09-11 13:45:12','2026-09-11 13:45:12'),(9,5,21.00,50.00,1750.00,'2026-09-11 13:45:19','2026-09-11 13:45:19'),(10,5,51.00,100.00,1500.00,'2026-09-11 13:45:39','2026-09-11 13:45:39');
/*!40000 ALTER TABLE `finishing_harga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `golongan_konsumen`
--

DROP TABLE IF EXISTS `golongan_konsumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golongan_konsumen` (
  `id_golongan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_golongan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_golongan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `golongan_konsumen`
--

LOCK TABLES `golongan_konsumen` WRITE;
/*!40000 ALTER TABLE `golongan_konsumen` DISABLE KEYS */;
INSERT INTO `golongan_konsumen` VALUES (1,'Retail','Harga umum'),(2,'Reseller','Harga untuk member tetap'),(3,'Member Platinum','Harga khusus pelanggan dari owner pribadi'),(4,'Member Foto','Khusus member cetak foto saja'),(6,'Sponsor','Persetujuan dari Manajemen untuk menggunakan harga dengan golongan sponsor'),(7,'Asosiasi','Khusus Asosiasi percetakan madura');
/*!40000 ALTER TABLE `golongan_konsumen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gudang`
--

DROP TABLE IF EXISTS `gudang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gudang` (
  `id_gudang` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_gudang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `penanggung_jawab` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telepon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_gudang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gudang`
--

LOCK TABLES `gudang` WRITE;
/*!40000 ALTER TABLE `gudang` DISABLE KEYS */;
INSERT INTO `gudang` VALUES (1,'Gudang Utama',NULL,NULL,NULL);
/*!40000 ALTER TABLE `gudang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `harga_promo`
--

DROP TABLE IF EXISTS `harga_promo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harga_promo` (
  `id_promo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `harga_promo` decimal(15,2) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `status_promo` enum('aktif','non-aktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_promo`),
  KEY `harga_promo_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `harga_promo`
--

LOCK TABLES `harga_promo` WRITE;
/*!40000 ALTER TABLE `harga_promo` DISABLE KEYS */;
INSERT INTO `harga_promo` VALUES (2,1,13000.00,'2026-09-10','2026-09-18','non-aktif'),(3,1,10000.00,'2026-09-01','2026-09-30','non-aktif');
/*!40000 ALTER TABLE `harga_promo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jasa`
--

DROP TABLE IF EXISTS `jasa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jasa` (
  `id_jasa` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_jasa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_satuan` bigint(20) unsigned DEFAULT NULL,
  `harga_default` decimal(15,2) DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jasa`),
  KEY `jasa_id_satuan_foreign` (`id_satuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jasa`
--

LOCK TABLES `jasa` WRITE;
/*!40000 ALTER TABLE `jasa` DISABLE KEYS */;
INSERT INTO `jasa` VALUES (3,'Pasang Stiker',21,50000.00,NULL,'2026-09-10 09:00:16','2026-09-12 17:01:20');
/*!40000 ALTER TABLE `jasa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jasa_harga`
--

DROP TABLE IF EXISTS `jasa_harga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jasa_harga` (
  `id_jasa_harga` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_jasa` bigint(20) unsigned NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jasa_harga`),
  KEY `jasa_harga_id_jasa_foreign` (`id_jasa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jasa_harga`
--

LOCK TABLES `jasa_harga` WRITE;
/*!40000 ALTER TABLE `jasa_harga` DISABLE KEYS */;
/*!40000 ALTER TABLE `jasa_harga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jenis_bahan`
--

DROP TABLE IF EXISTS `jenis_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_bahan` (
  `id_jenis_bahan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jenis_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_bahan`
--

LOCK TABLES `jenis_bahan` WRITE;
/*!40000 ALTER TABLE `jenis_bahan` DISABLE KEYS */;
INSERT INTO `jenis_bahan` VALUES (2,'Universal',NULL,'2026-09-10 07:53:57','2026-09-10 07:53:57');
/*!40000 ALTER TABLE `jenis_bahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_categories`
--

DROP TABLE IF EXISTS `jersey_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_categories`
--

LOCK TABLES `jersey_categories` WRITE;
/*!40000 ALTER TABLE `jersey_categories` DISABLE KEYS */;
INSERT INTO `jersey_categories` VALUES (1,'Jersey Futsal',0,'2026-09-12 12:27:40','2026-09-12 13:40:06'),(2,'Jersey Bola',0,'2026-09-12 12:27:40','2026-09-12 13:14:21'),(3,'Jersey Printing',1,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(4,'Jersey Esports',0,'2026-09-12 12:27:40','2026-09-12 13:14:22'),(5,'Jersey Custom',0,'2026-09-12 12:27:40','2026-09-12 13:14:20'),(6,'Sportswear',0,'2026-09-16 12:45:47','2026-09-16 14:02:44'),(7,'Event/Gathering',0,'2026-09-16 12:45:56','2026-09-16 14:02:45'),(8,'Merchandise',0,'2026-09-16 12:46:03','2026-09-16 14:02:44');
/*!40000 ALTER TABLE `jersey_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_collar_types`
--

DROP TABLE IF EXISTS `jersey_collar_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_collar_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_collar_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_collar_types`
--

LOCK TABLES `jersey_collar_types` WRITE;
/*!40000 ALTER TABLE `jersey_collar_types` DISABLE KEYS */;

--
-- Table structure for table `jersey_components`
--

DROP TABLE IF EXISTS `jersey_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `jersey_product_type_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_category_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_components_name_unique` (`name`),
  KEY `jersey_components_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_components_jersey_product_type_id_foreign` (`jersey_product_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_components`
--

LOCK TABLES `jersey_components` WRITE;
/*!40000 ALTER TABLE `jersey_components` DISABLE KEYS */;
INSERT INTO `jersey_components` VALUES (1,'Body',1,3,1,0,'2026-09-12 13:58:37','2026-09-16 14:14:39'),(2,'Lengan',NULL,NULL,2,0,'2026-09-12 13:58:37','2026-09-18 16:55:57'),(3,'Rib',NULL,NULL,3,1,'2026-09-12 13:58:37','2026-09-12 13:58:37'),(4,'Celana',2,3,4,1,'2026-09-12 13:58:37','2026-09-16 10:29:47'),(5,'Lengan panjang',1,6,0,0,'2026-09-12 14:29:38','2026-09-16 14:15:50'),(6,'Rib Wangki',NULL,NULL,0,0,'2026-09-12 15:03:24','2026-09-18 16:55:46');
/*!40000 ALTER TABLE `jersey_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_designers`
--

DROP TABLE IF EXISTS `jersey_designers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_designers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_designers_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_designers`
--

LOCK TABLES `jersey_designers` WRITE;
/*!40000 ALTER TABLE `jersey_designers` DISABLE KEYS */;
INSERT INTO `jersey_designers` VALUES (1,'Rahman',1,'2026-09-12 13:39:37','2026-09-12 13:39:37'),(2,'Rehan',1,'2026-09-12 13:39:40','2026-09-12 13:39:40');
/*!40000 ALTER TABLE `jersey_designers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_materials`
--

DROP TABLE IF EXISTS `jersey_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_materials_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_materials`
--

LOCK TABLES `jersey_materials` WRITE;
/*!40000 ALTER TABLE `jersey_materials` DISABLE KEYS */;
INSERT INTO `jersey_materials` VALUES (1,'Brazil',1,'2026-09-12 14:24:04','2026-09-12 14:24:04'),(2,'Milano',1,'2026-09-12 14:24:10','2026-09-12 14:24:10'),(3,'Nike',1,'2026-09-12 14:24:15','2026-09-12 14:24:15'),(4,'Billabong',1,'2026-09-12 14:24:31','2026-09-12 14:24:31'),(5,'Cotton Combed',1,'2026-09-12 14:24:40','2026-09-12 14:24:40');
/*!40000 ALTER TABLE `jersey_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_order_items`
--

DROP TABLE IF EXISTS `jersey_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_line_id` bigint(20) unsigned NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `player_number` varchar(255) DEFAULT NULL,
  `size` enum('S','M','L','XL','2XL','3XL','4XL','5XL') NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `sleeve` varchar(50) DEFAULT NULL,
  `jersey_sleeve_type_id` bigint(20) unsigned DEFAULT NULL,
  `collar` varchar(255) DEFAULT NULL,
  `jersey_collar_type_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_order_items_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  KEY `jersey_order_items_jersey_order_line_id_foreign` (`jersey_order_line_id`),
  KEY `jersey_order_items_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_items`
--

LOCK TABLES `jersey_order_items` WRITE;
/*!40000 ALTER TABLE `jersey_order_items` DISABLE KEYS */;
INSERT INTO `jersey_order_items` VALUES (1,2,'Dimas','9','M','L','Pendek',1,NULL,NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(2,2,'Doni','4','M','L','Pendek',1,NULL,NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(3,2,'Rudi','6','L','L','Pendek',1,NULL,NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(4,2,'Kamal','7','L','L','Pendek',1,NULL,NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(5,2,'Rendi','30','3XL','L','Pendek',1,NULL,NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04');
/*!40000 ALTER TABLE `jersey_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_order_line_materials`
--

DROP TABLE IF EXISTS `jersey_order_line_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_order_line_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_line_id` bigint(20) unsigned NOT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `warna` varchar(255) DEFAULT NULL,
  `penggunaan_kg` decimal(10,2) DEFAULT NULL,
  `penggunaan_meter` decimal(10,2) DEFAULT NULL,
  `penggunaan_yard` decimal(10,2) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_order_line_materials_jersey_order_line_id_foreign` (`jersey_order_line_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_line_materials`
--

LOCK TABLES `jersey_order_line_materials` WRITE;
/*!40000 ALTER TABLE `jersey_order_line_materials` DISABLE KEYS */;
INSERT INTO `jersey_order_line_materials` VALUES (5,4,'Lengan panjang',NULL,NULL,3.83,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(6,4,'Rib Wangki',NULL,NULL,0.24,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(7,4,'Body',NULL,NULL,5.57,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(8,4,'Lengan',NULL,NULL,1.87,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(9,4,'Rib',NULL,NULL,0.22,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(10,4,'Celana',NULL,NULL,6.97,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(11,5,'Lengan panjang',NULL,NULL,3.83,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(12,5,'Rib Wangki',NULL,NULL,0.24,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(13,5,'Body',NULL,NULL,5.57,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(14,5,'Lengan',NULL,NULL,1.87,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(15,5,'Rib',NULL,NULL,0.22,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(16,5,'Celana',NULL,NULL,6.97,NULL,NULL,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(35,9,'Lengan panjang',NULL,2.64,6.62,NULL,NULL,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(36,9,'Rib Wangki',NULL,0.18,1.56,NULL,NULL,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(37,9,'Body',NULL,3.06,10.41,NULL,NULL,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(38,9,'Lengan',NULL,1.10,3.40,NULL,NULL,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(39,9,'Rib',NULL,0.08,0.43,NULL,NULL,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(40,9,'Celana',NULL,2.27,13.33,NULL,NULL,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(100,20,'Rib Wangki',NULL,0.14,1.68,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(101,20,'Body',NULL,2.46,10.53,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(102,20,'Lengan',NULL,0.90,3.59,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(103,20,'Rib',NULL,0.07,0.42,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(104,20,'Lengan Lengan Pendek',NULL,0.90,3.02,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(105,21,'Rib Wangki',NULL,0.08,0.96,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(106,21,'Lengan',NULL,0.53,2.13,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(107,21,'Rib',NULL,0.04,0.24,NULL,NULL,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(108,22,'Rib Wangki',NULL,0.10,1.20,NULL,NULL,'2026-09-16 14:46:36','2026-09-16 14:46:36'),(109,22,'Lengan',NULL,0.62,2.48,NULL,NULL,'2026-09-16 14:46:36','2026-09-16 14:46:36'),(110,22,'Rib',NULL,0.05,0.30,NULL,NULL,'2026-09-16 14:46:36','2026-09-16 14:46:36'),(111,22,'Lengan Lengan Pendek',NULL,0.62,2.08,NULL,NULL,'2026-09-16 14:46:36','2026-09-16 14:46:36');
/*!40000 ALTER TABLE `jersey_order_line_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_order_lines`
--

DROP TABLE IF EXISTS `jersey_order_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_order_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_id` bigint(20) unsigned NOT NULL,
  `jersey_product_type_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_category_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_material_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_sleeve_type_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_collar_type_id` bigint(20) unsigned DEFAULT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `bahan` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `order_type` enum('quantity_only','player_list','mixed') NOT NULL DEFAULT 'quantity_only',
  `is_susulan` tinyint(1) NOT NULL DEFAULT 0,
  `qty` int(10) unsigned NOT NULL DEFAULT 0,
  `harga_satuan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `size_surcharge_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sleeve_collar_surcharge_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jersey_price_setting_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_order_lines_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_order_lines_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  KEY `jersey_order_lines_jersey_material_id_foreign` (`jersey_material_id`),
  KEY `jersey_order_lines_jersey_order_id_foreign` (`jersey_order_id`),
  KEY `jersey_order_lines_jersey_price_setting_id_foreign` (`jersey_price_setting_id`),
  KEY `jersey_order_lines_jersey_product_type_id_foreign` (`jersey_product_type_id`),
  KEY `jersey_order_lines_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_lines`
--

LOCK TABLES `jersey_order_lines` WRITE;
/*!40000 ALTER TABLE `jersey_order_lines` DISABLE KEYS */;

--
-- Table structure for table `jersey_order_payments`
--

DROP TABLE IF EXISTS `jersey_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_id` bigint(20) unsigned NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `metode_bayar` enum('Tunai','Transfer','QRIS','Lainnya') NOT NULL DEFAULT 'Tunai',
  `bukti_path` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_order_payments_created_by_foreign` (`created_by`),
  KEY `jersey_order_payments_jersey_order_id_foreign` (`jersey_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_payments`
--

LOCK TABLES `jersey_order_payments` WRITE;
/*!40000 ALTER TABLE `jersey_order_payments` DISABLE KEYS */;
INSERT INTO `jersey_order_payments` VALUES (6,3,'2026-09-13',200000.00,'Transfer',NULL,NULL,1,'2026-09-12 17:23:36','2026-09-12 17:23:36',NULL);
/*!40000 ALTER TABLE `jersey_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_order_size_quantities`
--

DROP TABLE IF EXISTS `jersey_order_size_quantities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_order_size_quantities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_line_id` bigint(20) unsigned NOT NULL,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_order_line_size_unique` (`jersey_order_line_id`,`jersey_size_id`),
  KEY `jersey_order_size_quantities_jersey_size_id_foreign` (`jersey_size_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_size_quantities`
--

LOCK TABLES `jersey_order_size_quantities` WRITE;
/*!40000 ALTER TABLE `jersey_order_size_quantities` DISABLE KEYS */;
INSERT INTO `jersey_order_size_quantities` VALUES (1,1,2,4,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(2,1,3,2,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(3,1,5,4,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(4,2,2,2,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(5,2,3,2,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(6,2,6,1,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(9,4,2,3,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(10,4,3,2,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(11,4,5,1,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(12,4,6,1,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(13,5,2,3,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(14,5,3,2,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(15,5,5,1,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(16,5,6,1,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(17,5,8,1,'2026-09-12 15:31:38','2026-09-12 15:31:38'),(22,9,1,2,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(23,9,2,5,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(24,9,3,2,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(25,9,6,4,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(26,9,7,2,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(38,20,2,4,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(39,20,3,9,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(40,20,4,1,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(41,21,3,6,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(42,21,4,2,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(43,22,2,6,'2026-09-16 14:46:36','2026-09-16 14:46:36'),(44,22,3,4,'2026-09-16 14:46:36','2026-09-16 14:46:36');
/*!40000 ALTER TABLE `jersey_order_size_quantities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_orders`
--

DROP TABLE IF EXISTS `jersey_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `id_konsumen` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `team_name` varchar(255) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('baru','proses','selesai','diambil') NOT NULL DEFAULT 'baru',
  `jersey_designer_id` bigint(20) unsigned DEFAULT NULL,
  `designer_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` enum('Belum Bayar','DP','Lunas') NOT NULL DEFAULT 'Belum Bayar',
  `catatan_referensi_transaksi` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_orders_order_number_unique` (`order_number`),
  KEY `jersey_orders_created_by_foreign` (`created_by`),
  KEY `jersey_orders_id_konsumen_foreign` (`id_konsumen`),
  KEY `jersey_orders_jersey_designer_id_foreign` (`jersey_designer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_orders`
--

LOCK TABLES `jersey_orders` WRITE;
/*!40000 ALTER TABLE `jersey_orders` DISABLE KEYS */;
INSERT INTO `jersey_orders` VALUES (1,'JO-20260912-001','2026-09-12',5,'Komar Camplong','08532154542',NULL,'2026-09-17','baru',1,'Rahman',NULL,1650000.00,0.00,0.00,0.00,'Belum Bayar',NULL,1,'2026-09-12 13:51:04','2026-09-12 17:22:37'),(3,'JO-20260912-002','2026-09-12',5,'Komar Camplong','08532154542','Hayabusa','2026-09-18','baru',2,'Rehan',NULL,610000.00,0.00,0.00,200000.00,'DP',NULL,1,'2026-09-12 15:31:38','2026-09-12 17:23:36'),(7,'JO-20260913-001','2026-09-13',4,'Samir','002124878','CV DATA ANGGUNA',NULL,'baru',1,'Rahman',NULL,1260000.00,0.00,0.00,0.00,'Belum Bayar',NULL,1,'2026-09-12 17:30:02','2026-09-12 17:30:02'),(14,'JO-20260916-001','2026-09-16',5,'Komar Camplong','08532154542',NULL,'2026-09-18','baru',1,'Rahman',NULL,1850000.00,0.00,0.00,0.00,'Belum Bayar',NULL,1,'2026-09-16 14:11:59','2026-09-16 14:11:59'),(15,'JO-20260916-002','2026-09-16',6,'Kiki Pangarengan','083225454',NULL,NULL,'baru',1,'Rahman',NULL,800000.00,0.00,0.00,0.00,'Belum Bayar',NULL,NULL,'2026-09-16 14:46:36','2026-09-16 14:46:36');
/*!40000 ALTER TABLE `jersey_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_price_settings`
--

DROP TABLE IF EXISTS `jersey_price_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_price_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_product_type_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_category_id` bigint(20) unsigned DEFAULT NULL,
  `jersey_material_id` bigint(20) unsigned DEFAULT NULL,
  `qty_min` int(10) unsigned NOT NULL,
  `qty_max` int(10) unsigned DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_price_settings_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_price_settings_jersey_material_id_foreign` (`jersey_material_id`),
  KEY `jersey_price_tier_idx` (`jersey_product_type_id`,`jersey_category_id`,`qty_min`,`qty_max`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_price_settings`
--

LOCK TABLES `jersey_price_settings` WRITE;
/*!40000 ALTER TABLE `jersey_price_settings` DISABLE KEYS */;
INSERT INTO `jersey_price_settings` VALUES (5,1,3,1,1,50,80000.00,1,'2026-09-12 15:05:16','2026-09-12 15:05:16'),(6,1,3,1,51,100,75000.00,1,'2026-09-12 15:05:30','2026-09-12 15:05:30'),(7,2,3,1,1,30,30000.00,1,'2026-09-12 15:06:15','2026-09-12 15:06:15'),(8,2,3,1,31,100,27000.00,1,'2026-09-12 15:06:28','2026-09-12 15:06:28'),(9,3,3,1,1,50,65000.00,1,'2026-09-12 16:34:29','2026-09-12 16:34:29');
/*!40000 ALTER TABLE `jersey_price_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_product_types`
--

DROP TABLE IF EXISTS `jersey_product_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_product_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `has_sleeve_collar` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_product_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_product_types`
--

LOCK TABLES `jersey_product_types` WRITE;
/*!40000 ALTER TABLE `jersey_product_types` DISABLE KEYS */;

--
-- Table structure for table `jersey_size_collar_usage`
--

DROP TABLE IF EXISTS `jersey_size_collar_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_size_collar_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `jersey_collar_type_id` bigint(20) unsigned NOT NULL,
  `usage_meter` decimal(8,3) DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_size_collar_unique` (`jersey_size_id`,`jersey_collar_type_id`),
  KEY `jersey_size_collar_usage_jersey_collar_type_id_foreign` (`jersey_collar_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_size_collar_usage`
--

LOCK TABLES `jersey_size_collar_usage` WRITE;
/*!40000 ALTER TABLE `jersey_size_collar_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `jersey_size_collar_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_size_component_usage`
--

DROP TABLE IF EXISTS `jersey_size_component_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_size_component_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `jersey_component_id` bigint(20) unsigned NOT NULL,
  `usage_meter` decimal(8,3) DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_size_component_unique` (`jersey_size_id`,`jersey_component_id`),
  KEY `jersey_size_component_usage_jersey_component_id_foreign` (`jersey_component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_size_component_usage`
--

LOCK TABLES `jersey_size_component_usage` WRITE;
/*!40000 ALTER TABLE `jersey_size_component_usage` DISABLE KEYS */;

--
-- Table structure for table `jersey_size_sleeve_usage`
--

DROP TABLE IF EXISTS `jersey_size_sleeve_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_size_sleeve_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `jersey_sleeve_type_id` bigint(20) unsigned NOT NULL,
  `usage_meter` decimal(8,3) DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_size_sleeve_unique` (`jersey_size_id`,`jersey_sleeve_type_id`),
  KEY `jersey_size_sleeve_usage_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_size_sleeve_usage`
--

LOCK TABLES `jersey_size_sleeve_usage` WRITE;
/*!40000 ALTER TABLE `jersey_size_sleeve_usage` DISABLE KEYS */;
INSERT INTO `jersey_size_sleeve_usage` VALUES (1,1,1,0.180,55.00,'2026-09-16 13:48:07','2026-09-16 13:48:10'),(2,2,1,0.200,60.00,'2026-09-16 13:48:13','2026-09-16 13:48:18'),(3,3,1,0.220,65.00,'2026-09-16 13:48:20','2026-09-16 13:48:22'),(4,4,1,0.240,70.00,'2026-09-16 13:48:24','2026-09-16 13:48:26'),(5,5,1,0.260,80.00,'2026-09-16 13:48:28','2026-09-16 13:48:44'),(6,6,1,0.280,90.00,'2026-09-16 13:48:47','2026-09-16 13:48:48'),(7,7,1,0.310,100.00,'2026-09-16 13:48:53','2026-09-16 13:48:55'),(8,8,1,0.340,110.00,'2026-09-16 13:48:57','2026-09-16 13:49:04'),(9,1,5,0.300,95.00,'2026-09-16 13:49:06','2026-09-16 13:49:09'),(10,2,5,0.330,105.00,'2026-09-16 13:49:10','2026-09-16 13:49:12'),(11,3,5,0.360,125.00,'2026-09-16 13:49:13','2026-09-16 13:49:15'),(12,4,5,0.390,125.00,'2026-09-16 13:49:20','2026-09-16 13:49:22'),(13,5,5,0.430,140.00,'2026-09-16 13:49:24','2026-09-16 13:49:26'),(14,6,5,0.470,155.00,'2026-09-16 13:49:28','2026-09-16 13:49:30'),(15,7,5,0.510,170.00,'2026-09-16 13:49:31','2026-09-16 13:49:33'),(16,8,5,0.550,185.00,'2026-09-16 13:49:35','2026-09-16 13:49:41'),(17,1,2,0.420,130.00,'2026-09-16 13:49:50','2026-09-16 13:49:52'),(18,2,2,0.460,145.00,'2026-09-16 13:49:53','2026-09-16 13:49:55'),(19,3,2,0.500,160.00,'2026-09-16 13:49:56','2026-09-16 13:50:03'),(20,4,2,0.540,175.00,'2026-09-16 13:50:06','2026-09-16 13:50:08'),(21,5,2,0.600,195.00,'2026-09-16 13:50:09','2026-09-16 13:50:11'),(22,6,2,0.650,215.00,'2026-09-16 13:50:14','2026-09-16 13:50:16'),(23,7,2,0.710,235.00,'2026-09-16 13:50:19','2026-09-16 13:50:22'),(24,8,2,0.770,255.00,'2026-09-16 13:50:24','2026-09-16 13:50:30'),(25,1,6,0.450,145.00,'2026-09-16 13:50:55','2026-09-16 13:51:02'),(26,2,6,0.490,160.00,'2026-09-16 13:51:07','2026-09-16 13:51:11'),(27,3,6,0.540,178.00,'2026-09-16 13:51:15','2026-09-16 13:51:18'),(28,8,6,0.820,277.00,'2026-09-16 13:51:35','2026-09-16 13:51:39'),(29,7,6,0.760,257.00,'2026-09-16 13:51:44','2026-09-16 13:51:50'),(30,6,6,0.700,235.00,'2026-09-16 13:51:55','2026-09-16 13:52:00'),(31,5,6,0.640,215.00,'2026-09-16 13:52:04','2026-09-16 13:52:09'),(32,4,6,0.580,193.00,'2026-09-16 13:52:14','2026-09-16 13:52:34');
/*!40000 ALTER TABLE `jersey_size_sleeve_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_sizes`
--

DROP TABLE IF EXISTS `jersey_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_sizes_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_sizes`
--

LOCK TABLES `jersey_sizes` WRITE;
/*!40000 ALTER TABLE `jersey_sizes` DISABLE KEYS */;
INSERT INTO `jersey_sizes` VALUES (1,'S',1,1,0.00,'2026-09-12 12:27:40','2026-09-12 13:20:20'),(2,'M',2,1,0.00,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(3,'L',3,1,0.00,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(4,'XL',4,1,0.00,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(5,'2XL',5,1,10000.00,'2026-09-12 12:27:40','2026-09-12 13:40:51'),(6,'3XL',6,1,10000.00,'2026-09-12 12:27:40','2026-09-12 13:40:57'),(7,'4XL',7,1,15000.00,'2026-09-12 12:27:40','2026-09-16 12:46:44'),(8,'5XL',8,1,15000.00,'2026-09-12 12:59:58','2026-09-16 12:46:55');
/*!40000 ALTER TABLE `jersey_sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_sleeve_types`
--

DROP TABLE IF EXISTS `jersey_sleeve_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_sleeve_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_sleeve_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_sleeve_types`
--

LOCK TABLES `jersey_sleeve_types` WRITE;
/*!40000 ALTER TABLE `jersey_sleeve_types` DISABLE KEYS */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_umum`
--

DROP TABLE IF EXISTS `jurnal_umum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal_umum` (
  `id_jurnal` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tgl_jurnal` date NOT NULL,
  `no_referensi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kode_akun` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `debet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jurnal`),
  KEY `jurnal_umum_kode_akun_foreign` (`kode_akun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_umum`
--

LOCK TABLES `jurnal_umum` WRITE;
/*!40000 ALTER TABLE `jurnal_umum` DISABLE KEYS */;
INSERT INTO `jurnal_umum` VALUES (49,'2026-09-11','INV08092026-002','Pembelian bahan baku INV08092026-002','1200',4342320.00,0.00,'2026-09-11 02:50:53','2026-09-11 02:50:53',NULL),(50,'2026-09-11','INV08092026-002','Pembelian bahan baku INV08092026-002','2000',0.00,4342320.00,'2026-09-11 02:50:53','2026-09-11 02:50:53',NULL),(51,'2026-09-11','DEP-20260911092618-1','Setoran deposit DEP-20260911092618-1','1000',50000.00,0.00,'2026-09-11 09:26:18','2026-09-11 09:26:18',NULL),(52,'2026-09-11','DEP-20260911092618-1','Setoran deposit DEP-20260911092618-1','2100',0.00,50000.00,'2026-09-11 09:26:18','2026-09-11 09:26:18',NULL),(79,'2026-09-11','INV-20260911-001','Penjualan INV-20260911-001','1100',197940.00,0.00,'2026-09-11 11:33:31','2026-09-11 11:33:31',NULL),(80,'2026-09-11','INV-20260911-001','Penjualan INV-20260911-001','4000',0.00,197940.00,'2026-09-11 11:33:31','2026-09-11 11:33:31',NULL),(93,'2026-09-11','INV-20260226-003','Pembayaran Tunai INV-20260226-003','1000',100000.00,0.00,'2026-09-11 15:43:02','2026-09-11 15:43:02',NULL),(94,'2026-09-11','INV-20260226-003','Pembayaran Tunai INV-20260226-003','1100',0.00,100000.00,'2026-09-11 15:43:02','2026-09-11 15:43:02',NULL),(95,'2026-09-11','INV-20260227-001','Pembayaran Tunai INV-20260227-001','1000',25000.00,0.00,'2026-09-11 15:43:13','2026-09-11 15:43:13',NULL),(96,'2026-09-11','INV-20260227-001','Pembayaran Tunai INV-20260227-001','1100',0.00,25000.00,'2026-09-11 15:43:13','2026-09-11 15:43:13',NULL),(97,'2026-09-11','INV-000999','Pembelian bahan baku INV-000999','1200',1110000.00,0.00,'2026-09-11 16:24:28','2026-09-11 16:24:28',NULL),(98,'2026-09-11','INV-000999','Pembelian bahan baku INV-000999','2000',0.00,1110000.00,'2026-09-11 16:24:28','2026-09-11 16:24:28',NULL),(99,'2026-09-11','INV-000123','Pembelian bahan baku INV-000123','1200',7550000.00,0.00,'2026-09-11 16:25:20','2026-09-11 16:25:20',NULL),(100,'2026-09-11','INV-000123','Pembelian bahan baku INV-000123','1000',0.00,7550000.00,'2026-09-11 16:25:20','2026-09-11 16:25:20',NULL),(107,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1000',30000.00,0.00,'2026-09-11 17:31:35','2026-09-11 17:31:35',NULL),(108,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1100',0.00,30000.00,'2026-09-11 17:31:35','2026-09-11 17:31:35',NULL),(109,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1000',50000.00,0.00,'2026-09-11 17:31:48','2026-09-11 17:31:48',NULL),(110,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1100',0.00,50000.00,'2026-09-11 17:31:48','2026-09-11 17:31:48',NULL),(111,'2026-09-16','INV-20260916-001','Penjualan INV-20260916-001','1100',25000.00,0.00,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL),(112,'2026-09-16','INV-20260916-001','Penjualan INV-20260916-001','4000',0.00,25000.00,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL),(113,'2026-09-16','INV-20260916-001','HPP bahan baku INV-20260916-001','5000',2810.58,0.00,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL),(114,'2026-09-16','INV-20260916-001','HPP bahan baku INV-20260916-001','1200',0.00,2810.58,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL),(115,'2026-09-16','INV-20260916-001','Pembayaran via deposit INV-20260916-001','2100',25000.00,0.00,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL),(116,'2026-09-16','INV-20260916-001','Pembayaran via deposit INV-20260916-001','1100',0.00,25000.00,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL),(123,'2026-09-18','KK-3','Pengeluaran Kas Kecil KK-3','5130',350000.00,0.00,'2026-09-18 11:44:29','2026-09-18 11:44:29',NULL),(124,'2026-09-18','KK-3','Pengeluaran Kas Kecil KK-3','1000',0.00,350000.00,'2026-09-18 11:44:29','2026-09-18 11:44:29',NULL),(125,'2026-09-18','KK-3','Koreksi/pembalikan KK-3','5130',0.00,350000.00,'2026-09-18 15:33:27','2026-09-18 15:33:27',NULL),(126,'2026-09-18','KK-3','Koreksi/pembalikan KK-3','1000',350000.00,0.00,'2026-09-18 15:33:27','2026-09-18 15:33:27',NULL);
/*!40000 ALTER TABLE `jurnal_umum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_stok`
--

DROP TABLE IF EXISTS `kartu_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `tipe` enum('IN','OUT','ADJUSTMENT') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `stok_sebelum` decimal(15,4) NOT NULL,
  `stok_sesudah` decimal(15,4) NOT NULL,
  `referensi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_id_bahan` (`id_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` VALUES (1,1,'2026-03-02 18:46:33','IN',10.0000,0.0000,10.0000,'asd124545','Pembelian bahan',1,'2026-03-02 18:46:33','2026-09-11 15:51:46'),(2,1,'2026-03-05 17:28:12','IN',13.0000,10.0000,23.0000,'SJ2148785','Penerimaan Gudang: Frontlite 280 13roll\r\nstiker inflex 1roll',1,'2026-03-05 10:28:12','2026-09-11 15:51:46'),(3,8,'2026-03-05 17:28:12','IN',1.0000,0.0000,1.0000,'SJ2148785','Penerimaan Gudang: Frontlite 280 13roll\r\nstiker inflex 1roll',1,'2026-03-05 10:28:12','2026-09-11 15:51:46'),(4,1,'2026-03-05 17:30:15','IN',6.0000,23.0000,29.0000,'jl5123123','Penerimaan Gudang: Frontlite 280gram 6 roll,\r\nSublime Paper 1 roll',1,'2026-03-05 10:30:15','2026-09-11 15:51:46'),(5,5,'2026-03-05 17:30:15','IN',1.0000,0.0000,1.0000,'jl5123123','Penerimaan Gudang: Frontlite 280gram 6 roll,\r\nSublime Paper 1 roll',1,'2026-03-05 10:30:15','2026-09-11 15:51:46'),(6,1,'2026-03-05 18:08:37','IN',5.0000,0.0000,5.0000,'SJ.05032026-005','Penerimaan Gudang: Frontlite 280gr 5roll',1,'2026-03-05 11:08:37','2026-09-11 15:51:46'),(7,1,'2026-03-05 18:20:19','IN',5.0000,5.0000,10.0000,'NF.05032026-005','Pembelian bahan',1,'2026-03-05 18:20:19','2026-09-11 15:51:46'),(8,4,'2026-03-05 22:02:15','IN',1.0000,0.0000,1.0000,'SJ.05032026-00125','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. AC 210gr (1.00 rim)\r\n2. CANVAS (1.00 Roll)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-05 15:02:15','2026-09-11 15:51:46'),(9,10,'2026-03-05 22:02:15','IN',1.0000,0.0000,1.0000,'SJ.05032026-00125','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. AC 210gr (1.00 rim)\r\n2. CANVAS (1.00 Roll)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-05 15:02:15','2026-09-11 15:51:46'),(12,4,'2026-03-05 22:19:04','IN',1.0000,1.0000,2.0000,'NF.032457899','Pembelian bahan',1,'2026-03-05 22:19:04','2026-09-11 15:51:46'),(13,10,'2026-03-05 22:19:04','IN',1.0000,1.0000,2.0000,'NF.032457899','Pembelian bahan',1,'2026-03-05 22:19:04','2026-09-11 15:51:46'),(14,6,'2026-03-06 07:36:04','IN',1.0000,0.0000,1.0000,'SJ.05324578784','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PET FILM DTF (1.00 Roll)\r\n2. AC 260gr (1 rim)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-06 00:36:04','2026-09-11 15:51:46'),(15,3,'2026-03-06 07:36:04','IN',1.0000,0.0000,1.0000,'SJ.05324578784','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PET FILM DTF (1.00 Roll)\r\n2. AC 260gr (1 rim)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-06 00:36:04','2026-09-11 15:51:46'),(16,1,'2026-09-07 20:19:04','IN',1.0000,4.0000,5.0000,'SJ05478799','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr (1.00 roll)\r\n2. PET FILM DTF (1.00 roll)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-07 13:19:04','2026-09-11 15:51:46'),(17,6,'2026-09-07 20:19:04','IN',1.0000,1.0000,2.0000,'SJ05478799','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr (1.00 roll)\r\n2. PET FILM DTF (1.00 roll)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-07 13:19:04','2026-09-11 15:51:46'),(20,1,'2026-09-10 16:31:14','ADJUSTMENT',3.5000,-3.5000,0.0000,'KOREKSI-2026-09-10','Koreksi stok negatif akibat bug historis: pemakaian bahan baku dari order tidak pernah tercatat di kartu_stok sebelum diperbaiki. Di-set ke 0 sebagai titik awal bersih, menunggu stock opname fisik untuk angka pasti.',NULL,'2026-09-10 09:31:14','2026-09-11 15:51:46'),(21,3,'2026-09-10 16:31:14','ADJUSTMENT',4.0000,-4.0000,0.0000,'KOREKSI-2026-09-10','Koreksi stok negatif akibat bug historis: pemakaian bahan baku dari order tidak pernah tercatat di kartu_stok sebelum diperbaiki. Di-set ke 0 sebagai titik awal bersih, menunggu stock opname fisik untuk angka pasti.',NULL,'2026-09-10 09:31:14','2026-09-11 15:51:46'),(22,9,'2026-09-10 16:31:14','ADJUSTMENT',1.5200,-1.5200,0.0000,'KOREKSI-2026-09-10','Koreksi stok negatif akibat bug historis: pemakaian bahan baku dari order tidak pernah tercatat di kartu_stok sebelum diperbaiki. Di-set ke 0 sebagai titik awal bersih, menunggu stock opname fisik untuk angka pasti.',NULL,'2026-09-10 09:31:14','2026-09-11 15:51:46'),(46,11,'2026-09-11 09:49:09','IN',2.0000,0.0000,2.0000,'SJ.11092026-002','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-11 02:49:09','2026-09-11 15:51:46'),(47,12,'2026-09-11 09:49:09','IN',4.0000,1.0000,5.0000,'SJ.11092026-002','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-11 02:49:09','2026-09-11 15:51:46'),(48,3,'2026-09-11 09:49:09','IN',4.0000,0.0000,4.0000,'SJ.11092026-002','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-11 02:49:09','2026-09-11 15:51:46'),(49,29,'2026-09-11 09:57:19','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','Uji coba bahan',1,'2026-09-11 02:57:19','2026-09-11 15:51:46'),(59,5,'2026-09-11 18:21:05','ADJUSTMENT',5.0000,0.0000,5.0000,'STOCK-OPNAME','Bahan datang dengan Travel',1,'2026-09-11 11:21:05','2026-09-11 15:51:46'),(61,7,'2026-09-11 18:33:31','OUT',0.0280,0.0000,-0.0280,'INV-20260911-001','Pemakaian produksi order INV-20260911-001',1,'2026-09-11 11:33:31','2026-09-11 15:51:46'),(62,3,'2026-09-11 19:36:29','OUT',0.0100,4.0000,3.9900,'QA-GUARD-TEST-OK','Pemakaian produksi order QA-GUARD-TEST-OK',1,'2026-09-11 12:36:29','2026-09-11 15:51:46'),(65,30,'2026-09-11 23:17:31','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Sisa stock',1,'2026-09-11 16:17:31','2026-09-11 16:17:31'),(66,27,'2026-09-11 23:17:42','ADJUSTMENT',5.0000,0.0000,5.0000,'STOCK-OPNAME','Belanjaan Baru',1,'2026-09-11 16:17:42','2026-09-11 16:17:42'),(67,28,'2026-09-11 23:17:53','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:17:53','2026-09-11 16:17:53'),(68,1,'2026-09-11 23:18:02','ADJUSTMENT',3.0000,0.0000,3.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:02','2026-09-11 16:18:02'),(69,7,'2026-09-11 23:18:27','ADJUSTMENT',2.0280,-0.0280,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:27','2026-09-11 16:18:27'),(70,8,'2026-09-11 23:18:40','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:40','2026-09-11 16:18:40'),(71,9,'2026-09-11 23:18:48','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:48','2026-09-11 16:18:48'),(72,3,'2026-09-16 21:52:26','OUT',0.0100,3.9900,3.9800,'INV-20260916-001','Pemakaian produksi order INV-20260916-001',35,'2026-09-16 14:52:26','2026-09-16 14:52:26'),(74,37,'2026-09-21 08:15:04','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:15:04','2026-09-21 01:15:04'),(75,39,'2026-09-21 08:15:20','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:15:20','2026-09-21 01:15:20'),(76,38,'2026-09-21 08:15:28','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:15:28','2026-09-21 01:15:28'),(77,36,'2026-09-21 08:15:39','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','opname harian dan stok',1,'2026-09-21 01:15:39','2026-09-21 01:15:39'),(78,32,'2026-09-21 08:15:52','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:15:52','2026-09-21 01:15:52'),(79,37,'2026-09-21 08:16:52','ADJUSTMENT',398.0000,2.0000,400.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:16:52','2026-09-21 01:16:52'),(80,39,'2026-09-21 08:17:39','ADJUSTMENT',349.0000,1.0000,350.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:17:39','2026-09-21 01:17:39'),(81,38,'2026-09-21 08:17:47','ADJUSTMENT',249.0000,1.0000,250.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:17:47','2026-09-21 01:17:47'),(82,36,'2026-09-21 08:18:14','ADJUSTMENT',748.0000,2.0000,750.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:18:14','2026-09-21 01:18:14'),(83,4,'2026-09-21 08:18:44','ADJUSTMENT',248.0000,2.0000,250.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:18:44','2026-09-21 01:18:44'),(84,3,'2026-09-21 08:18:54','ADJUSTMENT',233.0200,3.9800,237.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:18:54','2026-09-21 01:18:54'),(85,34,'2026-09-21 08:19:13','ADJUSTMENT',320.0000,0.0000,320.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:19:13','2026-09-21 01:19:13'),(86,35,'2026-09-21 08:19:24','ADJUSTMENT',315.0000,0.0000,315.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:19:24','2026-09-21 01:19:24'),(87,12,'2026-09-21 08:19:35','ADJUSTMENT',240.0000,5.0000,245.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:19:35','2026-09-21 01:19:35'),(88,33,'2026-09-21 08:19:52','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:19:52','2026-09-21 01:19:52'),(89,31,'2026-09-21 08:20:03','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','opname harian',1,'2026-09-21 01:20:03','2026-09-21 01:20:03'),(91,2,'2026-09-11 04:53:06','IN',1.0000,0.0000,1.0000,'STOK-AWAL-BACKFILL','Backfill: stok awal tidak tercatat di kartu_stok saat bahan ini dibuat (audit Phase 6)',1,'2026-09-10 21:53:06','2026-09-23 12:31:21'),(92,13,'2026-09-12 00:36:59','IN',5.0000,0.0000,5.0000,'STOK-AWAL-BACKFILL','Backfill: stok awal tidak tercatat di kartu_stok saat bahan ini dibuat (audit Phase 6)',1,'2026-09-11 17:36:59','2026-09-23 12:31:21'),(94,13,'2026-09-23 23:23:51','ADJUSTMENT',1.0000,5.0000,4.0000,'SO-20260923-002','Stock Opname SO-20260923-002: Sudah terpakai',1,'2026-09-23 16:23:51','2026-09-23 16:23:51'),(95,27,'2026-09-23 23:23:51','ADJUSTMENT',3.0000,5.0000,2.0000,'SO-20260923-002','Stock Opname SO-20260923-002: Sudah terpakai',1,'2026-09-23 16:23:51','2026-09-23 16:23:51');
/*!40000 ALTER TABLE `kartu_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karyawan` (
  `id_karyawan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_karyawan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bagian` enum('Admin','Desain','Produksi','Kasir','Gudang') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_karyawan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawan`
--

LOCK TABLES `karyawan` WRITE;
/*!40000 ALTER TABLE `karyawan` DISABLE KEYS */;
INSERT INTO `karyawan` VALUES (1,'Admin Utama','Admin','0836587999',1,NULL,'2026-03-24 07:13:00'),(5,'Maulidia','Desain','083245455658',1,'2026-02-25 14:33:59','2026-03-06 00:43:19'),(6,'Rehan','Desain','089978878',1,'2026-02-25 14:36:02','2026-02-25 14:36:02'),(7,'Rahman','Desain','085322154545',1,'2026-02-25 14:36:11','2026-02-25 14:36:11'),(8,'Fathur Rosi','Produksi','-',1,'2026-03-06 00:43:43','2026-03-06 00:43:43'),(9,'Moh. Riski','Produksi','-',1,'2026-03-06 00:44:00','2026-03-06 00:44:00'),(10,'Haifa','Kasir','081578892592',1,'2026-03-24 07:14:58','2026-03-24 07:14:58');
/*!40000 ALTER TABLE `karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawan_mesin`
--

DROP TABLE IF EXISTS `karyawan_mesin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karyawan_mesin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_karyawan` bigint(20) unsigned NOT NULL,
  `id_mesin` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karyawan_mesin_id_karyawan_id_mesin_unique` (`id_karyawan`,`id_mesin`),
  KEY `karyawan_mesin_id_mesin_foreign` (`id_mesin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawan_mesin`
--

LOCK TABLES `karyawan_mesin` WRITE;
/*!40000 ALTER TABLE `karyawan_mesin` DISABLE KEYS */;
INSERT INTO `karyawan_mesin` VALUES (2,8,3,NULL,NULL),(3,9,5,NULL,NULL),(4,8,1,NULL,NULL);
/*!40000 ALTER TABLE `karyawan_mesin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_harian`
--

DROP TABLE IF EXISTS `kas_harian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_harian` (
  `id_kas` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tgl_kas` date NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kas`),
  KEY `kas_harian_id_user_foreign` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_harian`
--

LOCK TABLES `kas_harian` WRITE;
/*!40000 ALTER TABLE `kas_harian` DISABLE KEYS */;
INSERT INTO `kas_harian` VALUES (1,'2026-02-26',1,200000.00,0.00,0.00,0.00,0.00,1073600.00,NULL,NULL,40000.00,0.00,'open',NULL,NULL,NULL,NULL,NULL),(2,'2026-02-27',1,200000.00,0.00,0.00,0.00,0.00,200000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-02-27 11:41:29','2026-02-27 11:41:29'),(3,'2026-02-27',1,200000.00,0.00,0.00,0.00,0.00,200000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-02-27 11:43:37','2026-02-27 11:43:37'),(7,'2026-09-09',1,300000.00,0.00,0.00,0.00,0.00,300000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-09 23:27:35','2026-09-09 23:27:35'),(11,'2026-09-10',1,100000.00,0.00,0.00,0.00,0.00,100000.00,100000.00,0.00,0.00,0.00,'closed','2026-09-10 08:36:03',NULL,NULL,'2026-09-10 08:31:53','2026-09-10 08:36:03'),(17,'2026-09-11',1,100000.00,0.00,0.00,0.00,0.00,375000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-11 15:32:24','2026-09-11 15:32:24'),(18,'2026-09-12',1,100000.00,0.00,0.00,0.00,0.00,180000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-11 17:30:27','2026-09-11 17:30:27'),(19,'2026-09-16',1,500000.00,0.00,0.00,0.00,0.00,500000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-16 14:51:11','2026-09-16 14:51:11');
/*!40000 ALTER TABLE `kas_harian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_kecil`
--

DROP TABLE IF EXISTS `kas_kecil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_kecil` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_kategori` bigint(20) unsigned NOT NULL,
  `keterangan` varchar(500) NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `bukti_path` varchar(255) DEFAULT NULL,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kas_kecil_id_kategori_foreign` (`id_kategori`),
  KEY `kas_kecil_id_user_foreign` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_kecil`
--

LOCK TABLES `kas_kecil` WRITE;
/*!40000 ALTER TABLE `kas_kecil` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_kecil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_manual`
--

DROP TABLE IF EXISTS `kas_manual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_manual` (
  `id_kas_manual` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kas` bigint(20) unsigned NOT NULL,
  `waktu` datetime NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Masuk','Keluar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kas_manual`),
  KEY `kas_manual_id_kas_foreign` (`id_kas`),
  KEY `kas_manual_id_user_foreign` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_manual`
--

LOCK TABLES `kas_manual` WRITE;
/*!40000 ALTER TABLE `kas_manual` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_manual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori` (
  `id_kategori` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kode_kategori` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (2,'INDOOR','IND',NULL,NULL),(3,'OUTDOOR','OTD',NULL,NULL),(4,'A3','A3',NULL,NULL),(5,'CENTURY STAR','CEN',NULL,NULL),(6,'SUBLIME','SUB','2026-02-25 23:30:16','2026-02-25 23:30:16'),(7,'RHINO DTF','DTF','2026-02-25 23:30:29','2026-02-25 23:30:29');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori_finishing`
--

DROP TABLE IF EXISTS `kategori_finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_finishing` (
  `id_kategori` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_finishing`
--

LOCK TABLES `kategori_finishing` WRITE;
/*!40000 ALTER TABLE `kategori_finishing` DISABLE KEYS */;
/*!40000 ALTER TABLE `kategori_finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori_kas_kecil`
--

DROP TABLE IF EXISTS `kategori_kas_kecil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_kas_kecil` (
  `id_kategori_kas_kecil` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `kode_akun` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kategori_kas_kecil`),
  UNIQUE KEY `kategori_kas_kecil_nama_kategori_unique` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_kas_kecil`
--

LOCK TABLES `kategori_kas_kecil` WRITE;
/*!40000 ALTER TABLE `kategori_kas_kecil` DISABLE KEYS */;

--
-- Table structure for table `kedatangan_bahan`
--

DROP TABLE IF EXISTS `kedatangan_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kedatangan_bahan` (
  `id_kedatangan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_surat_jalan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_supplier` bigint(20) unsigned NOT NULL,
  `id_gudang` bigint(20) unsigned NOT NULL,
  `tgl_datang` date NOT NULL,
  `status` enum('Draft','Selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Draft',
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kedatangan`),
  KEY `kedatangan_bahan_id_gudang_foreign` (`id_gudang`),
  KEY `kedatangan_bahan_id_supplier_foreign` (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kedatangan_bahan`
--

LOCK TABLES `kedatangan_bahan` WRITE;
/*!40000 ALTER TABLE `kedatangan_bahan` DISABLE KEYS */;
INSERT INTO `kedatangan_bahan` VALUES (19,'SJ.05032026-005',1,1,'2026-03-05','Selesai','Frontlite 280gr 5roll',NULL,NULL),(20,'SJ.05032026-00125',2,1,'2026-03-05','Selesai','PENERIMAAN BARANG FISIK:\r\n1. AC 210gr (1.00 rim)\r\n2. CANVAS (1.00 Roll)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',NULL,NULL),(21,'SJ.05324578784',1,1,'2026-03-05','Selesai','PENERIMAAN BARANG FISIK:\r\n1. PET FILM DTF (1.00 Roll)\r\n2. AC 260gr (1 rim)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',NULL,NULL),(22,'SJ05478799',3,1,'2026-03-05','Selesai','PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr (1.00 roll)\r\n2. PET FILM DTF (1.00 roll)\r\n\r\nNB : Barang Lengkap!',NULL,NULL),(23,'SJ06042026-01500',3,1,'2026-03-05','Draft','PENERIMAAN BARANG FISIK:\r\n1. STIKER INFLEX (1 Roll)\r\n\r\nNB : Barang Lengkap!',NULL,NULL),(29,'SJ.11092026-002',6,1,'2026-09-11','Selesai','PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',NULL,NULL);
/*!40000 ALTER TABLE `kedatangan_bahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kedatangan_bahan_detail`
--

DROP TABLE IF EXISTS `kedatangan_bahan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kedatangan_bahan_detail` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kedatangan` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `satuan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('OK','Rusak','Kurang') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OK',
  PRIMARY KEY (`id_detail`),
  KEY `kedatangan_bahan_detail_id_bahan_foreign` (`id_bahan`),
  KEY `kedatangan_bahan_detail_id_kedatangan_foreign` (`id_kedatangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kedatangan_bahan_detail`
--

LOCK TABLES `kedatangan_bahan_detail` WRITE;
/*!40000 ALTER TABLE `kedatangan_bahan_detail` DISABLE KEYS */;
INSERT INTO `kedatangan_bahan_detail` VALUES (38,19,1,5.00,'roll','OK'),(48,20,4,1.00,'rim','OK'),(49,20,10,1.00,'Roll','OK'),(50,21,6,1.00,'Roll','OK'),(51,21,3,1.00,'rim','OK'),(52,23,8,1.00,NULL,'OK'),(55,22,1,1.00,'roll','OK'),(56,22,6,1.00,'roll','OK'),(65,29,11,2.00,'roll','OK'),(66,29,12,4.00,'rim','OK'),(67,29,3,4.00,'rim','OK');
/*!40000 ALTER TABLE `kedatangan_bahan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `konsumen`
--

DROP TABLE IF EXISTS `konsumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `konsumen` (
  `id_konsumen` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_konsumen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_konsumen_manual` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_golongan` bigint(20) unsigned NOT NULL,
  `tipe_konsumen` enum('permanent','temporary') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'temporary',
  `tgl_registrasi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_konsumen`),
  KEY `konsumen_id_golongan_foreign` (`id_golongan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `konsumen`
--

LOCK TABLES `konsumen` WRITE;
/*!40000 ALTER TABLE `konsumen` DISABLE KEYS */;
INSERT INTO `konsumen` VALUES (1,'Arafiq','','08121545487','Sampang',1,'temporary','2026-02-25 15:10:43'),(2,'Budi','','0854547878','Sampang',2,'permanent','2026-02-25 15:10:58'),(3,'Kamil','','08547578787','Sampang',1,'temporary','2026-02-25 15:11:09'),(4,'Samir','Samir','002124878','-',1,'temporary','2026-02-26 16:54:45'),(5,'Komar Camplong','Komar Camplong','08532154542','-',1,'temporary','2026-02-27 09:54:23'),(6,'Kiki Pangarengan','Kiki Pangarengan','083225454',NULL,1,'temporary','2026-02-27 10:51:26');
/*!40000 ALTER TABLE `konsumen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `konsumen_deposit`
--

DROP TABLE IF EXISTS `konsumen_deposit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `konsumen_deposit` (
  `id_deposit` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_konsumen` bigint(20) unsigned NOT NULL,
  `tipe` enum('Setoran','Pemakaian') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Setoran',
  `id_transaksi` bigint(20) unsigned DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode_bayar` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Tunai',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_deposit` date NOT NULL,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_deposit`),
  KEY `konsumen_deposit_id_konsumen_foreign` (`id_konsumen`),
  KEY `konsumen_deposit_id_transaksi_foreign` (`id_transaksi`),
  KEY `konsumen_deposit_id_user_foreign` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `konsumen_deposit`
--

LOCK TABLES `konsumen_deposit` WRITE;
/*!40000 ALTER TABLE `konsumen_deposit` DISABLE KEYS */;
INSERT INTO `konsumen_deposit` VALUES (6,1,'Setoran',NULL,50000.00,'Tunai','DP awal','2026-09-11',1,'2026-09-11 09:26:18','2026-09-11 09:26:18',NULL),(7,1,'Pemakaian',49,25000.00,'Deposit','Pembayaran otomatis nota INV-20260916-001 saat order dibuat [dicatat ulang ke admin - id_user asli sudah terhapus]','2026-09-16',1,'2026-09-16 14:52:26','2026-09-16 14:52:26',NULL);
/*!40000 ALTER TABLE `konsumen_deposit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id_menu` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_menu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url_link` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'Master Bahan','bahan-baku',0),(2,'BOM Produk','bom',0),(3,'Transaksi Baru','penjualan',0),(4,'Pengaturan Akses','setting-akses',0),(5,'Data Karyawan','karyawan',0),(6,'Data User','users',0),(7,'Data Konsumen','konsumen',0),(8,'Golongan Harga','golongan',0),(9,'Supplier Bahan','supplier',0),(10,'Satuan & Konversi','satuan',0),(11,'Bank Perusahaan','bank',0),(12,'Golongan Konsumen','golongan',0),(13,'Produk & BOM','produk',0),(14,'Price List & Level','setting-harga',0),(15,'Kategori','kategori',0),(16,'Kasir','kasir',0),(17,'Kedatangan Bahan','kedatangan-bahan',0),(18,'Golongan','golongan',0),(20,'Pembelian Bahan','pembelian-bahan',0),(21,'Finishing Produk','finishing',0),(22,'SPK','spk',0),(23,'Produksi','produksi',0),(24,'Collecting','collecting',0),(25,'Laporan Order','laporan/order',0),(26,'Laporan Produksi','laporan/produksi',0),(27,'Aset Mesin','mesin',0),(28,'Harga Promo','harga-promo',0),(29,'Laporan Kas','laporan/kas',0),(30,'Merk','merk',0),(31,'Jenis Bahan','jenis-bahan',0),(32,'Jasa','jasa',0),(33,'Gudang','gudang',0),(34,'Deposit Konsumen','konsumen-deposit',0),(35,'Chart of Accounts','akun',0),(36,'Laporan Keuangan','akuntansi',0),(37,'Pengaturan Aplikasi','pengaturan-aplikasi',0),(38,'Order Jersey','jersey',0),(39,'Kas Kecil','kas-kecil',0),(40,'Laporan Belanja Bahan','laporan/belanja-bahan',0),(41,'Kasir Utama (Input Order)','penjualan-input-order',0),(42,'Order Jersey (Input Order)','jersey-input-order',0),(43,'Laporan Order Jersey','laporan/order-jersey',0),(44,'Laporan Piutang','laporan/piutang',0),(45,'Kategori Kas Kecil','kategori-kas-kecil',0),(46,'Laporan Stok Bahan Baku','laporan/stok-bahan',0),(47,'Laporan HPP & Margin','laporan/hpp',0),(48,'Penjualan Barang','penjualan-barang',0),(49,'Log Aktivitas','log-aktivitas',0),(50,'Approval Workflow','approval',0),(51,'Accounting Period','accounting-period',0),(52,'Backup & Recovery','backup',0),(53,'Stock Opname','stock-opname',0);
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merk`
--

DROP TABLE IF EXISTS `merk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merk` (
  `id_merk` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_merk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_merk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merk`
--

LOCK TABLES `merk` WRITE;
/*!40000 ALTER TABLE `merk` DISABLE KEYS */;
INSERT INTO `merk` VALUES (2,'Semua Merk',NULL,'2026-09-10 07:54:12','2026-09-10 07:54:12');
/*!40000 ALTER TABLE `merk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesin`
--

DROP TABLE IF EXISTS `mesin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesin` (
  `id_mesin` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_mesin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_mesin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesin`
--

LOCK TABLES `mesin` WRITE;
/*!40000 ALTER TABLE `mesin` DISABLE KEYS */;
INSERT INTO `mesin` VALUES (1,'EPSON S80670','Eco-solvent'),(2,'FLORA OUTDOOR','Solvent'),(3,'KONICA MINOLTA','Laser A3'),(4,'CENTURY STAR','Direct Print'),(5,'RHINO DTF','Digital Sablon'),(6,'ESPON F7270','Sublime');
/*!40000 ALTER TABLE `mesin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_02_25_100910_create_master_karyawan_table',1),(2,'2026_02_25_100910_create_rbac_tables',1),(3,'2026_02_25_101844_create_sessions_table',1),(4,'2026_02_25_104514_create_master_produksi_tables',1),(5,'2026_02_25_111057_create_complex_system_tables',1),(6,'2026_02_25_112610_add_missing_erp_tables',1),(7,'2026_02_26_001742_create_cache_table',1),(8,'2026_02_26_125746_fix_transaksi_columns',1),(9,'2026_03_01_174529_add_tipe_satuan_to_satuan_table',1),(10,'2026_03_05_102450_create_kartu_stoks_table',1),(11,'2026_09_07_000001_reconcile_inventory_module_schema',1),(12,'2026_09_10_001021_add_spk_produksi_fields',1),(13,'2026_09_10_010000_add_collecting_menu',1),(14,'2026_09_10_020000_add_laporan_menu',1),(15,'2026_09_10_030000_fix_menu_access_gaps',1),(16,'2026_09_10_040000_add_harga_promo_menu',1),(17,'2026_09_10_050000_add_finishing_fields_to_transaksi_detail',1),(18,'2026_09_10_060000_add_bank_to_pembayaran_riwayat',1),(19,'2026_09_10_070000_add_laporan_kas_menu',1),(20,'2026_09_10_080000_drop_orphaned_kartu_stoks_and_kas_sesi_tables',1),(21,'2026_09_10_090000_backfill_bahan_baku_satuan_relations',1),(22,'2026_09_10_100000_add_harga_rata_rata_to_bahan_baku',1),(23,'2026_09_10_110000_add_hpp_snapshot_to_transaksi_detail',1),(24,'2026_09_10_120000_create_bag_akun_table',1),(25,'2026_09_10_130000_add_kode_akun_fk_to_jurnal_umum',1),(26,'2026_09_10_140000_create_merk_table',1),(27,'2026_09_10_150000_create_jenis_bahan_table',1),(28,'2026_09_10_160000_add_merk_and_jenis_bahan_to_bahan_baku',1),(29,'2026_09_10_170000_create_jasa_table',1),(30,'2026_09_10_180000_enrich_gudang_table',1),(31,'2026_09_10_190000_create_konsumen_deposit_table',1),(32,'2026_09_10_200000_register_new_master_data_menus',1),(33,'2026_09_10_210000_fix_gudang_id_auto_increment',1),(34,'2026_09_10_220000_backfill_satuan_stok_dash_values',1),(35,'2026_09_10_230000_widen_stock_decimal_precision',1),(36,'2026_09_10_240000_add_deposit_and_jasa_accounts',1),(37,'2026_09_10_250000_add_tipe_to_konsumen_deposit',1),(38,'2026_09_10_260000_add_deposit_to_pembayaran_riwayat_metode_enum',1),(39,'2026_09_10_270000_add_jasa_support_to_transaksi_detail',1),(40,'2026_09_10_280000_add_dimensi_roll_to_bahan_baku',1),(41,'2026_09_10_290000_register_akuntansi_menus',1),(42,'2026_09_10_300000_extend_order_kantor_fields',1),(43,'2026_09_10_310000_fix_kartu_stok_bahan_fk_to_restrict',1),(44,'2026_09_10_320000_split_frontlite_280_into_roll_width_variants',1),(45,'2026_09_11_100000_create_karyawan_mesin_table',1),(46,'2026_09_11_110000_expand_kasir_role_to_full_finance_scope',1),(47,'2026_09_11_120000_fix_operator_desain_role_permissions',1),(48,'2026_09_11_130000_fix_stale_menu_url_link_labels',1),(49,'2026_09_11_140000_create_operator_produksi_role',1),(50,'2026_09_11_150000_create_gudang_role',1),(51,'2026_09_11_160000_add_collecting_status_to_transaksi',1),(52,'2026_09_11_170000_fix_konsumen_deposit_fk_to_restrict',1),(53,'2026_09_11_180000_add_missing_fk_kedatangan_bahan_gudang',1),(54,'2026_09_11_190000_add_pending_status_to_transaksi_detail',1),(55,'2026_09_11_190100_add_status_nota_to_transaksi',1),(56,'2026_09_11_200000_create_finishing_harga_table',1),(57,'2026_09_11_200100_create_jasa_harga_table',1),(58,'2026_09_11_210000_add_biaya_desain_potongan_to_transaksi_detail',1),(59,'2026_09_11_220000_add_status_to_produk',1),(60,'2026_09_11_230000_create_nota_edit_request_table',1),(61,'2026_09_11_240000_make_range_min_max_decimal_in_harga_tables',1),(62,'2026_09_11_250000_add_debit_metode_bayar',1),(63,'2026_09_12_010000_create_kas_manual_table',1),(64,'2026_09_12_020000_add_status_rolling_to_kas_harian',1),(65,'2026_09_12_030000_restore_missing_timestamps_on_transaksi',1),(66,'2026_09_12_030100_restore_missing_timestamps_on_transaksi_detail',1),(67,'2026_09_12_040000_recreate_missing_laravel_framework_tables',1),(68,'2026_09_12_150000_create_app_settings_table',1),(69,'2026_09_12_160000_create_jersey_module_tables',1),(70,'2026_09_12_170000_restructure_jersey_multi_item_orders',1),(71,'2026_09_12_220000_fix_corrupted_default_quotes',1),(72,'2026_09_12_223000_add_material_to_jersey_price_settings',1),(73,'2026_09_13_010000_add_size_surcharge_total_to_jersey_order_lines',1),(74,'2026_09_13_020000_add_weight_to_jersey_size_component_usage',1),(75,'2026_09_13_030000_create_jersey_order_payments_table',1),(76,'2026_09_13_040000_add_sleeve_collar_master_data_to_jersey',1),(77,'2026_09_13_050000_add_is_susulan_to_jersey_order_lines',1),(78,'2026_09_13_060000_convert_sleeve_column_to_varchar',1),(79,'2026_09_13_070000_add_has_sleeve_collar_to_jersey_product_types',1),(80,'2026_09_12_180000_add_material_estimation_to_jersey',1),(81,'2026_09_16_010000_add_scope_to_jersey_components',1),(82,'2026_09_16_020000_add_price_surcharge_to_jersey_sleeve_collar_types',1),(83,'2026_09_16_030000_create_kas_kecil_table',1),(84,'2026_09_16_040000_scope_operator_desain_to_input_order_only',1),(85,'2026_09_16_180000_add_fax_website_to_app_settings',1),(86,'2026_09_17_090000_add_laporan_piutang_menu',1),(87,'2026_09_17_100000_create_kategori_kas_kecil_table',1),(88,'2026_09_17_100100_add_kategori_kas_kecil_menu',1),(89,'2026_09_17_110000_add_laporan_stok_bahan_menu',1),(90,'2026_09_17_120000_grant_gudang_laporan_stok_bahan',1),(91,'2026_09_17_130000_add_libur_kas_minggu_to_app_settings',1),(92,'2026_09_17_140000_add_laporan_hpp_menu',1),(93,'2026_09_18_090000_add_nota_settings_to_app_settings',1),(94,'2026_09_18_100000_add_id_bahan_to_transaksi_detail',1),(95,'2026_09_18_110000_add_penjualan_barang_menu',1),(96,'2026_09_18_120000_add_pembulatan_nota_to_app_settings',1),(97,'2026_09_18_130000_add_beban_usaha_accounts',1),(98,'2026_09_18_130100_add_kode_akun_to_kategori_kas_kecil',1),(99,'2026_09_23_150000_create_bom_finishing_table',1),(100,'2026_09_23_160000_fix_inconsistent_id_column_types',2),(101,'2026_09_23_170000_add_soft_deletes_to_ledger_tables',3),(102,'2026_09_10_045900_create_finishing_table',1),(103,'2026_02_25_101900_create_bank_perusahaan_table',1),(104,'2026_02_25_101901_create_kategori_finishing_table',1),(105,'2026_02_25_101902_create_worksheet_table',1),(106,'2026_09_10_045901_create_produk_finishing_table',1),(107,'2026_09_10_055000_create_pembayaran_riwayat_table',1),(108,'2026_09_11_000000_create_kas_harian_table',1),(109,'2026_02_25_111058_add_id_mesin_to_produk_table',1),(110,'2026_09_23_180000_create_audit_logs_table',4),(111,'2026_09_23_190000_add_log_aktivitas_menu',4),(113,'2026_09_23_200000_create_document_number_counters_table',5),(114,'2026_09_23_210000_create_approval_requests_table',6),(115,'2026_09_23_220000_add_approval_menu',6),(116,'2026_09_23_230000_create_accounting_periods_table',7),(117,'2026_09_23_240000_add_accounting_period_menu',7),(118,'2026_09_23_250000_create_backup_logs_table',8),(119,'2026_09_23_260000_add_backup_menu',8),(120,'2026_09_23_270000_create_stock_opnames_table',9),(121,'2026_09_23_280000_add_stock_opname_menu',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nota_edit_request`
--

DROP TABLE IF EXISTS `nota_edit_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nota_edit_request` (
  `id_request` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaksi` bigint(20) unsigned NOT NULL,
  `id_user_requester` bigint(20) unsigned NOT NULL,
  `alasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak','Selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Menunggu',
  `id_user_approver` bigint(20) unsigned DEFAULT NULL,
  `catatan_approver` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_keputusan` timestamp NULL DEFAULT NULL,
  `tgl_selesai` timestamp NULL DEFAULT NULL,
  `id_transaksi_koreksi` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_request`),
  KEY `nota_edit_request_id_transaksi_foreign` (`id_transaksi`),
  KEY `nota_edit_request_id_user_approver_foreign` (`id_user_approver`),
  KEY `nota_edit_request_id_user_requester_foreign` (`id_user_requester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nota_edit_request`
--

LOCK TABLES `nota_edit_request` WRITE;
/*!40000 ALTER TABLE `nota_edit_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `nota_edit_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayaran_riwayat`
--

DROP TABLE IF EXISTS `pembayaran_riwayat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayaran_riwayat` (
  `id_pembayaran` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaksi` bigint(20) unsigned NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` enum('Tunai','Transfer','Debit','Deposit') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_bank` bigint(20) unsigned DEFAULT NULL,
  `tgl_bayar` datetime NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `pembayaran_riwayat_id_bank_foreign` (`id_bank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran_riwayat`
--

LOCK TABLES `pembayaran_riwayat` WRITE;
/*!40000 ALTER TABLE `pembayaran_riwayat` DISABLE KEYS */;
INSERT INTO `pembayaran_riwayat` VALUES (1,3,500000.00,'Tunai',NULL,'2026-02-26 22:21:55','Pembayaran DP'),(2,3,150400.00,'Tunai',NULL,'2026-02-26 22:33:43','Pembayaran via Kasir'),(3,3,40000.00,'Transfer',NULL,'2026-02-26 22:51:17','Pembayaran Transfer via Kasir'),(4,3,100000.00,'Tunai',NULL,'2026-02-26 23:14:38','Pembayaran via Kasir'),(16,4,100000.00,'Tunai',NULL,'2026-09-11 22:43:02','Pembayaran via Kasir'),(17,5,25000.00,'Tunai',NULL,'2026-09-11 22:43:13','Pembayaran via Kasir'),(21,7,30000.00,'Tunai',NULL,'2026-09-12 00:31:35','Pembayaran via Kasir'),(22,7,50000.00,'Tunai',NULL,'2026-09-12 00:31:48','Pembayaran via Kasir'),(23,49,25000.00,'Deposit',NULL,'2026-09-16 21:52:26','Otomatis dipotong dari saldo deposit saat order dibuat');
/*!40000 ALTER TABLE `pembayaran_riwayat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembelian_bahan`
--

DROP TABLE IF EXISTS `pembelian_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembelian_bahan` (
  `id_pembelian` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kedatangan` bigint(20) unsigned NOT NULL,
  `no_faktur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `tipe_pembelian` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jatuh_tempo_hari` int(11) DEFAULT NULL,
  `jatuh_tempo_tgl` date DEFAULT NULL,
  `status_pajak` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `include_pajak` tinyint(1) DEFAULT 0,
  `grand_total` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_pembayaran` enum('Hutang','Lunas') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Hutang',
  `tgl_pelunasan` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pembelian`),
  UNIQUE KEY `pembelian_bahan_no_faktur_unique` (`no_faktur`),
  KEY `pembelian_bahan_id_kedatangan_foreign` (`id_kedatangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembelian_bahan`
--

LOCK TABLES `pembelian_bahan` WRITE;
/*!40000 ALTER TABLE `pembelian_bahan` DISABLE KEYS */;
INSERT INTO `pembelian_bahan` VALUES (3,19,'NF.05032026-005','2026-03-02','Biasa',NULL,NULL,NULL,0,5600000.00,'2026-09-11 15:51:46','2026-03-05 11:20:19','Hutang',NULL,NULL),(5,20,'NF.032457899','2026-03-05','Biasa',NULL,NULL,NULL,0,3765000.00,'2026-09-11 15:51:46','2026-03-05 15:27:40','Lunas','2026-03-05 15:27:40',NULL),(8,29,'INV08092026-002','2026-09-09','Tempo',NULL,NULL,NULL,1,4342320.00,'2026-09-11 15:51:46','2026-09-11 02:50:53','Hutang',NULL,NULL),(9,21,'INV-000999','2026-09-11','Tempo',NULL,NULL,NULL,1,1110000.00,'2026-09-11 16:24:28','2026-09-11 16:24:28','Hutang',NULL,NULL),(10,22,'INV-000123','2026-09-11','Cash',NULL,NULL,NULL,0,7550000.00,'2026-09-11 16:25:20','2026-09-11 16:25:20','Lunas','2026-09-10 17:00:00',NULL);
/*!40000 ALTER TABLE `pembelian_bahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembelian_bahan_detail`
--

DROP TABLE IF EXISTS `pembelian_bahan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembelian_bahan_detail` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pembelian` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) DEFAULT 0.00,
  `potongan` decimal(15,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_bahan` (`id_bahan`),
  KEY `id_pembelian` (`id_pembelian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembelian_bahan_detail`
--

LOCK TABLES `pembelian_bahan_detail` WRITE;
/*!40000 ALTER TABLE `pembelian_bahan_detail` DISABLE KEYS */;
INSERT INTO `pembelian_bahan_detail` VALUES (2,3,1,5.00,1120000.00,0.00,0.00,5600000.00),(5,5,4,1.00,265000.00,0.00,0.00,265000.00),(6,5,10,1.00,3500000.00,0.00,0.00,3500000.00),(9,8,11,2.00,650000.00,0.00,0.00,1300000.00),(10,8,12,4.00,395000.00,0.00,0.00,1580000.00),(11,8,3,4.00,258000.00,0.00,0.00,1032000.00),(12,9,6,1.00,650000.00,0.00,0.00,650000.00),(13,9,3,1.00,350000.00,0.00,0.00,350000.00),(14,10,1,5.00,1384000.00,0.00,0.00,6920000.00),(15,10,6,1.00,630000.00,0.00,0.00,630000.00);
/*!40000 ALTER TABLE `pembelian_bahan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produk` (
  `id_produk` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `barcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_mesin` bigint(20) unsigned DEFAULT NULL,
  `metode_hitung` enum('ukuran','quantity') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_panjang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_lebar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
INSERT INTO `produk` VALUES (1,'FLS280 3.2m',NULL,2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:27:14','2026-09-11 13:27:14'),(2,'TC Direct Print 120cm',NULL,4,'ukuran',3.60,120.00,300.00,'-','aktif','2026-09-11 13:27:25','2026-09-11 13:27:25'),(3,'Sublime Paper',NULL,6,'ukuran',0.16,155.00,10.00,'-','aktif','2026-09-11 11:23:14','2026-09-11 11:23:14'),(4,'AC 260gram',NULL,3,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-11 13:42:17','2026-09-11 13:42:17'),(5,'AC 210gram',NULL,3,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-11 13:42:10','2026-09-11 13:42:10'),(7,'STIKER CAMEL 155','CML',1,'ukuran',0.31,155.00,20.00,'MINIMAL CETAK MENGIKUTI ATURAN MINIMAL ORDER','aktif','2026-03-03 10:10:05','2026-09-12 16:39:49'),(8,'STIKER EXILO 153',NULL,1,'ukuran',0.31,153.00,20.00,'-','aktif','2026-03-06 01:47:06','2026-09-12 16:39:21'),(9,'STIKER INFLEX 153',NULL,1,'ukuran',0.31,155.00,20.00,'-','aktif','2026-03-06 01:47:50','2026-09-12 16:38:57'),(18,'FLS280 2.2m','FLS2.2',2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:43:20','2026-09-11 13:43:20'),(19,'FLS280 2.6m',NULL,2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:43:42','2026-09-11 13:43:42'),(20,'FLS280 1.6m',NULL,2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:43:58','2026-09-11 13:43:58'),(22,'DTF Print Pet Film','DTF-PF',5,'ukuran',1.00,100.00,100.00,'-','aktif','2026-09-12 14:13:16','2026-09-12 14:13:16'),(23,'BONTAX','BTX',3,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-12 14:14:09','2026-09-12 14:14:09'),(26,'DTF - A3','DTFA3',5,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-12 16:43:41','2026-09-12 16:43:41'),(27,'DTF - A4','DTFA4',5,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-12 16:44:04','2026-09-12 16:44:04'),(28,'DTF - A5','DTFA5',5,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-12 16:44:26','2026-09-12 16:44:26'),(29,'LOGO 10cm','LOGO10',5,'quantity',0.00,0.00,0.00,'-','aktif','2026-09-12 16:44:53','2026-09-12 16:44:53');
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_finishing`
--

DROP TABLE IF EXISTS `produk_finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produk_finishing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned DEFAULT NULL,
  `id_finishing` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_finishing`
--

LOCK TABLES `produk_finishing` WRITE;
/*!40000 ALTER TABLE `produk_finishing` DISABLE KEYS */;
/*!40000 ALTER TABLE `produk_finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id_role` bigint(20) unsigned NOT NULL,
  `id_menu` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_role`,`id_menu`),
  KEY `role_permissions_id_menu_foreign` (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(1,39),(1,40),(1,44),(1,45),(1,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,52),(1,53),(2,7),(2,12),(2,23),(2,24),(2,26),(2,41),(2,42),(3,3),(3,7),(3,11),(3,12),(3,16),(3,24),(3,29),(3,34),(3,36),(3,38),(3,48),(4,21),(4,22),(4,23),(5,1),(5,17),(5,33),(5,46),(5,53);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_role` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrator'),(2,'Operator Desain'),(3,'Kasir'),(4,'Operator Produksi'),(5,'Gudang');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satuan`
--

DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satuan` (
  `id_satuan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nilai_konversi` decimal(10,2) NOT NULL,
  `tipe_satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Barang',
  PRIMARY KEY (`id_satuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuan`
--

LOCK TABLES `satuan` WRITE;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES (7,'kg',1.00,'Barang'),(8,'gram',1.00,'Barang'),(9,'liter',1.00,'Barang'),(10,'ml',1.00,'Barang'),(11,'pcs',1.00,'Barang'),(12,'box',1.00,'Barang'),(13,'cm',1.00,'Panjang'),(14,'m',1.00,'Panjang'),(17,'roll',1.00,'Barang'),(18,'lembar',1.00,'Barang'),(20,'rim',1.00,'Barang'),(21,'m2',1.00,'Luas'),(22,'cm2',1.00,'Luas');
/*!40000 ALTER TABLE `satuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  KEY `sessions_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('06flrjtQxdrEE1cSy0zhN0ivX1bFVBVZ2MbE6ogm',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoidTBQRElUOGkzUnNZWlFPclJMY01BUTBiS2lLU205MUdWTXR6Zm12cSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201508),('4DtVreQpFrhWUE7t9ZZruwv50MKsP4SvRr5GhItv',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoid1JZOEhHQXh2dFlmdGxwcUxVejVRYktzN0s5T2VoSVlGZTdDZDVMUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790179753),('7sKVfq14YqozmNu6AHM4NyccnEGNdPVhJYlk2si9',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoieklKWWpFeTlybkZzMHRSdDQ5MDJOQmVoTGtMMUhDMjNwQ3lhejZBQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201506),('8JXnikO8IZo4H7YnihJBl2tBkj4P6rvySjZAACFY',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ0laSU5oUTNmekY3TGN2ZlFkYmNSNVU5RzhQVE52eUM3enVuaWxMRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('amI3if3pAF9MYfAFNprZsimsaEXO5FpdZ4ucuqkV',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiS1BTUk5tMUZ1aVl3RzIwa3JSejhtZjNrbGMxRzFtS25jTTRqSkpsUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201508),('c4Q7RS1zEpgrwlpgGGVpVCaSZvLQGkmNO8tnBPmV',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSlYxeDRaOWhYRER6RUxqZFBZNG4xTjk2cmlxekpNR0JGQVhLdWxXZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201508),('CaDFzR20nduVBRa3OPL3NA8z4gL01euQsnXEzRYD',18,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Safari/537.36 MSIX','YTo1OntzOjY6Il90b2tlbiI7czo0MDoicEhGT1lwTXROZVlDbFRzSGxJZGlPdjhnaXNsQ0ZRV2FmSDAyWkdOUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly9sb2NhbGhvc3Q6ODc2NS9rYXNpci9jaGVjay1wZW1iYXlhcmFuIjtzOjU6InJvdXRlIjtzOjY6Imthc2lyLiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE4O3M6MzoidXJsIjthOjA6e319',1790181028),('dSyhbwwFfvEcnAhyPcU3bzxPEkKZCWe9DDCAXbOu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiT291WUlaUzQyU25kR0hEcFRoRUtITWtOVFg1NW1kTElEM1BGaG1reCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('ep6CEdUnAiH0g6D1Cm0C9Sg9feLvcTj3KWLxQB9I',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiendWU1Rlc2ViY1BjSWxMdHN3SHFTZkFuUDlqa1hiU1dLR0V6aXZMayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201508),('G3sN9Y8YrHYef61fiayaKVXb6mWwO57qe1onQ5oc',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiOG1ad3hhS1J6c2lyRDFKVkNUVElwS2NVQ1ZEVExldFNXajF3VG5wbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201508),('HNT9nu21r4waCsHQVlUMF3DxtbybqLlPsJX57h6u',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiaHVzVFNZa1ZxVlg1TUVTWDVuYVFxNnVNZFROdjRBeE5wZkRzc1U3eCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201508),('iK2a7tdWzHYvepqM6wbMv3YJNf7Veta69Geidmx0',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoidDdNUGd3cEJiR1pDRnc5aWtqeTA4SGRLWTJCUks4UDU3SWc0MXJiWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201506),('jCb3GcS3OL4oAG23Zf4DazKhGWbsGQw2G7OaMSTc',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMzhDOGJnbTlkNVBxV25PeWZMMUZFOVMwMnF0ZmVIWlFLeVNZUHFJUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('jt24mTDpzpOUAUAPql8sE53xzK6px7EvIlPF5PyV',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiV0VSOGFnVmJ2OGZMb1BxcW9zUElEY1BLVFJpSEZzUDVYT05JOFdpZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('kLiITUxpLLO2KhGUhLAvFQAmd0BgWh4akaKP8Hwx',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoicUhKNmlNb2QwaEN6WEJZTHkxaDNwdU5wNUtSaHA2Rk14TUk0c3hEdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('LhoCUqZ4I0GDK6YP3GNW4xe1EcVwSVGZAxGEhcdy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoicjZiYzhydHFHNXFWcURZQnA2blRxbnRNZ0pBZTR3NzloUkljMDduUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('RiHCXJ0CvmYqVQCVJWwOHFBEyZePrWGfxTe4Tgq3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiUFlXaVhJUVE1am1QUU1pZnl3eW5mSU9CN3BRcDl3b3lRTlRMS1lHbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201506),('S8de10zYSFWeAErYWSVyAFgEZxv7mRbKZjANFRmg',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoia2JBTXVvck9iY0VZWktSUjB4NjFwRTc4TGpKSG9TVlFvYUxSdmRocyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly9sb2NhbGhvc3QvcGVyY2V0YWthbi1hcHAvcHVibGljL2thc2lyL2NoZWNrLXBlbWJheWFyYW4iO3M6NToicm91dGUiO3M6Njoia2FzaXIuIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1790201519),('thACDhtojlk89Kv856oOcJ0tQgPxe2wqXTQCx6U4',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoianNSUlJ3QmhiTTl0eDd1MHVqZmc2bEF0WjF2MXlTMjNWVmxUeXFhMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201507),('WEll1M9pW1jAd0srX783b508CI2iN8qKdLW37pka',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUzhZVXM3enFib09ZS1NnRXZYRjAwUkRrbWdpV0VNam5rU2hTYUN3ayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly9sb2NhbGhvc3QvcGVyY2V0YWthbi1hcHAvcHVibGljL2thc2lyL2NoZWNrLXBlbWJheWFyYW4iO3M6NToicm91dGUiO3M6Njoia2FzaXIuIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1790180699),('wpLyc7V0UponuguiGWsgT4ths5cKESzk5Gs2la7S',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiektzekFKWlpDYnBFSDg4MDhrVGtzbVJtMzZJZE00V0FwNE9TTlgxRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201506),('XzxhtoYC9zYP5q4lwrAGKHGX5Cgkg5g8EMrei9wo',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.7032.0 Chrome/152.0.7977.130 Electron/44.4.3 Safari/537.36 MSIX','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMGtxdzZqZVliQ3BhVEdPTm1xcG5QZUczNVdkQmt2Mk9YakJjaHdMVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790201506);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_harga`
--

DROP TABLE IF EXISTS `setting_harga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_harga` (
  `id_harga` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `id_golongan` bigint(20) unsigned NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) DEFAULT NULL,
  `harga_jual` decimal(15,2) NOT NULL,
  `id_satuan` bigint(20) unsigned DEFAULT NULL,
  `qty_berlaku` int(11) DEFAULT 1,
  `id_satuan_berlaku` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_harga`),
  KEY `fk_setting_harga_satuan` (`id_satuan`),
  KEY `fk_setting_harga_satuan_berlaku` (`id_satuan_berlaku`),
  KEY `setting_harga_id_golongan_foreign` (`id_golongan`),
  KEY `setting_harga_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_harga`
--

LOCK TABLES `setting_harga` WRITE;
/*!40000 ALTER TABLE `setting_harga` DISABLE KEYS */;
INSERT INTO `setting_harga` VALUES (30,3,1,1.00,9999999.00,30000.00,NULL,1,NULL,'2026-03-07 20:10:14','2026-09-11 22:51:46'),(31,3,2,1.00,9999999.00,30000.00,NULL,1,NULL,'2026-03-07 20:10:14','2026-09-11 22:51:46'),(32,3,3,1.00,9999999.00,30000.00,NULL,1,NULL,'2026-03-07 20:10:14','2026-09-11 22:51:46'),(85,2,1,1.00,9999999.00,20000.00,21,1,21,'2026-03-24 13:42:48','2026-09-11 22:51:46'),(86,2,2,1.00,224.00,18000.00,21,1,21,'2026-03-24 13:42:48','2026-09-11 22:51:46'),(87,2,2,224.00,448.00,15000.00,21,1,21,'2026-03-24 13:42:48','2026-09-11 22:51:46'),(88,1,1,1.00,999999.00,18000.00,21,1,21,'2026-03-24 13:51:25','2026-09-11 22:51:46'),(89,1,2,1.00,100.00,16000.00,21,1,21,'2026-03-24 13:51:25','2026-09-11 22:51:46'),(90,1,3,1.00,9999999.00,15000.00,21,1,21,'2026-03-24 13:51:25','2026-09-11 22:51:46'),(126,4,1,1.00,10.00,5000.00,18,1,18,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(127,4,1,11.00,50.00,4500.00,18,1,18,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(143,5,1,10.00,49.00,4850.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(144,5,1,50.00,149.00,4500.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(145,5,1,150.00,500.00,4000.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(157,7,2,1.00,NULL,67000.00,NULL,1,NULL,'2026-09-11 08:09:32','2026-09-11 22:51:46'),(161,7,3,1.00,NULL,60000.00,NULL,1,NULL,'2026-09-11 08:25:31','2026-09-11 22:51:46'),(164,8,2,1.00,NULL,47000.00,NULL,1,NULL,'2026-09-11 09:24:33','2026-09-11 22:51:46'),(166,9,2,1.00,NULL,77000.00,NULL,1,NULL,'2026-09-11 09:24:54','2026-09-11 22:51:46'),(168,20,1,0.01,224.00,20000.00,NULL,1,NULL,'2026-09-11 14:04:08','2026-09-11 22:51:46'),(170,20,1,225.00,448.00,16000.00,NULL,1,NULL,'2026-09-11 14:04:57','2026-09-12 17:02:53'),(173,18,1,0.01,154.00,20000.00,NULL,1,NULL,'2026-09-11 14:19:40','2026-09-11 22:51:46'),(174,18,1,155.00,308.00,16000.00,NULL,1,NULL,'2026-09-11 14:19:56','2026-09-12 17:03:40'),(175,18,2,0.01,154.00,16000.00,NULL,1,NULL,'2026-09-11 14:20:05','2026-09-11 22:51:46'),(176,18,2,155.00,308.00,13000.00,NULL,1,NULL,'2026-09-11 14:20:19','2026-09-11 22:51:46'),(177,19,1,0.01,182.00,20000.00,NULL,1,NULL,'2026-09-11 14:20:38','2026-09-11 22:51:46'),(178,19,1,183.00,366.00,15000.00,NULL,1,NULL,'2026-09-11 14:20:53','2026-09-11 22:51:46'),(179,20,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:00','2026-09-11 22:51:46'),(180,18,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:10','2026-09-11 22:51:46'),(181,19,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:18','2026-09-11 22:51:46'),(182,1,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:24','2026-09-11 22:51:46'),(183,7,1,0.01,NULL,70000.00,NULL,1,NULL,'2026-09-11 14:25:39','2026-09-11 22:51:46'),(184,8,1,0.01,NULL,50000.00,NULL,1,NULL,'2026-09-11 14:25:53','2026-09-11 22:51:46'),(185,9,1,0.01,NULL,80000.00,NULL,1,NULL,'2026-09-11 14:26:04','2026-09-11 22:51:46'),(186,20,7,0.01,224.00,13000.00,NULL,1,NULL,'2026-09-12 17:02:20','2026-09-12 17:02:20'),(187,20,4,0.01,224.00,20000.00,NULL,1,NULL,'2026-09-12 17:02:27','2026-09-12 17:02:27'),(188,20,2,0.01,224.00,16000.00,NULL,1,NULL,'2026-09-12 17:02:41','2026-09-12 17:02:41'),(189,18,7,0.01,224.00,13000.00,NULL,1,NULL,'2026-09-12 17:03:13','2026-09-12 17:03:13'),(190,18,4,0.01,224.00,20000.00,NULL,1,NULL,'2026-09-12 17:03:21','2026-09-12 17:03:21'),(191,23,2,1.00,10.00,10000.00,NULL,1,NULL,'2026-09-12 17:04:52','2026-09-12 17:04:52'),(192,22,1,1.00,60.00,45000.00,NULL,1,NULL,'2026-09-16 11:41:39','2026-09-16 11:41:39'),(193,22,2,1.00,60.00,45000.00,NULL,1,NULL,'2026-09-16 11:41:48','2026-09-16 11:41:48'),(210,5,2,1.00,9.00,5000.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(211,5,2,10.00,49.00,4850.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(212,5,2,50.00,149.00,4500.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(213,5,2,150.00,500.00,4000.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(214,5,3,1.00,9.00,5000.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(215,5,3,10.00,49.00,4850.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(216,5,3,50.00,149.00,4500.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(217,5,3,150.00,500.00,4000.00,NULL,1,NULL,'2026-09-09 17:18:02','2026-09-18 06:49:21'),(218,4,2,1.00,10.00,5000.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(219,4,2,11.00,50.00,4500.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(220,4,3,1.00,10.00,5000.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(221,4,3,11.00,50.00,4500.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(222,4,4,1.00,10.00,5000.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(223,4,4,11.00,50.00,4500.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(224,4,6,1.00,10.00,5000.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(225,4,6,11.00,50.00,4500.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(226,4,7,1.00,10.00,5000.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08'),(227,4,7,11.00,50.00,4500.00,NULL,1,NULL,'2026-09-21 01:28:08','2026-09-21 01:28:08');
/*!40000 ALTER TABLE `setting_harga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_opname_details`
--

DROP TABLE IF EXISTS `stock_opname_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_opname_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_stock_opname` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `stok_sistem` decimal(15,4) NOT NULL,
  `stok_fisik` decimal(15,4) DEFAULT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_opname_details_id_stock_opname_id_bahan_unique` (`id_stock_opname`,`id_bahan`),
  KEY `stock_opname_details_id_bahan_foreign` (`id_bahan`),
  CONSTRAINT `stock_opname_details_id_bahan_foreign` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  CONSTRAINT `stock_opname_details_id_stock_opname_foreign` FOREIGN KEY (`id_stock_opname`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_opname_details`
--

LOCK TABLES `stock_opname_details` WRITE;
/*!40000 ALTER TABLE `stock_opname_details` DISABLE KEYS */;
INSERT INTO `stock_opname_details` VALUES (26,2,1,3.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(27,2,2,1.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(28,2,3,237.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(29,2,4,250.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(30,2,6,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(31,2,7,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(32,2,8,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(33,2,9,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(34,2,10,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(35,2,11,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(36,2,12,245.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(37,2,13,5.0000,4.0000,'Sudah terpakai','2026-09-23 16:21:02','2026-09-23 16:22:27'),(38,2,27,5.0000,2.0000,'Sudah terpakai','2026-09-23 16:21:02','2026-09-23 16:22:27'),(39,2,28,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(40,2,29,1.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(41,2,30,2.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(42,2,31,1.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(43,2,32,1.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(44,2,33,1.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(45,2,34,320.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(46,2,35,315.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(47,2,36,750.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(48,2,37,400.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(49,2,38,250.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27'),(50,2,39,350.0000,NULL,NULL,'2026-09-23 16:21:02','2026-09-23 16:22:27');
/*!40000 ALTER TABLE `stock_opname_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_opnames`
--

DROP TABLE IF EXISTS `stock_opnames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_opnames` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor` varchar(50) NOT NULL,
  `id_gudang` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Draft','Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Draft',
  `catatan` text DEFAULT NULL,
  `id_user_pembuat` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_opnames_nomor_unique` (`nomor`),
  KEY `stock_opnames_id_gudang_foreign` (`id_gudang`),
  KEY `stock_opnames_id_user_pembuat_foreign` (`id_user_pembuat`),
  CONSTRAINT `stock_opnames_id_gudang_foreign` FOREIGN KEY (`id_gudang`) REFERENCES `gudang` (`id_gudang`) ON DELETE SET NULL,
  CONSTRAINT `stock_opnames_id_user_pembuat_foreign` FOREIGN KEY (`id_user_pembuat`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_opnames`
--

LOCK TABLES `stock_opnames` WRITE;
/*!40000 ALTER TABLE `stock_opnames` DISABLE KEYS */;
INSERT INTO `stock_opnames` VALUES (2,'SO-20260923-002',1,'2026-09-23','Disetujui','Opname manual',1,'2026-09-23 16:21:02','2026-09-23 16:23:51');
/*!40000 ALTER TABLE `stock_opnames` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier` (
  `id_supplier` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kontak` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'ALEA GRAFIKA','Surabaya','082212454878'),(2,'CV. MANDIRI JAYA','Surabaya','082124578782'),(3,'PT. CELLO GRAFIKA','Surabaya','08212487845'),(4,'PT. SARI AGUNG','Jalan Kedung Cuwek, Surabaya','-'),(5,'MAS Anas Mejuar','Surabaya','-'),(6,'Mitra Printing Solution','Surabaya','-'),(7,'COLORLINK','Surabaya','-');
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi` (
  `id_transaksi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_nota` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_order` enum('Reguler','PO') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Reguler',
  `no_po` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_konsumen` bigint(20) unsigned NOT NULL,
  `nama_konsumen_manual` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_user_kasir` bigint(20) unsigned NOT NULL,
  `tgl_order` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_bayar` decimal(15,2) NOT NULL,
  `status_pembayaran` enum('Lunas','DP','Belum Bayar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status_nota` enum('Aktif','Dibatalkan') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `alasan_batal_nota` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_dibatalkan_nota` timestamp NULL DEFAULT NULL,
  `id_user_pembatal_nota` bigint(20) unsigned DEFAULT NULL,
  `status_collecting` enum('Belum Diambil','Sudah Diambil') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Belum Diambil',
  `tgl_collecting` timestamp NULL DEFAULT NULL,
  `id_user_collecting` bigint(20) unsigned DEFAULT NULL,
  `metode_pembayaran` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `transaksi_nomor_nota_unique` (`nomor_nota`),
  KEY `transaksi_id_konsumen_foreign` (`id_konsumen`),
  KEY `transaksi_id_user_collecting_foreign` (`id_user_collecting`),
  KEY `transaksi_id_user_kasir_foreign` (`id_user_kasir`),
  KEY `transaksi_id_user_pembatal_nota_foreign` (`id_user_pembatal_nota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (2,'INV-20260226-001','Reguler',NULL,1,NULL,1,'2026-02-26 13:08:44',161600.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,NULL,NULL,'2026-02-26 14:47:15'),(3,'INV-20260226-002','Reguler',NULL,2,NULL,1,'2026-02-26 14:48:21',790400.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-02-26 16:14:38'),(4,'INV-20260226-003','Reguler',NULL,3,NULL,1,'2026-02-26 16:02:17',405000.00,'DP','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-11 15:43:02'),(5,'INV-20260227-001','Reguler',NULL,2,NULL,1,'2026-02-27 11:12:36',25000.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-11 15:43:13'),(7,'INV-20260227-002','Reguler',NULL,2,NULL,1,'2026-02-27 15:59:43',180000.00,'DP','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-18 01:28:47'),(10,'INV-20260909-001','Reguler',NULL,1,NULL,1,'2026-09-09 15:03:24',0.00,'Belum Bayar','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-17 12:49:49'),(48,'INV-20260911-001','Reguler',NULL,2,NULL,1,'2026-09-11 11:33:31',20000.00,'Belum Bayar','Aktif',NULL,NULL,NULL,'Sudah Diambil','2026-09-11 16:34:21',1,NULL,NULL,'2026-09-11 16:50:23'),(49,'INV-20260916-001','Reguler',NULL,1,NULL,35,'2026-09-16 14:52:26',25000.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Deposit',NULL,NULL);
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_detail`
--

DROP TABLE IF EXISTS `transaksi_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi_detail` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaksi` bigint(20) unsigned NOT NULL,
  `id_produk` bigint(20) unsigned DEFAULT NULL,
  `id_jasa` bigint(20) unsigned DEFAULT NULL,
  `id_bahan` bigint(20) unsigned DEFAULT NULL,
  `id_finishing` bigint(20) unsigned DEFAULT NULL,
  `biaya_finishing` decimal(15,2) DEFAULT 0.00,
  `biaya_cetak` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_desain` decimal(15,2) NOT NULL DEFAULT 0.00,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `transaksi_detail_id_bahan_foreign` (`id_bahan`),
  KEY `transaksi_detail_id_finishing_foreign` (`id_finishing`),
  KEY `transaksi_detail_id_jasa_foreign` (`id_jasa`),
  KEY `transaksi_detail_id_produk_foreign` (`id_produk`),
  KEY `transaksi_detail_id_transaksi_foreign` (`id_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_detail`
--

LOCK TABLES `transaksi_detail` WRITE;
/*!40000 ALTER TABLE `transaksi_detail` DISABLE KEYS */;
INSERT INTO `transaksi_detail` VALUES (1,2,1,NULL,NULL,NULL,0.00,0.00,0.00,'menu',NULL,310.00,100.00,2,0,'Normal','Standar',NULL,NULL,111600.00,0.00,0.00,0.00,'Desain',NULL,NULL),(2,2,4,NULL,NULL,NULL,0.00,0.00,0.00,'menu',NULL,0.00,0.00,10,0,'Normal','Standar',NULL,NULL,50000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(3,3,1,NULL,NULL,NULL,0.00,0.00,0.00,'toko',NULL,100.00,500.00,3,0,'Normal','Standar',NULL,NULL,240000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(4,3,2,NULL,NULL,NULL,0.00,0.00,0.00,'umbul-umbul',NULL,120.00,550.00,3,0,'Normal','Standar',NULL,NULL,356400.00,0.00,0.00,0.00,'Desain',NULL,NULL),(5,3,5,NULL,NULL,NULL,0.00,0.00,0.00,'Tanpa File',NULL,0.00,0.00,40,0,'Normal','Standar',NULL,NULL,194000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(6,4,1,NULL,NULL,NULL,0.00,0.00,0.00,'Jualan',NULL,400.00,250.00,1,0,'Normal','Standar',NULL,NULL,180000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(7,4,3,NULL,NULL,NULL,0.00,0.00,0.00,'sablon',NULL,150.00,500.00,1,0,'Normal','Standar',NULL,NULL,225000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(8,5,5,NULL,NULL,NULL,0.00,0.00,0.00,'takjil',NULL,0.00,0.00,5,0,'Normal','Standar',5,NULL,25000.00,0.00,0.00,0.00,'Cetak',NULL,NULL),(10,7,4,NULL,NULL,NULL,0.00,180000.00,0.00,'menu',NULL,0.00,0.00,40,0,'Normal','Standar',40,NULL,180000.00,0.00,0.00,0.00,'Selesai',NULL,NULL),(13,10,8,NULL,NULL,NULL,0.00,0.00,0.00,'Stiker gerobak',NULL,152.00,100.00,1,0,'Normal','Standar',1,NULL,0.00,0.00,0.00,0.00,'Selesai',NULL,NULL),(60,48,7,NULL,NULL,1,20000.00,0.00,0.00,'NG Colorado Bag 2026 Side 01','order_thumbnails/ix1H3ewWfrIw9MLZZQGojBU4V07GqnUKPBoEV0EZ.jpg',155.00,140.00,1,0,'Normal','Custom',2,NULL,20000.00,0.00,0.00,165390.00,'Selesai',NULL,NULL),(61,49,4,NULL,NULL,NULL,0.00,25000.00,0.00,'QA Audit Desainer - AC 260gram','order_thumbnails/spzzfhY9ijnUFWfmqtcFtnCYWuTWmpOeuhvBCHSY.jpg',0.00,0.00,5,0,'Normal','Standar',NULL,NULL,25000.00,0.00,2810.58,25000.00,'Antre',NULL,NULL);
/*!40000 ALTER TABLE `transaksi_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_user` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_karyawan` bigint(20) unsigned NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_role` bigint(20) unsigned NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_id_karyawan_foreign` (`id_karyawan`),
  KEY `users_id_role_foreign` (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','$2y$12$G2HMSNe1XosO.y0b/Oi9BeTRrhba3WqWG8LGFBrwMdrJ0gL6ISx7m',1,NULL,NULL,NULL),(4,6,'rehan','$2y$12$4E7JRf2FEslrjHLz2llGV.HkHrndxHcZw7BexJ.AiPdo.lpiT0xFa',2,NULL,'2026-02-25 14:36:24','2026-09-10 01:29:20'),(5,7,'rahman','$2y$12$qniJ544jbTScKyUIDGlTD.InXuVXN0JERQ/2epuOsC0Uiv7jzga0u',2,NULL,'2026-02-25 14:36:40','2026-02-25 14:36:40'),(14,8,'opepson','$2y$12$d6392M3gnUYGyL5Z5duul.uKzA2t39YUgN1PbWcXIjj87q/.fz6x2',4,NULL,'2026-09-11 04:04:08','2026-09-11 04:04:08'),(18,1,'_browsercheck','$2y$12$7/iR/m6YKxH69EC.eiQ3GuIshcOJdHraLt.bNEGXqcWEsaVucchdi',1,NULL,'2026-09-23 16:09:07','2026-09-23 16:09:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `worksheet`
--

DROP TABLE IF EXISTS `worksheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worksheet` (
  `id_worksheet` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_worksheet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worksheet`
--

LOCK TABLES `worksheet` WRITE;
/*!40000 ALTER TABLE `worksheet` DISABLE KEYS */;
/*!40000 ALTER TABLE `worksheet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'percetakan_erp'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24  5:40:03
