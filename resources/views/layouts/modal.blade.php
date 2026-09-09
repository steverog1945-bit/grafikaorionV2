@props([
    'id'       => null,
    'title'    => null,
    'maxWidth' => 'lg', // sm | md | lg | xl | 2xl
])

@php
    $widths = [
        'sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg',
        'xl' => 'max-w-xl', '2xl' => 'max-w-2xl', '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl', '5xl' => 'max-w-5xl', '6xl' => 'max-w-6xl',
    ];
    $widthClass = $widths[$maxWidth] ?? $widths['lg'];
@endphp

<div id="modal-{{ $id }}" class="hidden fixed inset-0 z-[60] items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeModal('{{ $id }}')"></div>

    {{-- Panel --}}
    <div class="relative bg-white w-full {{ $widthClass }} rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        @if($title)
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 id="modal-{{ $id }}-title" class="text-base font-bold text-slate-800">{{ $title }}</h3>
                <button type="button" onclick="closeModal('{{ $id }}')" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="p-6">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
