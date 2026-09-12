@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-6xl mx-auto">

    <x-page-header title="Order Jersey Baru" subtitle="Satu order bisa berisi beberapa jenis barang sekaligus (Jersey, Celana, Sublime lainnya, dst)." />

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

                <x-form.group label="Pelanggan" required>
                    <select id="select_id_konsumen" name="id_konsumen" class="select2-konsumen-jersey" required>
                        <option value="">-- Cari Nama / No. HP --</option>
                        @foreach($konsumenList as $k)
                            <option value="{{ $k->id_konsumen }}" data-telp="{{ $k->telepon }}">{{ $k->nama_konsumen }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1.5 ml-2">Belum ada namanya? <a href="{{ route('konsumen.index') }}" target="_blank" class="text-slate-600 underline">Tambah di Master Konsumen</a></p>
                </x-form.group>

                <x-form.group label="Nama Tim (opsional)">
                    <x-form.input type="text" name="team_name" value="{{ old('team_name') }}" />
                </x-form.group>

                <x-form.group label="Deadline">
                    <x-form.input type="date" name="deadline" value="{{ old('deadline') }}" />
                </x-form.group>

                <x-form.group label="Desainer">
                    <x-form.select name="jersey_designer_id">
                        <option value="">-- Pilih --</option>
                        @foreach($designers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </x-form.select>
                </x-form.group>
            </div>
        </x-card>

        {{-- DAFTAR BARANG (multi-line) --}}
        <div id="wrapper-lines" class="space-y-6 mb-6"></div>

        <button type="button" onclick="tambahBarisBarang()" class="w-full mb-6 py-4 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 font-bold text-xs uppercase tracking-widest hover:border-slate-400 hover:bg-slate-50 transition-all">
            <i class="fas fa-plus"></i> Tambah Jenis Barang
        </button>

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
            <x-form.group label="Catatan Umum Order" class="mt-6">
                <textarea name="notes" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-slate-400/30 shadow-inner">{{ old('notes') }}</textarea>
            </x-form.group>
        </x-card>

        <div class="flex justify-end">
            <button type="submit" class="px-12 py-5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs uppercase tracking-widest">Simpan Order</button>
        </div>
    </form>
</div>

{{-- ===================== TEMPLATE 1 BARIS BARANG ===================== --}}
<template id="template_baris_barang">
    <div class="jersey-line-card bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Barang #<span class="line-number">1</span></span>
            <button type="button" onclick="this.closest('.jersey-line-card').remove()" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Barang</label>
                    <input type="text" name="lines[__LINE__][nama_barang]" required placeholder="mis. Jersey MTsN Buol" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe Produk</label>
                    <select name="lines[__LINE__][jersey_product_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
                        <option value="">-- Pilih --</option>
                        @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
                    <select name="lines[__LINE__][jersey_category_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
                        <option value="">-- Pilih --</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bahan</label>
                    <input type="text" name="lines[__LINE__][bahan]" placeholder="mis. Jersey, Cotton Combed" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Keterangan Desain</label>
                    <textarea name="lines[__LINE__][keterangan]" rows="2" placeholder="mis. Lengan kanan bendera, dada kiri logo klub..." class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner"></textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-20 h-20 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 thumbnail-preview-wrapper">
                    <i class="fas fa-image text-slate-300 text-xl thumbnail-placeholder"></i>
                    <img class="w-full h-full object-cover hidden thumbnail-preview" src="" alt="">
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Thumbnail Desain (opsional)</label>
                    <input type="file" accept="image/*" onchange="uploadThumbnailLine(this)" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-xs font-semibold text-slate-600 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-700 file:text-white">
                    <input type="hidden" name="lines[__LINE__][thumbnail]" class="thumbnail-path">
                    <p class="text-[10px] text-slate-400 mt-1 thumbnail-status"></p>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mode Order</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <label class="line-type-option relative flex items-center justify-center py-3 px-3 rounded-xl border-2 border-slate-700 bg-slate-100 text-slate-700 font-bold text-[11px] cursor-pointer">
                        <input type="radio" name="lines[__LINE__][order_type]" value="quantity_only" checked onchange="toggleLineType(this)" class="absolute top-1.5 right-1.5">
                        Qty per Ukuran
                    </label>
                    <label class="line-type-option relative flex items-center justify-center py-3 px-3 rounded-xl border-2 border-slate-100 text-slate-400 font-bold text-[11px] cursor-pointer">
                        <input type="radio" name="lines[__LINE__][order_type]" value="player_list" onchange="toggleLineType(this)" class="absolute top-1.5 right-1.5">
                        Daftar Pemain
                    </label>
                    <label class="line-type-option relative flex items-center justify-center py-3 px-3 rounded-xl border-2 border-slate-100 text-slate-400 font-bold text-[11px] cursor-pointer">
                        <input type="radio" name="lines[__LINE__][order_type]" value="mixed" onchange="toggleLineType(this)" class="absolute top-1.5 right-1.5">
                        Campuran
                    </label>
                </div>
            </div>

            <div class="wrapper-qty-ukuran">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Qty per Ukuran</p>
                <div class="grid grid-cols-3 md:grid-cols-8 gap-2">
                    @foreach($sizes as $s)
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase text-center mb-1">{{ $s->name }}</label>
                        <input type="number" name="lines[__LINE__][size_quantities][{{ $s->id }}]" min="0" value="0" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-bold text-center">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="wrapper-daftar-pemain hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daftar Pemain</p>
                    <button type="button" onclick="tambahBarisPemain(this)" class="text-[10px] font-bold text-slate-600 uppercase underline">+ Tambah Pemain</button>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="text-[10px] text-slate-400 uppercase">
                        <th class="p-1 text-left">Nama</th><th class="p-1 text-left w-16">No.</th><th class="p-1 text-left w-20">Ukuran</th>
                        <th class="p-1 text-left w-20">Gender</th><th class="p-1 text-left w-24">Lengan</th><th class="p-1 text-left">Kerah</th><th class="p-1 w-8"></th>
                    </tr></thead>
                    <tbody class="tbody-pemain divide-y divide-slate-50"></tbody>
                </table>
                </div>
            </div>

            <div>
                <button type="button" onclick="toggleSpesifikasi(this)" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest underline">+ Spesifikasi Bahan (opsional)</button>
                <div class="wrapper-spesifikasi hidden mt-3">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Spesifikasi Order</p>
                        <button type="button" onclick="tambahBarisMaterial(this)" class="text-[10px] font-bold text-slate-600 uppercase underline">+ Tambah Baris</button>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead><tr class="text-[10px] text-slate-400 uppercase">
                            <th class="p-1 text-left">Nama Barang</th><th class="p-1 text-left w-24">Warna</th>
                            <th class="p-1 text-left w-16">Kg</th><th class="p-1 text-left w-16">Meter</th><th class="p-1 text-left w-16">Yard</th>
                            <th class="p-1 text-left">Keterangan</th><th class="p-1 w-8"></th>
                        </tr></thead>
                        <tbody class="tbody-material divide-y divide-slate-50"></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- ===================== TEMPLATE BARIS PEMAIN ===================== --}}
