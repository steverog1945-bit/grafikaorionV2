@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Neraca Saldo per ' . \Carbon\Carbon::parse($akhir)->format('d/m/Y')])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 print:pt-0 max-w-[1600px] mx-auto">
    <div class="print:hidden">
        <x-page-header title="Neraca Saldo" subtitle="Trial balance - saldo kumulatif semua akun sampai tanggal tertentu, harus selalu balance.">
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
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Per Tanggal</label>
                <input type="date" name="akhir" value="{{ $akhir }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden print:hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-4 text-left w-28">Kode</th>
                        <th class="px-6 py-4 text-left">Nama Akun</th>
                        <th class="px-6 py-4 text-left w-28">Tipe</th>
                        <th class="px-6 py-4 text-right w-36">Debet</th>
                        <th class="px-8 py-4 text-right w-36">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $r)
                    <tr class="hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-3 text-sm font-bold text-slate-600">{{ $r->kode_akun }}</td>
                        <td class="px-6 py-3 text-sm text-slate-700">
                            {{ $r->nama_akun }}
                            @if($r->saldo_tidak_normal)
                                <span class="ml-2 inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 uppercase" title="Saldo terbalik dari saldo normal yang didefinisikan">
                                    <i class="fas fa-exclamation-triangle"></i> Tidak Normal
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-xs text-slate-400">{{ $r->tipe_akun }}</td>
                        <td class="px-6 py-3 text-right text-sm {{ $r->kolom_debet > 0 ? 'text-slate-700 font-bold' : 'text-slate-300' }}">
                            {{ $r->kolom_debet > 0 ? number_format($r->kolom_debet, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-8 py-3 text-right text-sm {{ $r->kolom_kredit > 0 ? 'text-slate-700 font-bold' : 'text-slate-300' }}">
                            {{ $r->kolom_kredit > 0 ? number_format($r->kolom_kredit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center text-slate-400 font-medium italic">Belum ada akun atau belum ada transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-50/70 border-t-2 border-slate-100">
                        <td colspan="3" class="px-8 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                        <td class="px-6 py-4 text-right text-sm font-black text-slate-800">{{ number_format($totalDebet, 0, ',', '.') }}</td>
                        <td class="px-8 py-4 text-right text-sm font-black text-slate-800">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-8 py-3 text-center text-[10px] font-bold uppercase tracking-widest {{ abs($totalDebet - $totalKredit) < 0.01 ? 'text-emerald-600 bg-emerald-50/50' : 'text-rose-600 bg-rose-50/50' }}">
                            @if(abs($totalDebet - $totalKredit) < 0.01)
                                <i class="fas fa-check-circle"></i> Balance - Total Debet = Total Kredit
                            @else
                                <i class="fas fa-exclamation-circle"></i> TIDAK BALANCE - Selisih {{ number_format(abs($totalDebet - $totalKredit), 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Versi cetak - pola print-table yang sama dengan laporan lain. --}}
    <div class="hidden print:block">
        <x-laporan.print-table title="Neraca Saldo" :headers="['Kode', 'Nama Akun', 'Tipe', 'Debet', 'Kredit']" :align="['left', 'left', 'left', 'right', 'right']">
            @forelse($rows as $r)
            <tr>
                <td class="px-3 py-1.5 font-semibold text-slate-700">{{ $r->kode_akun }}</td>
                <td class="px-3 py-1.5 text-slate-700">{{ $r->nama_akun }}{{ $r->saldo_tidak_normal ? ' (Tidak Normal)' : '' }}</td>
                <td class="px-3 py-1.5 text-slate-500">{{ $r->tipe_akun }}</td>
                <td class="px-3 py-1.5 text-right text-slate-600">{{ $r->kolom_debet > 0 ? number_format($r->kolom_debet, 0, ',', '.') : '-' }}</td>
                <td class="px-3 py-1.5 text-right text-slate-600">{{ $r->kolom_kredit > 0 ? number_format($r->kolom_kredit, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td class="px-3 py-1.5 text-slate-400 italic" colspan="5">Belum ada akun atau belum ada transaksi.</td>
            </tr>
            @endforelse
            @if($rows->isNotEmpty())
            <tr class="bg-slate-50 border-t border-slate-200">
                <td class="px-3 py-1.5 font-bold text-slate-700 uppercase" colspan="3">Total</td>
                <td class="px-3 py-1.5 text-right font-bold text-slate-800">{{ number_format($totalDebet, 0, ',', '.') }}</td>
                <td class="px-3 py-1.5 text-right font-bold text-slate-800">{{ number_format($totalKredit, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="px-3 py-1.5 text-center font-bold text-slate-700" colspan="5">
                    {{ abs($totalDebet - $totalKredit) < 0.01 ? 'Balance - Total Debet = Total Kredit' : 'TIDAK BALANCE - Selisih ' . number_format(abs($totalDebet - $totalKredit), 0, ',', '.') }}
                </td>
            </tr>
            @endif
        </x-laporan.print-table>
    </div>
</div>
@endsection
