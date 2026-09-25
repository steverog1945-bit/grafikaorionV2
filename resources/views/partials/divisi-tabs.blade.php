{{-- Tab divisi otomatis dari master mesin (lihat App\Services\DivisiService). Parameter: $routeName, $divisis --}}
@if($divisis->isNotEmpty())
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route($routeName, request()->except(['divisi', 'page'])) }}"
       class="px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all {{ !request()->filled('divisi') ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400' }}">
        Semua Divisi
    </a>
    @foreach($divisis as $d)
    <a href="{{ route($routeName, array_merge(request()->except('page'), ['divisi' => $d->id_mesin])) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all {{ (string) request('divisi') === (string) $d->id_mesin && request()->filled('divisi') ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400' }}">
        {{ $d->nama_mesin }}
        @if($d->jumlah > 0)<span class="min-w-[1.25rem] h-5 px-1.5 rounded-full bg-amber-500 text-white text-[10px] font-bold inline-flex items-center justify-center">{{ $d->jumlah }}</span>@endif
    </a>
    @endforeach
</div>
@endif
