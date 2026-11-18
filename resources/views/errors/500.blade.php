@extends('errors.master', [
    'kode'  => '500',
    'judul' => 'Terjadi Kesalahan pada Server',
    'pesan' => 'Sistem sedang mengalami gangguan atau kesalahan internal. Tim teknis akan memeriksa segera. Coba beberapa saat lagi; hubungi Administrator jika masalah berlanjut.',
    'detail' => 'Kode kesalahan HTTP 500 — Internal Server Error',
    'iconStyle' => '--icon-bg: color-mix(in srgb, var(--color-amber-600) 8%, transparent); --icon-line: color-mix(in srgb, var(--color-amber-600) 18%, transparent);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="var(--color-amber-700)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Terjadi kesalahan pada server"><path d="M4 14a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4Z"/><path d="M8 12V7a4 4 0 0 1 8 0v5"/><path d="M10 18h.01M14 18h.01"/></svg>',
    'pintasan' => [],
])