<template id="template_baris_pemain_jersey">
    <tr class="baris-pemain">
        <td class="p-1"><input type="text" name="__PREFIX__[items][__ITEM__][player_name]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1"><input type="text" name="__PREFIX__[items][__ITEM__][player_number]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1">
            <select name="__PREFIX__[items][__ITEM__][size]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
                @foreach($sizes as $s)<option value="{{ $s->name }}">{{ $s->name }}</option>@endforeach
            </select>
        </td>
        <td class="p-1">
            <select name="__PREFIX__[items][__ITEM__][gender]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
                <option value="L">L</option><option value="P">P</option>
            </select>
        </td>
        <td class="p-1">
            <select name="__PREFIX__[items][__ITEM__][sleeve]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold">
                <option value="Pendek">Pendek</option><option value="Panjang">Panjang</option>
            </select>
        </td>
        <td class="p-1"><input type="text" name="__PREFIX__[items][__ITEM__][collar]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1 text-center"><button type="button" onclick="this.closest('tr').remove()" class="w-6 h-6 text-rose-400"><i class="far fa-trash-alt text-[10px]"></i></button></td>
    </tr>
</template>

{{-- ===================== TEMPLATE BARIS MATERIAL ===================== --}}
<template id="template_baris_material_jersey">
    <tr class="baris-material">
        <td class="p-1"><input type="text" name="__PREFIX__[materials][__MAT__][nama_barang]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1"><input type="text" name="__PREFIX__[materials][__MAT__][warna]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1"><input type="number" step="0.01" name="__PREFIX__[materials][__MAT__][penggunaan_kg]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1"><input type="number" step="0.01" name="__PREFIX__[materials][__MAT__][penggunaan_meter]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1"><input type="number" step="0.01" name="__PREFIX__[materials][__MAT__][penggunaan_yard]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1"><input type="text" name="__PREFIX__[materials][__MAT__][keterangan]" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-semibold"></td>
        <td class="p-1 text-center"><button type="button" onclick="this.closest('tr').remove()" class="w-6 h-6 text-rose-400"><i class="far fa-trash-alt text-[10px]"></i></button></td>
    </tr>
