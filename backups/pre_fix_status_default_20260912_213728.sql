-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: percetakan_erp
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
  `email` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'Orion Grafika',NULL,NULL,NULL,NULL,'2026-09-12 12:23:14','2026-09-12 12:23:14');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bag_akun`
--

DROP TABLE IF EXISTS `bag_akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bag_akun` (
  `id_akun` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_akun` varchar(20) NOT NULL,
  `nama_akun` varchar(100) NOT NULL,
  `tipe_akun` enum('Aset','Kewajiban','Modal','Pendapatan','Beban') NOT NULL,
  `saldo_normal` enum('Debet','Kredit') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_akun`),
  UNIQUE KEY `bag_akun_kode_akun_unique` (`kode_akun`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bag_akun`
--

LOCK TABLES `bag_akun` WRITE;
/*!40000 ALTER TABLE `bag_akun` DISABLE KEYS */;
INSERT INTO `bag_akun` VALUES (1,'1000','Kas','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(2,'1010','Bank','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(3,'1100','Piutang Usaha','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(4,'1200','Persediaan Bahan Baku','Aset','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(5,'2000','Hutang Usaha','Kewajiban','Kredit',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(6,'3000','Modal','Modal','Kredit',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(7,'4000','Pendapatan Penjualan','Pendapatan','Kredit',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(8,'5000','Harga Pokok Penjualan','Beban','Debet',1,'2026-09-10 07:14:45','2026-09-10 07:14:45'),(9,'2100','Hutang Deposit Konsumen','Kewajiban','Kredit',1,'2026-09-10 08:28:38','2026-09-10 08:28:38'),(10,'4100','Pendapatan Jasa','Pendapatan','Kredit',1,'2026-09-10 08:28:38','2026-09-10 08:28:38'),(12,'4200','Pendapatan Lain-lain','Pendapatan','Kredit',1,'2026-09-11 17:42:29','2026-09-11 17:42:29'),(13,'5100','Biaya Lain-lain','Beban','Debet',1,'2026-09-11 17:42:29','2026-09-11 17:42:29');
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
  `kode_bahan` varchar(50) NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
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
  `satuan_stok` varchar(20) NOT NULL,
  `status` varchar(20) DEFAULT '''aktif''',
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bahan_baku`
--

LOCK TABLES `bahan_baku` WRITE;
/*!40000 ALTER TABLE `bahan_baku` DISABLE KEYS */;
INSERT INTO `bahan_baku` VALUES (1,'FLS280-320','FRONTLITE 280gr 3.2m',2,3,2,2,3.0000,0.000,17,21,224.000,3.200,70.000,1384000.00,'roll','aktif','2026-09-11 16:25:20',NULL),(2,'TC','TC Textile Import',4,5,2,2,1.0000,0.000,17,21,271.200,1.200,226.000,0.00,'roll','aktif','2026-09-10 21:53:06',NULL),(3,'AC260','AC 260gr',3,4,NULL,NULL,3.9900,0.000,20,18,500.000,NULL,NULL,281057.64,'rim','aktif','2026-09-11 16:24:28',NULL),(4,'AC210','AC 210gr',3,4,NULL,NULL,2.0000,0.000,20,18,500.000,NULL,NULL,265000.00,'rim','aktif','2026-09-10 15:22:30',NULL),(5,'SB','SUBLIME PAPER',6,6,NULL,NULL,5.0000,0.000,17,21,1.000,NULL,NULL,0.00,'Roll','nonaktif','2026-09-11 18:21:05',NULL),(6,'DTF','PET FILM DTF',5,7,NULL,NULL,2.0000,0.000,17,21,62.000,NULL,NULL,640000.00,'roll','aktif','2026-09-11 16:25:20',NULL),(7,'ST-CML','STIKER CAMEL',1,2,2,2,2.0000,0.000,17,21,77.500,1.550,50.000,0.00,'roll','aktif','2026-09-11 16:18:27',NULL),(8,'ST-INF','STIKER INFLEX',1,2,2,2,2.0000,0.000,17,21,77.500,1.550,50.000,0.00,'roll','aktif','2026-09-11 16:18:40',NULL),(9,'ST-EXI','STIKER EXILO',1,2,2,2,2.0000,0.000,17,21,76.500,1.530,50.000,0.00,'roll','aktif','2026-09-11 16:18:48',NULL),(10,'CNV','CANVAS',1,2,2,2,2.0000,0.000,17,21,60.000,1.200,50.000,3500000.00,'roll','aktif','2026-09-11 09:10:21',NULL),(11,'PHO','PHOTOPAPER',1,2,2,2,2.0000,0.000,17,21,45.000,0.900,50.000,650000.00,'roll','aktif','2026-09-11 09:50:53',NULL),(12,'BTX','BONTAX',3,4,2,2,5.0000,0.000,20,18,500.000,NULL,NULL,395000.00,'rim','aktif','2026-09-11 09:50:53',NULL),(13,'PPS','Paper Sublime',6,6,2,2,5.0000,1.000,17,14,160.000,1.600,100.000,0.00,'roll','aktif','2026-09-11 17:36:59',NULL),(27,'FLS280-220','FRONTLITE 280gr 2.2m',2,3,2,2,5.0000,0.000,17,21,154.000,2.200,70.000,0.00,'roll','aktif','2026-09-11 16:17:42',NULL),(28,'FLS280-260','FRONTLITE 280gr 2.6m',2,3,2,2,2.0000,0.000,17,21,182.000,2.600,70.000,0.00,'roll','aktif','2026-09-11 16:17:53',NULL),(29,'FLS280-160','FRONTLITE 280gr 1.6m',2,3,2,2,1.0000,0.000,17,21,112.000,1.600,70.000,0.00,'roll','aktif','2026-09-11 09:57:19',NULL),(30,'FLS280-110','FRONTLITE 280gr 1.1m',2,3,2,2,2.0000,0.000,17,21,77.000,1.100,70.000,0.00,'roll','aktif','2026-09-11 16:17:31',NULL);
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
  `nama_bank` varchar(50) NOT NULL,
  `nomor_rekening` varchar(50) NOT NULL,
  `atas_nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id_bank`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `cara_hitung_pemakaian` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_bom`),
  KEY `bom_detail_id_bahan_foreign` (`id_bahan`),
  KEY `bom_detail_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bom_detail`
--

LOCK TABLES `bom_detail` WRITE;
/*!40000 ALTER TABLE `bom_detail` DISABLE KEYS */;
INSERT INTO `bom_detail` VALUES (20,3,13,'(P*L)/10000','2026-09-11 11:23:14','2026-09-11 11:23:14'),(25,8,9,'(P*L)/10000','2026-09-11 13:21:29','2026-09-11 13:21:29'),(26,7,7,'(P*L)/10000','2026-09-11 13:21:54','2026-09-11 13:21:54'),(27,9,8,'(P*L)/10000','2026-09-11 13:25:45','2026-09-11 13:25:45'),(28,1,1,'(P*L)/10000','2026-09-11 13:27:14','2026-09-11 13:27:14'),(29,2,2,'(P*L)/10000','2026-09-11 13:27:25','2026-09-11 13:27:25'),(30,5,4,'Q','2026-09-11 13:42:10','2026-09-11 13:42:10'),(31,4,3,'Q','2026-09-11 13:42:17','2026-09-11 13:42:17'),(32,18,27,'(P*L)/10000','2026-09-11 13:43:20','2026-09-11 13:43:20'),(33,19,28,'(P*L)/10000','2026-09-11 13:43:42','2026-09-11 13:43:42'),(34,20,29,'(P*L)/10000','2026-09-11 13:43:58','2026-09-11 13:43:58'),(35,21,6,'(P*L)/10000','2026-09-12 14:11:34','2026-09-12 14:11:34'),(36,22,6,'(P*L)/10000','2026-09-12 14:13:16','2026-09-12 14:13:16'),(37,23,12,'Q','2026-09-12 14:14:09','2026-09-12 14:14:09');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-app_settings_current','O:21:\"App\\Models\\AppSetting\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"app_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:1;s:13:\"nama_aplikasi\";s:13:\"Orion Grafika\";s:6:\"alamat\";N;s:7:\"telepon\";N;s:5:\"email\";N;s:9:\"logo_path\";N;s:10:\"created_at\";s:19:\"2026-09-12 19:23:14\";s:10:\"updated_at\";s:19:\"2026-09-12 19:23:14\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:1;s:13:\"nama_aplikasi\";s:13:\"Orion Grafika\";s:6:\"alamat\";N;s:7:\"telepon\";N;s:5:\"email\";N;s:9:\"logo_path\";N;s:10:\"created_at\";s:19:\"2026-09-12 19:23:14\";s:10:\"updated_at\";s:19:\"2026-09-12 19:23:14\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:13:\"nama_aplikasi\";i:1;s:6:\"alamat\";i:2;s:7:\"telepon\";i:3;s:5:\"email\";i:4;s:9:\"logo_path\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2104575841);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
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
  `nama_finishing` varchar(100) DEFAULT NULL,
  `harga_default` decimal(15,2) DEFAULT 0.00,
  `id_satuan` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_kategori` bigint(20) DEFAULT NULL,
  `id_mesin` bigint(20) unsigned DEFAULT NULL,
  `metode_hitung` varchar(20) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id_finishing`),
  KEY `fk_finishing_mesin` (`id_mesin`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finishing`
--

LOCK TABLES `finishing` WRITE;
/*!40000 ALTER TABLE `finishing` DISABLE KEYS */;
INSERT INTO `finishing` VALUES (1,'Kisscut',20000.00,NULL,'2026-03-07 16:33:27','2026-09-10 03:20:25',NULL,1,'luas','Harga berlaku per meter persegi'),(4,'Finishing Standart',0.00,NULL,'2026-09-10 03:20:06','2026-09-10 03:20:06',NULL,2,'quantity','Mata Ayam Pojok'),(5,'Hot Laminasi Doff/Glossy',2000.00,NULL,'2026-09-11 07:39:08','2026-09-11 13:46:27',NULL,3,'quantity','Menggunakan range harga'),(6,'Mata Ayam',700.00,NULL,'2026-09-11 13:46:04','2026-09-11 13:46:04',NULL,2,'quantity','Mata ayam (pcs)');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_golongan` varchar(50) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  PRIMARY KEY (`id_golongan`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_gudang` varchar(50) NOT NULL,
  `alamat` text DEFAULT NULL,
  `penanggung_jawab` varchar(100) DEFAULT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_gudang`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `status_promo` enum('aktif','non-aktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_promo`),
  KEY `harga_promo_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_jasa` varchar(100) NOT NULL,
  `id_satuan` bigint(20) unsigned DEFAULT NULL,
  `harga_default` decimal(15,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jasa`),
  KEY `jasa_id_satuan_foreign` (`id_satuan`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jasa`
--

LOCK TABLES `jasa` WRITE;
/*!40000 ALTER TABLE `jasa` DISABLE KEYS */;
INSERT INTO `jasa` VALUES (3,'Pasang Stiker',NULL,NULL,NULL,'2026-09-10 09:00:16','2026-09-11 07:56:42');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_jenis` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jenis_bahan`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_categories`
--

LOCK TABLES `jersey_categories` WRITE;
/*!40000 ALTER TABLE `jersey_categories` DISABLE KEYS */;
INSERT INTO `jersey_categories` VALUES (1,'Jersey Futsal',0,'2026-09-12 12:27:40','2026-09-12 13:40:06'),(2,'Jersey Bola',0,'2026-09-12 12:27:40','2026-09-12 13:14:21'),(3,'Jersey Printing',1,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(4,'Jersey Esports',0,'2026-09-12 12:27:40','2026-09-12 13:14:22'),(5,'Jersey Custom',0,'2026-09-12 12:27:40','2026-09-12 13:14:20');
/*!40000 ALTER TABLE `jersey_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jersey_components`
--

DROP TABLE IF EXISTS `jersey_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jersey_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_components_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_components`
--

LOCK TABLES `jersey_components` WRITE;
/*!40000 ALTER TABLE `jersey_components` DISABLE KEYS */;
INSERT INTO `jersey_components` VALUES (1,'Body',1,1,'2026-09-12 13:58:37','2026-09-12 13:58:37'),(2,'Lengan',2,1,'2026-09-12 13:58:37','2026-09-12 13:58:37'),(3,'Rib',3,1,'2026-09-12 13:58:37','2026-09-12 13:58:37'),(4,'Celana',4,1,'2026-09-12 13:58:37','2026-09-12 13:58:37'),(5,'Lengan panjang',0,1,'2026-09-12 14:29:38','2026-09-12 14:29:38');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `sleeve` enum('Pendek','Panjang') NOT NULL,
  `collar` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_order_items_jersey_order_line_id_foreign` (`jersey_order_line_id`),
  CONSTRAINT `jersey_order_items_jersey_order_line_id_foreign` FOREIGN KEY (`jersey_order_line_id`) REFERENCES `jersey_order_lines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_items`
--

LOCK TABLES `jersey_order_items` WRITE;
/*!40000 ALTER TABLE `jersey_order_items` DISABLE KEYS */;
INSERT INTO `jersey_order_items` VALUES (1,2,'Dimas','9','M','L','Pendek',NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(2,2,'Doni','4','M','L','Pendek',NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(3,2,'Rudi','6','L','L','Pendek',NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(4,2,'Kamal','7','L','L','Pendek',NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(5,2,'Rendi','30','3XL','L','Pendek',NULL,NULL,'2026-09-12 13:51:04','2026-09-12 13:51:04');
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
  KEY `jersey_order_line_materials_jersey_order_line_id_foreign` (`jersey_order_line_id`),
  CONSTRAINT `jersey_order_line_materials_jersey_order_line_id_foreign` FOREIGN KEY (`jersey_order_line_id`) REFERENCES `jersey_order_lines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_line_materials`
--

LOCK TABLES `jersey_order_line_materials` WRITE;
/*!40000 ALTER TABLE `jersey_order_line_materials` DISABLE KEYS */;
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
  `nama_barang` varchar(255) NOT NULL,
  `bahan` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `order_type` enum('quantity_only','player_list','mixed') NOT NULL DEFAULT 'quantity_only',
  `qty` int(10) unsigned NOT NULL DEFAULT 0,
  `harga_satuan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jersey_price_setting_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_order_lines_jersey_order_id_foreign` (`jersey_order_id`),
  KEY `jersey_order_lines_jersey_product_type_id_foreign` (`jersey_product_type_id`),
  KEY `jersey_order_lines_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_order_lines_jersey_price_setting_id_foreign` (`jersey_price_setting_id`),
  KEY `jersey_order_lines_jersey_material_id_foreign` (`jersey_material_id`),
  CONSTRAINT `jersey_order_lines_jersey_category_id_foreign` FOREIGN KEY (`jersey_category_id`) REFERENCES `jersey_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jersey_order_lines_jersey_material_id_foreign` FOREIGN KEY (`jersey_material_id`) REFERENCES `jersey_materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jersey_order_lines_jersey_order_id_foreign` FOREIGN KEY (`jersey_order_id`) REFERENCES `jersey_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jersey_order_lines_jersey_price_setting_id_foreign` FOREIGN KEY (`jersey_price_setting_id`) REFERENCES `jersey_price_settings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jersey_order_lines_jersey_product_type_id_foreign` FOREIGN KEY (`jersey_product_type_id`) REFERENCES `jersey_product_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_lines`
--

LOCK TABLES `jersey_order_lines` WRITE;
/*!40000 ALTER TABLE `jersey_order_lines` DISABLE KEYS */;
INSERT INTO `jersey_order_lines` VALUES (1,1,2,3,NULL,'Kaos','Brazil',NULL,'jersey_thumbnails/ymbZJyYp9HIr3Yb5J17jpSWmtjskO6XBakIEJlwB.jpg','quantity_only',10,80000.00,800000.00,1,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(2,1,2,3,NULL,'Celana','Brazil',NULL,'jersey_thumbnails/80HeV32zRipNS2OLFqWlLDSAt6876shU5RY8RlTz.png','mixed',10,80000.00,800000.00,1,'2026-09-12 13:51:04','2026-09-12 13:51:04');
/*!40000 ALTER TABLE `jersey_order_lines` ENABLE KEYS */;
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
  KEY `jersey_order_size_quantities_jersey_size_id_foreign` (`jersey_size_id`),
  CONSTRAINT `jersey_order_size_quantities_jersey_order_line_id_foreign` FOREIGN KEY (`jersey_order_line_id`) REFERENCES `jersey_order_lines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jersey_order_size_quantities_jersey_size_id_foreign` FOREIGN KEY (`jersey_size_id`) REFERENCES `jersey_sizes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_order_size_quantities`
--

LOCK TABLES `jersey_order_size_quantities` WRITE;
/*!40000 ALTER TABLE `jersey_order_size_quantities` DISABLE KEYS */;
INSERT INTO `jersey_order_size_quantities` VALUES (1,1,2,4,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(2,1,3,2,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(3,1,5,4,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(4,2,2,2,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(5,2,3,2,'2026-09-12 13:51:04','2026-09-12 13:51:04'),(6,2,6,1,'2026-09-12 13:51:04','2026-09-12 13:51:04');
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
  KEY `jersey_orders_jersey_designer_id_foreign` (`jersey_designer_id`),
  KEY `jersey_orders_created_by_foreign` (`created_by`),
  KEY `jersey_orders_id_konsumen_foreign` (`id_konsumen`),
  CONSTRAINT `jersey_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  CONSTRAINT `jersey_orders_id_konsumen_foreign` FOREIGN KEY (`id_konsumen`) REFERENCES `konsumen` (`id_konsumen`) ON DELETE SET NULL,
  CONSTRAINT `jersey_orders_jersey_designer_id_foreign` FOREIGN KEY (`jersey_designer_id`) REFERENCES `jersey_designers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_orders`
--

LOCK TABLES `jersey_orders` WRITE;
/*!40000 ALTER TABLE `jersey_orders` DISABLE KEYS */;
INSERT INTO `jersey_orders` VALUES (1,'JO-20260912-001','2026-09-12',5,'Komar Camplong','08532154542',NULL,'2026-09-17','baru',1,'Rahman',NULL,1600000.00,0.00,0.00,0.00,'Belum Bayar',NULL,1,'2026-09-12 13:51:04','2026-09-12 13:51:04');
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
  `qty_min` int(10) unsigned NOT NULL,
  `qty_max` int(10) unsigned DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jersey_price_settings_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_price_tier_idx` (`jersey_product_type_id`,`jersey_category_id`,`qty_min`,`qty_max`),
  CONSTRAINT `jersey_price_settings_jersey_category_id_foreign` FOREIGN KEY (`jersey_category_id`) REFERENCES `jersey_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jersey_price_settings_jersey_product_type_id_foreign` FOREIGN KEY (`jersey_product_type_id`) REFERENCES `jersey_product_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_price_settings`
--

LOCK TABLES `jersey_price_settings` WRITE;
/*!40000 ALTER TABLE `jersey_price_settings` DISABLE KEYS */;
INSERT INTO `jersey_price_settings` VALUES (1,NULL,3,1,20,80000.00,1,'2026-09-12 13:43:39','2026-09-12 13:43:39');
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
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_product_types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_product_types`
--

LOCK TABLES `jersey_product_types` WRITE;
/*!40000 ALTER TABLE `jersey_product_types` DISABLE KEYS */;
INSERT INTO `jersey_product_types` VALUES (1,'Jersey',1,'2026-09-12 13:39:49','2026-09-12 13:41:35'),(2,'Celana Pendek',1,'2026-09-12 13:39:55','2026-09-12 13:41:27'),(3,'Training',1,'2026-09-12 13:39:59','2026-09-12 13:41:33');
/*!40000 ALTER TABLE `jersey_product_types` ENABLE KEYS */;
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jersey_size_component_unique` (`jersey_size_id`,`jersey_component_id`),
  KEY `jersey_size_component_usage_jersey_component_id_foreign` (`jersey_component_id`),
  CONSTRAINT `jersey_size_component_usage_jersey_component_id_foreign` FOREIGN KEY (`jersey_component_id`) REFERENCES `jersey_components` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jersey_size_component_usage_jersey_size_id_foreign` FOREIGN KEY (`jersey_size_id`) REFERENCES `jersey_sizes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_size_component_usage`
--

LOCK TABLES `jersey_size_component_usage` WRITE;
/*!40000 ALTER TABLE `jersey_size_component_usage` DISABLE KEYS */;
INSERT INTO `jersey_size_component_usage` VALUES (1,1,2,0.200,'2026-09-12 14:06:49','2026-09-12 14:26:26'),(10,2,1,0.730,'2026-09-12 14:25:04','2026-09-12 14:25:04'),(11,3,1,0.750,'2026-09-12 14:25:16','2026-09-12 14:25:16'),(12,4,1,0.860,'2026-09-12 14:25:34','2026-09-12 14:25:34'),(13,5,1,0.920,'2026-09-12 14:25:58','2026-09-12 14:25:58'),(14,1,1,0.712,'2026-09-12 14:26:11','2026-09-12 14:26:11'),(15,2,2,0.240,'2026-09-12 14:26:39','2026-09-12 14:26:39'),(16,3,2,0.260,'2026-09-12 14:26:54','2026-09-12 14:26:54'),(17,4,2,0.285,'2026-09-12 14:27:06','2026-09-12 14:27:13'),(18,5,2,0.310,'2026-09-12 14:27:39','2026-09-12 14:27:39'),(19,2,3,0.030,'2026-09-12 14:27:59','2026-09-12 14:27:59'),(20,1,3,0.030,'2026-09-12 14:28:08','2026-09-12 14:28:08'),(21,3,3,0.030,'2026-09-12 14:28:11','2026-09-12 14:28:11'),(22,4,3,0.030,'2026-09-12 14:28:22','2026-09-12 14:28:22'),(23,5,3,0.030,'2026-09-12 14:28:28','2026-09-12 14:28:28'),(24,1,4,0.840,'2026-09-12 14:28:43','2026-09-12 14:28:43'),(25,2,4,0.890,'2026-09-12 14:28:54','2026-09-12 14:28:54'),(26,3,4,1.000,'2026-09-12 14:29:08','2026-09-12 14:29:08'),(27,4,4,1.000,'2026-09-12 14:29:08','2026-09-12 14:29:08'),(28,5,4,1.000,'2026-09-12 14:29:33','2026-09-12 14:29:33'),(29,1,5,0.500,'2026-09-12 14:30:05','2026-09-12 14:30:05'),(30,2,5,0.500,'2026-09-12 14:30:09','2026-09-12 14:30:09'),(31,3,5,0.600,'2026-09-12 14:30:30','2026-09-12 14:30:30'),(32,4,5,0.600,'2026-09-12 14:30:52','2026-09-12 14:30:52'),(33,5,5,0.650,'2026-09-12 14:30:58','2026-09-12 14:30:58');
/*!40000 ALTER TABLE `jersey_size_component_usage` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jersey_sizes`
--

LOCK TABLES `jersey_sizes` WRITE;
/*!40000 ALTER TABLE `jersey_sizes` DISABLE KEYS */;
INSERT INTO `jersey_sizes` VALUES (1,'S',1,1,0.00,'2026-09-12 12:27:40','2026-09-12 13:20:20'),(2,'M',2,1,0.00,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(3,'L',3,1,0.00,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(4,'XL',4,1,0.00,'2026-09-12 12:27:40','2026-09-12 12:27:40'),(5,'2XL',5,1,10000.00,'2026-09-12 12:27:40','2026-09-12 13:40:51'),(6,'3XL',6,1,10000.00,'2026-09-12 12:27:40','2026-09-12 13:40:57'),(7,'4XL',7,1,10000.00,'2026-09-12 12:27:40','2026-09-12 13:41:02'),(8,'5XL',8,1,10000.00,'2026-09-12 12:59:58','2026-09-12 13:41:07');
/*!40000 ALTER TABLE `jersey_sizes` ENABLE KEYS */;
UNLOCK TABLES;

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
  `no_referensi` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `kode_akun` varchar(20) NOT NULL,
  `debet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jurnal`),
  KEY `jurnal_umum_kode_akun_foreign` (`kode_akun`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_umum`
--

LOCK TABLES `jurnal_umum` WRITE;
/*!40000 ALTER TABLE `jurnal_umum` DISABLE KEYS */;
INSERT INTO `jurnal_umum` VALUES (49,'2026-09-11','INV08092026-002','Pembelian bahan baku INV08092026-002','1200',4342320.00,0.00,'2026-09-11 02:50:53','2026-09-11 02:50:53'),(50,'2026-09-11','INV08092026-002','Pembelian bahan baku INV08092026-002','2000',0.00,4342320.00,'2026-09-11 02:50:53','2026-09-11 02:50:53'),(51,'2026-09-11','DEP-20260911092618-1','Setoran deposit DEP-20260911092618-1','1000',50000.00,0.00,'2026-09-11 09:26:18','2026-09-11 09:26:18'),(52,'2026-09-11','DEP-20260911092618-1','Setoran deposit DEP-20260911092618-1','2100',0.00,50000.00,'2026-09-11 09:26:18','2026-09-11 09:26:18'),(79,'2026-09-11','INV-20260911-001','Penjualan INV-20260911-001','1100',197940.00,0.00,'2026-09-11 11:33:31','2026-09-11 11:33:31'),(80,'2026-09-11','INV-20260911-001','Penjualan INV-20260911-001','4000',0.00,197940.00,'2026-09-11 11:33:31','2026-09-11 11:33:31'),(93,'2026-09-11','INV-20260226-003','Pembayaran Tunai INV-20260226-003','1000',100000.00,0.00,'2026-09-11 15:43:02','2026-09-11 15:43:02'),(94,'2026-09-11','INV-20260226-003','Pembayaran Tunai INV-20260226-003','1100',0.00,100000.00,'2026-09-11 15:43:02','2026-09-11 15:43:02'),(95,'2026-09-11','INV-20260227-001','Pembayaran Tunai INV-20260227-001','1000',25000.00,0.00,'2026-09-11 15:43:13','2026-09-11 15:43:13'),(96,'2026-09-11','INV-20260227-001','Pembayaran Tunai INV-20260227-001','1100',0.00,25000.00,'2026-09-11 15:43:13','2026-09-11 15:43:13'),(97,'2026-09-11','INV-000999','Pembelian bahan baku INV-000999','1200',1110000.00,0.00,'2026-09-11 16:24:28','2026-09-11 16:24:28'),(98,'2026-09-11','INV-000999','Pembelian bahan baku INV-000999','2000',0.00,1110000.00,'2026-09-11 16:24:28','2026-09-11 16:24:28'),(99,'2026-09-11','INV-000123','Pembelian bahan baku INV-000123','1200',7550000.00,0.00,'2026-09-11 16:25:20','2026-09-11 16:25:20'),(100,'2026-09-11','INV-000123','Pembelian bahan baku INV-000123','1000',0.00,7550000.00,'2026-09-11 16:25:20','2026-09-11 16:25:20'),(107,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1000',30000.00,0.00,'2026-09-11 17:31:35','2026-09-11 17:31:35'),(108,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1100',0.00,30000.00,'2026-09-11 17:31:35','2026-09-11 17:31:35'),(109,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1000',50000.00,0.00,'2026-09-11 17:31:48','2026-09-11 17:31:48'),(110,'2026-09-12','INV-20260227-002','Pembayaran Tunai INV-20260227-002','1100',0.00,50000.00,'2026-09-11 17:31:48','2026-09-11 17:31:48');
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
  `tipe` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `stok_sebelum` decimal(15,4) NOT NULL,
  `stok_sesudah` decimal(15,4) NOT NULL,
  `referensi` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_id_bahan` (`id_bahan`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` VALUES (1,1,'2026-03-02 18:46:33','IN',10.0000,0.0000,10.0000,'asd124545','Pembelian bahan',1,'2026-03-02 18:46:33','2026-09-11 15:51:46'),(2,1,'2026-03-05 17:28:12','IN',13.0000,10.0000,23.0000,'SJ2148785','Penerimaan Gudang: Frontlite 280 13roll\r\nstiker inflex 1roll',1,'2026-03-05 10:28:12','2026-09-11 15:51:46'),(3,8,'2026-03-05 17:28:12','IN',1.0000,0.0000,1.0000,'SJ2148785','Penerimaan Gudang: Frontlite 280 13roll\r\nstiker inflex 1roll',1,'2026-03-05 10:28:12','2026-09-11 15:51:46'),(4,1,'2026-03-05 17:30:15','IN',6.0000,23.0000,29.0000,'jl5123123','Penerimaan Gudang: Frontlite 280gram 6 roll,\r\nSublime Paper 1 roll',1,'2026-03-05 10:30:15','2026-09-11 15:51:46'),(5,5,'2026-03-05 17:30:15','IN',1.0000,0.0000,1.0000,'jl5123123','Penerimaan Gudang: Frontlite 280gram 6 roll,\r\nSublime Paper 1 roll',1,'2026-03-05 10:30:15','2026-09-11 15:51:46'),(6,1,'2026-03-05 18:08:37','IN',5.0000,0.0000,5.0000,'SJ.05032026-005','Penerimaan Gudang: Frontlite 280gr 5roll',1,'2026-03-05 11:08:37','2026-09-11 15:51:46'),(7,1,'2026-03-05 18:20:19','IN',5.0000,5.0000,10.0000,'NF.05032026-005','Pembelian bahan',1,'2026-03-05 18:20:19','2026-09-11 15:51:46'),(8,4,'2026-03-05 22:02:15','IN',1.0000,0.0000,1.0000,'SJ.05032026-00125','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. AC 210gr (1.00 rim)\r\n2. CANVAS (1.00 Roll)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-05 15:02:15','2026-09-11 15:51:46'),(9,10,'2026-03-05 22:02:15','IN',1.0000,0.0000,1.0000,'SJ.05032026-00125','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. AC 210gr (1.00 rim)\r\n2. CANVAS (1.00 Roll)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-05 15:02:15','2026-09-11 15:51:46'),(12,4,'2026-03-05 22:19:04','IN',1.0000,1.0000,2.0000,'NF.032457899','Pembelian bahan',1,'2026-03-05 22:19:04','2026-09-11 15:51:46'),(13,10,'2026-03-05 22:19:04','IN',1.0000,1.0000,2.0000,'NF.032457899','Pembelian bahan',1,'2026-03-05 22:19:04','2026-09-11 15:51:46'),(14,6,'2026-03-06 07:36:04','IN',1.0000,0.0000,1.0000,'SJ.05324578784','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PET FILM DTF (1.00 Roll)\r\n2. AC 260gr (1 rim)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-06 00:36:04','2026-09-11 15:51:46'),(15,3,'2026-03-06 07:36:04','IN',1.0000,0.0000,1.0000,'SJ.05324578784','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PET FILM DTF (1.00 Roll)\r\n2. AC 260gr (1 rim)\r\n\r\nCatatan: Barang diterima dalam kondisi baik & tersegel.',1,'2026-03-06 00:36:04','2026-09-11 15:51:46'),(16,1,'2026-09-07 20:19:04','IN',1.0000,4.0000,5.0000,'SJ05478799','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr (1.00 roll)\r\n2. PET FILM DTF (1.00 roll)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-07 13:19:04','2026-09-11 15:51:46'),(17,6,'2026-09-07 20:19:04','IN',1.0000,1.0000,2.0000,'SJ05478799','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. FRONTLITE 280gr (1.00 roll)\r\n2. PET FILM DTF (1.00 roll)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-07 13:19:04','2026-09-11 15:51:46'),(20,1,'2026-09-10 16:31:14','ADJUSTMENT',3.5000,-3.5000,0.0000,'KOREKSI-2026-09-10','Koreksi stok negatif akibat bug historis: pemakaian bahan baku dari order tidak pernah tercatat di kartu_stok sebelum diperbaiki. Di-set ke 0 sebagai titik awal bersih, menunggu stock opname fisik untuk angka pasti.',NULL,'2026-09-10 09:31:14','2026-09-11 15:51:46'),(21,3,'2026-09-10 16:31:14','ADJUSTMENT',4.0000,-4.0000,0.0000,'KOREKSI-2026-09-10','Koreksi stok negatif akibat bug historis: pemakaian bahan baku dari order tidak pernah tercatat di kartu_stok sebelum diperbaiki. Di-set ke 0 sebagai titik awal bersih, menunggu stock opname fisik untuk angka pasti.',NULL,'2026-09-10 09:31:14','2026-09-11 15:51:46'),(22,9,'2026-09-10 16:31:14','ADJUSTMENT',1.5200,-1.5200,0.0000,'KOREKSI-2026-09-10','Koreksi stok negatif akibat bug historis: pemakaian bahan baku dari order tidak pernah tercatat di kartu_stok sebelum diperbaiki. Di-set ke 0 sebagai titik awal bersih, menunggu stock opname fisik untuk angka pasti.',NULL,'2026-09-10 09:31:14','2026-09-11 15:51:46'),(46,11,'2026-09-11 09:49:09','IN',2.0000,0.0000,2.0000,'SJ.11092026-002','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-11 02:49:09','2026-09-11 15:51:46'),(47,12,'2026-09-11 09:49:09','IN',4.0000,1.0000,5.0000,'SJ.11092026-002','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-11 02:49:09','2026-09-11 15:51:46'),(48,3,'2026-09-11 09:49:09','IN',4.0000,0.0000,4.0000,'SJ.11092026-002','Penerimaan Gudang: PENERIMAAN BARANG FISIK:\r\n1. PHOTOPAPER (2.00 roll)\r\n2. BONTAX (4.00 rim)\r\n3. AC 260gr (4 rim)\r\n\r\nNB : Barang Lengkap!',1,'2026-09-11 02:49:09','2026-09-11 15:51:46'),(49,29,'2026-09-11 09:57:19','ADJUSTMENT',1.0000,0.0000,1.0000,'STOCK-OPNAME','Uji coba bahan',1,'2026-09-11 02:57:19','2026-09-11 15:51:46'),(59,5,'2026-09-11 18:21:05','ADJUSTMENT',5.0000,0.0000,5.0000,'STOCK-OPNAME','Bahan datang dengan Travel',1,'2026-09-11 11:21:05','2026-09-11 15:51:46'),(61,7,'2026-09-11 18:33:31','OUT',0.0280,0.0000,-0.0280,'INV-20260911-001','Pemakaian produksi order INV-20260911-001',1,'2026-09-11 11:33:31','2026-09-11 15:51:46'),(62,3,'2026-09-11 19:36:29','OUT',0.0100,4.0000,3.9900,'QA-GUARD-TEST-OK','Pemakaian produksi order QA-GUARD-TEST-OK',1,'2026-09-11 12:36:29','2026-09-11 15:51:46'),(65,30,'2026-09-11 23:17:31','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Sisa stock',1,'2026-09-11 16:17:31','2026-09-11 16:17:31'),(66,27,'2026-09-11 23:17:42','ADJUSTMENT',5.0000,0.0000,5.0000,'STOCK-OPNAME','Belanjaan Baru',1,'2026-09-11 16:17:42','2026-09-11 16:17:42'),(67,28,'2026-09-11 23:17:53','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:17:53','2026-09-11 16:17:53'),(68,1,'2026-09-11 23:18:02','ADJUSTMENT',3.0000,0.0000,3.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:02','2026-09-11 16:18:02'),(69,7,'2026-09-11 23:18:27','ADJUSTMENT',2.0280,-0.0280,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:27','2026-09-11 16:18:27'),(70,8,'2026-09-11 23:18:40','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:40','2026-09-11 16:18:40'),(71,9,'2026-09-11 23:18:48','ADJUSTMENT',2.0000,0.0000,2.0000,'STOCK-OPNAME','Belanja Baru',1,'2026-09-11 16:18:48','2026-09-11 16:18:48');
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
  `nama_karyawan` varchar(100) NOT NULL,
  `bagian` enum('Admin','Desain','Produksi','Kasir','Gudang') NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_karyawan`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `status_lock` enum('open','closed') NOT NULL DEFAULT 'open',
  `waktu_tutup` timestamp NULL DEFAULT NULL,
  `keterangan_closing` text DEFAULT NULL,
  `status_rolling` enum('Rolling','Tidak Rolling') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kas`),
  KEY `kas_harian_id_user_foreign` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_harian`
--

LOCK TABLES `kas_harian` WRITE;
/*!40000 ALTER TABLE `kas_harian` DISABLE KEYS */;
INSERT INTO `kas_harian` VALUES (1,'2026-02-26',1,200000.00,0.00,0.00,0.00,0.00,1073600.00,NULL,NULL,40000.00,0.00,'open',NULL,NULL,NULL,NULL,NULL),(2,'2026-02-27',1,200000.00,0.00,0.00,0.00,0.00,200000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-02-27 11:41:29','2026-02-27 11:41:29'),(3,'2026-02-27',1,200000.00,0.00,0.00,0.00,0.00,200000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-02-27 11:43:37','2026-02-27 11:43:37'),(7,'2026-09-09',1,300000.00,0.00,0.00,0.00,0.00,300000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-09 23:27:35','2026-09-09 23:27:35'),(11,'2026-09-10',1,100000.00,0.00,0.00,0.00,0.00,100000.00,100000.00,0.00,0.00,0.00,'closed','2026-09-10 08:36:03',NULL,NULL,'2026-09-10 08:31:53','2026-09-10 08:36:03'),(17,'2026-09-11',1,100000.00,0.00,0.00,0.00,0.00,375000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-11 15:32:24','2026-09-11 15:32:24'),(18,'2026-09-12',1,100000.00,0.00,0.00,0.00,0.00,180000.00,NULL,NULL,0.00,0.00,'open',NULL,NULL,NULL,'2026-09-11 17:30:27','2026-09-11 17:30:27');
/*!40000 ALTER TABLE `kas_harian` ENABLE KEYS */;
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
  `keterangan` text DEFAULT NULL,
  `status` enum('Masuk','Keluar') NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kas_manual`),
  KEY `kas_manual_id_kas_foreign` (`id_kas`),
  KEY `kas_manual_id_user_foreign` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_kategori` varchar(50) NOT NULL,
  `kode_kategori` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (2,'INDOOR','IND',NULL,NULL),(3,'OUTDOOR','OTD',NULL,NULL),(4,'A3','A3',NULL,NULL),(5,'CENTURY STAR','CST',NULL,NULL),(6,'SUBLIME','SBM','2026-02-25 23:30:16','2026-02-25 23:30:16'),(7,'RHINO DTF','DTF','2026-02-25 23:30:29','2026-02-25 23:30:29');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori_finishing`
--

DROP TABLE IF EXISTS `kategori_finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_finishing` (
  `id_kategori` bigint(20) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_finishing`
--

LOCK TABLES `kategori_finishing` WRITE;
/*!40000 ALTER TABLE `kategori_finishing` DISABLE KEYS */;
/*!40000 ALTER TABLE `kategori_finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kedatangan_bahan`
--

DROP TABLE IF EXISTS `kedatangan_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kedatangan_bahan` (
  `id_kedatangan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_surat_jalan` varchar(255) NOT NULL,
  `id_supplier` bigint(20) unsigned NOT NULL,
  `id_gudang` bigint(20) unsigned NOT NULL,
  `tgl_datang` date NOT NULL,
  `status` enum('Draft','Selesai') DEFAULT 'Draft',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kedatangan`),
  KEY `kedatangan_bahan_id_supplier_foreign` (`id_supplier`),
  KEY `kedatangan_bahan_id_gudang_foreign` (`id_gudang`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `satuan` varchar(50) DEFAULT NULL,
  `status` enum('OK','Rusak','Kurang') DEFAULT 'OK',
  PRIMARY KEY (`id_detail`),
  KEY `kedatangan_bahan_detail_id_kedatangan_foreign` (`id_kedatangan`),
  KEY `kedatangan_bahan_detail_id_bahan_foreign` (`id_bahan`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_konsumen` varchar(100) NOT NULL,
  `nama_konsumen_manual` varchar(50) DEFAULT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text DEFAULT NULL,
  `id_golongan` bigint(20) unsigned NOT NULL,
  `tipe_konsumen` enum('permanent','temporary') NOT NULL DEFAULT 'temporary',
  `tgl_registrasi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_konsumen`),
  KEY `konsumen_id_golongan_foreign` (`id_golongan`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `tipe` enum('Setoran','Pemakaian') NOT NULL DEFAULT 'Setoran',
  `id_transaksi` bigint(20) unsigned DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode_bayar` varchar(30) NOT NULL DEFAULT 'Tunai',
  `keterangan` text DEFAULT NULL,
  `tgl_deposit` date NOT NULL,
  `id_user` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_deposit`),
  KEY `konsumen_deposit_id_konsumen_foreign` (`id_konsumen`),
  KEY `konsumen_deposit_id_transaksi_foreign` (`id_transaksi`),
  KEY `konsumen_deposit_id_user_foreign` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `konsumen_deposit`
--

LOCK TABLES `konsumen_deposit` WRITE;
/*!40000 ALTER TABLE `konsumen_deposit` DISABLE KEYS */;
INSERT INTO `konsumen_deposit` VALUES (6,1,'Setoran',NULL,50000.00,'Tunai','DP awal','2026-09-11',1,'2026-09-11 09:26:18','2026-09-11 09:26:18');
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
  `nama_menu` varchar(100) NOT NULL,
  `url_link` varchar(100) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'Master Bahan','bahan-baku',0),(2,'BOM Produk','bom',0),(3,'Transaksi Baru','penjualan',0),(4,'Pengaturan Akses','setting-akses',0),(5,'Data Karyawan','karyawan',0),(6,'Data User','users',0),(7,'Data Konsumen','konsumen',0),(8,'Golongan Harga','golongan',0),(9,'Supplier Bahan','supplier',0),(10,'Satuan & Konversi','satuan',0),(11,'Bank Perusahaan','bank',0),(12,'Golongan Konsumen','golongan',0),(13,'Produk & BOM','produk',0),(14,'Price List & Level','setting-harga',0),(15,'Kategori','kategori',0),(16,'Kasir','kasir',0),(17,'Kedatangan Bahan','kedatangan-bahan',0),(18,'Golongan','golongan',0),(20,'Pembelian Bahan','pembelian-bahan',0),(21,'Finishing Produk','finishing',0),(22,'SPK','spk',0),(23,'Produksi','produksi',0),(24,'Collecting','collecting',0),(25,'Laporan Order','laporan/order',0),(26,'Laporan Produksi','laporan/produksi',0),(27,'Aset Mesin','mesin',0),(28,'Harga Promo','harga-promo',0),(29,'Laporan Kas','laporan/kas',0),(30,'Merk','merk',0),(31,'Jenis Bahan','jenis-bahan',0),(32,'Jasa','jasa',0),(33,'Gudang','gudang',0),(34,'Deposit Konsumen','konsumen-deposit',0),(35,'Chart of Accounts','akun',0),(36,'Laporan Keuangan','akuntansi',0),(37,'Pengaturan Aplikasi','pengaturan-aplikasi',0),(38,'Order Jersey','jersey',0);
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
  `nama_merk` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_merk`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_mesin` varchar(100) NOT NULL,
  `tipe_mesin` varchar(50) NOT NULL,
  PRIMARY KEY (`id_mesin`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_02_25_100910_create_master_karyawan_table',1),(2,'2026_02_25_100910_create_rbac_tables',1),(3,'2026_02_25_101844_create_sessions_table',2),(4,'2026_02_25_104514_create_master_produksi_tables',3),(5,'2026_02_25_111057_create_complex_system_tables',3),(6,'2026_02_25_112610_add_missing_erp_tables',4),(7,'2026_02_26_001742_create_cache_table',5),(8,'2026_02_26_125746_fix_transaksi_columns',6),(9,'2026_03_01_174529_add_tipe_satuan_to_satuan_table',7),(10,'2026_03_05_102450_create_kartu_stoks_table',8),(11,'2026_03_07_133203_create_bom_finishing_table',8),(12,'2026_09_07_000001_reconcile_inventory_module_schema',9),(13,'2026_09_10_001021_add_spk_produksi_fields',10),(14,'2026_09_10_010000_add_collecting_menu',11),(15,'2026_09_10_020000_add_laporan_menu',12),(16,'2026_09_10_030000_fix_menu_access_gaps',13),(17,'2026_09_10_040000_add_harga_promo_menu',14),(18,'2026_09_10_050000_add_finishing_fields_to_transaksi_detail',15),(19,'2026_09_10_060000_add_bank_to_pembayaran_riwayat',16),(20,'2026_09_10_070000_add_laporan_kas_menu',17),(21,'2026_09_10_080000_drop_orphaned_kartu_stoks_and_kas_sesi_tables',18),(22,'2026_09_10_090000_backfill_bahan_baku_satuan_relations',18),(23,'2026_09_10_100000_add_harga_rata_rata_to_bahan_baku',18),(24,'2026_09_10_110000_add_hpp_snapshot_to_transaksi_detail',18),(25,'2026_09_10_120000_create_bag_akun_table',18),(26,'2026_09_10_130000_add_kode_akun_fk_to_jurnal_umum',18),(27,'2026_09_10_140000_create_merk_table',19),(28,'2026_09_10_150000_create_jenis_bahan_table',19),(29,'2026_09_10_160000_add_merk_and_jenis_bahan_to_bahan_baku',19),(30,'2026_09_10_170000_create_jasa_table',19),(31,'2026_09_10_180000_enrich_gudang_table',19),(32,'2026_09_10_190000_create_konsumen_deposit_table',19),(33,'2026_09_10_200000_register_new_master_data_menus',19),(34,'2026_09_10_210000_fix_gudang_id_auto_increment',20),(35,'2026_09_10_220000_backfill_satuan_stok_dash_values',21),(36,'2026_09_10_230000_widen_stock_decimal_precision',22),(37,'2026_09_10_240000_add_deposit_and_jasa_accounts',23),(38,'2026_09_10_250000_add_tipe_to_konsumen_deposit',23),(39,'2026_09_10_260000_add_deposit_to_pembayaran_riwayat_metode_enum',24),(40,'2026_09_10_270000_add_jasa_support_to_transaksi_detail',25),(41,'2026_09_10_280000_add_dimensi_roll_to_bahan_baku',26),(42,'2026_09_10_290000_register_akuntansi_menus',27),(43,'2026_09_10_300000_extend_order_kantor_fields',28),(44,'2026_09_10_310000_fix_kartu_stok_bahan_fk_to_restrict',29),(45,'2026_09_10_320000_split_frontlite_280_into_roll_width_variants',30),(46,'2026_09_11_100000_create_karyawan_mesin_table',31),(47,'2026_09_11_110000_expand_kasir_role_to_full_finance_scope',32),(48,'2026_09_11_120000_fix_operator_desain_role_permissions',33),(49,'2026_09_11_130000_fix_stale_menu_url_link_labels',34),(50,'2026_09_11_140000_create_operator_produksi_role',35),(51,'2026_09_11_150000_create_gudang_role',36),(52,'2026_09_11_160000_add_collecting_status_to_transaksi',37),(53,'2026_09_11_170000_fix_konsumen_deposit_fk_to_restrict',38),(54,'2026_09_11_180000_add_missing_fk_kedatangan_bahan_gudang',39),(55,'2026_09_11_190000_add_pending_status_to_transaksi_detail',40),(56,'2026_09_11_190100_add_status_nota_to_transaksi',41),(57,'2026_09_11_200000_create_finishing_harga_table',42),(58,'2026_09_11_200100_create_jasa_harga_table',43),(59,'2026_09_11_210000_add_biaya_desain_potongan_to_transaksi_detail',44),(60,'2026_09_11_220000_add_status_to_produk',45),(61,'2026_09_11_230000_create_nota_edit_request_table',46),(62,'2026_09_11_240000_make_range_min_max_decimal_in_harga_tables',47),(63,'2026_09_11_250000_add_debit_metode_bayar',48),(64,'2026_09_12_010000_create_kas_manual_table',49),(65,'2026_09_12_020000_add_status_rolling_to_kas_harian',50),(66,'2026_09_12_030000_restore_missing_timestamps_on_transaksi',51),(67,'2026_09_12_030100_restore_missing_timestamps_on_transaksi_detail',51),(68,'2026_09_12_040000_recreate_missing_laravel_framework_tables',51),(69,'2026_09_12_150000_create_app_settings_table',52),(70,'2026_09_12_160000_create_jersey_module_tables',53),(71,'2026_09_12_170000_restructure_jersey_multi_item_orders',54),(72,'2026_09_13_100000_add_material_estimation_to_jersey',55);
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
  `alasan` text NOT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak','Selesai') NOT NULL DEFAULT 'Menunggu',
  `id_user_approver` bigint(20) unsigned DEFAULT NULL,
  `catatan_approver` text DEFAULT NULL,
  `tgl_keputusan` timestamp NULL DEFAULT NULL,
  `tgl_selesai` timestamp NULL DEFAULT NULL,
  `id_transaksi_koreksi` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_request`),
  KEY `nota_edit_request_id_transaksi_foreign` (`id_transaksi`),
  KEY `nota_edit_request_id_user_requester_foreign` (`id_user_requester`),
  KEY `nota_edit_request_id_user_approver_foreign` (`id_user_approver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` enum('Tunai','Transfer','Debit','Deposit') NOT NULL,
  `id_bank` bigint(20) unsigned DEFAULT NULL,
  `tgl_bayar` datetime NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `pembayaran_riwayat_id_bank_foreign` (`id_bank`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran_riwayat`
--

LOCK TABLES `pembayaran_riwayat` WRITE;
/*!40000 ALTER TABLE `pembayaran_riwayat` DISABLE KEYS */;
INSERT INTO `pembayaran_riwayat` VALUES (1,3,500000.00,'Tunai',NULL,'2026-02-26 22:21:55','Pembayaran DP'),(2,3,150400.00,'Tunai',NULL,'2026-02-26 22:33:43','Pembayaran via Kasir'),(3,3,40000.00,'Transfer',NULL,'2026-02-26 22:51:17','Pembayaran Transfer via Kasir'),(4,3,100000.00,'Tunai',NULL,'2026-02-26 23:14:38','Pembayaran via Kasir'),(16,4,100000.00,'Tunai',NULL,'2026-09-11 22:43:02','Pembayaran via Kasir'),(17,5,25000.00,'Tunai',NULL,'2026-09-11 22:43:13','Pembayaran via Kasir'),(21,7,30000.00,'Tunai',NULL,'2026-09-12 00:31:35','Pembayaran via Kasir'),(22,7,50000.00,'Tunai',NULL,'2026-09-12 00:31:48','Pembayaran via Kasir');
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
  `no_faktur` varchar(255) NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `tipe_pembelian` varchar(50) DEFAULT NULL,
  `jatuh_tempo_hari` int(11) DEFAULT NULL,
  `jatuh_tempo_tgl` date DEFAULT NULL,
  `status_pajak` varchar(50) DEFAULT NULL,
  `include_pajak` tinyint(1) DEFAULT 0,
  `grand_total` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_pembayaran` enum('Hutang','Lunas') DEFAULT 'Hutang',
  `tgl_pelunasan` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_pembelian`),
  UNIQUE KEY `pembelian_bahan_no_faktur_unique` (`no_faktur`),
  KEY `pembelian_bahan_id_kedatangan_foreign` (`id_kedatangan`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembelian_bahan`
--

LOCK TABLES `pembelian_bahan` WRITE;
/*!40000 ALTER TABLE `pembelian_bahan` DISABLE KEYS */;
INSERT INTO `pembelian_bahan` VALUES (3,19,'NF.05032026-005','2026-03-02','Biasa',NULL,NULL,NULL,0,5600000.00,'2026-09-11 15:51:46','2026-03-05 11:20:19','Hutang',NULL),(5,20,'NF.032457899','2026-03-05','Biasa',NULL,NULL,NULL,0,3765000.00,'2026-09-11 15:51:46','2026-03-05 15:27:40','Lunas','2026-03-05 15:27:40'),(8,29,'INV08092026-002','2026-09-09','Tempo',NULL,NULL,NULL,1,4342320.00,'2026-09-11 15:51:46','2026-09-11 02:50:53','Hutang',NULL),(9,21,'INV-000999','2026-09-11','Tempo',NULL,NULL,NULL,1,1110000.00,'2026-09-11 16:24:28','2026-09-11 16:24:28','Hutang',NULL),(10,22,'INV-000123','2026-09-11','Cash',NULL,NULL,NULL,0,7550000.00,'2026-09-11 16:25:20','2026-09-11 16:25:20','Lunas','2026-09-10 17:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_produk` varchar(100) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `id_mesin` int(11) DEFAULT NULL,
  `metode_hitung` enum('ukuran','quantity') NOT NULL,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_panjang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_lebar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT '''aktif''',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
INSERT INTO `produk` VALUES (1,'FLS280 3.2m',NULL,2,'ukuran',0.00,0.00,0.00,'-','aktif','0000-00-00 00:00:00','2026-09-11 13:27:14'),(2,'TC Direct Print 120cm',NULL,4,'ukuran',3.60,120.00,300.00,'-','aktif','0000-00-00 00:00:00','2026-09-11 13:27:25'),(3,'Sublime Paper',NULL,6,'ukuran',0.16,155.00,10.00,'-','aktif','0000-00-00 00:00:00','2026-09-11 11:23:14'),(4,'AC 260gram',NULL,3,'quantity',0.00,0.00,0.00,'-','aktif','0000-00-00 00:00:00','2026-09-11 13:42:17'),(5,'AC 210gram',NULL,3,'quantity',0.00,0.00,0.00,'-','aktif','0000-00-00 00:00:00','2026-09-11 13:42:10'),(7,'STIKER CAMEL 155CM',NULL,1,'ukuran',0.31,155.00,20.00,'MINIMAL CETAK MENGIKUTI ATURAN MINIMAL ORDER','aktif','2026-03-03 10:10:05','2026-09-11 13:21:54'),(8,'STIKER EXILO',NULL,1,'ukuran',0.31,153.00,20.00,'-','aktif','2026-03-06 01:47:06','2026-09-11 13:21:29'),(9,'STIKER INFLEX',NULL,1,'ukuran',0.31,155.00,20.00,'-','aktif','2026-03-06 01:47:50','2026-09-11 13:25:45'),(18,'FLS280 2.2m','FLS2.2',2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:43:20','2026-09-11 13:43:20'),(19,'FLS280 2.6m',NULL,2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:43:42','2026-09-11 13:43:42'),(20,'FLS280 1.6m',NULL,2,'ukuran',0.00,0.00,0.00,'-','aktif','2026-09-11 13:43:58','2026-09-11 13:43:58'),(21,'DTF Print Pet Film','DTF-PF',5,'ukuran',1.00,100.00,100.00,'-','\'aktif\'','2026-09-12 14:11:34','2026-09-12 14:11:34'),(22,'DTF Print Pet Film','DTF-PF',5,'ukuran',1.00,100.00,100.00,'-','\'aktif\'','2026-09-12 14:13:16','2026-09-12 14:13:16'),(23,'BONTAX','BTX',3,'quantity',0.00,0.00,0.00,'-','\'aktif\'','2026-09-12 14:14:09','2026-09-12 14:14:09'),(24,'QA_TEST_PRODUK_REPRO',NULL,NULL,'ukuran',0.00,0.00,0.00,'-','\'aktif\'','2026-09-12 14:34:34','2026-09-12 14:34:34');
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
  `id_produk` int(11) DEFAULT NULL,
  `id_finishing` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(2,3),(2,7),(2,12),(2,21),(2,22),(2,23),(2,26),(3,3),(3,7),(3,11),(3,12),(3,16),(3,24),(3,29),(3,34),(3,36),(4,21),(4,22),(4,23),(5,1),(5,17),(5,33);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nama_satuan` varchar(20) NOT NULL,
  `nilai_konversi` decimal(10,2) NOT NULL,
  `tipe_satuan` varchar(255) NOT NULL DEFAULT '''Barang''',
  PRIMARY KEY (`id_satuan`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  KEY `sessions_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('pRLbcWJgt5qLiKBykt8EmJ9axbHgr89LQvftkoIG',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUDU3amRxUlZKdkRSY1J1U0s4em1DSEhtOUN1dHdoN2tXZnYzb05YNSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAva2FzaXIvY2hlY2stcGVtYmF5YXJhbiI7czo1OiJyb3V0ZSI7czo2OiJrYXNpci4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1789223845),('SZYfYU80u38ZH7bUVVqQejQZltifcRBZkBr948Ug',18,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/1.52386.3 Chrome/152.0.7977.76 Safari/537.36 MSIX','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQjRYMmFQVFZZWmhETmhWcGhVZW9mUnp0alQxdGF4eEZYdTU5c0J6WSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE4O3M6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3QvcGVyY2V0YWthbi1hcHAvcHVibGljL2plcnNleS8yIjt9fQ==',1789222648);
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
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_harga`
--

LOCK TABLES `setting_harga` WRITE;
/*!40000 ALTER TABLE `setting_harga` DISABLE KEYS */;
INSERT INTO `setting_harga` VALUES (30,3,1,1.00,9999999.00,30000.00,NULL,1,NULL,'2026-03-07 20:10:14','2026-09-11 22:51:46'),(31,3,2,1.00,9999999.00,30000.00,NULL,1,NULL,'2026-03-07 20:10:14','2026-09-11 22:51:46'),(32,3,3,1.00,9999999.00,30000.00,NULL,1,NULL,'2026-03-07 20:10:14','2026-09-11 22:51:46'),(85,2,1,1.00,9999999.00,20000.00,21,1,21,'2026-03-24 13:42:48','2026-09-11 22:51:46'),(86,2,2,1.00,224.00,18000.00,21,1,21,'2026-03-24 13:42:48','2026-09-11 22:51:46'),(87,2,2,224.00,448.00,15000.00,21,1,21,'2026-03-24 13:42:48','2026-09-11 22:51:46'),(88,1,1,1.00,999999.00,18000.00,21,1,21,'2026-03-24 13:51:25','2026-09-11 22:51:46'),(89,1,2,1.00,100.00,16000.00,21,1,21,'2026-03-24 13:51:25','2026-09-11 22:51:46'),(90,1,3,1.00,9999999.00,15000.00,21,1,21,'2026-03-24 13:51:25','2026-09-11 22:51:46'),(126,4,1,1.00,10.00,5000.00,18,1,18,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(127,4,1,11.00,50.00,4500.00,18,1,18,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(128,4,2,11.00,50.00,4500.00,NULL,1,NULL,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(129,4,2,1.00,10.00,5000.00,18,1,18,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(130,4,3,1.00,10.00,5000.00,NULL,1,NULL,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(131,4,3,11.00,50.00,4500.00,NULL,1,NULL,'2026-03-26 14:49:03','2026-09-11 22:51:46'),(143,5,1,10.00,49.00,4850.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(144,5,1,50.00,149.00,4500.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(145,5,1,150.00,500.00,4000.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(146,5,2,1.00,9.00,5000.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(147,5,2,10.00,49.00,4850.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(148,5,2,50.00,149.00,4500.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(149,5,2,150.00,500.00,4000.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(150,5,3,1.00,9.00,5000.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(151,5,3,10.00,49.00,4850.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(152,5,3,50.00,149.00,4500.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(153,5,3,150.00,500.00,4000.00,NULL,1,NULL,'2026-09-10 00:18:02','2026-09-11 22:51:46'),(157,7,2,1.00,NULL,67000.00,NULL,1,NULL,'2026-09-11 08:09:32','2026-09-11 22:51:46'),(161,7,3,1.00,NULL,60000.00,NULL,1,NULL,'2026-09-11 08:25:31','2026-09-11 22:51:46'),(164,8,2,1.00,NULL,47000.00,NULL,1,NULL,'2026-09-11 09:24:33','2026-09-11 22:51:46'),(166,9,2,1.00,NULL,77000.00,NULL,1,NULL,'2026-09-11 09:24:54','2026-09-11 22:51:46'),(168,20,1,0.01,224.00,20000.00,NULL,1,NULL,'2026-09-11 14:04:08','2026-09-11 22:51:46'),(170,20,1,225.00,448.00,15000.00,NULL,1,NULL,'2026-09-11 14:04:57','2026-09-11 22:51:46'),(173,18,1,0.01,154.00,20000.00,NULL,1,NULL,'2026-09-11 14:19:40','2026-09-11 22:51:46'),(174,18,1,155.00,308.00,15000.00,NULL,1,NULL,'2026-09-11 14:19:56','2026-09-11 22:51:46'),(175,18,2,0.01,154.00,16000.00,NULL,1,NULL,'2026-09-11 14:20:05','2026-09-11 22:51:46'),(176,18,2,155.00,308.00,13000.00,NULL,1,NULL,'2026-09-11 14:20:19','2026-09-11 22:51:46'),(177,19,1,0.01,182.00,20000.00,NULL,1,NULL,'2026-09-11 14:20:38','2026-09-11 22:51:46'),(178,19,1,183.00,366.00,15000.00,NULL,1,NULL,'2026-09-11 14:20:53','2026-09-11 22:51:46'),(179,20,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:00','2026-09-11 22:51:46'),(180,18,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:10','2026-09-11 22:51:46'),(181,19,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:18','2026-09-11 22:51:46'),(182,1,6,0.01,NULL,15000.00,NULL,1,NULL,'2026-09-11 14:22:24','2026-09-11 22:51:46'),(183,7,1,0.01,NULL,70000.00,NULL,1,NULL,'2026-09-11 14:25:39','2026-09-11 22:51:46'),(184,8,1,0.01,NULL,50000.00,NULL,1,NULL,'2026-09-11 14:25:53','2026-09-11 22:51:46'),(185,9,1,0.01,NULL,80000.00,NULL,1,NULL,'2026-09-11 14:26:04','2026-09-11 22:51:46');
/*!40000 ALTER TABLE `setting_harga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier` (
  `id_supplier` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kontak` varchar(255) NOT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `nomor_nota` varchar(50) NOT NULL,
  `tipe_order` enum('Reguler','PO') NOT NULL DEFAULT 'Reguler',
  `no_po` varchar(100) DEFAULT NULL,
  `id_konsumen` bigint(20) unsigned NOT NULL,
  `nama_konsumen_manual` varchar(255) DEFAULT NULL,
  `id_user_kasir` bigint(20) unsigned NOT NULL,
  `tgl_order` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_bayar` decimal(15,2) NOT NULL,
  `status_pembayaran` enum('Lunas','DP','Belum Bayar') NOT NULL,
  `status_nota` enum('Aktif','Dibatalkan') NOT NULL DEFAULT 'Aktif',
  `alasan_batal_nota` text DEFAULT NULL,
  `tgl_dibatalkan_nota` timestamp NULL DEFAULT NULL,
  `id_user_pembatal_nota` bigint(20) unsigned DEFAULT NULL,
  `status_collecting` enum('Belum Diambil','Sudah Diambil') NOT NULL DEFAULT 'Belum Diambil',
  `tgl_collecting` timestamp NULL DEFAULT NULL,
  `id_user_collecting` bigint(20) unsigned DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `transaksi_nomor_nota_unique` (`nomor_nota`),
  KEY `transaksi_id_konsumen_foreign` (`id_konsumen`),
  KEY `transaksi_id_user_kasir_foreign` (`id_user_kasir`),
  KEY `transaksi_id_user_pembatal_nota_foreign` (`id_user_pembatal_nota`),
  KEY `transaksi_id_user_collecting_foreign` (`id_user_collecting`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (2,'INV-20260226-001','Reguler',NULL,1,NULL,1,'2026-02-26 13:08:44',161600.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,NULL,NULL,'2026-02-26 14:47:15'),(3,'INV-20260226-002','Reguler',NULL,2,NULL,1,'2026-02-26 14:48:21',790400.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-02-26 16:14:38'),(4,'INV-20260226-003','Reguler',NULL,3,NULL,1,'2026-02-26 16:02:17',405000.00,'DP','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-11 15:43:02'),(5,'INV-20260227-001','Reguler',NULL,2,NULL,1,'2026-02-27 11:12:36',25000.00,'Lunas','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-11 15:43:13'),(7,'INV-20260227-002','Reguler',NULL,2,NULL,1,'2026-02-27 15:59:43',180000.00,'DP','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,'Tunai',NULL,'2026-09-11 17:31:48'),(10,'INV-20260909-001','Reguler',NULL,1,NULL,1,'2026-09-09 15:03:24',0.00,'Belum Bayar','Aktif',NULL,NULL,NULL,'Belum Diambil',NULL,NULL,NULL,NULL,'2026-09-11 16:55:15'),(48,'INV-20260911-001','Reguler',NULL,2,NULL,1,'2026-09-11 11:33:31',20000.00,'Belum Bayar','Aktif',NULL,NULL,NULL,'Sudah Diambil','2026-09-11 16:34:21',1,NULL,NULL,'2026-09-11 16:50:23');
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
  `id_finishing` bigint(20) unsigned DEFAULT NULL,
  `biaya_finishing` decimal(15,2) DEFAULT 0.00,
  `biaya_cetak` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_desain` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nama_file` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `p_ukuran` decimal(10,2) NOT NULL,
  `l_ukuran` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `status_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `status_urgensi` enum('Normal','Urgent','Very Urgent') NOT NULL DEFAULT 'Normal',
  `status_finishing` enum('Standar','Custom') NOT NULL DEFAULT 'Standar',
  `qty_cetak` int(11) DEFAULT NULL,
  `catatan_produksi` text DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hpp_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_spk` enum('Antre','Desain','Cetak','Selesai','Batal','Pending') NOT NULL DEFAULT 'Antre',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `transaksi_detail_id_transaksi_foreign` (`id_transaksi`),
  KEY `transaksi_detail_id_produk_foreign` (`id_produk`),
  KEY `transaksi_detail_id_jasa_foreign` (`id_jasa`),
  KEY `transaksi_detail_id_finishing_foreign` (`id_finishing`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_detail`
--

LOCK TABLES `transaksi_detail` WRITE;
/*!40000 ALTER TABLE `transaksi_detail` DISABLE KEYS */;
INSERT INTO `transaksi_detail` VALUES (1,2,1,NULL,NULL,0.00,0.00,0.00,'menu',NULL,310.00,100.00,2,0,'Normal','Standar',NULL,NULL,111600.00,0.00,0.00,0.00,'Antre',NULL,NULL),(2,2,4,NULL,NULL,0.00,0.00,0.00,'menu',NULL,0.00,0.00,10,0,'Normal','Standar',NULL,NULL,50000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(3,3,1,NULL,NULL,0.00,0.00,0.00,'toko',NULL,100.00,500.00,3,0,'Normal','Standar',NULL,NULL,240000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(4,3,2,NULL,NULL,0.00,0.00,0.00,'umbul-umbul',NULL,120.00,550.00,3,0,'Normal','Standar',NULL,NULL,356400.00,0.00,0.00,0.00,'Antre',NULL,NULL),(5,3,5,NULL,NULL,0.00,0.00,0.00,'Tanpa File',NULL,0.00,0.00,40,0,'Normal','Standar',NULL,NULL,194000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(6,4,1,NULL,NULL,0.00,0.00,0.00,'Jualan',NULL,400.00,250.00,1,0,'Normal','Standar',NULL,NULL,180000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(7,4,3,NULL,NULL,0.00,0.00,0.00,'sablon',NULL,150.00,500.00,1,0,'Normal','Standar',NULL,NULL,225000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(8,5,5,NULL,NULL,0.00,0.00,0.00,'takjil',NULL,0.00,0.00,5,0,'Normal','Standar',NULL,NULL,25000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(10,7,4,NULL,NULL,0.00,180000.00,0.00,'menu',NULL,0.00,0.00,40,0,'Normal','Standar',NULL,NULL,180000.00,0.00,0.00,0.00,'Antre',NULL,NULL),(13,10,8,NULL,NULL,0.00,0.00,0.00,'Stiker gerobak',NULL,152.00,100.00,1,0,'Normal','Standar',1,NULL,0.00,0.00,0.00,0.00,'Selesai',NULL,NULL),(60,48,7,NULL,1,20000.00,0.00,0.00,'NG Colorado Bag 2026 Side 01','order_thumbnails/ix1H3ewWfrIw9MLZZQGojBU4V07GqnUKPBoEV0EZ.jpg',155.00,140.00,1,0,'Normal','Custom',2,NULL,20000.00,0.00,0.00,165390.00,'Selesai',NULL,NULL);
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
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_role` bigint(20) unsigned NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_id_karyawan_foreign` (`id_karyawan`),
  KEY `users_id_role_foreign` (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','$2y$12$G2HMSNe1XosO.y0b/Oi9BeTRrhba3WqWG8LGFBrwMdrJ0gL6ISx7m',1,NULL,NULL,NULL),(4,6,'rehan','$2y$12$4E7JRf2FEslrjHLz2llGV.HkHrndxHcZw7BexJ.AiPdo.lpiT0xFa',2,NULL,'2026-02-25 14:36:24','2026-09-10 01:29:20'),(5,7,'rahman','$2y$12$qniJ544jbTScKyUIDGlTD.InXuVXN0JERQ/2epuOsC0Uiv7jzga0u',2,NULL,'2026-02-25 14:36:40','2026-02-25 14:36:40'),(14,8,'opepson','$2y$12$d6392M3gnUYGyL5Z5duul.uKzA2t39YUgN1PbWcXIjj87q/.fz6x2',4,NULL,'2026-09-11 04:04:08','2026-09-11 04:04:08');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `worksheet`
--

DROP TABLE IF EXISTS `worksheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worksheet` (
  `id_worksheet` bigint(20) NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_worksheet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worksheet`
--

LOCK TABLES `worksheet` WRITE;
/*!40000 ALTER TABLE `worksheet` DISABLE KEYS */;
/*!40000 ALTER TABLE `worksheet` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-12 21:37:29
