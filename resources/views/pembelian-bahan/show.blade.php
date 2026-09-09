@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-[3rem] shadow-xl overflow-hidden border border-slate-100">
        {{-- Header Detail --}}
        <div class="p-10 bg-slate-900 text-white flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-1">Faktur Pembelian</p>
                <h1 class="text-3xl font-black tracking-tighter">{{ $pembelian->no_faktur }}</h1>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-1">Tanggal</p>
                <p class="font-bold">{{ \Carbon\Carbon::parse($pembelian->tgl_transaksi)->format('d F Y') }}</p>
            </div>
        </div>

        {{-- Info Supplier & SJ --}}
        <div class="grid grid-cols-2 gap-8 p-10 border-b border-slate-50">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Supplier</label>
                <p class="text-lg font-black text-slate-800">{{ $pembelian->kedatangan->supplier->nama_supplier ?? 'Umum' }}</p>
            </div>
            <div class="text-right">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Reff. Surat Jalan</label>
                <p class="text-lg font-black text-indigo-600">{{ $pembelian->kedatangan->no_surat_jalan }}</p>
            </div>
        </div>

        {{-- Tabel Item --}}
        <div class="p-0">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-10 py-4 text-left">Bahan</th>
                        <th class="px-4 py-4 text-center">Qty</th>
                        <th class="px-4 py-4 text-right">Harga</th>
                        <th class="px-10 py-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($pembelian->detail as $item)
                    <tr>
                        <td class="px-10 py-6">
                            <p class="font-black text-slate-700 uppercase text-xs">{{ $item->bahan->nama_bahan }}</p>
                            <p class="text-[10px] text-slate-400 font-bold tracking-widest">{{ $item->bahan->kode_bahan }}</p>
                        </td>
                        <td class="px-4 py-6 text-center font-bold text-slate-600 text-sm">
                            {{ number_format($item->qty, 2) }}
                        </td>
                        <td class="px-4 py-6 text-right font-bold text-slate-600 text-sm">
                            {{ number_format($item->harga, 0, ',', '.') }}
                        </td>
                        <td class="px-10 py-6 text-right font-black text-slate-800 text-sm">
                            {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-indigo-600 text-white">
                        <td colspan="3" class="px-10 py-6 text-right text-[10px] font-black uppercase tracking-[0.2em]">Grand Total Faktur</td>
                        <td class="px-10 py-6 text-right font-black text-xl">Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-8 flex justify-between items-center">
        <a href="{{ route('pembelian-bahan.index') }}" class="text-xs font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke List
        </a>
        <button onclick="window.print()" class="px-8 py-4 bg-white border border-slate-200 rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-all">
            <i class="fas fa-print mr-2 text-indigo-500"></i> Cetak Faktur
        </button>
    </div>
</div>
@endsection