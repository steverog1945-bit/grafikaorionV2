@extends('layouts.app')

@section('content')
<style>
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        aside, .no-print, .topbar { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .eval-table th, .eval-table td { padding: 3px 6px !important; font-size: 10px !important; }
        .blok { break-inside: avoid; }
    }
    .eval-table { width: 100%; border-collapse: collapse; }
    .eval-table th { background: #f1f5f9; color: #475569; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: 8px 10px; border-bottom: 1px solid #cbd5e1; text-align: left; }
    .eval-table td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12.5px; color: #334155; vertical-align: top; }
    .eval-table td.num, .eval-table th.num { text-align: right; font-variant-numeric: tabular-nums; }
    .kartu { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px 16px; }
    .kartu .l { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; }
    .kartu .v { font-size: 18px; font-weight: 800; color: #1e293b; margin-top: 2px; }
</style>

@php
    $n = fn ($v, $d = 2) => angka((float) $v, $d);
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $k = $a['keuangan'];
    $sc = $a['salah_cetak'];
    $tabel = fn ($judul, $ket = null) => null;
@endphp

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1500px] mx-auto">

    <x-page-header title="Audit Bulanan">
        <x-slot:subtitle>
            <i class="fas fa-clipboard-check text-emerald-500"></i>
            Semua input {{ $bulan->translatedFormat('F Y') }} dalam satu halaman &middot; baca-saja
        </x-slot:subtitle>
    </x-page-header>

    <x-card class="mb-6 no-print">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Bulan</label>
                <x-form.input type="month" name="bulan" value="{{ $bulan->format('Y-m') }}" data-no-autosubmit />
            </div>
            <x-button type="submit" variant="primary"><i class="fas fa-filter"></i> Tampilkan</x-button>
            <a href="{{ route('audit-owner.csv', ['bulan' => $bulan->format('Y-m')]) }}" class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-bold hover:bg-emerald-100"><i class="fas fa-file-csv"></i> Unduh CSV (semua bagian)</a>
            <button type="button" onclick="window.print()" class="px-4 py-3 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold hover:bg-slate-200"><i class="fas fa-print"></i> Cetak</button>
        </form>
    </x-card>

    {{-- 1. KEUANGAN --}}
    <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-3">1. Keadaan Keuangan</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 blok">
        <div class="kartu"><div class="l">Omzet Nota ({{ $k['jumlah_nota'] }} nota)</div><div class="v">{{ $rp($k['omzet']) }}</div></div>
        <div class="kartu"><div class="l">Omzet Jersey ({{ $k['jumlah_jersey'] }} order)</div><div class="v">{{ $rp($k['omzet_jersey']) }}</div></div>
        <div class="kartu"><div class="l">Biaya Aktual (HPP+Rework+Waste)</div><div class="v">{{ $rp($k['biaya_aktual']) }}</div></div>
        <div class="kartu"><div class="l">Laba Kotor / Margin</div><div class="v {{ $k['laba_kotor'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $rp($k['laba_kotor']) }} <span class="text-xs">({{ $n($k['margin'], 1) }}%)</span></div></div>
        <div class="kartu"><div class="l">Saldo Kas (buku besar)</div><div class="v">{{ $rp($k['saldo_kas']) }}</div></div>
        <div class="kartu"><div class="l">Saldo Bank</div><div class="v">{{ $rp($k['saldo_bank']) }}</div></div>
        <div class="kartu"><div class="l">Piutang ({{ $k['nota_belum_lunas'] }} nota belum lunas)</div><div class="v">{{ $rp($k['piutang']) }}</div></div>
        <div class="kartu"><div class="l">Hutang Supplier</div><div class="v">{{ $rp($k['hutang']) }}</div></div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <x-card class="blok">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Uang Masuk (pembayaran nota)</h3>
            <table class="eval-table">
                <thead><tr><th>Metode</th><th class="num">Transaksi</th><th class="num">Total</th></tr></thead>
                <tbody>
                @forelse($k['kas_masuk'] as $r)<tr><td>{{ $r->metode }}</td><td class="num">{{ $r->jumlah }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@empty<tr><td colspan="3" class="text-slate-400">Tidak ada.</td></tr>@endforelse
                <tr><td colspan="2" class="font-bold">Total</td><td class="num font-bold">{{ $rp($k['kas_masuk_total']) }}</td></tr>
                <tr><td colspan="2">Pembayaran order jersey</td><td class="num">{{ $rp($k['pembayaran_jersey']) }}</td></tr>
                </tbody>
            </table>
        </x-card>
        <x-card class="blok">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Uang Keluar</h3>
            <table class="eval-table">
                <thead><tr><th>Uraian</th><th class="num">Jumlah</th><th class="num">Nilai</th></tr></thead>
                <tbody>
                <tr><td>Pembelian bahan (faktur)</td><td class="num">{{ $k['pembelian_jumlah'] }}</td><td class="num">{{ $rp($k['pembelian_total']) }}</td></tr>
                <tr><td>Pembayaran hutang supplier</td><td class="num">-</td><td class="num">{{ $rp($k['pembayaran_hutang']) }}</td></tr>
                @foreach($k['kas_kecil'] as $r)<tr><td>Kas kecil: {{ $r->kategori }}</td><td class="num">{{ $r->jumlah }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@endforeach
                @foreach($k['kas_manual'] as $r)<tr><td>Kas manual: {{ $r->status }}</td><td class="num">{{ $r->jumlah }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@endforeach
                </tbody>
            </table>
        </x-card>
    </div>

    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Kas Harian / Closing ({{ $a['kas_harian']->count() }} sesi)</h3>
        <table class="eval-table">
            <thead><tr><th>Tanggal</th><th>Kasir</th><th class="num">Saldo Awal</th><th class="num">Saldo Sistem</th><th class="num">Saldo Fisik</th><th class="num">Selisih</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody>
            @forelse($a['kas_harian'] as $r)
                <tr><td>{{ \Carbon\Carbon::parse($r->tgl_kas)->format('d/m/Y') }}</td><td>{{ $r->kasir }}</td><td class="num">{{ $rp($r->saldo_awal) }}</td><td class="num">{{ $rp($r->saldo_akhir) }}</td><td class="num">{{ $r->saldo_fisik !== null ? $rp($r->saldo_fisik) : '-' }}</td>
                    <td class="num {{ abs((float) $r->selisih) > 0 ? 'text-rose-600 font-bold' : '' }}">{{ $r->selisih !== null ? $rp($r->selisih) : '-' }}</td><td>{{ $r->status_lock }}</td><td>{{ $r->keterangan_closing }}</td></tr>
            @empty<tr><td colspan="8" class="text-slate-400">Tidak ada sesi kas.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>

    <x-card class="mb-10 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Nota Dibatalkan: {{ $k['nota_dibatalkan_jumlah'] }} nota &middot; {{ $rp($k['nota_dibatalkan_nilai']) }}</h3>
        <table class="eval-table">
            <thead><tr><th>Nota</th><th class="num">Nilai</th><th>Tanggal Batal</th><th>Alasan</th></tr></thead>
            <tbody>
            @forelse($k['nota_dibatalkan'] as $r)<tr><td>{{ $r->nomor_nota }}</td><td class="num">{{ $rp($r->total_bayar) }}</td><td>{{ $r->tgl_dibatalkan_nota }}</td><td>{{ $r->alasan_batal_nota }}</td></tr>@empty<tr><td colspan="4" class="text-slate-400">Tidak ada.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>

    {{-- 2. PERMINTAAN BAHAN --}}
    <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-3">2. Permintaan Bahan</h2>
    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Purchase Request
            @foreach($a['permintaan']['pr_per_status'] as $st => $c)<span class="ml-2 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 normal-case">{{ $st }}: {{ $c }}</span>@endforeach
        </h3>
        <table class="eval-table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Status</th><th>Pembuat</th><th>Item diminta</th></tr></thead>
            <tbody>
            @forelse($a['permintaan']['pr'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->status }}</td><td>{{ $r->pembuat }}</td><td>{{ $r->item }}</td></tr>@empty<tr><td colspan="5" class="text-slate-400">Tidak ada permintaan bahan.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>
    <x-card class="mb-10 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Purchase Order &middot; total {{ $rp($a['permintaan']['po_total']) }}</h3>
        <table class="eval-table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Supplier</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>
            @forelse($a['permintaan']['po'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->nama_supplier }}</td><td>{{ $r->status }}</td><td class="num">{{ $rp($r->total) }}</td></tr>@empty<tr><td colspan="5" class="text-slate-400">Tidak ada PO.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>

    {{-- 3. PEMAKAIAN BAHAN --}}
    <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-3">3. Pengurangan Bahan (cetak &amp; salah cetak)</h2>
    <x-card class="mb-6 blok">
        <p class="text-[11px] text-slate-400 mb-3">Dari Kartu Stok. Kontrol: stok awal + masuk &minus; keluar + penyesuaian = stok akhir (kolom Kontrol harus 0).</p>
        <div class="overflow-x-auto">
        <table class="eval-table">
            <thead><tr><th>Bahan</th><th>Satuan</th><th class="num">Stok Awal</th><th class="num">Masuk</th><th class="num">Keluar Cetak</th><th class="num">Salah Cetak (Waste)</th><th class="num">Rework</th><th class="num">Keluar Lain</th><th class="num">Penyesuaian</th><th class="num">Stok Akhir</th><th class="num">Nilai Salah Cetak</th><th class="num">Kontrol</th></tr></thead>
            <tbody>
            @forelse($a['stok'] as $r)
                <tr><td class="font-semibold">{{ $r->nama_bahan }}</td><td>{{ $r->satuan }}</td><td class="num">{{ $n($r->stok_awal, 4) }}</td><td class="num">{{ $n($r->masuk, 4) }}</td><td class="num">{{ $n($r->keluar_cetak, 4) }}</td>
                    <td class="num {{ $r->keluar_waste > 0 ? 'text-rose-600 font-semibold' : '' }}">{{ $n($r->keluar_waste, 4) }}</td><td class="num {{ $r->keluar_rework > 0 ? 'text-rose-600 font-semibold' : '' }}">{{ $n($r->keluar_rework, 4) }}</td>
                    <td class="num">{{ $n($r->keluar_lain, 4) }}</td><td class="num">{{ $n($r->penyesuaian, 4) }}</td><td class="num font-bold">{{ $n($r->stok_akhir, 4) }}</td>
                    <td class="num">{{ $rp($r->nilai_salah_cetak) }}</td><td class="num {{ abs($r->selisih_kontrol) > 0.0001 ? 'text-rose-600 font-bold' : 'text-emerald-600' }}">{{ abs($r->selisih_kontrol) > 0.0001 ? $n($r->selisih_kontrol, 4) : 'OK' }}</td></tr>
            @empty<tr><td colspan="12" class="text-slate-400">Tidak ada pergerakan stok.</td></tr>@endforelse
            </tbody>
        </table>
        </div>
    </x-card>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 blok">
        <div class="kartu"><div class="l">Biaya Waste Diterapkan</div><div class="v">{{ $rp($sc['waste_biaya']) }}</div></div>
        <div class="kartu"><div class="l">Waste Abnormal</div><div class="v">{{ $sc['waste_abnormal'] }} kejadian</div></div>
        <div class="kartu"><div class="l">Biaya Rework Selesai</div><div class="v">{{ $rp($sc['rework_biaya']) }}</div></div>
        <div class="kartu"><div class="l">Reject QC</div><div class="v">{{ $n($sc['qc_reject'], 4) }} / {{ $n($sc['qc_diperiksa'], 4) }} <span class="text-xs">({{ $n($sc['qc_persen_reject'], 2) }}%)</span></div></div>
    </div>
    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Rincian Waste (salah cetak / bahan terbuang)</h3>
        <table class="eval-table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Bahan</th><th class="num">Qty</th><th>Jenis</th><th>Status</th><th class="num">Biaya</th><th>Alasan</th><th>Petugas</th></tr></thead>
            <tbody>
            @forelse($sc['waste'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->nama_bahan }}</td><td class="num">{{ $n($r->qty, 4) }} {{ $r->satuan_stok }}</td><td>{{ $r->jenis }}</td><td>{{ $r->status }}</td><td class="num">{{ $rp($r->biaya) }}</td><td>{{ $r->alasan }}</td><td>{{ $r->petugas }}</td></tr>@empty<tr><td colspan="9" class="text-slate-400">Tidak ada waste.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>
    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Rincian Rework (cetak ulang)</h3>
        <table class="eval-table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th class="num">Qty</th><th>Status</th><th class="num">Biaya Material</th><th>Catatan</th><th>Petugas</th></tr></thead>
            <tbody>
            @forelse($sc['rework'] as $r)<tr><td>{{ $r->nomor }}</td><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td class="num">{{ $n($r->qty_rework, 4) }}</td><td>{{ $r->status }}</td><td class="num">{{ $rp($r->biaya_material) }}</td><td>{{ $r->catatan }}</td><td>{{ $r->petugas }}</td></tr>@empty<tr><td colspan="7" class="text-slate-400">Tidak ada rework.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>
    <x-card class="mb-10 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Penyesuaian Stok / Opname</h3>
        <table class="eval-table">
            <thead><tr><th>Tanggal</th><th>Bahan</th><th class="num">Sebelum</th><th class="num">Sesudah</th><th class="num">Selisih</th><th class="num">Nilai</th><th>Referensi / Alasan</th><th>Petugas</th></tr></thead>
            <tbody>
            @forelse($a['penyesuaian'] as $r)<tr><td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td><td>{{ $r->nama_bahan }}</td><td class="num">{{ $n($r->stok_sebelum, 4) }} {{ $r->satuan_stok }}</td><td class="num">{{ $n($r->stok_sesudah, 4) }} {{ $r->satuan_stok }}</td>
                <td class="num {{ $r->selisih < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $n($r->selisih, 4) }}</td><td class="num">{{ $rp($r->nilai) }}</td><td>{{ $r->referensi }} {{ $r->keterangan }}</td><td>{{ $r->petugas }}</td></tr>@empty<tr><td colspan="8" class="text-slate-400">Tidak ada penyesuaian.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>

    {{-- 4. PENGENDALIAN --}}
    <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-3">4. Pengendalian &amp; Jejak Input</h2>
    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Approval
            @foreach($a['approval']['per_status'] as $st => $c)<span class="ml-2 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 normal-case">{{ $st }}: {{ $c }}</span>@endforeach
        </h3>
        <table class="eval-table">
            <thead><tr><th>Dibuat</th><th>Jenis</th><th>Ringkasan</th><th>Pengaju</th><th>Alasan</th><th>Status</th><th>Pemutus</th><th>Catatan</th></tr></thead>
            <tbody>
            @forelse($a['approval']['daftar'] as $r)<tr><td>{{ $r->created_at }}</td><td>{{ str_replace('_', ' ', $r->tipe_aksi) }}</td><td>{{ $r->ringkasan }}</td><td>{{ $r->pengaju }}</td><td>{{ $r->alasan_pengajuan }}</td><td>{{ $r->status }}</td><td>{{ $r->pemutus }}</td><td>{{ $r->catatan_keputusan }}</td></tr>@empty<tr><td colspan="8" class="text-slate-400">Tidak ada pengajuan.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>
    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Input per Pengguna</h3>
        <table class="eval-table">
            <thead><tr><th>Pengguna</th>@foreach($a['input_user']['kolom'] as $c)<th class="num">{{ $c }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse($a['input_user']['baris'] as $r)<tr><td>{{ $r['nama'] }}</td>@foreach($a['input_user']['kolom'] as $c)<td class="num">{{ $r[$c] ?? 0 }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($a['input_user']['kolom']) + 1 }}" class="text-slate-400">Tidak ada input.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>
    <x-card class="mb-6 blok">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Jejak Aktivitas: {{ $a['jejak']['total'] }} catatan
            @foreach($a['jejak']['per_event'] as $ev => $c)<span class="ml-2 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 normal-case">{{ $ev }}: {{ $c }}</span>@endforeach
        </h3>
        <p class="text-[11px] text-slate-400 mb-3">Yang ditampilkan: perubahan terkait harga, pembatalan, koreksi, void, dan tutup paksa. Penelusuran lengkap ada di menu Log Aktivitas.</p>
        <table class="eval-table">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Event</th><th>Objek</th><th>Keterangan</th></tr></thead>
            <tbody>
            @forelse($a['jejak']['penting'] as $r)<tr><td>{{ $r->created_at }}</td><td>{{ $r->user_label }}</td><td>{{ $r->event }}</td><td>{{ class_basename($r->auditable_type) }}</td><td>{{ $r->keterangan }}</td></tr>@empty<tr><td colspan="5" class="text-slate-400">Tidak ada.</td></tr>@endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
