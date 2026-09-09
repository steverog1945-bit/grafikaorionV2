@props([
    'wrapperClass' => 'bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden',
    'bare'         => false, // true = skip wrapper card, dipakai saat tabel sudah ada di dalam card/panel lain
])

@if($bare)
    <div class="modern-table-wrapper !shadow-none !border-0">
        <table {{ $attributes->merge(['class' => 'modern-table']) }}>
            {{ $slot }}
        </table>
    </div>
@else
    <div class="{{ $wrapperClass }}">
        <div class="modern-table-wrapper">
            <table {{ $attributes->merge(['class' => 'modern-table']) }}>
                {{ $slot }}
            </table>
        </div>
    </div>
@endif
