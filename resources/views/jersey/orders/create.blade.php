@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-5xl mx-auto">

    <x-page-header title="Order Jersey Baru" subtitle="Buat order jersey - mode daftar pemain atau qty per ukuran." />

    @if (session('error'))
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 font-semibold">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 font-semibold">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jersey.orders.store') }}" method="POST" id="jersey-order-form">
        @csrf

        <x-card class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.group label="Tanggal Order" required>
                    <x-form.input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required />
                </x-form.group>

                <x-form.group label="Pelanggan Terdaftar (opsional)">
                    <x-form.select name="jersey_customer_id" id="select_jersey_customer" onchange="isiDariPelangganTerdaftar()">
                        <option value="">-- Ketik manual di bawah --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">{{ $c->name }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>

                <x-form.group label="Nama Tim (opsional)">
                    <x-form.input type="text" name="team_name" value="{{ old('team_name') }}" />
                </x-form.group>

                <x-form.group label="Nama Pelanggan" required>
                    <x-form.input type="text" name="customer_name" id="input_customer_name" value="{{ old('customer_name') }}" required />
                </x-form.group>

                <x-form.group label="No. HP Pelanggan">
                    <x-form.input type="text" name="customer_phone" id="input_customer_phone" value="{{ old('customer_phone') }}" />
                </x-form.group>

                <x-form.group label="Deadline">
                    <x-form.input type="date" name="deadline" value="{{ old('deadline') }}" />
                </x-form.group>

                <x-form.group label="Tipe Produk">
                    <x-form.select name="jersey_product_type_id">
                        <option value="">-- Pilih --</option>
                        @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                    </x-form.select>
                </x-form.group>

                <x-form.group label="Kategori">
                    <x-form.select name="jersey_category_id">
                        <option value="">-- Pilih --</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </x-form.select>
                </x-form.group>

                <x-form.group label="Desainer">
                    <x-form.select name="jersey_designer_id">
                        <option value="">-- Pilih --</option>
                        @foreach($designers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </x-form.select>
                </x-form.group>
            </div>
        </x-card>

        <x-card class="mb-6">
            <x-form.group label="Mode Order" required>
                <div class="grid grid-cols-3 gap-3">
                    <label class="order-type-option relative flex flex-col items-center gap-1 py-4 px-4 rounded-xl border-2 border-indigo-500 bg-indigo-50 text-indigo-700 font-bold text-xs cursor-pointer">
                        <input type="radio" name="order_type" value="player_list" checked onchange="toggleOrderType()" class="absolute top-2 right-2">
                        Daftar Pemain
                    </label>
                    <label class="order-type-option relative flex flex-col items-center gap-1 py-4 px-4 rounded-xl border-2 border-slate-100 text-slate-400 font-bold text-xs cursor-pointer">
                        <input type="radio" name="order_type" value="quantity_only" onchange="toggleOrderType()" class="absolute top-2 right-2">
                        Qty per Ukuran
                    </label>
                    <label class="order-type-option relative flex flex-col items-center gap-1 py-4 px-4 rounded-xl border-2 border-slate-100 text-slate-400 font-bold text-xs cursor-pointer">
                        <input type="radio" name="order_type" value="mixed" onchange="toggleOrderType()" class="absolute top-2 right-2">
                        Campuran
                    </label>
                </div>
            </x-form.group>
        </x-card>

        {{-- MODE: DAFTAR PEMAIN --}}
        <x-card class="mb-6" id="wrapper_player_list">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-700">Daftar Pemain</h3>
                <x-button type="button" size="sm" variant="success" onclick="tambahBarisPemain()"><i class="fas fa-plus"></i> Tambah Pemain</x-button>
            </div>
            <x-table bare class="!min-w-0">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Nama Pemain</th>
                        <th class="p-2 text-left w-20">No.</th>
                        <th class="p-2 text-left w-24">Ukuran</th>
                        <th class="p-2 text-left w-24">Gender</th>
                        <th class="p-2 text-left w-28">Lengan</th>
                        <th class="p-2 text-left">Kerah</th>
                        <th class="p-2 text-center w-12"></th>
                    </tr>
                </thead>
                <tbody id="tbody_pemain" class="divide-y divide-slate-50"></tbody>
            </x-table>
        </x-card>

        {{-- MODE: QTY PER UKURAN --}}
        <x-card class="mb-6 hidden" id="wrapper_qty_ukuran">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Qty per Ukuran</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($sizes as $s)
                <x-form.group label="{{ $s->name }}">
                    <x-form.input type="number" name="size_quantities[{{ $s->id }}]" min="0" value="0" />
                </x-form.group>
                @endforeach
            </div>
        </x-card>

        <x-card class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.group label="Diskon (Rp)">
                    <x-form.input type="text" inputmode="numeric" name="discount" value="{{ old('discount', 0) }}" class="format-ribuan" />
                </x-form.group>
                <x-form.group label="Biaya Tambahan (Rp)">
                    <x-form.input type="text" inputmode="numeric" name="additional_charge" value="{{ old('additional_charge', 0) }}" class="format-ribuan" />
                </x-form.group>
                <x-form.group label="Deposit / DP (Rp)">
                    <x-form.input type="text" inputmode="numeric" name="deposit" value="{{ old('deposit', 0) }}" class="format-ribuan" />
                </x-form.group>
            </div>
            <x-form.group label="Catatan" class="mt-6">
                <textarea name="notes" rows="3" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ old('notes') }}</textarea>
            </x-form.group>
        </x-card>

        <div class="flex justify-end">
            <x-button type="submit" class="!px-12 !py-5">Simpan Order</x-button>
        </div>
    </form>
