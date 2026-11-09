<?php

// Backup & Recovery (roadmap LEVEL 1 - WAJIB). Lihat App\Services\BackupService.
return [
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

    'disk_path' => storage_path('app/backups'),

    // Hardening P2-2: salinan KEDUA di luar server (syarat deploy yang
    // sudah dicatat sendiri di proyek ini) - nama disk dari
    // config/filesystems.php (mis. 's3', bisa diarahkan ke penyedia
    // S3-compatible manapun lewat env AWS_*/AWS_ENDPOINT). Kosong/null =
    // fitur ini dimatikan (tidak ada salinan kedua), TIDAK menghentikan
    // backup lokal - backup lokal tetap berjalan seperti sebelumnya.
    'secondary_disk' => env('BACKUP_SECONDARY_DISK'),

    // Enkripsi dump at-rest (audit run-1: mysqldump ditulis sebagai SQL teks
    // biasa; pengguna lokal dapat membaca seluruh PII + hash kredensial).
    // Format v1: magic "JLA1" || 12-byte IV || 16-byte tag || ciphertext (AES-256-GCM).
    // Backup terenkripsi WAJIB didekripsi manual saat restore — lihat kunci
    // BACKUP_ENCRYPTION_KEY di .env (salin ke password manager owner, lalu hapus dari disk).
    'encryption' => env('BACKUP_ENCRYPTION_ENABLED', false),
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),

    // Jumlah file terbaru yang disimpan per tipe - file di luar batas ini
    // otomatis dihapus setelah backup baru berhasil dibuat.
    'retention' => [
        'daily' => 7,
        'weekly' => 4,
        'monthly' => 12,
        'manual' => 10,
    ],
];
