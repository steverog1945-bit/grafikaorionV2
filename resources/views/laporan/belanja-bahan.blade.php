@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .faktur-block { break-inside: avoid; }
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

    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-1">Detail Faktur</div>

    @forelse($data as $p)
    @php $item = $itemPerFaktur->get($p->id_pembelian, collect()); @endphp
    <div class="faktur-block bg-white rounded-3xl border border-slate-100 shadow-sm mb-5 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[14px] font-bold text-slate-800 uppercase">{{ $p->no_faktur }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-500">
                        {{ $p->tipe_pembelian }}
                    </span>
                </div>
                <p class="text-[12px] text-slate-500 mt-1.5">
                    <span class="font-bold text-slate-700">{{ $p->nama_supplier ?? '-' }}</span>
                    &middot; {{ \Carbon\Carbon::parse($p->tgl_transaksi)->format('d M Y') }}
                    @if($p->jatuh_tempo_tgl) &middot; Jatuh Tempo: {{ \Carbon\Carbon::parse($p->jatuh_tempo_tgl)->format('d M Y') }} @endif
                </p>
            </div>
            <div class="text-right shrink-0">
                @if($p->status_pembayaran === 'Lunas')
                    <x-badge variant="success">Lunas</x-badge>
                @else
                    <x-badge variant="warning">Hutang</x-badge>
                @endif
            </div>
        </div>

        @if($item->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-[10px] text-slate-400 uppercase bg-slate-50/60">
                        <th class="p-3 text-left w-6">#</th>
                        <th class="p-3 text-left">Bahan</th>
                        <th class="p-3 text-right">Qty</th>
                        <th class="p-3 text-right">Harga</th>
                        <th class="p-3 text-right">Diskon</th>
                        <th class="p-3 text-right">Potongan</th>
                        <th class="p-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($item as $i => $d)
                    <tr>
                        <td class="p-3 text-slate-400">{{ $i + 1 }}</td>
                        <td class="p-3 font-semibold text-slate-700">{{ $d->nama_bahan ?? '-' }}</td>
                        <td class="p-3 text-right text-slate-500">{{ number_format($d->qty, 0, ',', '.') }} {{ $d->satuan_stok }}</td>
                        <td class="p-3 text-right text-slate-500">{{ number_format($d->harga, 0, ',', '.') }}</td>
                        <td class="p-3 text-right text-slate-500">{{ number_format($d->diskon, 0, ',', '.') }}</td>
                        <td class="p-3 text-right text-slate-500">{{ number_format($d->potongan, 0, ',', '.') }}</td>
                        <td class="p-3 text-right font-bold text-slate-800">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-[11px] text-slate-300 italic p-4">Tidak ada rincian bahan untuk faktur ini.</p>
        @endif

        <div class="px-6 py-4 bg-slate-50/60 border-t border-slate-100 flex justify-end">
            <p class="text-[13px] font-black flex justify-between gap-6"><span>Grand Total</span> <span class="text-indigo-600">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</span></p>
        </div>
    </div>
    @empty
    <x-card>
        <x-empty-state title="Tidak ada pembelian bahan" description="Sesuaikan filter tanggal untuk melihat rekap belanja bahan." />
    </x-card>
    @endforelse

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>
@endsection
