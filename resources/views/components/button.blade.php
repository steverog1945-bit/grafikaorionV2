@props([
    'variant' => 'primary', // primary | secondary | success | warning | danger | outline
    'size'    => 'default',  // default | sm | icon
    'type'    => 'button',
    'href'    => null,
])

@php
    // Rombak visual (tahap 2): tanpa ALL-CAPS paksa - label kalimat normal
    // terasa lebih manusiawi dan tetap jelas; state tetap komunikatif.
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold tracking-normal transition-all active:scale-[0.985] disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100';

    $variants = [
        // Glass (ui-refresh.css): gradien indigo lembut + blur + inner highlight.
        'primary'   => 'btn-glass-primary text-white',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-500',
        'success'   => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-100',
        'warning'   => 'bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-100',
        'danger'    => 'bg-rose-500 hover:bg-rose-600 text-white shadow-lg shadow-rose-100',
        'outline'   => 'bg-transparent border border-slate-200 text-slate-500 hover:bg-slate-50',
        // Tanpa hover bawaan - dipakai untuk icon button di tabel yang tiap
        // instance-nya butuh warna hover berbeda (edit=amber, hapus=rose,
        // dst) lewat prop class, tanpa risiko rebutan specificity dgn variant.
        'ghost'     => 'bg-transparent text-slate-400',
    ];

    $sizes = [
        'default' => 'text-xs px-6 py-3',
        'sm'      => 'text-[10px] px-4 py-2',
        // Icon-only: bentuk persegi, tanpa tracking/uppercase pada slot ikon
        'icon'    => '!p-0 w-9 h-9 !rounded-lg normal-case tracking-normal',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
