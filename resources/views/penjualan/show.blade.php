@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Detail Transaksi {{ $transaksi->nomor_nota }}" subtitle="Tampilan baca saja • edit tersimpan belum didukung untuk menjaga integritas stok & jurnal akuntansi">
        <x-slot:actions>
            <x-button href="{{ route('penjualan.riwayat') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <x-card>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Konsumen</p>
            <p class="text-lg font-bold text-slate-800">{{ $transaksi->nama_konsumen }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ $transaksi->telepon ?? '-' }}</p>
            <p class="text-xs text-indigo-500 font-bold uppercase mt-2">{{ $transaksi->nama_golongan ?? '-' }}</p>
        </x-card>

        <x-card>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Info Order</p>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold {{ $transaksi->tipe_order === 'PO' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-50 text-slate-600 border border-slate-100' }}">
                    {{ $transaksi->tipe_order }}
                </span>
                @if($transaksi->no_po)
                    <span class="text-xs text-slate-500">PO: {{ $transaksi->no_po }}</span>
                @endif
            </div>
            <p class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($transaksi->tgl_order)->format('d M Y, H:i') }}</p>
            <p class="text-xs text-slate-400 mt-1">Kasir: {{ $transaksi->nama_kasir ?? '-' }}</p>
        </x-card>

        <x-card>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Status & Total</p>
            <x-badge :status="$transaksi->status_pembayaran" />
            <p class="text-2xl font-black text-indigo-600 mt-2">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</p>
        </x-card>
    </div>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left w-12">No</th>
                <th class="px-6 py-5 text-left w-16">Gambar</th>
                <th class="px-6 py-5 text-left">Nama File / Item</th>
                <th class="px-6 py-5 text-left">Info Ukuran</th>
                <th class="px-4 py-5 text-center">Qty</th>
                <th class="px-4 py-5 text-center">Urgensi</th>
                <th class="px-6 py-5 text-right">Biaya Cetak</th>
                <th class="px-6 py-5 text-right">Biaya Finishing</th>
                <th class="px-6 py-5 text-right">Subtotal</th>
                <th class="px-6 py-5 text-center">Status SPK</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($items as $i => $item)
            <tr class="hover:bg-slate-50/30 transition-all">
                <td class="px-6 py-4 text-xs text-slate-400">{{ $i + 1 }}</td>
                <td class="px-6 py-4">
                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" class="w-10 h-10 rounded-lg object-cover border border-slate-100">
                    @else
                        <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300"><i class="far fa-image"></i></div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm font-bold text-slate-700">{{ $item->nama_file }}</p>
                    <p class="text-xs text-slate-400">{{ $item->nama_produk ?? ($item->nama_jasa ? '🔧 ' . $item->nama_jasa : '-') }}</p>
                    @if($item->nama_finishing)
                        <p class="text-[11px] text-indigo-500 font-bold mt-1">✨ {{ $item->nama_finishing }} ({{ $item->status_finishing ?? 'Standar' }})</p>
                    @endif
                </td>
                <td class="px-6 py-4 text-xs text-slate-500">
                    @if($item->p_ukuran > 0 || $item->l_ukuran > 0)
                        {{ number_format($item->p_ukuran, 0) }} x {{ number_format($item->l_ukuran, 0) }} cm
                    @else
                        -
                    @endif
                </td>
                <td class="px-4 py-4 text-center text-sm font-bold text-slate-700">{{ $item->qty }}</td>
                <td class="px-4 py-4 text-center">
                    <x-badge :status="$item->status_urgensi ?? 'Normal'" :variant="($item->status_urgensi ?? 'Normal') === 'Very Urgent' ? 'danger' : (($item->status_urgensi ?? 'Normal') === 'Urgent' ? 'warning' : 'info')" />
                </td>
                <td class="px-6 py-4 text-right text-sm text-slate-600">{{ number_format($item->biaya_cetak ?? 0, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-right text-sm text-slate-600">{{ number_format($item->biaya_finishing ?? 0, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-center">
                    <x-badge :status="$item->status_spk" />
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-slate-50/70 border-t-2 border-slate-100">
                <td colspan="8" class="px-6 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-widest">Total</td>
                <td class="px-6 py-4 text-right text-sm font-black text-slate-800">{{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </x-table>
</div>
@endsection
