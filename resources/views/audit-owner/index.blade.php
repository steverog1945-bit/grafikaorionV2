@extends('layouts.app')

@section('content')
<style>
    /* ============================================================
       KACA HALAMAN - bahannya benar-benar kaca: di light mode latar
       PUTIH BENING (bukan turunan warna sidebar), blur dalam +
       saturasi tinggi, garis rambut, dan cahaya tepi atas. Semua
       warna hidup di variabel --kaca-* supaya light & dark memakai
       bahan yang sama, cukup ganti nilai variabelnya.
       ============================================================ */
    .kaca {
        --kaca-bg: rgba(255, 255, 255, 0.55);
        --kaca-hover: rgba(255, 255, 255, 0.72);
        --kaca-border: rgba(15, 23, 42, 0.08);
        --kaca-line: rgba(15, 23, 42, 0.06);
        --kaca-head: rgba(15, 23, 42, 0.03);
        --kaca-ink: #1d2433;
        --kaca-muted: #5b6472;
        background: var(--kaca-bg);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border: 1px solid var(--kaca-border);
        border-radius: 16px;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 12px 32px -16px rgba(15, 23, 42, 0.18),
            inset 0 1px 0 rgba(255, 255, 255, 0.65);
        transition:
            background 220ms cubic-bezier(0.32, 0.72, 0, 1),
            border-color 220ms cubic-bezier(0.32, 0.72, 0, 1),
            box-shadow 220ms cubic-bezier(0.32, 0.72, 0, 1);
    }
    .kaca:hover {
        background: var(--kaca-hover);
        border-color: rgba(15, 23, 42, 0.12);
        box-shadow:
            0 2px 4px rgba(15, 23, 42, 0.05),
            0 18px 40px -18px rgba(15, 23, 42, 0.24),
            inset 0 1px 0 rgba(255, 255, 255, 0.70);
    }
    html.dark .kaca {
        --kaca-bg: rgba(23, 34, 59, 0.62);
        --kaca-hover: rgba(30, 42, 73, 0.72);
        --kaca-border: rgba(255, 255, 255, 0.09);
        --kaca-line: rgba(255, 255, 255, 0.07);
        --kaca-head: rgba(255, 255, 255, 0.04);
        --kaca-ink: #f1f5f9;
        --kaca-muted: #94a3b8;
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.30),
            0 12px 32px -16px rgba(0, 0, 0, 0.50),
            inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    html.dark .kaca:hover {
        border-color: rgba(255, 255, 255, 0.14);
        box-shadow:
            0 2px 4px rgba(0, 0, 0, 0.35),
            0 18px 40px -18px rgba(0, 0, 0, 0.55),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    /* Kartu angka: mungil, satu fakta per kartu */
    .kaca-e {
        padding: 10px 14px 11px;
        border-radius: 14px;
        cursor: default;
    }
    .kaca-e .l { font-size: 9.5px; font-weight: 700; color: var(--kaca-muted); text-transform: uppercase; letter-spacing: 0.07em; line-height: 1.35; }
    .kaca-e .v { font-size: 15px; font-weight: 800; color: var(--kaca-ink); margin-top: 3px; font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
    .kaca-e .v small { font-size: 10.5px; font-weight: 700; }

    /* Panel berisi tabel */
    .kaca-p { padding: 12px 16px 8px; }
    .k-judul { font-size: 10px; font-weight: 700; color: var(--kaca-muted); text-transform: uppercase; letter-spacing: 0.09em; margin-bottom: 8px; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
    .k-ket { font-size: 10.5px; color: var(--kaca-muted); margin-bottom: 8px; }

    /* Tabel: rapat, tenang, baris bernafas */
    .t { width: 100%; border-collapse: collapse; }
    .t th { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--kaca-muted); text-align: left; padding: 6px 10px; border-bottom: 1px solid var(--kaca-border); background: var(--kaca-head); white-space: nowrap; }
    .t td { font-size: 12px; color: var(--kaca-ink); padding: 6px 10px; border-bottom: 1px solid var(--kaca-line); vertical-align: top; font-variant-numeric: tabular-nums; }
    .t tbody tr:last-child td { border-bottom: 0; }
    .t tbody tr { transition: background 140ms cubic-bezier(0.32, 0.72, 0, 1); }
    .t tbody tr:hover { background: var(--kaca-head); }
    .t td.num, .t th.num { text-align: right; font-variant-numeric: tabular-nums; }
    .t .kosong { color: var(--kaca-muted); font-style: italic; }

    /* Filter & tombol kaca */
    .kaca-input {
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(15, 23, 42, 0.10);
        border-radius: 10px; padding: 8px 12px;
        font-size: 13px; font-weight: 600; color: #1d2433;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: border-color 140ms cubic-bezier(0.32, 0.72, 0, 1), box-shadow 140ms cubic-bezier(0.32, 0.72, 0, 1), background 140ms cubic-bezier(0.32, 0.72, 0, 1);
    }
    .kaca-input:focus { outline: none; border-color: rgba(79, 70, 229, 0.45); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18); background: rgba(255, 255, 255, 0.85); }
    html.dark .kaca-input { background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.12); color: #e2e8f0; }
    html.dark .kaca-input:focus { background: rgba(255, 255, 255, 0.10); border-color: rgba(129, 140, 248, 0.5); }

    .kaca-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 999px;
        font-size: 12.5px; font-weight: 600; color: #1d2433;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(15, 23, 42, 0.10);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.6);
        transition: all 140ms cubic-bezier(0.32, 0.72, 0, 1);
        cursor: pointer;
    }
    .kaca-btn:hover { background: rgba(255, 255, 255, 0.85); border-color: rgba(15, 23, 42, 0.16); box-shadow: 0 3px 10px -2px rgba(15, 23, 42, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.7); }
    .kaca-btn:active { transform: scale(0.97); }
    html.dark .kaca-btn { background: rgba(255, 255, 255, 0.07); border-color: rgba(255, 255, 255, 0.12); color: #e2e8f0; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.06); }
    html.dark .kaca-btn:hover { background: rgba(255, 255, 255, 0.13); border-color: rgba(255, 255, 255, 0.18); }
    .kaca-btn-utama { background: #4f46e5; border-color: transparent; color: #fff; box-shadow: 0 4px 14px -4px rgba(79, 70, 229, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.22); }
    .kaca-btn-utama:hover { background: #4338ca; border-color: transparent; box-shadow: 0 6px 18px -4px rgba(79, 70, 229, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.25); }
    html.dark .kaca-btn-utama { background: #6366f1; color: #fff; }

    /* Pil status kecil di judul panel */
    .pil { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 600; color: var(--kaca-muted); background: var(--kaca-head); border: 1px solid var(--kaca-line); text-transform: none; letter-spacing: 0; }

    /* Nomor bab */
    .bab-no { width: 20px; height: 20px; border-radius: 999px; background: #1d2433; color: #fff; font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    html.dark .bab-no { background: #e2e8f0; color: #0f172a; }
    .bab-judul { font-size: 13px; font-weight: 700; color: #334155; letter-spacing: -0.01em; }
    html.dark .bab-judul { color: #cbd5e1; }

    @media (prefers-reduced-motion: reduce) {
        .kaca, .kaca-btn, .kaca-input, .t tbody tr { transition: none !important; }
    }

    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        aside, .no-print, .topbar { display: none !important; }
        main { margin-left: 0 !important; padding: 0 8mm !important; }
        body { background: #fff !important; }
        .kaca, .kaca-e {
            background: #fff !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
        .kaca:hover { background: #fff !important; }
        .t th { background: #f1f5f9 !important; color: #475569 !important; border-bottom: 1px solid #94a3b8 !important; }
        .t td { color: #1e293b !important; border-bottom: 1px solid #e2e8f0 !important; padding: 3px 6px !important; font-size: 10px !important; }
        .t th { padding: 3px 6px !important; font-size: 9px !important; }
        .kaca-e .l { color: #475569 !important; }
        .kaca-e .v { color: #0f172a !important; }
        .bab-no { background: #1e293b !important; color: #fff !important; }
        .bab-judul, .k-judul, .k-ket, .kaca-muted-print { color: #334155 !important; }
        .blok { break-inside: avoid; }
    }
</style>

@php
    $n = fn ($v, $d = 2) => angka((float) $v, $d);
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $k = $a['keuangan'];
    $sc = $a['salah_cetak'];
@endphp

<div class="w-full px-4 sm:px-6 lg:px-8 py-8 max-w-[1500px] mx-auto">

    <x-page-header title="Audit Bulanan">
        <x-slot:subtitle>
            <i class="fas fa-clipboard-check text-emerald-500"></i>
            Seluruh input {{ $bulan->translatedFormat('F Y') }} dalam satu halaman &middot; hanya-baca
        </x-slot:subtitle>
    </x-page-header>

    {{-- Filter: satu baris tenang, tanpa kotak besar --}}
    <div class="kaca no-print mb-6 px-5 py-3.5">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:var(--kaca-muted)">Bulan</span>
            <input type="month" name="bulan" value="{{ $bulan->format('Y-m') }}" class="kaca-input" data-no-autosubmit>
            <span class="flex-1"></span>
            <button type="submit" class="kaca-btn kaca-btn-utama"><i class="fas fa-filter text-[11px]"></i> Tampilkan</button>
            <a href="{{ route('audit-owner.csv', ['bulan' => $bulan->format('Y-m')]) }}" class="kaca-btn" title="Semua bagian laporan dalam satu berkas CSV"><i class="fas fa-file-csv text-[11px]"></i> Unduh CSV</a>
            <button type="button" onclick="window.print()" class="kaca-btn"><i class="fas fa-print text-[11px]"></i> Cetak</button>
        </form>
    </div>

    {{-- ================= 1. KEUANGAN ================= --}}
    <div class="flex items-center gap-2.5 mb-3">
        <span class="bab-no">1</span>
        <h2 class="bab-judul">Keuangan bulan ini</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 mb-3 blok">
        <div class="kaca kaca-e"><div class="l">Omzet nota &middot; {{ $k['jumlah_nota'] }} nota</div><div class="v">{{ $rp($k['omzet']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Omzet jersey &middot; {{ $k['jumlah_jersey'] }} order</div><div class="v">{{ $rp($k['omzet_jersey']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Biaya aktual &middot; HPP + rework + waste</div><div class="v">{{ $rp($k['biaya_aktual']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Laba kotor &middot; margin {{ $n($k['margin'], 1) }}%</div><div class="v {{ $k['laba_kotor'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $rp($k['laba_kotor']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Saldo kas &middot; buku besar</div><div class="v">{{ $rp($k['saldo_kas']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Saldo bank</div><div class="v">{{ $rp($k['saldo_bank']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Piutang &middot; {{ $k['nota_belum_lunas'] }} nota belum lunas</div><div class="v">{{ $rp($k['piutang']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Hutang supplier</div><div class="v">{{ $rp($k['hutang']) }}</div></div>
    </div>

    <div class="grid md:grid-cols-2 gap-3 mb-3">
        <div class="kaca kaca-p blok">
            <h3 class="k-judul">Uang masuk &mdash; pembayaran nota</h3>
            <table class="t">
                <thead><tr><th>Metode</th><th class="num">Transaksi</th><th class="num">Total</th></tr></thead>
                <tbody>
                @forelse($k['kas_masuk'] as $r)<tr><td>{{ $r->metode }}</td><td class="num">{{ $r->jumlah }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@empty<tr><td colspan="3" class="kosong">Belum ada pembayaran nota bulan ini.</td></tr>@endforelse
                <tr><td colspan="2" class="font-bold">Total</td><td class="num font-bold">{{ $rp($k['kas_masuk_total']) }}</td></tr>
                <tr><td>Pembayaran order jersey</td><td class="num">&mdash;</td><td class="num">{{ $rp($k['pembayaran_jersey']) }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="kaca kaca-p blok">
            <h3 class="k-judul">Uang keluar</h3>
            <table class="t">
                <thead><tr><th>Uraian</th><th class="num">Jumlah</th><th class="num">Nilai</th></tr></thead>
                <tbody>
                <tr><td>Pembelian bahan (faktur)</td><td class="num">{{ $k['pembelian_jumlah'] }}</td><td class="num">{{ $rp($k['pembelian_total']) }}</td></tr>
                <tr><td>Pembayaran hutang supplier</td><td class="num">&mdash;</td><td class="num">{{ $rp($k['pembayaran_hutang']) }}</td></tr>
                @foreach($k['kas_kecil'] as $r)<tr><td>Kas kecil &middot; {{ $r->kategori }}</td><td class="num">{{ $r->jumlah }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@endforeach
                @foreach($k['kas_manual'] as $r)<tr><td>Kas manual &middot; {{ $r->status }}</td><td class="num">{{ $r->jumlah }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@endforeach
                @if($k['kas_kecil']->isEmpty() && $k['kas_manual']->isEmpty())<tr><td colspan="3" class="kosong">Belum ada pengeluaran lain.</td></tr>@endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Kas harian &mdash; {{ $a['kas_harian']->count() }} sesi closing</h3>
        <table class="t">
            <thead><tr><th>Tanggal</th><th>Kasir</th><th class="num">Saldo awal</th><th class="num">Saldo sistem</th><th class="num">Saldo fisik</th><th class="num">Selisih</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody>
            @forelse($a['kas_harian'] as $r)
                <tr><td>{{ \Carbon\Carbon::parse($r->tgl_kas)->format('d/m/Y') }}</td><td>{{ $r->kasir }}</td><td class="num">{{ $rp($r->saldo_awal) }}</td><td class="num">{{ $rp($r->saldo_akhir) }}</td><td class="num">{{ $r->saldo_fisik !== null ? $rp($r->saldo_fisik) : '&mdash;' }}</td>
                    <td class="num {{ abs((float) $r->selisih) > 0 ? 'text-rose-600 font-bold' : '' }}">{{ $r->selisih !== null ? $rp($r->selisih) : '&mdash;' }}</td><td>{{ $r->status_lock }}</td><td>{{ $r->keterangan_closing }}</td></tr>
            @empty<tr><td colspan="8" class="kosong">Belum ada sesi kas bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Nota dibatalkan &mdash; {{ $k['nota_dibatalkan_jumlah'] }} nota senilai {{ $rp($k['nota_dibatalkan_nilai']) }}</h3>
        <table class="t">
            <thead><tr><th>Nota</th><th class="num">Nilai</th><th>Tanggal batal</th><th>Alasan</th></tr></thead>
            <tbody>
            @forelse($k['nota_dibatalkan'] as $r)<tr><td>{{ $r->nomor_nota }}</td><td class="num">{{ $rp($r->total_bayar) }}</td><td>{{ $r->tgl_dibatalkan_nota }}</td><td>{{ $r->alasan_batal_nota }}</td></tr>@empty<tr><td colspan="4" class="kosong">Tidak ada nota yang dibatalkan.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= 2. PERMINTAAN BAHAN ================= --}}
    <div class="flex items-center gap-2.5 mb-3 mt-8">
        <span class="bab-no">2</span>
        <h2 class="bab-judul">Permintaan bahan</h2>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Purchase Request
            @foreach($a['permintaan']['pr_per_status'] as $st => $c)<span class="pil">{{ $st }} &middot; {{ $c }}</span>@endforeach
        </h3>
        <table class="t">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Status</th><th>Pembuat</th><th>Item diminta</th></tr></thead>
            <tbody>
            @forelse($a['permintaan']['pr'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->status }}</td><td>{{ $r->pembuat }}</td><td>{{ $r->item }}</td></tr>@empty<tr><td colspan="5" class="kosong">Belum ada permintaan bahan.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Purchase Order <span class="pil">total {{ $rp($a['permintaan']['po_total']) }}</span></h3>
        <table class="t">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Supplier</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>
            @forelse($a['permintaan']['po'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->nama_supplier }}</td><td>{{ $r->status }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@empty<tr><td colspan="5" class="kosong">Belum ada PO bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= 3. PEMAKAIAN BAHAN ================= --}}
    <div class="flex items-center gap-2.5 mb-3 mt-8">
        <span class="bab-no">3</span>
        <h2 class="bab-judul">Pemakaian bahan &mdash; cetak &amp; salah cetak</h2>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <p class="k-ket">Dihitung dari Kartu Stok. Uji: stok awal + masuk &minus; keluar + penyesuaian harus pas dengan stok akhir &mdash; kolom <b>Kontrol</b> menunjukkan 0 kalau cocok.</p>
        <div class="overflow-x-auto">
        <table class="t">
            <thead><tr><th>Bahan</th><th>Satuan</th><th class="num">Stok awal</th><th class="num">Masuk</th><th class="num">Keluar cetak</th><th class="num">Salah cetak</th><th class="num">Rework</th><th class="num">Keluar lain</th><th class="num">Penyesuaian</th><th class="num">Stok akhir</th><th class="num">Nilai salah cetak</th><th class="num">Kontrol</th></tr></thead>
            <tbody>
            @forelse($a['stok'] as $r)
                <tr><td class="font-semibold">{{ $r->nama_bahan }}</td><td>{{ $r->satuan }}</td><td class="num">{{ $n($r->stok_awal, 4) }}</td><td class="num">{{ $n($r->masuk, 4) }}</td><td class="num">{{ $n($r->keluar_cetak, 4) }}</td>
                    <td class="num {{ $r->keluar_waste > 0 ? 'text-rose-600 font-semibold' : '' }}">{{ $n($r->keluar_waste, 4) }}</td><td class="num {{ $r->keluar_rework > 0 ? 'text-rose-600 font-semibold' : '' }}">{{ $n($r->keluar_rework, 4) }}</td>
                    <td class="num">{{ $n($r->keluar_lain, 4) }}</td><td class="num">{{ $n($r->penyesuaian, 4) }}</td><td class="num font-bold">{{ $n($r->stok_akhir, 4) }}</td>
                    <td class="num">{{ $rp($r->nilai_salah_cetak) }}</td><td class="num {{ abs($r->selisih_kontrol) > 0.0001 ? 'text-rose-600 font-bold' : 'text-emerald-600' }}">{{ abs($r->selisih_kontrol) > 0.0001 ? $n($r->selisih_kontrol, 4) : 'pas' }}</td></tr>
            @empty<tr><td colspan="12" class="kosong">Belum ada pergerakan stok.</td></tr>@endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 mb-3 blok">
        <div class="kaca kaca-e"><div class="l">Biaya waste diterapkan</div><div class="v">{{ $rp($sc['waste_biaya']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Waste abnormal</div><div class="v">{{ $sc['waste_abnormal'] }} <small>kejadian</small></div></div>
        <div class="kaca kaca-e"><div class="l">Biaya rework selesai</div><div class="v">{{ $rp($sc['rework_biaya']) }}</div></div>
        <div class="kaca kaca-e"><div class="l">Reject QC</div><div class="v">{{ $n($sc['qc_reject'], 4) }} <small>/ {{ $n($sc['qc_diperiksa'], 4) }} &middot; {{ $n($sc['qc_persen_reject'], 2) }}%</small></div></div>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Rincian waste &mdash; bahan terbuang</h3>
        <table class="t">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Bahan</th><th class="num">Qty</th><th>Jenis</th><th>Status</th><th class="num">Biaya</th><th>Alasan</th><th>Petugas</th></tr></thead>
            <tbody>
            @forelse($sc['waste'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->nama_bahan }}</td><td class="num">{{ $n($r->qty, 4) }} {{ $r->satuan_stok }}</td><td>{{ $r->jenis }}</td><td>{{ $r->status }}</td><td class="num">{{ $rp($r->biaya) }}</td><td>{{ $r->alasan }}</td><td>{{ $r->petugas }}</td></tr>@empty<tr><td colspan="9" class="kosong">Tidak ada waste bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Rincian rework &mdash; cetak ulang</h3>
        <table class="t">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th class="num">Qty</th><th>Status</th><th class="num">Biaya material</th><th>Catatan</th><th>Petugas</th></tr></thead>
            <tbody>
            @forelse($sc['rework'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td class="num">{{ $n($r->qty_rework, 4) }}</td><td>{{ $r->status }}</td><td class="num">{{ $rp($r->biaya_material) }}</td><td>{{ $r->catatan }}</td><td>{{ $r->petugas }}</td></tr>@empty<tr><td colspan="7" class="kosong">Tidak ada rework bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Penyesuaian stok &amp; opname</h3>
        <table class="t">
            <thead><tr><th>Tanggal</th><th>Bahan</th><th class="num">Sebelum</th><th class="num">Sesudah</th><th class="num">Selisih</th><th class="num">Nilai</th><th>Referensi / alasan</th><th>Petugas</th></tr></thead>
            <tbody>
            @forelse($a['penyesuaian'] as $r)<tr><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->nama_bahan }}</td><td class="num">{{ $n($r->stok_sebelum, 4) }} {{ $r->satuan_stok }}</td><td class="num">{{ $n($r->stok_sesudah, 4) }} {{ $r->satuan_stok }}</td>
                <td class="num {{ $r->selisih < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $n($r->selisih, 4) }}</td><td class="num">{{ $rp($r->nilai) }}</td><td>{{ $r->referensi }} {{ $r->keterangan }}</td><td>{{ $r->petugas }}</td></tr>@empty<tr><td colspan="8" class="kosong">Tidak ada penyesuaian bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= 4. PENGENDALIAN ================= --}}
    <div class="flex items-center gap-2.5 mb-3 mt-8">
        <span class="bab-no">4</span>
        <h2 class="bab-judul">Pengendalian &amp; jejak input</h2>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Pengajuan &amp; persetujuan
            @foreach($a['approval']['per_status'] as $st => $c)<span class="pil">{{ $st }} &middot; {{ $c }}</span>@endforeach
        </h3>
        <table class="t">
            <thead><tr><th>Dibuat</th><th>Jenis</th><th>Ringkasan</th><th>Pengaju</th><th>Alasan</th><th>Status</th><th>Pemutus</th><th>Catatan</th></tr></thead>
            <tbody>
            @forelse($a['approval']['daftar'] as $r)<tr><td>{{ $r->created_at }}</td><td>{{ str_replace('_', ' ', $r->tipe_aksi) }}</td><td>{{ $r->ringkasan }}</td><td>{{ $r->pengaju }}</td><td>{{ $r->alasan_pengajuan }}</td><td>{{ $r->status }}</td><td>{{ $r->pemutus }}</td><td>{{ $r->catatan_keputusan }}</td></tr>@empty<tr><td colspan="8" class="kosong">Belum ada pengajuan bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    <div class="kaca kaca-p mb-3 blok">
        <h3 class="k-judul">Input per pengguna</h3>
        <table class="t">
            <thead><tr><th>Pengguna</th>@foreach($a['input_user']['kolom'] as $c)<th class="num">{{ $c }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse($a['input_user']['baris'] as $r)<tr><td>{{ $r['nama'] }}</td>@foreach($a['input_user']['kolom'] as $c)<td class="num">{{ $r[$c] ?? 0 }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($a['input_user']['kolom']) + 1 }}" class="kosong">Belum ada input tercatat.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    <div class="kaca kaca-p mb-6 blok">
        <h3 class="k-judul">Jejak aktivitas <span class="pil">{{ $a['jejak']['total'] }} catatan</span>
            @foreach($a['jejak']['per_event'] as $ev => $c)<span class="pil">{{ $ev }} &middot; {{ $c }}</span>@endforeach
        </h3>
        <p class="k-ket">Yang ditampilkan hanya yang penting: perubahan harga, pembatalan, koreksi, void, dan tutup paksa. Riwayat lengkapnya ada di menu Log Aktivitas.</p>
        <table class="t">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Event</th><th>Objek</th><th>Keterangan</th></tr></thead>
            <tbody>
            @forelse($a['jejak']['penting'] as $r)<tr><td>{{ $r->created_at }}</td><td>{{ $r->user_label }}</td><td>{{ $r->event }}</td><td>{{ class_basename($r->auditable_type) }}</td><td>{{ $r->keterangan }}</td></tr>@empty<tr><td colspan="5" class="kosong">Tidak ada aktivitas khusus bulan ini.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
