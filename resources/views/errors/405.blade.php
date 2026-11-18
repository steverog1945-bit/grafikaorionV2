@extends('errors.master', [
    'kode'  => '405',
    'judul' => 'Metode Tidak Diizinkan',
    'pesan' => 'Halaman ini tidak menerima jenis aksi yang Anda kirim. Kembali ke halaman sebelumnya, lalu coba lagi dari sana.',
    'detail' => 'Kode kesalahan HTTP 405 — Method Not Allowed',
    'iconStyle' => '--icon-bg: color-mix(in srgb, var(--color-indigo-600) 8%, transparent); --icon-line: color-mix(in srgb, var(--color-indigo-600) 18%, transparent);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="var(--color-indigo-600)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Metode tidak diizinkan"><circle cx="12" cy="12" r="9"/><path d="M6 6l12 12"/></svg>',
    'pintasan' => [],
])
