{{-- Tab divisi otomatis dari master mesin (lihat App\Services\DivisiService). Parameter: $routeName, $divisis --}}
@php
    $terbatasDivisi = \App\Services\DivisiService::mesinDiizinkan(auth()->user()) !== null;
    $aktifDivisi = request()->filled('divisi') ? (string) request('divisi') : null;
@endphp
@if($divisis->isNotEmpty())
    @if($terbatasDivisi && $divisis->count() === 1)
        {{-- Operator dengan satu divisi: cukup penanda, bukan tab pilihan --}}
        <div class="mb-6 inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-white shadow-lg shadow-indigo-200/60">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/20"><i class="fas fa-industry text-sm"></i></span>
            <span class="leading-tight">
                <span class="block text-[10px] font-bold uppercase tracking-widest text-indigo-100">Divisi Anda</span>
                <span class="block text-sm font-black">{{ $divisis->first()->nama_mesin }}</span>
            </span>
            @if($divisis->first()->jumlah > 0)<span class="ml-2 rounded-full bg-white/25 px-2.5 py-0.5 text-xs font-black">{{ $divisis->first()->jumlah }} aktif</span>@endif
        </div>
    @else
        <div class="mb-6 flex max-w-full gap-1.5 overflow-x-auto rounded-2xl border border-slate-200/70 bg-white p-1.5 shadow-sm" role="tablist">
            <a href="{{ route($routeName, request()->except(['divisi', 'page'])) }}"
               class="group inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-[12px] font-bold transition-all {{ $aktifDivisi === null ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <i class="fas fa-layer-group text-[11px] {{ $aktifDivisi === null ? 'text-indigo-100' : 'text-slate-300 group-hover:text-indigo-400' }}"></i>
                {{ $terbatasDivisi ? 'Semua Divisi Saya' : 'Semua Divisi' }}
            </a>
            @foreach($divisis as $d)
            @php $aktif = $aktifDivisi !== null && $aktifDivisi === (string) $d->id_mesin; @endphp
            <a href="{{ route($routeName, array_merge(request()->except('page'), ['divisi' => $d->id_mesin])) }}"
               class="group inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-[12px] font-bold transition-all {{ $aktif ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                {{ $d->nama_mesin }}
                @if($d->jumlah > 0)
                    <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[10px] font-black {{ $aktif ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-700' }}">{{ $d->jumlah }}</span>
                @endif
            </a>
            @endforeach
        </div>
    @endif
@endif
