@extends('errors.master', [
    'kode'  => '404',
    'judul' => 'Halaman Tidak Ditemukan',
    'pesan' => 'Alamat yang Anda buka tidak ada atau sudah dipindahkan. Periksa kembali tautan, atau kembali ke halaman utama aplikasi.',
    'detail' => 'Kode kesalahan HTTP 404 — Not Found',
    'iconStyle' => '--icon-bg: rgba(79,70,229,.08); --icon-line: rgba(79,70,229,.18);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Halaman tidak ditemukan"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/><path d="M8.5 11h5"/></svg>',
    'pintasan' => [],
])
