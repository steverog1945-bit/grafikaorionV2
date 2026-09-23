<?php

// Backup & Recovery (roadmap LEVEL 1 - WAJIB). Lihat App\Services\BackupService.
return [
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

    'disk_path' => storage_path('app/backups'),

    // Jumlah file terbaru yang disimpan per tipe - file di luar batas ini
    // otomatis dihapus setelah backup baru berhasil dibuat.
    'retention' => [
        'daily' => 7,
        'weekly' => 4,
        'monthly' => 12,
        'manual' => 10,
    ],
];
