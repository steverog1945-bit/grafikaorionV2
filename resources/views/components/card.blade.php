@props([
    'title'    => null,
    'subtitle' => null,
    'padding'  => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-100 shadow-sm']) }}>
    @if($title)
        {{-- flex-wrap: judul + actions (tombol) dipakai di HAMPIR SEMUA
             modul lewat komponen ini - tanpa wrap, tombol berteks panjang
             menembus keluar kartu di layar sempit (lihat audit mobile). --}}
        <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-slate-800">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
