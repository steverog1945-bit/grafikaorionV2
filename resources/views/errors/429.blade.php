@extends('errors.master', [
    'kode'  => '429',
    'judul' => 'Terlalu Banyak Permintaan',
    'pesan' => 'Anda mengirim permintaan terlalu sering dalam waktu singkat, sehingga sistem menundanya sebentar. Tunggu beberapa detik, lalu coba lagi.',
    'detail' => 'Kode kesalahan HTTP 429 — Too Many Requests',
    'iconStyle' => '--icon-bg: rgba(190,18,61,.08); --icon-line: rgba(190,18,61,.18);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="#be123d" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Terlalu banyak permintaan"><path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5"/><path d="M12 15h.01"/></svg>',
    'pintasan' => [],
])
