@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        tr[id^="detail-order-"] { display: table-row !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Order Jersey'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Laporan Order Jersey">
        <x-slot:subtitle>
            Laporan <span class="mx-1 text-slate-300">/</span> Rekap Order &amp; Piutang Jersey - terpisah dari Laporan Order percetakan biasa
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
        <form method="GET" action="{{ route('laporan.order-jersey') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ request('tgl_dari') }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ request('tgl_sampai') }}" />
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Pembayaran</label>
                <x-form.select name="status_pembayaran">
                    <option value="">-- Semua --</option>
                    @foreach(['Lunas', 'DP', 'Belum Bayar'] as $s)
                        <option value="{{ $s }}" {{ request('status_pembayaran') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Desainer</label>
                <x-form.select name="jersey_designer_id">
                    <option value="">-- Semua --</option>
                    @foreach($designers as $d)
                        <option value="{{ $d->id }}" {{ (string) request('jersey_designer_id') === (string) $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'status_pembayaran', 'jersey_designer_id']))
                <a href="{{ route('laporan.order-jersey') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Order</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($jumlahOrder, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Transaksi</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Piutang (sisa tagihan)</p>
            <h3 class="text-2xl font-bold text-rose-500 mt-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- BREAKDOWN PER KATEGORI &amp; DESAINER --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 no-print">
        @if($breakdownKategori->isNotEmpty())
        <x-card title="Rincian per Kategori Jersey">
            <x-table bare>
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($breakdownKategori as $b)
                    <tr>
                        <td class="px-4 py-3 text-[12px] font-bold text-slate-700">{{ $b->kategori }}</td>
                        <td class="px-4 py-3 text-right text-[12px] text-slate-600">{{ number_format($b->total_qty, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-[12px] font-semibold text-slate-600">Rp {{ number_format($b->total_subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </x-table>
        </x-card>
        @endif

        @if($breakdownDesainer->isNotEmpty())
        <x-card title="Rincian per Desainer">
            <x-table bare>
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">Desainer</th>
                        <th class="px-4 py-3 text-right">Jml Order</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($breakdownDesainer as $b)
                    <tr>
                        <td class="px-4 py-3 text-[12px] font-bold text-slate-700">{{ $b->nama }}</td>
                        <td class="px-4 py-3 text-right text-[12px] text-slate-600">{{ number_format($b->jumlah_order, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-[12px] font-semibold text-slate-600">Rp {{ number_format($b->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </x-table>
        </x-card>
        @endif
    </div>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left w-8"></th>
                <th class="px-2 py-5 text-left">No. Order</th>
                <th class="px-4 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-left">Desainer</th>
                <th class="px-4 py-5 text-center">Status Bayar</th>
                <th class="px-8 py-5 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $o)
            @php $lineOrder = $linePerOrder->get($o->id, collect()); @endphp
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5 no-print">
                    @if($lineOrder->isNotEmpty())
                    <button type="button" onclick="toggleDetailOrder({{ $o->id }})" class="w-6 h-6 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Lihat rincian barang">
                        <i class="fas fa-chevron-right text-[10px]" id="chevron-order-{{ $o->id }}"></i>
                    </button>
                    @endif
                </td>
                <td class="px-2 py-5"><span class="text-[13px] font-bold text-slate-700 uppercase">{{ $o->order_number }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($o->order_date)->format('d M Y') }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] font-bold text-slate-700 uppercase">{{ $o->konsumen->nama_konsumen ?? $o->customer_name ?? '-' }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] text-slate-500">{{ $o->designer->name ?? $o->designer_name ?? '-' }}</span></td>
                <td class="px-4 py-5 text-center"><x-badge :status="$o->status_pembayaran" /></td>
                <td class="px-8 py-5 text-right"><span class="text-[13px] font-bold text-slate-800">Rp {{ number_format($o->total, 0, ',', '.') }}</span></td>
            </tr>
            @if($lineOrder->isNotEmpty())
            <tr id="detail-order-{{ $o->id }}" class="hidden">
                <td></td>
                <td colspan="6" class="px-4 pb-5">
                    <div class="bg-slate-50 rounded-2xl p-4 overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase">
                                    <th class="p-2 text-left">Nama Barang</th>
                                    <th class="p-2 text-right">Qty</th>
                                    <th class="p-2 text-right">Harga Satuan</th>
                                    <th class="p-2 text-right">Tambahan</th>
                                    <th class="p-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white">
                                @foreach($lineOrder as $line)
                                <tr>
                                    <td class="p-2 font-semibold text-slate-600">{{ $line->nama_barang }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ $line->qty }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ number_format($line->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ number_format($line->size_surcharge_total + $line->sleeve_collar_surcharge_total, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right font-bold text-slate-700">{{ number_format($line->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="7">
                    <x-empty-state title="Tidak ada order jersey" description="Sesuaikan filter untuk melihat data." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>

@push('scripts')
<script>
    function toggleDetailOrder(id) {
        const baris = document.getElementById(`detail-order-${id}`);
        const chevron = document.getElementById(`chevron-order-${id}`);
        if (!baris) return;
        baris.classList.toggle('hidden');
        chevron.classList.toggle('fa-chevron-right');
        chevron.classList.toggle('fa-chevron-down');
    }
</script>
@endpush
@endsection
