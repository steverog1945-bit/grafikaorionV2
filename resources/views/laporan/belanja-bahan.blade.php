@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Belanja Bahan'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Laporan Belanja Bahan">
        <x-slot:subtitle>
            Laporan <span class="mx-1 text-slate-300">/</span> Pembukuan Pembelian Bahan Baku - terpisah dari Laporan Kas &amp; Keuangan Jersey
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
        <form method="GET" action="{{ route('laporan.belanja-bahan') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $tglDari }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $tglSampai }}" />
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Bayar</label>
                <x-form.select name="status_pembayaran">
                    <option value="">-- Semua --</option>
                    <option value="Lunas" {{ request('status_pembayaran') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="Hutang" {{ request('status_pembayaran') === 'Hutang' ? 'selected' : '' }}>Hutang</option>
                </x-form.select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Tipe Pembelian</label>
                <x-form.select name="tipe_pembelian">
                    <option value="">-- Semua --</option>
                    <option value="Cash" {{ request('tipe_pembelian') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Tempo" {{ request('tipe_pembelian') === 'Tempo' ? 'selected' : '' }}>Tempo</option>
                    <option value="Biasa" {{ request('tipe_pembelian') === 'Biasa' ? 'selected' : '' }}>Biasa</option>
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'status_pembayaran', 'tipe_pembelian']))
                <a href="{{ route('laporan.belanja-bahan') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Faktur</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($jumlahFaktur, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Belanja Bahan</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sisa Hutang (belum Lunas)</p>
            <h3 class="text-2xl font-bold text-rose-500 mt-1">Rp {{ number_format($totalHutang, 0, ',', '.') }}</h3>
        </div>
    </div>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">No. Faktur</th>
                <th class="px-4 py-5 text-center">Tipe</th>
                <th class="px-4 py-5 text-right">Grand Total</th>
                <th class="px-4 py-5 text-left">Jatuh Tempo</th>
                <th class="px-4 py-5 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $p)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[13px] font-bold text-slate-700">{{ \Carbon\Carbon::parse($p->tgl_transaksi)->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ $p->no_faktur }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    <span class="text-[11px] font-bold uppercase text-slate-500">{{ $p->tipe_pembelian }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-indigo-600 font-bold">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] text-slate-500">{{ $p->jatuh_tempo_tgl ? \Carbon\Carbon::parse($p->jatuh_tempo_tgl)->format('d M Y') : '-' }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    @if($p->status_pembayaran === 'Lunas')
                        <x-badge variant="success">Lunas</x-badge>
                    @else
                        <x-badge variant="warning">Hutang</x-badge>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <x-empty-state title="Tidak ada pembelian bahan" description="Sesuaikan filter tanggal untuk melihat rekap belanja bahan." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>
@endsection
