@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-6xl mx-auto">

    <x-page-header title="Kelola Barang - {{ $order->order_number }}" subtitle="Ubah, tambah, atau hapus barang pada order ini. Harga, tambahan ukuran, & estimasi bahan dihitung ulang otomatis.">
        <x-slot:actions>
            <x-button href="{{ route('jersey.orders.show', $order->id) }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-amber-50 text-amber-700 p-4 rounded-2xl mb-6 text-xs font-semibold flex items-start gap-2">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <span>Menyimpan perubahan di sini akan menghitung ulang SEMUA barang pada order ini - id barang lama berubah, jadi tautan SPK yang sudah dicetak sebelumnya perlu dicetak ulang dari halaman Detail Order setelah disimpan.</span>
    </div>

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

    <form action="{{ route('jersey.orders.update-lines', $order->id) }}" method="POST" id="jersey-order-form">
        @csrf @method('PUT')

        <div id="wrapper-lines" class="space-y-6 mb-6">
            @foreach($order->lines as $line)
            <div class="jersey-line-card bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden" data-prefix="lines[{{ $line->id }}]">
                @include('jersey.orders._line-card-fields', ['idx' => $line->id, 'line' => $line, 'productTypes' => $productTypes, 'categories' => $categories, 'materials' => $materials, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
            </div>
            @endforeach
        </div>

        <button type="button" onclick="tambahBarisBarang()" class="w-full mb-6 py-4 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 font-bold text-xs uppercase tracking-widest hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">
            <i class="fas fa-plus"></i> Tambah Jenis Barang
        </button>

        <div class="flex justify-end">
            <x-button type="submit" class="!px-12 !py-5">Simpan Perubahan Barang</x-button>
        </div>
    </form>
</div>

{{-- ===================== TEMPLATE 1 BARIS BARANG (kosong, untuk "+ Tambah Jenis Barang") ===================== --}}
<template id="template_baris_barang">
    <div class="jersey-line-card bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        @include('jersey.orders._line-card-fields', ['idx' => '__LINE__', 'line' => null, 'productTypes' => $productTypes, 'categories' => $categories, 'materials' => $materials, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
    </div>
</template>

{{-- ===================== TEMPLATE BARIS PEMAIN (kosong) ===================== --}}
<template id="template_baris_pemain_jersey">
    @include('jersey.orders._player-row-fields', ['prefix' => '__PREFIX__', 'itemIdx' => '__ITEM__', 'item' => null, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
</template>

@push('scripts')
<script>
    // jerseyLineIndex dimulai dari angka besar (timestamp) supaya barang
    // BARU yang ditambah lewat "+ Tambah Jenis Barang" di halaman edit ini
    // tidak pernah bentrok dengan id barang lama yang sudah dipakai sebagai
    // index array (lines[{id}]) pada kartu-kartu yang sudah dirender server.
    let jerseyLineIndex = Date.now();

    function tambahBarisBarang() {
        const idx = jerseyLineIndex++;
        const html = document.getElementById('template_baris_barang').innerHTML.replaceAll('__LINE__', idx);
        document.getElementById('wrapper-lines').insertAdjacentHTML('beforeend', html);
        const card = document.getElementById('wrapper-lines').lastElementChild;
        card.dataset.prefix = `lines[${idx}]`;
        pasangPasteThumbnail(card);
        perbaruiNomorBarang();
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function perbaruiNomorBarang() {
        document.querySelectorAll('.jersey-line-card').forEach((card, i) => {
            card.querySelector('.line-number').textContent = i + 1;
        });
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
        document.querySelectorAll('.jersey-line-card').forEach(pasangPasteThumbnail);
        perbaruiNomorBarang();
    });

    document.getElementById('jersey-order-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        document.querySelectorAll('.baris-pemain').forEach(row => {
            if (row.querySelector('[name*="player_name"]').value.trim() === '') row.remove();
        });

        const cards = document.querySelectorAll('.jersey-line-card');
        if (cards.length === 0) {
            Swal.fire('Belum Ada Barang', 'Order harus punya minimal 1 jenis barang.', 'warning');
            return;
        }

        for (const card of cards) {
            if (card.querySelector('.thumbnail-path').value.trim() === '') {
                const nomor = card.querySelector('.line-number').textContent;
                Swal.fire('Thumbnail Belum Diisi', `Barang #${nomor} belum ada thumbnail desain - wajib diisi sebagai acuan warna & bentuk produksi.`, 'warning');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
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

        const konfirmasiSimpan = await Swal.fire({
            title: 'Simpan Perubahan Barang?',
            html: 'Semua barang pada order ini akan dihitung ulang (harga, tambahan ukuran, estimasi bahan). Tautan SPK lama untuk barang yang berubah perlu dicetak ulang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
        });
        if (!konfirmasiSimpan.isConfirmed) return;

        this.querySelectorAll('input.format-ribuan').forEach(input => {
            input.value = cleanRupiah(input.value);
        });

        this.submit();
    });
</script>
@endpush
@endsection
