@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Laporan Daftar Piutang'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 print:pt-0 max-w-[1600px] mx-auto">

    <div class="print:hidden">
        <x-page-header title="Laporan Daftar Piutang">
            <x-slot:subtitle>
                Laporan <span class="mx-1 text-slate-500">/</span> Nota Belum Lunas (Sisa Tagihan Riil)
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
        <form method="GET" action="{{ route('laporan.piutang') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $tglDari }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $tglSampai }}" />
            </div>
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Konsumen</label>
                {{-- Konsumen = select2 AJAX (lihat catatan yang sama di laporan/order). --}}
                <select name="id_konsumen" class="select2-konsumen-laporan w-full">
                    <option value="">-- Cari Nama / No. HP --</option>
                    @foreach($konsumenAwal as $k)
                        <option value="{{ $k->id_konsumen }}" selected>{{ $k->nama_konsumen }}{{ $k->telepon ? ' - ' . $k->telepon : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Urutkan</label>
                <x-form.select name="sort_by">
                    <option value="tanggal_terbaru" {{ $sortBy === 'tanggal_terbaru' ? 'selected' : '' }}>Tanggal Terbaru</option>
                    <option value="tanggal_terlama" {{ $sortBy === 'tanggal_terlama' ? 'selected' : '' }}>Tanggal Terlama</option>
                    <option value="piutang_terbesar" {{ $sortBy === 'piutang_terbesar' ? 'selected' : '' }}>Piutang Terbesar</option>
                    <option value="piutang_terkecil" {{ $sortBy === 'piutang_terkecil' ? 'selected' : '' }}>Piutang Terkecil</option>
                    <option value="nama_az" {{ $sortBy === 'nama_az' ? 'selected' : '' }}>Nama Konsumen A-Z</option>
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'id_konsumen', 'sort_by']))
                <a href="{{ route('laporan.piutang') }}" class="text-[11px] font-medium text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME - kartu berwarna di layar (tidak diubah), tabel standar
         polos khusus cetak. --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 print:hidden">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Jumlah Nota</p>
            <h3 class="text-2xl font-medium text-slate-900 mt-1">{{ number_format($jumlahNota, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Total Piutang</p>
            <h3 class="text-2xl font-medium text-rose-500 mt-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="hidden print:block">
        <x-laporan.resume-table title="Resume" :rows="[
            'Jumlah Nota' => number_format($jumlahNota, 0, ',', '.'),
            'Total Piutang' => 'Rp ' . number_format($totalPiutang, 0, ',', '.'),
        ]" />
    </div>

    @if($waBoleh)
    {{-- Editor template Pesan WA (hanya role dengan hak harga). Template
         disimpan ke app_settings.wa_pesan_template dan dipakai tombol
         "Kirim WA Tagihan" di tiap baris tabel. Placeholder: {nama} {nota}
         {tanggal} {total} {status} {sisa} {wa}. Kosong = pakai bawaan. --}}
    <details class="mb-6 no-print rounded-2xl border border-slate-100 bg-white shadow-sm group overflow-hidden">
        <summary class="cursor-pointer select-none flex items-center gap-2.5 px-6 py-4 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors list-none">
            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fab fa-whatsapp"></i></span>
            Template Pesan WA
            <span class="text-[11px] font-normal text-slate-400">— klik untuk ubah</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 ml-auto transition-transform group-open:rotate-180"></i>
        </summary>
        <form method="POST" action="{{ route('laporan.piutang.wa-template') }}" class="px-6 pb-5 pt-4 border-t border-slate-100">
            @csrf
            <textarea name="wa_pesan_template" rows="6" maxlength="2000"
                class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-[12px] font-medium text-slate-700 leading-relaxed focus:ring-2 focus:ring-emerald-500/20 shadow-inner"
                placeholder="Halo {nama}, ini Orion Grafika...">{{ $waTemplate }}</textarea>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[10px] text-slate-400 leading-relaxed">Placeholder: <code class="px-1 bg-slate-100 rounded">{nama}</code> <code class="px-1 bg-slate-100 rounded">{nota}</code> <code class="px-1 bg-slate-100 rounded">{tanggal}</code> <code class="px-1 bg-slate-100 rounded">{total}</code> <code class="px-1 bg-slate-100 rounded">{status}</code> <code class="px-1 bg-slate-100 rounded">{sisa}</code> <code class="px-1 bg-slate-100 rounded">{wa}</code></p>
                <x-button type="submit" variant="primary">
                    <i class="fas fa-save"></i> Simpan Template
                </x-button>
            </div>
        </form>
    </details>
    @endif

    <div class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-3 px-1 print:hidden">Detail Data Piutang</div>

    <div class="print:hidden">
    <x-table class="rtable">
        <thead>
            <tr>
                <th class="px-4 py-5 text-center w-10">#</th>
                <th class="px-6 py-5 text-left">No. Nota</th>
                <th class="px-4 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-right">Total Transaksi</th>
                <th class="px-4 py-5 text-right">Sudah Dibayar</th>
                <th class="px-8 py-5 text-right">Piutang</th>
                @if($waBoleh)<th class="px-4 py-5 text-center w-16 no-print">WA</th>@endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $i => $p)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $data->firstItem() + $i }}</td>
                <td data-label="No. Nota" class="px-6 py-5"><span class="text-[13px] font-medium text-slate-700 uppercase">{{ $p->nomor_nota }}</span></td>
                <td data-label="Tanggal" class="px-4 py-5"><span class="text-[12px] font-medium text-slate-600">{{ \Carbon\Carbon::parse($p->tgl_order)->translatedFormat('d M Y') }}</span></td>
                <td data-label="Konsumen" class="px-4 py-5"><span class="text-[12px] font-medium text-slate-700 uppercase">{{ $p->nama_konsumen }}</span></td>
                <td data-label="Total Transaksi" class="px-4 py-5 text-right"><span class="text-[12px] text-slate-500">Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</span></td>
                <td data-label="Sudah Dibayar" class="px-4 py-5 text-right"><span class="text-[12px] text-emerald-600 font-medium">Rp {{ number_format($p->total_dibayar, 0, ',', '.') }}</span></td>
                <td data-label="Piutang" class="px-8 py-5 text-right"><span class="text-[13px] font-medium text-rose-500">Rp {{ number_format($p->piutang, 0, ',', '.') }}</span></td>
                @if($waBoleh)
                <td data-label="WA" class="px-4 py-5 text-center no-print">
                    @if($p->wa_link)
                        <a href="{{ $p->wa_link }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition-all"
                           title="Kirim WA tagihan ke {{ $p->nama_konsumen }}{{ $p->telepon ? ' (' . $p->telepon . ')' : '' }}"
                           aria-label="Kirim WA tagihan ke {{ $p->nama_konsumen }}">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    @else
                        <span class="text-slate-300" title="Konsumen tidak punya nomor WA valid">&mdash;</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $waBoleh ? 8 : 7 }}">
                    <x-empty-state title="Tidak ada piutang" description="Sesuaikan filter untuk melihat daftar piutang." />
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($data->isNotEmpty())
        <tfoot class="bg-slate-50 border-t border-slate-100">
            <tr>
                <td colspan="6" class="px-6 py-3 text-[11px] font-medium uppercase text-slate-500">Total Piutang {{ request()->boolean('print') ? '(seluruh laporan)' : '(halaman ini)' }}</td>
                <td class="px-8 py-3 text-[13px] font-medium text-rose-600 text-right">Rp {{ number_format($data->sum('piutang'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </x-table>
    </div>

    {{-- Versi cetak: tabel polos sederhana (gaya visual sama dengan
         laporan lain), bukan tabel bawaan layar (.modern-table) yang
         paddingnya lebih lega untuk kebutuhan UI. --}}
    <div class="hidden print:block">
        <x-laporan.print-table title="Detail Data Piutang" :headers="['#', 'No. Nota', 'Tanggal', 'Konsumen', 'Total Transaksi', 'Sudah Dibayar', 'Piutang']" :align="['center', 'left', 'left', 'left', 'right', 'right', 'right']">
            @forelse($data as $i => $p)
            <tr>
                <td class="px-3 py-1.5 text-center text-slate-400">{{ $data->firstItem() + $i }}</td>
                <td class="px-3 py-1.5 font-medium text-slate-700">{{ $p->nomor_nota }}</td>
                <td class="px-3 py-1.5 text-slate-600">{{ \Carbon\Carbon::parse($p->tgl_order)->format('d/m/Y') }}</td>
                <td class="px-3 py-1.5 text-slate-700">{{ $p->nama_konsumen }}</td>
                <td class="px-3 py-1.5 text-right text-slate-600">{{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                <td class="px-3 py-1.5 text-right text-slate-600">{{ number_format($p->total_dibayar, 0, ',', '.') }}</td>
                <td class="px-3 py-1.5 text-right font-medium text-slate-800">{{ number_format($p->piutang, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td class="px-3 py-1.5 text-slate-400 italic" colspan="7">Tidak ada piutang.</td>
            </tr>
            @endforelse
            @if($data->isNotEmpty())
            <tr class="bg-slate-50 border-t border-slate-200">
                <td class="px-3 py-1.5 font-medium text-slate-700 uppercase" colspan="6">Total Piutang {{ request()->boolean('print') ? '(seluruh laporan)' : '(halaman ini)' }}</td>
                <td class="px-3 py-1.5 text-right font-medium text-slate-800">{{ number_format($data->sum('piutang'), 0, ',', '.') }}</td>
            </tr>
            @endif
        </x-laporan.print-table>
    </div>

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Filter konsumen = select2 AJAX (server-side search, limit 20).
        $('.select2-konsumen-laporan').select2({
            placeholder: '-- Cari Nama / No. HP --',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('konsumen.search-ajax') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '', _: Date.now() };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                }
            }
        }).on('change', function () { $(this).closest('form').submit(); });
    });
</script>
@endpush
@include('partials.print-report-footer')
@endsection