</div>

{{-- Template baris pemain - di-clone via JS, bukan server-render, supaya
     nomor index array items[] selalu berurutan rapi tanpa perlu re-render
     dari server tiap tambah/hapus baris. --}}
<template id="template_baris_pemain">
    <tr class="baris-pemain">
        <td class="p-2"><input type="text" name="items[__INDEX__][player_name]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-2"><input type="text" name="items[__INDEX__][player_number]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-2">
            <select name="items[__INDEX__][size]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
                @foreach($sizes as $s)<option value="{{ $s->name }}">{{ $s->name }}</option>@endforeach
            </select>
        </td>
        <td class="p-2">
            <select name="items[__INDEX__][gender]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
                <option value="L">L</option>
                <option value="P">P</option>
            </select>
        </td>
        <td class="p-2">
            <select name="items[__INDEX__][sleeve]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
                <option value="Pendek">Pendek</option>
                <option value="Panjang">Panjang</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="items[__INDEX__][collar]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    let jerseyPlayerRowIndex = 0;

    function tambahBarisPemain() {
        const tpl = document.getElementById('template_baris_pemain').innerHTML.replaceAll('__INDEX__', jerseyPlayerRowIndex);
        document.getElementById('tbody_pemain').insertAdjacentHTML('beforeend', tpl);
        jerseyPlayerRowIndex++;
    }

    function toggleOrderType() {
        const tipe = document.querySelector('input[name="order_type"]:checked').value;
        document.getElementById('wrapper_player_list').classList.toggle('hidden', tipe === 'quantity_only');
        document.getElementById('wrapper_qty_ukuran').classList.toggle('hidden', tipe === 'player_list');

        document.querySelectorAll('.order-type-option').forEach(label => {
            const checked = label.querySelector('input').checked;
            label.classList.toggle('border-indigo-500', checked);
            label.classList.toggle('bg-indigo-50', checked);
            label.classList.toggle('text-indigo-700', checked);
            label.classList.toggle('border-slate-100', !checked);
            label.classList.toggle('text-slate-400', !checked);
        });
    }

    function isiDariPelangganTerdaftar() {
        const sel = document.getElementById('select_jersey_customer');
        const opt = sel.options[sel.selectedIndex];
        if (!opt.value) return;
        document.getElementById('input_customer_name').value = opt.dataset.name || '';
        document.getElementById('input_customer_phone').value = opt.dataset.phone || '';
    }

    // Minimal 1 baris pemain saat halaman dibuka (mode default = Daftar Pemain)
    document.addEventListener('DOMContentLoaded', () => tambahBarisPemain());

    // Validasi qty total > 0 di sisi klien SEBELUM submit - baris pemain
    // kosong (player_name kosong) sengaja TIDAK pakai atribut required lagi
    // (sempat jadi bug: baris tersembunyi di mode Qty per Ukuran tetap
    // dianggap wajib oleh browser walau display:none, memblokir submit
    // tanpa pesan apa pun) - validasi total qty sekarang murni di sini +
    // di server (JerseyOrderController::store), bukan per-field HTML.
    document.getElementById('jersey-order-form').addEventListener('submit', function (e) {
        const tipe = document.querySelector('input[name="order_type"]:checked').value;
        let totalQty = 0;

        // Baris pemain yang nama-nya kosong (mis. baris otomatis pertama
        // yang tidak jadi dipakai karena mode diganti ke Qty per Ukuran)
        // dibuang dari form SEBELUM submit - kalau tetap terkirim, server
        // menganggap array items[] "ada isinya" dan mewajibkan player_name
        // terisi (required_with:items), padahal baris itu memang sengaja
        // tidak dipakai.
        document.querySelectorAll('#tbody_pemain .baris-pemain').forEach(row => {
            if (row.querySelector('[name*="player_name"]').value.trim() === '') row.remove();
        });

        if (tipe !== 'quantity_only') {
            document.querySelectorAll('#tbody_pemain .baris-pemain').forEach(row => {
                if (row.querySelector('[name*="player_name"]').value.trim() !== '') totalQty++;
            });
        }
        if (tipe !== 'player_list') {
            document.querySelectorAll('[name^="size_quantities"]').forEach(inp => {
                totalQty += parseInt(inp.value) || 0;
            });
        }

        if (totalQty <= 0) {
            e.preventDefault();
            Swal.fire('Order Kosong', 'Isi minimal 1 nama pemain atau 1 qty ukuran sebelum menyimpan.', 'warning');
        }
    });
</script>
@endpush
@endsection
