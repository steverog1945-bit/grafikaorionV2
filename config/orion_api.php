<?php

// API eksternal (Android/iOS/integrasi) belum siap dipakai operasional.
// Secara default OFF agar tidak mengekspos data dan jalur transaksi yang
// masih menunggu audit RBAC, rate limiting, dan kesetaraan checkout.
return [
    'enabled' => (bool) env('ORION_PUBLIC_API_ENABLED', false),

    /*
    | Peta RBAC endpoint API: prefix path (TANPA 'api/') => daftar id_menu
    | yang boleh mengakses. Sumber kebenarannya SAMA dengan web: tabel
    | role_permissions/user_weights lewat App\Support\Izin::boleh(), jadi
    | satu hak akses berlaku konsisten di web dan di API - tidak ada sistem
    | izin kedua yang bisa saling mengalahkan.
    |
    | Nilai biasa  = daftar id_menu (semua metode HTTP).
    | Nilai peta   = ['GET' => [...], 'POST' => [...]] bila hak lihat dan hak
    |                tulis terpisah (mirip pemisahan 'penjualan' vs
    |                'penjualan/riwayat' di config/menu_access.php).
    |
    | Endpoint yang BELUM ada di daftar ini ditolak 403 (fail-closed) untuk
    | semua role non-Administrator - endpoint baru tidak bocor begitu saja
    | sebelum dipetakan. Aksi diambil dari metode HTTP: GET/view, POST/add,
    | PUT-PATCH/edit, DELETE/delete (Izin::aksiDariMetode).
    */
    'menus' => [
        // Master data untuk integrasi kasir/input order - selaras dengan
        // 'api/penjualan' dan 'konsumen/store-ajax' => [3, 41] di web:
        // pemegang Transaksi Baru (3) / Input Order (41) boleh membaca
        // daftar produk & konsumen, belum berhak menu master (13/7).
        'konsumen' => [3, 41],
        'produk' => [3, 41],

        // Nota: controller index/show/store ter-scope id_user_kasir
        // (anti-IDOR), jadi role di sini hanya melihat nota miliknya.
        'transaksi' => [
            // Lihat nota ≈ 'penjualan/riwayat' => [3, 61] di web.
            'GET' => [3, 61],
            // Buat nota ≈ create/store 'penjualan' => [3, 41] di web.
            'POST' => [3, 41],
        ],

        // Ledger pembayaran bagian modul kasir ('kasir' => [16]).
        'pembayaran' => [16],

        // Antrean produksi ('spk' => [22]).
        'spk' => [22],

        // Stok bahan: gudang [1] / laporan stok [46].
        'stok' => [1, 46],

        // Laporan keuangan - selaras web tanpa pelonggaran apa pun.
        'laporan/penjualan' => [25],
        'laporan/piutang' => [44],
        'laporan/kas' => [29],

        // Angka agregat keuangan (omzet, kas, piutang dalam dashboard)
        // setara Dashboard Eksekutif => [60]: khusus Administrator/owner
        // sampai owner secara eksplisit memberi menu 60 ke role lain.
        'dashboard/kpi' => [60],
    ],
];
