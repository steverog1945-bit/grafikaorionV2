@extends('layouts.app')

@section('content')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
    }
</style>

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Laporan Daftar Piutang">
        <x-slot:subtitle>
            Laporan <span class="mx-1 text-slate-300">/</span> Nota Belum Lunas (Sisa Tagihan Riil)
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button type="button" onclick="window.print()" variant="secondary" class="no-print">
                <i class="fas fa-print"></i>
                Cetak
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER --}}
    <x-card class="mb-6 no-print">
        <form method="GET" action="{{ route('laporan.piutang') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $tglDari }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $tglSampai }}" />
            </div>
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Konsumen</label>
                <select name="id_konsumen" class="select2-konsumen-laporan w-full">
                    <option value="">-- Cari Nama / No. HP --</option>
                    @foreach($konsumenList as $k)
                        <option value="{{ $k->id_konsumen }}" {{ (string) request('id_konsumen') === (string) $k->id_konsumen ? 'selected' : '' }}>{{ $k->nama_konsumen }}{{ $k->telepon ? ' - ' . $k->telepon : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Urutkan</label>
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
                <a href="{{ route('laporan.piutang') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME - tabel standar, sama persis di layar maupun cetak. --}}
    <x-laporan.resume-table title="Resume" :rows="[
        'Jumlah Nota' => number_format($jumlahNota, 0, ',', '.'),
        'Total Piutang' => 'Rp ' . number_format($totalPiutang, 0, ',', '.'),
    ]" />

    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-1">Detail Data Piutang</div>

    <x-table class="rtable">
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">No. Nota</th>
                <th class="px-4 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-right">Total Transaksi</th>
                <th class="px-4 py-5 text-right">Sudah Dibayar</th>
                <th class="px-8 py-5 text-right">Piutang</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $p)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td data-label="No. Nota" class="px-6 py-5"><span class="text-[13px] font-bold text-slate-700 uppercase">{{ $p->nomor_nota }}</span></td>
                <td data-label="Tanggal" class="px-4 py-5"><span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($p->tgl_order)->format('d M Y') }}</span></td>
                <td data-label="Konsumen" class="px-4 py-5"><span class="text-[12px] font-bold text-slate-700 uppercase">{{ $p->nama_konsumen }}</span></td>
                <td data-label="Total Transaksi" class="px-4 py-5 text-right"><span class="text-[12px] text-slate-500">Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</span></td>
                <td data-label="Sudah Dibayar" class="px-4 py-5 text-right"><span class="text-[12px] text-emerald-600 font-semibold">Rp {{ number_format($p->total_dibayar, 0, ',', '.') }}</span></td>
                <td data-label="Piutang" class="px-8 py-5 text-right"><span class="text-[13px] font-bold text-rose-500">Rp {{ number_format($p->piutang, 0, ',', '.') }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <x-empty-state title="Tidak ada piutang" description="Sesuaikan filter untuk melihat daftar piutang." />
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($data->isNotEmpty())
        <tfoot class="bg-slate-50 border-t border-slate-100">
            <tr>
                <td colspan="5" class="px-6 py-3 text-[11px] font-black uppercase text-slate-500">Total Piutang (halaman ini)</td>
                <td class="px-8 py-3 text-[13px] font-black text-rose-600 text-right">Rp {{ number_format($data->sum('piutang'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </x-table>

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('.select2-konsumen-laporan').select2({ placeholder: '-- Cari Nama / No. HP --', allowClear: true, width: '100%' });
    });
</script>
@endpush
@endsection
