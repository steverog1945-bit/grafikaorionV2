<?php

/**
 * Peta akses per-URL untuk middleware CheckMenuAccess. Ini adalah SUMBER
 * KEBENARAN penegakan hak akses di level route - sebelumnya role_permissions
 * di database hanya dipakai untuk menyembunyikan/menampilkan link sidebar
 * (lihat layouts/app.blade.php), TIDAK pernah dicek ulang di controller/route,
 * jadi siapa pun yang login bisa membuka URL apa pun langsung.
 *
 * 'rules': prefix URL => daftar id_menu yang boleh mengakses (cukup salah
 * satu id_menu tersebut ada di role_permissions milik role user).
 * Middleware memilih prefix TERPANJANG yang cocok, jadi urutan penulisan di
 * sini tidak masalah (mis. 'konsumen/store-ajax' otomatis lebih diutamakan
 * daripada 'konsumen' walau keduanya cocok).
 *
 * 'always_allow': prefix yang boleh diakses siapa pun yang sudah login,
 * tanpa perlu menu apa pun (dashboard, logout, dsb).
 *
 * Administrator SELALU lolos tanpa dicek daftar ini (lihat middleware) -
 * supaya admin tidak pernah terkunci sendiri akibat mapping yang kurang.
 */
return [
    'rules' => [
        'bahan-baku' => [1],
        'kartu-stok' => [1],

        'penjualan' => [3],
        'api/penjualan' => [3],
        'konsumen/store-ajax' => [3],

        'setting-akses' => [4],
        'karyawan' => [5],
        'users' => [6],
        'konsumen' => [7],
        'golongan' => [8, 12, 18],
        'supplier' => [9],
        'satuan' => [10],
        'bank' => [11],
        'produk' => [13],
        'setting-harga' => [14],
        'kategori' => [15],

        'kasir' => [16],

        'kedatangan-bahan' => [17],
        'ajax/bahan' => [17],

        'mesin' => [27],

        'pembelian-bahan' => [20],
        'finishing' => [21],
        'spk' => [22],
        'produksi' => [23],
        'collecting' => [24],
        'laporan/order' => [25],
        'laporan/produksi' => [26],
        'laporan/kas' => [29],
        'harga-promo' => [28],

        'merk' => [30],
        'jenis-bahan' => [31],
        'jasa' => [32],
        'gudang' => [33],
        'konsumen-deposit' => [34],
    ],

    'always_allow' => [
        'dashboard',
        'logout',
        'storage',
        'up',
    ],
];
