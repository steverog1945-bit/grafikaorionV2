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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'Master Bahan','bahan-baku',0),(2,'BOM Produk','bom',0),(3,'Transaksi Baru','penjualan',0),(4,'Pengaturan Akses','setting-akses',0),(5,'Data Karyawan','karyawan',0),(6,'Data User','users',0),(7,'Data Konsumen','konsumen',0),(8,'Golongan Harga','golongan',0),(9,'Supplier Bahan','supplier',0),(10,'Satuan & Konversi','satuan',0),(11,'Bank Perusahaan','bank',0),(12,'Golongan Konsumen','golongan',0),(13,'Produk & BOM','produk',0),(14,'Price List & Level','setting-harga',0),(15,'Kategori','kategori',0),(16,'Kasir','kasir',0),(17,'Kedatangan Bahan','kedatangan-bahan',0),(18,'Golongan','golongan',0),(20,'Pembelian Bahan','pembelian-bahan',0),(21,'Finishing Produk','finishing',0),(22,'SPK','spk',0),(23,'Produksi','produksi',0),(24,'Collecting','collecting',0),(25,'Laporan Order','laporan/order',0),(26,'Laporan Produksi','laporan/produksi',0),(27,'Aset Mesin','mesin',0),(28,'Harga Promo','harga-promo',0),(29,'Laporan Kas','laporan/kas',0),(30,'Merk','merk',0),(31,'Jenis Bahan','jenis-bahan',0),(32,'Jasa','jasa',0),(33,'Gudang','gudang',0),(34,'Deposit Konsumen','konsumen-deposit',0),(35,'Chart of Accounts','akun',0),(36,'Laporan Keuangan','akuntansi',0),(37,'Pengaturan Aplikasi','pengaturan-aplikasi',0),(38,'Order Jersey','jersey',0),(39,'Kas Kecil','kas-kecil',0),(40,'Laporan Belanja Bahan','laporan/belanja-bahan',0),(41,'Kasir Utama (Input Order)','penjualan-input-order',0),(42,'Order Jersey (Input Order)','jersey-input-order',0),(43,'Laporan Order Jersey','laporan/order-jersey',0),(44,'Laporan Piutang','laporan/piutang',0),(45,'Kategori Kas Kecil','kategori-kas-kecil',0),(46,'Laporan Stok Bahan Baku','laporan/stok-bahan',0),(47,'Laporan HPP & Margin','laporan/hpp',0);
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
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
INSERT INTO `role_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,20),(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(1,39),(1,40),(1,44),(1,45),(1,46),(1,47),(2,7),(2,12),(2,23),(2,24),(2,26),(2,41),(2,42),(3,3),(3,7),(3,11),(3,12),(3,16),(3,24),(3,29),(3,34),(3,36),(3,38),(4,21),(4,22),(4,23),(5,1),(5,17),(5,33),(5,46);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-18  7:48:23
