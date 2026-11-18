@extends('errors.master', [
    'kode'  => '404',
    'judul' => 'Halaman Tidak Ditemukan',
    'pesan' => 'Alamat yang Anda buka tidak ada atau sudah dipindahkan. Periksa kembali tautan, atau kembali ke halaman utama aplikasi.',
    'detail' => 'Kode kesalahan HTTP 404 — Not Found',
    'iconStyle' => '--icon-bg: color-mix(in srgb, var(--color-indigo-600) 8%, transparent); --icon-line: color-mix(in srgb, var(--color-indigo-600) 18%, transparent);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="var(--color-indigo-600)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Halaman tidak ditemukan"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/><path d="M8.5 11h5"/></svg>',
    'pintasan' => [],
])
