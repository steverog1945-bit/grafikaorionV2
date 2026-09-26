USE percetakan_erp;
CREATE TABLE `app_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_aplikasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PRINT-ERP',
  `alamat` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `telepon` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fax` varchar(50) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `libur_kas_minggu` tinyint(1) NOT NULL DEFAULT 1,
  `format_nota` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'a4',
  `nomor_nota_prefix` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INV',
  `nota_footer_text` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pembulatan_nota` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '500',
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bag_akun` (
  `id_akun` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_akun` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_akun` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_akun` enum('Aset','Kewajiban','Modal','Pendapatan','Beban') COLLATE utf8mb4_general_ci NOT NULL,
  `saldo_normal` enum('Debet','Kredit') COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `bag_akun_kode_akun_unique` (`kode_akun`),
  PRIMARY KEY (`id_akun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bahan_baku` (
  `id_bahan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_bahan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_bahan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_mesin` bigint(20) unsigned NOT NULL,
  `id_kategori` bigint(20) unsigned NOT NULL,
  `id_merk` bigint(20) unsigned NULL DEFAULT NULL,
  `id_jenis_bahan` bigint(20) unsigned NULL DEFAULT NULL,
  `stok` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `stok_minimal` decimal(15,3) NOT NULL DEFAULT 0.000,
  `id_satuan_stok` bigint(20) unsigned NULL DEFAULT NULL,
  `id_satuan_hitung` bigint(20) unsigned NULL DEFAULT NULL,
  `nilai_konversi` decimal(15,3) NOT NULL DEFAULT 1.000,
  `lebar_roll` decimal(10,3) NULL DEFAULT NULL,
  `panjang_roll` decimal(10,3) NULL DEFAULT NULL,
  `harga_rata_rata` decimal(15,2) NOT NULL DEFAULT 0.00,
  `satuan_stok` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `bahan_baku_id_jenis_bahan_foreign` (`id_jenis_bahan`),
  KEY `bahan_baku_id_kategori_foreign` (`id_kategori`),
  KEY `bahan_baku_id_merk_foreign` (`id_merk`),
  KEY `bahan_baku_id_mesin_foreign` (`id_mesin`),
  KEY `bahan_baku_id_satuan_hitung_foreign` (`id_satuan_hitung`),
  KEY `bahan_baku_id_satuan_stok_foreign` (`id_satuan_stok`),
  UNIQUE KEY `bahan_baku_kode_bahan_unique` (`kode_bahan`),
  PRIMARY KEY (`id_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bank_perusahaan` (
  `id_bank` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_bank` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_rekening` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `atas_nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_bank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bom_detail` (
  `id_bom` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `cara_hitung_pemakaian` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `bom_detail_id_bahan_foreign` (`id_bahan`),
  KEY `bom_detail_id_produk_foreign` (`id_produk`),
  PRIMARY KEY (`id_bom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bom_finishing` (
  `id_bom_finishing` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `id_finishing` bigint(20) unsigned NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 1.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `bom_finishing_id_finishing_foreign` (`id_finishing`),
  KEY `bom_finishing_id_produk_foreign` (`id_produk`),
  PRIMARY KEY (`id_bom_finishing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  KEY `cache_expiration_index` (`expiration`),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  KEY `cache_locks_expiration_index` (`expiration`),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finishing` (
  `id_finishing` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_finishing` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `harga_default` decimal(15,2) NULL DEFAULT 0.00,
  `id_satuan` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_kategori` bigint(20) NULL DEFAULT NULL,
  `id_mesin` bigint(20) unsigned NULL DEFAULT NULL,
  `metode_hitung` varchar(20) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  KEY `fk_finishing_mesin` (`id_mesin`),
  PRIMARY KEY (`id_finishing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finishing_harga` (
  `id_finishing_harga` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_finishing` bigint(20) unsigned NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) NULL DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `finishing_harga_id_finishing_foreign` (`id_finishing`),
  PRIMARY KEY (`id_finishing_harga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `golongan_konsumen` (
  `id_golongan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_golongan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_golongan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `gudang` (
  `id_gudang` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_gudang` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `penanggung_jawab` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `telepon` varchar(30) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_gudang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `harga_promo` (
  `id_promo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `harga_promo` decimal(15,2) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `status_promo` enum('aktif','non-aktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  KEY `harga_promo_id_produk_foreign` (`id_produk`),
  PRIMARY KEY (`id_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jasa` (
  `id_jasa` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_jasa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_satuan` bigint(20) unsigned NULL DEFAULT NULL,
  `harga_default` decimal(15,2) NULL DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jasa_id_satuan_foreign` (`id_satuan`),
  PRIMARY KEY (`id_jasa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jasa_harga` (
  `id_jasa_harga` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_jasa` bigint(20) unsigned NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) NULL DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jasa_harga_id_jasa_foreign` (`id_jasa`),
  PRIMARY KEY (`id_jasa_harga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jenis_bahan` (
  `id_jenis_bahan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_jenis_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_categories_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_collar_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_collar_types_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jersey_product_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_category_id` bigint(20) unsigned NULL DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_components_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_components_jersey_product_type_id_foreign` (`jersey_product_type_id`),
  UNIQUE KEY `jersey_components_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_designers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_designers_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_materials_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_line_id` bigint(20) unsigned NOT NULL,
  `player_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `player_number` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `size` enum('S','M','L','XL','2XL','3XL','4XL','5XL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sleeve` varchar(50) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jersey_sleeve_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `collar` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jersey_collar_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_order_items_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  KEY `jersey_order_items_jersey_order_line_id_foreign` (`jersey_order_line_id`),
  KEY `jersey_order_items_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_order_line_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_line_id` bigint(20) unsigned NOT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `warna` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `penggunaan_kg` decimal(10,2) NULL DEFAULT NULL,
  `penggunaan_meter` decimal(10,2) NULL DEFAULT NULL,
  `penggunaan_yard` decimal(10,2) NULL DEFAULT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_order_line_materials_jersey_order_line_id_foreign` (`jersey_order_line_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_order_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_id` bigint(20) unsigned NOT NULL,
  `jersey_product_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_category_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_material_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_sleeve_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_collar_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bahan` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_type` enum('quantity_only','player_list','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quantity_only',
  `is_susulan` tinyint(1) NOT NULL DEFAULT 0,
  `qty` int(10) unsigned NOT NULL DEFAULT 0,
  `harga_satuan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `size_surcharge_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sleeve_collar_surcharge_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jersey_price_setting_id` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_order_lines_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_order_lines_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  KEY `jersey_order_lines_jersey_material_id_foreign` (`jersey_material_id`),
  KEY `jersey_order_lines_jersey_order_id_foreign` (`jersey_order_id`),
  KEY `jersey_order_lines_jersey_price_setting_id_foreign` (`jersey_price_setting_id`),
  KEY `jersey_order_lines_jersey_product_type_id_foreign` (`jersey_product_type_id`),
  KEY `jersey_order_lines_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_id` bigint(20) unsigned NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `metode_bayar` enum('Tunai','Transfer','QRIS','Lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tunai',
  `bukti_path` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_order_payments_created_by_foreign` (`created_by`),
  KEY `jersey_order_payments_jersey_order_id_foreign` (`jersey_order_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_order_size_quantities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_order_line_id` bigint(20) unsigned NOT NULL,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_order_line_size_unique` (`jersey_order_line_id`,`jersey_size_id`),
  KEY `jersey_order_size_quantities_jersey_size_id_foreign` (`jersey_size_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_date` date NOT NULL,
  `id_konsumen` bigint(20) unsigned NULL DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `team_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `deadline` date NULL DEFAULT NULL,
  `status` enum('baru','proses','selesai','diambil') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baru',
  `jersey_designer_id` bigint(20) unsigned NULL DEFAULT NULL,
  `designer_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` enum('Belum Bayar','DP','Lunas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Belum Bayar',
  `catatan_referensi_transaksi` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_orders_created_by_foreign` (`created_by`),
  KEY `jersey_orders_id_konsumen_foreign` (`id_konsumen`),
  KEY `jersey_orders_jersey_designer_id_foreign` (`jersey_designer_id`),
  UNIQUE KEY `jersey_orders_order_number_unique` (`order_number`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_price_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_product_type_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_category_id` bigint(20) unsigned NULL DEFAULT NULL,
  `jersey_material_id` bigint(20) unsigned NULL DEFAULT NULL,
  `qty_min` int(10) unsigned NOT NULL,
  `qty_max` int(10) unsigned NULL DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jersey_price_settings_jersey_category_id_foreign` (`jersey_category_id`),
  KEY `jersey_price_settings_jersey_material_id_foreign` (`jersey_material_id`),
  KEY `jersey_price_tier_idx` (`jersey_product_type_id`,`jersey_category_id`,`qty_min`,`qty_max`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_product_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_sleeve_collar` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_product_types_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_size_collar_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `jersey_collar_type_id` bigint(20) unsigned NOT NULL,
  `usage_meter` decimal(8,3) NULL DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_size_collar_unique` (`jersey_size_id`,`jersey_collar_type_id`),
  KEY `jersey_size_collar_usage_jersey_collar_type_id_foreign` (`jersey_collar_type_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_size_component_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `jersey_component_id` bigint(20) unsigned NOT NULL,
  `usage_meter` decimal(8,3) NULL DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_size_component_unique` (`jersey_size_id`,`jersey_component_id`),
  KEY `jersey_size_component_usage_jersey_component_id_foreign` (`jersey_component_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_size_sleeve_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jersey_size_id` bigint(20) unsigned NOT NULL,
  `jersey_sleeve_type_id` bigint(20) unsigned NOT NULL,
  `usage_meter` decimal(8,3) NULL DEFAULT NULL,
  `usage_weight_gram` decimal(10,2) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_size_sleeve_unique` (`jersey_size_id`,`jersey_sleeve_type_id`),
  KEY `jersey_size_sleeve_usage_jersey_sleeve_type_id_foreign` (`jersey_sleeve_type_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_sizes_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jersey_sleeve_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `jersey_sleeve_types_name_unique` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cancelled_at` int(11) NULL DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned NULL DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  KEY `jobs_queue_index` (`queue`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jurnal_umum` (
  `id_jurnal` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tgl_jurnal` date NOT NULL,
  `no_referensi` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_akun` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `debet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `jurnal_umum_kode_akun_foreign` (`kode_akun`),
  PRIMARY KEY (`id_jurnal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kartu_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `tipe` enum('IN','OUT','ADJUSTMENT') COLLATE utf8mb4_general_ci NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `stok_sebelum` decimal(15,4) NOT NULL,
  `stok_sesudah` decimal(15,4) NOT NULL,
  `referensi` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_id_bahan` (`id_bahan`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `karyawan` (
  `id_karyawan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_karyawan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bagian` enum('Admin','Desain','Produksi','Kasir','Gudang') COLLATE utf8mb4_general_ci NOT NULL,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_karyawan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `karyawan_mesin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_karyawan` bigint(20) unsigned NOT NULL,
  `id_mesin` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `karyawan_mesin_id_karyawan_id_mesin_unique` (`id_karyawan`,`id_mesin`),
  KEY `karyawan_mesin_id_mesin_foreign` (`id_mesin`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kas_harian` (
  `id_kas` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tgl_kas` date NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `saldo_awal` decimal(15,2) NOT NULL,
  `total_tunai_masuk` decimal(15,2) NULL DEFAULT 0.00,
  `total_transfer_masuk` decimal(15,2) NULL DEFAULT 0.00,
  `total_debit_masuk` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_tunai_keluar` decimal(15,2) NULL DEFAULT 0.00,
  `saldo_akhir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo_fisik` decimal(15,2) NULL DEFAULT NULL,
  `selisih` decimal(15,2) NULL DEFAULT NULL,
  `total_transfer` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_diskon` decimal(15,2) NULL DEFAULT 0.00,
  `status_lock` enum('open','closed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open',
  `waktu_tutup` timestamp NULL DEFAULT NULL,
  `keterangan_closing` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status_rolling` enum('Rolling','Tidak Rolling') COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `kas_harian_id_user_foreign` (`id_user`),
  PRIMARY KEY (`id_kas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kas_kecil` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_kategori` bigint(20) unsigned NOT NULL,
  `keterangan` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `bukti_path` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `id_user` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `kas_kecil_id_kategori_foreign` (`id_kategori`),
  KEY `kas_kecil_id_user_foreign` (`id_user`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kas_manual` (
  `id_kas_manual` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kas` bigint(20) unsigned NOT NULL,
  `waktu` datetime NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` enum('Masuk','Keluar') COLLATE utf8mb4_general_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `kas_manual_id_kas_foreign` (`id_kas`),
  KEY `kas_manual_id_user_foreign` (`id_user`),
  PRIMARY KEY (`id_kas_manual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kategori` (
  `id_kategori` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_kategori` varchar(5) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kategori_finishing` (
  `id_kategori` bigint(20) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kategori_kas_kecil` (
  `id_kategori_kas_kecil` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_akun` varchar(20) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `kategori_kas_kecil_nama_kategori_unique` (`nama_kategori`),
  PRIMARY KEY (`id_kategori_kas_kecil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kedatangan_bahan` (
  `id_kedatangan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_surat_jalan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id_supplier` bigint(20) unsigned NOT NULL,
  `id_gudang` bigint(20) unsigned NOT NULL,
  `tgl_datang` date NOT NULL,
  `status` enum('Draft','Selesai') COLLATE utf8mb4_general_ci NULL DEFAULT 'Draft',
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `kedatangan_bahan_id_gudang_foreign` (`id_gudang`),
  KEY `kedatangan_bahan_id_supplier_foreign` (`id_supplier`),
  PRIMARY KEY (`id_kedatangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kedatangan_bahan_detail` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kedatangan` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` enum('OK','Rusak','Kurang') COLLATE utf8mb4_general_ci NULL DEFAULT 'OK',
  KEY `kedatangan_bahan_detail_id_bahan_foreign` (`id_bahan`),
  KEY `kedatangan_bahan_detail_id_kedatangan_foreign` (`id_kedatangan`),
  PRIMARY KEY (`id_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `konsumen` (
  `id_konsumen` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_konsumen` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_konsumen_manual` varchar(50) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `id_golongan` bigint(20) unsigned NOT NULL,
  `tipe_konsumen` enum('permanent','temporary') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'temporary',
  `tgl_registrasi` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `konsumen_id_golongan_foreign` (`id_golongan`),
  PRIMARY KEY (`id_konsumen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `konsumen_deposit` (
  `id_deposit` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_konsumen` bigint(20) unsigned NOT NULL,
  `tipe` enum('Setoran','Pemakaian') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Setoran',
  `id_transaksi` bigint(20) unsigned NULL DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode_bayar` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Tunai',
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `tgl_deposit` date NOT NULL,
  `id_user` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `konsumen_deposit_id_konsumen_foreign` (`id_konsumen`),
  KEY `konsumen_deposit_id_transaksi_foreign` (`id_transaksi`),
  KEY `konsumen_deposit_id_user_foreign` (`id_user`),
  PRIMARY KEY (`id_deposit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menus` (
  `id_menu` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_menu` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `url_link` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `merk` (
  `id_merk` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_merk` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_merk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mesin` (
  `id_mesin` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_mesin` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_mesin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nota_edit_request` (
  `id_request` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaksi` bigint(20) unsigned NOT NULL,
  `id_user_requester` bigint(20) unsigned NOT NULL,
  `alasan` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak','Selesai') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Menunggu',
  `id_user_approver` bigint(20) unsigned NULL DEFAULT NULL,
  `catatan_approver` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `tgl_keputusan` timestamp NULL DEFAULT NULL,
  `tgl_selesai` timestamp NULL DEFAULT NULL,
  `id_transaksi_koreksi` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `nota_edit_request_id_transaksi_foreign` (`id_transaksi`),
  KEY `nota_edit_request_id_user_approver_foreign` (`id_user_approver`),
  KEY `nota_edit_request_id_user_requester_foreign` (`id_user_requester`),
  PRIMARY KEY (`id_request`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pembayaran_riwayat` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` enum('Tunai','Transfer','Debit','Deposit') COLLATE utf8mb4_general_ci NOT NULL,
  `id_bank` bigint(20) unsigned NULL DEFAULT NULL,
  `tgl_bayar` datetime NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  KEY `pembayaran_riwayat_id_bank_foreign` (`id_bank`),
  PRIMARY KEY (`id_pembayaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pembelian_bahan` (
  `id_pembelian` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_kedatangan` bigint(20) unsigned NOT NULL,
  `no_faktur` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `tipe_pembelian` varchar(50) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `jatuh_tempo_hari` int(11) NULL DEFAULT NULL,
  `jatuh_tempo_tgl` date NULL DEFAULT NULL,
  `status_pajak` varchar(50) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `include_pajak` tinyint(1) NULL DEFAULT 0,
  `grand_total` decimal(15,2) NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_pembayaran` enum('Hutang','Lunas') COLLATE utf8mb4_general_ci NULL DEFAULT 'Hutang',
  `tgl_pelunasan` timestamp NULL DEFAULT NULL,
  KEY `pembelian_bahan_id_kedatangan_foreign` (`id_kedatangan`),
  UNIQUE KEY `pembelian_bahan_no_faktur_unique` (`no_faktur`),
  PRIMARY KEY (`id_pembelian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pembelian_bahan_detail` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pembelian` bigint(20) unsigned NOT NULL,
  `id_bahan` bigint(20) unsigned NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) NULL DEFAULT 0.00,
  `potongan` decimal(15,2) NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  KEY `id_bahan` (`id_bahan`),
  KEY `id_pembelian` (`id_pembelian`),
  PRIMARY KEY (`id_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `produk` (
  `id_produk` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `barcode` varchar(50) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `id_mesin` int(11) NULL DEFAULT NULL,
  `metode_hitung` enum('ukuran','quantity') COLLATE utf8mb4_general_ci NOT NULL,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_panjang` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_lebar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `produk_finishing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` int(11) NULL DEFAULT NULL,
  `id_finishing` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `id_role` bigint(20) unsigned NOT NULL,
  `id_menu` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_role`,`id_menu`),
  KEY `role_permissions_id_menu_foreign` (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `satuan` (
  `id_satuan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nilai_konversi` decimal(10,2) NOT NULL,
  `tipe_satuan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Barang',
  PRIMARY KEY (`id_satuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint(20) unsigned NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  KEY `sessions_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `setting_harga` (
  `id_harga` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_produk` bigint(20) unsigned NOT NULL,
  `id_golongan` bigint(20) unsigned NOT NULL,
  `range_min` decimal(10,2) NOT NULL DEFAULT 1.00,
  `range_max` decimal(10,2) NULL DEFAULT NULL,
  `harga_jual` decimal(15,2) NOT NULL,
  `id_satuan` bigint(20) unsigned NULL DEFAULT NULL,
  `qty_berlaku` int(11) NULL DEFAULT 1,
  `id_satuan_berlaku` bigint(20) unsigned NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `fk_setting_harga_satuan` (`id_satuan`),
  KEY `fk_setting_harga_satuan_berlaku` (`id_satuan_berlaku`),
  PRIMARY KEY (`id_harga`),
  KEY `setting_harga_id_golongan_foreign` (`id_golongan`),
  KEY `setting_harga_id_produk_foreign` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `supplier` (
  `id_supplier` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `kontak` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transaksi` (
  `id_transaksi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_nota` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_order` enum('Reguler','PO') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Reguler',
  `no_po` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `id_konsumen` bigint(20) unsigned NOT NULL,
  `nama_konsumen_manual` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `id_user_kasir` bigint(20) unsigned NOT NULL,
  `tgl_order` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_bayar` decimal(15,2) NOT NULL,
  `status_pembayaran` enum('Lunas','DP','Belum Bayar') COLLATE utf8mb4_general_ci NOT NULL,
  `status_nota` enum('Aktif','Dibatalkan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `alasan_batal_nota` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `tgl_dibatalkan_nota` timestamp NULL DEFAULT NULL,
  `id_user_pembatal_nota` bigint(20) unsigned NULL DEFAULT NULL,
  `status_collecting` enum('Belum Diambil','Sudah Diambil') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Belum Diambil',
  `tgl_collecting` timestamp NULL DEFAULT NULL,
  `id_user_collecting` bigint(20) unsigned NULL DEFAULT NULL,
  `metode_pembayaran` varchar(50) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_transaksi`),
  KEY `transaksi_id_konsumen_foreign` (`id_konsumen`),
  KEY `transaksi_id_user_collecting_foreign` (`id_user_collecting`),
  KEY `transaksi_id_user_kasir_foreign` (`id_user_kasir`),
  KEY `transaksi_id_user_pembatal_nota_foreign` (`id_user_pembatal_nota`),
  UNIQUE KEY `transaksi_nomor_nota_unique` (`nomor_nota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transaksi_detail` (
  `id_detail` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaksi` bigint(20) unsigned NOT NULL,
  `id_produk` bigint(20) unsigned NULL DEFAULT NULL,
  `id_jasa` bigint(20) unsigned NULL DEFAULT NULL,
  `id_bahan` bigint(20) unsigned NULL DEFAULT NULL,
  `id_finishing` bigint(20) unsigned NULL DEFAULT NULL,
  `biaya_finishing` decimal(15,2) NULL DEFAULT 0.00,
  `biaya_cetak` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_desain` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nama_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `p_ukuran` decimal(10,2) NOT NULL,
  `l_ukuran` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `status_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `status_urgensi` enum('Normal','Urgent','Very Urgent') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Normal',
  `status_finishing` enum('Standar','Custom') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Standar',
  `qty_cetak` int(11) NULL DEFAULT NULL,
  `catatan_produksi` text COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hpp_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual_snapshot` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_spk` enum('Antre','Desain','Cetak','Selesai','Batal','Pending') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Antre',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `transaksi_detail_id_bahan_foreign` (`id_bahan`),
  KEY `transaksi_detail_id_finishing_foreign` (`id_finishing`),
  KEY `transaksi_detail_id_jasa_foreign` (`id_jasa`),
  KEY `transaksi_detail_id_produk_foreign` (`id_produk`),
  KEY `transaksi_detail_id_transaksi_foreign` (`id_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id_user` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_karyawan` bigint(20) unsigned NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id_role` bigint(20) unsigned NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  KEY `users_id_karyawan_foreign` (`id_karyawan`),
  KEY `users_id_role_foreign` (`id_role`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `worksheet` (
  `id_worksheet` bigint(20) NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(100) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_worksheet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
