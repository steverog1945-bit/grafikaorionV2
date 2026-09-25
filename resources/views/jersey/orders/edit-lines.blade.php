@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
@include('partials.image-compress-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 max-w-[1400px] mx-auto">

    <x-page-header title="Kelola Barang - {{ $order->order_number }}" subtitle="Ubah, tambah, atau hapus barang pada order ini. Harga, tambahan ukuran, & estimasi bahan dihitung ulang otomatis.">
        <x-slot:actions>
            <x-button href="{{ route('jersey.orders.show', $order->id) }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-amber-50 text-amber-700 p-3 rounded-2xl mb-4 text-xs font-semibold flex items-start gap-2">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <span>Menyimpan perubahan di sini akan menghitung ulang SEMUA barang pada order ini - id barang lama berubah, jadi tautan SPK yang sudah dicetak sebelumnya perlu dicetak ulang dari halaman Detail Order setelah disimpan.</span>
    </div>

    @if (session('error'))
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-4 font-semibold">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-4 font-semibold">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jersey.orders.update-lines', $order->id) }}" method="POST" id="jersey-order-form">
        @csrf @method('PUT')
        @versi($order)

        {{-- Total & tombol Simpan dipindah ke kolom kanan STICKY - selalu
             kelihatan tanpa perlu scroll ke paling bawah, sama seperti
             Order Jersey Baru & Keranjang Pesanan Input Order. --}}
        <div class="flex flex-col lg:flex-row gap-5 items-start">
            <div class="w-full lg:w-[64%] space-y-4">
                <div id="wrapper-lines" class="space-y-4">
                    @foreach($order->lines as $line)
                    <div class="jersey-line-card bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden" data-prefix="lines[{{ $line->id }}]">
                        @include('jersey.orders._line-card-fields', ['idx' => $line->id, 'line' => $line, 'productTypes' => $productTypes, 'categories' => $categories, 'materials' => $materials, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="tambahBarisBarang()" class="w-full py-3 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 font-bold text-xs uppercase tracking-widest hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">
                    <i class="fas fa-plus"></i> Tambah Jenis Barang
                </button>
            </div>

            <div class="w-full lg:w-[36%] lg:sticky lg:top-6 space-y-4">
                <x-card>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Ringkasan Barang</h3>
                    <div id="ringkasan-daftar-barang" class="divide-y divide-slate-50">
                        <p class="text-xs text-slate-300 italic text-center py-5">Belum ada barang.</p>
                    </div>
                </x-card>

                <div class="flex items-center justify-between gap-4 flex-wrap bg-indigo-600 rounded-2xl px-6 py-4">
                    <span class="text-xs font-bold text-indigo-200 uppercase tracking-widest">Perkiraan Total Barang</span>
                    <span id="preview-total-order" class="text-2xl font-black text-white">Rp 0</span>
                </div>

                <x-button type="submit" class="w-full !py-4">Simpan Perubahan Barang</x-button>
            </div>
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
@include('jersey.orders._size-helper')
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
        perbaruiVisibilitasLenganKerah(card);
        jadwalkanPreviewHarga();
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
        perbaruiVisibilitasLenganKerah(card);
    }

    function tambahBarisPemain(btn) {
        const card = btn.closest('.jersey-line-card');
        const tbody = card.querySelector('.tbody-pemain');
        const itemIdx = tbody.querySelectorAll('.baris-pemain').length;
        const html = document.getElementById('template_baris_pemain_jersey').innerHTML
            .replaceAll('__PREFIX__', card.dataset.prefix)
            .replaceAll('__ITEM__', itemIdx);
        tbody.insertAdjacentHTML('beforeend', html);
        perbaruiVisibilitasLenganKerah(card);
        jadwalkanPreviewHarga();
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

    // ---- Thumbnail: upload via klik-pilih-file ATAU tempel (Ctrl+V) ----
    // CATATAN PENTING: paste TIDAK dipasang langsung ke elemen dropzone
    // (dulu begitu, dan gagal). Kotak dropzone punya onclick yang memicu
    // .click() pada <input type=file> tersembunyi di dalamnya untuk buka
    // dialog pilih file OS - begitu dialog itu terbuka lalu ditutup/
    // dibatalkan, fokus browser TIDAK kembali ke kotak dropzone (kadang
    // hilang ke body, kadang ke elemen lain), jadi listener 'paste' yang
    // butuh fokus pada dropzone itu sendiri tidak pernah kepicu - persis
    // gejala yang dilaporkan: paste gambar dari WhatsApp tidak berfungsi.
    // Solusi: satu listener 'paste' GLOBAL di document, mengarah ke kartu
    // yang terakhir disentuh mouse (hover/klik) pada dropzone-nya - tidak
    // bergantung pada fokus DOM yang rapuh setelah dialog file native.
    let kartuAktifUntukPasteThumbnail = null;
    function pasangPasteThumbnail(card) {
        const dropzone = card.querySelector('.thumbnail-dropzone');
        dropzone.addEventListener('mouseenter', () => { kartuAktifUntukPasteThumbnail = card; });
        dropzone.addEventListener('click', () => { kartuAktifUntukPasteThumbnail = card; });
    }
    document.addEventListener('paste', (e) => {
        if (!kartuAktifUntukPasteThumbnail || !document.body.contains(kartuAktifUntukPasteThumbnail)) return;
        const items = (e.clipboardData || window.clipboardData)?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                prosesUploadThumbnail(kartuAktifUntukPasteThumbnail, item.getAsFile());
                e.preventDefault();
                return;
            }
        }
    });

    async function uploadThumbnailLine(input) {
        if (!input.files || !input.files[0]) return;
        const card = input.closest('.jersey-line-card');
        await prosesUploadThumbnail(card, input.files[0]);
    }

    async function prosesUploadThumbnail(card, file) {
        const statusEl = card.querySelector('.thumbnail-status');
        statusEl.textContent = 'Memampatkan gambar...';
        statusEl.classList.remove('text-rose-500');
        // Dikompres di browser dulu - lihat catatan di
        // jersey/orders/create.blade.php::prosesUploadThumbnail().
        let fileUpload = file;
        try {
            const compressed = await compressImage(file);
            fileUpload = new File([compressed], file.name.replace(/\.[^./]+$/, '') + '.jpg', { type: 'image/jpeg' });
        } catch (e) { /* gagal kompres (jarang) - lanjut pakai file asli */ }
        statusEl.textContent = 'Mengunggah...';
        const formData = new FormData();
        formData.append('thumbnail', fileUpload);
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
        document.querySelectorAll('.jersey-line-card').forEach(card => {
            pasangPasteThumbnail(card);
            perbaruiVisibilitasLenganKerah(card);
        });
        perbaruiNomorBarang();
        jalankanPreviewHarga();
    });

    // ---- Perkiraan harga LIVE - dihitung ulang di server (bukan ditiru di
    // JS) tiap field yang memengaruhi harga berubah, supaya angka yang
    // dilihat operator SELALU sama dengan yang akan benar-benar tersimpan.
    // Di-debounce supaya tidak nembak request tiap ketikan. ----
    let timerPreviewHarga = null;
    function jadwalkanPreviewHarga() {
        clearTimeout(timerPreviewHarga);
        timerPreviewHarga = setTimeout(jalankanPreviewHarga, 400);
    }

    async function jalankanPreviewHarga() {
        const form = document.getElementById('jersey-order-form');
        if (!form) return;
        let data;
        try {
            // Form ini punya hidden _method=PUT (untuk submit sungguhan ke
            // update-lines) - kalau ikut terkirim, request preview yang
            // aslinya POST akan di-spoof jadi PUT oleh Laravel dan malah
            // kena route PUT /jersey/{order} (update order), bukan endpoint
            // preview. Dibuang dulu khusus untuk request preview ini.
            const fd = new FormData(form);
            fd.delete('_method');
            const res = await fetch('{{ route("jersey.orders.preview-harga") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd,
            });
            data = await res.json();
        } catch (e) {
            return;
        }

        const ringkasanEl = document.getElementById('ringkasan-daftar-barang');
        ringkasanEl.innerHTML = '';

        document.querySelectorAll('.jersey-line-card').forEach((card, idx) => {
            const m = (card.dataset.prefix || '').match(/\[(.+)\]/);
            const info = m ? data.lines?.[m[1]] : null;
            const nilaiEl = card.querySelector('.line-price-preview-value');
            const detailEl = card.querySelector('.line-price-preview-detail');
            if (nilaiEl) {
                if (!info) {
                    nilaiEl.textContent = 'Rp 0';
                    if (detailEl) detailEl.textContent = 'Isi qty & pilihan barang untuk lihat perkiraan harga.';
                } else {
                    nilaiEl.textContent = 'Rp ' + Math.round(info.subtotal).toLocaleString('id-ID');
                    const bagian = [`${info.qty} pcs × Rp ${Math.round(info.harga_satuan).toLocaleString('id-ID')}`];
                    if (info.size_surcharge_total > 0) bagian.push(`+Rp ${Math.round(info.size_surcharge_total).toLocaleString('id-ID')} ukuran`);
                    if (info.sleeve_collar_surcharge_total > 0) bagian.push(`+Rp ${Math.round(info.sleeve_collar_surcharge_total).toLocaleString('id-ID')} lengan/kerah`);
                    if (!info.ada_tier) bagian.push('belum ada tier harga yang cocok');
                    if (detailEl) detailEl.textContent = bagian.join(', ');
                }
            }

            // Baris ringkasan di sidebar - via createElement/textContent (bukan
            // innerHTML string) supaya nama barang yang diketik operator tidak
            // pernah dianggap markup.
            const namaBarang = card.querySelector('input[name$="[nama_barang]"]')?.value.trim() || `Barang #${idx + 1}`;
            const thumbSrc = card.querySelector('.thumbnail-preview:not(.hidden)')?.src || '';
            const qty = info ? info.qty : 0;
            const subtotal = info ? Math.round(info.subtotal) : 0;

            const baris = document.createElement('div');
            baris.className = 'flex items-center gap-2.5 py-2';

            const thumbWrap = document.createElement('div');
            thumbWrap.className = 'w-9 h-9 rounded-lg bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center';
            if (thumbSrc) {
                const img = document.createElement('img');
                img.src = thumbSrc;
                img.className = 'w-full h-full object-cover';
                thumbWrap.appendChild(img);
            } else {
                thumbWrap.innerHTML = '<i class="fas fa-tshirt text-slate-300 text-xs"></i>';
            }
            baris.appendChild(thumbWrap);

            const infoWrap = document.createElement('div');
            infoWrap.className = 'flex-1 min-w-0';
            const namaEl = document.createElement('p');
            namaEl.className = 'text-xs font-bold text-slate-700 truncate';
            namaEl.textContent = namaBarang;
            const qtyEl = document.createElement('p');
            qtyEl.className = 'text-[10px] text-slate-400';
            qtyEl.textContent = `${qty} pcs`;
            infoWrap.append(namaEl, qtyEl);
            baris.appendChild(infoWrap);

            const subtotalEl = document.createElement('span');
            subtotalEl.className = 'text-xs font-bold text-slate-600 shrink-0';
            subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            baris.appendChild(subtotalEl);

            ringkasanEl.appendChild(baris);
        });

        if (!ringkasanEl.children.length) {
            ringkasanEl.innerHTML = '<p class="text-xs text-slate-300 italic text-center py-5">Belum ada barang.</p>';
        }

        const totalEl = document.getElementById('preview-total-order');
        if (totalEl) totalEl.textContent = 'Rp ' + Math.round(data.total_order || 0).toLocaleString('id-ID');
    }

    document.getElementById('jersey-order-form').addEventListener('input', (e) => {
        if (e.target.closest('.jersey-line-card')) jadwalkanPreviewHarga();
    });
    document.getElementById('jersey-order-form').addEventListener('change', (e) => {
        if (e.target.closest('.jersey-line-card')) jadwalkanPreviewHarga();
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
