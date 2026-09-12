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
                    <p class="text-[11px] text-slate-400 mt-1.5 ml-2">Belum ada namanya? <a href="{{ route('konsumen.index') }}" target="_blank" class="text-indigo-600 underline">Tambah di Master Konsumen</a></p>
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

        <button type="button" onclick="tambahBarisBarang()" class="w-full mb-6 py-4 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 font-bold text-xs uppercase tracking-widest hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">
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
                <textarea name="notes" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ old('notes') }}</textarea>
            </x-form.group>
        </x-card>

        <div class="flex justify-end">
            <x-button type="submit" class="!px-12 !py-5">Simpan Order</x-button>
        </div>
    </form>
</div>

{{-- ===================== TEMPLATE 1 BARIS BARANG ===================== --}}
<template id="template_baris_barang">
    <div class="jersey-line-card bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 bg-indigo-50/50 border-b border-indigo-100 flex items-center justify-between">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Barang #<span class="line-number">1</span></span>
            <button type="button" onclick="this.closest('.jersey-line-card').remove()" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Barang</label>
                    <input type="text" name="lines[__LINE__][nama_barang]" required placeholder="mis. Jersey MTsN Buol" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe Produk</label>
                    <select name="lines[__LINE__][jersey_product_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        <option value="">-- Pilih --</option>
                        @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
                    <select name="lines[__LINE__][jersey_category_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        <option value="">-- Pilih --</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bahan</label>
                    <select name="lines[__LINE__][jersey_material_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($materials as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                    @if($materials->isEmpty())
                        <p class="text-[10px] text-rose-500 mt-1">Belum ada data Bahan - <a href="{{ route('jersey.master.index') }}" target="_blank" class="underline">tambah dulu di Master Data</a>.</p>
                    @endif
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Keterangan Desain</label>
                    <textarea name="lines[__LINE__][keterangan]" rows="2" placeholder="mis. Lengan kanan bendera, dada kiri logo klub..." class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Thumbnail Desain <span class="text-rose-500">*</span>
                    <span class="normal-case font-normal text-slate-400">- acuan warna &amp; bentuk untuk laporan produksi</span>
                </label>
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="thumbnail-dropzone w-32 h-32 rounded-xl bg-slate-50 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center overflow-hidden shrink-0 cursor-pointer transition-all hover:border-indigo-400 hover:bg-indigo-50/30" tabindex="0" onclick="this.querySelector('.thumbnail-file-input').click()">
                        <i class="fas fa-camera text-slate-300 text-lg thumbnail-placeholder"></i>
                        <span class="text-[9px] text-slate-400 font-bold uppercase text-center px-2 mt-1 thumbnail-placeholder">Klik, Tempel (Ctrl+V), atau Pilih File</span>
                        <img class="w-full h-full object-cover hidden thumbnail-preview" src="" alt="">
                        <input type="file" accept="image/*" onchange="uploadThumbnailLine(this)" class="hidden thumbnail-file-input">
                    </div>
                    <div class="flex-1">
                        <input type="hidden" name="lines[__LINE__][thumbnail]" class="thumbnail-path" required>
                        <p class="text-xs text-slate-400 thumbnail-status">Belum ada gambar - klik kotak di samping lalu pilih file, atau salin gambar (mis. dari WhatsApp/desain) dan tempel (Ctrl+V) di sini.</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mode Order</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <label class="line-type-option relative flex items-center justify-center py-3 px-3 rounded-xl border-2 border-indigo-500 bg-indigo-50 text-indigo-700 font-bold text-[11px] cursor-pointer">
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
                        <input type="number" name="lines[__LINE__][size_quantities][{{ $s->id }}]" min="0" value="0" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="wrapper-daftar-pemain hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daftar Pemain</p>
                    <button type="button" onclick="tambahBarisPemain(this)" class="text-[10px] font-bold text-indigo-600 uppercase underline">+ Tambah Pemain</button>
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

            <p class="text-[11px] text-slate-400 bg-slate-50 rounded-xl p-3">
                <i class="fas fa-info-circle text-indigo-400"></i>
                Spesifikasi kebutuhan bahan (meter kain per komponen) dihitung <strong>otomatis</strong> dari qty per ukuran x estimasi di Master Data, dan akan terlihat di halaman detail order &amp; SPK setelah disimpan.
            </p>
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
        pasangPasteThumbnail(card);
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
            label.classList.toggle('border-indigo-500', checked);
            label.classList.toggle('bg-indigo-50', checked);
            label.classList.toggle('text-indigo-700', checked);
            label.classList.toggle('border-slate-100', !checked);
            label.classList.toggle('text-slate-400', !checked);
        });
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

    // ---- Thumbnail: upload via klik-pilih-file ATAU tempel (Ctrl+V) ----
    function pasangPasteThumbnail(card) {
        const dropzone = card.querySelector('.thumbnail-dropzone');
        dropzone.addEventListener('paste', (e) => {
            const items = (e.clipboardData || window.clipboardData).items;
            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    const file = item.getAsFile();
                    prosesUploadThumbnail(card, file);
                    e.preventDefault();
                    return;
                }
            }
        });
    }

    async function uploadThumbnailLine(input) {
        if (!input.files || !input.files[0]) return;
        const card = input.closest('.jersey-line-card');
        await prosesUploadThumbnail(card, input.files[0]);
    }

    async function prosesUploadThumbnail(card, file) {
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
        $('.select2-konsumen-jersey').select2({ placeholder: '-- Cari Nama / No. HP --', allowClear: true, width: '100%' });
        tambahBarisBarang();
    });

    document.getElementById('jersey-order-form').addEventListener('submit', function (e) {
        // Baris pemain kosong dibuang dulu - baris tersembunyi (mis. mode
        // diganti ke Qty per Ukuran) tetap terkirim ke server kalau tidak
        // dibersihkan.
        document.querySelectorAll('.baris-pemain').forEach(row => {
            if (row.querySelector('[name*="player_name"]').value.trim() === '') row.remove();
        });

        const cards = document.querySelectorAll('.jersey-line-card');
        if (cards.length === 0) {
            e.preventDefault();
            Swal.fire('Belum Ada Barang', 'Tambahkan minimal 1 jenis barang sebelum menyimpan.', 'warning');
            return;
        }

        // Thumbnail WAJIB per barang - dicek di sini juga (bukan cuma
        // required HTML) supaya pesannya jelas menunjuk barang yang mana.
        for (const card of cards) {
            if (card.querySelector('.thumbnail-path').value.trim() === '') {
                e.preventDefault();
                const nomor = card.querySelector('.line-number').textContent;
                Swal.fire('Thumbnail Belum Diisi', `Barang #${nomor} belum ada thumbnail desain - wajib diisi sebagai acuan warna & bentuk produksi.`, 'warning');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }
    });
</script>
@endpush
@endsection
