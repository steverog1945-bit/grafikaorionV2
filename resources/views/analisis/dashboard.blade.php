@extends('layouts.app')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1500px] mx-auto">

    <x-page-header title="Dashboard Eksekutif">
        <x-slot:subtitle>
            <i class="fas fa-chart-line text-amber-400"></i>
            {{ $dari->translatedFormat('d M Y') }} - {{ $sampai->translatedFormat('d M Y') }}
        </x-slot:subtitle>
    </x-page-header>

    <x-card class="mb-8">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div><label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari</label><x-form.input type="date" name="tgl_dari" value="{{ $dari->toDateString() }}" /></div>
            <div><label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai</label><x-form.input type="date" name="tgl_sampai" value="{{ $sampai->toDateString() }}" /></div>
            <x-button type="submit" variant="primary"><i class="fas fa-filter"></i> Tampilkan</x-button>
            <a href="{{ route('eksekutif.dashboard') }}" class="text-xs font-medium text-slate-400 hover:text-indigo-600 pb-3">Bulan ini</a>
        </form>
    </x-card>

    @if(count($peringatan))
    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach($peringatan as $p)
        <a href="{{ $p['url'] }}" class="flex items-center justify-between p-4 rounded-lg bg-amber-50 border border-amber-100 hover:bg-amber-100 transition">
            <span class="text-sm font-medium text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $p['label'] }}</span>
            <span class="text-lg font-medium text-amber-700">{{ angka($p['jumlah'], 4) }}</span>
        </a>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        @foreach([
            ['Omzet (' . $r['jumlah_nota'] . ' nota)', $rp($r['omzet']), 'text-slate-800', $r['omzet']],
            ['Biaya Aktual', $rp($r['biaya_aktual']), 'text-amber-600', $r['biaya_aktual']],
            ['Laba Kotor (' . $r['margin'] . '%)', $rp($r['laba_kotor']), $r['laba_kotor'] < 0 ? 'text-rose-600' : 'text-emerald-600', $r['laba_kotor']],
            ['Omzet Jersey (' . $r['jumlah_jersey'] . ' order)', $rp($r['omzet_jersey']), 'text-violet-600', $r['omzet_jersey']],
        ] as [$label, $nilai, $warna, $mentah])
        <x-card><div class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2">{{ $label }}</div><div class="text-xl font-medium {{ $warna }}" data-kpi-value="{{ $mentah }}">{{ $nilai }}</div></x-card>
        @endforeach
    </div>
    <x-hint tip="Biaya aktual = HPP material + rework + waste. Tenaga kerja, mesin, overhead, dan beban operasional belum termasuk, jadi laba kotor bukan laba bersih." icon="chart-line" />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([['Saldo Kas', $r['saldo_kas'], 'text-slate-800'], ['Saldo Bank', $r['saldo_bank'], 'text-slate-800'], ['Piutang Usaha', $r['piutang'], 'text-rose-600'], ['Hutang Usaha', $r['hutang'], 'text-amber-600']] as [$label, $nilai, $warna])
        <x-card><div class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2">{{ $label }} (buku besar)</div><div class="text-lg font-medium {{ $warna }}">{{ $rp($nilai) }}</div></x-card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <script type="application/json" id="eksekutif-tren-data">@json($tren)</script>
        <x-card class="xl:col-span-2" data-chart-card>
            <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest mb-5">Omzet 6 Bulan Terakhir</h3>
            {{-- Grafik canvas uPlot (virtualized decimation ~4-5jt titik/detik di canvas). Dimuat hanya di halaman ini via app.js; warna & jarak mengikuti token yang sudah dipakai app. --}}
            <div id="dashboard-eksekutif-chart" class="w-full" style="min-height: 190px"></div>
            <noscript><p class="text-xs text-slate-400 italic">Butuh JavaScript untuk grafik.</p></noscript>
        </x-card>

        <x-card>
            <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest mb-1">Estimasi Bulan Depan</h3>
            <p class="text-[11px] text-slate-400 mb-4">Rata-rata 3 bulan penuh ({{ $estimasi['periode'] }}). Acuan kasar, bukan prediksi. Kas keluar = biaya dari kas usaha; hutang supplier dan biaya yang dibayar pemilik tidak termasuk.</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Omzet</span><b>{{ $rp($estimasi['omzet']) }}</b></div>
                <div class="flex justify-between"><span class="text-slate-500">Belanja bahan</span><b>{{ $rp($estimasi['belanja_bahan']) }}</b></div>
                <div class="flex justify-between"><span class="text-slate-500">Kas masuk</span><b>{{ $rp($estimasi['kas_masuk']) }}</b></div>
                <div class="flex justify-between"><span class="text-slate-500">Kas keluar</span><b>{{ $rp($estimasi['kas_keluar']) }}</b></div>
                <div class="flex justify-between pt-2 border-t border-slate-100"><span class="font-medium text-slate-700">Arus kas bersih</span><b class="{{ $estimasi['arus_kas_bersih'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $rp($estimasi['arus_kas_bersih']) }}</b></div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest mb-4">Top 5 Pelanggan</h3>
            @forelse($topPelanggan as $p)
                <div class="flex justify-between py-2 border-b border-slate-50 text-sm" data-top-row><span class="text-slate-600">{{ $p->nama }} <span class="text-[11px] text-slate-400">({{ $p->jumlah_nota }} nota)</span></span><b>{{ $rp($p->omzet) }}</b></div>
            @empty<p class="text-xs text-slate-400 italic">Belum ada transaksi pada periode ini.</p>@endforelse
        </x-card>
        <x-card>
            <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest mb-4">Top 5 Produk / Jasa</h3>
            @forelse($topProduk as $p)
                <div class="flex justify-between py-2 border-b border-slate-50 text-sm" data-top-row><span class="text-slate-600">{{ $p->nama }} <span class="text-[11px] text-slate-400">({{ angka($p->qty, 2) }})</span></span><b>{{ $rp($p->omzet) }}</b></div>
            @empty<p class="text-xs text-slate-400 italic">Belum ada transaksi pada periode ini.</p>@endforelse
        </x-card>
    </div>

    <div class="mt-6 flex flex-wrap gap-4 text-xs font-medium uppercase tracking-widest">
        <a href="{{ route('laporan.profitabilitas') }}" class="text-indigo-600 hover:underline">Profitabilitas per produk/pelanggan/mesin</a>
        <a href="{{ route('laporan.pemakaian-bahan') }}" class="text-indigo-600 hover:underline">Analisis pemakaian bahan</a>
        <a href="{{ route('laporan.job-costing') }}" class="text-indigo-600 hover:underline">Job costing per nota</a>
    </div>
</div>
@endsection
