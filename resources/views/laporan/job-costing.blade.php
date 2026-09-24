@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Laporan Job Costing">
        <x-slot:subtitle>
            <i class="fas fa-calculator text-amber-400"></i>
            Biaya aktual per nota: HPP material + rework + waste terkait. Tenaga kerja, mesin, dan overhead belum tercatat di aplikasi.
        </x-slot:subtitle>
    </x-page-header>

    <x-card class="mb-8">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $tglDari }}" />
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $tglSampai }}" />
            </div>
            <x-button type="submit" variant="primary"><i class="fas fa-filter"></i> Tampilkan</x-button>
        </form>
    </x-card>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @foreach([['Omzet', $total['omzet'], 'text-slate-800'], ['HPP Material', $total['hpp'], 'text-slate-600'], ['Rework + Waste', $total['rework'] + $total['waste'], 'text-rose-500'], ['Biaya Aktual', $total['aktual'], 'text-amber-600'], ['Laba Kotor (' . $total['margin'] . '%)', $total['laba'], 'text-emerald-600']] as [$label, $nilai, $warna])
        <x-card>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ $label }}</div>
            <div class="text-lg font-extrabold {{ $warna }}">Rp {{ number_format($nilai, 0, ',', '.') }}</div>
        </x-card>
        @endforeach
    </div>

    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-4 text-left">Nota</th>
                <th class="px-4 py-4 text-right">Omzet</th>
                <th class="px-4 py-4 text-right">HPP Material</th>
                <th class="px-4 py-4 text-right">Rework</th>
                <th class="px-4 py-4 text-right">Waste</th>
                <th class="px-4 py-4 text-right">Biaya Aktual</th>
                <th class="px-4 py-4 text-right">Laba Kotor</th>
                <th class="px-8 py-4 text-right">Margin (estimasi)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $r)
            <tr class="hover:bg-slate-50/30">
                <td class="px-8 py-4"><span class="text-[13px] font-bold text-slate-700">{{ $r->nomor_nota }}</span><div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($r->tgl_order)->format('d M Y') }} - {{ $r->nama_konsumen ?? '-' }}</div></td>
                <td class="px-4 py-4 text-right text-sm">{{ number_format($r->omzet, 0, ',', '.') }}</td>
                <td class="px-4 py-4 text-right text-sm text-slate-500">{{ number_format($r->hpp, 0, ',', '.') }}</td>
                <td class="px-4 py-4 text-right text-sm {{ $r->biaya_rework > 0 ? 'text-rose-500 font-semibold' : 'text-slate-300' }}">{{ number_format($r->biaya_rework, 0, ',', '.') }}</td>
                <td class="px-4 py-4 text-right text-sm {{ $r->biaya_waste > 0 ? 'text-rose-500 font-semibold' : 'text-slate-300' }}">{{ number_format($r->biaya_waste, 0, ',', '.') }}</td>
                <td class="px-4 py-4 text-right text-sm font-semibold">{{ number_format($r->biaya_aktual, 0, ',', '.') }}</td>
                <td class="px-4 py-4 text-right text-sm font-bold {{ $r->laba < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($r->laba, 0, ',', '.') }} <span class="text-[10px] text-slate-400">({{ angka($r->margin, 1) }}%)</span></td>
                <td class="px-8 py-4 text-right text-xs text-slate-400">{{ $r->margin_estimasi }}%</td>
            </tr>
            @empty
            <tr><td colspan="8"><x-empty-state title="Tidak ada nota pada periode ini" /></td></tr>
            @endforelse
        </tbody>
    </x-table>
</div>
@endsection
