@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header :title="'Kartu Stok: ' . $bahan->nama_bahan">
        <x-slot:subtitle>
            Bahan Baku &amp; Logistik <span class="mx-1 text-slate-300">/</span> Riwayat Pergerakan Stok
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ url('/kartu-stok') }}" variant="secondary">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
        <form method="GET" action="{{ url('/kartu-stok/'.$bahan->id_bahan) }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <input type="date" name="awal" value="{{ $awal }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <input type="date" name="akhir" value="{{ $akhir }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Tipe</label>
                <select name="tipe" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                    <option value="">Semua Tipe</option>
                    <option value="IN" {{ $tipe == 'IN' ? 'selected' : '' }}>IN (Masuk)</option>
                    <option value="OUT" {{ $tipe == 'OUT' ? 'selected' : '' }}>OUT (Keluar)</option>
                    <option value="ADJUSTMENT" {{ $tipe == 'ADJUSTMENT' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <button type="submit" class="h-[46px] px-6 flex items-center justify-center bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
            <a href="{{ url('/kartu-stok/'.$bahan->id_bahan) }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    <div class="grid {{ strtolower($bahan->nama_satuan_stok ?? '') === 'roll' && $bahan->nilai_konversi > 1 ? 'grid-cols-2' : 'grid-cols-1' }} gap-4 mb-6">
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Stok Saat Ini</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-1">{{ number_format($bahan->stok, 2) }} <span class="text-base font-bold text-slate-400">{{ $bahan->nama_satuan_stok ?? $bahan->satuan_stok }}</span></h3>
                </div>
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </x-card>
        @if(strtolower($bahan->nama_satuan_stok ?? '') === 'roll' && $bahan->nilai_konversi > 1 && $bahan->nama_satuan_hitung)
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estimasi Luas Tersisa</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-1">≈ {{ number_format($bahan->stok * $bahan->nilai_konversi, 0, ',', '.') }} <span class="text-base font-bold text-slate-400">{{ $bahan->nama_satuan_hitung }}</span></h3>
                    <p class="text-[10px] text-slate-400 mt-1">Dihitung dari {{ number_format($bahan->stok, 2) }} roll × {{ number_format($bahan->nilai_konversi, 0, ',', '.') }} {{ $bahan->nama_satuan_hitung }}/roll</p>
                </div>
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-ruler-combined"></i>
                </div>
            </div>
        </x-card>
        @endif
    </div>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-center">Tipe</th>
                <th class="px-4 py-5 text-right">Qty</th>
                <th class="px-4 py-5 text-right">Stok Sebelum</th>
                <th class="px-4 py-5 text-right">Stok Sesudah</th>
                <th class="px-4 py-5 text-left">Referensi</th>
                <th class="px-6 py-5 text-left">Keterangan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            <tr class="bg-slate-50/40">
                <td colspan="4" class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Saldo Awal ({{ \Carbon\Carbon::parse($awal)->format('d M Y') }})</td>
                <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">{{ number_format($saldoAwal, 2) }}</td>
                <td colspan="2"></td>
            </tr>
            @forelse($kartu as $k)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y H:i') }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    @if($k->tipe === 'IN')
                        <x-badge variant="success">IN</x-badge>
                    @elseif($k->tipe === 'OUT')
                        <x-badge variant="danger">OUT</x-badge>
                    @else
                        <x-badge variant="warning">ADJ</x-badge>
                    @endif
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[13px] font-bold text-slate-800">{{ number_format($k->qty, 2) }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-slate-500 font-semibold">{{ number_format($k->stok_sebelum, 2) }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-slate-700 font-bold">{{ number_format($k->stok_sesudah, 2) }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ $k->referensi ?? '-' }}</span>
                </td>
                <td class="px-6 py-5">
                    <span class="text-[12px] text-slate-500">{{ $k->keterangan ?? '-' }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <x-empty-state title="Tidak ada transaksi pada rentang filter ini" description="Coba perlebar rentang tanggal atau reset filter tipe." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

</div>
@endsection
