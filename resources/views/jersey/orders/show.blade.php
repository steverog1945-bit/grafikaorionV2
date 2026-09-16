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
            <x-button type="button" variant="success" onclick="openModal('tambah-susulan')">
                <i class="fas fa-plus"></i> Tambah Barang Susulan
            </x-button>
            <x-button href="{{ route('jersey.orders.edit-lines', $order->id) }}" variant="secondary">
                <i class="fas fa-boxes-stacked"></i> Kelola Barang
            </x-button>
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
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-700">Ringkasan Biaya</h3>
                <x-badge :status="$order->status_pembayaran" />
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Subtotal Barang</span><span>Rp {{ number_format($order->lines->sum('subtotal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Diskon</span><span>Rp {{ number_format($order->discount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Biaya Tambahan</span><span>Rp {{ number_format($order->additional_charge, 0, ',', '.') }}</span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-base"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-emerald-600"><span class="text-slate-400">Sudah Dibayar</span><span class="font-semibold">Rp {{ number_format($order->deposit, 0, ',', '.') }}</span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-base text-rose-600"><span>Sisa Tagihan</span><span>Rp {{ number_format($order->sisa_tagihan, 0, ',', '.') }}</span></div>
            </div>
            @if($order->sisa_tagihan > 0)
                <x-button type="button" variant="success" class="w-full mt-4" onclick="openModal('tambah-pembayaran')">
                    <i class="fas fa-hand-holding-usd"></i> Catat Pembayaran
                </x-button>
            @endif
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
                        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            {{ $line->nama_barang }}
                            @if($line->is_susulan)
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest">Susulan</span>
                            @endif
                        </h3>
                        <p class="text-[11px] text-slate-400">{{ $line->productType->name ?? '-' }} &middot; {{ $line->category->name ?? '-' }} &middot; {{ $line->bahan ?? '-' }} @if($line->sleeveType) &middot; {{ $line->sleeveType->name }} @endif @if($line->collarType) &middot; {{ $line->collarType->name }} @endif</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $line->qty }} pcs &times; Rp {{ number_format($line->harga_satuan, 0, ',', '.') }}
                            @if($line->size_surcharge_total > 0)
                                + Rp {{ number_format($line->size_surcharge_total, 0, ',', '.') }} <span class="text-amber-500 font-semibold">(tambahan ukuran)</span>
                            @endif
                            @if($line->sleeve_collar_surcharge_total > 0)
                                + Rp {{ number_format($line->sleeve_collar_surcharge_total, 0, ',', '.') }} <span class="text-amber-500 font-semibold">(tambahan lengan/kerah)</span>
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

    <x-card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-700">Riwayat Pembayaran</h3>
            @if($order->sisa_tagihan > 0)
                <x-button type="button" size="sm" variant="success" onclick="openModal('tambah-pembayaran')">
                    <i class="fas fa-plus"></i> Tambah Pembayaran
                </x-button>
            @endif
        </div>
        @if($order->payments->isEmpty())
            <x-empty-state title="Belum ada pembayaran" description="Catat DP atau pelunasan lewat tombol Tambah Pembayaran / Catat Pembayaran." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-[10px] text-slate-400 uppercase"><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Metode</th><th class="p-2 text-right">Jumlah</th><th class="p-2 text-left">Keterangan</th><th class="p-2 text-center">Bukti</th><th class="p-2 text-center w-12">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($order->payments->sortByDesc('tanggal_bayar') as $p)
                        <tr>
                            <td class="p-2 font-semibold text-slate-600">{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                            <td class="p-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-50 text-slate-500 text-[10px] font-bold uppercase">{{ $p->metode_bayar }}</span>
                            </td>
                            <td class="p-2 text-right font-bold text-emerald-600">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                            <td class="p-2 text-slate-400">{{ $p->keterangan ?? '-' }}</td>
                            <td class="p-2 text-center">
                                @if($p->bukti_path)
                                    <a href="{{ asset('storage/' . $p->bukti_path) }}" target="_blank" class="text-indigo-500 hover:text-indigo-700"><i class="fas fa-image"></i></a>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="p-2 text-center">
                                <form id="delete-payment-form-{{ $p->id }}" action="{{ route('jersey.keuangan.bayar.destroy', $p->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmHapusPembayaran({{ $p->id }})" class="text-slate-300 hover:text-rose-500" title="Hapus"><i class="far fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <x-card>
        <h3 class="text-sm font-bold text-slate-700 mb-4">Update Data Order</h3>
        <form action="{{ route('jersey.orders.update', $order->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf @method('PUT')
            <x-form.group label="Status Order">
                <x-form.select name="status">
                    @foreach(['baru' => 'Baru', 'proses' => 'Proses', 'selesai' => 'Selesai', 'diambil' => 'Diambil'] as $val => $label)
                        <option value="{{ $val }}" @selected($order->status === $val)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>
            <x-form.group label="Deadline">
                <x-form.input type="date" name="deadline" value="{{ $order->deadline?->format('Y-m-d') }}" />
            </x-form.group>
            <x-form.group label="Nama Tim (opsional)">
                <x-form.input type="text" name="team_name" value="{{ $order->team_name }}" />
            </x-form.group>
            <x-form.group label="Desainer">
                <x-form.select name="jersey_designer_id">
                    <option value="">-- Pilih --</option>
                    @foreach($designers as $d)<option value="{{ $d->id }}" @selected($order->jersey_designer_id === $d->id)>{{ $d->name }}</option>@endforeach
                </x-form.select>
            </x-form.group>
            <x-form.group label="Diskon (Rp)">
                <input type="text" inputmode="numeric" name="discount" value="{{ number_format($order->discount, 0, ',', '.') }}" class="format-ribuan w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </x-form.group>
            <x-form.group label="Biaya Tambahan (Rp)">
                <input type="text" inputmode="numeric" name="additional_charge" value="{{ number_format($order->additional_charge, 0, ',', '.') }}" class="format-ribuan w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </x-form.group>
            <div class="md:col-span-3">
                <x-form.group label="Catatan">
                    <textarea name="notes" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ $order->notes }}</textarea>
                </x-form.group>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <x-button type="submit">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>

    <x-modal id="tambah-susulan" title="Tambah Barang Susulan" maxWidth="4xl">
        <p class="text-xs text-slate-400 mb-4 -mt-2">Barang lama pada order ini <b class="text-slate-500">tidak akan berubah/terhapus</b> - cocok untuk kasus qty/jenis barang bertambah setelah order awal berjalan (mis. panitia bertambah, butuh jersey tambahan).</p>
        <form id="form-tambah-susulan" action="{{ route('jersey.orders.lines.susulan', $order->id) }}" method="POST">
            @csrf
            <div class="jersey-line-card bg-slate-50/60 rounded-2xl border border-slate-200 overflow-hidden mb-5">
                @include('jersey.orders._line-card-fields', ['idx' => 0, 'line' => null, 'productTypes' => $productTypes, 'categories' => $categories, 'materials' => $materials, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('tambah-susulan')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-[2] py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase">Simpan Barang Susulan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="tambah-pembayaran" title="Catat Pembayaran" maxWidth="md">
        <form id="form-tambah-pembayaran" action="{{ route('jersey.keuangan.bayar.store', $order->id) }}" method="POST">
            @csrf
            <div class="mb-4 p-3 bg-rose-50 rounded-xl text-xs text-rose-600 font-semibold">
                Sisa tagihan order ini: Rp {{ number_format($order->sisa_tagihan, 0, ',', '.') }}
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <x-form.group label="Tanggal Bayar">
                    <x-form.input type="date" name="tanggal_bayar" value="{{ now()->format('Y-m-d') }}" required />
                </x-form.group>
                <x-form.group label="Jumlah (Rp)">
                    <input type="text" inputmode="numeric" name="jumlah_display" id="pembayaran-jumlah-display" required
                        class="format-ribuan w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                        placeholder="0">
                    <input type="hidden" name="jumlah" id="pembayaran-jumlah">
                </x-form.group>
            </div>
            <x-form.group label="Metode Bayar" class="mb-4">
                <x-form.select name="metode_bayar">
                    @foreach(['Tunai', 'Transfer', 'QRIS', 'Lainnya'] as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bukti Bayar (opsional)</label>
                <div class="bukti-dropzone relative border-2 border-dashed border-slate-200 rounded-xl p-4 text-center cursor-pointer hover:border-indigo-300" tabindex="0" onclick="document.getElementById('bukti-file-input').click()">
                    <img class="bukti-preview hidden mx-auto max-h-32 rounded-lg mb-2">
                    <p class="bukti-placeholder text-[11px] text-slate-400"><i class="fas fa-receipt mb-1 block text-lg text-slate-300"></i>Klik, Tempel (Ctrl+V), atau Pilih File</p>
                    <input type="file" id="bukti-file-input" class="hidden" accept="image/*">
                </div>
                <input type="hidden" name="bukti_path" id="bukti-path">
                <p class="bukti-status text-[10px] mt-1"></p>
            </div>
            <x-form.group label="Keterangan (opsional)" class="mb-4">
                <input type="text" name="keterangan" placeholder="mis. Cicilan ke-2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </x-form.group>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('tambah-pembayaran')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase">Simpan Pembayaran</button>
            </div>
        </form>
    </x-modal>
</div>

{{-- Template baris pemain kosong - dipakai tombol "+ Tambah Pemain" di
     dalam modal Tambah Barang Susulan (pola sama seperti create.blade.php). --}}
<template id="template_baris_pemain_jersey">
    @include('jersey.orders._player-row-fields', ['prefix' => '__PREFIX__', 'itemIdx' => '__ITEM__', 'item' => null, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
</template>

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

    function confirmHapusPembayaran(id) {
        Swal.fire({
            title: 'Hapus Baris Pembayaran?',
            html: 'Status pembayaran order akan dihitung ulang setelah baris ini dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
        }).then((result) => {
            if (result.isConfirmed) document.getElementById(`delete-payment-form-${id}`).submit();
        });
    }

    // ---- Modal Tambah Pembayaran: bukti bayar via klik-pilih-file ATAU
    // tempel (Ctrl+V) - pola sama persis dengan upload thumbnail di form
    // Order Jersey Baru. ----
    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.querySelector('.bukti-dropzone');
        if (!dropzone) return;
        dropzone.addEventListener('paste', (e) => {
            const items = (e.clipboardData || window.clipboardData).items;
            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    prosesUploadBukti(item.getAsFile());
                    e.preventDefault();
                    return;
                }
            }
        });
        document.getElementById('bukti-file-input').addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) prosesUploadBukti(e.target.files[0]);
        });
    });

    async function prosesUploadBukti(file) {
        const statusEl = document.querySelector('.bukti-status');
        statusEl.textContent = 'Mengunggah...';
        statusEl.classList.remove('text-rose-500');
        const formData = new FormData();
        formData.append('bukti', file);
        formData.append('_token', '{{ csrf_token() }}');
        try {
            const res = await fetch('{{ route("jersey.keuangan.upload-bukti") }}', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('bukti-path').value = data.path;
                const img = document.querySelector('.bukti-preview');
                img.src = data.url;
                img.classList.remove('hidden');
                document.querySelector('.bukti-placeholder').classList.add('hidden');
                statusEl.textContent = 'Bukti siap. Klik kotak untuk mengganti.';
            } else {
                statusEl.textContent = 'Gagal mengunggah, coba lagi.';
                statusEl.classList.add('text-rose-500');
            }
        } catch (e) {
            statusEl.textContent = 'Gagal mengunggah, coba lagi.';
            statusEl.classList.add('text-rose-500');
        }
    }

    document.getElementById('form-tambah-pembayaran').addEventListener('submit', (e) => {
        document.getElementById('pembayaran-jumlah').value = cleanRupiah(document.getElementById('pembayaran-jumlah-display').value);
    });

    // ---- Modal Tambah Barang Susulan - dukungan mode order (toggle),
    // daftar pemain, & upload thumbnail sama persis dengan Order Jersey
    // Baru / Kelola Barang (satu kartu barang saja, idx tetap 0). ----
    function toggleLineType(radio) {
        const card = radio.closest('.jersey-line-card');
        const tipe = radio.value;
        card.querySelector('.wrapper-qty-ukuran').classList.toggle('hidden', tipe === 'player_list');
        card.querySelector('.wrapper-daftar-pemain').classList.toggle('hidden', tipe === 'quantity_only');
        if (tipe !== 'quantity_only' && card.querySelectorAll('.tbody-pemain .baris-pemain').length === 0) {
            tambahBarisPemain(card.querySelector('.wrapper-daftar-pemain button'));
        }
        card.querySelectorAll('.line-type-option').forEach(label => {
            const checked = label.querySelector('input').checked;
            label.classList.toggle('border-indigo-500', checked);
            label.classList.toggle('bg-indigo-50', checked);
            label.classList.toggle('text-indigo-700', checked);
            label.classList.toggle('border-slate-100', !checked);
            label.classList.toggle('text-slate-400', !checked);
        });
        perbaruiVisibilitasLenganKerah(card);
    }

    function tambahBarisPemain(btn) {
        const card = btn.closest('.jersey-line-card');
        const tbody = card.querySelector('.tbody-pemain');
        const itemIdx = tbody.querySelectorAll('.baris-pemain').length;
        const html = document.getElementById('template_baris_pemain_jersey').innerHTML
            .replaceAll('__PREFIX__', 'lines[0]')
            .replaceAll('__ITEM__', itemIdx);
        tbody.insertAdjacentHTML('beforeend', html);
        perbaruiVisibilitasLenganKerah(card);
    }

    // Lengan/Kerah default (level barang) tidak relevan untuk mode Daftar
    // Pemain (selalu pakai pilihan tiap pemain), dan kolom Lengan/Kerah di
    // tabel Daftar Pemain tidak relevan kalau Tipe Produk-nya memang tidak
    // punya lengan/kerah (mis. Celana) - dikelola lewat checkbox "Punya
    // Lengan & Kerah" di Master Data Tipe Produk, bukan hardcode nama.
    function perbaruiVisibilitasLenganKerah(source) {
        const card = source.closest ? source.closest('.jersey-line-card') : source;
        if (!card) return;
        const ptSelect = card.querySelector('select[name$="[jersey_product_type_id]"]');
        const opsiTerpilih = ptSelect ? ptSelect.options[ptSelect.selectedIndex] : null;
        const punyaLenganKerah = !opsiTerpilih || opsiTerpilih.value === '' || opsiTerpilih.dataset.hasSleeveCollar !== '0';
        const orderType = card.querySelector('input[name$="[order_type]"]:checked')?.value || 'quantity_only';
        const tampilkanDefault = punyaLenganKerah && orderType !== 'player_list';
        card.querySelectorAll('.wrapper-lengan-kerah-default').forEach(el => el.classList.toggle('hidden', !tampilkanDefault));
        card.querySelectorAll('.col-lengan-kerah').forEach(el => el.classList.toggle('hidden', !punyaLenganKerah));
    }

    function pasangPasteThumbnail(card) {
        const dropzone = card.querySelector('.thumbnail-dropzone');
        dropzone.addEventListener('paste', (e) => {
            const items = (e.clipboardData || window.clipboardData).items;
            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    prosesUploadThumbnailBarang(card, item.getAsFile());
                    e.preventDefault();
                    return;
                }
            }
        });
    }

    async function uploadThumbnailLine(input) {
        if (!input.files || !input.files[0]) return;
        await prosesUploadThumbnailBarang(input.closest('.jersey-line-card'), input.files[0]);
    }

    async function prosesUploadThumbnailBarang(card, file) {
        const statusEl = card.querySelector('.thumbnail-status');
        statusEl.textContent = 'Mengunggah...';
        statusEl.classList.remove('text-rose-500');
        const formData = new FormData();
        formData.append('thumbnail', file);
        formData.append('_token', '{{ csrf_token() }}');
        try {
            const res = await fetch('{{ route("jersey.orders.upload-thumbnail") }}', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                card.querySelector('.thumbnail-path').value = data.path;
                const img = card.querySelector('.thumbnail-preview');
                img.src = data.url;
                img.classList.remove('hidden');
                card.querySelectorAll('.thumbnail-placeholder').forEach(el => el.classList.add('hidden'));
                statusEl.textContent = 'Thumbnail siap. Klik kotak untuk mengganti.';
            } else {
                statusEl.textContent = 'Gagal mengunggah, coba lagi.';
                statusEl.classList.add('text-rose-500');
            }
        } catch (e) {
            statusEl.textContent = 'Gagal mengunggah, coba lagi.';
            statusEl.classList.add('text-rose-500');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const card = document.querySelector('#modal-tambah-susulan .jersey-line-card');
        if (card) {
            pasangPasteThumbnail(card);
            perbaruiVisibilitasLenganKerah(card);
        }
    });

    document.getElementById('form-tambah-susulan').addEventListener('submit', async function (e) {
        e.preventDefault();

        document.querySelectorAll('#modal-tambah-susulan .baris-pemain').forEach(row => {
            if (row.querySelector('[name*="player_name"]').value.trim() === '') row.remove();
        });

        const card = document.querySelector('#modal-tambah-susulan .jersey-line-card');
        if (!card || card.querySelector('.thumbnail-path').value.trim() === '') {
            Swal.fire('Thumbnail Belum Diisi', 'Barang susulan wajib ada thumbnail desain.', 'warning');
            return;
        }

        let peringatan = [];
        try {
            const res = await fetch('{{ route("jersey.orders.cek-kelengkapan") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(this),
            });
            const data = await res.json();
            peringatan = data.peringatan || [];
        } catch (err) {
            peringatan = [];
        }

        if (peringatan.length > 0) {
            const daftar = peringatan.map(p => `<li>${p}</li>`).join('');
            const konfirmasi = await Swal.fire({
                title: 'Ada Komponen Belum Lengkap',
                html: `<ul style="text-align:left; font-size:12.5px; padding-left:1.2em;">${daftar}</ul>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Ya, Simpan Sekarang',
                cancelButtonText: 'Batal, Saya Cek Lagi',
                customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
            });
            if (!konfirmasi.isConfirmed) return;
        }

        this.submit();
    });
</script>
@endpush
@endsection
