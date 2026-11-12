@props([
    'tip' => '',
    'icon' => 'circle-info',
    'tone' => 'slate',
    'align' => 'end',
])
{{-- Ikon petunjuk/keterangan kontekstual - menggantikan kalimat hint
     panjang di bawah label atau di dalam kartu. Tip muncul sebagai tooltip
     saat hover/fokus (title sebagai fallback tanpa CSS). tone: slate|indigo|emerald|amber|rose.
     align: start|end - posisi tooltip agar tidak terpotong di tepi kartu. --}}
@php
    $tones = [
        'slate'   => 'text-slate-300 hover:text-slate-500 hover:bg-slate-100',
        'indigo'  => 'text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50',
        'emerald' => 'text-emerald-500 hover:text-emerald-600 hover:bg-emerald-50',
        'amber'   => 'text-amber-400 hover:text-amber-600 hover:bg-amber-50',
        'rose'    => 'text-rose-400 hover:text-rose-600 hover:bg-rose-50',
    ];
    $posisi = $align === 'start' ? 'left-0' : 'right-0';
    $panah  = $align === 'start' ? 'left-3' : 'right-3';
@endphp
<span class="group/hint relative inline-flex items-center align-middle">
    <button type="button" tabindex="0"
            aria-label="{{ $tip }}"
            title="{{ $tip }}"
            class="w-5 h-5 rounded-full inline-flex items-center justify-center transition-colors duration-200 {{ $tones[$tone] ?? $tones['slate'] }} focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
        <i class="fas fa-{{ $icon }} text-[11px]"></i>
    </button>
    <span role="tooltip"
          class="pointer-events-none absolute bottom-full {{ $posisi }} mb-2 z-50 w-64 max-w-[min(16rem,80vw)] whitespace-normal rounded-xl bg-slate-800/95 backdrop-blur-sm px-3 py-2 text-[11px] font-normal leading-snug text-slate-100 text-left normal-case tracking-normal shadow-xl shadow-slate-900/20 ring-1 ring-white/10 opacity-0 translate-y-1 scale-95 transition-all duration-200 group-hover/hint:opacity-100 group-hover/hint:translate-y-0 group-hover/hint:scale-100 group-focus-within/hint:opacity-100 group-focus-within/hint:translate-y-0 group-focus-within/hint:scale-100">
        {{ $tip }}
        <span class="absolute top-full {{ $panah }} border-[6px] border-transparent border-t-slate-800/95"></span><span class="hidden"></span>
    </span>
</span>
