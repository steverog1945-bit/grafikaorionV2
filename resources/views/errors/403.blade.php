@extends('errors.master', [
    'kode'  => '403',
    'judul' => 'Akses Ditolak',
    'pesan' => 'Akun Anda tidak memiliki hak untuk membuka halaman ini. Bila Anda merasa seharusnya bisa, hubungi Administrator untuk ditambahkan izin yang sesuai.',
    'detail' => 'Kode kesalahan HTTP 403 — Forbidden',
    'iconStyle' => '--icon-bg: color-mix(in srgb, var(--color-rose-700) 8%, transparent); --icon-line: color-mix(in srgb, var(--color-rose-700) 18%, transparent);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="var(--color-rose-700)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Akses ditolak"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14v2.5"/></svg>',
    'pintasan' => [],
])
