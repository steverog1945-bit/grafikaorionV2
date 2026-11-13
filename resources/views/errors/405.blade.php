@extends('errors.master', [
    'kode'  => '405',
    'judul' => 'Metode Tidak Diizinkan',
    'pesan' => 'Halaman ini tidak menerima jenis aksi yang Anda kirim. Kembali ke halaman sebelumnya, lalu coba lagi dari sana.',
    'detail' => 'Kode kesalahan HTTP 405 — Method Not Allowed',
    'iconStyle' => '--icon-bg: rgba(79,70,229,.08); --icon-line: rgba(79,70,229,.18);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Metode tidak diizinkan"><circle cx="12" cy="12" r="9"/><path d="M6 6l12 12"/></svg>',
    'pintasan' => [],
])
