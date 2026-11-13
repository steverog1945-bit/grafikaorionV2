@extends('errors.master', [
    'kode'  => '419',
    'judul' => 'Sesi Berakhir',
    'pesan' => 'Sesi masuk Anda telah berakhir - terlalu lama tidak aktif, atau token halaman sudah tidak berlaku. Muat ulang halaman ini, lalu masuk kembali.',
    'detail' => 'Kode kesalahan HTTP 419 — Page Expired',
    'iconStyle' => '--icon-bg: rgba(217,119,6,.08); --icon-line: rgba(217,119,6,.18);',
    'ikon' => '<svg viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Sesi berakhir"><circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/></svg>',
    'pintasan' => [],
])
