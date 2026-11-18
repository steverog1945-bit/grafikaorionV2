@props([
    'path'     => null,           // path relatif di storage - ekstensi diambil dari sini
    'ext'      => null,           // opsional: teks ekstensi siap pakai (CDR, AI, ...)
    'size'     => 'w-14 h-14',    // ukuran penuh tile (w-* h-*)
    'radius'   => 'rounded-lg',
    'iconSize' => 'text-lg',
    'extSize'  => 'text-[9px]',
])
@php
    $label = strtoupper(trim((string) ($ext ?? ($path ? \App\Support\BerkasDesain::ekstensi($path) : ''))));
    if ($label === '') {
        $label = 'FILE';
    }
@endphp
<span {{ $attributes->merge(['class' => "thumbnail-placeholder thumb-filetype shrink-0 bg-white border border-slate-300 {$radius} flex flex-col items-center justify-center gap-0.5 text-slate-600 select-none {$size}"]) }}
      role="img"
      aria-label="Berkas desain {{ $label }} (tidak bisa ditampilkan di peramban)"
      title="Berkas desain {{ $label }} — tidak bisa ditampilkan di peramban; file ada di server, dibuka tim produksi">
    <i class="fas fa-file {{ $iconSize }} leading-none" aria-hidden="true"></i>
    <span class="thumb-filetype-ext font-bold tracking-widest leading-none {{ $extSize }}">{{ $label }}</span>
</span>
