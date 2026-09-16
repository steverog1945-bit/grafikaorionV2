@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        /* Detail item per nota disembunyikan di layar (toggle), tapi
           selalu tampil saat dicetak supaya laporan cetak lengkap tanpa
           perlu klik satu-satu. */
        tr[id^="detail-nota-"] { display: table-row !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Rekap Transaksi Order'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Laporan Rekap Transaksi Order">
        <x-slot:subtitle>
            Laporan <span class="mx-1 text-slate-300">/</span> Rekap Order &amp; Piutang
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
        <form method="GET" action="{{ route('laporan.order') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ request('tgl_dari') }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ request('tgl_sampai') }}" />
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Transaksi</label>
                <x-form.select name="status_pembayaran">
                    <option value="">-- Semua --</option>
                    @foreach(['Lunas', 'DP', 'Belum Bayar'] as $s)
                        <option value="{{ $s }}" {{ request('status_pembayaran') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Golongan Konsumen</label>
                <x-form.select name="id_golongan">
                    <option value="">-- Semua --</option>
                    @foreach($golongan as $g)
                        <option value="{{ $g->id_golongan }}" {{ (string) request('id_golongan') === (string) $g->id_golongan ? 'selected' : '' }}>{{ $g->nama_golongan }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Konsumen</label>
                <x-form.input type="text" name="konsumen" value="{{ request('konsumen') }}" placeholder="Nama konsumen..." />
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'status_pembayaran', 'id_golongan', 'konsumen']))
                <a href="{{ route('laporan.order') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Transaksi</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Transaksi</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Piutang (sisa tagihan riil)</p>
            <h3 class="text-2xl font-bold text-rose-500 mt-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- BREAKDOWN PER DIVISI/MESIN - tabel produk tidak punya kolom
         kategori tersendiri, jadi pengelompokan pakai Divisi (mesin) yang
         sudah dipakai konsisten di Laporan Produksi. --}}
    @if($breakdownDivisi->isNotEmpty())
    <x-card title="Rincian Biaya per Divisi" class="mb-6 no-print">
        <x-table bare>
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Divisi</th>
                    <th class="px-4 py-3 text-right">Biaya Cetak</th>
                    <th class="px-4 py-3 text-right">Biaya Design</th>
                    <th class="px-4 py-3 text-right">Biaya Finishing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($breakdownDivisi as $b)
                <tr>
                    <td class="px-4 py-3 text-[12px] font-bold text-slate-700">{{ $b->divisi }}</td>
                    <td class="px-4 py-3 text-right text-[12px] font-semibold text-slate-600">{{ number_format($b->total_biaya_cetak, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-[12px] font-semibold text-slate-600">{{ number_format($b->total_biaya_desain, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-[12px] font-semibold text-slate-600">{{ number_format($b->total_biaya_finishing, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
    </x-card>
    @endif

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left w-8"></th>
                <th class="px-2 py-5 text-left">Nomor Nota</th>
                <th class="px-4 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-center">Golongan</th>
                <th class="px-4 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $t)
            @php $itemNota = $itemPerNota->get($t->id_transaksi, collect()); @endphp
            <tr class="hover:bg-slate-50/50 transition-all {{ $t->status_nota === 'Dibatalkan' ? 'opacity-50' : '' }}">
                <td class="px-6 py-5 no-print">
                    @if($itemNota->isNotEmpty())
                    <button type="button" onclick="toggleDetailNota({{ $t->id_transaksi }})" class="w-6 h-6 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Lihat rincian item">
                        <i class="fas fa-chevron-right text-[10px]" id="chevron-nota-{{ $t->id_transaksi }}"></i>
                    </button>
                    @endif
                </td>
                <td class="px-2 py-5"><span class="text-[13px] font-bold text-slate-700 uppercase">{{ $t->nomor_nota }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($t->tgl_order)->format('d M Y') }}</span></td>
                <td class="px-4 py-5"><span class="text-[12px] font-bold text-slate-700 uppercase">{{ $t->nama_konsumen }}</span></td>
                <td class="px-4 py-5 text-center">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $t->nama_golongan }}
                    </span>
                </td>
                <td class="px-4 py-5 text-center">
                    @if($t->status_nota === 'Dibatalkan')
                        <x-badge status="Dibatalkan" variant="danger" />
                    @else
                        <x-badge :status="$t->status_pembayaran" />
                    @endif
                </td>
                <td class="px-8 py-5 text-right"><span class="text-[13px] font-bold text-slate-800">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</span></td>
            </tr>
            @if($itemNota->isNotEmpty())
            <tr id="detail-nota-{{ $t->id_transaksi }}" class="hidden">
                <td></td>
                <td colspan="6" class="px-4 pb-5">
                    <div class="bg-slate-50 rounded-2xl p-4 overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase">
                                    <th class="p-2 text-left">Produk</th>
                                    <th class="p-2 text-right">Qty</th>
                                    <th class="p-2 text-right">Cetak</th>
                                    <th class="p-2 text-right">Design</th>
                                    <th class="p-2 text-right">Finishing</th>
                                    <th class="p-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white">
                                @foreach($itemNota as $item)
                                <tr>
                                    <td class="p-2 font-semibold text-slate-600">{{ $item->nama_produk ?? $item->nama_file ?? '-' }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ $item->qty }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ number_format($item->biaya_cetak, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ number_format($item->biaya_desain, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right text-slate-500">{{ number_format($item->biaya_finishing, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right font-bold text-slate-700">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
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
                    <x-empty-state title="Tidak ada data" description="Sesuaikan filter untuk melihat transaksi." />
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
    function toggleDetailNota(id) {
        const baris = document.getElementById(`detail-nota-${id}`);
        const chevron = document.getElementById(`chevron-nota-${id}`);
        if (!baris) return;
        baris.classList.toggle('hidden');
        chevron.classList.toggle('fa-chevron-right');
        chevron.classList.toggle('fa-chevron-down');
    }
</script>
@endpush
@endsection
