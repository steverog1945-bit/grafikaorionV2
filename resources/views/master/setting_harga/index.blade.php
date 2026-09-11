@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Price List & Level" subtitle="Harga jual produk per golongan konsumen, bisa bertingkat berdasarkan qty.">
        <x-slot:actions>
            <x-button href="{{ route('harga-promo.index') }}" variant="secondary">
                <i class="fas fa-fire"></i>
                Harga Promo
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <x-card class="mb-6">
        <form action="{{ route('setting-harga.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Produk</label>
                <x-form.input type="text" name="search" value="{{ request('search') }}" placeholder="Nama produk..." />
            </div>

            <div class="w-full md:w-52">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Mesin/Divisi</label>
                <x-form.select name="category">
                    <option value="">Semua Mesin</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_mesin }}" {{ request('category') == $cat->id_mesin ? 'selected' : '' }}>{{ strtoupper($cat->nama_mesin) }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['search', 'category']))
                <a href="{{ route('setting-harga.index') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Produk</th>
                @foreach($golongan as $g)
                <th class="px-4 py-5 text-center">{{ $g->nama_golongan }}</th>
                @endforeach
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($produk as $p)
            @php $hargaProduk = $all_harga[$p->id_produk] ?? collect(); @endphp
            <tr class="group hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <div class="text-[13px] font-bold text-slate-700 uppercase">{{ $p->nama_produk }}</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase border border-indigo-100">
                            <i class="fas fa-print mr-1"></i>{{ $p->nama_mesin ?? 'Tanpa Mesin' }}
                        </span>
                        <span class="text-[9px] text-slate-400 font-medium italic">By {{ $p->metode_hitung }}</span>
                    </div>
                </td>

                @foreach($golongan as $g)
                <td class="px-4 py-5 text-center">
                    @php $tiersGol = $hargaProduk->where('id_golongan', $g->id_golongan); @endphp
                    @if($tiersGol->isEmpty())
                        <span class="text-slate-300 italic text-[10px]">Belum Diatur</span>
                    @elseif($tiersGol->count() === 1)
                        @php $satu = $tiersGol->first(); @endphp
                        <span class="text-slate-700 font-semibold">Rp{{ number_format($satu->harga_jual, 0, ',', '.') }}</span>
                        @if($satu->range_min > 1 || $satu->range_max)
                            <div class="text-[9px] text-slate-400">qty {{ $satu->range_min }}-{{ $satu->range_max ?? '∞' }}</div>
                        @endif
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">{{ $tiersGol->count() }} TIER</span>
                        <div class="text-[9px] text-slate-400 mt-0.5">Rp{{ number_format($tiersGol->min('harga_jual'), 0, ',', '.') }} - Rp{{ number_format($tiersGol->max('harga_jual'), 0, ',', '.') }}</div>
                    @endif
                </td>
                @endforeach

                <td class="px-8 py-5 text-right">
                    <x-button size="icon" variant="ghost" onclick="openModal('harga-produk-{{ $p->id_produk }}')" class="hover:text-indigo-600" title="Kelola Harga">
                        <i class="fas fa-sliders-h text-sm"></i>
                    </x-button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($golongan) + 2 }}">
                    <x-empty-state title="Produk tidak ditemukan" description="Sesuaikan filter di atas." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$produk" />

    {{-- MODAL KELOLA HARGA (satu per produk - server-rendered, setiap tier
         punya form CRUD sendiri dengan id_harga asli, add/edit/hapus satu
         tier TIDAK PERNAH menyentuh tier lain sama sekali). --}}
    @foreach($produk as $p)
    @php $hargaProdukModal = $all_harga[$p->id_produk] ?? collect(); @endphp
    <x-modal id="harga-produk-{{ $p->id_produk }}" title="Kelola Harga: {{ $p->nama_produk }}" maxWidth="3xl">
        <div class="mb-4 p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500">
            Tiap baris berlaku untuk satu golongan &amp; rentang qty tertentu. Kosongkan <b>Max</b> untuk rentang tak terbatas ("qty X ke atas"). Harga dipilih otomatis di Kasir Utama berdasarkan golongan konsumen &amp; qty yang diinput.
        </div>

        @if($hargaProdukModal->isEmpty())
            <p class="text-xs text-slate-400 italic mb-4">Belum ada harga diatur untuk produk ini - order dengan produk ini akan ditolak di Kasir sampai minimal satu tier diisi.</p>
        @else
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase text-slate-400 border-b border-slate-100">
                            <th class="text-left py-2 pr-2">Golongan</th>
                            <th class="text-center py-2 px-2 w-20">Min</th>
                            <th class="text-center py-2 px-2 w-20">Max</th>
                            <th class="text-left py-2 px-2">Harga (Rp)</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($hargaProdukModal as $t)
                        @php $namaGolonganTier = optional($golongan->firstWhere('id_golongan', $t->id_golongan))->nama_golongan ?? '?'; @endphp
                        <tr>
                            <td colspan="5" class="py-1">
                                <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2">
                                    <form action="{{ route('setting-harga.tier.update', $t->id_harga) }}" method="POST" class="flex items-center gap-2 flex-1">
                                        @csrf @method('PATCH')
                                        <span class="w-28 shrink-0 text-[11px] font-bold text-slate-600 uppercase truncate" title="{{ $namaGolonganTier }}">{{ $namaGolonganTier }}</span>
                                        <input type="number" name="range_min" value="{{ $t->range_min }}" min="1" required class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                                        <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                                        <input type="number" name="range_max" value="{{ $t->range_max }}" min="1" placeholder="~" class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                                        <span class="text-[10px] text-slate-400">Rp</span>
                                        <input type="number" name="harga_jual" value="{{ (int) $t->harga_jual }}" min="0" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                                            <i class="fas fa-save text-xs"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('setting-harga.tier.destroy', $t->id_harga) }}" method="POST" id="hapus-tier-harga-{{ $t->id_harga }}">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmHapusTierHarga({{ $t->id_harga }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form action="{{ route('setting-harga.tier.store', $p->id_produk) }}" method="POST" class="flex items-center gap-2 bg-emerald-50/50 border border-dashed border-emerald-200 rounded-xl p-2">
            @csrf
            <select name="id_golongan" required class="w-28 shrink-0 text-[11px] bg-white border-none rounded-lg py-2 px-1 font-bold text-slate-600">
                @foreach($golongan as $g)
                    <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                @endforeach
            </select>
            <input type="number" name="range_min" placeholder="Min" value="1" min="1" required class="w-16 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[10px] text-slate-300 font-bold">s/d</span>
            <input type="number" name="range_max" placeholder="~" min="1" class="w-16 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[10px] text-slate-400">Rp</span>
            <input type="number" name="harga_jual" placeholder="0" min="0" required class="flex-1 text-xs bg-white border-none rounded-lg py-2 font-bold">
            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition-all shrink-0" title="Tambah Tier">
                <i class="fas fa-plus text-xs"></i>
            </button>
        </form>

        <div class="pt-4 flex justify-end">
            <x-button type="button" variant="secondary" onclick="closeModal('harga-produk-{{ $p->id_produk }}')">Tutup</x-button>
        </div>
    </x-modal>
    @endforeach

</div>

@push('scripts')
<script>
    function confirmHapusTierHarga(id) {
        Swal.fire({
            title: 'Hapus Tier Harga Ini?',
            text: 'Order dengan qty di rentang ini tidak akan punya harga lagi sampai diatur ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('hapus-tier-harga-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
