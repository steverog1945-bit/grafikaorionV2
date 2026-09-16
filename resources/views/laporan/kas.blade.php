@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .sesi-block { break-inside: avoid; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Kas'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <div class="print:hidden">
        <x-page-header title="Laporan Kas">
            <x-slot:subtitle>
                Laporan <span class="mx-1 text-slate-300">/</span> Rekap Sesi Kas Harian
            </x-slot:subtitle>
            <x-slot:actions>
                <x-button type="button" onclick="window.print()" variant="secondary" class="no-print">
                    <i class="fas fa-print"></i>
                    Cetak
                </x-button>
            </x-slot:actions>
        </x-page-header>
    </div>

    {{-- FILTER --}}
    <x-card class="mb-6 no-print">
        <form method="GET" action="{{ route('laporan.kas') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $tglDari }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $tglSampai }}" />
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Sesi</label>
                <x-form.select name="status_lock">
                    <option value="">-- Semua --</option>
                    <option value="open" {{ request('status_lock') === 'open' ? 'selected' : '' }}>Masih Buka</option>
                    <option value="closed" {{ request('status_lock') === 'closed' ? 'selected' : '' }}>Sudah Tutup</option>
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'status_lock']))
                <a href="{{ route('laporan.kas') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6 print:hidden">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Sesi</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($jumlahSesi, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Tunai Masuk</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalTunaiMasuk, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Transfer Masuk</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">Rp {{ number_format($totalTransferMasuk, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Debit Masuk</p>
            <h3 class="text-2xl font-bold text-sky-600 mt-1">Rp {{ number_format($totalDebitMasuk, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Selisih</p>
            <h3 class="text-2xl font-bold {{ $totalSelisih < 0 ? 'text-rose-500' : 'text-slate-900' }} mt-1">Rp {{ number_format($totalSelisih, 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="hidden print:block mb-4">
        <p class="text-[12px] font-bold text-slate-900 mb-1">Resume</p>
        <table class="text-[11px]">
            <tr><td class="pr-3 text-slate-600">Jumlah Sesi</td><td class="pr-2">:</td><td class="font-semibold">{{ number_format($jumlahSesi, 0, ',', '.') }}</td></tr>
            <tr><td class="pr-3 text-slate-600">Total Tunai Masuk</td><td class="pr-2">:</td><td class="font-semibold">Rp {{ number_format($totalTunaiMasuk, 0, ',', '.') }}</td></tr>
            <tr><td class="pr-3 text-slate-600">Total Transfer Masuk</td><td class="pr-2">:</td><td class="font-semibold">Rp {{ number_format($totalTransferMasuk, 0, ',', '.') }}</td></tr>
            <tr><td class="pr-3 text-slate-600">Total Debit Masuk</td><td class="pr-2">:</td><td class="font-semibold">Rp {{ number_format($totalDebitMasuk, 0, ',', '.') }}</td></tr>
            <tr><td class="pr-3 text-slate-600">Total Selisih</td><td class="pr-2">:</td><td class="font-semibold">Rp {{ number_format($totalSelisih, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-1">Detail Sesi Kas</div>

    @forelse($data as $k)
    @php $manual = $manualPerSesi->get($k->id_kas, collect()); @endphp
    <div class="sesi-block bg-white rounded-3xl border border-slate-100 shadow-sm mb-5 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[14px] font-bold text-slate-800">{{ \Carbon\Carbon::parse($k->tgl_kas)->format('d M Y') }}</span>
                    @if($k->status_lock === 'open')
                        <x-badge variant="warning">Masih Buka</x-badge>
                    @else
                        <x-badge variant="success">Tutup</x-badge>
                    @endif
                </div>
                <p class="text-[12px] text-slate-500 mt-1.5">
                    Kasir: <span class="font-bold text-slate-700">{{ $k->nama_kasir ?? '-' }}</span>
                    &middot; Saldo Awal: Rp {{ number_format($k->saldo_awal, 0, ',', '.') }}
                </p>
            </div>
        </div>

        @if($manual->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-xs rtable">
                <thead>
                    <tr class="text-[10px] text-slate-400 uppercase bg-slate-50/60">
                        <th class="p-3 text-left w-6">#</th>
                        <th class="p-3 text-left">Waktu</th>
                        <th class="p-3 text-left">Keterangan</th>
                        <th class="p-3 text-left">Oleh</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($manual as $i => $m)
                    <tr>
                        <td data-label="#" class="p-3 text-slate-400">{{ $i + 1 }}</td>
                        <td data-label="Waktu" class="p-3 text-slate-500">{{ \Carbon\Carbon::parse($m->waktu)->format('H:i') }}</td>
                        <td data-label="Keterangan" class="p-3 font-semibold text-slate-700">{{ $m->keterangan }}</td>
                        <td data-label="Oleh" class="p-3 text-slate-500">{{ $m->nama_user ?? '-' }}</td>
                        <td data-label="Status" class="p-3 text-center">
                            <x-badge :variant="$m->status === 'Masuk' ? 'success' : 'danger'">{{ $m->status }}</x-badge>
                        </td>
                        <td data-label="Nominal" class="p-3 text-right font-bold {{ $m->status === 'Masuk' ? 'text-emerald-600' : 'text-rose-500' }}">
                            {{ $m->status === 'Masuk' ? '+' : '-' }} Rp {{ number_format($m->nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-[11px] text-slate-300 italic p-4">Tidak ada transaksi manual pada sesi ini.</p>
        @endif

        <div class="px-6 py-4 bg-slate-50/60 border-t border-slate-100 grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2">
            <p class="text-[12px] text-slate-500 flex justify-between gap-3"><span>Tunai Masuk</span> <span class="font-bold text-emerald-600">Rp {{ number_format($k->total_tunai_masuk, 0, ',', '.') }}</span></p>
            <p class="text-[12px] text-slate-500 flex justify-between gap-3"><span>Transfer Masuk</span> <span class="font-bold text-indigo-600">Rp {{ number_format($k->total_transfer_masuk, 0, ',', '.') }}</span></p>
            <p class="text-[12px] text-slate-500 flex justify-between gap-3"><span>Debit Masuk</span> <span class="font-bold text-sky-600">Rp {{ number_format($k->total_debit_masuk ?? 0, 0, ',', '.') }}</span></p>
            <p class="text-[12px] text-slate-500 flex justify-between gap-3"><span>Tunai Keluar</span> <span class="font-bold text-rose-500">Rp {{ number_format($k->total_tunai_keluar, 0, ',', '.') }}</span></p>
            <p class="text-[12px] text-slate-500 flex justify-between gap-3"><span>Saldo Fisik</span> <span class="font-bold text-slate-700">{{ $k->saldo_fisik !== null ? 'Rp ' . number_format($k->saldo_fisik, 0, ',', '.') : '-' }}</span></p>
            <p class="text-[13px] font-black flex justify-between gap-3 {{ $k->selisih === null ? 'text-slate-300' : ($k->selisih < 0 ? 'text-rose-500' : ($k->selisih > 0 ? 'text-amber-500' : 'text-emerald-600')) }}">
                <span>Selisih</span> <span>{{ $k->selisih !== null ? 'Rp ' . number_format($k->selisih, 0, ',', '.') : '-' }}</span>
            </p>
        </div>
    </div>
    @empty
    <x-card>
        <x-empty-state title="Tidak ada sesi kas" description="Sesuaikan filter tanggal untuk melihat rekap kas." />
    </x-card>
    @endforelse

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>
@endsection
