@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-6xl mx-auto">

    <x-page-header :title="$order->order_number" subtitle="Detail order jersey">
        <x-slot:actions>
            <div class="relative">
                <x-button type="button" variant="secondary" onclick="togglePrintMenuRingkasan(event)">
                    <i class="fas fa-print"></i> Cetak Ringkasan
                </x-button>
                <div id="print-menu-ringkasan" class="hidden absolute right-0 mt-2 w-52 bg-white border border-slate-100 shadow-xl rounded-xl z-50 overflow-hidden">
                    <a href="{{ route('jersey.orders.print', [$order->id, 'thermal']) }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-[10px] font-bold text-slate-600 hover:bg-slate-50 border-b border-slate-50">
                        <i class="fas fa-receipt text-indigo-400"></i> THERMAL (58MM)
                    </a>
                    <a href="{{ route('jersey.orders.print', [$order->id, 'normal']) }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-[10px] font-bold text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-file-invoice text-indigo-400"></i> A4 / F4
                    </a>
                </div>
            </div>
            <x-button href="{{ route('jersey.orders.index') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </x-button>
            <form id="delete-form-jersey-order" action="{{ route('jersey.orders.destroy', $order->id) }}" method="POST">
                @csrf @method('DELETE')
                <x-button type="button" variant="danger" onclick="confirmHapusOrderJersey()"><i class="far fa-trash-alt"></i> Hapus</x-button>
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
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Pelanggan</p><p class="font-semibold">{{ $order->konsumen->nama_konsumen ?? $order->customer_name }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">No. HP</p><p class="font-semibold">{{ $order->customer_phone ?? '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Tim</p><p class="font-semibold">{{ $order->team_name ?? '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Tanggal Order</p><p class="font-semibold">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Deadline</p><p class="font-semibold">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</p></div>
                <div><p class="text-[10px] text-slate-400 uppercase font-bold">Desainer</p><p class="font-semibold">{{ $order->designer_name ?? '-' }}</p></div>
            </div>
            @if($order->notes)
                <div class="mt-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-500">{{ $order->notes }}</div>
            @endif
        </x-card>

        <x-card>
            <h3 class="text-sm font-bold text-slate-700 mb-4">Ringkasan Biaya</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Subtotal Barang</span><span>Rp {{ number_format($order->lines->sum('subtotal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Diskon</span><span>Rp {{ number_format($order->discount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Biaya Tambahan</span><span>Rp {{ number_format($order->additional_charge, 0, ',', '.') }}</span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-base"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Deposit/DP</span><span>Rp {{ number_format($order->deposit, 0, ',', '.') }}</span></div>
            </div>
        </x-card>
    </div>

    <div class="space-y-6 mb-6">
        @foreach($order->lines as $line)
        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-4 gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                        @if($line->thumbnail)
                            <img src="{{ asset('storage/' . $line->thumbnail) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-image text-slate-300"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-700">{{ $line->nama_barang }}</h3>
                        <p class="text-[11px] text-slate-400">{{ $line->productType->name ?? '-' }} &middot; {{ $line->category->name ?? '-' }} &middot; {{ $line->bahan ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $line->qty }} pcs &times; Rp {{ number_format($line->harga_satuan, 0, ',', '.') }}
                            @if($line->size_surcharge_total > 0)
                                + Rp {{ number_format($line->size_surcharge_total, 0, ',', '.') }} <span class="text-amber-500 font-semibold">(tambahan ukuran)</span>
                            @endif
                            = <span class="font-bold text-slate-600">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</span>
                        </p>
                    </div>
                </div>
                <x-button href="{{ route('jersey.orders.lines.spk', [$order->id, $line->id]) }}" size="sm" variant="secondary" target="_blank">
                    <i class="fas fa-print"></i> SPK
                </x-button>
            </div>

            @if($line->keterangan)
                <div class="mb-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-500">{{ $line->keterangan }}</div>
            @endif

            @if($line->items->isNotEmpty())
            <div class="mb-4 overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-[10px] text-slate-400 uppercase"><th class="p-2 text-left">Nama</th><th class="p-2 text-left">No.</th><th class="p-2 text-left">Ukuran</th><th class="p-2 text-left">Gender</th><th class="p-2 text-left">Lengan</th><th class="p-2 text-left">Kerah</th></tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($line->items as $item)
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
                </table>
            </div>
            @endif

            @if($line->sizeQuantities->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($line->sizeQuantities as $sq)
                    <div class="px-3 py-1.5 bg-slate-50 rounded-lg text-[11px] font-bold text-slate-600">{{ $sq->size->name ?? '-' }}: {{ $sq->quantity }} pcs</div>
                @endforeach
            </div>
            @endif
        </x-card>
        @endforeach
    </div>

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

@push('scripts')
<script>
    function togglePrintMenuRingkasan(event) {
        event.stopPropagation();
        const menu = document.getElementById('print-menu-ringkasan');
        menu.classList.toggle('hidden');
    }
    document.addEventListener('click', () => document.getElementById('print-menu-ringkasan')?.classList.add('hidden'));

    function confirmHapusOrderJersey() {
        Swal.fire({
            title: 'Hapus Order Jersey?',
            html: 'Order <b>{{ $order->order_number }}</b> beserta seluruh barang & rinciannya akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-jersey-order').submit();
        });
    }
</script>
@endpush
@endsection
