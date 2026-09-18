@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Jurnal Umum', 'tglDari' => $awal, 'tglSampai' => $akhir])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 print:pt-0 max-w-[1600px] mx-auto">
    <div class="print:hidden">
        <x-page-header title="Jurnal Umum" subtitle="Semua entri debet/kredit yang tercatat otomatis dari transaksi penjualan, pembayaran, pembelian, dan deposit.">
            <x-slot:actions>
                <x-button type="button" onclick="window.print()" variant="secondary" class="no-print">
                    <i class="fas fa-print"></i>
                    Cetak
                </x-button>
            </x-slot:actions>
        </x-page-header>
    </div>

    <div class="no-print">
        @include('akuntansi._subnav')
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 mb-6 no-print">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                <input type="date" name="awal" value="{{ $awal }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                <input type="date" name="akhir" value="{{ $akhir }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-4 text-left w-28">Tanggal</th>
                        <th class="px-6 py-4 text-left">No. Referensi</th>
                        <th class="px-6 py-4 text-left">Keterangan</th>
                        <th class="px-6 py-4 text-left w-24">Akun</th>
                        <th class="px-6 py-4 text-right w-36">Debet</th>
                        <th class="px-8 py-4 text-right w-36">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $r)
                    <tr class="hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-3 text-xs text-slate-500">{{ \Carbon\Carbon::parse($r->tgl_jurnal)->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-xs text-slate-500">{{ $r->no_referensi }}</td>
                        <td class="px-6 py-3 text-sm text-slate-700">{{ $r->keterangan }}</td>
                        <td class="px-6 py-3 text-xs">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 text-slate-600 font-bold">{{ $r->kode_akun }}</span>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $r->nama_akun }}</div>
                        </td>
                        <td class="px-6 py-3 text-right text-sm {{ $r->debet > 0 ? 'text-slate-700 font-bold' : 'text-slate-300' }}">
                            {{ $r->debet > 0 ? number_format($r->debet, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-8 py-3 text-right text-sm {{ $r->kredit > 0 ? 'text-slate-700 font-bold' : 'text-slate-300' }}">
                            {{ $r->kredit > 0 ? number_format($r->kredit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center text-slate-400 font-medium italic">Tidak ada jurnal pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-50/70 border-t-2 border-slate-100">
                        <td colspan="4" class="px-8 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                        <td class="px-6 py-4 text-right text-sm font-black text-slate-800">{{ number_format($totalDebet, 0, ',', '.') }}</td>
                        <td class="px-8 py-4 text-right text-sm font-black text-slate-800">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                    </tr>
                    @if(abs($totalDebet - $totalKredit) < 0.01)
                    <tr>
                        <td colspan="6" class="px-8 py-3 text-center text-[10px] font-bold text-emerald-600 uppercase tracking-widest bg-emerald-50/50">
                            <i class="fas fa-check-circle"></i> Balance - Total Debet = Total Kredit
                        </td>
                    </tr>
                    @endif
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
