@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
        .nota-block { break-inside: avoid; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Kebocoran Tagihan'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 print:pt-0 max-w-[1600px] mx-auto">

    <div class="print:hidden">
        <x-page-header title="Kebocoran Tagihan">
            <x-slot:subtitle>
                Laporan <span class="mx-1 text-slate-500">/</span> Rekonsiliasi Berkas vs Nota - pekerjaan disebut tapi tidak tertagih
            </x-slot:subtitle>
            <x-slot:actions>
                <x-button type="button" onclick="cetakLaporanPenuh()" variant="secondary" class="no-print">
                    <i class="fas fa-print"></i>
                    Cetak
                </x-button>
            </x-slot:actions>
        </x-page-header>
    </div>

    {{-- FILTER --}}
    <x-card class="mb-6 no-print">
        <form method="GET" action="{{ route('laporan.kebocoran-tagihan') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $dari->format('Y-m-d') }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $sampai->format('Y-m-d') }}" />
            </div>
            <x-button type="submit" variant="primary">
                <i class="fas fa-magnifying-glass mr-1"></i> Pindai
            </x-button>
        </form>
    </x-card>

    {{-- RESUME (layar saja; saat cetak diganti tabel polos di bawah) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6 print:hidden">
        <x-card class="!p-4">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Nota Diperiksa</p>
            <p class="text-2xl font-semibold text-slate-800 mt-1">{{ angka($jumlahNotaDiperiksa) }}</p>
        </x-card>
        <x-card class="!p-4 {{ $jumlahNotaBermasalah ? 'border-amber-300 bg-amber-50/60' : '' }}">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Nota Ada Temuan</p>
            <p class="text-2xl font-semibold {{ $jumlahNotaBermasalah ? 'text-amber-700' : 'text-emerald-600' }} mt-1">{{ angka($jumlahNotaBermasalah) }}</p>
        </x-card>
        <x-card class="!p-4 {{ $jumlahTemuan ? 'border-amber-300 bg-amber-50/60' : '' }}">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Total Temuan</p>
            <p class="text-2xl font-semibold {{ $jumlahTemuan ? 'text-amber-700' : 'text-emerald-600' }} mt-1">{{ angka($jumlahTemuan) }}</p>
        </x-card>
        <x-card class="!p-4 {{ $temuanAi->isNotEmpty() ? 'border-indigo-300 bg-indigo-50/60' : '' }}">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Temuan Audit AI</p>
            <p class="text-2xl font-semibold {{ $temuanAi->isNotEmpty() ? 'text-indigo-700' : 'text-slate-400' }} mt-1">{{ angka($temuanAi->count()) }}</p>
        </x-card>
    </div>

    @php($rentangMaks = $dari->copy()->addDays(93))
    <p class="text-[11px] text-slate-400 mb-4 no-print">Rentang dipindai maksimal 92 hari (batas performa pindai per nota). Temuan = pekerjaan/finishing yang disebut di teks bebas berkas/catatan tetapi tidak ada di item tertagih nota - konfirmasi manual sebelum bertindak.</p>

    @if($grup === [])
    <x-card class="mb-6">
        <div class="py-8 text-center">
            <i class="fas fa-circle-check text-emerald-500 text-3xl mb-3"></i>
            <p class="text-sm font-medium text-slate-700">Tidak ada temuan pada rentang ini</p>
            <p class="text-xs text-slate-400 mt-1">Semua sebutan pekerjaan di teks bebas tertagih di notanya.</p>
        </div>
    </x-card>
    @endif

    @foreach($grup as $g)
    <div class="nota-block mb-4 rounded-xl border border-slate-200 bg-white overflow-hidden print:hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('berkas-order.show', $g['nota']->id_transaksi) }}" class="text-sm font-semibold text-slate-800 hover:text-indigo-600">{{ $g['nota']->nomor_nota }}</a>
                <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($g['nota']->tgl_order)->translatedFormat('d M Y') }}</span>
                <span class="text-[11px] text-slate-500 truncate">{{ $g['nota']->nama_konsumen ?? '-' }}</span>
            </div>
            <span class="text-[11px] font-medium text-amber-700 bg-amber-100 rounded-full px-2.5 py-0.5">{{ count($g['temuan']) }} temuan</span>
        </div>
        <div class="px-4 py-2 divide-y divide-slate-100">
            @foreach($g['temuan'] as $t)
            <div class="py-2 text-sm">
                <b class="text-slate-800">Tertagih hilang: {{ $t['nama_finishing'] }}</b>
                <span class="text-[11px] text-slate-400 ml-2">&ldquo;{{ $t['kutipan'] }}&rdquo;</span>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $t['sumber'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- CETAK: tabel polos yang sama dengan laporan lain (print-table).
         Kartu layar ber-rounded & badge warna disembunyikan saat cetak. --}}
    <div class="hidden print:block">
        <x-laporan.print-table title="Rincian Temuan" :headers="['Nota', 'Tanggal', 'Konsumen', 'Pekerjaan Disebut', 'Sumber']" :align="['left', 'left', 'left', 'left', 'left']">
            @forelse($grup as $g)
                @foreach($g['temuan'] as $t)
                <tr>
                    <td class="px-3 py-1.5">{{ $g['nota']->nomor_nota }}</td>
                    <td class="px-3 py-1.5">{{ \Carbon\Carbon::parse($g['nota']->tgl_order)->format('d/m/Y') }}</td>
                    <td class="px-3 py-1.5">{{ $g['nota']->nama_konsumen ?? '-' }}</td>
                    <td class="px-3 py-1.5">{{ $t['nama_finishing'] }}</td>
                    <td class="px-3 py-1.5">{{ $t['sumber'] }}</td>
                </tr>
                @endforeach
            @empty
                <tr><td class="px-3 py-1.5 text-slate-400" colspan="5">Tidak ada temuan pada rentang ini.</td></tr>
            @endforelse
        </x-laporan.print-table>
    </div>

    @if($temuanAi->isNotEmpty())
    <h3 class="text-sm font-semibold text-indigo-800 uppercase tracking-widest mb-3 mt-8 print:hidden">Hasil Audit AI Tersimpan</h3>
    <x-card class="mb-6 print:hidden">
        <div class="divide-y divide-slate-100">
            @foreach($temuanAi as $t)
            <div class="py-2.5 text-sm {{ (float) $t->keyakinian < 0.6 ? 'text-slate-400' : 'text-slate-700' }}">
                <span class="font-medium">{{ $t->temuan }}</span>
                @if($t->kutipan)<span class="text-[11px] text-slate-400 ml-1">&ldquo;{{ $t->kutipan }}&rdquo;</span>@endif
                <div class="text-[11px] text-slate-400 mt-0.5">
                    <a href="{{ route('berkas-order.show', $t->id_transaksi) }}" class="hover:text-indigo-600">{{ $t->nomor_nota }}</a>
                    &middot; {{ $t->sumber }} &middot; {{ ucfirst(str_replace('_', ' ', $t->kategori)) }}
                    &middot; keyakinian {{ number_format((float) $t->keyakinian * 100, 0) }}% &middot; {{ $t->model }}
                </div>
            </div>
            @endforeach
        </div>
    </x-card>
    @endif

</div>
@include('partials.print-report-footer')
@endsection
