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
        // Stock Opname - sama level akses dengan bahan-baku/kartu-stok
        // (staff gudang yang menghitung fisik). Persetujuan hasil hitung
        // (baru benar-benar mengubah stok) tetap lewat /approval yang
        // sudah khusus Administrator - bukan di sini.
        'stock-opname' => [1],

        // 'penjualan/riwayat' & 'penjualan/edit-nota' sengaja HANYA [3]
        // (bukan ikut [41]) - role yang cuma dikasih id_menu 41 ("input
        // order saja", mis. Operator Desain) harus tetap 403 di sini,
        // walau base 'penjualan' di bawah dia dapat akses (untuk
        // create/store/show - show WAJIB tetap terbuka karena itu halaman
        // redirect sukses setelah submit order baru). Route mutasi yang
        // path-nya '/{id}/aksi' (batalkan-nota, request-edit) tidak bisa
        // dipisah lewat prefix di sini sama sekali (id ada SEBELUM segmen
        // pembeda) - itu digembok lewat middleware restrict.input-only
        // langsung di route-nya (lihat routes/web.php).
        'penjualan/riwayat' => [3],
        'penjualan/edit-nota' => [3],
        'penjualan' => [3, 41],
        // Penjualan Barang HANYA Kasir (3) - beda dari Input Order biasa
        // yang juga dibuka untuk Operator Desain (41), jual bahan baku
        // langsung bukan bagian dari alur kerja desainer.
        'penjualan-barang' => [3],
        // 'api/penjualan/produk/{id}' dipanggil AJAX oleh halaman Kasir
        // Utama sendiri (ambil detail harga produk saat dipilih) - WAJIB
        // ikut [41], kalau tidak pemilihan produk gagal total untuk role
        // yang cuma dikasih akses input-order (ditemukan langsung lewat
        // uji coba nyata: "Gagal ambil produk", 403).
        'api/penjualan' => [3, 41],
        'konsumen/store-ajax' => [3, 41],

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
        // Purchase Request & Purchase Order - sama level akses dengan
        // pembelian-bahan (persetujuan PR tetap lewat /approval, admin-only).
        'purchase-request' => [20],
        'purchase-order' => [20],
        'finishing' => [21],
        'spk' => [22],
        'produksi' => [23],
        'collecting' => [24],
        'laporan/order' => [25],
        'laporan/produksi' => [26],
        'laporan/kas' => [29],
        'laporan/belanja-bahan' => [40],
        'laporan/order-jersey' => [43],
        'laporan/piutang' => [44],
        'laporan/stok-bahan' => [46],
        'laporan/hpp' => [47],
        'harga-promo' => [28],

        'kas-kecil' => [39],
        'kategori-kas-kecil' => [45],

        'merk' => [30],
        'jenis-bahan' => [31],
        'jasa' => [32],
        'gudang' => [33],
        'konsumen-deposit' => [34],

        'akun' => [35],
        'akuntansi' => [36],
        // Log Aktivitas (Audit Trail) - khusus Administrator, tidak ada
        // id_menu lain yang diberi akses (Administrator selalu lolos
        // CheckMenuAccess tanpa perlu masuk daftar ini sama sekali, jadi
        // baris ini murni jaring pengaman kalau suatu saat perlu dibuka
        // untuk role lain juga).
        'log-aktivitas' => [],
        'approval' => [],
        'accounting-period' => [],
        'backup' => [],

        // Sama pola dengan 'penjualan' di atas: 'jersey/master' &
        // 'jersey/keuangan' HANYA [38] (bukan ikut [42]). Route mutasi
        // yang path-nya '/{order}/aksi' (update, destroy, edit-lines,
        // update-lines, lines/susulan) tidak bisa dipisah lewat prefix
        // (order id ada SEBELUM segmen pembeda) - digembok lewat
        // middleware restrict.input-only langsung di route-nya.
        'jersey/master' => [38],
        'jersey/keuangan' => [38],
        'jersey/create' => [38, 42],
        'jersey/store' => [38, 42],
        'jersey/cek-kelengkapan' => [38, 42],
        'jersey/preview-harga' => [38, 42],
        'jersey/upload-thumbnail' => [38, 42],
        // Base 'jersey' menaungi GET / (daftar order - index) DAN
        // GET/{order} (show, dibutuhkan sebagai halaman redirect sukses
        // setelah submit order baru) - role id_menu 42 saja otomatis bisa
        // juga membuka index & show barang orang lain (kompromi yang
        // disadari, lihat catatan di RestrictInputOnlyRoles), tapi TIDAK
        // bisa update/hapus/edit-lines/susulan karena route itu digembok
        // middleware terpisah di atas.
        'jersey' => [38, 42],
    ],

    'always_allow' => [
        'dashboard',
        'logout',
        'storage',
        'up',
    ],
];
