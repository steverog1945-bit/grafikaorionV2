@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Produksi'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Laporan Produksi">
        <x-slot:subtitle>
            Laporan <span class="mx-1 text-slate-300">/</span> Rekap Produksi per Divisi &amp; Produk
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button type="button" onclick="window.print()" variant="secondary" class="no-print">
                <i class="fas fa-print"></i>
                Cetak
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER --}}
    <x-card class="mb-6 no-print">
        <form method="GET" action="{{ route('laporan.produksi') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ request('tgl_dari') }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ request('tgl_sampai') }}" />
            </div>
            <div class="w-full md:w-52">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Divisi (Mesin)</label>
                <x-form.select name="id_mesin">
                    <option value="">-- Semua Divisi --</option>
                    @foreach($divisis as $d)
                        <option value="{{ $d->id_mesin }}" {{ (string) request('id_mesin') === (string) $d->id_mesin ? 'selected' : '' }}>{{ $d->nama_mesin }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status SPK (detail)</label>
                <x-form.select name="status_spk">
                    <option value="">-- Semua Status --</option>
                    @foreach(['Antre', 'Desain', 'Cetak', 'Selesai', 'Batal'] as $s)
                        <option value="{{ $s }}" {{ request('status_spk') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'id_mesin', 'status_spk']))
                <a href="{{ route('laporan.produksi') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RINGKASAN PER DIVISI & PRODUK --}}
    <x-card title="Ringkasan per Divisi & Produk" class="mb-6" :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-y border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400">Divisi</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase text-slate-400">Produk</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase text-slate-400 text-center">Jml Nota</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase text-slate-400 text-center">Total Order</th>
                        <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400 text-center">Total Cetak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($ringkasan as $r)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-3 text-[12px] font-bold text-slate-700 uppercase">{{ $r->divisi ?? '-' }}</td>
                        <td class="px-4 py-3 text-[12px] font-semibold text-slate-600">{{ $r->nama_produk }}</td>
                        <td class="px-4 py-3 text-[12px] font-bold text-slate-700 text-center">{{ $r->total_transaksi }}</td>
                        <td class="px-4 py-3 text-[12px] font-bold text-slate-700 text-center">{{ number_format($r->total_order, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-[12px] font-bold text-emerald-600 text-center">{{ number_format($r->total_cetak, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                @if($ringkasan->isNotEmpty())
                <tfoot class="bg-slate-50 border-t border-slate-100">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-[11px] font-black uppercase text-slate-500">Total Keseluruhan</td>
                        <td class="px-4 py-3 text-[13px] font-black text-slate-800 text-center">{{ number_format($totalOrderKeseluruhan, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-[13px] font-black text-emerald-700 text-center">{{ number_format($totalCetakKeseluruhan, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-card>

    {{-- DETAIL PER SPK --}}
    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">SPK</th>
                <th class="px-4 py-5 text-left">Nama File</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Divisi</th>
                <th class="px-4 py-5 text-left">Desainer</th>
                <th class="px-4 py-5 text-center">Qty Order</th>
                <th class="px-4 py-5 text-center">Qty Cetak</th>
                <th class="px-8 py-5 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($detail as $d)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5"><span class="text-[12px] font-bold text-slate-500">#{{ $d->id_detail }}</span></td>
                <td class="px-4 py-5"><span class="text-[13px] font-bold text-slate-700 uppercase">{{ $d->nama_file }}</span> <div class="text-[10px] text-slate-400">{{ $d->nomor_nota }}</div></td>
                <td class="px-4 py-5"><span class="text-[12px] font-bold text-slate-700 uppercase">{{ $d->nama_konsumen }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($d->tgl_order)->format('d M Y') }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] text-slate-500">{{ $d->divisi ?? '-' }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] text-slate-500">{{ $d->desainer ?? '-' }}</span></td>
                <td class="px-4 py-5 text-center"><span class="text-[13px] font-bold text-slate-700">{{ $d->qty }}</span></td>
                <td class="px-4 py-5 text-center"><span class="text-[13px] font-bold text-slate-700">{{ $d->qty_cetak ?? '-' }}</span></td>
                <td class="px-8 py-5 text-center"><x-badge :status="$d->status_spk" /></td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <x-empty-state title="Tidak ada data" description="Sesuaikan filter untuk melihat detail produksi." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="no-print">
        <x-table.pagination :paginator="$detail" />
    </div>

</div>
@endsection
