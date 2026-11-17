@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
@include('partials.image-compress-script')
<style>
/* Perombakan visual minimalis & modern - input order jersey */
.jersey-mobile-form-page input[type="text"],
.jersey-mobile-form-page input[type="date"],
.jersey-mobile-form-page input[type="number"],
.jersey-mobile-form-page input[type="file"],
.jersey-mobile-form-page select,
.jersey-mobile-form-page textarea {
    background-color: var(--surface-primary, #ffffff);
    border: 1px solid var(--border-primary, #e2e8f0);
    border-radius: 0.75rem;
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary, #334155);
    transition: border-color .15s ease, box-shadow .15s ease;
}
.jersey-mobile-form-page input:focus,
.jersey-mobile-form-page select:focus,
.jersey-mobile-form-page textarea:focus {
    outline: none;
    border-color: var(--brand-sidebar-accent, #E8891D);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-sidebar-accent, #E8891D) 18%, transparent);
}
.jersey-mobile-form-page .baris-pemain select,
.jersey-mobile-form-page .baris-pemain input {
    background-color: var(--surface-secondary, #f8fafc);
    border: 1px solid var(--border-secondary, var(--border-primary, #e2e8f0));
    border-radius: 0.5rem;
    padding: 0.5rem;
    font-size: 0.75rem;
}
</style>
<div class="jersey-mobile-form-page w-full px-3 sm:px-5 lg:px-6 py-5">

    <x-page-header title="Pesanan Jersey Baru" subtitle="Satu order bisa berisi beberapa jenis barang sekaligus (Jersey, Celana, Sublime lainnya, dst)." />

    @if (session('error'))
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-4 font-medium">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-4 font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jersey.orders.store') }}" method="POST" id="jersey-order-form">
        @csrf

        {{-- Ringkasan (daftar barang + Diskon/Biaya/Deposit/Catatan + Total +
             tombol Simpan) dipindah ke kolom kanan yang STICKY - dulu satu
             kolom penuh dari atas ke bawah, jadi total & tombol Simpan cuma
             kelihatan setelah scroll sampai paling bawah (baru kerasa parah
             kalau Barang-nya banyak). Sekarang selalu kelihatan tanpa scroll
             tambahan, sama seperti pola Keranjang Pesanan di Input Order.
             Panel ini juga BUKAN cuma 3 field penyesuaian menggantung di
             atas tombol Simpan (kerasa aneh) - sekarang benar-benar
             "ringkasan": daftar barang yang sudah diisi (nama, qty,
             thumbnail, subtotal - live update dari jalankanPreviewHarga())
             tampil dulu di atas, baru penyesuaian & total di bawahnya. --}}
        <div class="flex flex-col lg:flex-row gap-5 items-start">
            <div class="w-full lg:w-[64%] space-y-4" data-grid-focus>
                <x-card>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-form.group label="Tanggal Order" required>
                            <x-form.input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required />
                        </x-form.group>

                        <x-form.group>
                            {{-- Baris label satu garis: label kiri, link kanan.
                                 Setinggi label normal (mb-2) - kotak Pelanggan
                                 tetap lurus dengan kotak Tanggal Order. --}}
                            <div class="flex items-center justify-between h-[18px] mb-2 ml-2">
                                <span class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">Pelanggan <span class="text-rose-500">*</span></span>
                                <a href="{{ route('konsumen.index') }}" target="_blank" title="Tambah Konsumen (buka Master Konsumen)" class="text-[10px] font-semibold whitespace-nowrap text-indigo-600 hover:text-indigo-500">+ Baru</a>
                            </div>
                            <select id="select_id_konsumen" name="id_konsumen" class="select2-konsumen-jersey" required>
                                <option value="">Cari Nama / No. HP</option>
                                @foreach($konsumenList as $k)
                                    <option value="{{ $k->id_konsumen }}" data-telp="{{ $k->telepon }}">{{ $k->nama_konsumen }}</option>
                                @endforeach
                            </select>
                        </x-form.group>

                        <x-form.group label="Nama Tim (Opsional)">
                            <x-form.input type="text" name="team_name" value="{{ old('team_name') }}" />
                        </x-form.group>

                        <x-form.group label="Deadline">
                            <x-form.input type="date" name="deadline" value="{{ old('deadline') }}" />
                        </x-form.group>

                        <x-form.group label="Status Desain">
                            <input type="hidden" name="file_siap_cetak" id="file_siap_cetak" value="{{ old('file_siap_cetak') ? '1' : '0' }}">
                            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                                <button type="button" id="btn_desain_perlu" onclick="setSiapCetak(0)" class="py-2.5 rounded-lg text-xs font-medium uppercase tracking-widest transition-all">Perlu desain</button>
                                <button type="button" id="btn_desain_siap" onclick="setSiapCetak(1)" class="py-2.5 rounded-lg text-xs font-medium uppercase tracking-widest transition-all">Siap cetak</button>
                            </div>
                        </x-form.group>
                        <div id="wrapper_desainer_jersey">
                            <x-form.group label="Desainer">
                                @if($desainerTerkunci)
                                    <input type="hidden" id="input_desainer_jersey" name="id_desainer" value="{{ $desainerTerkunci }}">
                                @else
                                    <select id="input_desainer_jersey" name="id_desainer" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                                        <option value="">-- Tanpa Desainer --</option>
                                        @foreach($desainerList as $ds)
                                            <option value="{{ $ds->id_user }}" {{ (int) $desainerDefault === (int) $ds->id_user ? 'selected' : '' }}>{{ $ds->nama }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </x-form.group>
                        </div>

                        <script>
                            function setSiapCetak(val) {
                                const input = document.getElementById('file_siap_cetak');
                                if (input) input.value = val ? '1' : '0';
                                const perlu = document.getElementById('btn_desain_perlu');
                                const siap = document.getElementById('btn_desain_siap');
                                if (perlu) perlu.className = 'py-2.5 rounded-lg text-xs font-medium uppercase tracking-widest transition-all ' + (val ? 'text-slate-500' : 'bg-white text-indigo-600 shadow-sm');
                                if (siap) siap.className = 'py-2.5 rounded-lg text-xs font-medium uppercase tracking-widest transition-all ' + (val ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500');
                                const wrap = document.getElementById('wrapper_desainer_jersey');
                                if (wrap) wrap.style.display = val ? 'none' : '';
                            }
                            function toggleDesainerSiapCetak() { var _f = document.getElementById('file_siap_cetak'); setSiapCetak((_f ? _f.value : null) === '1'); }
                            document.addEventListener('DOMContentLoaded', toggleDesainerSiapCetak);
                        </script>
                    </div>
                </x-card>

                {{-- DAFTAR BARANG (multi-line) --}}
                <div id="wrapper-lines" class="space-y-4"></div>

                <button type="button" onclick="tambahBarisBarang()" class="w-full py-3 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 font-medium text-xs uppercase tracking-widest hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">
                    <i class="fas fa-plus"></i> Tambah Jenis Barang
                </button>
            </div>

            <div class="w-full lg:w-[36%] lg:sticky lg:top-6 space-y-4">
                <x-card>
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-3">Ringkasan Order</h3>

                    {{-- Diisi/diperbarui oleh jalankanPreviewHarga() setiap kali
                         qty/pilihan barang berubah - lihat ringkasanKosongHtml
                         di bawah untuk pesan default sebelum ada barang sama
                         sekali. --}}
                    <div id="ringkasan-daftar-barang" class="divide-y divide-slate-50">
                        <p class="text-xs text-slate-500 italic text-center py-5">Belum ada barang ditambahkan.</p>
                    </div>

                    <div class="border-t border-slate-100 my-4"></div>

                    <div class="space-y-4">
                        @bolehHarga
                        <x-form.group label="Diskon per Tier (Rp/pcs)">
                            <x-form.input type="text" inputmode="numeric" name="discount_per_tier" value="{{ old('discount_per_tier', 0) }}" class="format-ribuan" />
                        </x-form.group>
                        <x-form.group label="Diskon (Rp)">
                            <x-form.input type="text" inputmode="numeric" name="discount" value="{{ old('discount', 0) }}" class="format-ribuan" />
                        </x-form.group>
                        <x-form.group label="Biaya Tambahan (Rp)">
                            <x-form.input type="text" inputmode="numeric" name="additional_charge" value="{{ old('additional_charge', 0) }}" class="format-ribuan" />
                        </x-form.group>
                        @endbolehHarga
                        <x-form.group label="Deposit / DP (Rp)">
                            <x-form.input type="text" inputmode="numeric" name="deposit" value="{{ old('deposit', 0) }}" class="format-ribuan" />
                        </x-form.group>
                        <x-form.group label="Catatan Umum Order">
                            <textarea name="notes" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ old('notes') }}</textarea>
                        </x-form.group>
                    </div>
                </x-card>

                <div class="flex items-center justify-between gap-4 flex-wrap bg-indigo-600 rounded-2xl px-6 py-4">
                    <span class="text-xs font-medium text-indigo-200 uppercase tracking-widest">Perkiraan Total</span>
                    <span id="preview-total-order" class="text-2xl font-medium text-white">Rp 0</span>
                </div>

                <x-button type="submit" class="w-full !py-4">Simpan Order</x-button>
            </div>
        </div>
    </form>
</div>

{{-- ===================== TEMPLATE 1 BARIS BARANG (kosong) ===================== --}}
<template id="template_baris_barang">
    <div class="jersey-line-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @include('jersey.orders._line-card-fields', ['idx' => '__LINE__', 'line' => null, 'productTypes' => $productTypes, 'categories' => $categories, 'materials' => $materials, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
    </div>
</template>

{{-- ===================== TEMPLATE BARIS PEMAIN (kosong) ===================== --}}
<template id="template_baris_pemain_jersey">
    @include('jersey.orders._player-row-fields', ['prefix' => '__PREFIX__', 'itemIdx' => '__ITEM__', 'item' => null, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
</template>

@push('scripts')
@include('jersey.orders._size-helper')
{{-- Import CSV Daftar Pemain: parser + validasi master (sekali per halaman). --}}
@include('jersey.orders._csv-pemain-tools')
{{-- Nama barang terstruktur: isi otomatis dari pilihan yang sudah dipilih. --}}
@include('partials.nama-barang-jersey-script')
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
        perbaruiVisibilitasLenganKerah(card);
        if (window.tandaiNamaBarangJerseyBaru) tandaiNamaBarangJerseyBaru(card);
        jadwalkanPreviewHarga();
    }

    // ---- Pengunci optimis perpindahan mode order ----
    // Server selalu menghitung ulang harga dari DATABASE (tier
    // jersey_price_settings + surcharge master ukuran/lengan/kerah) - form
    // tidak pernah mengarang angka. Tugas JS di sini menjaga yang TERKIRIM
    // benar-benar milik mode yang dipilih: begitu mode pindah, input milik
    // mode lama DIBUANG (bukan cuma disembunyikan), karena browser tetap
    // mengirim input tersembunyi - dulu inilah sumber "penumpukan
    // perkiraan harga" (qty lama + pemain baru dihitung bersamaan).
    async function toggleLineType(radio) {
        const card = radio.closest('.jersey-line-card');
        const tipe = radio.value;
        const modeLama = card.dataset.modeAktif
            || (function () { var _r = [...card.querySelectorAll('input[name$="[order_type]"]')].find(function (r) { var _l = r.closest('label'); return _l && _l.classList && _l.classList.contains('border-indigo-500'); }); return _r ? _r.value : undefined; })()
            || 'quantity_only';

        const adaQty = [...card.querySelectorAll('.input-qty-ukuran')].some(i => parseInt(i.value) > 0);
        const adaPemain = [...card.querySelectorAll('.baris-pemain')].some(r => r.querySelector('[name*="player_name"]').value.trim() !== '');

        // Mode Campuran tidak membuang apa pun: qty & pemain memang aktif bersamaan.
        const pemainAkanDibuang = tipe === 'quantity_only' && adaPemain;
        const qtyAkanDibuang = tipe === 'player_list' && adaQty;

        if (pemainAkanDibuang || qtyAkanDibuang) {
            const konfirmasi = await Swal.fire({
                title: 'Ganti Mode Order?',
                html: pemainAkanDibuang
                    ? 'Daftar pemain yang sudah diisi akan <b>dikosongkan</b> - mode <b>Qty per Ukuran</b> tidak memakai daftar pemain. Kalau dibiarkan, pemain itu tetap terkirim &amp; ikut terhitung ke harga.'
                    : 'Qty per ukuran yang sudah diisi akan <b>dikosongkan</b> - mode <b>Daftar Pemain</b> tidak memakai qty per ukuran. Kalau dibiarkan, qty itu tetap terkirim &amp; ikut terhitung ke harga (penumpukan harga).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#93481A',
                confirmButtonText: 'Ya, Kosongkan & Ganti Mode',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
            });
            if (!konfirmasi.isConfirmed) {
                card.querySelectorAll('input[name$="[order_type]"]').forEach(r => { r.checked = r.value === modeLama; });
                sinkronkanTampilanMode(card);
                return;
            }
            if (pemainAkanDibuang) card.querySelectorAll('.baris-pemain').forEach(tr => tr.remove());
            if (qtyAkanDibuang) card.querySelectorAll('.input-qty-ukuran').forEach(i => { i.value = 0; });
            if (typeof hitungUlangQtyUkuran === 'function') hitungUlangQtyUkuran();
        }

        // Mode Daftar Pemain: qty per ukuran TIDAK dipakai sama sekali, jadi
        // inputnya dinonaktifkan - bukan cuma disembunyikan. Wrapper `hidden`
        // hanya menyembunyikan tampilan; input di dalamnya tetap terkirim ke
        // server, dan itulah sumber bug qty 35 -> 70 (35 pemain + sisa
        // size_quantities 35 dijumlahkan). Disable = tidak terkirim sama sekali.
        // Mode Campuran tetap memakai keduanya, jadi tidak dinonaktifkan.
        const wrapperQty = card.querySelector('.wrapper-qty-ukuran');
        if (wrapperQty) {
            const bukanModePemain = tipe === 'player_list';
            wrapperQty.classList.toggle('hidden', bukanModePemain);
            wrapperQty.querySelectorAll('input, select').forEach(el => {
                el.disabled = bukanModePemain;
            });
        }
        var _w = card.querySelector('.wrapper-daftar-pemain'); if (_w && _w.classList) _w.classList.toggle('hidden', tipe === 'quantity_only');
        if (tipe !== 'quantity_only' && card.querySelectorAll('.tbody-pemain .baris-pemain').length === 0) {
            tambahBarisPemain(card.querySelector('.wrapper-daftar-pemain button'));
        }
        sinkronkanTampilanMode(card);
        perbaruiVisibilitasLenganKerah(card);
        if (typeof jadwalkanPreviewHarga === 'function') jadwalkanPreviewHarga();
        if (typeof jadwalkanPreviewHargaSusulan === 'function') jadwalkanPreviewHargaSusulan();
    }

    // Warna kartu mode mengikuti radio yang benar-benar terpilih - dipakai
    // juga saat import CSV memindahkan mode secara programatik.
    function sinkronkanTampilanMode(card) {
        const terpilih = card.querySelector('input[name$="[order_type]"]:checked');
        if (terpilih) card.dataset.modeAktif = terpilih.value;
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
        perbaruiVisibilitasLenganKerah(card);
        jadwalkanPreviewHarga();
    }

    // Lengan/Kerah default (level barang) tidak relevan untuk mode Daftar
    // Pemain (selalu pakai pilihan tiap pemain), dan kolom Lengan/Kerah di
    // tabel Daftar Pemain tidak relevan kalau Tipe Produk-nya memang tidak
    // punya lengan/kerah (mis. Celana) - dikelola lewat checkbox "Punya
    // Lengan & Kerah" di Master Data Tipe Produk, bukan hardcode nama.
    function perbaruiVisibilitasLenganKerah(source) {
        // Sumber kebenaran: flag master has_sleeve_collar yang terkirim via
        // data-has-sleeve-collar pada option tipe produk (dashboard Master
        // Tipe Produk -> "Punya Lengan & Kerah"). Tidak ada hardcode nama.
        const card = source.closest ? source.closest('.jersey-line-card') : source;
        if (!card) return;
        var _ot = card.querySelector('input[name$="[order_type]"]:checked');
        const orderType = (_ot ? _ot.value : null) || 'quantity_only';
        var _ops = card.querySelector('select[name$="[jersey_product_type_id]"]');
        const opsiProduk = (_ops && _ops.selectedOptions) ? _ops.selectedOptions[0] : undefined;
        const hasSleeveCollar = (opsiProduk && opsiProduk.value === '')
            || (opsiProduk && opsiProduk.dataset && opsiProduk.dataset.hasSleeveCollar !== '0');
        const tampilkanDefault = orderType !== 'player_list' && hasSleeveCollar;

        // Hanya kelas `hidden` yang sudah ada - TANPA opacity/pointer-events
        // agar kartu tetap bisa diklik normal.
        card.querySelectorAll('.wrapper-lengan-kerah-default').forEach(el => {
            el.classList.toggle('hidden', !tampilkanDefault);
            el.querySelectorAll('select, input').forEach(f => {
                // `required` WAJIB ikut dilepas saat tersembunyikan, kalau
                // tidak Chrome membatalkan submit tanpa pesan apa pun
                // ("... is not focusable" - bug "submit diam-diam batal").
                // Field TIDAK pernah disabled supaya nilai tetap terkirim,
                // khususnya tipe tanpa lengan/kerah yang otomatis diisi
                // nilai "Tanpa" dari master.
                f.required = tampilkanDefault;
                if (tampilkanDefault && f.dataset.autoTanpa === '1') {
                    // Kembali ke tipe berlengan: kosongkan pilihan yang
                    // sebelumnya diisi otomatis agar kasir memilih sadar.
                    f.value = '';
                    delete f.dataset.autoTanpa;
                }
            });
            if (!hasSleeveCollar) {
                el.querySelectorAll('select').forEach(f => {
                    if (f.value === '') {
                        const tanpa = [...f.options].find(o => o.textContent.indexOf('Tanpa') === 0);
                        if (tanpa) { f.value = tanpa.value; f.dataset.autoTanpa = '1'; }
                    }
                });
            }
        });
        card.querySelectorAll('.col-lengan-kerah').forEach(el => el.classList.toggle('hidden', !hasSleeveCollar));
        card.querySelectorAll('.baris-pemain select[name$="[jersey_sleeve_type_id]"], .baris-pemain select[name$="[jersey_collar_type_id]"]').forEach(f => {
            f.required = hasSleeveCollar;
            if (!hasSleeveCollar) {
                if (f.value === '') {
                    const tanpa = [...f.options].find(o => o.textContent.indexOf('Tanpa') === 0);
                    if (tanpa) { f.value = tanpa.value; f.dataset.autoTanpa = '1'; }
                }
            } else if (f.dataset.autoTanpa === '1') {
                f.value = '';
                delete f.dataset.autoTanpa;
            }
        });
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
        var _cd = (e.clipboardData || window.clipboardData);
        const items = _cd ? _cd.items : undefined;
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
        // Dikompres di browser SEBELUM diunggah (lihat compressImage() di
        // partials/image-compress-script.blade.php) - gambar asli beresolusi
        // penuh tidak pernah ikut terkirim ke server, cuma hasil kompresnya
        // (thumbnail ini murni acuan visual produksi, bukan untuk dicetak).
        //
        // Format gambar dikompres sampai 200 KB supaya tetap jernih saat
        // dicetak di SPK. PDF dirender jadi gambar oleh pdf.js. Format berkas
        // desain lain (CDR, AI, EPS) tidak bisa dirender peramban, jadi
        // dikirim apa adanya.
        let fileUpload = file;
        try {
            const prepared = await siapkanBerkasUntukUnggah(file, file.name);
            fileUpload = prepared.file;
        } catch (e) { /* gagal kompres (jarang) - lanjut pakai file asli */ }
        statusEl.textContent = 'Mengunggah...';
        const formData = new FormData();
        formData.append('thumbnail', fileUpload);
        formData.append('_token', '{{ csrf_token() }}');
        try {
            const res = await fetch('{{ route("jersey.orders.upload-thumbnail") }}', { method: 'POST', headers: { 'Accept': 'application/json' }, body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                card.querySelector('.thumbnail-path').value = data.path;
                const img = card.querySelector('.thumbnail-preview');
                if (data.bisa_ditampilkan) {
                    img.src = data.url;
                    img.classList.remove('hidden');
                    card.querySelectorAll('.thumbnail-placeholder').forEach(el => el.classList.add('hidden'));
                    statusEl.textContent = 'Thumbnail siap. Klik kotak untuk mengganti.';
                } else {
                    // Berkas desain non-gambar: kotak pratinjau menampilkan
                    // ikon, bukan gambar rusak. Filenya tetap tersimpan di
                    // server untuk dibuka tim produksi.
                    img.removeAttribute('src');
                    img.classList.add('hidden');
                    const ext = (data.nama_asli || '').split('.').pop() || '';
                    statusEl.textContent = 'Berkas desain siap' + (ext ? ' (' + ext.toUpperCase() + ')' : '') + '. File ada di server, dibuka tim produksi.';
                }
            } else {
                // Pesan galat validasi ada di errors.thumbnail[0] (mis.
                // "Ukuran file terlalu besar (maksimal 10 MB).") - data.message
                // cuma kalimat generik bahasa Inggris dari Laravel.
                const pesan = (data.errors && data.errors.thumbnail && data.errors.thumbnail[0]) || data.message || data.msg || 'Gagal mengunggah, coba lagi.';
                statusEl.textContent = pesan;
                statusEl.classList.add('text-rose-500');
                Swal.fire('Berkas desain belum terunggah', pesan, 'error');
            }
        } catch (e) {
            // Respons bukan JSON (res.json() gagal) - hampir selalu karena body
            // POST melewati post_max_size PHP (40 MB): PHP membuang seluruh
            // unggahan dan menjawab halaman galat, bukan JSON.
            if (fileUpload.size > 30 * 1024 * 1024) {
                statusEl.textContent = 'Ukuran berkas ' + Math.round(fileUpload.size / (1024 * 1024)) + ' MB melampaui batas server (39 MB). Kecilkan dokumen, lalu unggah lagi.';
                Swal.fire('Berkas terlalu besar', statusEl.textContent, 'error');
            } else {
                statusEl.textContent = 'Gagal mengunggah, coba lagi.';
                Swal.fire('Gagal mengunggah', 'Periksa jaringan lalu coba lagi.', 'error');
            }
            statusEl.classList.add('text-rose-500');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        $('.select2-konsumen-jersey').select2({ placeholder: '-- Cari Nama / No. HP --', width: '100%' });
        // Kursor langsung siap di select2 Pelanggan saat form dibuka (fokus DOM
        // murni - API select2('focus') terbukti ikut membuka dropdown). Delay
        // singkat agar pemulihan fokus Chrome setelah reload tidak menimpanya.
        // Urutan Tab tetap mengalir alami (Tanggal -> Pelanggan -> Nama Tim ->
        // Deadline -> File Siap Cetak -> Desainer -> kartu).
        setTimeout(function () {
            var selK = document.getElementById('select_id_konsumen');
            var selSel = selK ? selK.querySelector('.select2-selection') : null;
            if (!selSel && selK && selK.nextElementSibling && selK.nextElementSibling.classList.contains('select2')) {
                selSel = selK.nextElementSibling.querySelector('.select2-selection');
            }
            if (selSel) { selSel.focus(); return; }
            try { window.jQuery(selK).select2('focus'); } catch (e) {}
        }, 400);
        tambahBarisBarang();
    });

    // ---- Perkiraan harga LIVE - dihitung ulang di server (bukan ditiru di
    // JS) tiap field yang memengaruhi harga berubah, supaya angka yang
    // dilihat operator SELALU sama dengan yang akan benar-benar tersimpan
    // (rumus tier/surcharge cukup kompleks untuk ditiru aman di sisi
    // klien). Di-debounce supaya tidak nembak request tiap ketikan. ----
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
            // _method dibuang kalau ada (form ini sebenarnya POST murni,
            // tapi jaga-jaga konsisten dengan edit-lines.blade.php yang
            // punya @method('PUT') - kalau ikut terkirim ke sini, Laravel
            // men-spoof method request preview ini jadi PUT).
            const fd = new FormData(form);
            fd.delete('_method');
            const res = await fetch('{{ route("jersey.orders.preview-harga") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd,
            });
            data = await res.json();
        } catch (e) {
            return; // preview murni bantu-lihat, jangan sampai mengganggu alur form
        }

        // Diskon per Tier bisa membuat harga nol/negatif - server memberi
        // tahu lewat { error: "pesan" }; tampilkan apa adanya ke operator,
        // jangan diam-diam menampilkan Rp 0 palsu.
        if (data && data.error) {
            Swal.fire('Tidak Bisa Dihitung', data.error, 'error');
            const ringkasanEl2 = document.getElementById('ringkasan-daftar-barang');
            if (ringkasanEl2) ringkasanEl2.innerHTML = '<p class="text-xs text-rose-500 font-medium text-center py-5">Tidak bisa dihitung - periksa Diskon per Tier / tier harga.</p>';
            const totalEl2 = document.getElementById('preview-total-order');
            if (totalEl2) totalEl2.textContent = 'Rp 0';
            return;
        }

        const ringkasanEl = document.getElementById('ringkasan-daftar-barang');
        ringkasanEl.innerHTML = '';

        document.querySelectorAll('.jersey-line-card').forEach((card, idx) => {
            const m = (card.dataset.prefix || '').match(/\[(.+)\]/);
            const info = m ? (data.lines ? data.lines[m[1]] : null) : null;
            const nilaiEl = card.querySelector('.line-price-preview-value');
            const detailEl = card.querySelector('.line-price-preview-detail');
            if (nilaiEl) {
                if (!info) {
                    nilaiEl.textContent = 'Rp 0';
                    if (detailEl) detailEl.textContent = 'Isi qty & pilihan barang untuk lihat perkiraan harga.';
                } else {
                    nilaiEl.textContent = 'Rp ' + Math.round(info.subtotal).toLocaleString('id-ID');
                    // Teks detail sekarang hanya menyimpan CATATAN (tier belum
                    // cocok, pemain belum pilih ukuran) - rincian angkanya
                    // pindah ke daftar line-price-preview-list di bawah, satu
                    // baris per komponen harga.
                    const catatan = [];
                    if (!info.ada_tier) catatan.push('belum ada tier harga yang cocok');
                    if (info.pemain_tanpa_ukuran > 0) catatan.push(`${info.pemain_tanpa_ukuran} pemain belum pilih ukuran (dihitung harga dewasa)`);
                    if (detailEl) detailEl.textContent = catatan.join(', ') || (info.tier_label ? `Tier ${info.tier_label}` : '');
                }
            }

            // Daftar rincian harga - satu baris per komponen (harga dasar per
            // ukuran, selisih ukuran, lengan/kerah). Dibangun via createElement/
            // textContent (bukan innerHTML string) dengan pola yang sama seperti
            // baris ringkasan sidebar, supaya label dari master data tidak pernah
            // dianggap markup.
            const listEl = card.querySelector('.line-price-preview-list');
            if (listEl) {
                listEl.innerHTML = '';
                const barisRincian = (info && Array.isArray(info.rincian)) ? info.rincian : [];
                for (const r of barisRincian) {
                    if (r == null || r.amount == null) continue;
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between gap-3 py-1';
                    const kiri = document.createElement('span');
                    kiri.className = 'min-w-0';
                    kiri.textContent = r.label;
                    const kanan = document.createElement('span');
                    kanan.className = 'font-medium tabular-nums shrink-0';
                    const nominal = Math.round(r.amount);
                    kanan.textContent = (nominal < 0 ? '−Rp ' : 'Rp ') + Math.abs(nominal).toLocaleString('id-ID');
                    li.append(kiri, kanan);
                    listEl.appendChild(li);
                }
                listEl.classList.toggle('hidden', barisRincian.length === 0);
            }

            // Baris ringkasan di sidebar - dibangun via createElement/textContent
            // (bukan innerHTML string) supaya nama barang yang diketik operator
            // tidak pernah dianggap markup, walau isinya aneh-aneh.
            var _nb = card.querySelector('input[name$="[nama_barang]"]');
            const namaBarang = (_nb ? _nb.value.trim() : '') || `Barang #${idx + 1}`;
            var _ts = card.querySelector('.thumbnail-preview:not(.hidden)');
            const thumbSrc = (_ts ? _ts.src : '') || '';
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
                thumbWrap.innerHTML = '<i class="fas fa-tshirt text-slate-500 text-xs"></i>';
            }
            baris.appendChild(thumbWrap);

            const infoWrap = document.createElement('div');
            infoWrap.className = 'flex-1 min-w-0';
            const namaEl = document.createElement('p');
            namaEl.className = 'text-xs font-medium text-slate-700 truncate';
            namaEl.textContent = namaBarang;
            const qtyEl = document.createElement('p');
            qtyEl.className = 'text-[10px] text-slate-400';
            qtyEl.textContent = `${qty} pcs`;
            infoWrap.append(namaEl, qtyEl);
            baris.appendChild(infoWrap);

            const subtotalEl = document.createElement('span');
            subtotalEl.className = 'text-xs font-medium text-slate-600 shrink-0';
            subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            baris.appendChild(subtotalEl);

            ringkasanEl.appendChild(baris);
        });

        if (!ringkasanEl.children.length) {
            ringkasanEl.innerHTML = '<p class="text-xs text-slate-500 italic text-center py-5">Belum ada barang ditambahkan.</p>';
        }

        const totalEl = document.getElementById('preview-total-order');
        if (totalEl) totalEl.textContent = 'Rp ' + Math.round(data.total_order || 0).toLocaleString('id-ID');
    }

    document.getElementById('jersey-order-form').addEventListener('input', (e) => {
        if (e.target.closest('.jersey-line-card') || e.target.name === 'discount' || e.target.name === 'discount_per_tier' || e.target.name === 'additional_charge') {
            jadwalkanPreviewHarga();
        }
    });
    document.getElementById('jersey-order-form').addEventListener('change', (e) => {
        if (e.target.closest('.jersey-line-card')) jadwalkanPreviewHarga();
    });

    // Penanda submit sedang berjalan. Tanpa ini, klik "Simpan Order" dua
    // kali saat request pertama masih diproses menghasilkan 2 POST ke
    // jersey.orders.store - jadi 2 order + 2 detail order.
    let sedangMenyimpan = false;

    document.getElementById('jersey-order-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        if (sedangMenyimpan) return;

        // Baris pemain kosong dibuang dulu - baris tersembunyi (mis. mode
        // diganti ke Qty per Ukuran) tetap terkirim ke server kalau tidak
        // dibersihkan.
        document.querySelectorAll('.baris-pemain').forEach(row => {
            if (row.querySelector('[name*="player_name"]').value.trim() === '') row.remove();
        });

        const cards = document.querySelectorAll('.jersey-line-card');
        if (cards.length === 0) {
            Swal.fire('Belum Ada Barang', 'Tambahkan minimal 1 jenis barang sebelum menyimpan.', 'warning');
            return;
        }

        // Audit kelengkapan (dropdown wajib terisi): Tipe Produk, Kategori,
        // Bahan, Ukuran pemain, dan qty-per-ukuran WAJIB - blokir simpan dgn
        // notif daftar kekurangan + tandai field yang kosong (ring merah),
        // bukan cuma mengandalkan validasi server. Lengan/Kerah default tetap
        // opsional (produk bisa tanpa lengan/kerah) - dilengkapi peringatan
        // non-blocking dari cek-kelengkapan seperti sebelumnya.
        const kurang = [];
        document.querySelectorAll('.jersey-line-card').forEach(card => {
            card.querySelectorAll('input, select').forEach(el => el.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/40'));
            const nomor = card.querySelector('.line-number').textContent;
            const namaBarang = card.querySelector('[name*="[nama_barang]"]').value.trim() || `Barang #${nomor}`;

            const wajibPilih = [
                ['[name*="[jersey_product_type_id]"]', 'Tipe Produk'],
                ['[name*="[jersey_category_id]"]', 'Kategori'],
                ['[name*="[jersey_material_id]"]', 'Bahan'],
            ];
            for (const [selector, label] of wajibPilih) {
                const el = card.querySelector(selector);
                if (el && !el.value) {
                    kurang.push(`${namaBarang}: ${label} belum dipilih`);
                    el.classList.add('ring-2', 'ring-rose-400', 'bg-rose-50/40');
                }
            }

            var _lt = card.querySelector('input.line-type-option:checked');
            const tipe = (_lt ? _lt.value : null) || 'quantity_only';
            const adaQtyUkuran = [...card.querySelectorAll('.input-qty-ukuran')].some(i => parseInt(i.value) > 0);
            const pemain = [...card.querySelectorAll('.baris-pemain')].filter(r => r.querySelector('[name*="player_name"]').value.trim() !== '');
            const pemainTanpaUkuran = pemain.filter(r => !r.querySelector('[name*="[size]"]').value);
            pemainTanpaUkuran.forEach(r => r.querySelector('[name*="[size]"]').classList.add('ring-2', 'ring-rose-400', 'bg-rose-50/40'));

            if (pemain.length > 0 && pemainTanpaUkuran.length > 0) {
                kurang.push(`${namaBarang}: ${pemainTanpaUkuran.length} pemain belum memilih Ukuran`);
            }
            if (tipe !== 'player_list' && !adaQtyUkuran && pemain.length === 0) {
                kurang.push(`${namaBarang}: belum ada qty ukuran maupun daftar pemain`);
            }
        });

        if (kurang.length > 0) {
            const daftar = kurang.map(k => `<li>${escHtml(k)}</li>`).join('');
            Swal.fire({
                title: 'Lengkapi Data Pesanan Lebih Dulu',
                html: `<ul style="text-align:left; font-size:12.5px; padding-left:1.2em;">${daftar}</ul>`,
                icon: 'warning',
                confirmButtonText: 'Ya, Saya Lengkapi',
                customClass: { confirmButton: 'rounded-lg px-6 py-2.5' },
            });
            return;
        }

        // Thumbnail WAJIB per barang - dicek di sini juga (bukan cuma
        // required HTML) supaya pesannya jelas menunjuk barang yang mana.
        for (const card of cards) {
            if (card.querySelector('.thumbnail-path').value.trim() === '') {
                const nomor = card.querySelector('.line-number').textContent;
                Swal.fire('Thumbnail Belum Diisi', `Barang #${nomor} belum ada thumbnail desain - wajib diisi sebagai acuan warna & bentuk produksi.`, 'warning');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        // Cek kelengkapan komponen penunjang profesionalitas kerja (harga
        // dari tier belum ketemu, bahan belum dipilih, dst) - warn don't
        // block, sama seperti pola "Subtotal Item Ini Jadi Rp 0?" di form
        // pembayaran Kasir. Order tetap bisa disimpan kalau operator yakin.
        let peringatan = [];
        try {
            const res = await fetch('{{ route('jersey.orders.cek-kelengkapan') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(this),
            });
            const data = await res.json();
            peringatan = data.peringatan || [];
        } catch (err) {
            peringatan = []; // gagal cek (mis. jaringan) - jangan sampai memblokir penyimpanan
        }

        if (peringatan.length > 0) {
            const daftar = peringatan.map(p => `<li>${escHtml(p)}</li>`).join('');
            const konfirmasi = await Swal.fire({
                title: 'Ada Komponen Belum Lengkap',
                html: `<ul style="text-align:left; font-size:12.5px; padding-left:1.2em;">${daftar}</ul>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#93481A',
                confirmButtonText: 'Ya, Simpan Sekarang',
                cancelButtonText: 'Batal, Saya Cek Lagi',
                customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
            });
            if (!konfirmasi.isConfirmed) return;
        }

        // form.submit() (native) TIDAK memicu event 'submit' - listener
        // pembersih format-ribuan di partials.rupiah-format-script (yang
        // biasanya jalan otomatis di form lain) jadi ikut terlewat,
        // akibatnya Diskon/Biaya Tambahan/Deposit terkirim mentah dengan
        // titik ribuan (mis. "150.000" kebaca PHP sebagai desimal 150).
        // Dibersihkan manual di sini sebelum submit sungguhan.
        this.querySelectorAll('input.format-ribuan').forEach(input => {
            input.value = cleanRupiah(input.value);
        });

        // ---- Loading state ----
        // Disetel tepat sebelum this.submit() (navigasi penuh), supaya tidak
        // pernah tertahan kalau ada validasi di atas yang menolak. Tombol
        // dinonaktifkan sebagai pagar kedua; sedangMenyimpan sudah menolak
        // klik kedua sebelum spinner sempat tampil.
        sedangMenyimpan = true;
        const btnSimpan = this.querySelector('button[type="submit"]');
        if (btnSimpan) {
            btnSimpan.disabled = true;
            btnSimpan.classList.add('opacity-70', 'cursor-not-allowed');
            btnSimpan.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...';
        }

        // Pagar pengaman: kalau navigasi entah bagaimana tidak terjadi, form
        // jangan sampai terkunci selamanya dengan tombol mati.
        setTimeout(() => {
            if (!btnSimpan) return;
            sedangMenyimpan = false;
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-70', 'cursor-not-allowed');
            btnSimpan.innerHTML = 'Simpan Order';
        }, 20000);

        this.submit();
    });
</script>
@endpush
@endsection
