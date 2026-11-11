@extends('errors.master', [
    'kode'  => '403',
    'judul' => 'Akses Ditolak',
    'pesan' => 'Akun Anda tidak memiliki hak untuk membuka halaman ini. Bila Anda merasa seharusnya bisa, hubungi Administrator untuk ditambahkan izin yang sesuai.',
    'detail' => 'Kode kesalahan HTTP 403 — Forbidden',
    'iconStyle' => '--icon-bg: rgba(190,18,61,.08); --icon-line: rgba(190,18,61,.18);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="#be123d" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Akses ditolak"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14v2.5"/></svg>',
    'pintasan' => [],
])
