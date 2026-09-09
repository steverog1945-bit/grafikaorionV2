@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Collecting">
        <x-slot:subtitle>
            Produksi &amp; Logistik <span class="mx-1 text-slate-300">/</span> Kelengkapan Pesanan Sebelum Diambil
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ route('produksi.index') }}" variant="secondary">
                <i class="fas fa-industry"></i>
                Lihat Produksi
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('collecting.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Nama Konsumen</label>
                <x-form.input type="text" name="nama_konsumen" value="{{ request('nama_konsumen') }}" placeholder="Cari konsumen..." />
            </div>

            <div class="w-full md:w-52">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Nomor Faktur</label>
                <x-form.input type="text" name="nomor_nota" value="{{ request('nomor_nota') }}" placeholder="INV-..." />
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Order Dari</label>
                <x-form.input type="date" name="tgl_dari" value="{{ request('tgl_dari') }}" />
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Order Sampai</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ request('tgl_sampai') }}" />
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Collecting</label>
                <x-form.select name="status_collecting">
                    <option value="">-- Semua --</option>
                    <option value="belum" {{ request('status_collecting') === 'belum' ? 'selected' : '' }}>Belum Lengkap</option>
                    <option value="lengkap" {{ request('status_collecting') === 'lengkap' ? 'selected' : '' }}>Lengkap</option>
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['nama_konsumen', 'nomor_nota', 'tgl_dari', 'tgl_sampai', 'status_collecting']))
                <a href="{{ route('collecting.index') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Nomor Faktur</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-left">Tanggal Order</th>
                <th class="px-4 py-5 text-center">Progres Item</th>
                <th class="px-4 py-5 text-center">Status Bayar</th>
                <th class="px-4 py-5 text-center">Status Collecting</th>
                <th class="px-8 py-5 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($collecting as $item)
            @php $lengkap = $item->total_item > 0 && $item->item_kelar == $item->total_item; @endphp
            <tr class="group hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[13px] font-bold text-slate-700 uppercase group-hover:text-indigo-600 transition-colors">{{ $item->nomor_nota }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-bold text-slate-700 uppercase">{{ $item->nama_konsumen }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($item->tgl_order)->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    <div class="inline-flex items-center gap-2">
                        <div class="w-20 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $lengkap ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $item->total_item > 0 ? round(($item->item_kelar / $item->total_item) * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500">{{ $item->item_kelar }}/{{ $item->total_item }}</span>
                    </div>
                    @if($item->item_batal > 0)
                        <div class="text-[9px] text-rose-400 mt-1">{{ $item->item_batal }} item dibatalkan</div>
                    @endif
                </td>
                <td class="px-4 py-5 text-center">
                    <x-badge :status="$item->status_pembayaran" />
                </td>
                <td class="px-4 py-5 text-center">
                    @if($lengkap)
                        <x-badge variant="success">Lengkap</x-badge>
                    @else
                        <x-badge variant="warning">Belum Lengkap</x-badge>
                    @endif
                </td>
                <td class="px-8 py-5 text-center">
                    <x-button href="{{ route('produksi.index', ['konsumen' => $item->nama_konsumen]) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-indigo-600" title="Lihat progres di Produksi">
                        <i class="fas fa-eye text-xs"></i>
                    </x-button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <x-empty-state title="Tidak ada data" description="Sesuaikan filter atau tunggu order baru." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$collecting" />

</div>
@endsection
