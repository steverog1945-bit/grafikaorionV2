@props([
    'label'    => null,
    'name'     => null,
    'required' => false,
    'help'     => null,
    // dalam=true: label diletakkan di DALAM kolom (gaya kolom berlabel di dalam; lihat ui-refresh.css bagian 25).
    // Label besar seperti placeholder saat kolom kosong, mengecil ke atas saat kolom diisi atau sedang diketik.
    'dalam'    => false,
])

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($dalam)
        <div class="ld">
            {{ $slot }}
            @if($label)
                <label class="ld-label">{{ $label }}@if($required)<span class="ld-wajib" aria-hidden="true">*</span>@endif</label>
            @endif
        </div>
    @else
        @if($label)
            <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-2 ml-2">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500">*</span>
                @endif
            </label>
        @endif

        {{ $slot }}
    @endif

    @if($name)
        @error($name)
            <p class="mt-1.5 ml-2 text-xs font-medium text-rose-500">{{ $message }}</p>
        @enderror
    @endif

    @if($help)
        <p class="mt-1.5 ml-2 text-xs font-medium text-slate-500">{{ $help }}</p>
    @endif
</div>
