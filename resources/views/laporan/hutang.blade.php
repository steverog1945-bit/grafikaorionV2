@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Hutang Supplier'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 print:pt-0 max-w-[1600px] mx-auto">

    <div class="print:hidden">
        <x-page-header title="Laporan Hutang Supplier">
            <x-slot:subtitle>
                <i class="fas fa-file-invoice-dollar text-amber-400"></i>
                Sisa hutang per supplier berdasarkan umur keterlambatan dari jatuh tempo
            </x-slot:subtitle>
            <x-slot:actions>
                <x-button type="button" onclick="window.print()" variant="secondary" class="no-print">
                    <i class="fas fa-print"></i>
                    Cetak
                </x-button>
            </x-slot:actions>
        </x-page-header>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8 print:hidden">
        @foreach([['Total Hutang', 'total', 'text-slate-800'], ['Belum Jatuh Tempo', 'belum', 'text-emerald-600'], ['Telat 1-30 hari', 'b30', 'text-amber-500'], ['Telat 31-60 hari', 'b60', 'text-orange-500'], ['Telat > 60 hari', 'b60plus', 'text-rose-600']] as [$label, $key, $warna])
        <x-card>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ $label }}</div>
            <div class="text-lg font-extrabold {{ $warna }}">Rp {{ number_format($total[$key], 0, ',', '.') }}</div>
        </x-card>
        @endforeach
    </div>
    <div class="hidden print:block">
        <x-laporan.resume-table title="Resume" :rows="[
            'Total Hutang' => 'Rp ' . number_format($total['total'], 0, ',', '.'),
            'Belum Jatuh Tempo' => 'Rp ' . number_format($total['belum'], 0, ',', '.'),
            'Telat 1-30 hari' => 'Rp ' . number_format($total['b30'], 0, ',', '.'),
            'Telat 31-60 hari' => 'Rp ' . number_format($total['b60'], 0, ',', '.'),
            'Telat > 60 hari' => 'Rp ' . number_format($total['b60plus'], 0, ',', '.'),
        ]" />
    </div>

    <div class="print:hidden">
    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-5 text-left">Supplier / Faktur</th>
                <th class="px-4 py-5 text-right">Belum Jatuh Tempo</th>
                <th class="px-4 py-5 text-right">1-30</th>
                <th class="px-4 py-5 text-right">31-60</th>
                <th class="px-4 py-5 text-right">&gt; 60</th>
                <th class="px-8 py-5 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($perSupplier as $s)
            <tr class="bg-slate-50/60">
                <td class="px-8 py-4 font-bold text-slate-700 uppercase text-[13px]">{{ $s['nama'] }}</td>
                @foreach(['belum', 'b30', 'b60', 'b60plus'] as $b)
                    <td class="px-4 py-4 text-right text-sm font-semibold text-slate-600">{{ $s[$b] ? number_format($s[$b], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td class="px-8 py-4 text-right font-extrabold text-indigo-600">{{ number_format($s['total'], 0, ',', '.') }}</td>
            </tr>
            @foreach($s['faktur'] as $f)
            <tr class="hover:bg-slate-50/30">
                <td class="px-8 py-3 pl-14">
                    <a href="{{ route('pembelian-bahan.show', $f->id_pembelian) }}" class="text-[12px] font-semibold text-slate-600 hover:text-indigo-600">{{ $f->no_faktur }}</a>
                    <div class="text-[10px] text-slate-400">Jatuh tempo {{ \Carbon\Carbon::parse($f->jatuh_tempo_tgl ?: $f->tgl_transaksi)->format('d M Y') }}{{ $f->hari_telat ? ' - telat ' . $f->hari_telat . ' hari' : '' }}</div>
                </td>
                @foreach(['belum', 'b30', 'b60', 'b60plus'] as $b)
                    <td class="px-4 py-3 text-right text-xs text-slate-500">{{ $f->bucket === $b ? number_format($f->sisa, 0, ',', '.') : '' }}</td>
                @endforeach
                <td class="px-8 py-3 text-right text-xs font-bold text-slate-600">{{ number_format($f->sisa, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @empty
            <tr><td colspan="6"><x-empty-state title="Tidak ada hutang supplier yang belum lunas" /></td></tr>
            @endforelse
        </tbody>
    </x-table>
    </div>

    <div class="hidden print:block">
        <x-laporan.print-table title="Detail Hutang per Supplier" :headers="['Supplier / Faktur', 'Belum Jatuh Tempo', '1-30', '31-60', '> 60', 'Total']" :align="['left', 'right', 'right', 'right', 'right', 'right']">
            @forelse($perSupplier as $s)
            <tr class="bg-slate-50">
                <td class="px-3 py-1.5 font-bold text-slate-700 uppercase">{{ $s['nama'] }}</td>
                @foreach(['belum', 'b30', 'b60', 'b60plus'] as $b)
                    <td class="px-3 py-1.5 text-right font-semibold text-slate-600">{{ $s[$b] ? number_format($s[$b], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td class="px-3 py-1.5 text-right font-bold text-slate-800">{{ number_format($s['total'], 0, ',', '.') }}</td>
            </tr>
            @foreach($s['faktur'] as $f)
            <tr>
                <td class="px-3 py-1.5 pl-6 text-slate-600">{{ $f->no_faktur }} <span class="text-slate-400">- jatuh tempo {{ \Carbon\Carbon::parse($f->jatuh_tempo_tgl ?: $f->tgl_transaksi)->format('d/m/Y') }}{{ $f->hari_telat ? ', telat ' . $f->hari_telat . ' hari' : '' }}</span></td>
                @foreach(['belum', 'b30', 'b60', 'b60plus'] as $b)
                    <td class="px-3 py-1.5 text-right text-slate-600">{{ $f->bucket === $b ? number_format($f->sisa, 0, ',', '.') : '' }}</td>
                @endforeach
                <td class="px-3 py-1.5 text-right text-slate-700">{{ number_format($f->sisa, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @empty
            <tr>
                <td class="px-3 py-1.5 text-slate-400 italic" colspan="6">Tidak ada hutang supplier yang belum lunas.</td>
            </tr>
            @endforelse
        </x-laporan.print-table>
    </div>
</div>
@endsection
