@extends('errors.master', [
    'kode'  => '503',
    'judul' => 'Layanan Sedang Tidak Tersedia',
    'pesan' => 'Sistem sedang dalam pemeliharaan atau kelebihan beban. Silakan coba lagi beberapa saat; setiap kegagalan sudah tercatat untuk tim teknis.',
    'detail' => 'Kode kesalahan HTTP 503 — Service Unavailable',
    'iconStyle' => '--icon-bg: rgba(100,116,139,.08); --icon-line: rgba(100,116,139,.18);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Sistem tidak tersedia"><path d="M3 12h4l3 9 4-18 3 9h4"/></svg>',
    'pintasan' => [],
])
