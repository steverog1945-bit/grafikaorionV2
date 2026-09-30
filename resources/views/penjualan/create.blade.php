@extends('layouts.app')
@section('content')
@include('partials.rupiah-format-script')
@include('partials.image-compress-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    @if(!empty($quotationRef))
    <div class="mb-6 p-5 rounded-2xl bg-amber-50 border border-amber-200">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div class="text-sm font-medium text-amber-800"><i class="fas fa-file-signature"></i> Order dari Penawaran {{ $quotationRef->nomor }} - {{ $quotationRef->nama_pelanggan }}</div>
            <div class="text-sm font-medium text-amber-800">Total penawaran Rp {{ number_format($quotationRef->total, 0, ',', '.') }}{{ $quotationRef->diskon > 0 ? ' (sudah diskon Rp ' . number_format($quotationRef->diskon, 0, ',', '.') . ')' : '' }}</div>
        </div>
        <ul class="text-xs text-amber-900 space-y-1">
            @foreach($quotationRef->items as $it)
            <li>&bull; <b>{{ $it->nama_item }}</b>
                {{ $it->tipe === 'Produk' && $it->p_ukuran > 0 ? rtrim(rtrim($it->p_ukuran, '0'), '.') . ' x ' . rtrim(rtrim($it->l_ukuran, '0'), '.') . ' cm' : '' }}
                &times; {{ angka($it->qty, 2) }}
                = Rp {{ number_format($it->subtotal, 0, ',', '.') }}{{ $it->catatan ? ' - ' . $it->catatan : '' }}</li>
            @endforeach
        </ul>
        <p class="text-[11px] text-amber-700 mt-3">Input item di bawah sesuai penawaran ini. Setelah order tersimpan, penawaran otomatis berstatus "Menjadi Order".</p>
    </div>
    @endif

    <x-page-header>
        <x-slot:title>INPUT <span class="text-indigo-600">ORDER</span></x-slot:title>
        <x-slot:subtitle>
            <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
            Order Cetak Reguler &bull; {{ date('d M Y') }}
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ route('penjualan.riwayat') }}" variant="outline">
                <i class="fas fa-receipt"></i> Riwayat Nota
            </x-button>
            <div class="bg-white shadow-sm border border-slate-100 rounded-2xl px-5 sm:px-8 py-3.5 flex items-center gap-4 sm:gap-6 max-w-full">
                <div class="text-right min-w-0">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Nomor Nota</p>
                    <p id="no_nota_display" class="text-base sm:text-xl font-medium text-indigo-600 tracking-tight truncate">{{ $no_nota }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-500 hidden sm:flex items-center justify-center shrink-0"><i class="fas fa-file-invoice"></i></span>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="flex flex-col lg:flex-row gap-6 items-start">

        <div class="w-full lg:w-[58%] space-y-6">
            {{-- ============ DATA PELANGGAN ============ --}}
            <x-card>
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fas fa-user"></i></span>
                    <div>
                        <h3 class="text-base font-medium text-slate-800">Data Pelanggan</h3>
                        <p class="text-[11px] text-slate-400">Siapa pemesan dan jenis ordernya</p>
                    </div>
                </div>

                {{-- md:grid-cols-4: Cari Pelanggan 2 kolom, Nomor Telepon & Tipe Harga masing-masing 1
                     (kartu ini sempit saat keranjang di kanan aktif, jadi select2 pelanggan diberi porsi terbesar). --}}
                {{-- Lebar tata letak mengikuti lebar KARTU (container query), bukan lebar layar: Cari Pelanggan selalu satu baris penuh
                     agar tetap besar & jelas; pasangan kolom hanya dipakai bila kartu cukup lebar, sehingga tidak saling bertabrakan. --}}
                {{-- Dua kolom rapat agar tidak perlu scroll: Cari Pelanggan selebar baris; Nomor Telepon | Tipe Harga; Tipe Order | Tanggal Order
                     (tanggal tepat di bawah Tipe Harga); No. PO muncul selebar baris hanya bila tipe order = PO. Tetap satu kolom bila kartu sangat sempit. --}}
                <div class="@container">
                    <div class="grid grid-cols-1 @md:grid-cols-2 gap-x-4 gap-y-3.5">
                        <div class="@md:col-span-2">
                            <x-form.group>
                        <div class="flex items-center justify-between mb-2 ml-2 mr-1">
                            <label class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Cari Pelanggan</label>
                            <button type="button" onclick="openModal('konsumen-baru')" title="Daftarkan pelanggan baru" class="text-indigo-600 hover:text-indigo-700 font-medium text-[11px] flex items-center gap-1">
                                <i class="fas fa-plus text-[9px]"></i> Baru
                            </button>
                        </div>
                        {{-- Pelanggan = select2 AJAX (search-ajax, limit 20):
                             dataset konsumen puluhan ribu TIDAK lagi diseret ke
                             browser tiap buka POS. Konsumen awal (redirect
                             ?id_konsumen=) dirender server supaya langsung tampil. --}}
                        <select id="id_konsumen" name="id_konsumen" class="select2-konsumen">
                            <option value="">Cari Nama / No. HP...</option>
                            @foreach($konsumenAwal as $k)
                                <option value="{{ $k->id_konsumen }}"
                                        data-nama="{{ $k->nama_konsumen }}"
                                        data-telp="{{ $k->telepon }}"
                                        data-golongan="{{ $k->id_golongan }}">
                                    {{ $k->nama_konsumen }}
                                </option>
                            @endforeach
                        </select>
                    </x-form.group>
                        </div>
                        <x-form.group label="Nomor Telepon">
                        <input type="text" id="display_telp" class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-500 shadow-inner" readonly placeholder="-">
                    </x-form.group>
                        <x-form.group label="Tipe Harga">
                        <x-form.select id="id_golongan">
                            @foreach($golongan as $g)
                                <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                            @endforeach
                        </x-form.select>
                    </x-form.group>
                        <x-form.group label="Tipe Order">
                        <div class="flex gap-2">
                            <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 px-3 text-xs font-medium cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-600 text-slate-500 transition-all">
                                <input type="radio" name="tipe_order" value="Reguler" checked onchange="onTipeOrderChange()" class="accent-indigo-600"> Reguler
                            </label>
                            <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 px-3 text-xs font-medium cursor-pointer has-[:checked]:bg-amber-50 has-[:checked]:border-amber-300 has-[:checked]:text-amber-600 text-slate-500 transition-all">
                                <input type="radio" name="tipe_order" value="PO" onchange="onTipeOrderChange()" class="accent-amber-600"> PO
                            </label>
                        </div>
                    </x-form.group>
                        <x-form.group label="Tanggal Order">
                        <x-form.input type="date" id="tgl_order" value="{{ date('Y-m-d') }}" />
                    </x-form.group>
                        <x-form.group label="No. PO" id="wrapper_no_po" class="hidden @md:col-span-2">
                        <x-form.input type="text" id="no_po" placeholder="Nomor PO dari konsumen" />
                    </x-form.group>
                    </div>
                </div>
            </x-card>

            {{-- ============ DETAIL PESANAN ============ --}}
            <x-card class="@container">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-box-open"></i></span>
                    <div>
                        <h3 class="text-base font-medium text-slate-800">Detail Pesanan</h3>
                        <p class="text-[11px] text-slate-400">Produk, ukuran, finishing, dan biaya item</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3.5 mb-3.5">
                    <x-form.group label="Kategori">
                        <x-form.select id="filter_kategori" onchange="filterProduk(this.value)">
                            <option value="all">Semua Kategori</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </x-form.select>
                    </x-form.group>
                    <x-form.group label="Pilih Produk">
                        <select id="select_produk" onchange="getDetailProduk()" class="select2-produk w-full" disabled>
                            <option value="">-- Ketik Nama Produk --</option>
                            @foreach($produk as $p)
                                <option value="{{ $p->id_produk }}" data-kategori="{{ $p->id_kategori }}" data-metode="{{ $p->metode_hitung }}" data-siap-cetak="{{ in_array((int) $p->id_produk, $produkSiapCetak, true) ? 1 : 0 }}">{{ $p->nama_produk }}</option>
                            @endforeach
                        </select>
                        {{-- Produk TERKUNCI sampai Kategori sungguhan dipilih (bukan "Semua Kategori"). Lihat filterProduk(). --}}
                        <p id="hint_kunci_produk" class="text-[11px] text-amber-600 mt-1.5 ml-2">
                            <i class="fas fa-lock text-[10px]"></i> Pilih Kategori dulu untuk mengaktifkan pilihan produk.
                        </p>
                    </x-form.group>
                </div>

                {{-- Jenis Pengerjaan: hanya tampil untuk produk yang punya Harga File Siap Cetak (menu Master > Harga File Siap Cetak) --}}
                <div id="wrapper_siap_cetak" class="hidden mb-3.5">
                    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Jenis Pengerjaan</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 px-3 text-xs font-medium cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-600 text-slate-500 transition-all" title="Harga Daftar Harga biasa; boleh ditambah biaya desain">
                            <input type="radio" name="jenis_pengerjaan" value="desain" checked onchange="setSiapCetak(false)" class="accent-indigo-600"> Dengan Desain
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 px-3 text-xs font-medium cursor-pointer has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-300 has-[:checked]:text-emerald-600 text-slate-500 transition-all" title="File sudah jadi dari pelanggan: memakai harga File Siap Cetak tersendiri, tanpa biaya desain &amp; komisi desainer">
                            <input type="radio" name="jenis_pengerjaan" value="siap" onchange="setSiapCetak(true)" class="accent-emerald-600"> File Siap Cetak
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3.5 mb-3.5">
                    <x-form.group label="Nama File / Deskripsi">
                    <x-form.input type="text" id="nama_file" placeholder="Contoh: Banner Spanduk 3x1m" />
                </x-form.group>
                    <x-form.group label="Thumbnail Gambar" required>
                        <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-3 py-2 shadow-inner">
                            <input type="file" id="input_thumbnail" accept="image/png,image/jpeg,image/webp" onchange="uploadThumbnail(this)" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-medium file:uppercase file:bg-indigo-50 file:text-indigo-600">
                            {{-- Tag ini sebelumnya TIDAK ditutup: penutup '>' hilang,
                                 sehingga parser HTML menelani </div> berikutnya sebagai
                                 bagian dari tag img. Akibatnya satu </div> hilang,
                                 seluruh isi kartu produk (ukuran, finishing, biaya,
                                 tombol) jadi ter-nest satu level terlalu dalam, dan
                                 kolom kiri ikut menelan keranjang. width/height
                                 ditulis supaya kotak 44x44 sudah ada sebelum gambar
                                 dimuat - tanpa itu, gambar melompat saat muncul. --}}
                            <img loading="lazy" decoding="async" width="44" height="44" alt="Pratinjau thumbnail desain" id="thumbnail_preview" class="hidden w-11 h-11 rounded-lg object-cover border border-slate-200">
                        </div>
                    </x-form.group>
                </div>

                <div id="wrapper_ukuran" class="hidden grid grid-cols-3 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-3">
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Panjang (cm)</label>
                        <input type="number" id="input_p" onchange="hitungSubtotal()" class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Lebar (cm)</label>
                        <input type="number" id="input_l" onchange="hitungSubtotal()" class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Qty</label>
                        <input type="number" id="input_qty_ukuran" onchange="hitungSubtotal()" class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20" value="1">
                    </div>
                </div>
                <p id="note_ukuran" class="hidden text-xs font-medium text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5 mb-4 leading-relaxed">
                    <i class="fas fa-info-circle mr-1"></i>
                    Ukuran untuk menghitung harga bukan ukuran file asli. Silakan ubah ukuran jika bahan memerlukan penyesuaian.
                </p>

                <div id="wrapper_qty" class="hidden mb-4">
                    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Jumlah Unit</label>
                    <input type="number" id="input_qty_biasa" onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-xl font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20" value="1">
                </div>

                <div class="grid grid-cols-2 gap-3.5 mb-4">
                    <x-form.group label="Status Urgensi">
                        <x-form.select id="input_status_urgensi">
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Very Urgent">Very Urgent</option>
                        </x-form.select>
                    </x-form.group>
                    <x-form.group label="Catatan Produksi">
                    <textarea id="input_catatan_produksi" rows="1" placeholder="Catatan khusus untuk tim produksi (opsional)" class="w-full min-h-[46px] resize-none bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
                </x-form.group>
                </div>

                

                {{-- Indikator stok BOM - PERINGATAN saja, tidak memblokir order --}}
                <div id="stok_warning" hidden class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="text-xs font-medium text-amber-700 uppercase tracking-wide">Stok bahan tidak cukup</p>
                        <p id="stok_warning_detail" class="text-xs text-amber-600 mt-1"></p>
                    </div>
                </div>

                {{-- Indikator di bawah ukuran/qty minimum produk - PERINGATAN saja --}}
                <div id="min_order_warning" hidden class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-ruler-combined text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="text-xs font-medium text-amber-700 uppercase tracking-wide">Di bawah minimum order produk ini</p>
                        <p id="min_order_warning_detail" class="text-xs text-amber-600 mt-1"></p>
                    </div>
                </div>

                {{-- ---------- ESTIMASI HARGA ---------- --}}
                <div class="rounded-3xl bg-indigo-50 border border-indigo-100 px-6 py-5 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <p class="text-[10px] font-medium text-indigo-400 uppercase tracking-widest">Harga Estimasi</p>
                        <h2 id="display_subtotal" class="text-3xl font-medium text-indigo-600 tracking-tight mt-1">Rp 0</h2>
                    </div>
                    <div class="sm:text-right">
                        <p id="display_harga" class="text-indigo-400 text-xs font-medium">Rp 0 /unit</p>
                        <p id="status_hitung" class="hidden text-amber-600 text-xs font-medium mt-1"><i class="fas fa-circle-notch fa-spin mr-1"></i> Menghitung...</p>
                        <span id="badge_promo" hidden class="inline-block mt-2 px-3 py-1 bg-rose-500 text-white text-[10px] font-medium uppercase tracking-widest rounded-full">🔥 Harga Promo Aktif</span>
                    </div>
                </div>

                {{-- ---------- FINISHING & BIAYA ---------- --}}
                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-3 ml-1">Finishing &amp; Biaya</p>
                <div class="grid grid-cols-2 @xl:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Finishing</label>
                        <select id="select_finishing" onchange="onFinishingChange()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                            <option value="">-- Tanpa Finishing --</option>
                            @foreach($finishing as $f)
                                <option value="{{ $f->id_finishing }}" data-harga="{{ $f->harga_default ?? 0 }}" data-metode="{{ $f->metode_hitung }}" data-kategori="{{ $f->id_kategori ?? '' }}">{{ $f->nama_finishing }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Spek Finishing</label>
                        <select id="input_status_finishing" onchange="onStatusFinishingChange()" disabled class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner disabled:opacity-40 disabled:cursor-not-allowed">
                            <option value="Standar">Standar</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Catatan Finishing</label>
                        <input type="text" id="input_catatan_finishing" maxlength="255" placeholder="mis. Doff" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    </div>
                    {{-- Biaya Cetak = komponen UTAMA subtotal (Cetak + Desain + Finishing, lihat hitungSubtotal()). Mengikuti Harga x Qty
                         sampai operator mengetik manual (cetakManualEdit). --}}
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Biaya Cetak (Rp)</label>
                        <input type="text" inputmode="numeric" id="input_cetak" @unlessbolehHarga readonly @endbolehHarga onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan" value="0">
                    </div>
                    {{-- Jumlah pcs finishing hanya untuk finishing metode 'quantity' (mis. Pasang Mata Ayam) - kasir wajib cek/sesuaikan. --}}
                    <div id="wrapper_qty_finishing" class="hidden">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Jumlah Pcs Finishing</label>
                        <input type="number" min="1" id="input_qty_finishing" onchange="onQtyFinishingManualChange()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner" value="1">
                    </div>
                    {{-- Biaya Finishing hanya muncul begitu jenis Finishing dipilih (lihat onFinishingChange()). --}}
                    <div id="wrapper_biaya_finishing" class="hidden">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Biaya Finishing (Rp)</label>
                        <input type="text" inputmode="numeric" id="input_finishing_biaya" @unlessbolehHarga readonly @endbolehHarga onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan" value="0">
                    </div>
                    {{-- Biaya Desain disembunyikan default; muncul lewat tombol "+ Biaya Desain" (tampilkanBiayaDesain()). --}}
                    <div id="wrapper_biaya_desain" class="hidden">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Biaya Desain (Rp)</label>
                        <input type="text" inputmode="numeric" id="input_desain" @unlessbolehHarga readonly @endbolehHarga onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan" value="0">
                    </div>
                    {{-- Desainer yang menerapkan biaya desain (untuk monitoring & komisi/bagi hasil) --}}
                    <div id="wrapper_desainer" class="hidden" title="Operator desain yang mengerjakan desain ini - dipakai untuk laporan biaya &amp; komisi desain">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Desainer</label>
                        <select id="input_desainer" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                            <option value="">-- Tanpa Desainer --</option>
                            @foreach($desainerList as $ds)
                                <option value="{{ $ds->id_user }}" {{ (int) $desainerDefault === (int) $ds->id_user ? 'selected' : '' }}>{{ $ds->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" id="btn_tambah_finishing" onclick="tambahFinishingKeDaftar()" class="hidden inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 rounded-xl font-medium text-[10px] uppercase tracking-widest shadow-lg shadow-sky-100 transition-all">
                            <i class="fas fa-plus"></i> Tambah Finishing
                        </button>
                        <button type="button" id="btn_tambah_desain" @unlessbolehHarga hidden @endbolehHarga onclick="tampilkanBiayaDesain()" class="text-indigo-500 hover:text-indigo-700 text-[10px] font-medium uppercase tracking-widest underline underline-offset-4 decoration-dotted">
                            <i class="fas fa-plus-circle"></i> Biaya Desain
                        </button>
                    </div>
                    <button onclick="tambahKeKeranjang()" class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-3.5 rounded-xl font-medium text-xs uppercase tracking-wider shadow-lg shadow-emerald-100 transition-all active:scale-95">
                        Tambah
                    </button>
                </div>

                {{-- Daftar finishing untuk item ini (boleh lebih dari satu, mis. Laminasi + Kisscut) --}}
                <div id="finishing_list_box" class="hidden mt-4 rounded-2xl border border-slate-100 bg-white overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 flex items-center justify-between">
                        <span class="text-[10px] font-medium uppercase tracking-widest text-slate-500">Finishing item ini</span>
                        <span class="text-xs font-medium text-slate-600">Total Finishing: <b id="finishing_list_total" class="text-slate-900">Rp 0</b></span>
                    </div>
                    <div id="finishing_list_rows" class="divide-y divide-slate-50"></div>
                </div>
            </x-card>

            {{-- ============ TAMBAH JASA (accordion, tertutup default; isi tidak berubah) ============ --}}
            <x-card :padding="false" class="@container">
                <button type="button" onclick="toggleTambahJasa()" class="w-full flex items-center gap-3 p-6 text-left">
                    <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0"><i class="fas fa-hand-holding-heart"></i></span>
                    <div>
                        <h3 class="text-base font-medium text-slate-800">Tambah Jasa</h3>
                        <p class="text-[11px] text-slate-400">Layanan non-material, harga manual</p>
                    </div>
                    <i id="icon_toggle_jasa" class="fas fa-chevron-down text-slate-300 ml-auto transition-transform"></i>
                </button>

                <div id="wrapper_tambah_jasa" class="hidden px-6 pb-6">
                    <div class="grid grid-cols-1 @md:grid-cols-3 gap-4 mb-4">
                        <x-form.group label="Pilih Jasa">
                            <select id="select_jasa" onchange="onJasaChange()" class="select2-produk w-full">
                                <option value="">-- Ketik Nama Jasa --</option>
                                @foreach($jasa as $j)
                                    <option value="{{ $j->id_jasa }}" data-nama="{{ $j->nama_jasa }}">{{ $j->nama_jasa }}{{ $j->nama_satuan ? ' ('.$j->nama_satuan.')' : '' }}</option>
                                @endforeach
                            </select>
                        </x-form.group>
                        <x-form.group label="Qty">
                            <x-form.input type="number" id="input_jasa_qty" value="1" oninput="refreshJasaHargaSuggestion()" />
                        </x-form.group>
                        <x-form.group label="Harga Satuan (Rp)">
                            <x-form.input type="text" inputmode="numeric" id="input_jasa_harga" placeholder="0" :readonly="!\App\Support\AksesHarga::bolehLihat()" class="format-ribuan" />
                        </x-form.group>
                    </div>

                    <x-form.group label="Keterangan" class="mb-4">
                        <x-form.input type="text" id="jasa_keterangan" placeholder="Contoh: Instalasi banner outdoor" />
                    </x-form.group>

                    <div class="flex justify-end">
                        <button onclick="tambahJasaKeKeranjang()" class="inline-flex items-center justify-center gap-2 bg-purple-500 hover:bg-purple-600 text-white px-8 py-3.5 rounded-xl font-medium text-xs uppercase tracking-wider shadow-lg shadow-purple-100 transition-all active:scale-95">
                            Tambah Jasa
                        </button>
                    </div>
                </div>
            </x-card>

            {{-- Penutup kolom kiri. Tanpa baris ini kolom kiri tidak pernah
                 tertutup, sehingga keranjang ikut jadi ANAK kolom kiri dan
                 duduk di bawah dua kartu (sekitar 1600px dari atas halaman).
                 Akibatnya kasir harus menggulir halaman hanya untuk melihat
                 keranjang, dan sticky tidak pernah aktif karena posisinya
                 belum tercapai. Satu baris ini yang mengembalikan keranjang
                 ke kolom kanannya. --}}
        </div>

        {{-- ============ KERANJANG - sticky agar Total & tombol Simpan selalu terlihat; daftar item scroll sendiri ============ --}}
        <div class="w-full lg:w-[42%] flex flex-col lg:sticky lg:top-24 lg:self-start">
            {{-- max-h SEHARUSNYA 100vh dikurangi tinggi top (top-24 = 6rem)
                 dikurangi jarak aman di bawah. Sebelumnya 8rem: itu 128px,
                 padahal ruang di bawah top hanya 100vh - 96px, jadi bagian
                 bawah keranjang selalu keluar layar ~32px - tombol Simpan
                 justru yang paling penting dan justru terpotong. --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col overflow-hidden lg:max-h-[calc(100vh-7rem)]">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fas fa-shopping-basket"></i></span>
                        <h3 class="text-base font-medium text-slate-800">Keranjang Pesanan</h3>
                    </div>
                    <div id="item_count_badge" class="bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg text-[11px] font-medium uppercase tracking-wider">0 Items</div>
                </div>

                <div class="flex-grow overflow-y-auto custom-scrollbar p-0 min-h-[120px]">
                    <div id="cart_body" class="divide-y divide-slate-50 text-slate-600">
                        <div class="py-16 text-center opacity-30">
                            <i class="fas fa-receipt text-2xl mb-2 block"></i>
                            <p class="text-xs font-medium uppercase tracking-widest">Belum Ada Pesanan</p>
                        </div>
                    </div>
                </div>

                <div class="mt-auto p-6 bg-slate-50 border-t border-slate-100 shrink-0">
                    <div class="flex justify-between items-center mb-5">
                        <div>
                            <p class="text-slate-400 text-[10px] font-medium uppercase tracking-widest mb-1">Total Pembayaran</p>
                            <h2 id="grand_total" class="text-3xl font-medium text-indigo-600 tracking-tight leading-none">Rp 0</h2>
                        </div>
                        <button onclick="resetCart()" title="Kosongkan keranjang" class="w-11 h-11 flex items-center justify-center bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <button type="button" onclick="simpanTransaksi(this)" class="w-full inline-flex items-center justify-center gap-2 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium text-xs uppercase tracking-wider shadow-lg shadow-indigo-100 transition-all active:scale-95">
                        <i class="fas fa-print"></i> Simpan &amp; Cetak Nota
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONSUMEN BARU --}}
<x-modal id="konsumen-baru" title="Daftar Konsumen Baru" maxWidth="md">
    <div class="space-y-5">
        <x-form.group label="Nama Lengkap">
            <x-form.input type="text" id="new_nama" placeholder="Masukkan nama pelanggan..." />
        </x-form.group>
        <x-form.group label="Nomor WhatsApp">
            <x-form.input type="number" id="new_hp" placeholder="0812xxxx" />
        </x-form.group>
        <x-form.group label="Tipe Harga (Golongan)">
            <x-form.select id="new_id_golongan">
                @foreach($golongan as $g)
                    <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                @endforeach
            </x-form.select>
        </x-form.group>
        <x-form.group label="Tipe Konsumen">
            <div class="flex gap-2">
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-xs font-medium cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-600 text-slate-500">
                    <input type="radio" name="new_tipe_konsumen" value="temporary" checked class="accent-indigo-600"> Temporary
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-xs font-medium cursor-pointer has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-300 has-[:checked]:text-emerald-600 text-slate-500">
                    <input type="radio" name="new_tipe_konsumen" value="permanent" class="accent-emerald-600"> Permanent
                </label>
            </div>
        </x-form.group>

        <x-button onclick="saveKonsumen()" class="w-full">
            SIMPAN PELANGGAN
        </x-button>
    </div>
</x-modal>

@endsection
@push('scripts')
<style>
    .shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }
    .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

    /* Styling Select2 (tinggi, border, font, dropdown, scrollbar hasil
       pencarian) SUDAH dipindahkan ke resources/css/app.css supaya satu
       sumber kebenaran dipakai di semua halaman (lihat catatan di sana) -
       jangan duplikasi override di sini lagi. */
</style>
<script>
    // 1. GLOBAL STATE
    let cart = [];
    let currentProduk = null;
    let editIndex = null;
    let currentThumbnail = null;
    let currentKategoriFilter = 'all';

    function onTipeOrderChange() {
        const tipe = document.querySelector('input[name="tipe_order"]:checked').value;
        document.getElementById('wrapper_no_po').classList.toggle('hidden', tipe !== 'PO');
    }

    // Accordion "Tambah Jasa" - tertutup default (lihat catatan di HTML-nya).
    function toggleTambahJasa() {
        document.getElementById('wrapper_tambah_jasa').classList.toggle('hidden');
        document.getElementById('icon_toggle_jasa').classList.toggle('rotate-180');
    }

    // compressImage() & namaFileDariGambar() ada di partial bersama
    // partials/image-compress-script.blade.php (di-include di awal file
    // ini) - dipakai juga oleh Pesanan Jersey, supaya logika kompresi cuma
    // ada di SATU tempat.

    // Upload thumbnail segera saat file dipilih (bukan dikirim bareng payload
    // /penjualan/store yang formatnya JSON) - path hasil upload disimpan di
    // currentThumbnail lalu ikut ke item keranjang sebagai string biasa.
    async function uploadThumbnail(input) {
        const file = input.files[0];
        if (!file) return;

        const originalName = file.name;
        input.disabled = true;

        try {
            const compressed = await compressImage(file);
            const compressedFile = new File([compressed], namaFileDariGambar(originalName) + '.jpg', { type: 'image/jpeg' });

            const formData = new FormData();
            formData.append('thumbnail', compressedFile);

            const response = await fetch('{{ route("penjualan.upload-thumbnail") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });
            const res = await response.json();
            if (res.status === 'success') {
                currentThumbnail = res.path;
                const preview = document.getElementById('thumbnail_preview');
                preview.src = res.url;
                preview.classList.remove('hidden');

                // Nama file / deskripsi diambil dari nama file gambar yang
                // diunggah - hanya kalau kolomnya masih kosong, supaya tidak
                // menimpa deskripsi yang sudah sengaja ditulis ulang saat
                // sedang mengedit item keranjang.
                const namaFileInput = document.getElementById('nama_file');
                if (!namaFileInput.value.trim()) {
                    namaFileInput.value = namaFileDariGambar(originalName);
                }

                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 }).fire({
                    icon: 'success', title: `Gambar terunggah (${(compressed.size / 1024).toFixed(0)} KB)`
                });
            } else {
                Swal.fire('Gagal', 'Upload thumbnail gagal.', 'error');
                input.value = '';
            }
        } catch (e) {
            Swal.fire('Error', 'Upload thumbnail gagal, cek koneksi.', 'error');
            input.value = '';
        } finally {
            input.disabled = false;
        }
    }

    $(document).ready(function() {
        $('.select2-konsumen').select2({
            placeholder: "-- Cari Nama / No. HP --",
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                // Server-side search (KonsumenController::searchAjax): LIKE
                // nama/telepon, limit 20. Klien tidak lagi memegang master
                // konsumen penuh.
                url: '{{ route('konsumen.search-ajax') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                cache: true
            }
        });

        // select_produk & select_jasa punya class "select2-produk" tapi
        // sebelumnya tidak pernah dipanggil .select2() sama sekali - keduanya
        // cuma <select> polos berbaju nama class yang menyesatkan, terlihat
        // beda (tanpa kotak pencarian) dibanding Cari Pelanggan yang benar-
        // benar sudah jadi widget Select2. Diinisialisasi sekarang supaya
        // ketiganya konsisten.
        //
        // select_produk butuh matcher kustom: filterProduk() (filter
        // Kategori) sebelumnya cukup set opt.style.display='none' pada
        // <option> karena itu masih <select> native (browser menghormati
        // display:none). Select2 membangun daftar hasilnya sendiri dari
        // data option, TIDAK pernah mengecek style.display - begitu
        // diinisialisasi Select2, filter Kategori diam-diam berhenti
        // berfungsi (semua produk selalu tampil apa pun kategorinya) kalau
        // tidak diganti jadi matcher seperti ini.
        $('#select_produk').select2({
            width: '100%',
            matcher: function (params, data) {
                const el = data.element;
                // Sumber kebenaran = nilai kolom Kategori yang sedang tampil. Bila belum ada kategori sungguhan terpilih,
                // TIDAK ada produk yang boleh muncul; produk hanya lolos bila kategorinya sama persis.
                const katAktif = document.getElementById('filter_kategori').value;
                if (data.id === '' || data.id === undefined) {
                    return data; // baris placeholder
                }
                const katId = el ? el.getAttribute('data-kategori') : null;
                if (!katAktif || katAktif === 'all' || katId === null || String(katId) !== String(katAktif)) {
                    return null;
                }
                if (!params.term || params.term.trim() === '') {
                    return data;
                }
                if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                    return data;
                }
                return null;
            }
        });
        $('#select_jasa').select2({ width: '100%' });

        // Redirect dari alur Edit Nota (PenjualanController::applyApprovedEdit)
        // membawa ?id_konsumen= supaya kasir tidak perlu mencari ulang
        // konsumennya saat mau membuat nota pengganti - murni kenyamanan,
        // kasir tetap bebas ganti kalau ternyata salah.
        @if(request()->filled('id_konsumen'))
            $('.select2-konsumen').val('{{ request('id_konsumen') }}').trigger('change');
        @endif

        // Bantu handler .data('golongan') lama: option AJAX select2 tidak punya
        // atribut data-*, jadi simpan metadata konsumen terpilih di cache element.
        function simpanMetaKonsumen(el, meta) { $(el).data('meta', meta || null); }

        $('.select2-konsumen').on('select2:select', function (e) {
            // AJAX mode: metadata lewat e.params.data (dibawa prosesResults),
            // bukan atribut data-* di option.
            simpanMetaKonsumen(this, e.params.data || null);
            let idGol = (e.params.data && e.params.data.golongan) || $(this).find(':selected').data('golongan');
            if (idGol) {
                $('#id_golongan').val(idGol);
                hitungSubtotal();
            }
        });

        $('#input_p').on('keypress', function(e) { if (e.which === 13) $('#input_l').focus().select(); });
        $('#input_l').on('keypress', function(e) { if (e.which === 13) $('#input_qty_ukuran').focus().select(); });

        $('.select2-konsumen').on('change', function() {
            handleIntegritasData();
            let idGol = $(this).find(':selected').data('golongan');
            if (idGol) {
                $('#id_golongan').val(idGol);
            }
            resetSemuaField();
        });

        $('#id_golongan').on('change', function() {
            handleIntegritasData();
            resetSemuaField();
        });

        $('.select2-konsumen').on('select2:select', function (e) {
            // AJAX mode: e.params.data ADALAH item prosesResults (punya
            // nama/telepon/golongan); fallback .dataset utk opsi server-render.
            const data = Object.assign({},
                (e.params.data && e.params.data.nama !== undefined) ? e.params.data : {},
                (e.params.data && e.params.data.element) ? e.params.data.element.dataset : {});

            if (cart.length > 0) {
                cart = [];
                renderCart();
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Keranjang dikosongkan karena pelanggan berubah', showConfirmButton: false, timer: 2000 });
            }

            $('#display_telp').val(data.telp || data.telepon || '-');
            $('#id_golongan').val(data.golongan).trigger('change');

            resetInputProduk();
        });
    });

    function handleIntegritasData() {
        if (cart.length > 0) {
            cart = [];
            renderCart();
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'Keranjang dikosongkan karena perubahan data pelanggan/harga',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }

    function resetSemuaField() {
        currentProduk = null;
        editIndex = null;

        $('#select_produk').val('').trigger('change');
        document.getElementById('nama_file').value = "";
        document.getElementById('input_p').value = "";
        document.getElementById('input_l').value = "";
        document.getElementById('input_qty_ukuran').value = "1";
        document.getElementById('input_qty_biasa').value = "1";
        document.getElementById('input_desain').value = "0";

        document.getElementById('display_harga').innerText = "Rp 0 /unit";
        document.getElementById('display_subtotal').innerText = "Rp 0";

        document.getElementById('wrapper_ukuran').classList.add('hidden');
        document.getElementById('wrapper_qty').classList.add('hidden');

        const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
        btn.innerText = "Tambah";
        btn.className = "inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-3.5 rounded-xl font-medium text-xs uppercase tracking-wider shadow-lg shadow-emerald-100 transition-all active:scale-95";
    }

    // 2. MODAL FUNCTIONS
    async function saveKonsumen() {
        const nama = document.getElementById('new_nama').value;
        const hp = document.getElementById('new_hp').value;
        const idGol = document.getElementById('new_id_golongan').value;
        const tipeKonsumen = document.querySelector('input[name="new_tipe_konsumen"]:checked').value;

        if (!nama) return Swal.fire('Data belum lengkap', 'Nama wajib diisi!', 'warning');

        try {
            const response = await fetch('{{ url('/konsumen/store-ajax') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    nama_konsumen: nama,
                    telepon: hp,
                    id_golongan: idGol,
                    tipe_konsumen: tipeKonsumen
                })
            });

            const res = await response.json();

            if (res.status === 'success') {
                let newOption = new Option(res.data.nama_konsumen, res.data.id_konsumen, true, true);

                simpanMetaKonsumen(document.getElementById('id_konsumen'), {
                    nama: res.data.nama_konsumen, telepon: res.data.telepon || '-', golongan: res.data.id_golongan
                });
                $(newOption).attr('data-nama', res.data.nama_konsumen);
                $(newOption).attr('data-telp', res.data.telepon);
                $(newOption).attr('data-golongan', res.data.id_golongan);

                $('.select2-konsumen').append(newOption).trigger('change');

                $('#display_telp').val(res.data.telepon || '-');
                $('#id_golongan').val(res.data.id_golongan);

                closeModal('konsumen-baru');
                Swal.fire('Berhasil', 'Pelanggan Baru Terpilih', 'success');
            } else {
                // Sebelumnya tidak ada penanganan di sini - tombol Simpan terlihat
                // tidak bereaksi padahal request sudah dikirim & gagal (mis. sesi
                // kas belum dibuka, atau nama sudah terdaftar).
                Swal.fire('Gagal', res.message || 'Data tidak dapat disimpan.', 'error');
            }
        } catch (e) {
            // Tangkap juga error validasi Laravel (HTTP 422) yang formatnya
            // { message: "...", errors: {...} } - fetch tidak melempar exception
            // untuk status non-2xx, jadi baris di atas tetap ke-handle oleh blok
            // if/else; catch ini murni untuk kegagalan jaringan/parsing.
            Swal.fire('Error', 'Cek koneksi database', 'error');
        }
    }

    // 3. PRODUCT FUNCTIONS
    function filterProduk(idKat) {
        currentKategoriFilter = idKat;
        $('#select_produk').val('').trigger('change');
        resetInputProduk();

        // Produk terkunci sampai Kategori sungguhan dipilih (bukan "Semua
        // Kategori"/kosong) - lihat catatan di field Pilih Produk.
        const terkunci = !idKat || idKat === 'all';
        $('#select_produk').prop('disabled', terkunci);
        document.getElementById('hint_kunci_produk').classList.toggle('hidden', !terkunci);
    }

    // Finishing hanya relevan untuk kategori bahan/produk tertentu (mis.
    // Mata Ayam untuk banner OUTDOOR, tidak relevan untuk stiker A3) -
    // finishing.id_kategori (kosong = berlaku untuk SEMUA kategori, demi
    // kompatibel mundur dengan finishing lama yang belum diisi kategorinya)
    // dicocokkan ke data-kategori produk yang sedang dipilih (sumber sama
    // dengan filter "Kategori" di atas). Opsi yang tidak relevan disembunyikan
    // (bukan dihapus) supaya value-nya tetap ada kalau nanti kategori produk
    // diganti lagi. Kalau finishing yang SEDANG terpilih jadi tidak relevan,
    // reset ke "Tanpa Finishing" supaya tidak ada pilihan tersembunyi yang
    // diam-diam masih aktif.
    function filterFinishingByKategori(idKategoriProduk) {
        const sel = document.getElementById('select_finishing');
        let terpilihJadiTidakRelevan = false;
        Array.from(sel.options).forEach(opt => {
            if (!opt.value) return; // "-- Tanpa Finishing --" selalu tampil
            const idKategoriFinishing = opt.dataset.kategori;
            const relevan = !idKategoriFinishing || !idKategoriProduk || idKategoriFinishing == idKategoriProduk;
            opt.hidden = !relevan;
            if (!relevan && opt.selected) terpilihJadiTidakRelevan = true;
        });
        if (terpilihJadiTidakRelevan) {
            sel.value = '';
            onFinishingChange();
        }
    }

    async function getDetailProduk() {
        const id = $('#select_produk').val();
        if (!id) { resetInputProduk(); return; }
        const selectedOption = $('#select_produk').find(':selected');
        const metode = selectedOption.data('metode');
        filterFinishingByKategori(selectedOption.data('kategori'));
        setSiapCetak(false, true);
        document.getElementById('wrapper_siap_cetak').classList.toggle('hidden', String(selectedOption.data('siap-cetak')) !== '1');
        $('#wrapper_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#note_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#wrapper_qty').toggleClass('hidden', metode !== 'quantity');

        try {
            const res = await fetch(`{{ url('/api/penjualan/produk') }}/${id}`);
            currentProduk = await res.json();
            currentProduk.metode_hitung = metode;
            // Ganti produk = Biaya Cetak manual (kalau ada) tidak relevan lagi
            // untuk produk yang baru dipilih - kembali ke auto-default dari
            // Daftar Harga produk ini.
            cetakManualEdit = false;
            finishingBiayaManualEdit = false;
            setTimeout(() => {
                if (metode === 'ukuran') { $('#input_p').focus().select(); }
                else { $('#input_qty_biasa').focus().select(); }
            }, 100);
            hitungSubtotal();
        } catch (e) { console.error("Gagal ambil produk:", e); }
    }

    // Biaya Cetak sekarang komponen UTAMA subtotal, defaultnya otomatis
    // mengikuti Harga Daftar Harga x Qty (lihat hitungSubtotal()) - pola
    // "manual edit" ini PERSIS sama seperti finishingBiayaManualEdit di
    // bawah: begitu operator mengetik sendiri, auto-fill berhenti menimpa
    // balik angka yang sudah sengaja diubah.
    let cetakManualEdit = false;
    document.getElementById('input_cetak').addEventListener('input', () => {
        cetakManualEdit = true;
    });

    // ---- File Siap Cetak: harga tersendiri per produk, tanpa biaya desain & desainer
    let siapCetak = false;
    function setSiapCetak(v, tanpaHitung) {
        siapCetak = !!v;
        document.querySelector(`input[name="jenis_pengerjaan"][value="${siapCetak ? 'siap' : 'desain'}"]`).checked = true;
        const tombolDesain = document.getElementById('btn_tambah_desain');
        if (siapCetak) {
            document.getElementById('input_desain').value = '0';
            document.getElementById('wrapper_biaya_desain').classList.add('hidden');
            document.getElementById('wrapper_desainer').classList.add('hidden');
            tombolDesain.classList.add('hidden');
        } else {
            tombolDesain.classList.remove('hidden');
        }
        if (!tanpaHitung) { cetakManualEdit = false; hitungSubtotal(); }
    }

    function tampilkanBiayaDesain() {
        document.getElementById('wrapper_biaya_desain').classList.remove('hidden');
        document.getElementById('wrapper_desainer').classList.remove('hidden');
        document.getElementById('btn_tambah_desain').classList.add('hidden');
        document.getElementById('input_desain').focus();
    }

    // Biaya finishing hasil auto-suggest (tier finishing_harga berdasarkan
    // qty, fallback harga_default) BUKAN harga final - kasir tetap bebas
    // menimpa manual. finishingBiayaManualEdit menandai kalau field sudah
    // disentuh manual SEJAK terakhir auto-fill, supaya perubahan qty
    // sesudahnya tidak diam-diam menimpa balik angka yang sudah sengaja
    // diubah kasir.
    let finishingBiayaManualEdit = false;
    document.getElementById('input_finishing_biaya').addEventListener('input', () => {
        finishingBiayaManualEdit = true;
    });

    async function onFinishingChange() {
        const sel = document.getElementById('select_finishing');

        // Spek finishing (Standar/Custom) cuma bermakna kalau ada jenis
        // finishing yang dipilih - satu-satunya sumber "sudah pilih
        // finishing atau belum" sekarang cuma dropdown ini, supaya tidak
        // ada dua tempat terpisah yang bisa saling bertentangan.
        const statusFinishing = document.getElementById('input_status_finishing');
        const wrapperBiayaFinishing = document.getElementById('wrapper_biaya_finishing');
        const wrapperQtyFinishing = document.getElementById('wrapper_qty_finishing');
        const metodeFinishing = sel.selectedOptions[0]?.dataset.metode;
        if (sel.value) {
            statusFinishing.disabled = false;
            wrapperBiayaFinishing.classList.remove('hidden');
            // Field Jumlah Pcs cuma relevan untuk metode 'quantity' - metode
            // 'luas'/'keliling' dihitung otomatis dari Panjang/Lebar/Qty
            // produk, tidak butuh input terpisah.
            wrapperQtyFinishing.classList.toggle('hidden', metodeFinishing !== 'quantity');
            if (metodeFinishing === 'quantity') {
                const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 1;
                const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 1;
                document.getElementById('input_qty_finishing').value = (currentProduk?.metode_hitung === 'ukuran') ? qU : qB;
                qtyFinishingManualEdit = false;
            }
        } else {
            statusFinishing.disabled = true;
            statusFinishing.value = 'Standar';
            document.getElementById('input_finishing_biaya').value = "0";
            wrapperBiayaFinishing.classList.add('hidden');
            wrapperQtyFinishing.classList.add('hidden');
        }

        // Reset lalu delegasikan ke hitungSubtotal() (yang sudah meng-await
        // refreshFinishingBiayaSuggestion() SEBELUM menghitung subtotal) -
        // sengaja TIDAK memanggil refreshFinishingBiayaSuggestion() lagi di
        // sini secara terpisah, supaya tidak ada dua fetch berjalan
        // bersamaan yang bisa saling menimpa (race condition).
        finishingBiayaManualEdit = false;
        document.getElementById('btn_tambah_finishing').classList.toggle('hidden', !sel.value);
        terapkanModeBiayaFinishing();
        await hitungSubtotal();
    }

    // Jumlah Pcs Finishing diedit manual oleh kasir (mis. 1 banner butuh 4
    // mata ayam) - begitu disentuh, berhenti auto-ikut qty produk supaya
    // angka yang sengaja disesuaikan tidak ditimpa balik.
    let qtyFinishingManualEdit = false;
    document.getElementById('input_qty_finishing').addEventListener('input', () => {
        qtyFinishingManualEdit = true;
    });
    function onQtyFinishingManualChange() {
        qtyFinishingManualEdit = true;
        finishingBiayaManualEdit = false; // qty berubah -> biaya perlu dihitung ulang otomatis
        hitungSubtotal();
    }

    // Qty acuan tier finishing & pengali biayanya BEDA per metode_hitung
    // finishing (kolom finishing.metode_hitung - milik finishing itu
    // sendiri, BUKAN otomatis ikut metode_hitung produk induknya):
    // - 'luas'   : luas total = (P x L / 10000) x qty produk, m² - diambil
    //              otomatis dari input Panjang/Lebar/Qty produk di atas.
    // - 'keliling': keliling total = (2 x (P+L) / 100) x qty produk, meter -
    //              untuk finishing jahit/border yang dikerjakan sekeliling
    //              tepi produk (rumus sama dengan auto-override BOM bahan
    //              "jahit"). Juga otomatis dari input di atas.
    // - 'quantity': jumlah pcs finishing dari field "Jumlah Pcs Finishing"
    //              yang bisa disesuaikan manual (TIDAK selalu sama dengan
    //              qty cetak produknya).
    // Tier finishing_harga (& harga_default fallback) adalah HARGA PER
    // SATUAN (per m²/per meter keliling/per pcs) - dikalikan qty di atas
    // untuk dapat total biaya, PERSIS pola yang sama seperti harga produk
    // (lihat hitungSubtotal(): hargaJual x qtyCari). Sebelum ini finishing
    // TIDAK PERNAH dikalikan qty (field Biaya Finishing langsung diisi
    // angka tier mentah), jadi order finishing dengan qty besar bisa
    // under-charge signifikan.
    async function refreshFinishingBiayaSuggestion() {
        const sel = document.getElementById('select_finishing');
        if (!sel.value || finishingBiayaManualEdit || !currentProduk) return;
        const idFinishingSaatMulai = sel.value;

        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 0;
        const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
        const metodeFinishing = sel.selectedOptions[0]?.dataset.metode;
        const pcsProduk = (currentProduk.metode_hitung === 'ukuran') ? qU : qB;

        let qtyFinishing;
        if (metodeFinishing === 'luas') {
            qtyFinishing = (currentProduk.metode_hitung === 'ukuran') ? (p * l / 10000) * qU : pcsProduk;
        } else if (metodeFinishing === 'keliling') {
            qtyFinishing = (currentProduk.metode_hitung === 'ukuran') ? (2 * (p + l) / 100) * qU : pcsProduk;
        } else {
            // 'quantity' - dari field Jumlah Pcs Finishing (default = qty
            // produk selama belum disentuh manual, lihat onFinishingChange()).
            qtyFinishing = parseFloat(document.getElementById('input_qty_finishing').value) || 0;
        }
        if (qtyFinishing <= 0) return;

        try {
            const res = await fetch(`{{ url('/api/penjualan/get-harga-finishing') }}/${sel.value}/${qtyFinishing}`);
            const data = await res.json();
            // Cek ulang SETELAH await, bukan cuma sebelum fetch - selama fetch
            // berjalan (network delay), kasir bisa saja sudah keburu mengetik
            // biaya finishing manual sendiri (finishingBiayaManualEdit jadi
            // true), atau editItem() sedang memulihkan angka historis item
            // yang sedang diedit. Tanpa cek ulang ini, hasil fetch yang lambat
            // datang tetap MENIMPA nilai yang baru saja sengaja diisi -
            // persis bug yang bikin biaya_finishing kesimpan beda dari yang
            // dipakai hitung subtotal (subtotal keburu dihitung duluan pakai
            // angka manual, baru fetch ini menimpa field-nya belakangan).
            if (finishingBiayaManualEdit || sel.value !== idFinishingSaatMulai) return;
            const hargaSatuanFinishing = parseFloat(data.harga) || 0;
            document.getElementById('input_finishing_biaya').value = formatRupiahDariAngka(hargaSatuanFinishing * qtyFinishing);
        } catch (e) {}
    }


    // ===================== FINISHING GANDA (Laminasi + Kisscut, dst) =====================
    // finishingList = finishing yang SUDAH ditambahkan untuk item yang sedang diisi. Kontrol finishing di bar biru adalah
    // baris isian; "Tambah Finishing" memasukkannya ke daftar. Subtotal memakai TOTAL daftar. Finishing Standar dihitung ulang
    // otomatis bila ukuran/qty berubah; Custom memakai biaya manual.
    let finishingList = [];
    const BOLEH_UBAH_HARGA = {{ \App\Support\AksesHarga::bolehLihat() ? 'true' : 'false' }};

    function totalFinishingList() {
        return finishingList.reduce((t, f) => t + (parseFloat(f.biaya) || 0), 0);
    }

    // Qty acuan tarif per metode finishing (luas m2 / keliling m / jumlah pakai) - rumus sama dengan server.
    function qtyAcuanFinishing(metode, qtyPakai) {
        if (!currentProduk) return 0;
        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 0;
        const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
        const pcs = currentProduk.metode_hitung === 'ukuran' ? qU : qB;
        if (metode === 'luas') return currentProduk.metode_hitung === 'ukuran' ? (p * l / 10000) * qU : pcs;
        if (metode === 'keliling') return currentProduk.metode_hitung === 'ukuran' ? (2 * (p + l) / 100) * qU : pcs;
        return parseFloat(qtyPakai) || pcs;
    }

    async function hargaStandarFinishing(idFinishing, metode, qtyPakai) {
        const q = qtyAcuanFinishing(metode, qtyPakai);
        if (q <= 0) return 0;
        try {
            const res = await fetch(`{{ url('/api/penjualan/get-harga-finishing') }}/${idFinishing}/${q}`);
            const data = await res.json();
            return Math.round((parseFloat(data.harga) || 0) * q);
        } catch (e) { return 0; }
    }

    async function hitungUlangFinishingStandar() {
        const hasil = await Promise.all(finishingList.map(f => f.status === 'Standar' ? hargaStandarFinishing(f.id_finishing, f.metode, f.qty_pakai) : Promise.resolve(f.biaya)));
        finishingList.forEach((f, i) => { f.biaya = hasil[i]; });
        renderFinishingList();
    }

    function renderFinishingList() {
        const box = document.getElementById('finishing_list_box');
        const rows = document.getElementById('finishing_list_rows');
        box.classList.toggle('hidden', finishingList.length === 0);
        document.getElementById('finishing_list_total').innerText = 'Rp ' + Math.round(totalFinishingList()).toLocaleString('id-ID');
        rows.innerHTML = finishingList.map((f, i) => `
            <div class="px-4 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-1">
                <span class="w-6 text-xs font-medium text-slate-400">${i + 1}</span>
                <div class="flex-1 min-w-[160px]">
                    <p class="text-sm font-medium text-slate-700">${escapeHtml(f.nama)} <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium uppercase ${f.status === 'Custom' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-50 text-indigo-600'}">${f.status}</span></p>
                    ${f.catatan ? `<p class="text-[11px] text-slate-400">${escapeHtml(f.catatan)}</p>` : ''}
                </div>
                <span class="text-xs text-slate-500 w-24 text-center">${f.metode === 'quantity' ? angkaId(parseFloat(f.qty_pakai) || 0, 2) + ' pakai' : (f.metode === 'luas' ? 'per m²' : 'per meter')}</span>
                <span class="text-sm font-medium text-slate-800 w-28 text-right">Rp ${Math.round(f.biaya).toLocaleString('id-ID')}</span>
                <button type="button" onclick="hapusFinishingDariDaftar(${i})" class="text-rose-400 hover:text-rose-600" title="Hapus finishing ini"><i class="fas fa-times"></i></button>
            </div>`).join('');
    }

    function hapusFinishingDariDaftar(i) {
        finishingList.splice(i, 1);
        renderFinishingList();
        hitungSubtotal();
    }

    // Isian finishing (bar biru) -> daftar. silent=true dipakai saat "Tambah" item ke keranjang (tanpa peringatan).
    async function tambahFinishingKeDaftar(silent = false) {
        const sel = document.getElementById('select_finishing');
        if (!sel.value) { if (!silent) Swal.fire('Finishing', 'Pilih jenis finishing dulu.', 'warning'); return false; }
        if (finishingList.length >= 5) { if (!silent) Swal.fire('Finishing', 'Maksimal 5 finishing per item.', 'warning'); return false; }
        if (finishingList.some(f => String(f.id_finishing) === String(sel.value))) { if (!silent) Swal.fire('Finishing', 'Finishing ini sudah ada di daftar. Hapus dulu bila ingin mengubahnya.', 'info'); return false; }

        const metode = sel.selectedOptions[0]?.dataset.metode;
        const status = document.getElementById('input_status_finishing').value === 'Custom' ? 'Custom' : 'Standar';
        const qtyPakai = metode === 'quantity' ? (parseFloat(document.getElementById('input_qty_finishing').value) || 0) : null;
        let biaya;
        if (status === 'Custom' && BOLEH_UBAH_HARGA) biaya = cleanRupiah(document.getElementById('input_finishing_biaya').value);
        else biaya = await hargaStandarFinishing(sel.value, metode, qtyPakai);

        finishingList.push({
            id_finishing: parseInt(sel.value), nama: sel.options[sel.selectedIndex].text, metode, status, qty_pakai: qtyPakai, biaya,
            catatan: document.getElementById('input_catatan_finishing').value.trim(),
        });

        // kosongkan baris isian
        sel.value = '';
        document.getElementById('input_catatan_finishing').value = '';
        onFinishingChange();
        renderFinishingList();
        return true;
    }

    // Standar = biaya otomatis (terkunci); Custom = biaya boleh diketik (hanya role yang berhak mengubah harga).
    function terapkanModeBiayaFinishing() {
        const custom = document.getElementById('input_status_finishing').value === 'Custom';
        const el = document.getElementById('input_finishing_biaya');
        el.readOnly = !(custom && BOLEH_UBAH_HARGA);
        el.classList.toggle('opacity-70', el.readOnly);
    }

    function onStatusFinishingChange() {
        finishingBiayaManualEdit = document.getElementById('input_status_finishing').value === 'Custom';
        if (!finishingBiayaManualEdit) refreshFinishingBiayaSuggestion();
        terapkanModeBiayaFinishing();
    }

    // Indikator "Menghitung..." selama perhitungan berjalan (dipicu saat pindah field / tab).
    let hitungBerjalan = 0;
    async function hitungSubtotal() {
        hitungBerjalan++;
        document.getElementById('status_hitung').classList.remove('hidden');
        try { return await hitungSubtotalInti(); }
        finally {
            hitungBerjalan--;
            if (hitungBerjalan <= 0) document.getElementById('status_hitung').classList.add('hidden');
        }
    }

    async function hitungSubtotalInti() {
        if (!currentProduk) return null;
        const idGol = document.getElementById('id_golongan').value;
        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 0;
        const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
        const biayaDesain = siapCetak ? 0 : cleanRupiah(document.getElementById('input_desain').value);

        // Jumlah Pcs Finishing ikut mengikuti qty produk SELAMA belum
        // disentuh manual (qtyFinishingManualEdit) - pola sama seperti Biaya
        // Cetak/Biaya Finishing yang auto-mengikuti sampai kasir mengetik
        // sendiri.
        if (!qtyFinishingManualEdit && !document.getElementById('wrapper_qty_finishing').classList.contains('hidden')) {
            document.getElementById('input_qty_finishing').value = (currentProduk.metode_hitung === 'ukuran') ? qU : qB;
        }

        let qtyCari = (currentProduk.metode_hitung === 'ukuran') ? (p * l / 10000) * qU : qB;
        if (qtyCari <= 0) { updateDisplay(0, 0); return null; }

        // Refresh dulu SEBELUM baca nilainya - supaya subtotal di bawah ini
        // memakai saran harga finishing yang sudah sesuai qty terbaru, bukan
        // nilai lama dari sebelum qty berubah (kalau dipanggil belakangan
        // tanpa di-await, hasilnya selalu telat satu langkah/race condition).
        await refreshFinishingBiayaSuggestion();
        await hitungUlangFinishingStandar();
        const biayaFinishing = totalFinishingList();

        try {
            const url = `{{ url('/api/penjualan/get-harga') }}/${currentProduk.id_produk}/${idGol}/${qtyCari}${siapCetak ? '?siap_cetak=1' : ''}`;
            const response = await fetch(url);
            const data = await response.json();
            const hargaJual = parseFloat(data.harga_jual) || 0;

            // Biaya Cetak = komponen UTAMA subtotal, defaultnya mengikuti
            // Harga Daftar Harga x Qty - TAPI cuma kalau operator belum
            // mengetik manual sendiri (cetakManualEdit), pola sama persis
            // dengan refreshFinishingBiayaSuggestion() di atas.
            const inputCetak = document.getElementById('input_cetak');
            if (!cetakManualEdit) {
                inputCetak.value = formatRupiah(Math.round(hargaJual * qtyCari));
            }
            const biayaCetak = cleanRupiah(inputCetak.value);

            const subtotal = biayaCetak + biayaDesain + biayaFinishing;
            updateDisplay(hargaJual, subtotal, data.is_promo);
            cekStokBom(currentProduk.id_produk, p, l, (currentProduk.metode_hitung === 'ukuran') ? qU : qB);
            cekMinOrder(p, l, qU, qB);
            return { harga_satuan: hargaJual, subtotal: subtotal, qty_hitung: qtyCari, p: p, l: l, qty_input: (currentProduk.metode_hitung === 'ukuran') ? qU : qB };
        } catch (error) { return null; }
    }

    // Indikator stok - PERINGATAN saja, tidak pernah memblokir Tambah/Simpan.
    async function cekStokBom(idProduk, p, l, qtyInput) {
        const warningBox = document.getElementById('stok_warning');
        try {
            const url = `{{ url('/api/penjualan/check-stok') }}?id_produk=${idProduk}&p=${p}&l=${l}&qty_input=${qtyInput}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.cukup) {
                warningBox.hidden = true;
                return;
            }

            const kurang = data.detail.filter(d => !d.cukup)
                .map(d => `${d.nama_bahan} (butuh ${d.dibutuhkan} ${d.satuan}, tersedia ${d.tersedia})`)
                .join(', ');

            document.getElementById('stok_warning_detail').innerText =
                `${kurang}. Order tetap bisa disimpan, tapi bahan perlu direstock sebelum naik cetak.`;
            warningBox.hidden = false;
        } catch (e) {
            warningBox.hidden = true;
        }
    }

    // Min Panjang/Lebar/Order di Master Produk sebelumnya dikumpulkan tapi
    // tidak pernah dibaca di mana pun - operator bisa input ukuran/qty di
    // bawah batas tanpa tahu itu di bawah minimum. PERINGATAN saja (pola
    // sama seperti stok BOM), tidak memblokir - kasir yang putuskan.
    function cekMinOrder(p, l, qU, qB) {
        const box = document.getElementById('min_order_warning');
        const detail = document.getElementById('min_order_warning_detail');
        if (!currentProduk) { box.hidden = true; return; }

        if (currentProduk.metode_hitung === 'ukuran') {
            const minP = parseFloat(currentProduk.min_panjang) || 0;
            const minL = parseFloat(currentProduk.min_lebar) || 0;
            const kurangP = minP > 0 && p > 0 && p < minP;
            const kurangL = minL > 0 && l > 0 && l < minL;
            if (kurangP || kurangL) {
                detail.innerText = `Minimum ${minP} x ${minL} cm untuk produk ini (input sekarang: ${p} x ${l} cm). Order tetap bisa disimpan.`;
                box.hidden = false;
                return;
            }
        } else {
            const minQty = parseFloat(currentProduk.min_order) || 0;
            if (minQty > 0 && qB > 0 && qB < minQty) {
                detail.innerText = `Minimum order ${minQty} pcs untuk produk ini (input sekarang: ${qB} pcs). Order tetap bisa disimpan.`;
                box.hidden = false;
                return;
            }
        }
        box.hidden = true;
    }

    function updateDisplay(harga, subtotal, isPromo) {
        const fmt = new Intl.NumberFormat('id-ID');
        document.getElementById('display_harga').innerText = `Rp ${fmt.format(harga)} /unit`;
        document.getElementById('display_subtotal').innerText = `Rp ${fmt.format(Math.round(subtotal))}`;
        document.getElementById('badge_promo').hidden = !isPromo;
    }

    // 4. KERANJANG & AKSI
    async function tambahKeKeranjang() {
        const idKonsumen = $('#id_konsumen').val();
        const namaFile = document.getElementById('nama_file').value.trim();

        if (!idKonsumen) return Swal.fire('Data belum lengkap', 'Pilih pelanggan dulu', 'error');
        if (!currentProduk) return Swal.fire('Data belum lengkap', 'Pilih produk dulu.', 'warning');
        {
            const katAktif = document.getElementById('filter_kategori').value;
            const katProduk = $('#select_produk').find(':selected').data('kategori');
            if (!katAktif || katAktif === 'all' || String(katProduk) !== String(katAktif)) {
                $('#select_produk').val('').trigger('change');
                return Swal.fire('Kategori tidak cocok', 'Produk yang dipilih bukan dari kategori yang sedang aktif. Pilih ulang produknya.', 'warning');
            }
        }
        if (!currentThumbnail) {
            Swal.fire('Upload Gambar Dulu', 'Unggah gambar/thumbnail acuan order sebelum menambahkannya ke keranjang.', 'warning');
            document.getElementById('input_thumbnail').focus();
            return;
        }
        if (namaFile.length < 3) {
            Swal.fire('Keterangan File', 'Isi deskripsi minimal 3 karakter', 'warning');
            document.getElementById('nama_file').focus();
            return;
        }

        // Finishing yang sudah dipilih di baris isian tapi belum ditekan "Tambah Finishing" ikut dimasukkan otomatis.
        if (document.getElementById('select_finishing').value) await tambahFinishingKeDaftar(true);

        const res = await hitungSubtotal();
        if (!res || res.harga_satuan <= 0) return Swal.fire('Data belum lengkap', 'Detail belum lengkap.', 'error');

        // BUG PENTING yang pernah kejadian nyata: validasi di atas cuma
        // mengecek res.harga_satuan (tarif Daftar Harga), BUKAN Biaya Cetak
        // yang sesungguhnya akan tersimpan (subtotal item ini = Biaya Cetak
        // + Desain + Finishing, lihat hitungSubtotal()). Kalau operator
        // sempat menyentuh field Biaya Cetak (cetakManualEdit jadi true,
        // auto-fill berhenti mengikuti) lalu MENGOSONGKANNYA atau
        // membiarkannya 0, guard di atas tetap lolos selama Daftar Harga-nya
        // sendiri valid - item masuk keranjang dengan Biaya Cetak 0 tanpa
        // peringatan apa pun, subtotal-nya 0 walau harga_satuan Daftar Harga-
        // nya benar. Dicegat eksplisit di sini.
        const biayaCetakSaatIni = cleanRupiah(document.getElementById('input_cetak').value);
        if (biayaCetakSaatIni <= 0) {
            Swal.fire('Biaya Cetak Kosong', 'Biaya Cetak tidak boleh 0 - isi dulu sebelum menambahkan ke keranjang.', 'error');
            document.getElementById('input_cetak').focus();
            return;
        }

        const idFinishing = finishingList.length ? finishingList[0].id_finishing : null;
        const namaFinishing = finishingList.length ? finishingList.map(f => f.nama).join(' + ') : null;

        const itemData = {
            id_produk: currentProduk.id_produk,
            nama_produk: currentProduk.nama_produk,
            nama_file: namaFile,
            thumbnail: currentThumbnail,
            p: res.p, l: res.l,
            qty_input: res.qty_input,
            qty_hitung: res.qty_hitung,
            harga_satuan: res.harga_satuan,
            biaya_desain: siapCetak ? 0 : cleanRupiah(document.getElementById('input_desain').value),
            id_desainer: siapCetak ? '' : document.getElementById('input_desainer').value,
            siap_cetak: siapCetak,
            biaya_cetak: cleanRupiah(document.getElementById('input_cetak').value),
            id_finishing: idFinishing,
            nama_finishing: namaFinishing,
            finishing: finishingList.map(f => ({ ...f })),
            biaya_finishing: totalFinishingList(),
            status_urgensi: document.getElementById('input_status_urgensi').value,
            status_finishing: finishingList.some(f => f.status === 'Custom') ? 'Custom' : 'Standar',
            catatan_produksi: document.getElementById('input_catatan_produksi').value.trim(),
            subtotal: res.subtotal,
            id_kategori: $('#filter_kategori').val()
        };

        if (editIndex !== null) {
            cart[editIndex] = itemData;
            editIndex = null;
        } else {
            cart.push(itemData);
        }

        renderCart();
        resetInputProduk();

        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).fire({
            icon: 'success', title: 'Item berhasil diproses'
        });
    }

    // Sama seperti finishingBiayaManualEdit - jasa sebelumnya 100% manual,
    // auto-suggest ini murni starting point, tidak boleh menimpa balik
    // angka yang sudah sengaja diubah kasir.
    let jasaHargaManualEdit = false;
    document.getElementById('input_jasa_harga').addEventListener('input', () => {
        jasaHargaManualEdit = true;
    });

    let ketJasaOtomatis = '';
    async function onJasaChange() {
        const sel = document.getElementById('select_jasa');
        const namaJasa = sel.value ? sel.options[sel.selectedIndex].dataset.nama : '';
        const ketInput = document.getElementById('jasa_keterangan');
        // Isi otomatis nama jasa; ikut berganti saat jasa diganti, KECUALI kasir sudah menulis keterangan sendiri.
        if (namaJasa && (!ketInput.value || ketInput.value === ketJasaOtomatis)) {
            ketInput.value = namaJasa;
            ketJasaOtomatis = namaJasa;
        } else if (!namaJasa && ketInput.value === ketJasaOtomatis) {
            ketInput.value = '';
            ketJasaOtomatis = '';
        }

        jasaHargaManualEdit = false;
        await refreshJasaHargaSuggestion();
    }

    async function refreshJasaHargaSuggestion() {
        const idJasa = document.getElementById('select_jasa').value;
        if (!idJasa) { document.getElementById('input_jasa_harga').value = ''; return; }
        if (jasaHargaManualEdit) return;

        const qty = parseFloat(document.getElementById('input_jasa_qty').value) || 1;

        try {
            const res = await fetch(`{{ url('/api/penjualan/get-harga-jasa') }}/${idJasa}/${qty}`);
            const data = await res.json();
            // Cek ulang SETELAH await - sama seperti refreshFinishingBiayaSuggestion,
            // supaya fetch yang telat nyampe tidak menimpa balik angka yang
            // sudah keburu diketik manual selama fetch berjalan.
            if (jasaHargaManualEdit) return;
            // Jasa tanpa harga acuan: kosongkan, jangan biarkan harga jasa sebelumnya terbawa.
            document.getElementById('input_jasa_harga').value = data.harga > 0 ? formatRupiahDariAngka(data.harga) : '';
        } catch (e) {}
    }

    function tambahJasaKeKeranjang() {
        const idKonsumen = $('#id_konsumen').val();
        if (!idKonsumen) return Swal.fire('Data belum lengkap', 'Pilih pelanggan dulu', 'error');

        const selJasa = document.getElementById('select_jasa');
        const idJasa = selJasa.value;
        if (!idJasa) return Swal.fire('Data belum lengkap', 'Pilih jasa dulu.', 'warning');

        const namaJasa = selJasa.options[selJasa.selectedIndex].dataset.nama;
        const qty = parseFloat(document.getElementById('input_jasa_qty').value) || 0;
        const harga = cleanRupiah(document.getElementById('input_jasa_harga').value);
        const keterangan = document.getElementById('jasa_keterangan').value.trim() || namaJasa;

        if (qty <= 0) return Swal.fire('Data belum lengkap', 'Qty jasa harus lebih dari 0.', 'warning');
        if (harga <= 0) return Swal.fire('Data belum lengkap', 'Harga satuan jasa harus diisi.', 'warning');

        cart.push({
            is_jasa: true,
            id_jasa: idJasa,
            nama_produk: '🔧 ' + namaJasa,
            nama_file: keterangan,
            qty_input: qty,
            qty_hitung: qty,
            harga_satuan: harga,
            subtotal: qty * harga,
            id_finishing: null,
            nama_finishing: null,
        });

        renderCart();

        // Reset form jasa
        $('#select_jasa').val(null).trigger('change');
        document.getElementById('input_jasa_qty').value = 1;
        document.getElementById('input_jasa_harga').value = '';
        document.getElementById('jasa_keterangan').value = '';
        jasaHargaManualEdit = false;

        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).fire({
            icon: 'success', title: 'Jasa ditambahkan ke keranjang'
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderCart() {
        const container = document.getElementById('cart_body');
        container.innerHTML = '';
        let total = 0;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="py-20 text-center opacity-30">
                    <i class="fas fa-receipt text-2xl mb-2 block"></i>
                    <p class="text-xs font-medium uppercase tracking-widest">Belum Ada Pesanan</p>
                </div>`;
        }

        // Chip mini - satu ukuran (text-xs font-medium) dipakai untuk semua
        // badge di kartu keranjang (finishing, urgensi), cuma warnanya yang
        // beda sesuai makna. Sebelumnya tiap badge punya ukuran font sendiri
        // (9px, 8px, 11px) yang tidak ada alasannya beda-beda - sekarang
        // "senada" beneran, bukan cuma kebetulan mirip.
        const urgensiChip = {
            'Urgent': 'bg-amber-50 text-amber-600',
            'Very Urgent': 'bg-red-50 text-red-600',
        };

        cart.forEach((item, index) => {
            total += item.subtotal;
            const urgensi = item.status_urgensi && item.status_urgensi !== 'Normal' ? item.status_urgensi : null;
            const biayaTambahan = [
                item.biaya_cetak ? `Cetak ${Math.round(item.biaya_cetak).toLocaleString('id-ID')}` : null,
                item.biaya_desain ? `Desain ${Math.round(item.biaya_desain).toLocaleString('id-ID')}` : null,
                item.biaya_finishing ? `Finishing ${Math.round(item.biaya_finishing).toLocaleString('id-ID')}` : null,
            ].filter(Boolean).join(' &bull; ');

            // Kartu, bukan baris tabel - keranjang ini juga tampil di panel
            // sempit (42% lebar di desktop, full width tapi tetap sempit di
            // tablet/mobile), tabel 4-kolom di lebar itu selalu perlu discroll
            // horizontal untuk melihat Aksi. Kartu yang membungkus otomatis
            // tidak pernah butuh scroll horizontal di lebar berapa pun.
            container.innerHTML += `
                <div class="group p-4 hover:bg-slate-50/70 transition-colors">
                    <div class="flex items-start gap-3">
                        ${item.thumbnail ? `<img loading="lazy" decoding="async" src="{{ asset('storage') }}/${item.thumbnail}" class="w-11 h-11 rounded-lg object-cover border border-slate-100 shrink-0" ` : `<div class="w-11 h-11 rounded-lg bg-slate-50 border border-slate-100 shrink-0 flex items-center justify-center text-slate-300"><i class="far fa-image text-sm"></i></div>`}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 leading-tight truncate">${escapeHtml(item.nama_produk)}</p>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">${escapeHtml(item.nama_file)}</p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                ${(item.finishing && item.finishing.length ? item.finishing : []).map(f => `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${f.status === 'Custom' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600'}">${escapeHtml(f.nama)}</span>`).join('')}
                                ${(!item.finishing || !item.finishing.length) && item.id_finishing ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600">${escapeHtml(item.nama_finishing)} &middot; ${escapeHtml(item.status_finishing || 'Standar')}</span>` : ''}
                                ${item.siap_cetak ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600" title="Harga File Siap Cetak, tanpa desain tim">File Siap Cetak</span>` : ''}
                                ${urgensi ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${urgensiChip[urgensi]}">${urgensi}</span>` : ''}
                            </div>
                            ${biayaTambahan ? `<p class="text-xs text-slate-400 mt-1.5">${biayaTambahan}</p>` : ''}
                        </div>
                        <div class="flex items-center gap-1 shrink-0 opacity-40 group-hover:opacity-100 transition-opacity">
                            <button onclick="editItem(${index})" class="w-7 h-7 flex items-center justify-center rounded-lg text-amber-500 hover:bg-amber-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button onclick="hapusItem(${index})" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-50">
                        <span class="text-xs font-medium text-slate-400">Qty ${angkaId(item.qty_hitung, 2)}</span>
                        <span class="text-sm font-medium text-slate-900">Rp ${new Intl.NumberFormat('id-ID').format(Math.round(item.subtotal))}</span>
                    </div>
                </div>`;
        });

        document.getElementById('grand_total').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(total))}`;
        $('[id="item_count_badge"]').text(cart.length + " Items");
    }

    function hapusItem(index) {
        Swal.fire({
            title: 'Hapus Item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                cart.splice(index, 1);
                renderCart();
            }
        });
    }

    function editItem(index) {
        const item = cart[index];

        $('#filter_kategori').val(item.id_kategori || 'all').trigger('change');
        $('#select_produk').val(item.id_produk).trigger('change');

        // editIndex HARUS diset SETELAH kedua trigger('change') di atas,
        // bukan sebelumnya: filter_kategori's onchange memanggil
        // filterProduk() -> resetInputProduk(), yang menimpa editIndex balik
        // ke null secara sinkron. Kalau diset sebelum baris ini, index-nya
        // langsung hilang lagi sebelum setTimeout ini sempat jalan - tombol
        // tampil "UPDATE ITEM" tapi klik-nya diam-diam menambah baris baru,
        // bukan mengganti baris yang sedang diedit.
        editIndex = index;

        // Dikunci TRUE SETELAH kedua trigger('change') di atas (yang lewat
        // resetInputProduk() ikut mereset flag ini ke false secara sinkron) -
        // baris yang sedang dibuka untuk diedit BUKAN sedang minta saran
        // baru, melainkan memulihkan angka biaya_finishing yang SUDAH final
        // (baik hasil ketikan manual maupun saran yang sudah diterima
        // sebelumnya). Bagian ASYNC dari trigger di atas (fetch produk,
        // fetch saran finishing) baru lanjut jalan belakangan (setelah baris
        // sinkron ini selesai) - dengan flag ini sudah true duluan,
        // refreshFinishingBiayaSuggestion() manapun yang telat nyampe tidak
        // akan menimpa balik angka historis begitu form-nya kebuka. Sama
        // persis alasannya untuk cetakManualEdit - item yang sedang diedit
        // sudah punya Biaya Cetak final-nya sendiri, bukan minta saran baru.
        finishingBiayaManualEdit = true;
        cetakManualEdit = true;

        setTimeout(() => {
            document.getElementById('nama_file').value = item.nama_file;
            document.getElementById('input_p').value = item.p;
            document.getElementById('input_l').value = item.l;
            document.getElementById('input_qty_ukuran').value = item.qty_input;
            document.getElementById('input_qty_biasa').value = item.qty_input;
            document.getElementById('input_desain').value = formatRupiah(item.biaya_desain || 0);
            document.getElementById('input_desainer').value = item.id_desainer || '';
            document.getElementById('wrapper_desainer').classList.toggle('hidden', !(item.biaya_desain > 0));
            setSiapCetak(!!item.siap_cetak, true);
            document.getElementById('input_cetak').value = formatRupiah(item.biaya_cetak || 0);
            // Finishing item lama: pulihkan ke DAFTAR (baris isian dikosongkan). Bentuk lama (tunggal) dijadikan satu elemen.
            finishingList = (item.finishing && item.finishing.length ? item.finishing : (item.id_finishing ? [{
                id_finishing: item.id_finishing, nama: item.nama_finishing, metode: document.querySelector(`#select_finishing option[value="${item.id_finishing}"]`)?.dataset.metode || 'luas',
                status: item.status_finishing || 'Standar', qty_pakai: null, biaya: item.biaya_finishing || 0, catatan: '',
            }] : [])).map(f => ({ ...f }));
            renderFinishingList();
            document.getElementById('select_finishing').value = '';
            document.getElementById('input_finishing_biaya').value = '0';
            document.getElementById('input_status_urgensi').value = item.status_urgensi || 'Normal';
            document.getElementById('input_status_finishing').disabled = true;
            document.getElementById('input_status_finishing').value = 'Standar';
            document.getElementById('btn_tambah_finishing').classList.add('hidden');
            document.getElementById('input_catatan_produksi').value = item.catatan_produksi || '';

            // Biaya Desain/Finishing muncul otomatis kalau item yang sedang
            // diedit ini memang sudah punya nilainya - konsisten dengan
            // "muncul ketika ada penambahan biaya", bukan cuma saat pertama
            // ditambahkan.
            document.getElementById('wrapper_biaya_desain').classList.toggle('hidden', !(item.biaya_desain > 0));
            document.getElementById('btn_tambah_desain').classList.toggle('hidden', item.biaya_desain > 0 || !!item.siap_cetak);
            document.getElementById('wrapper_biaya_finishing').classList.add('hidden');
            // Item lama tidak pernah menyimpan "jumlah pcs finishing" secara
            // terpisah (biaya_finishing tersimpan sebagai total jadi, bukan
            // rate+qty) - field ini cuma alat bantu hitung ulang, diisi
            // tebakan qty item ini & dikunci manual (qtyFinishingManualEdit)
            // supaya TIDAK diam-diam menghitung ulang & menimpa biaya_finishing
            // historis yang sudah final di atas.
            const metodeFinishingItem = document.querySelector(`#select_finishing option[value="${item.id_finishing}"]`)?.dataset.metode;
            document.getElementById('wrapper_qty_finishing').classList.add('hidden');
            document.getElementById('input_qty_finishing').value = item.qty_input || 1;
            qtyFinishingManualEdit = true;
            currentThumbnail = item.thumbnail || null;
            const preview = document.getElementById('thumbnail_preview');
            if (currentThumbnail) {
                preview.src = `{{ asset('storage') }}/${currentThumbnail}`;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }

            const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
            btn.innerText = "UPDATE ITEM";
            btn.classList.replace('bg-emerald-500', 'bg-amber-500'); btn.classList.replace('hover:bg-emerald-600', 'hover:bg-amber-600'); btn.classList.replace('shadow-emerald-100', 'shadow-amber-100');

            hitungSubtotal();
            document.getElementById('nama_file').focus();
        }, 400);
    }

    function resetInputProduk() {
        finishingList = [];
        renderFinishingList();
        document.getElementById('input_catatan_finishing').value = '';
        document.getElementById('btn_tambah_finishing').classList.add('hidden');
        document.getElementById('nama_file').value = "";
        document.getElementById('input_p').value = "";
        document.getElementById('input_l').value = "";
        document.getElementById('input_qty_ukuran').value = "1";
        document.getElementById('input_qty_biasa').value = "1";
        document.getElementById('input_desain').value = "0";
        document.getElementById('input_cetak').value = "0";
        document.getElementById('select_finishing').value = "";
        filterFinishingByKategori(null);
        document.getElementById('input_finishing_biaya').value = "0";
        finishingBiayaManualEdit = false;
        cetakManualEdit = false;
        siapCetak = false;
        document.querySelector('input[name="jenis_pengerjaan"][value="desain"]').checked = true;
        document.getElementById('wrapper_siap_cetak').classList.add('hidden');
        document.getElementById('wrapper_biaya_desain').classList.add('hidden');
        document.getElementById('wrapper_desainer').classList.add('hidden');
        document.getElementById('input_desainer').value = "{{ $desainerDefault }}";
        document.getElementById('btn_tambah_desain').classList.remove('hidden');
        document.getElementById('wrapper_biaya_finishing').classList.add('hidden');
        document.getElementById('wrapper_qty_finishing').classList.add('hidden');
        document.getElementById('input_qty_finishing').value = "1";
        qtyFinishingManualEdit = false;
        document.getElementById('input_status_urgensi').value = "Normal";
        document.getElementById('input_status_finishing').disabled = true;
        document.getElementById('input_status_finishing').value = "Standar";
        document.getElementById('input_catatan_produksi').value = "";
        document.getElementById('input_thumbnail').value = "";
        document.getElementById('thumbnail_preview').classList.add('hidden');
        currentThumbnail = null;
        document.getElementById('display_harga').innerText = "Rp 0 /unit";
        document.getElementById('display_subtotal').innerText = "Rp 0";
        document.getElementById('stok_warning').hidden = true;
        document.getElementById('min_order_warning').hidden = true;

        // editIndex HARUS direset di sini juga. Sebelumnya ini terlewat: kalau
        // user sedang mengedit item keranjang (editIndex terisi, tombol jadi
        // "UPDATE ITEM" amber) lalu ganti filter Kategori (yang memanggil
        // fungsi ini), tombolnya kembali hijau "TAMBAH" secara visual TAPI
        // editIndex masih menunjuk baris lama - klik Tambah berikutnya diam-diam
        // MENIMPA baris keranjang yang salah, bukan menambah baris baru.
        editIndex = null;

        const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
        btn.innerText = "TAMBAH";
        btn.classList.replace('bg-amber-500', 'bg-emerald-500'); btn.classList.replace('hover:bg-amber-600', 'hover:bg-emerald-600'); btn.classList.replace('shadow-amber-100', 'shadow-emerald-100');

        document.getElementById('nama_file').focus();
    }

    function resetCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            icon: 'warning',
            showCancelButton: true
        }).then((r) => { if (r.isConfirmed) { cart = []; renderCart(); } });
    }

    // Pengenal satu kali pengiriman: server hanya memproses id ini SEKALI (lihat middleware sekali.jalan), jadi
    // kirim ulang setelah jaringan putus tidak melahirkan nota kedua.
    let submitIdNota = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now() + '-' + Math.random().toString(36).slice(2));
    let sedangMenyimpan = false; // Guard duplicate submit: double-click / enter ganda / klik tombol di tab lain tidak boleh membuat DUA nota.
    async function simpanTransaksi(btn) {
        if (sedangMenyimpan) return; // satu submit dalam penerbangan - abaikan klik berikutnya sampai selesai.
        if (cart.length === 0) return Swal.fire('Keranjang Kosong', 'Pilih produk dulu.', 'error');
        const idKonsumen = $('#id_konsumen').val();
        if (!idKonsumen) return Swal.fire('Konsumen', 'Pilih konsumen dulu.', 'warning');

        // Audit kelengkapan (dropdown wajib terisi): Tipe Harga (golongan)
        // menentukan seluruh harga transaksi - wajib dipilih eksplisit.
        // Server (PenjualanController::store) sudah menolak tanpa ini;
        // di sini diberi notif + tandai field sebelum kirim.
        const idGolonganEl = document.getElementById('id_golongan');
        if (!idGolonganEl.value) {
            idGolonganEl.classList.add('ring-2', 'ring-rose-400', 'bg-rose-50/40');
            idGolonganEl.addEventListener('change', () => idGolonganEl.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/40'), { once: true });
            return Swal.fire('Lengkapi Data Pesanan Lebih Dulu', 'Tipe Harga (golongan konsumen) belum dipilih - pilih dulu, harga order mengikutinya.', 'warning');
        }
        idGolonganEl.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/40');

        const tipeOrder = document.querySelector('input[name="tipe_order"]:checked').value;
        const noPo = document.getElementById('no_po').value.trim();
        if (tipeOrder === 'PO' && !noPo) {
            document.getElementById('no_po').focus();
            return Swal.fire('No. PO', 'No. PO wajib diisi untuk tipe order PO.', 'warning');
        }

        const payload = {
            id_konsumen: idKonsumen,
            id_golongan: document.getElementById('id_golongan').value,
            no_nota: document.getElementById('no_nota_display').innerText,
            tipe_order: tipeOrder,
            no_po: noPo,
            tgl_order: document.getElementById('tgl_order').value,
            items: cart,
            quotation_id: {{ !empty($quotationRef) ? (int) $quotationRef->id : 'null' }},
            submit_id: submitIdNota,
            _token: '{{ csrf_token() }}'
        };

        try {
            // Kunci tombol selama request berjalan (flag JS + visual disabled).
            // Success & failure SAMA-SAMA membuka kunci: kalau gagal validasi,
            // operator harus bisa memperbaiki data lalu submit ulang; reload
            // hanya terjadi di jalur sukses.
            sedangMenyimpan = true;
            if (btn) { btn.disabled = true; btn.classList.add('opacity-50', 'pointer-events-none'); }
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
            const response = await fetch('{{ route('penjualan.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.status === 'success') {
                // Reset eksplisit dulu (jangan hanya andalkan reload) supaya
                // data pelanggan & keranjang pasti kosong lagi untuk transaksi
                // berikutnya, walau reload sempat tertunda/di-skip user.
                cart = [];
                editIndex = null;
                currentProduk = null;
                $('.select2-konsumen').val(null).trigger('change');
                $('#display_telp').val('');
                renderCart();
                resetInputProduk();

                let pesanBerhasil = 'Transaksi disimpan.';
                if (result.deposit_terpakai) {
                    const d = result.deposit_terpakai;
                    const fmt = new Intl.NumberFormat('id-ID');
                    pesanBerhasil += ` Rp ${fmt.format(d.dipakai)} otomatis dipotong dari saldo deposit konsumen ini (sisa saldo: Rp ${fmt.format(d.sisa_saldo)}). Status nota: ${d.status}.`;
                }

                Swal.fire('Berhasil!', pesanBerhasil, 'success').then(() => window.location.reload());
            } else {
                Swal.fire('Gagal!', result.msg || 'Terjadi kesalahan.', 'error');
            }
        } catch (e) { Swal.fire('Error!', 'Gagal sistem.', 'error'); }
        finally {
            sedangMenyimpan = false;
            if (btn) { btn.disabled = false; btn.classList.remove('opacity-50', 'pointer-events-none'); }
        }
    }
</script>
@endpush
