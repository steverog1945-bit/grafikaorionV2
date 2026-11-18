@props([
    'variant' => 'primary', // primary | secondary | success | warning | danger | outline
    'size'    => 'default',  // default | sm | icon
    'type'    => 'button',
    'href'    => null,
])

@php
    // OpenCode Desktop Aesthetic: compact, consistent, no excessive styling
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium text-[13px] tracking-normal transition-transform transition-background-color transition-border-color active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100';

    $variants = [
        'primary'   => 'bg-[var(--brand-sidebar-accent,var(--color-indigo-600))] hover:brightness-110 text-white shadow-sm',
        'secondary' => 'bg-[var(--surface-secondary,var(--color-slate-100))] hover:bg-[var(--surface-tertiary,var(--color-slate-200))] text-[var(--text-primary,var(--color-slate-900))] border border-[var(--border-primary,var(--color-slate-200))]',
        'success'   => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm',
        'warning'   => 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm',
        'danger'    => 'bg-rose-500 hover:bg-rose-600 text-white shadow-sm',
        'outline'   => 'bg-transparent border border-[var(--border-primary,var(--color-slate-300))] text-[var(--text-primary,var(--color-slate-900))] hover:bg-[var(--surface-secondary,var(--color-slate-50))]',
        'ghost'     => 'bg-transparent text-[var(--text-secondary,var(--color-slate-600))] hover:text-[var(--text-primary,var(--color-slate-900))] hover:bg-[var(--surface-secondary,var(--color-slate-100))]',
    ];

    $sizes = [
        'default' => 'px-4 py-2',
        'sm'      => 'px-3 py-1.5 text-xs',
        'icon'    => '!p-0 w-8 h-8 !rounded-lg',
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
