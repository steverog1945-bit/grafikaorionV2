@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-5xl mx-auto">

    <x-page-header :title="$order->order_number" subtitle="Detail order jersey">
        <x-slot:actions>
            <x-button href="{{ route('jersey.orders.print', $order->id) }}" variant="secondary" target="_blank">
                <i class="fas fa-print"></i> Cetak
            </x-button>
            <x-button href="{{ route('jersey.orders.index') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </x-button>
            <form action="{{ route('jersey.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Hapus order jersey ini? Tindakan tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <x-button type="submit" variant="danger"><i class="far fa-trash-alt"></i> Hapus</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-6 font-semibold">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-card class="md:col-span-2">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Informasi Order</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Pelanggan</p><p class="font-semibold">{{ $order->customer_name }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">No. HP</p><p class="font-semibold">{{ $order->customer_phone ?? '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Tim</p><p class="font-semibold">{{ $order->team_name ?? '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Tanggal Order</p><p class="font-semibold">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Deadline</p><p class="font-semibold">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Tipe / Kategori</p><p class="font-semibold">{{ $order->productType->name ?? '-' }} / {{ $order->category->name ?? '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Desainer</p><p class="font-semibold">{{ $order->designer_name ?? '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Mode</p><p class="font-semibold">{{ ['player_list' => 'Daftar Pemain', 'quantity_only' => 'Qty per Ukuran', 'mixed' => 'Campuran'][$order->order_type] ?? $order->order_type }}</p></div>
            </div>
            @if($order->notes)
                <div class="mt-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-500">{{ $order->notes }}</div>
            @endif
        </x-card>

        <x-card>
            <h3 class="text-sm font-bold text-slate-700 mb-4">Ringkasan Biaya</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Diskon</span><span>Rp {{ number_format($order->discount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Biaya Tambahan</span><span>Rp {{ number_format($order->additional_charge, 0, ',', '.') }}</span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-base"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Deposit/DP</span><span>Rp {{ number_format($order->deposit, 0, ',', '.') }}</span></div>
            </div>
        </x-card>
    </div>

    @if($order->items->isNotEmpty())
    <x-card class="mb-6">
        <h3 class="text-sm font-bold text-slate-700 mb-4">Daftar Pemain ({{ $order->items->count() }})</h3>
        <x-table bare>
            <thead><tr><th class="p-2 text-left">Nama</th><th class="p-2 text-left">No.</th><th class="p-2 text-left">Ukuran</th><th class="p-2 text-left">Gender</th><th class="p-2 text-left">Lengan</th><th class="p-2 text-left">Kerah</th></tr></thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($order->items as $item)
                <tr>
                    <td class="p-2 font-semibold">{{ $item->player_name }}</td>
                    <td class="p-2">{{ $item->player_number ?? '-' }}</td>
                    <td class="p-2">{{ $item->size }}</td>
                    <td class="p-2">{{ $item->gender }}</td>
                    <td class="p-2">{{ $item->sleeve }}</td>
                    <td class="p-2">{{ $item->collar ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
    </x-card>
    @endif

    @if($order->sizeQuantities->isNotEmpty())
    <x-card class="mb-6">
        <h3 class="text-sm font-bold text-slate-700 mb-4">Qty per Ukuran</h3>
        <div class="flex flex-wrap gap-3">
            @foreach($order->sizeQuantities as $sq)
                <div class="px-4 py-2 bg-slate-50 rounded-xl text-xs font-bold">{{ $sq->size->name ?? '-' }}: {{ $sq->quantity }} pcs</div>
            @endforeach
        </div>
    </x-card>
    @endif

    <x-card>
        <h3 class="text-sm font-bold text-slate-700 mb-4">Update Status & Pembayaran</h3>
        <form action="{{ route('jersey.orders.update', $order->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf @method('PUT')
            <x-form.group label="Status Order">
                <x-form.select name="status">
                    @foreach(['baru' => 'Baru', 'proses' => 'Proses', 'selesai' => 'Selesai', 'diambil' => 'Diambil'] as $val => $label)
                        <option value="{{ $val }}" @selected($order->status === $val)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>
            <x-form.group label="Status Pembayaran">
                <x-form.select name="status_pembayaran">
                    @foreach(['Belum Bayar', 'DP', 'Lunas'] as $val)
                        <option value="{{ $val }}" @selected($order->status_pembayaran === $val)>{{ $val }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>
            <x-form.group label="Ref. No. Transaksi Kasir" help="Diisi manual setelah dibayar di Kasir - bukan link otomatis.">
                <x-form.input type="text" name="catatan_referensi_transaksi" value="{{ $order->catatan_referensi_transaksi }}" placeholder="mis. INV-20260912-003" />
            </x-form.group>
            <x-form.group label="Deadline">
                <x-form.input type="date" name="deadline" value="{{ $order->deadline?->format('Y-m-d') }}" />
            </x-form.group>
            <div class="md:col-span-4">
                <x-form.group label="Catatan">
                    <textarea name="notes" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ $order->notes }}</textarea>
                </x-form.group>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <x-button type="submit">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
