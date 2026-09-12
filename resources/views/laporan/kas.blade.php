@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Kas'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

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
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6">
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

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Kasir</th>
                <th class="px-4 py-5 text-right">Saldo Awal</th>
                <th class="px-4 py-5 text-right">Tunai Masuk</th>
                <th class="px-4 py-5 text-right">Transfer Masuk</th>
                <th class="px-4 py-5 text-right">Debit Masuk</th>
                <th class="px-4 py-5 text-right">Tunai Keluar</th>
                <th class="px-4 py-5 text-right">Saldo Fisik</th>
                <th class="px-4 py-5 text-right">Selisih</th>
                <th class="px-4 py-5 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $k)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[13px] font-bold text-slate-700">{{ \Carbon\Carbon::parse($k->tgl_kas)->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ $k->nama_kasir ?? '-' }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-slate-500 font-semibold">Rp {{ number_format($k->saldo_awal, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-emerald-600 font-bold">Rp {{ number_format($k->total_tunai_masuk, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-indigo-600 font-bold">Rp {{ number_format($k->total_transfer_masuk, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-sky-600 font-bold">Rp {{ number_format($k->total_debit_masuk ?? 0, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-rose-500 font-semibold">Rp {{ number_format($k->total_tunai_keluar, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-slate-700 font-bold">{{ $k->saldo_fisik !== null ? 'Rp ' . number_format($k->saldo_fisik, 0, ',', '.') : '-' }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    @if($k->selisih !== null)
                        <span class="text-[12px] font-bold {{ $k->selisih < 0 ? 'text-rose-500' : ($k->selisih > 0 ? 'text-amber-500' : 'text-emerald-600') }}">Rp {{ number_format($k->selisih, 0, ',', '.') }}</span>
                    @else
                        <span class="text-[12px] text-slate-300">-</span>
                    @endif
                </td>
                <td class="px-4 py-5 text-center">
                    @if($k->status_lock === 'open')
                        <x-badge variant="warning">Masih Buka</x-badge>
                    @else
                        <x-badge variant="success">Tutup</x-badge>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    <x-empty-state title="Tidak ada sesi kas" description="Sesuaikan filter tanggal untuk melihat rekap kas." />
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
