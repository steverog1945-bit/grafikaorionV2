@extends('errors.master', [
    'kode'  => '503',
    'judul' => 'Layanan Sedang Tidak Tersedia',
    'pesan' => 'Sistem sedang dalam pemeliharaan atau kelebihan beban. Silakan coba lagi beberapa saat; setiap kegagalan sudah tercatat untuk tim teknis.',
    'detail' => 'Kode kesalahan HTTP 503 — Service Unavailable',
    'iconStyle' => '--icon-bg: color-mix(in srgb, var(--color-slate-500) 8%, transparent); --icon-line: color-mix(in srgb, var(--color-slate-500) 18%, transparent);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="var(--color-slate-600)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Sistem tidak tersedia"><path d="M3 12h4l3 9 4-18 3 9h4"/></svg>',
    'pintasan' => [],
])
