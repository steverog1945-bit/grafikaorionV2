@props([
    'id'       => null,
    'title'    => null,
    'subtitle' => null,
    'icon'     => null,   // nama ikon Font Awesome, mis. 'fa-layer-group'
    'maxWidth' => 'lg',   // sm | md | lg | xl | 2xl | 3xl | 4xl | 5xl | 6xl
])

@php
    $widths = [
        'sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg',
        'xl' => 'max-w-xl', '2xl' => 'max-w-2xl', '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl', '5xl' => 'max-w-5xl', '6xl' => 'max-w-6xl',
    ];
    $widthClass = $widths[$maxWidth] ?? $widths['lg'];
@endphp

{{-- Root memakai hidden (atribut), bukan kelas `hidden`.
     Alasannya: `hidden` (display:none) menang atas `flex` kalau keduanya
     aktif bersamaan, jadi toggle berbasis classList menyisakan elemen
     yang tak terlihat tapi masih bisa difokus. Dengan menyembunyikan
     lewat atribut, state tertutup benar-benar hilang dari accessibility
     tree. --}}
<div id="modal-{{ $id }}" data-modal-root hidden class="fixed inset-0 z-[60] items-center justify-center p-4">

    {{-- Area gelap. Dipasang sebagai elemen terpisah supaya klik di
         dalamnya menutup modal, sementara klik di panel tidak. --}}
    <div class="absolute inset-0 bg-slate-900/50" data-modal-backdrop></div>

    {{-- Panel. Animasi masuk/keluar dikendalikan atribut data-state
         yang di-toggle openModal()/closeModal() di app.js. --}}
    <div data-modal-panel
         class="relative bg-white w-full {{ $widthClass }} rounded-lg border border-slate-200 shadow-xl max-h-[90vh] flex flex-col overflow-hidden"
         role="dialog" aria-modal="true" aria-labelledby="modal-{{ $id }}-title">
        @if($title)
            {{-- Kepala modal.

                 Bentuknya mengambil struktur "Tambah Karyawan" yang
                 diminta: ikon + judul + subjudul, karena ketiganya
                 membantu pembaca tahu sedang menambah atau mengubah
                 apa, tanpa harus membaca isinya dulu.

                 Yang TIDAK diambil dari sana adalah radius 2.5rem,
                 tata letak terpusat, dan huruf besar. Ketiganya
                 menyimpang dari 110 modal lain yang sudah radius 8px,
                 dan menjauh dari tampilan desktop yang jadi acuan
                 aplikasi ini. Yang dipakai: ikon di kiri, teks rata
                 kiri, radius 8px, ukuran huruf yang sama dengan
                 seluruh modal lain. --}}
            <div class="flex items-start gap-3 px-5 py-4 border-b border-slate-200 shrink-0">
                @if($icon)
                    <span class="w-9 h-9 shrink-0 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i class="fas {{ $icon }} text-sm"></i>
                    </span>
                @endif
                <div class="min-w-0 grow">
                    <h3 id="modal-{{ $id }}-title" class="text-sm font-medium text-slate-900">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
                <button type="button" data-modal-close
                        class="w-8 h-8 -mr-1 shrink-0 rounded-md flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                        aria-label="Tutup {{ $title }}">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Isi modal boleh menggulir sendiri, kepala & kaki tetap diam. --}}
        <div class="p-5 overflow-y-auto grow min-h-0">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-slate-200 shrink-0">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
