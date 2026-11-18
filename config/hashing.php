<?php

/**
 * Penguatan konfigurasi produksi: bcrypt rounds dinaikkan dari default 10
 * ke 12 (faktor waktu 4x per hash - biaya brute-force offline naik sebanding,
 * dampaknya ke runtime cuma di momen buat/ganti password, tidak di tiap
 * request: verifikasi password tetap 1x per login).
 *
 * File ini sebelumnya TIDAK ADA - aplikasi memakai default framework
 * (rounds 10). Menyentuh .env dilarang, jadi standar keamanan ditegakkan
 * lewat fallback konfigurasi di sini.
 */

return [
    'driver' => 'bcrypt',

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

    'argon2id' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

    'verify' => true,
];