</template>

@push('scripts')
<script>
    let jerseyLineIndex = 0;

    function tambahBarisBarang() {
        const idx = jerseyLineIndex++;
        const html = document.getElementById('template_baris_barang').innerHTML.replaceAll('__LINE__', idx);
        document.getElementById('wrapper-lines').insertAdjacentHTML('beforeend', html);
        const card = document.getElementById('wrapper-lines').lastElementChild;
        card.querySelector('.line-number').textContent = document.querySelectorAll('.jersey-line-card').length;
        card.dataset.prefix = `lines[${idx}]`;
        // Selalu mulai dengan minimal 1 baris qty/pemain kosong supaya form
        // langsung siap diisi tanpa klik tambahan.
    }

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
            label.classList.toggle('border-slate-700', checked);
            label.classList.toggle('bg-slate-100', checked);
            label.classList.toggle('text-slate-700', checked);
            label.classList.toggle('border-slate-100', !checked);
            label.classList.toggle('text-slate-400', !checked);
        });
    }

    function toggleSpesifikasi(btn) {
        const wrapper = btn.nextElementSibling;
        wrapper.classList.toggle('hidden');
        if (!wrapper.classList.contains('hidden') && wrapper.querySelectorAll('.baris-material').length === 0) {
            tambahBarisMaterial(wrapper.querySelector('button'));
        }
    }

    function tambahBarisPemain(btn) {
        const card = btn.closest('.jersey-line-card');
        const tbody = card.querySelector('.tbody-pemain');
        const itemIdx = tbody.querySelectorAll('.baris-pemain').length;
        const html = document.getElementById('template_baris_pemain_jersey').innerHTML
            .replaceAll('__PREFIX__', card.dataset.prefix)
            .replaceAll('__ITEM__', itemIdx);
        tbody.insertAdjacentHTML('beforeend', html);
    }

    function tambahBarisMaterial(btn) {
        const card = btn.closest('.jersey-line-card');
        const tbody = card.querySelector('.tbody-material');
        const matIdx = tbody.querySelectorAll('.baris-material').length;
        const html = document.getElementById('template_baris_material_jersey').innerHTML
            .replaceAll('__PREFIX__', card.dataset.prefix)
            .replaceAll('__MAT__', matIdx);
        tbody.insertAdjacentHTML('beforeend', html);
    }

    async function uploadThumbnailLine(input) {
        if (!input.files || !input.files[0]) return;
        const card = input.closest('.jersey-line-card');
        const statusEl = card.querySelector('.thumbnail-status');
        statusEl.textContent = 'Mengunggah...';
        const formData = new FormData();
        formData.append('thumbnail', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        try {
            const res = await fetch('{{ route("jersey.orders.upload-thumbnail") }}', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                card.querySelector('.thumbnail-path').value = data.path;
                const img = card.querySelector('.thumbnail-preview');
                img.src = data.url;
                img.classList.remove('hidden');
                card.querySelector('.thumbnail-placeholder').classList.add('hidden');
                statusEl.textContent = 'Berhasil diunggah.';
            } else {
                statusEl.textContent = 'Gagal mengunggah.';
            }
        } catch (e) {
            statusEl.textContent = 'Gagal mengunggah.';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        $('.select2-konsumen-jersey').select2({ placeholder: '-- Cari Nama / No. HP --', allowClear: true, width: '100%' });
        tambahBarisBarang();
    });

    document.getElementById('jersey-order-form').addEventListener('submit', function (e) {
        // Baris pemain/material kosong dibuang dulu (sama alasannya seperti
        // versi single-line sebelumnya - baris tersembunyi tetap terkirim
        // ke server kalau tidak dibersihkan, walau sekarang atribut
        // required sudah tidak dipakai lagi di baris dinamis ini).
        document.querySelectorAll('.baris-pemain').forEach(row => {
            if (row.querySelector('[name*="player_name"]').value.trim() === '') row.remove();
        });
        document.querySelectorAll('.baris-material').forEach(row => {
            if (row.querySelector('[name*="nama_barang"]').value.trim() === '') row.remove();
        });

        if (document.querySelectorAll('.jersey-line-card').length === 0) {
            e.preventDefault();
            Swal.fire('Belum Ada Barang', 'Tambahkan minimal 1 jenis barang sebelum menyimpan.', 'warning');
            return;
        }
    });
</script>
@endpush
@endsection
