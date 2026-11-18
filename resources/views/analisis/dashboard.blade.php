@extends('layouts.app')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp
<div class="w-full px-4 sm:px-6 lg:px-8 py-8 max-w-[1500px] mx-auto eksekutif-dash">

    <x-page-header title="Dashboard Eksekutif">
        <x-slot:subtitle>
            <i class="fas fa-chart-line text-amber-400"></i>
            {{ $dari->translatedFormat('d M Y') }} &ndash; {{ $sampai->translatedFormat('d M Y') }}
        </x-slot:subtitle>
    </x-page-header>

    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4" role="search">
            <div><label for="f-dari" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Dari</label><x-form.input id="f-dari" type="date" name="tgl_dari" value="{{ $dari->toDateString() }}" /></div>
            <div><label for="f-sampai" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Sampai</label><x-form.input id="f-sampai" type="date" name="tgl_sampai" value="{{ $sampai->toDateString() }}" /></div>
            <x-button type="submit" variant="primary"><i class="fas fa-filter text-xs"></i> Tampilkan</x-button>
            <a href="{{ route('eksekutif.dashboard') }}" class="text-xs font-medium text-slate-400 hover:text-indigo-600 pb-3 transition-colors">Bulan ini</a>
        </form>
    </x-card>

    @if(count($peringatan))
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach($peringatan as $p)
        <a href="{{ $p['url'] }}" class="peringatan-tile group flex items-center gap-3 p-3.5 rounded-xl bg-amber-50 border border-amber-200/70 hover:bg-amber-100/70 focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:outline-none transition">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 group-hover:scale-105 transition-transform"><i class="fas fa-triangle-exclamation text-sm"></i></span>
            <span class="min-w-0">
                <span class="block text-[12px] font-medium text-amber-800 leading-snug">{{ $p['label'] }}</span>
                <span class="block text-lg font-semibold text-amber-700 tabular-nums leading-tight">{{ angka($p['jumlah'], 4) }}</span>
            </span>
            <i class="fas fa-chevron-right text-[10px] text-amber-300 ml-auto group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
        </a>
        @endforeach
    </div>
    @endif

    {{-- KPI utama: satu momen count-up saat halaman dibuka, sisanya quiet.
         Nilai asli sudah tersaji sebagai teks (tanpa JS tetap terbaca). --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
        @foreach([
            ['Omzet', 'text-slate-800', 'fa-sack-dollar', 'bg-indigo-50 text-indigo-600', $r['omzet'], $r['jumlah_nota'] . ' nota'],
            ['Biaya Aktual', 'text-amber-600', 'fa-receipt', 'bg-amber-50 text-amber-600', $r['biaya_aktual'], null],
            ['Laba Kotor', $r['laba_kotor'] < 0 ? 'text-rose-600' : 'text-emerald-600', 'fa-coins', $r['laba_kotor'] < 0 ? 'bg-rose-50 text-rose-500' : 'bg-emerald-50 text-emerald-600', $r['laba_kotor'], 'margin ' . $r['margin'] . '%'],
            ['Omzet Jersey', 'text-violet-600', 'fa-shirt', 'bg-violet-50 text-violet-600', $r['omzet_jersey'], $r['jumlah_jersey'] . ' order'],
        ] as [$label, $warna, $ikon, $chip, $mentah, $sub])
        <x-card class="kpi-tile group">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">{{ $label }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $chip }} group-hover:scale-110 transition-transform duration-200"><i class="fas {{ $ikon }} text-[13px]" aria-hidden="true"></i></span>
            </div>
            <div class="text-lg sm:text-xl font-semibold {{ $warna }} tabular-nums tracking-tight leading-none" data-kpi-value="{{ $mentah }}">{{ $rp($mentah) }}</div>
            @if($sub)<div class="mt-1.5 text-[11px] font-medium text-slate-400 tabular-nums">{{ $sub }}</div>@endif
        </x-card>
        @endforeach
    </div>
    {{-- Biaya aktual: HPP + rework + waste; belum termasuk tenaga kerja & overhead. --}}
    <x-hint tip="Biaya aktual = HPP + rework + waste. Belum termasuk tenaga kerja & overhead." icon="chart-line" />

    {{-- Saldo ringkas: label pendek, tanpa ikon berdesir, kolom menciut cerdas di layar kecil. --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-6 mt-4">
        @foreach([
            ['Kas', $r['saldo_kas'], 'text-slate-800', 'fa-vault'],
            ['Bank', $r['saldo_bank'], 'text-slate-800', 'fa-building-columns'],
            ['Piutang', $r['piutang'], 'text-rose-600', 'fa-file-invoice-dollar'],
            ['Hutang', $r['hutang'], 'text-amber-600', 'fa-truck-field'],
            ['Terbayar', $r['terbayar'], 'text-emerald-600', 'fa-hand-holding-dollar'],
        ] as [$label, $nilai, $warna, $ikon])
        <x-card class="kpi-saldo group">
            <div class="flex items-center gap-1.5 mb-1.5">
                <i class="fas {{ $ikon }} text-[10px] text-slate-300 group-hover:text-indigo-400 transition-colors" aria-hidden="true"></i>
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">{{ $label }}</span>
            </div>
            <div class="text-base sm:text-lg font-semibold {{ $warna }} tabular-nums tracking-tight leading-none" data-kpi-value="{{ $nilai }}">{{ $rp($nilai) }}</div>
        </x-card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <script type="application/json" id="eksekutif-tren-data">@json($tren)</script>
        <x-card class="xl:col-span-2" data-chart-card>
            <div class="flex items-baseline justify-between gap-3 mb-4">
                <h3 class="text-sm font-semibold text-slate-800">Omzet 6 Bulan Terakhir</h3>
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">uPlot</span>
            </div>
            <div id="dashboard-eksekutif-chart" class="w-full" style="min-height: 190px"></div>
            <noscript><p class="text-xs text-slate-400 italic">Butuh JavaScript untuk grafik.</p></noscript>
        </x-card>

        <x-card class="estimasi-card">
            <div class="flex items-baseline justify-between gap-3 mb-1">
                <h3 class="text-sm font-semibold text-slate-800">Estimasi Bulan Depan</h3>
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">3-bln rata-rata</span>
            </div>
            {{-- Rincian periode penuh tersedia lewat tooltip ikon (hover/focus), teks layar tetap satu baris. --}}
            <p class="text-[11px] text-slate-400 mb-4 flex items-center gap-1.5">Acuan kasar, bukan prediksi
                <span class="est-info inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 text-[9px] text-slate-400 cursor-help" tabindex="0" role="img" aria-label="Periode acuan: {{ $estimasi['periode'] }}. Kas keluar = biaya dari kas usaha; hutang supplier dan biaya yang dibayar pemilik tidak termasuk." title="Periode acuan: {{ $estimasi['periode'] }}. Kas keluar = biaya dari kas usaha; hutang supplier &amp; biaya pemilik tidak termasuk.">i</span>
            </p>
            <div class="space-y-2.5 text-sm">
                <div class="est-row"><span class="text-slate-500">Omzet</span><b class="tabular-nums">{{ $rp($estimasi['omzet']) }}</b></div>
                <div class="est-row"><span class="text-slate-500">Belanja bahan</span><b class="tabular-nums">{{ $rp($estimasi['belanja_bahan']) }}</b></div>
                <div class="est-row"><span class="text-slate-500">Kas masuk</span><b class="tabular-nums text-emerald-700">+{{ $rp($estimasi['kas_masuk']) }}</b></div>
                <div class="est-row"><span class="text-slate-500">Kas keluar</span><b class="tabular-nums text-rose-700">&minus;{{ $rp($estimasi['kas_keluar']) }}</b></div>
                <div class="flex justify-between pt-2.5 border-t border-slate-100"><span class="font-semibold text-slate-700">Arus kas bersih</span><b class="tabular-nums {{ $estimasi['arus_kas_bersih'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $rp($estimasi['arus_kas_bersih']) }}</b></div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @php
            $topMaksPelanggan = max(1, (float) ($topPelanggan->max('omzet') ?? 1));
            $topMaksProduk    = max(1, (float) ($topProduk->max('omzet') ?? 1));
        @endphp
        <x-card>
            <div class="flex items-baseline justify-between gap-3 mb-3">
                <h3 class="text-sm font-semibold text-slate-800">Top 5 Pelanggan</h3>
                <a href="{{ route('laporan.order') }}" class="text-[10px] font-medium text-indigo-600 uppercase tracking-widest hover:underline">Semua</a>
            </div>
            @forelse($topPelanggan as $p)
                <div class="top-row group" data-top-row>
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="text-sm text-slate-600 truncate min-w-0">{{ $p->nama }} <span class="text-[11px] text-slate-400">&middot; {{ $p->jumlah_nota }} nota</span></span>
                        <b class="text-sm tabular-nums whitespace-nowrap">{{ $rp($p->omzet) }}</b>
                    </div>
                    <div class="top-bar" aria-hidden="true"><span style="width: {{ max(2, round($p->omzet / $topMaksPelanggan * 100, 1)) }}%"></span></div>
                </div>
            @empty<p class="text-xs text-slate-400 italic py-4">Belum ada transaksi periode ini.</p>@endforelse
        </x-card>

        <x-card>
            <div class="flex items-baseline justify-between gap-3 mb-3">
                <h3 class="text-sm font-semibold text-slate-800">Top 5 Produk / Jasa</h3>
                <a href="{{ route('laporan.profitabilitas') }}" class="text-[10px] font-medium text-indigo-600 uppercase tracking-widest hover:underline">Semua</a>
            </div>
            @forelse($topProduk as $p)
                <div class="top-row group" data-top-row>
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="text-sm text-slate-600 truncate min-w-0">{{ $p->nama }} <span class="text-[11px] text-slate-400">&middot; {{ angka($p->qty, 2) }}</span></span>
                        <b class="text-sm tabular-nums whitespace-nowrap">{{ $rp($p->omzet) }}</b>
                    </div>
                    <div class="top-bar" aria-hidden="true"><span style="width: {{ max(2, round($p->omzet / $topMaksProduk * 100, 1)) }}%"></span></div>
                </div>
            @empty<p class="text-xs text-slate-400 italic py-4">Belum ada transaksi periode ini.</p>@endforelse
        </x-card>
    </div>

    <div class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-[11px] font-medium uppercase tracking-widest">
        <a href="{{ route('laporan.profitabilitas') }}" class="text-indigo-600 hover:underline underline-offset-4">Profitabilitas</a>
        <a href="{{ route('laporan.pemakaian-bahan') }}" class="text-indigo-600 hover:underline underline-offset-4">Pemakaian bahan</a>
        <a href="{{ route('laporan.job-costing') }}" class="text-indigo-600 hover:underline underline-offset-4">Job costing</a>
    </div>
</div>

{{-- Gaya khusus halaman ini (inline, bukan app.css) supaya build/bundle tidak berubah.
     Konsisten dengan guideline: transform/opacity only, focus-visible, reduced-motion. --}}
<style>
.eksekutif-dash .kpi-tile,.eksekutif-dash .kpi-saldo{transition:transform .18s ease,box-shadow .18s ease}
.eksekutif-dash .kpi-tile:hover,.eksekutif-dash .kpi-saldo:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,23,42,.08)}
.eksekutif-dash .kpi-tile:focus-visible,.eksekutif-dash .kpi-saldo:focus-visible{outline:2px solid #6366f1;outline-offset:2px}
.eksekutif-dash .peringatan-tile:active{transform:scale(.98)}
.eksekutif-dash .est-row{display:flex;justify-content:space-between;gap:1rem}
.eksekutif-dash .est-info:focus-visible{outline:2px solid #6366f1;outline-offset:2px;border-radius:9999px}
.eksekutif-dash .top-row{padding:.45rem .25rem;border-bottom:1px solid #f1f5f9}
.eksekutif-dash .top-row:last-child{border-bottom:0}
.eksekutif-dash .top-bar{height:4px;border-radius:9999px;background:#f1f5f9;margin-top:.35rem;overflow:hidden}
.eksekutif-dash .top-bar span{display:block;height:100%;border-radius:9999px;background:linear-gradient(90deg,#c7d2fe,#818cf8);transform-origin:left center;animation:eksekutif-bar-in .7s cubic-bezier(.22,1,.36,1) both}
@keyframes eksekutif-bar-in{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@media (prefers-reduced-motion: reduce){.eksekutif-dash .kpi-tile,.eksekutif-dash .kpi-saldo,.eksekutif-dash .peringatan-tile{transition:none}.eksekutif-dash .top-bar span{animation:none}}
</style>
@push('scripts')
<script>
// Count-up satu momen pada first paint. Tanpa JS, angka asli tetap tersaji (dari markup).
(function () {
    var els = document.querySelectorAll('.eksekutif-dash [data-kpi-value]');
    if (!els.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var dur = 900, ease = function (t) { return 1 - Math.pow(2, -10 * t); };
    var start = null;
    function frame(ts) {
        if (!start) start = ts;
        var p = Math.min(1, (ts - start) / dur), e = ease(p);
        els.forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-kpi-value')) || 0;
            el.textContent = 'Rp ' + Math.round(target * e).toLocaleString('id-ID');
        });
        if (p < 1) requestAnimationFrame(frame);
        else els.forEach(function (el) { // pastikan nilai final presisi (bukan pembulatan animasi)
            var target = parseFloat(el.getAttribute('data-kpi-value')) || 0;
            el.textContent = 'Rp ' + Math.round(target).toLocaleString('id-ID');
        });
    }
    requestAnimationFrame(frame);
})();
</script>
@endpush
@endsection
