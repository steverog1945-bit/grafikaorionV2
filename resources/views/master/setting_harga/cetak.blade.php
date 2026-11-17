@extends('layouts.app')

@section('content')
@php
    $landscape = request('orientasi', $golongan->count() > 4 ? 'landscape' : 'portrait') === 'landscape';
    $rpc = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp
{{-- Gaya dipakai di sini saja (prefix .dh-): tidak menimpa halaman lain.
     Font/aset semuanya lokal — layouts.app memuat font Inter & Ikon FA dari
     public/vendor, tanpa CDN. --}}
<style>
    .dh-sheet { max-width: 1600px; margin: 0 auto; }

    .dh-tabel { width: 100%; border-collapse: collapse; }
    .dh-tabel th { background: #f5f5f5; color: #171717; font-size: var(--fs-tabel, 10px); font-weight: 600; padding: 4px 6px; border-bottom: 1px solid #e5e5e5; text-align: right; }
    .dh-tabel th:first-child { text-align: left; }
    .dh-tabel td { padding: 4px 6px; border-bottom: 1px solid #ececec; font-size: var(--fs-tabel, 10px); color: #171717; }
    {{-- Mode gelap: sebelumnya kepala tabel tetap terang --}}
    html.dark .dh-tabel th { background: #1f2937; color: #e5e7eb; border-bottom-color: #2e3f4f; }
    html.dark .dh-tabel td { border-bottom-color: #223041; color: #dce9ed; }
    .dh-tabel td.num, .dh-tabel th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .dh-tabel td.kosong { color: #C7D3CB; }
    .dh-blok { break-inside: avoid; page-break-inside: avoid; margin-bottom: 12px; }
    .dh-blok-nama { font-weight: 600; font-size: 12px; color: #171717; }
    .dh-blok-info { font-size: 9.5px; color: #6f6f6f; text-transform: uppercase; letter-spacing: .04em; }
    .dh-catatan { font-size: 9.5px; color: #6f6f6f; margin-top: 2px; }
    .dh-sup { font-size: 10px; color: #6f6f6f; font-style: italic; }
    .dh-kategori-judul { font-size: 11px; font-weight: 600; color: #171717; border-bottom: 1px solid #e5e5e5; padding-bottom: 3px; margin: 16px 0 8px; }

    @media print {
        @page { size: A4 {{ $landscape ? 'landscape' : 'portrait' }}; margin: 8mm; }
        aside, .no-print, .topbar, nav { display: none !important; }
        {{-- padding 0 membuat kop laporan menempel ke tepi kertas; semua
             laporan lain memakai 28px 32px - diseragamkan ke sini juga. --}}
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
        .dh-tabel { font-size: var(--fs-tabel, 9px); }
        .dh-tabel th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        {{-- header tabel berulang antar halaman --}}
        .dh-tabel thead { display: table-header-group; }
        .dh-tabel tr { break-inside: avoid; page-break-inside: avoid; }
    }
    {{-- Mobile: tabel lebar digulir, jangan merusak layout ponsel --}}
    .dh-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
</style>

{{-- Kop usaha DARI AppSetting - memakai partial yang sama dengan semua
     laporan lain supaya senada (sebelumnya duplikat manual dengan ukuran
     huruf beda). $docTitle sesuai nama dokumen. --}}
@include('partials.print-report-header', ['docTitle' => 'Daftar Harga'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-4 print:!py-0 print:!px-0 dh-sheet">

    {{-- Layar (tidak dicetak): header + filter + tombol cetak --}}
    <div class="no-print">
        <x-page-header title="Cetak Daftar Harga" subtitle="Harga jual per golongan konsumen — angka yang sama dibaca mesin kasir.">
            <x-slot:actions>
                <a href="{{ route('setting-harga.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 text-xs font-medium uppercase tracking-wider">Kembali</a>
                <div class="inline-flex rounded-xl overflow-hidden border border-slate-200">
                    <a href="{{ route('setting-harga.cetak', array_merge(request()->query(), ['orientasi' => 'portrait'])) }}" class="px-3 py-3 text-xs font-medium {{ !$landscape ? 'bg-[var(--brand-sidebar-accent,#4F46E5)] text-white' : 'bg-white text-slate-500 hover:bg-slate-50' }}">Portrait</a>
                    <a href="{{ route('setting-harga.cetak', array_merge(request()->query(), ['orientasi' => 'landscape'])) }}" class="px-3 py-3 text-xs font-medium {{ $landscape ? 'bg-[var(--brand-sidebar-accent,#4F46E5)] text-white' : 'bg-white text-slate-500 hover:bg-slate-50' }}">Landscape</a>
                </div>
                <x-button type="button" onclick="window.print()"><i class="fas fa-print"></i> Cetak</x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card class="mb-8">
            <form method="GET" action="{{ route('setting-harga.cetak') }}" class="flex flex-wrap items-end gap-4">
                <div class="w-full md:w-64">
                    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Golongan Konsumen</label>
                    <x-form.select name="golongan">
                        <option value="">Semua Golongan</option>
                        @foreach($golongan as $g)
                            <option value="{{ $g->id_golongan }}" {{ $filterGolongan == $g->id_golongan ? 'selected' : '' }}>{{ $g->nama_golongan }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="w-full md:w-52">
                    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Kategori</label>
                    <x-form.select name="kategori">
                        <option value="semua" {{ $kategori === 'semua' ? 'selected' : '' }}>Semua (produk, jasa, finishing)</option>
                        <option value="produk" {{ $kategori === 'produk' ? 'selected' : '' }}>Produk saja</option>
                        <option value="jasa" {{ $kategori === 'jasa' ? 'selected' : '' }}>Jasa saja</option>
                        <option value="finishing" {{ $kategori === 'finishing' ? 'selected' : '' }}>Finishing saja</option>
                    </x-form.select>
                </div>
                <x-button type="submit"><i class="fas fa-filter"></i> Terapkan</x-button>
            </form>
        </x-card>
    </div>

    @if($bloks->isEmpty())
        <div class="no-print text-center py-16 text-slate-400 text-sm">Tidak ada item dengan harga untuk filter ini.</div>
    @endif

    @foreach(['produk', 'jasa', 'finishing'] as $tipeGrup)
        @php($blokGrup = $bloks->where('tipe', $tipeGrup)->values())
        @if($blokGrup->isNotEmpty())
            <div class="dh-kategori-judul">{{ ['produk' => 'Produk', 'jasa' => 'Jasa', 'finishing' => 'Finishing'][$tipeGrup] }}</div>

            @foreach($blokGrup as $b)
                <div class="dh-blok dh-wrap">
                    <div class="flex items-baseline gap-3 mb-2 flex-wrap">
                        <span class="dh-blok-nama">{{ $b['nama'] }}</span>
                        @if($b['info'])
                            <span class="dh-blok-info">{{ $b['info'] }}</span>
                        @endif
                    </div>

                    @if($b['promo'])
                        <div class="dh-sup mb-1">Harga Promo aktif s.d. {{ \Carbon\Carbon::parse($b['promo']['tglSelesai'])->format('d/m/Y') }} — mulai berlaku kembali harga normal di bawah.</div>
                    @endif

                    {{-- Blok Produk: matriks kolom = golongan --}}
                    @if(!empty($b['baris']))
                        @php($punyaKolomGolongan = (bool) ($b['kolomGolongan'] ?? true))
                        <table class="dh-tabel">
                            <thead>
                                <tr>
                                    <th style="width:120px">{{ $b['kolomGolongan'] ?? false ? 'Rentang Qty (' . $b['unit'] . ')' : 'Rentang Qty' }}</th>
                                    @if($punyaKolomGolongan)
                                        @foreach($golongan as $g)
                                            <th class="num">{{ $g->nama_golongan }}</th>
                                        @endforeach
                                    @else
                                        <th class="num">Semua Golongan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($b['baris'] as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        @if($punyaKolomGolongan)
                                            @foreach($golongan as $g)
                                                @php($sel = $row['harga'][(int) $g->id_golongan] ?? null)
                                                @if($sel === null)
                                                    <td class="num kosong">-</td>
                                                @else
                                                    <td class="num">{{ $rpc($sel['harga']) }}@if($sel['promo'] ?? false) <sup class="dh-sup">promo</sup>@endif</td>
                                                @endif
                                            @endforeach
                                        @else
                                            <td class="num">{{ $rpc($row['hargaFlat']) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    {{-- Harga File Siap Cetak (produk dengan harga tersendiri per golongan, dari harga_siap_cetak) --}}
                    @if(!empty($b['barisSiapCetak']))
                        <div class="dh-catatan mb-1 mt-3 font-medium text-[#3F4F47]">Harga File Siap Cetak</div>
                        <table class="dh-tabel">
                            <thead>
                                <tr>
                                    <th style="width:120px">Rentang Qty ({{ $b['unit'] ?? 'Pcs' }})</th>
                                    @foreach($golongan as $g)
                                        <th class="num">{{ $g->nama_golongan }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($b['barisSiapCetak'] as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        @foreach($golongan as $g)
                                            @php($idGolx = (int) $g->id_golongan)
                                            @php($sel = $row['harga'][$idGolx] ?? null)
                                            @if($sel === null)
                                                <td class="num kosong">-</td>
                                            @else
                                                <td class="num">{{ $rpc($sel['harga']) }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    {{-- Catatan minimum charge di bawah blok --}}
                    @if($b['catatan'])
                        <div class="dh-catatan">{{ $b['catatan'] }}</div>
                    @endif
                </div>
            @endforeach
        @endif
    @endforeach

    <div class="dh-catatan no-print mt-10">Catatan: sel "-" berarti golongan tersebut belum punya harga pada rentang itu. Harga di lembar ini diambil dari penetapan harga yang sama dengan yang dipakai mesin kasir.</div>
    @include('partials.print-report-footer')
</div>
@endsection
