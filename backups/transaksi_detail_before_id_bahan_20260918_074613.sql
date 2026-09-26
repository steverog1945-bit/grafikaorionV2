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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_detail`
--

LOCK TABLES `transaksi_detail` WRITE;
/*!40000 ALTER TABLE `transaksi_detail` DISABLE KEYS */;
INSERT INTO `transaksi_detail` VALUES (1,2,1,NULL,NULL,0.00,0.00,0.00,'menu',NULL,310.00,100.00,2,0,'Normal','Standar',NULL,NULL,111600.00,0.00,0.00,0.00,'Desain',NULL,NULL),(2,2,4,NULL,NULL,0.00,0.00,0.00,'menu',NULL,0.00,0.00,10,0,'Normal','Standar',NULL,NULL,50000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(3,3,1,NULL,NULL,0.00,0.00,0.00,'toko',NULL,100.00,500.00,3,0,'Normal','Standar',NULL,NULL,240000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(4,3,2,NULL,NULL,0.00,0.00,0.00,'umbul-umbul',NULL,120.00,550.00,3,0,'Normal','Standar',NULL,NULL,356400.00,0.00,0.00,0.00,'Desain',NULL,NULL),(5,3,5,NULL,NULL,0.00,0.00,0.00,'Tanpa File',NULL,0.00,0.00,40,0,'Normal','Standar',NULL,NULL,194000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(6,4,1,NULL,NULL,0.00,0.00,0.00,'Jualan',NULL,400.00,250.00,1,0,'Normal','Standar',NULL,NULL,180000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(7,4,3,NULL,NULL,0.00,0.00,0.00,'sablon',NULL,150.00,500.00,1,0,'Normal','Standar',NULL,NULL,225000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(8,5,5,NULL,NULL,0.00,0.00,0.00,'takjil',NULL,0.00,0.00,5,0,'Normal','Standar',NULL,NULL,25000.00,0.00,0.00,0.00,'Desain',NULL,NULL),(10,7,4,NULL,NULL,0.00,180000.00,0.00,'menu',NULL,0.00,0.00,40,0,'Normal','Standar',40,NULL,180000.00,0.00,0.00,0.00,'Selesai',NULL,NULL),(13,10,8,NULL,NULL,0.00,0.00,0.00,'Stiker gerobak',NULL,152.00,100.00,1,0,'Normal','Standar',1,NULL,0.00,0.00,0.00,0.00,'Selesai',NULL,NULL),(60,48,7,NULL,1,20000.00,0.00,0.00,'NG Colorado Bag 2026 Side 01','order_thumbnails/ix1H3ewWfrIw9MLZZQGojBU4V07GqnUKPBoEV0EZ.jpg',155.00,140.00,1,0,'Normal','Custom',2,NULL,20000.00,0.00,0.00,165390.00,'Selesai',NULL,NULL),(61,49,4,NULL,NULL,0.00,25000.00,0.00,'QA Audit Desainer - AC 260gram','order_thumbnails/spzzfhY9ijnUFWfmqtcFtnCYWuTWmpOeuhvBCHSY.jpg',0.00,0.00,5,0,'Normal','Standar',NULL,NULL,25000.00,0.00,2810.58,25000.00,'Antre',NULL,NULL);
/*!40000 ALTER TABLE `transaksi_detail` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-18  7:46:13
