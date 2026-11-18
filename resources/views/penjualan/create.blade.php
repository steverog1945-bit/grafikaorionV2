@extends('layouts.app')
@section('content')
@include('partials.rupiah-format-script')
@include('partials.image-compress-script')
{{-- LAYOUT INPUT ORDER - FULL WIDTH, 3 BAGIAN.
     Alur kerja kasir: cari pelanggan (sekali per nota) -> isi detail item
     (berulang, butuh ruang) -> lihat keranjang (harus selalu terlihat).

     Susunan sekarang:
       1. Data Pelanggan  = FULL WIDTH (baris sendiri, melintang penuh)
       2. Detail Pesanan  = container kiri
       3. Keranjang       = container kanan, sticky

     Rombakan sebelumnya menjagli kolom kiri 64% sehingga Data Pelanggan ikut
     sempit - padahal di situ field paling lebar (select2 pelanggan). Full
     width membuat baris Pelanggan bisa 8 kolom tanpa berdesakan. --}}
<div class="pos-mobile-shell w-full px-3 sm:px-5 lg:px-6 py-5">

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
        @if($notaEdit)
            <x-slot:title>EDIT <span class="text-indigo-600">NOTA</span></x-slot:title>
            <x-slot:subtitle>
                <span class="flex h-2 w-2 rounded-full bg-sky-500"></span>
                Mengoreksi nota {{ $notaEdit->nomor_nota }} &bull; {{ \Carbon\Carbon::parse($notaEdit->tgl_order)->translatedFormat('d M Y') }}
            </x-slot:subtitle>
        @elseif($notaSusulan)
            <x-slot:title>TAMBAH <span class="text-indigo-600">ORDER SUSULAN</span></x-slot:title>
            <x-slot:subtitle>
                <span class="flex h-2 w-2 rounded-full bg-amber-500"></span>
                Menambah item ke nota {{ $notaSusulan->nomor_nota }} &bull; {{ \Carbon\Carbon::parse($notaSusulan->tgl_order)->translatedFormat('d M Y') }}
            </x-slot:subtitle>
        @else
            <x-slot:title>INPUT <span class="text-indigo-600">ORDER</span></x-slot:title>
            <x-slot:subtitle>
                <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                Order Cetak Reguler &bull; {{ date('d M Y') }}
            </x-slot:subtitle>
        @endif
        <x-slot:actions>
            <x-button href="{{ $notaKunci ? route('penjualan.show', $notaKunci->id_transaksi) : route('penjualan.riwayat') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> {{ $notaKunci ? 'Kembali ke Nota' : 'Riwayat Nota' }}
            </x-button>
            <div class="bg-white shadow-sm border border-slate-100 rounded-2xl px-5 sm:px-8 py-3.5 flex items-center gap-4 sm:gap-6 max-w-full">
                <div class="text-right min-w-0">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Nomor Nota</p>
                    <p id="no_nota_display" class="text-base sm:text-xl font-medium text-indigo-600 tracking-tight truncate">{{ $no_nota }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 hidden sm:flex items-center justify-center shrink-0"><i class="fas fa-file-invoice"></i></span>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if($notaEdit)
        {{-- Papan penanda mode edit. Yang penting bagi operator: nota ini TIDAK
             dibatalkan - yang berubah cuma bagian yang memang diubah, dan
             barang yang dikurangi akan mengembalikan stok. --}}
        <div class="mb-4 bg-sky-50 border border-sky-200 rounded-2xl px-5 py-4 flex items-start gap-3.5">
            <i class="fas fa-circle-info text-sky-600 mt-0.5 shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm font-medium text-sky-800">Mengkoreksi nota {{ $notaEdit->nomor_nota }}</p>
                <p class="text-[13px] text-sky-700 mt-1 leading-relaxed">
                    Item-item nota sudah terisi di keranjang di bawah dan bisa
                    diubah seperti biasa. Item yang <b>tidak</b> Anda sentuh
                    tidak menyentuh stok sama sekali; item yang dihapus
                    mengembalikan stoknya; item yang baru memotong stok.
                    Nota ini <b>tidak dibatalkan</b> dan pembayarannya tidak
                    berubah.
                </p>
            </div>
        </div>
    @elseif($notaSusulan)
        {{-- Papan penanda mode susulan. Goalnya operator tahu dia sedang
             menambah item ke nota yang SUDAH jalan, bukan membuat nota baru -
             supaya tidak mengira total di layar ini jadi nota terpisah. --}}
        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-start gap-3.5">
            <i class="fas fa-circle-info text-amber-600 mt-0.5 shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm font-medium text-amber-800">Menambah item ke nota yang sudah berjalan</p>
                <p class="text-[13px] text-amber-700 mt-1 leading-relaxed">
                    Item yang Anda susun di bawah akan ditambahkan ke nota
                    <b>{{ $notaSusulan->nomor_nota }}</b> milik
                    <b>{{ $notaSusulan->nama_konsumen }}</b>.
                    Pelanggan, tanggal order, dan tipe order mengikuti nota asal dan tidak bisa diubah di sini.
                    Total nota akan naik sesuai tambahan ini.
                </p>
            </div>
        </div>
    @endif

    {{-- ===================================================================
         BAGIAN 1 - DATA PELANGGAN (FULL WIDTH, melintang penuh)
         ===================================================================
         Dipisah dari kolom detail karena isian di sini **diisi** satu kali per nota,
         sedangkan Detail Pesanan diisi berulang per item. Melewati seluruh
         lebar layar membuat baris ini muat 8 kolom tanpa berdesakan, dan
         select2 pelanggan (yang paling butuh ruang) tidak lagi terp Competitif
         oleh lebar keranjang.

         GRID - Container query (bukan breakpoint layar):
           < 400px  -> 1 kolom  (ponsel)
           400-620  -> 2 kolom
           620-900  -> 3 kolom
           900-1250 -> 4 kolom
           1250-   -> 6 kolom

           Dulu ada tahap 8 kolom di atas 1700px. Diperiksa 16 Okt 2026 dan
           DIHAPUS: pada jendela 1339px kontainernya sudah 920px, jadi
           @4xl (56rem / 896px) aktif dan tiap kolom hanya 105px. Akibatnya
           "Tipe Order" dapat 46px per tombol - teks "Reguler" butuh 48px,
           jadi terpotong jadi "Re...". Delapan kolom terlalu rapat untuk isi
           form yang bukan chip. Enam kolom memberi tiap field ~148px, dan
           enam kolom itu juga sudah memuat seluruh baris desktop dalam satu
           baris: Pelanggan (2) + Telepon + Tipe Harga + Tipe Order + Tanggal.
           =================================================================== --}}
    <x-card class="!p-4 mb-4 @container">
        <div class="flex items-center gap-2.5 mb-3">
            <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0"><i class="fas fa-user text-xs"></i></span>
            <div class="min-w-0">
                <h3 class="text-sm font-medium text-slate-800 leading-tight">Data Pelanggan</h3>
                <p class="text-[11px] text-slate-400 leading-tight">Siapa pemesan dan jenis ordernya</p>
            </div>
        </div>

        <div class="grid grid-cols-1 @xs:grid-cols-2 @md:grid-cols-3 @lg:grid-cols-4 @2xl:grid-cols-6 gap-x-3 gap-y-2.5">
            {{-- Cari Pelanggan: selalu lebar, minimal 2 kolom (penuh di 1-2 kolom)
                 supaya select2 punya ruang untuk nama pelanggan yang panjang. --}}
            <div class="@xs:col-span-2 @md:col-span-2 @lg:col-span-2 @2xl:col-span-2">
                @if($notaKunci)
                    {{-- Mode susulan/edit: pelanggan terkunci ke nota asal.
                         Bukan dropdown yang bisa diganti - kalau bisa diganti,
                         item bisa masuk ke nota orang lain. --}}
                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest truncate block mb-1.5 ml-1 mr-1">Pelanggan Nota</span>
                    <div class="w-full bg-slate-100 border border-slate-200 rounded-lg py-2.5 px-3 text-sm font-medium text-slate-700 flex items-center gap-2.5">
                        <i class="fas fa-user text-slate-400 text-xs shrink-0"></i>
                        <span class="truncate">{{ $notaKunci->nama_konsumen }}</span>
                        <span class="ml-auto text-[11px] font-normal text-slate-400 shrink-0">terkunci</span>
                    </div>
                    {{-- Nilai tetap dibawa ke server (wajib ada di payload
                         walau server memakai id_konsumen dari nota). --}}
                    <input type="hidden" id="id_konsumen" name="id_konsumen" value="{{ $notaKunci->id_konsumen }}">
                @else
                <div class="flex items-center justify-between mb-1.5 ml-1 mr-1 gap-2">
                    <label for="id_konsumen" class="text-[10px] font-medium text-slate-400 uppercase tracking-widest truncate">Cari Pelanggan</label>
                    <button type="button" onclick="openModal('konsumen-baru')" title="Daftarkan pelanggan baru"
                        class="text-[var(--brand-accent-light)] hover:text-[#005560] font-medium text-[11px] flex items-center gap-1 shrink-0">
                        <i class="fas fa-plus text-[9px]"></i> Baru
                    </button>
                </div>
                {{-- Pelanggan = select2 AJAX (search-ajax, limit 20):
                     dataset konsumen puluhan ribu TIDAK lagi diseret ke
                     browser tiap buka POS. Konsumen awal (redirect
                     ?id_konsumen=) dirender server supaya langsung tampil. --}}
                <select id="id_konsumen" name="id_konsumen" class="select2-konsumen" tabindex="1">
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
                @endif
            </div>

            <x-form.group label="Nomor Telepon">
                <input type="text" id="display_telp" autocomplete="off"
                    value="{{ $notaKunci ? $notaKunci->telepon : '' }}"
                    class="w-full bg-slate-100 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-500 shadow-inner"
                    readonly placeholder="-">
            </x-form.group>

            <x-form.group label="Tipe Harga" readonly>
                @if($notaKunci)
                    {{-- Golongan harga juga terkunci: harga item dihitung
                         dengan golongan konsumen nota asal. --}}
                    <input type="hidden" id="id_golongan" name="id_golongan" value="{{ $notaKunci->id_golongan }}">
                    <div class="w-full bg-slate-100 border border-slate-200 rounded-lg py-2.5 px-3 text-sm font-medium text-slate-700 flex items-center gap-2.5">
                        <i class="fas fa-tag text-slate-400 text-xs shrink-0"></i>
                        <span class="truncate">{{ optional($golongan->firstWhere('id_golongan', $notaKunci->id_golongan))->nama_golongan ?? '-' }}</span>
                        <span class="ml-auto text-[11px] font-normal text-slate-400 shrink-0">terkunci</span>
                    </div>
                @else
                <x-form.select id="id_golongan" autocomplete="off" disabled>
                    @foreach($golongan as $g)
                        <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                    @endforeach
                </x-form.select>
                <input type="hidden" id="id_golongan_hidden" name="id_golongan" value="{{ $golongan->first()->id_golongan ?? '' }}">
                <!-- <p class="text-[10px] text-slate-400 mt-1"><i class="fas fa-lock text-[9px] mr-1"></i>Terkunci sesuai golongan pelanggan terpilih</p> -->
                @endif
            </x-form.group>

            {{-- TIPE ORDER
                 Dua tombol radio dalam satu "segmented control".
                 py-2 (bukan py-3) + min-w-0 + truncate pada teks: inilah yang
                 mencegah label "PO" keluar kotak dan menabrak kolom Tetangga
                 pada lebar container ~620px, kondisi yang sebelumnya membuat
                 field Tanggal Order bertumpuk dengan label PO.

                 Kelas .pil-pilih (bukan has-[:checked]:bg-indigo-50) karena
                 warna "terpilih" itu harus terbaca di mode gelap juga.
                 Yang lama memakai bg-indigo-50 - biru sangat terang - yang
                 di mode gelap tetap terang, sedangkan teks indigo-600 di
                 atasnya ikut pudar sehingga keadaan "terpilih" hampir tak
                 terlihat. .pil-pilih memakai variabel --surface-* dan
                 menambahkan override html.dark (lihat app.css). --}}
            <x-form.group label="Tipe Order">
                @if($notaKunci)
                    {{-- Tipe order mengikuti nota asal dan tidak bisa diubah
                         (bukan order baru). --}}
                    <input type="hidden" name="tipe_order" value="{{ $notaKunci->tipe_order ?: 'Reguler' }}">
                    <div class="w-full bg-slate-100 border border-slate-200 rounded-lg py-2.5 px-3 text-sm font-medium text-slate-700 flex items-center gap-2.5">
                        <i class="fas fa-file-invoice text-slate-400 text-xs shrink-0"></i>
                        <span class="truncate">{{ $notaKunci->tipe_order ?: 'Reguler' }}</span>
                        @if($notaKunci->no_po)
                            <span class="ml-auto text-[11px] font-normal text-slate-400 shrink-0">PO {{ $notaKunci->no_po }}</span>
                        @else
                            <span class="ml-auto text-[11px] font-normal text-slate-400 shrink-0">terkunci</span>
                        @endif
                    </div>
                @else
                <div class="flex gap-1.5">
                    <label class="flex-1 min-w-0 flex items-center justify-center gap-1.5 rounded-lg pil-pilih py-2 px-1.5 text-xs font-medium cursor-pointer text-slate-600 transition-all"
                        title="Order reguler tanpa nomor PO">
                        <input type="radio" name="tipe_order" value="Reguler" checked onchange="onTipeOrderChange()" class="sr-only">
                        <span class="truncate">Reguler</span>
                    </label>
                    <label class="flex-1 min-w-0 flex items-center justify-center gap-1.5 rounded-lg pil-pilih py-2 px-1.5 text-xs font-medium cursor-pointer text-slate-600 transition-all"
                        title="Order dengan nomor Purchase Order dari konsumen">
                        <input type="radio" name="tipe_order" value="PO" onchange="onTipeOrderChange()" class="sr-only">
                        <span class="truncate">PO</span>
                    </label>
                </div>
                @endif
            </x-form.group>

            <x-form.group label="Tanggal Order">
                @if($notaKunci)
                    <input type="hidden" id="tgl_order" name="tgl_order" value="{{ \Carbon\Carbon::parse($notaKunci->tgl_order)->toDateString() }}">
                    <div class="w-full bg-slate-100 border border-slate-200 rounded-lg py-2.5 px-3 text-sm font-medium text-slate-700 flex items-center gap-2.5">
                        <i class="fas fa-calendar text-slate-400 text-xs shrink-0"></i>
                        <span class="truncate">{{ \Carbon\Carbon::parse($notaKunci->tgl_order)->translatedFormat('d M Y') }}</span>
                        <span class="ml-auto text-[11px] font-normal text-slate-400 shrink-0">terkunci</span>
                    </div>
                @else
                <x-form.input type="date" id="tgl_order" value="{{ date('Y-m-d') }}" />
                @endif
            </x-form.group>

            {{-- No. PO: muncul hanya saat Tipe Order = PO (lihat onTipeOrderChange()).
                 Ruang cadangan/lebar diatur agar melintang penuh di semua breakpoint supaya
                 isian yang panjang (nomor PO bisa 20+ karakter) tidak terpotong. --}}
            @unless($notaKunci)
            <x-form.group label="No. PO" id="wrapper_no_po"
                class="hidden @xs:col-span-2 @md:col-span-3 @lg:col-span-4 @2xl:col-span-6">
                <x-form.input type="text" id="no_po" placeholder="Nomor PO dari konsumen" />
            </x-form.group>
            @endunless
        </div>
    </x-card>

    {{-- ===================================================================
         BAGIAN 2 & 3 - DETAIL PESANAN (kiri) + KERANJANG (kanan)
         Dua container terpisah; keranjang sticky agar Total & tombol Simpan
         selalu terlihat sambil kasir mengisi item.
         =================================================================== --}}
    <div class="pos-order-layout flex flex-col xl:flex-row gap-4 items-start">

        {{-- min-w-0 itu wajib, bukan kosmetik.

         Flex item secara bawaan punya min-width:auto, artinya TIDAK BOLEH
         menyusut di bawah lebar min-content-nya. Keranjang yang isinya
         panjang (nama produk, nama file, nominal) membuat min-content-nya
         membesar; tanpa min-w-0 keranjang merebut lebar, kolom kiri tertahan
         di ~331px, kontainer jatuh di bawah ambang @lg, dan dua kolom
         Detail Pesanan runtuh jadi satu kolom - persis kondisi yang harus
         dicegah. Dengan min-w-0, pembagian 62:38 dipatuhi dan isi yang
         kepanjangan dipotong di dalam kotaknya, bukan dengan mendorong
         kolom lain. --}}
        <div class="pos-order-form w-full xl:flex-[62] min-w-0 space-y-4">

{{-- ============ DETAIL PESANAN ============
     Dua langkah berurutan, bukan satu deretan field: pilih produk dulu (01),
     baru isi detail dan ukurannya (02). Urutan itu bukan hiasan - select
     produk tetap terkunci sampai kategori benar-benar dipilih, jadi
     menampilkan semua field dalam satu baris membuat operator mengira formnya
     rusak.

     Yang berubah di sini HANYA susunan markup dan teksnya. Semua id, atribut
     onchange, dan wrapper visibility (wrapper_siap_cetak, wrapper_ukuran,
     wrapper_qty, note_ukuran) dibiarkan persis seperti semula supaya tidak
     ada logika atau pengikatan JS yang tersentuh. --}}
<x-card class="@container !p-4">
    <div class="flex items-center gap-3 mb-3">
        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fas fa-box-open text-sm"></i></span>
        <div class="min-w-0">
            <h3 class="text-sm font-medium text-slate-800">Detail Pesanan</h3>
            <p class="text-[11px] text-slate-400 leading-tight">Produk, ukuran, finishing, dan biaya item</p>
        </div>
    </div>

{{-- Breakpoint-nya @lg (32rem / 512px), bukan @xl (36rem / 576px).

         Skala container Tailwind v4 BUKAN sama dengan skala breakpoint:
         @md = 28rem (448px), @lg = 32rem (512px), @xl = 36rem (576px).

         Kenapa turun ke @lg. Kartu ini hanya mengisi 62% lebar halaman
         (keranjang mengambil sisanya), jadi lebarnya ikut turun dan tidak
         pernah menyentuh ambang @xl. Terukur di layar 1339px: kolom kiri
         577px, kartu di dalamnya 545px - 31px di bawah ambang 576px, jadi
         @xl TIDAK PERNAH aktif. Akibatnya dua kolom yang justru diminta
         operator baru benar-benar muncul di layar lebar, sementara 1280px
         sampai sekitar 1400px - termasuk 1366px, resolusi laptop paling
         umum - tetap tampil satu kolom. Di ambang itu tiap kolom hanya
         246px: cukup untuk dropdown produk berdampingan tombol cari
         (200px + 38px + gap), sedangkan di @md (448px) hanya 224px dan
         select-nya benar-benar terpotong. --}}
    <div class="grid grid-cols-1 @lg:grid-cols-2 gap-x-6 gap-y-4 p-3.5 bg-slate-50/70 border border-slate-100 rounded-xl">

        {{-- ---------- 01: Produk & Pengaturan ---------- --}}
        <div class="min-w-0">
            <div class="flex items-start gap-2.5 mb-3.5">
                <span class="w-6 h-6 shrink-0 rounded-full bg-[var(--brand-accent-light)] text-white text-[11px] font-semibold flex items-center justify-center">01</span>
                <div class="min-w-0 pt-0.5">
                    <p class="text-[13px] font-medium text-slate-800 leading-tight">Produk &amp; Pengaturan</p>
                    <p class="text-[11px] text-slate-400 leading-tight">Pilih sesuai pesanan</p>
                </div>
            </div>

            <div class="space-y-2.5">
                <x-form.group label="Kategori">
                    <x-form.select id="filter_kategori" onchange="filterProduk(this.value)" autocomplete="off" tabindex="2">
                        <option value="all">Semua Kategori</option>
                        @foreach($kategori as $kat)
                            <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>

                <x-form.group label="Pilih Produk">
                    <select id="select_produk" onchange="getDetailProduk()" class="select2-produk w-full" disabled data-qp-saring="#filter_kategori" data-qp-kunci="Pilih kategori dulu, baru produk." tabindex="3">
                        <option value="">-- Ketik Nama Produk --</option>
                        @foreach($produk as $p)
                            <option value="{{ $p->id_produk }}" data-kategori="{{ $p->id_kategori }}" data-metode="{{ $p->metode_hitung }}" data-siap-cetak="{{ in_array((int) $p->id_produk, $produkSiapCetak, true) ? 1 : 0 }}">{{ $p->nama_produk }}</option>
                        @endforeach
                    </select>
                    <p id="hint_kunci_produk" class="text-[11px] text-amber-600 mt-1 ml-1">
                        <i class="fas fa-lock text-[10px]"></i> Pilih kategori dulu.
                    </p>
                </x-form.group>

                <div>
                    <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Urgensi</label>
                    {{-- Padding px-0.5, bukan px-1.5, dan teks TIDAK pakai
                         kelas kecil apa pun sebagai pengatur ruang.

                         app.css memaksa seluruh kelas .text-[8px] sampai
                         .text-xs menjadi 13px !important (satu aturan global
                         yang tidak boleh dilanggar dari halaman ini). Jadi
                         teks yang benar-benar tampil SELALU 13px, dan "Mendesak"
                         butuh 65px. Tombol Urgensi hanya 73px di kolom 231px,
                         jadi px-1.5 menyisakan 59px - kurang 6px dan label
                         terpotong. px-0.5 menyisakan 67px, cukup. --}}
                    {{-- Dua opsi saja: Normal dan Urgent.

                         Opsi ketiga ("Very Urgent" / label "Mendesak")
                         DIHAPUS atas permintaan operator 16 Okt 2026.
                         Alasannya bukan sekadar tampilan: kasir selama ini
                         punya tiga tombol tapi tidak pernah memakai
                         yang tertinggi, dan setiap pilihan berarti satu
                         keputusan tambahan tanpa manfaat operasional.

                         Yang SENGAJA tidak diubah:
                           - Kolom di database tetap
                             enum('Normal','Urgent','Very Urgent') di
                             transaksi_detail. Mengubah enum butuh migration,
                             dan itu prohibited tanpa persetujuan terpisah.
                           - PenjualanController masih menerima 'Very Urgent'
                             di validasinya (baris 346), jadi order lama
                             atau kiriman API lama tidak akan ditolak.
                           - resources/views/penjualan/show.blade.php:124
                             masih punya cabang badge 'Very Urgent'.
                           Semua itu aman: hasil pemeriksaan oriondb pada
                           16 Okt 2026 menunjukkan seluruh 6 baris
                           transaksi_detail bernilai 'Normal', jadi tidak ada
                           data lama yang perlu ditampilkan ulang.

                         Sekarang grid-cols-2, bukan 3, jadi tiap tombol
                         mendapat ~112px dan label tidak pernah terpotong. --}}
                    <div class="grid grid-cols-2 gap-1.5" role="radiogroup" aria-label="Status urgensi">
                        <label class="flex min-w-0 h-[38px] items-center justify-center gap-1.5 rounded-lg pil-pilih px-2 text-[11px] font-medium text-slate-700 cursor-pointer"
                               title="Pengerjaan sesuai antrean normal">
                            <input type="radio" name="status_urgensi_radio" value="Normal" checked onchange="setUrgensiRadio(this.value)" class="sr-only" tabindex="4">
                            <span class="truncate">Normal</span>
                        </label>
                        <label class="flex min-w-0 h-[38px] items-center justify-center gap-1.5 rounded-lg pil-pilih px-2 text-[11px] font-medium text-slate-700 cursor-pointer"
                               title="Dikerjakan lebih cepat dari Normal">
                            <input type="radio" name="status_urgensi_radio" value="Urgent" onchange="setUrgensiRadio(this.value)" class="sr-only">
                            <span class="truncate">Urgent</span>
                        </label>
                    </div>
                    {{-- Select tersembunyi tetap jadi sumber nilai canonical supaya
                         seluruh kode yang membacanya (tambahKeKeranjang, editItem,
                         resetInputProduk) tidak ikut berubah. --}}
                    <select id="input_status_urgensi" class="sr-only" tabindex="-1" aria-hidden="true">
                        <option value="Normal" selected>Normal</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>

                <div>
                    <label for="input_catatan_produksi" class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Catatan Produksi</label>
                    <textarea id="input_catatan_produksi" rows="1" tabindex="5"
                              placeholder="Tulis catatan ... (opsional)"
                              class="w-full h-[38px] min-h-[38px] resize-none bg-white border border-slate-200 rounded-lg px-3 py-2 text-[13px] leading-none text-slate-700 focus:border-[var(--brand-accent-light)] focus:ring-2 focus:ring-[var(--brand-accent-light)]/15 transition-colors"></textarea>
                </div>
            </div>
        </div>

        {{-- ---------- 02: Detail Desain & Ukuran ---------- --}}
        <div class="min-w-0">
            <div class="flex items-start gap-2.5 mb-3.5">
                <span class="w-6 h-6 shrink-0 rounded-full bg-[var(--brand-accent-light)] text-white text-[11px] font-semibold flex items-center justify-center">02</span>
                <div class="min-w-0 pt-0.5">
                    <p class="text-[13px] font-medium text-slate-800 leading-tight">Detail Desain &amp; Ukuran</p>
                    <p class="text-[11px] text-slate-400 leading-tight">Upload file lebih dulu</p>
                </div>
            </div>

            <div class="space-y-2.5">
                <div>
                    <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Gambar File</label>
                    <div class="flex items-stretch gap-2">
                        <div class="relative shrink-0 w-[92px] h-[92px] rounded-lg bg-white border border-slate-200 overflow-hidden">
                            <img loading="lazy" decoding="async" alt="Pratinjau gambar desain"
                                 id="thumbnail_preview"
                                 class="hidden w-full h-full object-contain">
                            <span id="thumbnail_kosong" class="absolute inset-0 flex items-center justify-center text-slate-300 text-lg"><i class="fas fa-image"></i></span>
                            {{-- Berkas desain yang tidak bisa dirender peramban
                                 (CDR, AI, EPS, PSD): tampilkan thumbnail
                                 default, bukan ikon generik - supaya kotaknya
                                 tidak kosong. Nama file tetap dari berkas
                                 asli (lihat toast & kolom Nama File). --}}
                            <img id="thumbnail_berkas" src="{{ asset('storage/default-thumbnail.png') }}" alt="Berkas desain" class="hidden absolute inset-0 w-full h-full object-contain opacity-50">
                        </div>
                        <label for="input_thumbnail"
                               class="flex-1 min-w-0 flex flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-slate-300 bg-white hover:border-[var(--brand-accent-light)] hover:bg-[var(--brand-accent-light)]/5 cursor-pointer transition-colors px-3 py-2 text-center">
                            <i class="fas fa-cloud-arrow-up text-[var(--brand-accent-light)] text-base"></i>
                            <span class="text-[11px] font-medium text-slate-700">Pilih File</span>
        <x-hint tip="Gambar (JPG, PNG, WEBP, GIF, BMP) atau PDF (otomatis jadi gambar) atau CDR/AI/EPS - maks 10 MB, dokumen maks 39 MB" icon="file-arrow-up" />
                        </label>
                        <input type="file" id="input_thumbnail" tabindex="6"
                               accept="image/png,image/jpeg,image/webp,image/gif,image/bmp,image/svg+xml,image/tiff,application/pdf,.pdf,.cdr,.ai,.eps,.psd,.tif,.tiff,.heic"
                               onchange="uploadThumbnail(this)"
                               class="sr-only">
                    </div>
                </div>

                <x-form.group label="Nama File / Deskripsi">
                    <x-form.input type="text" id="nama_file" placeholder="Ganti sesuai kebutuhan" />
                </x-form.group>

                {{-- Jenis Pengerjaan: hanya untuk produk berbiaya File Siap Cetak (lihat getDetailProduk). --}}
                <div id="wrapper_siap_cetak" class="hidden">
                    <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Jenis Pengerjaan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-1.5 h-[38px] rounded-lg pil-pilih px-2 text-[11px] font-medium text-slate-700 cursor-pointer"
                               title="Harga daftar harga biasa; boleh ditambah biaya desain">
                            <input type="radio" name="jenis_pengerjaan" value="desain" checked onchange="setSiapCetak(false)" class="sr-only">
                            <span class="truncate">Dengan Desain</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 h-[38px] rounded-lg pil-pilih px-2 text-[11px] font-medium text-slate-700 cursor-pointer"
                               title="File sudah jadi dari pelanggan; memakai harga File Siap Cetak, tanpa biaya desain">
                            <input type="radio" name="jenis_pengerjaan" value="siap" onchange="setSiapCetak(true)" class="sr-only">
                            <span class="truncate">File Siap Cetak</span>
                        </label>
                    </div>
                </div>

                {{-- UKURAN: 3 field dalam 1 baris. --}}
                <div id="wrapper_ukuran" class="hidden">
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Panjang (cm)</label>
                            <input type="number" id="input_p" onchange="hitungSubtotal()" tabindex="7" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-[13px] font-medium text-slate-700 text-center focus:border-[var(--brand-accent-light)] focus:ring-2 focus:ring-[var(--brand-accent-light)]/15">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Lebar (cm)</label>
                            <input type="number" id="input_l" onchange="hitungSubtotal()" tabindex="7" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-[13px] font-medium text-slate-700 text-center focus:border-[var(--brand-accent-light)] focus:ring-2 focus:ring-[var(--brand-accent-light)]/15">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Qty</label>
                            <input type="number" id="input_qty_ukuran" onchange="hitungSubtotal()" tabindex="7" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-[13px] font-medium text-slate-700 text-center focus:border-[var(--brand-accent-light)] focus:ring-2 focus:ring-[var(--brand-accent-light)]/15" value="1">
                        </div>
                    </div>
                    <p id="note_ukuran" class="hidden mt-2 text-[11px] font-medium text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 leading-snug">
                        <span id="note_ukuran_teks">Ukuran untuk menghitung harga. Gunakan ukuran file asli.</span>
                    </p>
                </div>

                <div id="wrapper_qty" class="hidden">
                    <label class="block text-[10px] font-medium text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Jumlah Unit</label>
                    <input type="number" id="input_qty_biasa" onchange="hitungSubtotal()" class="w-full bg-white border border-slate-200 rounded-lg py-2.5 px-3 text-[13px] font-medium text-slate-700 text-center focus:border-[var(--brand-accent-light)] focus:ring-2 focus:ring-[var(--brand-accent-light)]/15" value="1">
                </div>
            </div>
        </div>
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
                <div id="min_order_warning" hidden class="mb-4 p-3 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                    <div>
                        <p class="text-xs font-medium text-red-700 uppercase tracking-wide">Input di bawah minimum order produk ini</p>
                        <p id="min_order_warning_detail" class="text-xs text-red-600 mt-1"></p>
                    </div>
                </div>

{{-- ---------- HARGA ESTIMASI ----------
     Satu kartu sendiri, bukan baris di dalam form. Alasannya: ini angka
     yang paling sering dibaca kasir, jadi harus menonjol sebagai satu blok,
     bukan menyamar jadi field lain.

     /unit pindah ke kanan atas supaya tidak dibaca sebagai bagian dari
     nominal besar - pemisahan posisi itu yang membuatoperator tidak salah
     mengira total sudah termasuk harga satuan. --}}
                <div class="mt-4 rounded-xl bg-[var(--brand-accent-light)]/[0.06] border border-[var(--brand-accent-light)]/15 px-4 py-3.5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-medium text-[var(--brand-accent-light)] uppercase tracking-widest">Harga Estimasi</p>
                        <h2 id="display_subtotal" class="text-3xl font-medium text-[var(--brand-accent-light)] tracking-tight leading-tight tabular-nums">Rp 0</h2>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <p id="status_hitung" class="hidden text-amber-700 text-xs font-medium"><i class="fas fa-circle-notch fa-spin mr-1"></i> Menghitung...</p>
                        <span id="badge_promo" hidden class="inline-flex items-center rounded-md bg-rose-600 px-2 py-0.5 text-[10px] font-medium text-white">Harga promo</span>
                        <p id="display_qty" hidden class="rounded-lg border border-[var(--brand-accent-light)]/25 bg-white px-2.5 py-1 text-[var(--brand-accent-light)] text-xs font-medium tabular-nums"></p>
                        <p id="display_harga" class="rounded-lg border border-[var(--brand-accent-light)]/25 bg-white px-2.5 py-1 text-[var(--brand-accent-light)] text-xs font-medium tabular-nums">Rp 0 /unit</p>
                    </div>
                </div>

                {{-- ---------- FINISHING & BIAYA ---------- --}}
                <div class="mt-4 flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0"><i class="fas fa-gear text-xs"></i></span>
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-slate-800 leading-tight">Finishing &amp; Biaya</p>
                        <p class="text-[11px] text-slate-400 leading-tight">Pilih sesuai pesanan</p>
                    </div>
                </div>
{{-- Tiga kolom.

         Tiga kolom sama lebar, tanpa col-span. Percobaan sebelumnya memberi Finishing dan Catatan Finishing masing-masing 2 kolom supaya nama panjang muat - tapi efeknya kolom ketiga selalu
         kosong, sehingga hasilnya IDENTIK dengan 2 kolom.
         Baris pertama: Finishing, Spek Finishing, Catatan Finishing. Baris kedua: Biaya Cetak, lalu field kondisional yang ikut muncul (Jumlah Pcs, Biaya Finishing, Biaya Desain, Desainer).

         Tiga kolom sebagai batas DASAR, bukan lewat container query. Versi sebelumnya memakai @xl (36rem / 576px), tapi kartu input order hanya mengisi 62% lebar halaman - keranjang mengambil
         sisanya - sehingga kontainernya tetap di bawah ambang itu di layar 1280px pun, dan hasilnya tetap 2 kolom. Yang diukur operator adalah lebar jendela, bukan lebar kartu. --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-3 gap-y-2.5">
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Finishing</label>
                        <select id="select_finishing" onchange="onFinishingChange()" class="w-full bg-slate-50 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                            <option value="">-- Tanpa Finishing --</option>
                            @foreach($finishing as $f)
                                <option value="{{ $f->id_finishing }}" data-harga="{{ $f->harga_default ?? 0 }}" data-minimum="{{ $f->minimum_charge ?? 0 }}" data-metode="{{ $f->metode_hitung }}" data-kategori="{{ $f->id_kategori ?? '' }}">{{ $f->nama_finishing }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Spek Finishing</label>
                        <select id="input_status_finishing" onchange="onStatusFinishingChange()" disabled class="w-full bg-slate-50 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner disabled:opacity-40 disabled:cursor-not-allowed">
                            <option value="Standar">Standar</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Catatan Finishing</label>
                        <input type="text" id="input_catatan_finishing" maxlength="255" placeholder="mis. Doff" class="w-full bg-slate-50 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    </div>
                    {{-- Biaya Cetak = komponen UTAMA subtotal (Cetak + Desain + Finishing, lihat hitungSubtotal()). Mengikuti Harga x Qty
                         sampai operator mengetik manual (cetakManualEdit). --}}
                    <div>
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Biaya Cetak (Rp)</label>
                        <input type="text" inputmode="numeric" id="input_cetak" @unlessbolehHarga readonly @endbolehHarga onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan" value="0">
                    </div>
                    {{-- Jumlah pcs finishing hanya untuk finishing metode 'quantity' (mis. Pasang Mata Ayam) - kasir wajib cek/sesuaikan. --}}
                    <div id="wrapper_qty_finishing" class="hidden">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jumlah Pcs Finishing</label>
                        <input type="number" min="1" id="input_qty_finishing" onchange="onQtyFinishingManualChange()" class="w-full bg-slate-50 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner" value="1">
                    </div>
                    {{-- Biaya Finishing hanya muncul begitu jenis Finishing dipilih (lihat onFinishingChange()). --}}
                    <div id="wrapper_biaya_finishing" class="hidden">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Biaya Finishing (Rp)</label>
                        <input type="text" inputmode="numeric" id="input_finishing_biaya" @unlessbolehHarga readonly @endbolehHarga onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-lg py-2.5 px-3 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan" value="0">
                    </div>
                    
                    {{-- Panel Potong Manual LINEAR_GRID.
     Satuan CM, sama dengan field Panjang/Lebar media di form, jadi operator
     cukup memakai angka yang sama tanpa mengonversi ke meter.
     (Meter tetap dipakai saat menghitung: cm / 100.)
     Sengaja RAMPAM supaya tidak menambah beban scrolling:
        - col-span-full: issuekan baris penuh grid (3 kolom) supaya 3 field muat
         lega tanpa membuat kolom di sebelahnya tergeser.
       - 3 field dalam SATU baris, bukan 2 baris + 1 baris penuh.
       - padding, tinggi input, dan font lebih kecil dari form utama.
     Tinggi akhir +/- 70px (semula +/- 190px). --}}
                    <div id="panel_linear_grider" class="hidden col-span-full grid grid-cols-3 gap-2 mt-1 px-3 py-2 bg-indigo-50 border border-indigo-100 rounded-xl">
                        <div>
                            <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Lebar Potong (cm)</label>
                            <input type="number" step="0.1" inputmode="decimal" id="input_lebar_potong" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20" min="0" value="0" onchange="segarkanBiayaPotongManual()">
                        </div>
                        <div>
                            <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Tinggi Potong (cm)</label>
                            <input type="number" step="0.1" inputmode="decimal" id="input_tinggi_potong" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20" min="0" value="0" onchange="segarkanBiayaPotongManual()">
                        </div>
                        <div>
                            <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Jarak (cm) - belum dipakai</label>
                            <input type="number" step="0.01" inputmode="decimal" id="input_jarak_potong" class="w-full bg-slate-100 border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-400 text-center shadow-inner cursor-not-allowed" min="0" value="0" disabled title="Belum dipakai rumus biaya. Kosongkan dulu.">
                        </div>
                        {{-- UKURAN MEDIA yang dipotong.

                             Produk ber-metode_hitung 'ukuran' (roll/banner) sudah
                             punya Panjang/Lebar di form utama, jadi kedua field
                             ini otomatis mengikuti dan dikunci.

                             Produk yang dijual PER LEMBAR (kategori A3: AC, HVS,
                             foto 10R, dll) TIDAK punya Panjang/Lebar sama
                             sekali di form - tanpa field ini rumus potong
                             tidak punya sisi media dan biaya potong tidak
                             bisa dihitung. Di situ operator mengisi ukuran
                             lembar yang dipotong (mis. A3 = 42 x 29,7 cm). --}}
                        <div class="col-span-3 grid grid-cols-2 gap-2 pt-2 border-t border-indigo-100">
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Lebar Media (cm)</label>
                                <input type="number" step="0.1" inputmode="decimal" id="input_lebar_media_potong" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20" min="0" value="0" onchange="segarkanBiayaPotongManual()">
                            </div>
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Tinggi Media (cm)</label>
                                <input type="number" step="0.1" inputmode="decimal" id="input_tinggi_media_potong" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-indigo-500/20" min="0" value="0" onchange="segarkanBiayaPotongManual()">
                            </div>
                        </div>
                        <p class="col-span-3 text-[10px] leading-snug text-indigo-700/90">
                            Biaya = meter garis potong &times; tarif master <span id="potong_manual_minimum">minimum charge dari master finishing</span>. Isi ukuran lembar yang dipotong bila produknya dijual per lembar (A3). Potong dimensi kecil tetap ikut rumus.
                            <br>Harga potong manual: ubah <b>Spek Finishing</b> menjadi <b>Custom</b>, lalu isi <b>Biaya Finishing</b> (butuh hak ubah harga).
                        </p>
                    </div>
                    {{-- Panel UKURAN KEPING (KEPING_ROLL) versi 2 (keputusan
     owner 2026-11-10): keping adalah INPUTAN operator - lebar keping x
     panjang keping x jumlah keping - dan yang menentukan input itu masuk
     akal atau tidak adalah ukuran cetak barang yang diisi di form utama,
     BUKAN lebar roll bahan.

     Rumus (identik dengan PenjualanCheckoutService::meterKepingTerjaga):
       kapasitasPerBarang = MAKS(
           floor(l / lebar keping) x floor(p / panjang keping),   // orientasi A
           floor(l / panjang keping) x floor(p / lebar keping)    // keping diputar 90 derajat
       )
       kapasitasTotal = kapasitasPerBarang x QTY CETAK barang
       1 <= jumlah keping <= kapasitasTotal
     File 2-up (owner 2026-11-10: media cetak 102 x 304 cm, 4 pcs, berisi
     2 layout 50 x 300) memenuhi 8 keping: 2 per barang x 4 = 8, lalu
     jahit = 8 x (2 x (0,50+3,00) m) = 56,00 m x Rp 1.500 = Rp 84.000
     (master 'Keping Roll' id 21, tanpa ubah master).
     Contoh (cetak 120 x 500 cm): 50x500 2pcs & 60x245 4pcs boleh,
     50x600 2pcs & 50x400 3pcs DITOLAK.

     Panel menampilkan evaluasi kapasitas SEBELUM simpan - kondisi yang
     nanti ditolak server berwarna merah di sini. Tarif dihitung PER METER
     (jasa jahit, keputusan owner): meter jahit total = jumlah keping x
     KELILING keping (2 x (lebar+panjang)), dari tier master Finishing
     (get-harga-finishing); TIDAK dikalikan qty cetak barang. --}}
                    <div id="panel_keping_roll" class="hidden col-span-full grid grid-cols-3 gap-2 mt-1 px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-xl">
                        {{-- Baris 1: ukuran cetak yang SEDANG dihitung (cerminan
     form utama, readonly) - dasar dari hitungan kapasitas keping. --}}
                        <div>
                            <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Panjang Cetak (cm)</label>
                            <input type="number" id="input_panel_p" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner" disabled title="Cerminan field Panjang di form utama">
                        </div>
                        <div>
                            <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Lebar Cetak (cm)</label>
                            <input type="number" id="input_panel_l" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner" disabled title="Cerminan field Lebar di form utama">
                        </div>
                        <div>
                            <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Qty Cetak</label>
                            <input type="number" id="input_panel_qty" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner" disabled title="Cerminan field Qty di form utama">
                        </div>
                        {{-- Baris 2: INPUTAN keping dari operator. --}}
                        <div class="col-span-3 grid grid-cols-3 gap-2 pt-2 border-t border-emerald-100">
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Lebar Keping (cm)</label>
                                <input type="number" step="0.1" inputmode="decimal" min="0" id="input_keping_lebar" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-emerald-500/20" title="Lebar satu keping jahit" onchange="segarkanPanelKepingRoll(); if(!finishingBiayaManualEdit) refreshFinishingBiayaSuggestion();">
                            </div>
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Panjang Keping (cm)</label>
                                <input type="number" step="0.1" inputmode="decimal" min="0" id="input_keping_panjang" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-emerald-500/20" title="Panjang satu keping jahit" onchange="segarkanPanelKepingRoll(); if(!finishingBiayaManualEdit) refreshFinishingBiayaSuggestion();">
                            </div>
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Jumlah Keping (pcs)</label>
                                <input type="number" step="1" inputmode="numeric" min="0" id="input_keping_jumlah" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner focus:ring-2 focus:ring-emerald-500/20" title="Jumlah keping untuk barang ini" onchange="segarkanPanelKepingRoll(); if(!finishingBiayaManualEdit) refreshFinishingBiayaSuggestion();">
                            </div>
                        </div>
                        {{-- Baris 3: hasil evaluasi live - kapasitas keping yang
     muat dalam ukuran cetak + saran total biaya (tarif per meter x meter
     jahit total). --}}
                        <div class="col-span-3 grid grid-cols-2 gap-2 pt-2 border-t border-emerald-100">
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Kapasitas Keping (pcs)</label>
                                <input type="number" id="input_keping_kapasitas" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner" disabled title="MAKS orientasi A/B lalu dikali qty cetak barang">
                            </div>
                            <div>
                                <label class="block text-[9px] font-medium text-slate-400 uppercase tracking-wider mb-1 ml-1">Saran Total Biaya</label>
                                <input type="text" id="input_keping_total" class="w-full bg-white border-none rounded-lg py-2 px-2 text-xs font-medium text-slate-700 text-center shadow-inner" disabled title="tarif per meter x meter jahit total (jumlah keping x keliling keping), dari master Finishing">
                            </div>
                        </div>
                        <p id="pesan_keping_roll" class="col-span-3 text-[10px] leading-snug text-indigo-700/90">
                            Isi ukuran &amp; jumlah keping - keping wajib muat dalam ukuran cetak (kapasitas). SUMBER ANGKA sama dengan server.
                        </p>
                    </div>
                    {{-- Field "Jarak (cm)" di atas sengaja dikunci.

                         Hasil audit 2026-11-02: field ini ada di form sejak awal
                         dan operator bisa mengisinya, tetapi nilainya TIDAK
                         pernah dipakai di mana pun -

                           - tidak dikirim ke server; create.blade.php hanya
                             mengirim lebar_potong, tinggi_potong, dan
                             tarif_per_meter;
                           - tidak ada kolom 'jarak' di tabel mana pun di oriondb;
                           - tidak dibaca HargaService::hitungBiayaFinishingLinearGrid()
                             maupun kalkulasiGridLinearGrid() di layar.

                         Operator yang mengisi 0,5 cm akan melihat biaya yang
                         tetap sama tanpa tanda bahwa angkanya diabaikan, dan
                         itu mudah salah dibaca sebagai kesalahan hitung.

                         Mengaktifkannya berarti keputusan bisnis: jarak itu
                         kerangka antar keping (pitch = ukuran potong + jarak),
                         lebar bilah pisau, atau margin trim. Ketiganya
                         mengubah rumus dan menghasilkan nominal berbeda.
                         Perlu diputuskan operator dulu, lalu diterapkan
                         sekaligus di server, di layar, dan di migration.

                         Bila diputuskan tidak dipakai sama sekali, blok ini
                         boleh dihapus seluruhnya - tidak ada kode lain yang
                         mereferensikan input_jarak_potong. --}}
                    {{-- Biaya Desain disembunyikan default; muncul lewat tombol "+ Biaya Desain" (tampilkanBiayaDesain()). --}}
                    <div id="wrapper_biaya_desain" class="hidden">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Biaya Desain (Rp)</label>
                        <input type="text" inputmode="numeric" id="input_desain" @unlessbolehDesain readonly @endbolehDesain onchange="hitungSubtotal()" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 text-right focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan" value="0">
                    </div>
                    {{-- Desainer yang menerapkan biaya desain (untuk monitoring & komisi/bagi hasil) --}}
                    <div id="wrapper_desainer" class="hidden" title="Operator desain yang mengerjakan desain ini - dipakai untuk laporan biaya &amp; komisi desain">
                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Desainer</label>
                        @if($desainerTerkunci)
                            {{-- TERKUNCI: yang membuat nota adalah Operator Desain,
                                 jadi desainernya adalah dirinya sendiri. Tidak ada
                                 dropdown - nama desainer lain tidak boleh dipilih
                                 di sini (server juga mengunci, lihat
                                 DesainKomisiService::kunciDesainer). --}}
                            <input type="hidden" id="input_desainer" name="id_desainer" value="{{ $desainerTerkunci }}">
                            <div class="w-full bg-slate-100 border border-slate-200 rounded-xl py-3 px-4 text-sm font-medium text-slate-700 flex items-center gap-2.5">
                                <i class="fas fa-user-check text-slate-400 text-xs shrink-0"></i>
                                <span class="truncate">{{ $desainerTerkunciNama }}</span>
                                <span class="ml-auto text-[11px] font-normal text-slate-400 shrink-0">otomatis (Anda)</span>
                            </div>
                        @else
                            <select id="input_desainer" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                                <option value="">-- Tanpa Desainer --</option>
                                @foreach($desainerList as $ds)
                                    <option value="{{ $ds->id_user }}" {{ (int) $desainerDefault === (int) $ds->id_user ? 'selected' : '' }}>{{ $ds->nama }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                {{-- BARIS AKSI: mt-4 pt-3 (bukan mt-6 pt-5) supaya tombol "Tambah" tidak
                     terdorong jauh ke bawah oleh baris kosong di atasnya. --}}
                <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="btn_tambah_finishing" onclick="tambahFinishingKeDaftar()" class="hidden inline-flex items-center gap-1.5 bg-sky-500 hover:bg-sky-600 text-white px-3.5 py-2 rounded-lg font-medium text-[10px] uppercase tracking-widest shadow-sm transition-all whitespace-nowrap">
                            <i class="fas fa-plus"></i> Tambah Finishing
                        </button>
                        <button type="button" id="btn_tambah_desain" @unlessbolehDesain hidden @endbolehDesain onclick="tampilkanBiayaDesain()" class="text-indigo-500 hover:text-indigo-700 text-[10px] font-medium uppercase tracking-widest underline underline-offset-4 decoration-dotted whitespace-nowrap">
                            <i class="fas fa-plus-circle"></i> Biaya Desain
                        </button>
                    </div>
                    <button onclick="tambahKeKeranjang()" class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-lg font-medium text-xs uppercase tracking-wider shadow-sm transition-all active:scale-95 whitespace-nowrap">
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
                    <i id="icon_toggle_jasa" class="fas fa-chevron-down text-slate-500 ml-auto transition-transform"></i>
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
                        <button onclick="tambahJasaKeKeranjang()" class="inline-flex items-center justify-center gap-2 bg-purple-500 hover:bg-purple-600 text-white px-8 py-3.5 rounded-xl font-medium text-xs uppercase tracking-wider shadow-sm transition-all active:scale-95">
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
        {{-- min-w-0:-coupled dengan min-w-0 di kolom Detail Pesanan. Tanpa itu,
             flex item keranjang menolak menyusut di bawah min-content dan
             merebut lebar dari kolom kirinya. --}}
        <div class="pos-cart-panel w-full xl:flex-[38] min-w-0 flex flex-col xl:sticky xl:top-20 xl:self-start">
            {{-- max-h SEHARUSNYA 100vh dikurangi tinggi top (top-24 = 6rem)
                 dikurangi jarak aman di bawah. Sebelumnya 8rem: itu 128px,
                 padahal ruang di bawah top hanya 100vh - 96px, jadi bagian
                 bawah keranjang selalu keluar layar ~32px - tombol Simpan
                 justru yang paling penting dan justru terpotong. --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col overflow-hidden lg:max-h-[calc(100vh-7rem)]">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fas fa-shopping-basket"></i></span>
                        <div class="min-w-0">
                            <h3 class="text-base font-medium text-slate-800 leading-tight">Keranjang Pesanan</h3>
                            @if($notaSusulan)
                                {{-- Di mode susulan keranjang hanya berisi item BARU.
                                     Item lama nota ditampilkan di panel terkunci di
                                     atasnya, jadi tidak tercampur. --}}
                                <p class="text-[11px] text-slate-400 leading-tight">Item susulan baru</p>
                            @endif
                        </div>
                    </div>
                    <div id="item_count_badge" class="bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg text-[11px] font-medium uppercase tracking-wider">{{ $notaSusulan ? '0 Item Baru' : '0 Items' }}</div>
                </div>

                @if($notaSusulanItems->isNotEmpty())
                    {{-- ISI NOTA SAAT INI - tampil read-only di atas keranjang.

                         Gunanya: operator harus tahu persis item apa yang SUDAH
                         tercatat di nota sebelum menambah yang baru, supaya
                         tidak ada item yang terlewat atau sengaja diduplikasi.

                         Sengaja BACA SAJA di sini. Mengubah isi nota yang sudah
                         berjalan punya jalurnya sendiri ("Edit Nota" = batal +
                         buat ulang) yang membalik stok, jurnal, dan pembayaran
                         berurutan serta ikut tunduk pada persetujuan edit di
                         Pengaturan Aplikasi. Item lama yang bisa diedit lewat
                         keranjang susulan akan menghasilkan BARIS DOBEL saat
                         disimpan, bukan koreksi. --}}
                    <div class="px-5 py-4 border-b border-slate-100 bg-amber-50/50 max-h-[28vh] overflow-y-auto custom-scrollbar">
                        <div class="flex items-center justify-between gap-2 mb-2.5">
                            <p class="text-[10px] font-medium uppercase tracking-widest text-amber-800 flex items-center gap-1.5">
                                <i class="fas fa-lock text-[9px]"></i> Sudah ada di nota ini
                            </p>
                            <span class="text-[11px] font-medium text-amber-700 tabular-nums">{{ count($notaSusulanItems) }} item &bull; Rp {{ number_format($notaSusulanItems->sum('subtotal'), 0, ',', '.') }}</span>
                        </div>
                        <ul class="space-y-2">
                            @foreach($notaSusulanItems as $itemLama)
                                @php
                                    $namaFinishingLama = collect($notaSusulanFinishing->get($itemLama->id_detail, collect()))->pluck('nama_finishing')->filter()->all();
                                    $judulLama = $itemLama->nama_produk ?: ($itemLama->nama_jasa ?: $itemLama->nama_file);
                                @endphp
                                <li class="flex items-start gap-2.5 bg-white/70 border border-amber-100 rounded-lg px-3 py-2">
                                    <span class="w-5 h-5 shrink-0 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center text-[10px] font-medium mt-0.5">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-slate-700 truncate">{{ $judulLama }}</p>
                                        <p class="text-[11px] text-slate-500 truncate">
                                            {{ $itemLama->nama_file }}
                                            @if($itemLama->p_ukuran > 0 && $itemLama->l_ukuran > 0)
                                                &bull; {{ rtrim(rtrim((string) $itemLama->p_ukuran, '0'), '.') }} x {{ rtrim(rtrim((string) $itemLama->l_ukuran, '0'), '.') }} cm
                                            @endif
                                            @if($itemLama->qty) &bull; Qty {{ (int) $itemLama->qty }} @endif
                                        </p>
                                        @if(count($namaFinishingLama))
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ implode(', ', $namaFinishingLama) }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs font-medium text-slate-700 tabular-nums">Rp {{ number_format((float) $itemLama->subtotal, 0, ',', '.') }}</p>
                                        @if($itemLama->is_susulan)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[9px] font-medium uppercase tracking-wide mt-1">Susulan</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-[11px] text-amber-700/90 mt-2.5 leading-snug">
                            Item di atas sudah tercatat di nota dan tidak bisa diubah dari layar ini. Untuk mengubah isi nota, pakai menu <b>Edit Nota</b> di Riwayat Nota.
                        </p>
                    </div>
                @endif

                <div class="flex-grow overflow-y-auto custom-scrollbar p-0 min-h-[120px]">
                    <div id="cart_body" class="divide-y divide-slate-50 text-slate-600">
                        <div class="py-16 text-center opacity-30">
                            <i class="fas fa-receipt text-2xl mb-2 block"></i>
                            <p class="text-xs font-medium uppercase tracking-widest">Belum Ada Pesanan</p>
                        </div>
                    </div>

                    {{-- Rincian kalkulasi Minimum Order per kelompok (spec owner
                         2026-11-15): diisi renderCart() - tersembunyi bila tidak
                         ada kelompok minimum di keranjang. --}}
                    <div id="minimum_rincian" hidden class="px-4 pb-4"></div>
                </div>

                <div class="mt-auto p-6 bg-slate-50 border-t border-slate-100 shrink-0">
                    <div class="flex justify-between items-center mb-5">
                        <div>
                            <p class="text-slate-400 text-[10px] font-medium uppercase tracking-widest mb-1">Total Pembayaran</p>
                            <h2 id="grand_total" class="text-3xl font-medium text-[var(--brand-accent-light)] tracking-tight leading-none">Rp 0</h2>
                        </div>
                        <button onclick="resetCart()" title="Kosongkan keranjang" class="w-11 h-11 flex items-center justify-center bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <button type="button" onclick="simpanTransaksi(this)" class="w-full inline-flex items-center justify-center gap-2 py-4 bg-[var(--brand-accent-light)] hover:bg-[#005560] text-white rounded-xl font-medium text-xs uppercase tracking-wider shadow-sm transition-colors active:scale-[0.99]">
                        @if($notaEdit)
                            <i class="fas fa-save"></i> Simpan Koreksi Nota {{ $notaEdit->nomor_nota }}
                        @elseif($notaSusulan)
                            <i class="fas fa-plus"></i> Tambahkan ke Nota {{ $notaSusulan->nomor_nota }}
                        @else
                            <i class="fas fa-print"></i> Simpan &amp; Cetak Nota
                        @endif
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
            <x-form.input type="text" id="new_nama" placeholder="Masukkan nama pelanggan..." autocomplete="off" />
            <div id="new_nama_notice" class="hidden mt-1 text-[11px] leading-snug pl-2 border-l-2 border-amber-300" role="status"></div>
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
            {{-- .pil-pilih, bukan has-[:checked]:bg-*-50: lihat catatan Tipe
                 Order di atas soal keterbacaan mode gelap. --}}
            <div class="flex gap-2">
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl pil-pilih p-3 text-xs font-medium cursor-pointer text-slate-600 transition-colors">
                    <input type="radio" name="new_tipe_konsumen" value="temporary" checked class="sr-only"> Temporary
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl pil-pilih p-3 text-xs font-medium cursor-pointer text-slate-600 transition-colors">
                    <input type="radio" name="new_tipe_konsumen" value="permanent" class="sr-only"> Permanent
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
    .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #E2EAE4 transparent; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2EAE4; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #C9D6CE; }

    /* Styling Select2 (tinggi, border, font, dropdown, scrollbar hasil
       pencarian) SUDAH dipindahkan ke resources/css/app.css supaya satu
       sumber kebenaran dipakai di semua halaman (lihat catatan di sana) -
       jangan duplikasi override di sini lagi. */
</style>
<script>
    // Live notice cek duplikat untuk modal "Daftar Konsumen Baru".
    (function () {
        var debounceT2;
        var namaInput = document.getElementById('new_nama');
        var telpInput = document.getElementById('new_hp');
        var notice = document.getElementById('new_nama_notice');
        if (!namaInput || !notice) return;

        // escHtml() global dari layouts/app (window.escHtml) - jangan dideklarasikan ulang.

        function render(exact, similar) {
            var html = '';
            if (exact) {
                html += '<div class="text-amber-800 font-medium">Nama ini sudah terdaftar (#' + exact.id_konsumen + ' Ã¢â‚¬â€ ' + escHtml(exact.nama_konsumen) + ' Ã‚Â· ' + escHtml(exact.telepon || '-') + '). Menggunakan data yang ada.</div>';
            } else if (similar && similar.length) {
                html += '<div class="text-slate-700">Nama serupa terdaftar:</div><ul class="mt-1 space-y-0.5">';
                var max = Math.min(similar.length, 6);
                for (var i = 0; i < max; i++) {
                    var s = similar[i];
                    html += '<li class="truncate"><button type="button" class="font-medium text-amber-800 hover:text-amber-900 hover:underline" data-nama="' + escHtml(s.nama_konsumen) + '" data-telp="' + escHtml(s.telepon || '') + '" data-id-golongan="' + (s.id_golongan || '') + '">'
                        + escHtml(s.nama_konsumen)
                        + '<span class="text-slate-400 font-normal"> Ã‚Â· ' + escHtml(s.telepon || '-') + ' Ã‚Â· ID #' + s.id_konsumen + '</span></button></li>';
                }
                html += '</ul>';
            }
            notice.innerHTML = html;
            notice.classList.toggle('hidden', !html);
        }

        function periksa() {
            clearTimeout(debounceT2);
            debounceT2 = setTimeout(function () {
                var n = namaInput.value.trim();
                var t = telpInput ? telpInput.value.trim() : '';
                if (n === '') { render(null, []); return; }
                var url = '{{ route('konsumen.cek-duplikat') }}?nama=' + encodeURIComponent(n) + '&telepon=' + encodeURIComponent(t);
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) { render(d.exact, d.similar); })
                    .catch(function () { notice.classList.add('hidden'); });
            }, 220);
        }

        namaInput.addEventListener('input', periksa);
        namaInput.addEventListener('change', periksa);
        if (telpInput) telpInput.addEventListener('input', periksa);

        notice.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-nama]');
            if (!btn) return;
            namaInput.value = btn.getAttribute('data-nama') || '';
            if (telpInput) telpInput.value = btn.getAttribute('data-telp') || '';
            var golonganSel = document.getElementById('new_id_golongan');
            if (golonganSel && btn.getAttribute('data-id-golongan')) golonganSel.value = btn.getAttribute('data-id-golongan');
            periksa();
        });
    })();

    // 1. GLOBAL STATE
    // Mode edit: keranjang diisi item-item nota yang dikoreksi, jadi
    // operator langsung melihat & mengubahnya di layar yang sama.
    let cart = @json($cartAwal);
    // Mode susulan: ringkas baris nota asal (ukuran/metode/minimum) - aturan
    // "produk sama >= 2 baris = minimum ditiadakan" dan akumulasi kelompok
    // minimum luas berlaku atas SATU nota (baris lama + baru), selaras dengan
    // server ($barisNotaAda di PenjualanCheckoutService). Kosong di luar
    // mode susulan.
    const ITEM_NOTA_ASAL = @json($notaSusulanMinimumJs ?? []);
    let currentProduk = null;
    // Kategori produk yang terpasang di dropdown (bukan API row - API tidak
    // mengembalikannya; kategori di POS = COALESCE(bahan_baku.id_kategori, 0)
    // hasil join BOM di dropdown). Dipakai relax minimum: hanya baris
    // se-kategori yang boleh saling menolong (keputusan owner 2026-11-12).
    let currentKategori = '0';
    let editIndex = null;
    let currentThumbnail = null;
let currentThumbnailPreview = null;
    // Menandai berkas acuan terakhir gagal karena UKURAN (bukan karena kasir
    // lupa mengunggah) - item tetap boleh masuk keranjang tanpa berkas.
    let thumbnailGagalUkuran = false;
    let currentKategoriFilter = 'all';

    // Mode susulan: layar ini menambah item ke nota yang sudah berjalan.
    // Dipisah dari MODE_SUSULAN di bawah karena dipakai juga saat render
    // keranjang (yang dipanggil sebelum konstanta itu dideklarasikan).
    const IS_SUSULAN = @if($notaSusulan) true @else false @endif;
    const IS_EDIT = @if($notaEdit) true @else false @endif;

    // Desainer terkunci (Operator Desain) dirender sebagai input tersembunyi
    // berisi id miliknya sendiri, bukan <select>. Nilainya tidak boleh
    // ditimpa dari item yang sedang diedit - server juga mengunci (lihat
    // DesainKomisiService::kunciDesainer), tampilan harusnya konsisten.
    function setNilaiDesainer(nilai) {
        const el = document.getElementById('input_desainer');
        if (!el) return;
        if (el.tagName === 'SELECT') {
            el.value = nilai || '';
        }
    }

    function onTipeOrderChange() {
        const tipe = document.querySelector('input[name="tipe_order"]:checked').value;
        var _wnp = document.getElementById('wrapper_no_po'); if (_wnp && _wnp.classList) _wnp.classList.toggle('hidden', tipe !== 'PO');
    }

    // Urgensi sekarang tampil sebagai segmented radio, sementara select
    // #input_status_urgensi disembunyikan sebagai sumber nilai canonical -
    // begini seluruh kode yang sudah membaca select itu (tambahKeKeranjang,
    // editItem, resetInputProduk) TIDAK perlu diubah sama sekali.
    //
    // Parameter 'sinkronSelect' hanya false saat dipanggil dari kode yang baru
    // saja menulis select (editItem / reset), supaya tidak menulis dua kali.
    function setUrgensiRadio(nilai, sinkronSelect = true) {
        const target = nilai || 'Normal';
        const select = document.getElementById('input_status_urgensi');

        if (sinkronSelect && select) select.value = target;

        document.querySelectorAll('input[name="status_urgensi_radio"]').forEach(function (radio) {
            radio.checked = (radio.value === target);
        });

        // Badge "Sangat Urgent" di baris keranjang ikut terisi.
        hitungSubtotal();
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

    // ---- Nama file / deskripsi item ----
    // (revisi owner 2026-11-16: KEMBALI ke perilaku awal) Deskripsi item
    // SELALU diambil dari nama berkas yang diunggah kasir - BUKAN dibangun
    // dari data order (produk/ukuran/finishing). Penyebabnya: deteksi
    // rekonsiliasi (laminasi/kisscut di nama berkas) butuh teks berkas yang
    // ASLI, bukan kalimat rapi dari data order yang menghapus kata kunci
    // pelanggan. Kasir tetap boleh menyunting kolomnya (dataset.auto
    // dihapus) sampai berkas baru diunggah.
    function isiNamaFileOtomatis(fallback) {
        const el = document.getElementById('nama_file');
        el.value = (fallback || '').trim();
        el.dataset.auto = '1';
    }

    document.getElementById('nama_file').addEventListener('input', function () {
        delete this.dataset.auto;
    });

    // Berkas melebihi batas ukuran TIDAK memblokir order: deskripsi tetap
    // memakai nama berkas yang dipilih, pratinjau jadi gambar default
    // thumbnail, dan item boleh masuk keranjang tanpa berkas - file sumber
    // besar dimintanya menyusul lewat jalur lain (flashdisk/pesan).
    function terimaBerkasTerlaluBesar(namaAsli, ukuranByte, maksMb) {
        thumbnailGagalUkuran = true;
        currentThumbnail = null;

        const preview = document.getElementById('thumbnail_preview');
        const kosong = document.getElementById('thumbnail_kosong');
        const berkas = document.getElementById('thumbnail_berkas');
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        kosong.classList.add('hidden');
        berkas.src = '{{ asset('storage/default-thumbnail.png') }}';
        berkas.classList.remove('hidden');

        // Deskripsi file DIPERTAHANKAN: nama berkas tetap tertulis walau
        // berkasnya tidak ikut terkirim ke server.
        isiNamaFileOtomatis(namaFileDariGambar(namaAsli));
        document.getElementById('input_thumbnail').value = '';

        Swal.fire({
            icon: 'warning',
            title: 'Berkas terlalu besar',
            text: 'Ukuran berkas ' + Math.round(ukuranByte / (1024 * 1024)) + ' MB melampaui batas ' + maksMb + ' MB. Deskripsi tetap disimpan dan acuan jadi thumbnail default - beri file sumbernya ke produksi lewat jalur lain.',
            confirmButtonText: 'OK'
        });
    }

    // Upload thumbnail segera saat file dipilih (bukan dikirim bareng payload
    // /penjualan/store yang formatnya JSON) - path hasil upload disimpan di
    // currentThumbnail lalu ikut ke item keranjang sebagai string biasa.
    async function uploadThumbnail(input) {
        const file = input.files[0];
        if (!file) return;

        const originalName = file.name;
        // Batas ukuran meniru App\Support\BerkasDesain: dokumen desain
        // (pdf/cdr/ai/eps/psd) 39 MB, format gambar lainnya 10 MB.
        const ekstensiAwal = (originalName.split('.').pop() || '').toLowerCase();
        const maksMb = ['pdf', 'cdr', 'ai', 'eps', 'psd'].indexOf(ekstensiAwal) !== -1 ? 39 : 10;
        // Berkas baru dipilih - keputusan "terima tanpa berkas" dari percobaan
        // sebelumnya dibatalkan.
        thumbnailGagalUkuran = false;
        input.disabled = true;

        // Nama file ditampilkan pada span terpisah (input file-nya disembunyikan
        // di belakang tombol "Pilih"), jadi kasir tetap tahu file mana yang
        // sedang dipilih tanpa teks nama file panjang menekan layout.
        const labelNama = document.getElementById('thumbnail_nama');
        if (labelNama) labelNama.textContent = originalName;

        try {
            // Gambar dikompres di browser sampai 200 KB supaya tetap jernih
            // saat dicetak di SPK; PDF dirender jadi gambar (halaman
            // pertama) oleh pdf.js, jadi berkas yang sampai di server berupa
            // GAMBAR. Format lain (CDR, AI, EPS) tidak bisa dirender
            // peramban, jadi dikirim apa adanya.
            const prepared = await siapkanBerkasUntukUnggah(file, originalName);
            const fileUpload = prepared.file;
            const filePreview = prepared.preview || null;

            // Ukuran dicek dari berkas siap unggah, bukan berkas asli: gambar
            // kompresibel bisa menyusut dari puluhan MB ke ~200 KB. Kalau
            // tetap melebihi batas, jangan tolak order - pakai jalur
            // terima-tanpa-berkas.
            if (fileUpload.size > maksMb * 1024 * 1024) {
                terimaBerkasTerlaluBesar(originalName, fileUpload.size, maksMb);
                return;
            }

            const formData = new FormData();
            formData.append('thumbnail', fileUpload);
            if (filePreview) formData.append('thumbnail_preview', filePreview);
            formData.append('nama_asli', originalName);

            const response = await fetch('{{ route("penjualan.upload-thumbnail") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });
            const res = await response.json();
            if (res.status === 'success') {
                currentThumbnail = res.path;
                currentThumbnailPreview = res.path_preview || null;
                thumbnailGagalUkuran = false;
                const preview = document.getElementById('thumbnail_preview');
                const kosong = document.getElementById('thumbnail_kosong');
                const berkas = document.getElementById('thumbnail_berkas');

                // Berkas yang bisa ditampilkan peramban (jpg/png/webp/...)
                // ditampilkan sebagai gambar; yang lain (PDF, CDR, ...) hanya
                // jadi keterangan - kalau dipaksa jadi <img>, peramban
                // menampilkan gambar rusak.
                if (res.bisa_ditampilkan) {
                    preview.src = res.url;
                    preview.classList.remove('hidden');
                    kosong.classList.add('hidden');
                    berkas.classList.add('hidden');
                } else {
                    preview.removeAttribute('src');
                    preview.classList.add('hidden');
                    kosong.classList.add('hidden');
                    berkas.src = '{{ asset('storage/default-thumbnail.png') }}';
                    berkas.classList.remove('hidden');
                }

                // Nama file / deskripsi SELALU mengikuti berkas yang BARU
                // diunggah. Dulu cuma diisi saat kolom masih kosong, jadi
                // begitu kasir mengganti berkas, deskripsi tetap memegang nama
                // berkas pertama dan tidak mewakili desain yang sekarang.
                isiNamaFileOtomatis(namaFileDariGambar(originalName));

                Toast.fire({
                    icon: 'success',
                    title: res.bisa_ditampilkan
                        ? `Gambar terunggah (${(fileUpload.size / 1024).toFixed(0)} KB)`
                        : `Berkas desain terunggah (${(fileUpload.size / 1024).toFixed(0)} KB)`
                });
            } else {
                // Pesan galat validasi Laravel ada di errors.thumbnail[0]
                // (mis. "Ukuran file terlalu besar (maksimal 10 MB).") -
                // res.message cuma kalimat generik bahasa Inggris.
                const pesanGalat = (res.errors && res.errors.thumbnail && res.errors.thumbnail[0]) || res.message || res.msg || 'Upload thumbnail gagal.';
                if (pesanGalat.indexOf('terlalu besar') !== -1) {
                    // Server menolak karena ukuran (standar dua lapis dengan
                    // cek peramban) - pakai jalur terima-tanpa-berkas juga.
                    terimaBerkasTerlaluBesar(originalName, fileUpload.size, maksMb);
                } else {
                    Swal.fire('Gagal', pesanGalat, 'error');
                }
                input.value = '';
            }
        } catch (e) {
            // Respons bukan JSON (response.json() gagal) - hampir selalu karena
            // body POST melewati post_max_size PHP (40 MB): PHP membuang
            // seluruh unggahan dan menjawab halaman galat, bukan JSON. Pesan
            // "Periksa jaringan" yang lama justru menyesatkan pengguna.
            if (fileUpload.size > 30 * 1024 * 1024) {
                terimaBerkasTerlaluBesar(originalName, fileUpload.size, maksMb);
            } else {
                Swal.fire('Gagal mengunggah', 'Gambar belum terunggah. Periksa jaringan lalu coba lagi.', 'error');
            }
            input.value = '';
        } finally {
            input.disabled = false;
        }
    }

    // Fungsi global untuk menyimpan metadata konsumen ke element
    // Didefinisikan di luar $(document).ready() agar bisa diakses oleh saveKonsumen()
    function simpanMetaKonsumen(el, meta) { $(el).data('meta', meta || null); }

    $(document).ready(function() {
        $('.select2-konsumen').select2({
            placeholder: "-- Cari Nama / No. HP --",
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
                    // `_: Date.now()` = cache-buster: tiap kueri berbeda URL,
                    // supaya tidak ada layer caching (browser/proxy/Apache)
                    // yang punya peluang menyajikan hasil pencarian lama -
                    // mis. konsumen yang baru ditambahkan lewat tab lain.
                    // (`cache: true` lama hanya diwariskan ke $.ajax dan
                    // sebenarnya tidak berpengaruh untuk dataType json.)
                    return { q: params.term || '', _: Date.now() };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                }
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

        // Fokus awal: pelanggan (select2) Ã¢â‚¬â€ alur input pesanan berurutan
        // pelanggan -> kategori -> produk -> urgensi -> catatan -> upload file
        // -> ukuran & qty. tabindex sudah diset di markup; di sini cukup
        // fokuskan select2 pelanggan saat form pertama dibuka (kalau tidak
        // terkunci oleh nota).
        @if(!$notaKunci)
        setTimeout(function () {
            var $sel = $('.select2-konsumen');
            if ($sel.length) { $sel.next('.select2-container').find('.select2-selection').focus(); }
        }, 150);
        @endif

        $('.select2-konsumen').on('select2:select', function (e) {
            // AJAX mode: metadata lewat e.params.data (dibawa prosesResults),
            // bukan atribut data-* di option.
            simpanMetaKonsumen(this, e.params.data || null);
            let idGol = (e.params.data && e.params.data.golongan) || $(this).find(':selected').data('golongan');
            if (idGol) {
                $('#id_golongan').val(idGol);
                $('#id_golongan_hidden').val(idGol);
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
                $('#id_golongan_hidden').val(idGol);
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

            $('#display_telp').val(data.telp || data.telepon || '-');
            $('#id_golongan').val(data.golongan).trigger('change');
            $('#id_golongan_hidden').val(data.golongan);

            // Harga keranjang TIDAK dikosongkan di sini. Golongan yang baru
            // sudah di-set di baris sebelumnya dan .trigger('change')-nya
            // sudah memanggil handleIntegritasData() -> hitung ulang tarif.

            resetInputProduk();
        });

        // --- RESET FORM ON LOAD (mencegah browser autofill) ---
        // Browser cenderang mengisi ulang field seperti telepon, golongan, kategori
        // saat refresh halaman. Reset manual di sini supaya form bersih.
        //
        // Mode susulan & mode edit: field pelanggan/golongan/tipe order/tanggal
        // TERKUNCI ke nota asal - jadi TIDAK boleh di-reset di sini
        // (mengosongkan #id_golongan akan membuat harga item salah hitung).
        @if($notaKunci)
        $('#display_telp').val(@json($notaKunci->telepon ?? '-'));
        $('#filter_kategori').val('all').trigger('change');
        resetInputProduk();
        // Reset jenis pengerjaan ke "Dengan Desain"
        document.querySelector('input[name="jenis_pengerjaan"][value="desain"]').checked = true;
        document.getElementById('wrapper_siap_cetak').classList.add('hidden');
        @if($notaEdit)
        // Mode edit: keranjang sudah terisi item nota - gambar ulang supaya
        // itemnya langsung terlihat dan bisa diubah.
        renderCart();
        @endif
        @else
        $('#display_telp').val('-');
        $('#id_golongan').val('').trigger('change');
        $('#id_golongan_hidden').val('');
        $('#filter_kategori').val('all').trigger('change');
        resetInputProduk();
        // Reset tipe order ke Reguler
        $('input[name="tipe_order"][value="Reguler"]').prop('checked', true);
        document.getElementById('wrapper_no_po').classList.add('hidden');
        // Reset jenis pengerjaan ke "Dengan Desain"
        document.querySelector('input[name="jenis_pengerjaan"][value="desain"]').checked = true;
        document.getElementById('wrapper_siap_cetak').classList.add('hidden');
        @endif

    });

    /**
     * Dipanggil setiap kali data yang memengaruhi harga berubah: pelanggan
     * ganti (baris handler select2-konsumen) maupun golongan harga yang
     * diganti langsung.
     *
     * Dulu isinya `cart = []` - keranjang dibuang seluruhnya. Salah pilih
     * pelanggan berarti kasir kehilangan semua pekerjaan yang sudah ia isi.
     * Sekarang yang dihitung ulang hanya tarifnya; barang, ukuran, desain,
     * dan finishing di keranjang tetap utuh.
     */
    function handleIntegritasData() {
        jalankanHitungUlangHarga();
    }

    function resetSemuaField() {
        currentProduk = null;
        editIndex = null;

        $('#select_produk').val('').trigger('change');
        document.getElementById('nama_file').value = "";
        delete document.getElementById('nama_file').dataset.auto;
        // Minimum order: catatan ukuran dikembalikan ke standar.
        document.getElementById('note_ukuran_teks').textContent = 'Ukuran untuk menghitung harga. Gunakan ukuran file asli.';
        document.getElementById('input_p').value = "";
        document.getElementById('input_l').value = "";
        // Panel Potong Manual ikut dikosongkan: ukuran media per lembar yang
        // diketik operator untuk produk sebelumnya tidak boleh terbawa ke
        // produk berikutnya.
        document.getElementById('input_lebar_media_potong').value = "0";
        document.getElementById('input_tinggi_media_potong').value = "0";
        document.getElementById('input_lebar_potong').value = "0";
        document.getElementById('input_tinggi_potong').value = "0";
        // Panel jahit keping (KEPING_ROLL): inputan keping juga dikosongkan -
        // ukuran keping produk sebelumnya tidak boleh terbawa ke berikutnya.
        document.getElementById('input_keping_lebar').value = "";
        document.getElementById('input_keping_panjang').value = "";
        document.getElementById('input_keping_jumlah').value = "";
        document.getElementById('input_keping_kapasitas').value = "";
        document.getElementById('input_keping_total').value = "";
        document.getElementById('input_qty_ukuran').value = "1";
        document.getElementById('input_qty_biasa').value = "1";
        document.getElementById('input_desain').value = "0";

        document.getElementById('display_harga').innerText = "Rp 0 /unit";
        document.getElementById('display_subtotal').innerText = "Rp 0";
        document.getElementById('display_qty').hidden = true;

        document.getElementById('wrapper_ukuran').classList.add('hidden');
        document.getElementById('wrapper_qty').classList.add('hidden');
        // Panel jahit keping ikut ditutup (reset total form).
        document.getElementById('panel_keping_roll').classList.add('hidden');

        const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
        btn.innerText = "Tambah";
        btn.className = "inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-3.5 rounded-xl font-medium text-xs uppercase tracking-wider shadow-sm transition-all active:scale-95";
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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({
                    nama_konsumen: nama,
                    telepon: hp,
                    id_golongan: idGol,
                    tipe_konsumen: tipeKonsumen
                })
            });

            const responseText = await response.text();
            let res;
            try {
                res = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError, 'Response:', responseText);
                throw new Error('Server response bukan JSON valid: ' + responseText.substring(0, 200));
            }

            if (res.status === 'success' || res.status === 'duplicate') {
                // 'duplicate' berarti konsumen dengan nama yang sama sudah
                // terdaftar. Yang dikembalikan adalah data yang SUDAH ada,
                // jadi kasir tidak perlu mendaftar ulang - cukup pilih.
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
                $('#id_golongan_hidden').val(res.data.id_golongan);

                closeModal('konsumen-baru');

                // Jalur ini hanya memicu 'change', TIDAK 'select2:select' -
                // jadi handler utama tidak ikut jalan dan harga keranjang
                // tidak pernah dihitung ulang. Akibatnya keranjang tetap memakai
                // tarif golongan LAMA padahal klien sudah jadi golongan baru,
                // lalu nota disimpan dengan angka itu. Panggil eksplisit di sini
                // supaya kedua jalur (dropdown vs modal) sama hasilnya.
                jalankanHitungUlangHarga();

                if (res.status === 'duplicate') {
                    Swal.fire('Sudah terdaftar', res.message, 'info');
                } else {
                    Swal.fire('Berhasil', 'Pelanggan Baru Terpilih', 'success');
                }
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
            console.error('saveKonsumen error:', e);
            Swal.fire('Gagal menyimpan', 'Data belum tersimpan. Periksa jaringan lalu coba lagi.', 'error');
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

        if (!terkunci) {
            // Kategori sudah dipilih: dropdown produk langsung dibuka dan
            // KOTAK PENCARIANNYA yang menerima fokus - operator bisa langsung
            // ketik tanpa klik tambahan. Patokan fokus ini sekaligus mencegah
            // fokus melompat ke field berikutnya sebelum pencarian diisi.
            $('#select_produk').select2('open');
            fokusKotakPencarianProduk(6);
        }
    }

    // Fokus ke kotak pencarian select2 produk - retry singkat karena input
    // pencarian baru ada setelah dropdown selesai dirender. Jangan merebut
    // fokus kalau operator sudah pindah ke field lain.
    function fokusKotakPencarianProduk(sisa) {
        const ae = document.activeElement;
        const diSelect2 = ae && ae !== document.body && ((ae.closest && ae.closest('.select2-container')) || (ae.closest && ae.closest('.select2-dropdown')));
        if (ae && ae !== document.body && !diSelect2) return;
        const $f = $('.select2-search__field:visible').filter(function () {
            return this.offsetParent !== null && !this.disabled;
        });
        try {
            if ($f.length) { $f[0].focus(); return; }
        } catch (e) {}
        if (sisa > 0) setTimeout(function () { fokusKotakPencarianProduk(sisa - 1); }, 40);
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
            // Dulu: `!idKategoriProduk || ...` - parameter null (dari
            // resetInputProduk setelah item masuk keranjang) dianggap
            // "relevan semuanya" sehingga SEMUA jenis finishing terbuka
            // tanpa filter kategori. Sekarang: tanpa produk terpilih hanya
            // finishing umum (tanpa kategori) yang tampil; filter penuh
            // baru diterapkan begitu produk dipilih (getDetailProduk).
            const relevan = !idKategoriFinishing || (idKategoriProduk && idKategoriFinishing == idKategoriProduk);
            opt.hidden = !relevan;
            if (!relevan && opt.selected) terpilihJadiTidakRelevan = true;
        });
        if (terpilihJadiTidakRelevan) {
            sel.value = '';
            onFinishingChange();
        }
    }

    // ===== Minimum Order (Rekonsiliasi owner 2026-11-14) =====
    // Produk dengan Minimum Order: input PANJANG dulu, LEBAR kedua -
    // berlaku untuk semua produk. KEDUANYA adalah LANTAI (input wajib
    // >= min_panjang dan >= min_lebar); kekurangan dinaikkan otomatis
    // + toast, dan server tetap otoritatif (checkout juga menaikkan +
    // menulis event audit 'koreksi_minimum'). Produk yang sama muncul
    // >= 2 baris di keranjang ini: HITUNGAN MINIMUM TIDAK BERLAKU
    // untuk baris-baris produk itu. "Lebar x panjang" hanya istilah
    // bahan baku (roll: lebar gulungan x panjang meter).
    function minBarisProdukSama() {
        // Baris OTHER cart dengan produk & kategori yang sama (baris yang
        // sedang diedit tidak dihitung) - eksempsi sisi layar, serupa
        // dengan hitungan sama di server. Mode susulan: baris nota asal
        // ikut dihitung karena satu nota = satu kesatuan.
        let n = 0;
        const idPr = currentProduk ? currentProduk.id_produk : 0;
        const kat = String(currentKategori ?? '0');
        cart.forEach((it, idx) => {
            if (idx === editIndex) return;
            if (String(it.id_produk) !== String(idPr)) return;
            if (String(it.kategori_produk ?? '0') !== kat) return;
            n++;
        });
        (ITEM_NOTA_ASAL || []).forEach((it) => {
            if (String(it.id_produk) !== String(idPr)) return;
            if (String(it.kategori_produk ?? '0') !== kat) return;
            n++;
        });
        return n;
    }
    function produkMinimumAktif(prod) {
        if (!prod) return false;
        if (prod.metode_hitung === 'ukuran') {
            return (parseFloat(prod.min_panjang) > 0 || parseFloat(prod.min_lebar) > 0);
        }
        return (parseFloat(prod.min_order) > 0);
    }
    // Perbarui catatan ukuran (minimum order) di bawah field ukuran.
    function terapkanMinimumOrder() {
        const teks = document.getElementById('note_ukuran_teks');
        if (!teks) return;
        if (!produkMinimumAktif(currentProduk)) {
            teks.textContent = 'Ukuran untuk menghitung harga. Gunakan ukuran file asli.';
            return;
        }
        const p = parseFloat(currentProduk.min_panjang) || 0;
        const l = parseFloat(currentProduk.min_lebar) || 0;
        const q = parseFloat(currentProduk.min_order) || 0;
        const ganda = minBarisProdukSama() >= 1
            ? ' Produk sama muncul di keranjang >= 2 baris: minimum tidak berlaku.'
            : '';
        teks.textContent = currentProduk.metode_hitung === 'ukuran'
            ? 'Ukuran untuk menghitung harga. Gunakan ukuran file asli. Minimum order: ' + p + ' x ' + l + ' cm (panjang x lebar); input di bawah minimum diblokir saat simpan.' + ganda
            : 'Minimum order: ' + q + ' pcs; qty di bawah minimum diblokir saat simpan.' + ganda;
    }

    // ===== Minimum Order kelompok: rincian + peringatan (spec owner 2026-11-15) =====
    // Akumulasi luas per kelompok minimum di keranjang (ukuran + min_luas_total
    // > 0). Kunci sisi layar = label kelompok; server menambah dimensi tarif &
    // konfigurasi minimum pada kuncinya (jangan menjumlahkan item dengan basis
    // harga / minimum berbeda) - preview di sini cukup label, angka final
    // selalu dihitung ulang server.
    function grupMinKeranjang() {
        const grup = {};
        const tambahBaris = (it) => {
            if (it.metode_hitung !== 'ukuran') return;
            const min = parseFloat(it.min_luas_total) || 0;
            if (!(min > 0)) return;
            const label = it.kelompok_minimum || String(it.id_produk);
            if (!grup[label]) grup[label] = { label: label, min: min, total: 0, terbesar: null, areaTerbesar: 0 };
            const area = (parseFloat(it.p) || 0) * (parseFloat(it.l) || 0) / 10000 * (parseFloat(it.qty_input) || 1);
            grup[label].total += area;
            if (area > grup[label].areaTerbesar) { grup[label].areaTerbesar = area; grup[label].terbesar = it; }
        };
        cart.forEach(tambahBaris);
        // Mode susulan: luasan nota asal ikut diakumulasi ke kelompok -
        // satu nota = satu kesatuan (selaras $barisNotaAda di server).
        (ITEM_NOTA_ASAL || []).forEach((it) => {
            tambahBaris({
                ...it,
                qty_input: parseFloat(it.qty) || 1,
            });
        });
        return Object.values(grup).map((g) => {
            g.total = Math.round(g.total * 10000) / 10000;
            g.selisih = Math.max(0, Math.round((g.min - g.total) * 10000) / 10000);
            return g;
        });
    }

    // Peringatan SweetAlert INFORMATIF (pemilian non-blocking): total luas
    // kelompok masih di bawah minimum order. Dipanggil setelah item ditambah/
    // diubah/dihapus; tidak diulang selama angkanya tidak berubah (dedup via
    // signature). Peringatan ini TIDAK menyimpan apa-apa; penolakan terjadi
    // saat KLIK SIMPAN (hard block) -lihat hard block di simpanTransaksi().
    // Untuk kelompok dengan >=2 item, simpan TETAP boleh dan selisih ditagih.
    let __sigPeringatanMinOrder = '';
    function peringatanKelompokMinOrder() {
        const kurang = grupMinKeranjang().filter((g) => g.selisih > 0);
        if (!kurang.length) { __sigPeringatanMinOrder = ''; return; }
        const g = kurang[0];
        const jumlahItem = cart.filter((it) => {
            if (it.metode_hitung !== 'ukuran') return false;
            if (!(parseFloat(it.min_luas_total) > 0)) return false;
            return (it.kelompok_minimum || String(it.id_produk)) === g.label;
        }).length + (ITEM_NOTA_ASAL || []).filter((it) => {
            if (it.metode_hitung !== 'ukuran') return false;
            if (!(parseFloat(it.min_luas_total) > 0)) return false;
            return (it.kelompok_minimum || String(it.id_produk)) === g.label;
        }).length;
        const sig = g.label + '|' + g.total + '|' + g.min;
        if (sig === __sigPeringatanMinOrder) return;
        __sigPeringatanMinOrder = sig;
        Swal.fire({
            icon: 'warning',
            title: 'Minimum Order Belum Terpenuhi',
            html: `Total luas pesanan saat ini adalah <b>${angkaId(g.total, 2)} m&sup2;</b>, sedangkan minimum order adalah <b>${angkaId(g.min, 2)} m&sup2;</b>. Kekurangan <b>${angkaId(g.selisih, 2)} m&sup2;</b>.${jumlahItem <= 1 ? ' <b>Simpan diblokir</b> sampai minimum terpenuhi (1 item saja).' : ' Selisih akan ditagih ke baris terbesar kelompok saat simpan (pengali).'} `,
            confirmButtonText: 'Mengerti'
        });
    }

    // Token urutan ganti produk: user bisa menekan-ganti produk beruntun
    // cepat; tanpa penjaga, respons fetch produk yang TELAT datang tetap
    // menimpa currentProduk dengan data produk yang sudah tidak terpilih
    // (produksi tampilan & saran finishing jadi basi - pola sama seperti
    // urutanHitungFinishing di bawah).
    let urutanProdukTerpilih = 0;
    async function getDetailProduk() {
        const id = $('#select_produk').val();
        if (!id) { resetInputProduk(); return; }
        const selectedOption = $('#select_produk').find(':selected');
        const metode = selectedOption.data('metode');
        const tokenProduk = ++urutanProdukTerpilih;
        filterFinishingByKategori(selectedOption.data('kategori'));
        setSiapCetak(false, true);
        document.getElementById('wrapper_siap_cetak').classList.toggle('hidden', String(selectedOption.data('siap-cetak')) !== '1');
        $('#wrapper_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#note_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#wrapper_qty').toggleClass('hidden', metode !== 'quantity');

        try {
            const res = await fetch(`{{ url('/api/penjualan/produk') }}/${id}`);
            // Sesi kedaluwarsa gagal dalam dua bentuk: res.redirected (302
            // ke /masuk) atau !res.ok (403/500). Keduanya dulu berakhir di
            // res.json() yang melempar SyntaxError - tertampung catch,
            // tercatat di console saja, dan layar diam total: produk
            // terlihat terpilih tapi currentProduk tetap kosong, estimasi
            // tidak terhitung, dan tombol Tambah bersuara "Pilih produk
            // dulu" seolah-olah produk memang belum dipilih. Tampilkan
            // penyebabnya di layar, dengan tindakan lanjutan yang jelas.
            if (!res || res.redirected || !res.ok) {
                currentProduk = null;
                Swal.fire(
                    res && res.redirected ? 'Sesi Berakhir' : 'Gagal Memuat Produk',
                    (res && res.redirected
                        ? 'Sesi Anda sudah berakhir sehingga detail produk tidak bisa diambil. Halaman akan dimuat ulang, silakan masuk kembali.'
                        : 'Detail produk tidak bisa diambil dari server (HTTP ' + (res ? res.status : 'gagal') + '). Periksa koneksi lalu muat ulang halaman (Ctrl+F5).'),
                    'error'
                ).then(function () { if (res && res.redirected) window.location.reload(); });
                return;
            }
            const hasilProduk = await res.json();
            // Respons basi: produk sudah terganti lagi sebelum respons tiba -
            // buang, urutan terbaru yang dipakai.
            if (tokenProduk !== urutanProdukTerpilih) return;
            currentProduk = hasilProduk;
            currentProduk.metode_hitung = metode;
            // Kategori dari opsi yang sedang terpilih di dropdown (sama seperti
            // data-kategori yang tampil ke kasir).
            currentKategori = String(selectedOption.data('kategori') ?? '0');
            // Ganti produk = Biaya Cetak manual (kalau ada) tidak relevan lagi
            // untuk produk yang baru dipilih - kembali ke auto-default dari
            // Daftar Harga produk ini.
            cetakManualEdit = false;
            finishingBiayaManualEdit = false;
            // Panel jahit keping perlu data produk BARU (lebar roll ikut
            // produk) - segarkan setelah currentProduk terpasang.
            segarkanPanelKepingRoll();
            terapkanMinimumOrder();
            setTimeout(() => {
                if (metode === 'ukuran') { $('#input_p').focus().select(); }
                else { $('#input_qty_biasa').focus().select(); }
            }, 100);
            hitungSubtotal();
        } catch (e) {
            console.error("Gagal ambil produk:", e);
            // Jangan diam: produk terlihat terpilih tapi detailnya gagal
            // dimuat - estimasi tidak terhitung dan tombol Tambah nantinya
            // menyalahkan operator padahal operator sudah memilih.
            currentProduk = null;
            Swal.fire('Gagal Memuat Produk',
                'Detail produk tidak bisa diambil dari server. Periksa koneksi Anda, lalu muat ulang halaman (Ctrl+F5) dan coba lagi. Jika sesi sudah berakhir, Anda akan diarahkan ke halaman masuk.',
                'error');
        }
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
        const metodeFinishing = (sel.selectedOptions && sel.selectedOptions[0] && sel.selectedOptions[0].dataset) ? sel.selectedOptions[0].dataset.metode : undefined;
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
                document.getElementById('input_qty_finishing').value = (currentProduk && currentProduk.metode_hitung === 'ukuran') ? qU : qB;
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
        
        // Toggle panel Potong Manual LINEAR_GRID.
        // Catatan: metodeFinishing SUDAH dideklarasikan di atas dalam scope
        // fungsi yang sama. Deklarasi ulang dengan const di sini menghancurkan
        // seluruh blok <script> ini dengan SyntaxError "Identifier
        // 'metodeFinishing' has already been declared" - jQuery, Select2, dan
        // filterProduk TIDAK PERNAH terdefinisi karena itu.
        const panelLinear = document.getElementById('panel_linear_grider');
        if (metodeFinishing === 'LINEAR_GRID') {
            panelLinear.classList.remove('hidden');
            terapkanDefaultPotongManual();
            hitungBiayaLinearGrid();
        } else {
            panelLinear.classList.add('hidden');
            document.getElementById('input_finishing_biaya').value = "0";
        }
        segarkanLabelMinimumPotongManual();

        // Toggle panel INPUTAN JAHIT KEPING (KEPING_ROLL) Ã¢â‚¬â€ keping adalah
        // inputan operator (lebar x panjang x jumlah keping) yang dijaga
        // kapasitas dari ukuran cetak (identik server), supaya kesalahan
        // keping terlihat SEBELUM nota disimpan.
        const panelKeping = document.getElementById('panel_keping_roll');
        if (panelKeping) {
            const kepingAktif = metodeFinishing === 'KEPING_ROLL';
            panelKeping.classList.toggle('hidden', !kepingAktif);
            if (kepingAktif) segarkanPanelKepingRoll();
        }
        
        terapkanModeBiayaFinishing();
        // Memilih/mengganti finishing dapat mengubah biaya finishing yang sudah
        // ada di daftar (mis. tarif master Potong Manual berubah saat produk
        // lain dipilih). Disegarkan di sini supaya Total Finishing tidak
        // tertinggal dari isi field.
        hitungUlangPotongManualDiDaftar();
        await hitungSubtotal();
    }

    // Tampilkan minimum charge aktif di panel Potong Manual, diambil dari
    // master finishing yang sedang dipilih. Anglebnya TIDAK ditulis manual
    // di sini - kalau iya, layar bisa lagi menampilkan angka yang berbeda
    // dari yang benar-benar dipakai server.
    function segarkanLabelMinimumPotongManual() {
        const el = document.getElementById('potong_manual_minimum');
        if (!el) return;
        const sel = document.getElementById('select_finishing');
        const opsi = (sel && sel.selectedOptions) ? sel.selectedOptions[0] : undefined;
        const minimum = parseFloat((opsi && opsi.dataset) ? opsi.dataset.minimum : undefined);
        if (!opsi || !opsi.value || !Number.isFinite(minimum)) {
            el.textContent = 'minimum charge dari master finishing';
            return;
        }
        el.textContent = minimum > 0
            ? '(minimum charge Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(minimum)) + ' per item finishing)'
            : '(tanpa minimum charge - biaya persis hasil rumus)';
    }

    // Isi dimensi potong default = SETENGAH ukuran media (satuan cm).
    //
    // Idempotent: hanya mengisi field yang masih 0/kosong, jadi nilai yang sudah
    // diketik operator tidak tertimpa. Dipanggil ulang setiap kali ukuran media
    // berubah supaya field yang ter-nol-kan resetInputProduk() ikut terisi lagi.
    function terapkanDefaultPotongManual() {
        const elLebar = document.getElementById('input_lebar_potong');
        const elTinggi = document.getElementById('input_tinggi_potong');
        if (!elLebar || !elTinggi) return;

        const elLebarMedia = document.getElementById('input_lebar_media_potong');
        const elTinggiMedia = document.getElementById('input_tinggi_media_potong');

        // Produk 'ukuran' (roll/banner) ukuran media-nya sudah ada di form
        // utama -> field media di panel ini mengikuti dan dikunci. Produk
        // lain (per lembar, mis. A3) tidak punya Panjang/Lebar, jadi field
        // media DICEBEHI operator.
        const pakaiUkuran = produkPakaiUkuran();
        if (elLebarMedia && elTinggiMedia) {
            elLebarMedia.disabled = pakaiUkuran;
            elTinggiMedia.disabled = pakaiUkuran;
            elLebarMedia.classList.toggle('bg-slate-100', pakaiUkuran);
            elTinggiMedia.classList.toggle('bg-slate-100', pakaiUkuran);
            elLebarMedia.classList.toggle('text-slate-400', pakaiUkuran);
            elTinggiMedia.classList.toggle('text-slate-400', pakaiUkuran);
            elLebarMedia.title = pakaiUkuran ? 'Otomatis mengikuti Panjang/Lebar produk.' : 'Lebar lembar yang dipotong (cm).';
            elTinggiMedia.title = pakaiUkuran ? 'Otomatis mengikuti Panjang/Lebar produk.' : 'Tinggi lembar yang dipotong (cm).';
        }

        const lebarMedia = pakaiUkuran ? (parseFloat(document.getElementById('input_p').value) || 0) : 0;
        const tinggiMedia = pakaiUkuran ? (parseFloat(document.getElementById('input_l').value) || 0) : 0;

        if (pakaiUkuran && elLebarMedia && elTinggiMedia) {
            elLebarMedia.value = lebarMedia;
            elTinggiMedia.value = tinggiMedia;
        }

        if (lebarMedia > 0 && (parseFloat(elLebar.value) || 0) <= 0) {
            elLebar.value = Math.round((lebarMedia / 2) * 100) / 100;
        }
        if (tinggiMedia > 0 && (parseFloat(elTinggi.value) || 0) <= 0) {
            elTinggi.value = Math.round((tinggiMedia / 2) * 100) / 100;
        }
    }

    // Apakah produk yang sedang dipilih punya Panjang/Lebar di form utama.
    function produkPakaiUkuran() {
        return typeof currentProduk !== 'undefined'
            && currentProduk
            && currentProduk.metode_hitung === 'ukuran';
    }

    // Ukuran MEDIA yang dipotong (cm), sesuai sumbernya:
    //  - produk 'ukuran'  -> Panjang/Lebar di form utama
    //  - produk per lembar -> field media di panel Potong Manual
    // Perbandingan di layar WAJIB sama dengan server
    // (PenjualanController::store), kalau tidak biaya yang tampil sebelum
    // disimpan bisa beda dengan yang tersimpan.
    function ukuranMediaPotong() {
        if (produkPakaiUkuran()) {
            return {
                lebar: parseFloat(document.getElementById('input_p').value) || 0,
                tinggi: parseFloat(document.getElementById('input_l').value) || 0,
            };
        }

        return {
            lebar: parseFloat((document.getElementById('input_lebar_media_potong') || {}).value) || 0,
            tinggi: parseFloat((document.getElementById('input_tinggi_media_potong') || {}).value) || 0,
        };
    }

    // Kalkulasi biaya finishing mode LINEAR_GRID (Potong Manual).
    //
    // SATUAN INPUT: CM (sama dengan field Panjang/Lebar media di form).
    // Meter = cm / 100, dipakai HANYA di dalam perhitungan.
    //
    // HARUS IDENTIK dengan HargaService::hitungBiayaFinishingLinearGrid(),
    // yang juga menerima cm dan mengonversinya ke meter di dalam fungsi.
    //
    // Fungsi ini MURNI (tidak baca/menulis DOM) supaya bisa dipakai ulang untuk
    // entri Potong Manual yang SUDAH ada di daftar finishing - lihat
    // hitungUlangPotongManualDiDaftar(). Semula perhitungan hanya hidup di
    // hitungBiayaLinearGrid() yang menulis ke field isian, sehingga entri di
    // daftar memakai f.biaya hasil hitungan saat pertama kali dimasukkan:
    //qtyCetak dinaikkan dari 1 ke 2, field isian ikut berubah ke angka
    // yang benar, tapi Total Finishing dan Subtotal tetap memakai angka
    // lama. Memisahkan perhitungan murni dari akses DOM adalah akar
    // perbaikannya.
    function kalkulasiGridLinearGrid(lebarMedia, tinggiMedia, lebarPotong, tinggiPotong, qtyCetak, tarifPerMeter, minimumCharge) {
        // Dimensi tidak lengkap: TIDAK boleh diam-diam jadi Rp0. Geometrically
        // tidak valid (potong lebih besar dari media) memang bernilai 0,
        // tapi dimensi kosong berarti operator belum mengisi - dua keadaan itu
        // dibedakan lewat flag `terisi` supaya lapisan atas bisa menolaknya.
        if (!(lebarPotong > 0) || !(tinggiPotong > 0) || !(lebarMedia > 0) || !(tinggiMedia > 0)) {
            return { terisi: false, biaya: 0, meter: 0 };
        }
        if (!(tarifPerMeter >= 0)) {
            return { terisi: false, biaya: 0, meter: 0 };
        }

        const kolom = Math.floor(lebarMedia / lebarPotong);
        const baris = Math.floor(tinggiMedia / tinggiPotong);

        // Potong >= media: tidak ada keping yang bisa dipotong -> 0, bukan
        // minimum charge. Ini kelegalan geometri, bukan kegagalan hitung.
        if (kolom < 1 || baris < 1) {
            return { terisi: true, biaya: 0, meter: 0 };
        }

        const garisVertikal = Math.max(0, kolom - 1);
        const garisHorizontal = Math.max(0, baris - 1);

        // Panjang garis dalam cm -> meter (/100).
        const meterPerMedia = ((garisVertikal * tinggiMedia) + (garisHorizontal * lebarMedia)) / 100;
        const qty = (qtyCetak > 0) ? qtyCetak : 1;
        const meterTotal = meterPerMedia * qty;

        return {
            terisi: true,
            biaya: Math.max(minimumCharge, meterTotal * tarifPerMeter),
            meter: meterTotal
        };
    }

    // Tarif per meter DAN minimum charge diambil dari MASTER finishing yang
    // sedang dipilih (opsi sudah membawa data-harga & data-minimum dari
    // server), bukan angka tetap di dalam kode.
    //
    // Dulu tarif di-hardcode 450 (window.LINEAR_GRID_TARIF tidak pernah
    // didefinisikan di mana pun) dan minimum charge di-hardcode 5000 - begitu
    // tarif master diubah, hitungan di layar menyimpang dari yang disimpan
    // server. Sekarang keduanya dibaca dari baris master yang sama dengan
    // yang dibaca server (HargaService), jadi layar dan server tidak
    // mungkin berbeda.
    //
    // Minimum charge 0 = tidak ada lantai biaya, biaya persis hasil rumus.
    function bacaTarifLinearGrid(opsi) {
        const tarif = parseFloat((opsi && opsi.dataset) ? opsi.dataset.harga : undefined);
        const minimum = parseFloat((opsi && opsi.dataset) ? opsi.dataset.minimum : undefined);
        return {
            tarifPerMeter: Number.isFinite(tarif) ? tarif : 0,
            minimumCharge: Number.isFinite(minimum) ? minimum : 0
        };
    }

    // Qty cetak yang dipakai mode Potong Manual: mengikuti mode hitung
    // produk yang sedang dipilih (mode 'ukuran' memakai Qty di sebelah
    // Lebar/Tinggi, mode lain memakai Qty biasa). Sumber kebenarannya
    // currentProduk, sama dengan cek di server.
    function qtyCetakBerjalan() {
        const hitungUkuran = typeof currentProduk !== 'undefined'
            && currentProduk
            && currentProduk.metode_hitung === 'ukuran';
        return (hitungUkuran
            ? parseFloat(document.getElementById('input_qty_ukuran').value)
            : parseFloat(document.getElementById('input_qty_biasa').value)) || 1;
    }

    // Hitung biaya dari isi panel Potong Manual, tulis ke field isian, dan
    // kembalikan angkanya supaya pemanggil bisa memakai nilai yang sama.
    function hitungBiayaLinearGrid() {
        const lebarPotong = parseFloat(document.getElementById('input_lebar_potong').value) || 0;
        const tinggiPotong = parseFloat(document.getElementById('input_tinggi_potong').value) || 0;

        const media = ukuranMediaPotong();
        const lebarMedia = media.lebar;
        const tinggiMedia = media.tinggi;

        const { tarifPerMeter, minimumCharge } = bacaTarifLinearGrid(
            (function(){var s=document.getElementById('select_finishing');return s?s.selectedOptions[0]:undefined;})()
        );

        const hasil = kalkulasiGridLinearGrid(
            lebarMedia, tinggiMedia, lebarPotong, tinggiPotong,
            qtyCetakBerjalan(), tarifPerMeter, minimumCharge
        );

        document.getElementById('input_finishing_biaya').value = formatRupiahDariAngka(Math.round(hasil.biaya));
        return hasil.biaya;
    }

    // Perbarui biaya SETIAP entri Potong Manual yang sudah ada di daftar
    // finishing, memakai dimensi potong milik entri itu sendiri (bukan isi
    // panel sekarang) terhadap ukuran media dan qty terkini.
    //
    // Inilah yang hilang sebelumnya: hitungBiayaLinearGrid() hanya menulis ke
    // field isian, sedangkan Total Finishing/Subtotal dibaca dari
    // finishingList[].biaya. Ketika qty dinaikkan, keduanya berbeda - field
    // menunjukkan angka baru, total masih angka lama.
    function hitungUlangPotongManualDiDaftar() {
        if (typeof finishingList === 'undefined' || !finishingList.length) return;

        const media = ukuranMediaPotong();
        const lebarMedia = media.lebar;
        const tinggiMedia = media.tinggi;
        const qtyCetak = qtyCetakBerjalan();

        let adaYangBerubah = false;

        finishingList.forEach((f) => {
            if (f.metode !== 'LINEAR_GRID') return;

            // Spek "Custom" = harga potong diisi manual oleh kasir. Angka itu
            // TIDAK boleh ditimpa hitungan rumus - sama seperti finishing lain
            // yang sudah dibayar manual.
            if (f.status === 'Custom') return;

            // Tarif entri diambil dari master saat masuk daftar; kalau entilama
            // belum menyimpannya, jatuh ke master yang sedang dipilih.
            const { tarifPerMeter, minimumCharge } = bacaTarifLinearGrid(
                document.querySelector(`#select_finishing option[value="${f.id_finishing}"]`)
            );
            f.tarif_per_meter = tarifPerMeter;

            const hasil = kalkulasiGridLinearGrid(
                lebarMedia, tinggiMedia,
                parseFloat(f.lebar_potong) || 0,
                parseFloat(f.tinggi_potong) || 0,
                qtyCetak, tarifPerMeter, minimumCharge
            );

            // Dimensi entri belum terisi -> biayanya TIDAK boleh dianggap Rp0
            // (itu akan menghapus biaya potong dari total tanpa jejak).
            // Biaya lama dipertahankan sampai dimensinya bisa dihitung.
            if (!hasil.terisi) return;

            f.biaya = hasil.biaya;
            adaYangBerubah = true;
        });

        if (adaYangBerubah) renderFinishingList();
    }

    // ---------------------------------------------------------------------
    // Biaya Potong Manual harus OTOMATIS berubah mengikuti media & qty.
    //
    // Field Panjang/Lebar/Qty produk punya onchange="hitungSubtotal()", tapi
    // hitungSubtotal() TIDAK memanggil hitungBiayaLinearGrid() - sehingga
    // mengubah ukuran media tidak pernah mengubah Biaya Finishing.
    // Listener di bawah menutup celah itu: setiap perubahan media/qty
    // menghitung ulang biaya finishing (bila mode Potong Manual aktif),
    // lalu me-refresh subtotal supaya angkanya ikut terupdate.
    // ---------------------------------------------------------------------
    function segarkanBiayaPotongManual() {
        const sel = document.getElementById('select_finishing');
        const panel = document.getElementById('panel_linear_grider');

        // Panel tersembunyi = bukan Potong Manual: jangan sentuh apa pun.
        if (!panel || panel.classList.contains('hidden')) return;
        if (!sel || !sel.value) return;

        // Bila kasir sengaja mengoreksi biaya finishing secara manual,
        // jangan ditimpa hitungan otomatis.
        if (finishingBiayaManualEdit) return;

        // Media berubah -> field potong yang ter-nol-kan (reset oleh
        // resetInputProduk) diisi ulang defaultnya, lalu biaya dihitung ulang.
        terapkanDefaultPotongManual();
        hitungBiayaLinearGrid();
        // Daftar finishing adalah sumber angka Total Finishing dan Subtotal,
        // jadi entri Potong Manual di dalamnya WAJIB ikut disegarkan di sini.
        // Hanya mengisi field isian tidak pernah mengubah subtotal.
        hitungUlangPotongManualDiDaftar();
        hitungSubtotal();
    }

    // Fungsi listener DAFTAR di sini, tapi listener PASANG sengaja dipanggil
    // dari pasangListenerPotongManual() SESUDAH `let finishingList = []`
    // dideklarasikan. Kalau langsung dipasang di baris ini, ketukan pertama
    // di Panjang/Lebar akan kena "Cannot access 'finishingList' before
    // initialization" (dead zone) karena variabelnya belum ada.
    function pasangListenerPotongManual() {
        ['input_p', 'input_l', 'input_qty_ukuran', 'input_qty_biasa'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', segarkanBiayaPotongManual);
            el.addEventListener('change', segarkanBiayaPotongManual);
        });
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
    // - 'luas'   : luas total = (P x L / 10000) x qty produk, m2 - diambil
    //              otomatis dari input Panjang/Lebar/Qty produk di atas.
    // - 'keliling': keliling total = (2 x (P+L) / 100) x qty produk, meter -
    //              untuk finishing jahit/border yang dikerjakan sekeliling
    //              tepi produk (rumus sama dengan auto-override BOM bahan
    //              "jahit"). Juga otomatis dari input di atas.
    // - 'KEPING_ROLL': qty acuan = meter jahit TOTAL = jumlah keping x
    //              keliling keping (2 x (lebar+panjang)) - jasa jahit,
    //              keputusan owner 2026-11-10: tarif per meter, TIDAK
    //              dikalikan qty cetak barang.
    //              Keping itu sendiri inputan operator (lebar x panjang x
    //              jumlah keping) dan dijaga guard server: jumlah keping
    //              wajib muat dalam ukuran cetak barang (kapasitas = MAKS
    //              orientasi A/B per barang x qty cetak) - lebar roll
    //              BUKAN patokannya.
    // - 'quantity': jumlah pcs finishing dari field "Jumlah Pcs Finishing"
    //              yang bisa disesuaikan manual (TIDAK selalu sama dengan
    //              qty cetak produknya).
    // Tier finishing_harga (& harga_default fallback) adalah HARGA PER
    // SATUAN (per m2/per meter keliling/per pcs) - dikalikan qty di atas
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
        const metodeFinishing = (sel.selectedOptions && sel.selectedOptions[0] && sel.selectedOptions[0].dataset) ? sel.selectedOptions[0].dataset.metode : undefined;
        const pcsProduk = (currentProduk.metode_hitung === 'ukuran') ? qU : qB;

        let qtyFinishing;
        if (metodeFinishing === 'luas') {
            qtyFinishing = (currentProduk.metode_hitung === 'ukuran') ? (p * l / 10000) * qU : pcsProduk;
        } else if (metodeFinishing === 'keliling') {
            qtyFinishing = (currentProduk.metode_hitung === 'ukuran') ? (2 * (p + l) / 100) * qU : pcsProduk;
        } else if (metodeFinishing === 'KEPING_ROLL') {
            // Keping = inputan operator (keputusan owner 2026-11-10): tarif
            // master per METER (jasa jahit) - meter jahit total = jumlah
            // keping x keliling keping yang diisi di panel jahit keping.
            qtyFinishing = meterJahitKepingPanel();
        } else {
            // 'quantity' - dari field Jumlah Pcs Finishing (default = qty
            // produk selama belum disentuh manual, lihat onFinishingChange()).
            qtyFinishing = parseFloat(document.getElementById('input_qty_finishing').value) || 0;
        }
        if (qtyFinishing <= 0) return;

        // Potong Manual (LINEAR_GRID): biaya TIDAK berasal dari tarif per pcs.
        // Endpoint di bawah menghitung harga x qty_pcs, yang untuk mode ini
        // tidak relevan - anyways fetch async-nya akan arrive belakangan dan
        // MENIMPA hitungan grid yang benar di field Biaya Finishing.
        // Biaya mode ini solely dihitung oleh hitungBiayaLinearGrid().
        const metodeFinishingTerpilih = (sel.selectedOptions && sel.selectedOptions[0] && sel.selectedOptions[0].dataset) ? sel.selectedOptions[0].dataset.metode : undefined;
        if (metodeFinishingTerpilih === 'LINEAR_GRID') {
            hitungBiayaLinearGrid();
            return;
        }

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
            // Keping: cermin saran total jadi ke panel jahit keping supaya
            // "penghitungan total keseluruhan" terbaca di tempat inputnya.
            const kepingTotal = document.getElementById('input_keping_total');
            if (kepingTotal && metodeFinishing === 'KEPING_ROLL') {
                kepingTotal.value = document.getElementById('input_finishing_biaya').value;
            }
        } catch (e) {}
    }


    // ===================== FINISHING GANDA (Laminasi + Kisscut, dst) =====================
    // finishingList = finishing yang SUDAH ditambahkan untuk item yang sedang diisi. Kontrol finishing di bar biru adalah
    // baris isian; "Tambah Finishing" memasukkannya ke daftar. Subtotal memakai TOTAL daftar. Finishing Standar dihitung ulang
    // otomatis bila ukuran/qty berubah; Custom memakai biaya manual.
    let finishingList = [];
    const BOLEH_UBAH_HARGA = {{ \App\Support\AksesHarga::bolehLihat() ? 'true' : 'false' }};

    // finishingList sudah ada di scope -> listener Potong Manual boleh dipasang.
    pasangListenerPotongManual();

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
        // Keping = inputan operator (keputusan owner 2026-11-10): tarif
        // master ditagih PER METER (jasa jahit) - meter jahit total =
        // jumlah keping x keliling keping. Entri daftar menyimpan meter
        // di f.qty_pakai; bar isian menghitung dari keping di panel.
        if (metode === 'KEPING_ROLL') return parseFloat(qtyPakai) || meterJahitKepingPanel();
        return parseFloat(qtyPakai) || pcs;
    }

    // Jumlah keping (KEPING_ROLL) yang diketik operator di panel jahit keping.
    function jumlahKepingPanel() {
        return parseFloat(document.getElementById('input_keping_jumlah').value) || 0;
    }

    // Meter jahit total dari keping panel = jumlah keping x KELILING KEPING
    // (2 x (lebar + panjang keping)) dalam meter - keputusan owner
    // (2026-11-10, contoh: 8 keping 50 x 300 -> 56,00 m x Rp 1.500 = Rp
    // 84.000, master 'Keping Roll' id 21). Jumlah JUGA wajib bulat; jangan
    // diam-diam menagih lebih sedikit dengan membulatkan input operator ke
    // bawah. IDENTIK dengan server (meterKepingTerjaga).
    function meterJahitKepingPanel() {
        const lek = parseFloat(document.getElementById('input_keping_lebar').value) || 0;
        const pak = parseFloat(document.getElementById('input_keping_panjang').value) || 0;
        const jml = jumlahKepingPanel();
        return (jml > 0 && Math.floor(jml) === jml && lek > 0 && pak > 0) ? jml * (2 * (lek + pak) / 100) : 0;
    }

    // Panel jahit keping (KEPING_ROLL) versi 2: keping adalah INPUTAN
    // operator (lebar x panjang x jumlah keping, keputusan owner
    // 2026-11-10). Evaluasi live di panel SINKRON dengan server
    // (PenjualanCheckoutService::meterKepingTerjaga):
    //   kapasitasPerBarang = MAKS(
    //       floor(l / lebar keping) x floor(p / panjang keping),   // orientasi A
    //       floor(l / panjang keping) x floor(p / lebar keping)    // keping diputar 90 derajat
    //   )
    //   kapasitasTotal = kapasitasPerBarang x QTY CETAK barang
    // Rasio dibulatkan 6 digit sebelum floor. Kedua orientasi dihitung
    // supaya TIDAK ADA AMBIGU antara P dan L (nilai yang tertukar tidak
    // lagi menolak keping yang fisiknya muat). File produksi 2-up (contoh
    // owner: media cetak 102 x 304, 4 pcs, file berisi 2 layout 50 x 300)
    // dihitung penuh: 2 keping per barang x 4 = 8 keping. Lebar roll bahan
    // TIDAK dipakai sebagai patokan.
    function segarkanPanelKepingRoll() {
        const panel = document.getElementById('panel_keping_roll');
        if (!panel || panel.classList.contains('hidden')) return;
        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 1;
        const lek = parseFloat(document.getElementById('input_keping_lebar').value) || 0;
        const pak = parseFloat(document.getElementById('input_keping_panjang').value) || 0;
        const jml = jumlahKepingPanel();

        const elKapasitas = document.getElementById('input_keping_kapasitas');
        const elPesan = document.getElementById('pesan_keping_roll');
        const elPanelP = document.getElementById('input_panel_p');
        const elPanelL = document.getElementById('input_panel_l');
        const elPanelQty = document.getElementById('input_panel_qty');

        // Cermini ukuran cetak di form utama supaya panel memuat dasar
        // hitungannya lengkap - operator tidak perlu menengok ke atas.
        elPanelP.value = p || '';
        elPanelL.value = l || '';
        elPanelQty.value = qU || '';
        elKapasitas.value = '';

        let pesan;
        let kelas;
        if (!currentProduk) {
            pesan = 'Pilih produk dulu - ukuran cetak diambil dari field Panjang/Lebar di form utama.';
            kelas = 'text-slate-500';
        } else if (currentProduk.metode_hitung !== 'ukuran') {
            pesan = 'Produk ini dijual per pcs, bukan per ukuran - jahit keping DITOLAK server. Pilih metode finishing lain, atau ubah master produk.';
            kelas = 'text-rose-600';
        } else if (p <= 0 || l <= 0) {
            pesan = 'Isi Panjang & Lebar cetak di form utama dulu untuk melihat kapasitas keping.';
            kelas = 'text-amber-600';
        } else if (lek <= 0 || pak <= 0 || jml <= 0) {
            // Keping adalah inputan operator: tampilkan contoh dari owner.
            pesan = 'Isi keping: Lebar Keping x Panjang Keping x Jumlah Keping (mis. cetak 120 x 500 cm -> 50 x 500 = 2 keping). Sumber angka sama dengan server.';
            kelas = 'text-amber-600';
        } else if (Math.floor(jml) !== jml) {
            pesan = 'Jumlah Keping harus bilangan bulat positif; nilai pecahan tidak dapat dipakai.';
            kelas = 'text-rose-600';
        } else {
            // Rumus IDENTIK dengan server (rasio 6 digit sebelum floor,
            // kedua orientasi, kapasitas x qty cetak).
            const floor6 = function (x) { return Math.floor(Math.round(x * 1e6) / 1e6); };
            const orientasiA = Math.max(0, floor6(l / lek)) * Math.max(0, floor6(p / pak));
            const orientasiB = Math.max(0, floor6(l / pak)) * Math.max(0, floor6(p / lek));
            const kapasitasPerBarang = Math.max(orientasiA, orientasiB);
            const qtyCetak = Math.max(0, Math.floor(qU || 0));
            const kapasitas = Math.floor(kapasitasPerBarang * qtyCetak);
            if (kapasitas < 1) {
                pesan = 'Keping ' + angkaId(lek, 1) + ' x ' + angkaId(pak, 1) + ' cm tidak muat dalam ukuran cetak ' + angkaId(l, 1) + ' x ' + angkaId(p, 1) + ' cm (orientasi manapun) - nota akan DITOLAK server.';
                kelas = 'text-rose-600';
            } else {
                elKapasitas.value = kapasitas;
                if (jml > kapasitas) {
                    pesan = angkaId(jml, 0) + ' keping ' + angkaId(lek, 1) + ' x ' + angkaId(pak, 1) + ' cm melebihi kapasitas: dalam ukuran cetak ' + angkaId(l, 1) + ' x ' + angkaId(p, 1) + ' cm qty ' + angkaId(qtyCetak, 0) + ' pcs maksimal ' + kapasitas + ' keping (' + kapasitasPerBarang + ' per barang x ' + angkaId(qtyCetak, 0) + ' pcs). Nota DITOLAK server.';
                    kelas = 'text-rose-600';
                } else {
                    pesan = angkaId(jml, 0) + ' keping ' + angkaId(lek, 1) + ' x ' + angkaId(pak, 1) + ' cm muat dalam ukuran cetak ' + angkaId(l, 1) + ' x ' + angkaId(p, 1) + ' cm qty ' + angkaId(qtyCetak, 0) + ' pcs (kapasitas ' + kapasitas + ' = ' + kapasitasPerBarang + ' per barang x ' + angkaId(qtyCetak, 0) + ' pcs). Tarif per METER jahit (jasa jahit): meter total = jumlah keping x keliling keping - saran total di samping dihitung ulang server saat nota disimpan.';
                    kelas = 'text-indigo-700/90';
                }
            }
        }
        elPesan.textContent = pesan;
        elPesan.className = 'col-span-3 text-[10px] leading-snug ' + kelas;
    }

    // Panel jahit keping ikut segar begitu ukuran/qty barang berubah - pola
    // listener yang sama dengan pasangListenerPotongManual().
    function pasangListenerKepingRoll() {
        ['input_p', 'input_l', 'input_qty_ukuran'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', segarkanPanelKepingRoll);
            el.addEventListener('change', segarkanPanelKepingRoll);
        });
        // Keping adalah INPUTAN operator: setiap ketikan menilai ulang
        // kapasitas live, dan tiap selesai isi (change) segarkan saran
        // total biaya (tarif per meter x meter jahit total) ke field
        // Biaya Finishing.
        ['input_keping_lebar', 'input_keping_panjang', 'input_keping_jumlah'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', segarkanPanelKepingRoll);
            el.addEventListener('change', function () {
                segarkanPanelKepingRoll();
                if (!finishingBiayaManualEdit) refreshFinishingBiayaSuggestion();
            });
        });
    }
    pasangListenerKepingRoll();

    // Kembalikan biaya Standar, atau `false` bila permintaan GAGAL.
    //
    // Nilai kembalian `false` itu disengaja. Sebelumnya kegagalan fetch
    // dikembalikan sebagai 0, dan 0 langsung ditulis ke f.biaya - jadi satu
    // koneksi yang putus sesaat menghapus biaya finishing dari subtotal
    // tanpa jejala kasir melihat: nota ikut under-charge. Sekarang kegagalan
    // dibiarkan terlihat sebagai "tidak terhitung" dan biaya sebelumnya
    // dipertahankan.
    async function hargaStandarFinishing(idFinishing, metode, qtyPakai) {
        const q = qtyAcuanFinishing(metode, qtyPakai);
        if (q <= 0) return false;
        try {
            const res = await fetch(`{{ url('/api/penjualan/get-harga-finishing') }}/${idFinishing}/${q}`);
            if (!res.ok) return false;
            const data = await res.json();
            const harga = parseFloat(data.harga);
            if (!Number.isFinite(harga)) return false;
            return Math.round(harga * q);
        } catch (e) { return false; }
    }

    // Token urutan: kalau operator mengubah qty beberapa kali cepat-cepat,
    // beberapa hitungUlangFinishingStandar() berjalan bersamaan. Tanpa token,
    // respons yang LAMBAT bisa tiba belakangan dan menimpa hasil yang lebih
    // baru - angka di layar jadi Mundur ke nilai qty lama.
    let urutanHitungFinishing = 0;

    async function hitungUlangFinishingStandar() {
        const token = ++urutanHitungFinishing;

        // Potong Manual (LINEAR_GRID) TIDAK lewat endpoint tarif per-pcs -
        // endpoint itu mengembalikan harga_default x qty_pcs, yang untuk mode
        // ini tidak relevan (hasilnya selalu "450" per item). Biayanya dihitung
        // dari meter potong x tarif per meter lewat kalkulasiGridLinearGrid().
        //
        // Dulu entri LINEAR_GRID justru DILEWATI TOTAL dan f.biaya dibekukan
        // begitu item masuk daftar. Akibatnya qtyCetak dinaikkan 1 -> 2, field
        // Biaya Finishing ikut berubah ke Rp9.090 tapi Total Finishing tetap
        // Rp5.000: dua angka berbeda untuk nilai yang sama di satu layar.
        const hasil = await Promise.all(finishingList.map(f =>
            (f.status === 'Standar' && f.metode !== 'LINEAR_GRID')
                ? hargaStandarFinishing(f.id_finishing, f.metode, f.qty_pakai)
                : Promise.resolve(null)
        ));

        // Respons lama: jangan sentuh apa pun, hitungan yang lebih baru yang
        // sedang berjalan yang berhak menulis hasil akhir.
        if (token !== urutanHitungFinishing) return;

        finishingList.forEach((f, i) => {
            // null = Potong Manual (dihandle terpisah) atau permintaan gagal.
            if (hasil[i] === null) return;
            // Fetch gagal: JANGAN turunkan biaya jadi Rp0 (lihat
            // hargaStandarFinishing). Biaya lama dipertahankan supaya nota
            // tidak ikut merosot gara-gara koneksi sesaat.
            if (hasil[i] === false) return;
            f.biaya = hasil[i];
        });

        hitungUlangPotongManualDiDaftar();
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
                <span class="text-xs text-slate-500 w-24 text-center">${f.metode === 'quantity' ? angkaId(parseFloat(f.qty_pakai) || 0, 2) + ' pakai' : (f.metode === 'luas' ? 'per m2' : (f.metode === 'LINEAR_GRID' ? 'total potong' : (f.metode === 'KEPING_ROLL' ? (angkaId(parseFloat(f.qty_pakai) || 0, 2) + ' m jahit') : 'per meter')))}</span>
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

        const metode = (sel.selectedOptions && sel.selectedOptions[0] && sel.selectedOptions[0].dataset) ? sel.selectedOptions[0].dataset.metode : undefined;
        const status = document.getElementById('input_status_finishing').value === 'Custom' ? 'Custom' : 'Standar';
        const qtyPakai = metode === 'quantity' ? (parseFloat(document.getElementById('input_qty_finishing').value) || 0)
            : (metode === 'KEPING_ROLL' ? meterJahitKepingPanel() : null);
        // Keping wajib lengkap SEBELUM lanjut - server menolak nota untuk
        // keping kosong, tapi cek di sini supaya pesannya langsung terbaca
        // operator (jalur silent saat "Tambah" item membenamkan pesan server).
        if (metode === 'KEPING_ROLL') {
            const lek = parseFloat(document.getElementById('input_keping_lebar').value) || 0;
            const pak = parseFloat(document.getElementById('input_keping_panjang').value) || 0;
            const jml = jumlahKepingPanel();
            if (jml > 0 && Math.floor(jml) !== jml) {
                if (!silent) Swal.fire('Jahit Keping', 'Jumlah Keping harus bilangan bulat positif; nilai pecahan tidak dapat dipakai.', 'warning');
                return false;
            }
            if (lek <= 0 || pak <= 0 || qtyPakai <= 0) {
                if (!silent) Swal.fire('Jahit Keping', 'Isi Lebar Keping, Panjang Keping, dan Jumlah Keping di panel ukuran keping dulu.', 'warning');
                return false;
            }
        }
        let biaya;
        if (status === 'Custom' && BOLEH_UBAH_HARGA) {
            biaya = cleanRupiah(document.getElementById('input_finishing_biaya').value);
        } else if (metode === 'LINEAR_GRID') {
            // Potong Manual: biaya TIDAK bisa diambil dari endpoint tarif
            // per-pcs. Endpoint itu mengembalikan harga_default (tarif per
            // meter) x qty - untuk mode ini hasilnya selalu "450" per item,
            // padahal biaya sebenarnya dihitung dari meter potong (lihat
            // hitungBiayaLinearGrid()) dan SUDAH tampil benar di field
            // Biaya Finishing. Mengambil angka dari field itu membuat
            // "Total Finishing" sama dengan yang kasir lihat.
            // Server menghitung ulang dari dimensi potong, jadi angka ini
            // hanya untuk tampilan daftar.
            //
            // Dimensi potong ikut disimpan di entri supaya biaya bisa dihitung
            // ULANG setiap kali media atau qty berubah - lihat
            // hitungUlangPotongManualDiDaftar(). Tanpa dimensi yang tersimpan,
            // entri hanya menyimpan satu angka mati.
            const hasilGrid = kalkulasiGridLinearGrid(
                ukuranMediaPotong().lebar,
                ukuranMediaPotong().tinggi,
                parseFloat(document.getElementById('input_lebar_potong').value) || 0,
                parseFloat(document.getElementById('input_tinggi_potong').value) || 0,
                qtyCetakBerjalan(),
                bacaTarifLinearGrid(sel.selectedOptions[0]).tarifPerMeter,
                bacaTarifLinearGrid(sel.selectedOptions[0]).minimumCharge
            );
            biaya = hasilGrid.terisi
                ? hasilGrid.biaya
                : cleanRupiah(document.getElementById('input_finishing_biaya').value);
        } else {
            const hargaStandar = await hargaStandarFinishing(sel.value, metode, qtyPakai);
            if (hargaStandar === false) {
                // Permintaan gagal: JANGAN diam-diam masukkan Rp0 ke nota.
                if (!silent) {
                    Swal.fire('Gagal Menghitung Finishing',
                        'Harga finishing tidak bisa dimuat dari server. Item BELUM ditambahkan - coba lagi atau isi biaya secara manual.',
                        'error');
                }
                return false;
            }
            biaya = hargaStandar;
        }

        finishingList.push({
            id_finishing: parseInt(sel.value), nama: sel.options[sel.selectedIndex].text, metode, status, qty_pakai: qtyPakai, biaya,
            // Potong Manual (LINEAR_GRID): dimensi potong ikut dikirim ke server
            // supaya biaya dihitung ulang dari rumus, bukan dari angka kasir.
            lebar_potong: metode === 'LINEAR_GRID'
                ? (parseFloat(document.getElementById('input_lebar_potong').value) || 0) : null,
            tinggi_potong: metode === 'LINEAR_GRID'
                ? (parseFloat(document.getElementById('input_tinggi_potong').value) || 0) : null,
            // Ukuran media yang dipotong. Untuk produk 'ukuran' ini sama
            // dengan Panjang/Lebar item; untuk produk per lembar (A3) inilah
            // satu-satunya sumber ukuran media, jadi server butuh ikut
            // menerimanya (lihat PenjualanController::store).
            lebar_media: metode === 'LINEAR_GRID' ? ukuranMediaPotong().lebar : null,
            tinggi_media: metode === 'LINEAR_GRID' ? ukuranMediaPotong().tinggi : null,
            tarif_per_meter: metode === 'LINEAR_GRID'
                ? bacaTarifLinearGrid(sel.selectedOptions[0]).tarifPerMeter : null,
            // Jahit keping (KEPING_ROLL): ukuran & jumlah keping dikirim ke
            // server supaya kapasitas dihitung ulang dari ukuran cetak
            // (meterKepingTerjaga) dan dimensi keping tersimpan di
            // lebar_potong/tinggi_potong (reuse kolom, sisi server).
            keping_lebar: metode === 'KEPING_ROLL'
                ? (parseFloat(document.getElementById('input_keping_lebar').value) || 0) : null,
            keping_panjang: metode === 'KEPING_ROLL'
                ? (parseFloat(document.getElementById('input_keping_panjang').value) || 0) : null,
            // qty_pakai entri ini = meter jahit total (jumlah x keliling keping).
            keping_jumlah: metode === 'KEPING_ROLL' ? Math.floor(jumlahKepingPanel()) : null,
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

// Penjaga hitung ulang harga keranjang: satu pergantian pelanggan memicu
// tiga handler hampir bersamaan (select2:select, change pelanggan, change
// golongan). Tanpa penjaga, satu penggantian = 3x jumlah item request ke API
// harga dan notifikasi yang bertumpuk.
let hitungUlangBerjalan = false;
let hitungUlangMenunggu = false;
// Token urutan hitung subtotal: hanya respons TERBARU yang boleh menulis
// field (lihat penjaga di hitungSubtotalInti), respons basi dibuang.
let urutanHitungSubtotal = 0;
// Di bawah minimum daftar harga (harga otomatis 0, lihat HargaService
// luas_min_daftar): angka minimum terakhir yang diketahui, dipakai
// tambahKeKeranjang untuk mencegat lebih awal dengan pesan yang jelas.
let peringatanMinDaftar = 0;
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
        // Token urutan (pola sama dengan urutanHitungFinishing): saat user
        // mengetik/berganti tab cepat, beberapa hitung berjalan bersamaan dan
        // respons get-harga yang lebih TUA bisa datang belakangan lalu
        // menimpa input_cetak & display subtotal dengan angka basi.
        const urutanSaatIni = ++urutanHitungSubtotal;
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
            // Mode HYBRID minimum luas (T4-A): deteksi hard-minimum LAMA berbasis
            // tier (harga 0 di bawah 1 mÃ‚Â², ditagih 1 mÃ‚Â²) sudah dihapus Ã¢â‚¬â€ harga
            // selalu fetch di luas aktual. Minimum luas tidak lagi disimpulkan
            // dari celah tier: kelompok minimum harus eksplisit di master
            // produk (min_luas_total + kelompok_minimum), dan selisihnya
            // digulung ke subtotal item terbesar kelompok saat checkout
            // (validasiItems/PenjualanCheckoutService).
            const url = `{{ url('/api/penjualan/get-harga') }}/${currentProduk.id_produk}/${idGol}/${qtyCari}${siapCetak ? '?siap_cetak=1' : ''}`;
            const response = await fetch(url);
            const data = await response.json();
            // Respons basi (user sudah mengetik lagi / pindah tab cepat) -
            // jangan tulis field, urutan terbaru yang boleh menimpa.
            if (urutanSaatIni !== urutanHitungSubtotal) return null;
            let hargaJual = parseFloat(data.harga_jual) || 0;
            const isPromo = data.is_promo;
            const promoSelesai = data.promo_selesai;

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
            updateDisplay(hargaJual, subtotal, isPromo, qtyCari);
            cekStokBom(currentProduk.id_produk, p, l, (currentProduk.metode_hitung === 'ukuran') ? qU : qB);
            // Harga 0 + daftar harga punya batas bawah (luas_min_daftar) ->
            // peringatan khusus "di bawah minimum daftar harga", bukan diam.
            peringatanMinDaftar = (hargaJual <= 0) ? (parseFloat(data.luas_min_daftar) || 0) : 0;
            await cekMinOrder(p, l, qU, qB, peringatanMinDaftar);
            return { harga_satuan: hargaJual, subtotal: subtotal, qty_hitung: qtyCari, p: p, l: l, qty_input: (currentProduk.metode_hitung === 'ukuran') ? qU : qB, luas_min_daftar: (hargaJual <= 0) ? (parseFloat(data.luas_min_daftar) || 0) : 0 };
        } catch (error) { console.error('hitungSubtotalInti gagal:', error); return { fetchError: true }; }
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
    async function cekMinOrder(p, l, qU, qB, luasMinDaftar) {
        const box = document.getElementById('min_order_warning');
        const detail = document.getElementById('min_order_warning_detail');
        if (!currentProduk) { box.hidden = true; return; }

        if (currentProduk.metode_hitung === 'ukuran') {
            const minP = parseFloat(currentProduk.min_panjang) || 0;
            const minL = parseFloat(currentProduk.min_lebar) || 0;
            const kurangP = minP > 0 && p > 0 && p < minP;
            const kurangL = minL > 0 && l > 0 && l < minL;
            if (kurangP || kurangL) {
                // Eksempsi (keputusan owner 2026-11-14): produk sama sudah
                // muncul >= 2 baris di keranjang -> hitungan minimum tidak
                // berlaku untuk baris ini, tidak perlu peringatan.
                if (minBarisProdukSama() >= 1) {
                    box.hidden = true;
                    return;
                }
                // Total kelompok yang menentukan (keputusan owner 2026-11-12):
                // tidak diperingatkan bila total luas kelompoknya di keranjang
                // (termasuk input saat ini) sudah memenuhi minimum order
                // produk - baris di bawah ukuran minimum tetap sah, konsisten
                // dengan relax di tombol Tambah dan di server. Hanya baris
                // se-KATEGORI yang dihitung (kelompok + kategori sama).
                const labelMin = (currentProduk.kelompok_minimum || String(currentProduk.id_produk)) + '|' + String(currentKategori || '0');
                const minLuasProduk = (parseFloat(currentProduk.min_luas_total) > 0)
                    ? parseFloat(currentProduk.min_luas_total)
                    : ((minP > 0 && minL > 0) ? (minP * minL / 10000) : 0);
                let luasKelompok = (p > 0 && l > 0) ? (p * l / 10000) * qU : 0;
                cart.forEach((it, idx) => {
                    if (idx === editIndex) return; // baris yang sedang diedit digantikan input saat ini
                    if (it.metode_hitung !== 'ukuran') return;
                    const labelIt = (it.kelompok_minimum || String(it.id_produk)) + '|' + String(it.kategori_produk || '0');
                    if (labelIt !== labelMin) return;
                    luasKelompok += (parseFloat(it.p) || 0) * (parseFloat(it.l) || 0) / 10000 * (parseFloat(it.qty_input) || 0);
                });
                if (minLuasProduk > 0
                    && Math.round(luasKelompok * 10000) / 10000 >= Math.round(minLuasProduk * 10000) / 10000) {
                    box.hidden = true;
                    // Jangan return: peringatan minimum daftar harga di bawah
                    // tetap perlu dievaluasi (isu harga, terpisah dari ukuran).
                } else {
                    // Sebutkan dimensi yang benar-benar di bawah minimum, supaya
                    // operator tidak bingung bila salah satu dimensi sudah memenuhi
                    // (mis. lebar cukup tapi panjang kurang).
                    const bagian = [];
                    if (kurangP) bagian.push(`panjang min ${minP} cm (input ${p} cm)`);
                    if (kurangL) bagian.push(`lebar min ${minL} cm (input ${l} cm)`);
                    detail.innerText = `Input di bawah minimum order produk ini: ${bagian.join('; ')}. Disarankan input >= minimum.`;
                    box.hidden = false;
                    return;
                }
            }
            // Minimum DAFTAR HARGA (range_min tier terkecil). Master produk bisa
            // membolehkan 120 x 200 (2,4 m2) padahal daftar harga baru menagih
            // mulai 3 m2 (mis. TC Century) -> di bawah itu harga otomatis Rp 0.
            // Dulu: hanya pesan generik "Detail belum lengkap" saat Tambah,
            // tanpa peringatan apa pun di layar isian. Dipanggil dengan
            // luasMinDaftar = 0 bila harga tidak 0 (promo aktif dsb.), jadi
            // tidak muncul peringatan palsu.
            const luasInput = (p * l / 10000) * qU;
            if (luasMinDaftar > 0 && luasInput > 0 && luasInput < luasMinDaftar) {
                detail.innerText = `Input di bawah minimum daftar harga: produk ini ditagih mulai ${luasMinDaftar} m2. Input saat ini ${luasInput} m2 - perbesar ukuran sampai mencapai minimum daftar.`;
                box.hidden = false;
                return;
            }
        } else {
            const minQty = parseFloat(currentProduk.min_order) || 0;
            if (minQty > 0 && qB > 0 && qB < minQty) {
                detail.innerText = `Input di bawah minimum order (${minQty} pcs). Disarankan input >= minimum. Sekarang: ${qB} pcs.`;
                box.hidden = false;
                return;
            }
            if (luasMinDaftar > 0 && qB > 0 && qB < luasMinDaftar) {
                detail.innerText = `Input di bawah minimum daftar harga: produk ini ditagih mulai ${luasMinDaftar} pcs. Input saat ini ${qB} pcs.`;
                box.hidden = false;
                return;
            }
        }


        box.hidden = true;
    }

    function updateDisplay(harga, subtotal, isPromo, qty) {
        const fmt = new Intl.NumberFormat('id-ID');
        document.getElementById('display_harga').innerText = `Rp ${fmt.format(harga)} /unit`;
        document.getElementById('display_subtotal').innerText = `Rp ${fmt.format(Math.round(subtotal))}`;
        document.getElementById('badge_promo').hidden = !isPromo;

        // Qty yang dipakai hitung (m2 untuk produk ber-methode 'ukuran', pcs
        // untuk 'quantity'). Ini hanya ditampilkan - angkanya sudah dihitung
        // di hitungSubtotalInti() dan tidak diubah di sini.
        //
        // Pakai properti .hidden, bukan classList('hidden'): elemennya
        // disembunyikan dengan ATRIBUT hidden di HTML, jadi classList tidak
        // pernah menghilangkan atribut itu dan chip tidak akan pernah tampil.
        const elQty = document.getElementById('display_qty');
        if (qty > 0) {
            const satuan = (currentProduk && currentProduk.metode_hitung === 'ukuran') ? 'm2' : 'pcs';
            elQty.innerHTML = `<i class="fas fa-cubes text-[10px] opacity-70 mr-1"></i>${fmt.format(qty)} ${satuan}`;
            elQty.hidden = false;
        } else {
            elQty.hidden = true;
        }
    }

    // 4. KERANJANG & AKSI
    /**
     * Hitung ulang harga item keranjang setelah pelanggan berganti.
     *
     * Barang dan ukuran item keranjang TIDAK berubah saat pelanggan diganti -
     * yang berubah tarifnya, karena harga produk memakai tier per golongan
     * konsumen (HargaService::getHargaEfektif menerima id_golongan). Dulu
     * keranjang dikosongkan paksa, jadi kasir kehilangan semua pekerjaan yang
     * sudah ia isi hanya karena salah pilih pelanggan. Sekarang barangnya
     * dibiarkan, tarifnya yang dihitung ulang.
     *
     * Yang SENGAJA tidak ikut dihitung ulang:
     *   - Biaya desain: input manual operator, tidak terikat golongan.
     *   - Biaya finishing: endpoint get-harga-finishing tidak menerima
     *     golongan sama sekali, jadi tarifnya sama untuk semua pelanggan.
     *   - Item jasa: harganya 100% manual, tidak pernah dari price list.
     *   - Biaya cetak yang sudah diketik manual (cetak_manual): itu harga
     *     yang disepakati, bukan hasil tarif. Menimpanya diam-diam sama
     *     dengan membuang keputusan kasir tanpa ia tahu.
     *
     * server (PenjualanController::store) memakai biaya_cetak yang dikirim
     * client apa adanya, jadi angka di sini menentukan angka yang tersimpan.
     */
    async function hitungUlangHargaKeranjang() {
        if (!cart.length) return { dihitung: 0, dipertahankan: 0, gagal: 0 };

        // Tiga handler (select2:select, change pelanggan, change golongan)
        // bisa menyala hampir bersamaan untuk satu kali penggantian. Kalau
        // tidak dijaga, satu penggantian = 3x jumlah item request ke API
        // harga, dan notifikasi bertumpuk.
        if (hitungUlangBerjalan) {
            hitungUlangMenunggu = true;
            return { dihitung: 0, dipertahankan: 0, gagal: 0, ditunda: true };
        }
        hitungUlangBerjalan = true;
        hitungUlangMenunggu = false;

        const idGol = document.getElementById('id_golongan').value;
        let dihitung = 0, dipertahankan = 0, gagal = 0;

        for (let i = 0; i < cart.length; i++) {
            const item = cart[i];

            if (item.is_jasa) continue;
            if (item.cetak_manual) { dipertahankan++; continue; }
            if (!item.id_produk || !(item.qty_hitung > 0)) { gagal++; continue; }

            try {
                // Mode HYBRID (T4-A): harga dihitung di luas/qty aktual - tidak
                // ada lagi lantai min qty 1 di sini. Minimum kelompok ditagih
                // terpisah saat checkout oleh server (validasiItems).
                const url = `{{ url('/api/penjualan/get-harga') }}/${item.id_produk}/${idGol}/${item.qty_hitung}${item.siap_cetak ? '?siap_cetak=1' : ''}`;
                const res = await fetch(url);
                if (!res.ok) { gagal++; continue; }

                const data = await res.json();
                const harga = parseFloat(data.harga_jual) || 0;
                if (harga <= 0) { gagal++; continue; }

                item.harga_satuan = harga;
                item.biaya_cetak = Math.round(harga * item.qty_hitung);
                item.subtotal = item.biaya_cetak
                    + (item.biaya_desain || 0)
                    + (item.biaya_finishing || 0);
                dihitung++;
            } catch (e) {
                gagal++;
            }
        }

        renderCart();
        hitungUlangBerjalan = false;

        // Kalau ada permintaan yang datang saat calculation masih berjalan,
        // jalankan sekali lagi setelah selesai - perubahan golongan kedua
        // tidak boleh hilang.
        if (hitungUlangMenunggu) {
            hitungUlangMenunggu = false;
            return await hitungUlangHargaKeranjang();
        }

        return { dihitung, dipertahankan, gagal };
    }

    /**
     * Hitung ulang + beri tahu kasir apa yang berubah dan apa yang tidak.
     *
     * Pemberitahuan ini bukan hiasan: harga yang dipertahankan (yang diketik
     * manual) TIDAK ikut menyesuaikan diri, jadi kasir wajib tahu item mana
     * yang masih memakai tarif lama supaya dia bisa meninjaunya sebelum
     * nota disimpan.
     */
    async function jalankanHitungUlangHarga() {
        if (!cart.length) return null;

        const hasil = await hitungUlangHargaKeranjang();
        if (!hasil) return null;

        if (hasil.dihitung === 0 && hasil.gagal === 0) {
            if (hasil.dipertahankan > 0) {
                Swal.fire({
                    toast: true, position: 'top-end', timer: 4000, showConfirmButton: false,
                    icon: 'info',
                    title: 'Harga keranjang tidak berubah',
                    text: `${hasil.dipertahankan} item memakai harga yang diketik manual - tidak disentuh.`
                });
            }
            return hasil;
        }

        const potongan = [];
        if (hasil.dihitung > 0) potongan.push(`${hasil.dihitung} item dihitung ulang`);
        if (hasil.dipertahankan > 0) potongan.push(`${hasil.dipertahankan} tetap manual`);
        if (hasil.gagal > 0) potongan.push(`${hasil.gagal} gagal dihitung`);

        Swal.fire({
            toast: true, position: 'top-end', timer: 4500, showConfirmButton: false,
            icon: hasil.gagal > 0 ? 'warning' : 'success',
            title: 'Harga keranjang mengikuti golongan baru',
            text: `${potongan.join(' . ')}. Periksa item yang tetap manual.`
        });

        return hasil;
    }

    // ---- Rekonsiliasi nama berkas vs finishing (POS, HARD BLOCK) ----
    // Keputusan owner 2026-11-16: nama berkas/deskripsi yang menyebut
    // pekerjaan finishing (mis. "laminasi", "kisscut") TAPI finishing itu
    // tidak ada di nota -> TOLAK saat tombol "Tambah ke Keranjang"/simpan
    // edit, SEBELUM baris masuk keranjang. Bukan lagi advisory - ini block.
    //
    // Port JS dari RekonsiliasiNotaService::temuan(); aturannya WAJIB sama:
    //   1. batas kata (normalisasi a-z0-9 + spasi),
    //   2. penjaga NEGASI - "tanpa/sisa/bekas/lama laminasi" bukan temuan,
    //   3. penjaga TERGABUNG - sebutan di dalam nama finishing yang sudah
    //      tertagih tidak jadi temuan,
    //   4. penjaga SUBSUMSI - alias spesifik yang match menutup alias generik.
    // Kamus diisi server (nama master finishing + tabel finishing_alias).
    const KAMUS_REKONSILIASI = @json(\App\Services\RekonsiliasiNotaService::kamus());
    // Finishing yang sudah tertagih di nota yang dikerjakan (mode SUSULAN) -
    // sebutan dianggap tercukupi bila pekerjaannya sudah ada di nota
    // (aturan nota-wide owner: "pada nota tidak ada item" baru ditolak).
    const FINISHING_TERTAGIH_NOTA = @json($finishingTertagihNota);

    function normalisasiRekonsiliasi(teks) {
        return String(teks || '').toLowerCase()
            .replace(/[^a-z0-9]+/gu, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function temuanRekonsiliasiJs(teks, tertagih) {
        const norm = normalisasiRekonsiliasi(teks);
        if (!norm || !KAMUS_REKONSILIASI.length) return [];
        const charged = (tertagih || []).map(Number);
        const esc = (s) => String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        // Nama resmi finishing yang tertagih (penjaga TERGABUNG).
        const namaTertagih = KAMUS_REKONSILIASI
            .filter((k) => k.id_kandidat.some((id) => charged.includes(Number(id))))
            .map((k) => k.alias);
        const hasil = [];
        const sudah = {};
        for (const k of KAMUS_REKONSILIASI) {
            if (k.id_kandidat.some((id) => charged.includes(Number(id))) || sudah[k.alias]) continue;
            const m = new RegExp('(?<![a-z0-9])' + esc(k.alias) + '(?![a-z0-9])').exec(norm);
            if (!m) continue;
            // PENJAGA 1 - negasi: kata batal dalam 12 karakter sebelum sebutan.
            const sebelum = norm.slice(Math.max(0, m.index - 12), m.index);
            if (/(^|\s)(tanpa|non|no|jangan|tak|bekas|sisa|lama)\s*$/.test(sebelum)) continue;
            // PENJAGA 2 - tergabung: alias bagian kata dari finishing tertagih.
            if (namaTertagih.some((nt) => new RegExp('(?<![a-z0-9])' + esc(k.alias) + '(?![a-z0-9])').test(nt))) continue;
            sudah[k.alias] = true;
            hasil.push(k);
        }
        // PENJAGA 3 - subsumsi: alias spesifik yang juga match menutup generik.
        return hasil.filter((h) => !hasil.some((lain) => lain.alias !== h.alias
            && lain.alias.length > h.alias.length
            && new RegExp('(?<![a-z0-9])' + esc(h.alias) + '(?![a-z0-9])').test(lain.alias)));
    }

    async function tambahKeKeranjang() {
        const idKonsumen = $('#id_konsumen').val();
        let namaFile = document.getElementById('nama_file').value.trim();

        if (!idKonsumen) return Swal.fire('Data belum lengkap', 'Pilih pelanggan dulu', 'error');
        if (!currentProduk) {
            // Produk terbaca terpilih tapi tidak termuat = gagal di jalur
            // server/koneksi, BUKAN operator lupa memilih - bedakan
            // pesannya supaya tidak menyalahkan operator.
            const pilihan = $('#select_produk').val();
            if (pilihan) {
                const nama = ($('#select_produk').find(':selected').text() || '').trim();
                return Swal.fire('Produk Gagal Dimuat',
                    'Produk "' + nama + '" terbaca terpilih, tetapi detailnya tidak berhasil diambil (sesi kedaluwarsa atau koneksi bermasalah). Muat ulang halaman (Ctrl+F5), masuk kembali bila diminta, lalu coba tambahkan lagi.',
                    'warning');
            }
            return Swal.fire('Data belum lengkap', 'Pilih produk dulu.', 'warning');
        }
        // Rekonsiliasi minimum (owner 2026-11-15, revisi): ukuran di bawah
        // minimum produk TIDAK dinaikkan otomatis di sini - input disimpan
        // apa adanya; penolakan terjadi saat SIMPAN (hard block di bawah),
        // kecuali produk sama sudah muncul >= 2 baris (eksempsi: hitungan
        // minimum tidak berlaku). Minimum QTY (pcs) tetap ditolak saat tambah.
        if (currentProduk.metode_hitung !== 'ukuran') {
            const minQty = parseFloat(currentProduk.min_order) || 0;
            const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
            if (minQty > 0 && qB > 0 && qB < minQty) {
                return Swal.fire('Di bawah minimum order', `Input di bawah minimum order produk ini (${minQty} pcs). Tambah qty dulu sebelum menambahkan.`, 'error');
            }
        }
        {
            const katAktif = document.getElementById('filter_kategori').value;
            const katProduk = $('#select_produk').find(':selected').data('kategori');
            if (!katAktif || katAktif === 'all' || String(katProduk) !== String(katAktif)) {
                $('#select_produk').val('').trigger('change');
                return Swal.fire('Kategori tidak cocok', 'Produk yang dipilih bukan dari kategori yang sedang aktif. Pilih ulang produknya.', 'warning');
            }
        }
        // Di bawah minimum DAFTAR HARGA (harga otomatis 0): cegat lebih awal,
        // sebelum operator dipaksa mengunggah gambar untuk order yang pasti
        // ditolak. Angkanya diset hitungSubtotalInti() dari luas_min_daftar.
        if (peringatanMinDaftar > 0) {
            const metodeUkuran = currentProduk.metode_hitung === 'ukuran';
            const pMin = parseFloat(document.getElementById('input_p').value) || 0;
            const lMin = parseFloat(document.getElementById('input_l').value) || 0;
            const qUMin = parseFloat(document.getElementById('input_qty_ukuran').value) || 1;
            const qBMin = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
            const nilaiMin = metodeUkuran ? (pMin * lMin / 10000) * qUMin : qBMin;
            if (nilaiMin > 0 && nilaiMin < peringatanMinDaftar) {
                return Swal.fire('Di bawah minimum daftar harga',
                    `Produk ini ditagih mulai ${peringatanMinDaftar}${metodeUkuran ? ' m2' : ' pcs'} sesuai Daftar Harga. Input saat ini ${nilaiMin}${metodeUkuran ? ' m2' : ' pcs'}. Perbesar ukuran/qty dulu sebelum menambahkan.`,
                    'error');
            }
        }
        // Tanpa berkas hanya diperbolehkan kalau berkas acuan terakhir gagal
        // karena ukuran (terimaBerkasTerlaluBesar) atau sedang menyedit item
        // lama yang memang tidak punya berkas - bukan karena kasir lupa
        // mengunggah.
        const itemEditLama = (editIndex !== null && cart[editIndex]) ? cart[editIndex] : null;
        if (!currentThumbnail && !thumbnailGagalUkuran && !(itemEditLama && !itemEditLama.thumbnail)) {
            Swal.fire('Upload Gambar Dulu', 'Unggah gambar/thumbnail acuan order sebelum menambahkannya ke keranjang.', 'warning');
            document.getElementById('input_thumbnail').focus();
            return;
        }
        // Nama file mengikuti berkas unggahan (revisi owner 2026-11-16):
        // tidak ada penyegaran dari data order lagi. Nilai kolom ini - nama
        // berkas atau suntingan kasir - yang dipakai apa adanya (namaFile
        // sudah dibaca dari kolom di awal fungsi).
        if (namaFile.length < 3) {
            Swal.fire('Keterangan File', 'Isi deskripsi minimal 3 karakter', 'warning');
            document.getElementById('nama_file').focus();
            return;
        }

        // Finishing yang sudah dipilih di baris isian tapi belum ditekan "Tambah Finishing" ikut dimasukkan otomatis.
        if (document.getElementById('select_finishing').value) await tambahFinishingKeDaftar(true);

        // HARD BLOCK rekonsiliasi nama berkas (keputusan owner 2026-11-16):
        // sebutan finishing di nama berkas/deskripsi yang tidak tertagih
        // di nota -> item TIDAK boleh masuk keranjang. Yang dianggap
        // tertagih = finishing item ini + finishing baris lain di keranjang
        // (nota-wide) + finishing nota asal (mode susulan). Baris yang
        // sedang DIEDIT tidak ikut menyumbang dari nilai lamanya - yang
        // dihitung daftar finishing barunya (finishingList).
        const tertagihNota = new Set((FINISHING_TERTAGIH_NOTA || []).map(Number));
        for (let i = 0; i < cart.length; i++) {
            if (i === editIndex) continue; // baris yang diedit: pakai finishingList baru
            const r = cart[i];
            if (r && !r.is_jasa) {
                (r.finishing || []).forEach((f) => { if (f && f.id_finishing) tertagihNota.add(Number(f.id_finishing)); });
                if (r.id_finishing) tertagihNota.add(Number(r.id_finishing));
            }
        }
        finishingList.forEach((f) => { if (f && f.id_finishing) tertagihNota.add(Number(f.id_finishing)); });
        const temuanNamaBerkas = temuanRekonsiliasiJs(namaFile, [...tertagihNota]);
        if (temuanNamaBerkas.length) {
            const daftar = temuanNamaBerkas
                .map((t) => '<b>&ldquo;' + escapeHtml(t.alias) + '&rdquo;</b> &rarr; ' + escapeHtml(t.nama_finishing))
                .join('<br>');
            return Swal.fire({
                icon: 'error',
                title: 'Berkas menyebut pekerjaan yang belum ditagih',
                html: 'Nama berkas menyebut pekerjaan yang tidak ada di nota:<br><br>' + daftar +
                    '<br><br>Tambahkan finishing-nya ke item dulu (bar biru &rarr; pilih finishing &rarr; Tambah Finishing) sebelum item masuk keranjang.',
            });
        }

        const res = await hitungSubtotal();
        if (res && res.fetchError) return Swal.fire('Gagal menghitung harga', 'Jaringan atau server bermasalah. Periksa koneksi lalu coba lagi.', 'error');
        if (!res || res.harga_satuan <= 0) {
            // Harga daftar tidak punya tier untuk qty ini (mis. TC Century baru
            // menagih mulai 3 m2 = 120 x 250 cm) -> sebutkan minimumnya secara
            // eksplisit, bukan pesan generik "Detail belum lengkap" yang tidak
            // menjelaskan apa pun ke operator.
            if (res && res.luas_min_daftar > 0 && res.qty_hitung > 0 && res.qty_hitung < res.luas_min_daftar) {
                const satuanMin = (currentProduk.metode_hitung === 'ukuran') ? ' m2' : ' pcs';
                return Swal.fire('Di bawah minimum daftar harga',
                    `Produk ini ditagih mulai ${res.luas_min_daftar}${satuanMin} sesuai Daftar Harga. Input saat ini ${res.qty_hitung}${satuanMin}. Perbesar ukuran/qty dulu sebelum menambahkan.`,
                    'error');
            }
            return Swal.fire('Data belum lengkap', 'Detail belum lengkap.', 'error');
        }

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
            // Penanda baris nota asal (mode edit). Kalau baris ini sedang
            // diperbarui, id_detail-nya ikut dibawa - server memakai itu
            // untuk tahu baris mana yang dikoreksi dan barang mana yang
            // memang baru. Tanpa ini, setiap penyimpanan edit akan
            // menambah baris dobel.
            id_detail: (editIndex !== null && cart[editIndex] && cart[editIndex].id_detail) ? cart[editIndex].id_detail : null,
            id_produk: currentProduk.id_produk,
            nama_produk: currentProduk.nama_produk,
            nama_file: namaFile,
            thumbnail: currentThumbnail,
            thumbnail_preview: currentThumbnailPreview,
            p: res.p, l: res.l,
            qty_input: res.qty_input,
            qty_hitung: res.qty_hitung,
            harga_satuan: res.harga_satuan,
            biaya_desain: siapCetak ? 0 : cleanRupiah(document.getElementById('input_desain').value),
            id_desainer: siapCetak ? '' : document.getElementById('input_desainer').value,
            siap_cetak: siapCetak,
            biaya_cetak: cleanRupiah(document.getElementById('input_cetak').value),
            // Dicatat per item, bukan global, supaya hitungUlangHargaKeranjang()
            // tahu item mana yang harganya sudah diketik kasir sendiri
            // (harga yang disepakati) dan mana yang masih hasil tarif.
            cetak_manual: cetakManualEdit,
            id_finishing: idFinishing,
            nama_finishing: namaFinishing,
            finishing: finishingList.map(f => ({ ...f })),
            biaya_finishing: totalFinishingList(),
            status_urgensi: document.getElementById('input_status_urgensi').value,
            status_finishing: finishingList.some(f => f.status === 'Custom') ? 'Custom' : 'Standar',
            catatan_produksi: document.getElementById('input_catatan_produksi').value.trim(),
            subtotal: res.subtotal,
            id_kategori: $('#filter_kategori').val(),
            min_order: currentProduk.min_order,
            // Dimensi minimum produk (untuk hard block saat simpan) - dikirim
            // sebagai-is; server tetap otoritatif.
            min_panjang: parseFloat(currentProduk.min_panjang) || 0,
            min_lebar: parseFloat(currentProduk.min_lebar) || 0,
            metode_hitung: currentProduk.metode_hitung,
            min_luas_total: parseFloat(currentProduk.min_luas_total) || 0,
            kelompok_minimum: currentProduk.kelompok_minimum || null,
            // Kategori produk saat baris dibuat (BUKAN filter aktif, yang bisa
            // bernilai 'all'; BUKAN pula dari API row - tidak ada kolomnya).
            // Dipakai relax minimum: hanya baris se-kategori yang boleh saling
            // menolong memenuhi minimum (keputusan owner 2026-11-12:
            // kelompok + kategori sama). Dikirim ke server sebagai-is.
            kategori_produk: String(currentKategori || '0')
        };

        const sedangEdit = editIndex !== null;
        if (editIndex !== null) {
            cart[editIndex] = itemData;
            editIndex = null;
        } else {
            cart.push(itemData);
        }

        // Mode HYBRID minimum luas (T4-A): tidak ada lagi absorben "item
        // sebelumnya ditagih 1 mÃ‚Â², lalu dikembalikan ke luas asli bila ada
        // item >= 1 mÃ‚Â²". Minimum berlaku per kelompok (min_luas_total +
        // kelompok_minimum) dan selisihnya digulung ke item terbesar saat
        // render keranjang + saat checkout di server - pertama-tama di sini
        // item masuk dengan luas aktualnya sendiri.

        renderCart();
        // Peringatan minimum order kelompok (non-blocking) setelah item
        // ditambah/diubah - dedup: tidak diulang selama angkanya sama.
        peringatanKelompokMinOrder();
        if (sedangEdit) {
            resetInputProduk();
        } else {
            // Item BARU masuk keranjang: pemilihan produk kembali KOSONG.
            // Dulu resetInputProduk() hanya mengosongkan field item (file,
            // ukuran, qty, biaya, finishing) tapi produk & currentProduk
            // tetap tertinggal - akibatnya event `change` tidak pernah
            // terpicu lagi di select_produk, sehingga harga tidak me-load
            // ulang, radio "jenis pengerjaan" (File Siap Cetak) tidak
            // muncul, dan filter kategori finishing tidak diterapkan untuk
            // item berikutnya. Dikosongkan + null-kan state supaya produk
            // berikutnya yang dipilih (termasuk produk yang SAMA) selalu
            // me-load ulang detail lengkap lewat getDetailProduk() -
            // jalur trigger('change') di bawah dengan val('') memanggil
            // resetInputProduk() (via jalur id-kosong getDetailProduk).
            currentProduk = null;
            $('#select_produk').val('').trigger('change');
            // Jalur id-kosong getDetailProduk tidak menyentuh wrapper metode
            // hitung - sembunyikan eksplisit agar panel produk lama tidak
            // terbawa ke item berikutnya.
            document.getElementById('wrapper_ukuran').classList.add('hidden');
            document.getElementById('note_ukuran').classList.add('hidden');
            document.getElementById('wrapper_qty').classList.add('hidden');
        }

        Toast.fire({
            icon: 'success',
            title: sedangEdit
                ? 'Item berhasil diperbarui'
                : 'Item masuk keranjang - pilih kembali produk & upload desain untuk item berikutnya'
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
            nama_produk: ' ' + namaJasa,
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

        Toast.fire({
            icon: 'success', title: 'Jasa ditambahkan ke keranjang'
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = (str == null ? '' : str);
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
        // Hanya dua nilai. Kunci 'Very Urgent' sengaja dihapus 16 Okt 2026
        // seiring opsi ketiganya di form - kunci yang tak terpakai cuma
        // menambah satu jalur warna yang tak pernah dievaluasi.
        // Fallback || di pemakaiannya tetap dijaga: order lama atau
        // kiriman API lama yang masih berisi 'Very Urgent' akan jatuh ke
        // abu-abu, bukan ke kelas "undefined" yang membuat chip kehilangan
        // seluruh warnanya. Saat ini tidak ada data seperti itu (semua
        // transaksi_detail = 'Normal'), tapi bentuknya tetap diamankan.
        const urgensiChip = {
            'Urgent': 'bg-amber-50 text-amber-700',
        };

        // Minimum luas outdoor, mode HYBRID (preview konsisten dengan backend
        // PenjualanCheckoutService): selisih per kelompok TIDAK lagi jadi baris
        // terpisah - digulung ke subtotal baris item TERBESAR kelompok itu.
        // Frontend hanya preview; angka final selalu dihitung ulang server.
        const minimumSurcharge = {};
        {
            const minGroups = {};
            cart.forEach((it, idx) => {
                if (!it.min_luas_total || it.min_luas_total <= 0) return;
                if (it.metode_hitung !== 'ukuran') return;
                const kelompok = it.kelompok_minimum || String(it.id_produk);
                const area = (parseFloat(it.p) || 0) * (parseFloat(it.l) || 0) / 10000 * (parseFloat(it.qty_input) || 0);
                if (!minGroups[kelompok]) minGroups[kelompok] = { min: it.min_luas_total, items: [], terbesar: null, idxTerbesar: null, areaTerbesar: 0 };
                minGroups[kelompok].items.push({ it, area });
                if (area > minGroups[kelompok].areaTerbesar) {
                    minGroups[kelompok].areaTerbesar = area;
                    minGroups[kelompok].terbesar = it;
                    minGroups[kelompok].idxTerbesar = idx;
                }
            });
            // Mode susulan: luasan baris nota asal ikut diakumulasi ke
            // kelompok sebelum selisih dihitung - kalau nota asal sudah
            // mencukupi minimum, item baru tidak ditagih (server
            // merekonsiliasi hal yang sama di rekonsiliasiKelompokSusulan).
            (ITEM_NOTA_ASAL || []).forEach((it) => {
                if (!(parseFloat(it.min_luas_total) > 0)) return;
                if (it.metode_hitung !== 'ukuran') return;
                const kelompok = it.kelompok_minimum || String(it.id_produk);
                if (!minGroups[kelompok]) return; // kelompok hanya di nota: tidak ada baris baru ditagih
                const area = ((parseFloat(it.p) || 0) * (parseFloat(it.l) || 0) / 10000) * (parseFloat(it.qty_input ?? it.qty) || 1);
                minGroups[kelompok].areaNota = (minGroups[kelompok].areaNota || 0) + area;
            });
            Object.keys(minGroups).forEach((kelompok) => {
                const g = minGroups[kelompok];
                const totalAktual = g.items.reduce((s, x) => s + x.area, 0) + (g.areaNota || 0);
                const selisih = Math.max(0, Math.round((g.min - totalAktual) * 10000) / 10000);
                if (selisih <= 0) return;
                const hargaSatuan = (g.terbesar && g.terbesar.harga_satuan) ? g.terbesar.harga_satuan : 0;
                if (!(hargaSatuan > 0) || g.idxTerbesar === null) return;
                minimumSurcharge[g.idxTerbesar] = {
                    kelompok: kelompok,
                    selisih: selisih,
                    selisihHarga: Math.round(hargaSatuan * selisih),
                };
            });
        }

        // Rincian kalkulasi Minimum Order per kelompok (spec 2026-11-15 Ã‚Â§6):
        // Total luas aktual, minimum order, kekurangan, luas ditagihkan,
        // tarif per mÃ‚Â², total harga + status (aktual vs minimum). Preview
        // saja - angka final selalu dihitung ulang server saat disimpan.
        const elRincian = document.getElementById('minimum_rincian');
        if (elRincian) {
            const grupMin = grupMinKeranjang();
            if (!grupMin.length) {
                elRincian.hidden = true;
                elRincian.innerHTML = '';
            } else {
                const fmtRp = (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(v));
                elRincian.hidden = false;
                elRincian.innerHTML = grupMin.map((g) => {
                    const cukup = g.selisih <= 0;
                    const tarif = (g.terbesar && g.terbesar.harga_satuan > 0) ? g.terbesar.harga_satuan : 0;
                    const ditagih = Math.max(g.total, g.min);
                    const warna = cukup ? 'text-emerald-700' : 'text-amber-700';
                    return `<div class="mt-2 rounded-xl border ${cukup ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'} px-3 py-2.5 text-xs leading-relaxed">
                        <p class="font-semibold ${cukup ? 'text-emerald-800' : 'text-amber-800'}"><i class="fas ${cukup ? 'fa-check-circle' : 'fa-tag'} mr-1"></i>Minimum Order ${escapeHtml(g.label)}</p>
                        <p class="mt-1 tabular-nums ${warna}">Total luas aktual: ${angkaId(g.total, 2)} m&sup2; &middot; Minimum order: ${angkaId(g.min, 2)} m&sup2;${cukup ? '' : ' &middot; Kekurangan: ' + angkaId(g.selisih, 2) + ' m&sup2;'}</p>
                        <p class="tabular-nums ${warna}">Luas ditagihkan: ${angkaId(ditagih, 2)} m&sup2; &middot; Tarif: ${fmtRp(tarif)}/m&sup2; &middot; Total: <b>${fmtRp(tarif * ditagih)}</b></p>
                        <p class="mt-0.5 font-medium ${warna}">${cukup ? 'Minimum terpenuhi - perhitungan memakai luas aktual.' : 'Harga memakai minimum order - kekurangan luas tetap ditagih.'}</p>
                    </div>`;
                }).join('');
            }
        }

        cart.forEach((item, index) => {
            const surcharge = minimumSurcharge[index] || null;
            const subtotalTampil = item.subtotal + (surcharge ? surcharge.selisihHarga : 0);
            total += subtotalTampil;
            const urgensi = item.status_urgensi && item.status_urgensi !== 'Normal' ? item.status_urgensi : null;
            const biayaTambahan = [
                item.biaya_cetak ? `Cetak ${Math.round(item.biaya_cetak).toLocaleString('id-ID')}` : null,
                item.biaya_desain ? `Desain ${Math.round(item.biaya_desain).toLocaleString('id-ID')}` : null,
                item.biaya_finishing ? `Finishing ${Math.round(item.biaya_finishing).toLocaleString('id-ID')}` : null,
            ].filter(Boolean).join(' &bull; ');

// Kartu, bukan baris tabel - keranjang ini juga tampil di panel
            // sempit (38% lebar di desktop, full width tapi tetap sempit di
            // tablet/mobile), tabel 5-kolom di lebar itu selalu perlu discroll
            // horizontal untuk melihat Aksi. Kartu yang membungkus otomatis
            // tidak pernah butuh scroll horizontal di lebar berapa pun.
            //
            // Chip finishing, File Siap Cetak, dan urgensi SENGAJA ditaruh
            // DI BAWAH baris thumbnail, memakai lebar penuh kartu - bukan di
            // dalam kolom teks. Ditempatkan di kolom teks, kolom itu hanya
            // ~200px sementara satu chip "Pemasangan Mata Ayam" sudah ~140px,
            // jadi tiap chip turun ke barisnya sendiri dan satu item bisa
            // memakan 5-6 baris. Di lebar penuh (~322px) dua chip muat satu
            // baris. Baris chip, baris biaya, dan baris subtotal memakai
            // pl-7 agar sejajar dengan teks produk, bukan dengan thumbnail.
            //
            // hover:bg-slate-50 (bukan /70 seperti sebelumnya): app.css:702-706
            // meng-override hanya /50, /60, dan /30 untuk mode gelap, sehingga
            // /70 tidak punya padanan dan hover menyalakan petak terang di
            // mode gelap.
            //
            // Ukuran teks: ui-refresh.css:1001-1017 memaksa .text-xs,
            // .text-[10px], dan .text-[11px] jadi 13px !important. Jadi
            // hierarki di dalam kartu ini memakai BERAT dan WARNA, bukan
            // ukuran font.
            const metaBagian = [
                (item.harga_satuan > 0) ? `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(item.harga_satuan))}` : null,
                (item.p > 0 && item.l > 0) ? `${angkaId(item.p, 0)} &times; ${angkaId(item.l, 0)} cm` : null,
            ].filter(Boolean).join(' &middot; ');

            const adaChip = (item.finishing && item.finishing.length)
                || ((!item.finishing || !item.finishing.length) && item.id_finishing)
                || item.siap_cetak
                || urgensi;

            container.innerHTML += `
                <article class="group px-4 py-3.5 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start gap-3">
                        <span class="w-4 shrink-0 pt-0.5 text-xs font-medium text-slate-500 tabular-nums">${index + 1}</span>
                        ${item.thumbnail ? (bisaDitampilkanBerkas(item.thumbnail) ? `<img loading="lazy" decoding="async" src="{{ asset('storage') }}/${item.thumbnail}" alt="Pratinjau desain" class="w-12 h-12 rounded-lg object-contain bg-white border border-slate-200 shrink-0">` : `<img loading="lazy" decoding="async" src="{{ asset('storage/default-thumbnail.png') }}" alt="Berkas desain" class="w-12 h-12 rounded-lg object-contain bg-white border border-slate-200 shrink-0 opacity-50" title="Berkas desain (${(item.thumbnail.split('.').pop() || '').toUpperCase()})">`) : `<div class="w-12 h-12 rounded-lg bg-slate-50 border border-slate-200 shrink-0 flex items-center justify-center text-slate-400"><i class="far fa-image"></i></div>`}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 leading-snug line-clamp-2">${escapeHtml(item.nama_produk)}</p>
                            ${item.nama_file ? `<p class="text-xs text-slate-500 mt-0.5 truncate">${escapeHtml(item.nama_file)}</p>` : ''}
                        </div>
                        <div class="flex shrink-0 gap-0.5">
                            <button type="button" onclick="editItem(${index})" title="Ubah item ini" aria-label="Ubah ${escapeHtml(item.nama_produk)}" class="w-8 h-8 flex items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 focus-visible:ring-2 focus-visible:ring-[var(--brand-accent-light)] focus-visible:ring-offset-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button type="button" onclick="hapusItem(${index})" title="Hapus item ini" aria-label="Hapus ${escapeHtml(item.nama_produk)}" class="w-8 h-8 flex items-center justify-center rounded-lg text-red-700 hover:bg-red-50 focus-visible:ring-2 focus-visible:ring-[var(--brand-accent-light)] focus-visible:ring-offset-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a2 2 0 00-1-1h-4a2 2 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    ${(metaBagian || adaChip) ? `
                    <div class="mt-2 pl-7 space-y-1.5">
                        ${metaBagian ? `<p class="text-xs font-medium text-slate-600 tabular-nums truncate">${metaBagian}</p>` : ''}
                        ${adaChip ? `<div class="flex flex-wrap items-center gap-1.5">
                        ${(item.finishing && item.finishing.length ? item.finishing : []).map(f => `<span class="inline-flex items-center gap-1 max-w-full px-2 py-0.5 rounded-md text-xs font-medium ${f.status === 'Custom' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700'}"><span class="truncate">${escapeHtml(f.nama)}</span>${(parseFloat(f.biaya) > 0) ? `<span class="shrink-0 font-normal tabular-nums">(${angkaId(f.biaya, 2)})</span>` : ''}</span>`).join('')}
                        ${(!item.finishing || !item.finishing.length) && item.id_finishing ? `<span class="inline-flex items-center gap-1 max-w-full px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700"><span class="truncate">${escapeHtml(item.nama_finishing)} &middot; ${escapeHtml(item.status_finishing || 'Standar')}</span>${(parseFloat(item.biaya_finishing) > 0) ? `<span class="shrink-0 font-normal tabular-nums">(${angkaId(item.biaya_finishing, 2)})</span>` : ''}</span>` : ''}
                        ${item.siap_cetak ? `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700" title="Harga File Siap Cetak, tanpa desain tim">File Siap Cetak</span>` : ''}
                        ${urgensi ? `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium ${urgensiChip[urgensi] || 'bg-slate-100 text-slate-700'}">${urgensi}</span>` : ''}
                    </div>` : ''}
                    </div>` : ''}

                    ${biayaTambahan ? `<p class="mt-2 pl-7 text-xs text-slate-500 leading-snug">${biayaTambahan}</p>` : ''}
                    ${surcharge ? `<p class="mt-1.5 pl-7 text-xs font-medium text-amber-700 leading-snug"><i class="fas fa-tag mr-1"></i>Termasuk Minimum Order ${escapeHtml(surcharge.kelompok)}: selisih ${surcharge.selisih.toFixed(2)} m&sup2; (${new Intl.NumberFormat('id-ID').format(surcharge.selisihHarga)})</p>` : ''}

                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-end justify-between gap-3 pl-7">
                        <span class="inline-flex items-center gap-1.5 shrink-0 text-xs font-medium text-slate-600 bg-slate-100 rounded-md px-2 py-1">
                            <i class="fas fa-cubes text-slate-400"></i>
                            Qty ${angkaId(item.qty_hitung, 2)}
                        </span>
                        <div class="text-right min-w-0">
                            <p class="text-xs text-slate-500 leading-none">Subtotal</p>
                            <p class="mt-1 text-base font-semibold text-slate-900 tabular-nums leading-none">Rp ${new Intl.NumberFormat('id-ID').format(Math.round(subtotalTampil))}</p>
                        </div>
                    </div>
                </article>`;
        });

        // Grand total sudah menghitung surcharge minimum yang digulung ke
        // baris item di atas - tidak ada lagi baris "Minimum Order" terpisah.
        document.getElementById('grand_total').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(total))}`;
        $('[id="item_count_badge"]').text(IS_SUSULAN ? (cart.length + " Item Baru") : (cart.length + " Items"));
    }

    // Kembali dari mode "UPDATE ITEM" ke mode tambah, TANPA mengosongkan isi
    // form. Dipakai saat item yang sedang diedit dihapus dari keranjang:
    // nilai yang diketik operator masih ada di layar, jadi bebas menekan
    // Tambah lagi untuk enteredkan ulang item yang sama - atau melanjutkannya
    // ke item berikutnya.
    function keluarDariModeEdit() {
        editIndex = null;
        const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
        if (!btn) return;
        btn.innerText = "TAMBAH";
        btn.classList.replace('bg-amber-500', 'bg-emerald-500');
        btn.classList.replace('hover:bg-amber-600', 'hover:bg-emerald-600');
    }

    function hapusItem(index) {
        Swal.fire({
            title: 'Hapus Item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            cart.splice(index, 1);

            // Index item yang SEDANG DIEDIT harus ikut diperbaiki.
            //
            // Dulu index-nya dibiarkan begitu saja setelah baris dihapus.
            // Dua akibat yang nyata:
            //  1) menghapus baris DI SEBELUM item yang sedang diedit
            //     membuat semua index sesudahnya geser satu ke atas, jadi
            //     "UPDATE ITEM" menimpa baris yang salah - item lama
            //     tetap utuh, item yang baru menjadi baris dobel;
            //  2) menghapus item yang SEDANG diedit bikin index-nya
            //     menunjuk ke luar jangkauan, dan Tambah menambah baris
            //     dobel dengan form kosong di tengahnya.
            if (editIndex !== null) {
                if (editIndex === index) {
                    keluarDariModeEdit();
                } else if (editIndex > index) {
                    editIndex = editIndex - 1;
                }
            }

            renderCart();
            // Spec 2026-11-15: penghapusan item bisa membuat total luas
            // kelompok jatuh di bawah minimum - peringatkan (non-blocking).
            peringatanKelompokMinOrder();
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
            delete document.getElementById('nama_file').dataset.auto; // nama historis item = milik kasir, jangan ditimpa auto
            document.getElementById('input_p').value = item.p;
            document.getElementById('input_l').value = item.l;
            document.getElementById('input_qty_ukuran').value = item.qty_input;
            document.getElementById('input_qty_biasa').value = item.qty_input;
            document.getElementById('input_desain').value = formatRupiahDariAngka(item.biaya_desain);
            setNilaiDesainer(item.id_desainer);
            document.getElementById('wrapper_desainer').classList.toggle('hidden', !(item.biaya_desain > 0));
            setSiapCetak(!!item.siap_cetak, true);
            document.getElementById('input_cetak').value = formatRupiahDariAngka(item.biaya_cetak);
            // Finishing item lama: pulihkan ke DAFTAR (baris isian dikosongkan). Bentuk lama (tunggal) dijadikan satu elemen.
            finishingList = (item.finishing && item.finishing.length ? item.finishing : (item.id_finishing ? [{
                id_finishing: item.id_finishing, nama: item.nama_finishing, metode: (function(){var o=document.querySelector(`#select_finishing option[value="${item.id_finishing}"]`);return (o&&o.dataset)?o.dataset.metode:'luas';})() || 'luas',
                status: item.status_finishing || 'Standar', qty_pakai: null, biaya: item.biaya_finishing || 0, catatan: '',
            }] : [])).map(f => ({ ...f }));
            renderFinishingList();
            document.getElementById('select_finishing').value = '';
            document.getElementById('input_finishing_biaya').value = '0';
            document.getElementById('input_status_urgensi').value = item.status_urgensi || 'Normal';
            setUrgensiRadio(item.status_urgensi || 'Normal', false);
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
            const metodeFinishingItem = (function(){var o=document.querySelector(`#select_finishing option[value="${item.id_finishing}"]`);return (o&&o.dataset)?o.dataset.metode:undefined;})();
            document.getElementById('wrapper_qty_finishing').classList.add('hidden');
            document.getElementById('input_qty_finishing').value = item.qty_input || 1;
            qtyFinishingManualEdit = true;
            currentThumbnail = item.thumbnail || null;
            currentThumbnailPreview = item.thumbnail_preview || null;
            const preview = document.getElementById('thumbnail_preview');
            const previewBerkas = document.getElementById('thumbnail_berkas');
            if (currentThumbnail) {
                // Berkas desain non-gambar (CDR, AI, EPS, PSD) tidak bisa
                // ditampilkan peramban - tampilkan thumbnail default, bukan
                // ikon generik, supaya kotaknya tidak kosong. Nama file tetap
                // dari berkas asli (kolom Nama File / toast).
                if (bisaDitampilkanBerkas(currentThumbnail)) {
                    preview.src = `{{ asset('storage') }}/${currentThumbnail}`;
                    preview.classList.remove('hidden');
                    previewBerkas.classList.add('hidden');
                } else {
                    preview.removeAttribute('src');
                    preview.classList.add('hidden');
                    previewBerkas.src = '{{ asset('storage/default-thumbnail.png') }}';
                    previewBerkas.classList.remove('hidden');
                }
                document.getElementById('thumbnail_kosong').classList.add('hidden');
            } else {
                preview.classList.add('hidden');
                previewBerkas.classList.add('hidden');
                document.getElementById('thumbnail_kosong').classList.remove('hidden');
            }

            const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
            btn.innerText = "UPDATE ITEM";
            btn.classList.replace('bg-emerald-500', 'bg-amber-500'); btn.classList.replace('hover:bg-emerald-600', 'hover:bg-amber-600');

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
        delete document.getElementById('nama_file').dataset.auto;
        // Minimum order: catatan ukuran dikembalikan ke standar.
        document.getElementById('note_ukuran_teks').textContent = 'Ukuran untuk menghitung harga. Gunakan ukuran file asli.';
        document.getElementById('input_p').value = "";
        document.getElementById('input_l').value = "";
        // Panel Potong Manual ikut dikosongkan: ukuran media per lembar yang
        // diketik operator untuk produk sebelumnya tidak boleh terbawa ke
        // produk berikutnya.
        document.getElementById('input_lebar_media_potong').value = "0";
        document.getElementById('input_tinggi_media_potong').value = "0";
        document.getElementById('input_lebar_potong').value = "0";
        document.getElementById('input_tinggi_potong').value = "0";
        // Panel jahit keping (KEPING_ROLL): inputan keping juga dikosongkan -
        // ukuran keping produk sebelumnya tidak boleh terbawa ke berikutnya.
        document.getElementById('input_keping_lebar').value = "";
        document.getElementById('input_keping_panjang').value = "";
        document.getElementById('input_keping_jumlah').value = "";
        document.getElementById('input_keping_kapasitas').value = "";
        document.getElementById('input_keping_total').value = "";
        document.getElementById('input_qty_ukuran').value = "1";
        document.getElementById('input_qty_biasa').value = "1";
        document.getElementById('input_desain').value = "0";
        document.getElementById('input_cetak').value = "0";
        document.getElementById('select_finishing').value = "";
        filterFinishingByKategori(null);
        document.getElementById('input_finishing_biaya').value = "0";
        // Panel jahit keping ikut ditutup: produk berikutnya belum tentu
        // memakai KEPING_ROLL.
        document.getElementById('panel_keping_roll').classList.add('hidden');
        finishingBiayaManualEdit = false;
        cetakManualEdit = false;
        siapCetak = false;
        document.querySelector('input[name="jenis_pengerjaan"][value="desain"]').checked = true;
        document.getElementById('wrapper_siap_cetak').classList.add('hidden');
        document.getElementById('wrapper_biaya_desain').classList.add('hidden');
        document.getElementById('wrapper_desainer').classList.add('hidden');
        setNilaiDesainer("{{ $desainerDefault }}");
        document.getElementById('btn_tambah_desain').classList.remove('hidden');
        document.getElementById('wrapper_biaya_finishing').classList.add('hidden');
        document.getElementById('wrapper_qty_finishing').classList.add('hidden');
        document.getElementById('input_qty_finishing').value = "1";
        qtyFinishingManualEdit = false;
        document.getElementById('input_status_urgensi').value = "Normal";
        setUrgensiRadio('Normal', false);
        document.getElementById('input_status_finishing').disabled = true;
        document.getElementById('input_status_finishing').value = "Standar";
        document.getElementById('input_catatan_produksi').value = "";
        document.getElementById('input_thumbnail').value = "";
        const labelThumb = document.getElementById('thumbnail_nama');
        if (labelThumb) labelThumb.textContent = 'Belum ada file';
        document.getElementById('thumbnail_preview').classList.add('hidden');
        // Placeholder ikon ikut dimunculkan lagi; tanpa ini kotak kosong setelah
        // item dihapus dari daftar.
        document.getElementById('thumbnail_kosong').classList.remove('hidden');
        // Thumbnail default (berkas desain) disembunyikan juga - kotaknya
        // kembali ke keadaan "belum ada file".
        const previewBerkas = document.getElementById('thumbnail_berkas');
        if (previewBerkas) previewBerkas.classList.add('hidden');
        currentThumbnail = null;
        thumbnailGagalUkuran = false;
        document.getElementById('display_harga').innerText = "Rp 0 /unit";
        document.getElementById('display_subtotal').innerText = "Rp 0";
        document.getElementById('display_qty').hidden = true;

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
        btn.classList.replace('bg-amber-500', 'bg-emerald-500'); btn.classList.replace('hover:bg-amber-600', 'hover:bg-emerald-600');

        document.getElementById('nama_file').focus();
    }

    function resetCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            icon: 'warning',
            showCancelButton: true
        }).then((r) => {
            if (!r.isConfirmed) {
                return;
            }
            cart = [];
            // Item yang sedang diedit ikut hilang bersama keranjangnya -
            // kalau index edit-nya dibiarkan, "UPDATE ITEM" akan menunjuk
            // baris yang sudah tidak ada.
            keluarDariModeEdit();
            renderCart();
        });
    }

    // Pengenal satu kali pengiriman: server hanya memproses id ini SEKALI (lihat middleware sekali.jalan), jadi
    // kirim ulang setelah jaringan putus tidak melahirkan nota kedua.
    let submitIdNota = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now() + '-' + Math.random().toString(36).slice(2));
    let sedangMenyimpan = false; // Guard duplicate submit: double-click / enter ganda / klik tombol di tab lain tidak boleh membuat DUA nota.
    let simpanMulai = 0; // Sejak kapan submit mengambang - dipakai supaya guard tidak macet: klik pada submit hang diungkap sesuai waktu dan kasus baru membuat hubungan yang lebih panjang.

    const BATAS_SIMPAN_MS = 90000; // 90 detik: lewat batas dianggap hang, tombol dibuka lagi.

    // Mode susulan: endpoint & tujuan kembali berbeda. Item dikirim ke
    // endpoint tambah-item milik nota asal (bukan endpoint nota baru), lalu
    // operator dikembalikan ke detail nota itu - bukan ke reload halaman
    // POS, supaya jelas itemnya sudah menempel ke nota yang mana.
    const MODE_SUSULAN = @if($notaSusulan) {
        url: '{{ route('penjualan.tambah-item', $notaSusulan->id_transaksi) }}',
        kembali: '{{ route('penjualan.show', $notaSusulan->id_transaksi) }}',
        noNota: @json($notaSusulan->nomor_nota)
    } @else null @endif;

    // Mode edit (koreksi selisih): endpoint & tujuan juga berbeda. Item
    // dikirim ke endpoint update-item milik nota yang dikoreksi, lalu
    // operator dikembalikan ke detail nota itu.
    const MODE_EDIT = @if($notaEdit) {
        url: '{{ route('penjualan.update-item', $notaEdit->id_transaksi) }}',
        kembali: '{{ route('penjualan.show', $notaEdit->id_transaksi) }}',
        noNota: @json($notaEdit->nomor_nota)
    } @else null @endif;

    async function simpanTransaksi(btn) {
        if (sedangMenyimpan) {
            const selisih = Math.round((Date.now() - simpanMulai) / 1000);
            // Guard supaya tak dobel-submit, TAPI tidak boleh macet: kalau submit
            // sebelumnya sudah mengambang lebih dari batas, dianggap hang dan
            // klik kali ini diproses lagi. Di bawah batas diberi umpan balik
            // agar operator tidak mengira tombolnya "tidak berfungsi".
            if (Date.now() - simpanMulai < BATAS_SIMPAN_MS) {
                return Swal.fire({ icon: 'info', title: 'Masih menyimpan...', text: 'Permintaan simpan sebelumnya sedang diproses (' + selisih + ' detik). Mohon tunggu sebentar.' });
            }
        }
        if (cart.length === 0) return Swal.fire('Keranjang Kosong', 'Pilih produk dulu.', 'error');
        // HARD BLOCK (keputusan owner 2026-11-15): minimum order bukan
        // diselesaikan dengan menaikkan angka otomatis - operator yang
        // memperbaiki sendiri / menambah item lain sekelompok. Baris
        // BARU (id_detail null) saja yang dihitung; baris lama (mode edit)
        // dibebaskan - server tetap otoritatif.
        const minBarisProdukSamaUntuk = (it) => {
            const idPr = String(it.id_produk);
            const kat = String(it.kategori_produk ?? '0');
            // Mode susulan: baris produk yang sama yang SUDAH ada di nota
            // ikut dihitung (satu nota = satu kesatuan; selaras server).
            const diCart = cart.filter((r) => String(r.id_produk) === idPr && String(r.kategori_produk ?? '0') === kat).length;
            const diNota = (ITEM_NOTA_ASAL || []).filter((r) => String(r.id_produk) === idPr && String(r.kategori_produk ?? '0') === kat).length;
            return diCart + diNota;
        };
        const pelanggaran = [];
        for (let i = 0; i < cart.length; i++) {
            const it = cart[i];
            if (it.id_detail) continue; // baris nota lama: server yang menentukan
            if (it.is_jasa || it.metode_hitung !== 'ukuran') continue;
            if (minBarisProdukSamaUntuk(it) >= 2) continue; // eksempsi produk sama
            const p = parseFloat(it.p) || 0, l = parseFloat(it.l) || 0, qty = parseFloat(it.qty_input) || 1;
            const luas = (p * l / 10000) * qty;
            const mP = parseFloat(it.min_panjang) || 0, mL = parseFloat(it.min_lebar) || 0;
            if (mP > 0 && p > 0 && p < mP) pelanggaran.push(`Baris ${i + 1} (${it.nama_produk || it.id_produk}): panjang ${p} cm di bawah minimum (${mP} cm). Ubah ke minimum order atau tambah item lain sekelompok.`);
            if (mL > 0 && l > 0 && l < mL) pelanggaran.push(`Baris ${i + 1} (${it.nama_produk || it.id_produk}): lebar ${l} cm di bawah minimum (${mL} cm). Ubah ke minimum order atau tambah item lain sekelompok.`);
            const mLuas = parseFloat(it.min_luas_total) || 0;
            if (!(mLuas > 0)) continue;
            const label = it.kelompok_minimum || String(it.id_produk);
            const sekelompok = cart.filter((r) => {
                if (r.is_jasa || r.metode_hitung !== 'ukuran') return false;
                if (!(parseFloat(r.min_luas_total) > 0)) return false;
                return (r.kelompok_minimum || String(r.id_produk)) === label;
            }).concat((ITEM_NOTA_ASAL || []).filter((r) => {
                // Baris nota asal ikut diakumulasi ke kelompok - satu nota =
                // satu kesatuan, selaras server ($barisNotaAda).
                if (r.metode_hitung !== 'ukuran') return false;
                if (!(parseFloat(r.min_luas_total) > 0)) return false;
                return (r.kelompok_minimum || String(r.id_produk)) === label;
            }));
            const total = sekelompok.reduce((s, r) => s + ((parseFloat(r.p) || 0) * (parseFloat(r.l) || 0) / 10000) * (parseFloat(r.qty_input ?? r.qty) || 1), 0);
            if (sekelompok.length === 1 && total < mLuas) {
                pelanggaran.push(`Minimum order kelompok "${label}" belum terpenuhi (${total.toFixed(2)} m&sup2; dari ${mLuas} m&sup2;). Tambah item lain sekelompok atau ubah ukuran item ini ke minimum order.`);
            }
        }
        if (pelanggaran.length) {
            return Swal.fire({ icon: 'error', title: 'Minimum order belum terpenuhi', html: pelanggaran.map((x) => `<p class="text-left text-sm mb-1">Ã¢â‚¬Â¢ ${x}</p>`).join('') });
        }
        // Cegah simpan nota jika ada item pcs di bawah minimum order.
        const itemMinPcs = cart.find(it => it.metode_hitung !== 'ukuran' && (parseFloat(it.min_order) || 0) > 0 && (parseFloat(it.qty_input) || 0) < (parseFloat(it.min_order) || 0));
        if (itemMinPcs) {
            return Swal.fire('Di bawah minimum order', `Item "${itemMinPcs.nama_produk}" di bawah minimum order (${itemMinPcs.min_order} pcs). Perbaiki dulu sebelum menyimpan.`, 'error');
        }
        const idKonsumen = $('#id_konsumen').val();
        if (!idKonsumen) return Swal.fire('Konsumen', 'Pilih konsumen dulu.', 'warning');

        // Audit kelengkapan (dropdown wajib terisi): Tipe Harga (golongan)
        // menentukan seluruh harga transaksi - wajib dipilih eksplisit.
        // Server (PenjualanController::store) sudah menolak tanpa ini;
        // di sini diberi notif + tandai field sebelum kirim.
        // Fallback id_golongan: di MODE EDIT elemen terkunci memakai
        // id="id_golongan" saja (tanpa id_golongan_hidden) - sebelumnya
        // baris ini melempar TypeError "idGolonganEl is null" yang mati
        // diam, jadi tombol Simpan terasa tidak berfungsi sama sekali.
        const idGolonganEl = document.getElementById('id_golongan_hidden') || document.getElementById('id_golongan');
        const namaGolonganEl = document.getElementById('id_golongan_hidden');
        if (!idGolonganEl) {
            return Swal.fire('Kesalahan Tampilan', 'Field Tipe Harga (golongan) tidak ditemukan. Muat ulang halaman lalu coba lagi.', 'error');
        }
        if (!idGolonganEl.value) {
            const targetTandai = namaGolonganEl || idGolonganEl;
            targetTandai.classList.add('ring-2', 'ring-rose-400', 'bg-rose-50/40');
            targetTandai.addEventListener('change', () => targetTandai.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/40'), { once: true });
            return Swal.fire('Lengkapi Data Pesanan Lebih Dulu', 'Tipe Harga (golongan konsumen) belum dipilih - pilih dulu, harga order mengikutinya.', 'warning');
        }
        namaGolonganEl && namaGolonganEl.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/40');

        var _to = document.querySelector('input[name="tipe_order"]:checked');
const tipeOrder = (_to ? _to.value : null) || 'Reguler';
        const noPo = document.getElementById('no_po') ? document.getElementById('no_po').value.trim() : '';
        if (tipeOrder === 'PO' && !noPo && !MODE_SUSULAN) {
            document.getElementById('no_po').focus();
            return Swal.fire('No. PO', 'No. PO wajib diisi untuk tipe order PO.', 'warning');
        }

        const payload = {
            id_konsumen: idKonsumen,
            id_golongan: idGolonganEl.value,
            no_nota: document.getElementById('no_nota_display') ? document.getElementById('no_nota_display').innerText : '',
            tipe_order: tipeOrder,
            no_po: noPo,
            tgl_order: document.getElementById('tgl_order') ? document.getElementById('tgl_order').value : '',
            items: cart,
            quotation_id: {{ !empty($quotationRef) ? (int) $quotationRef->id : 'null' }},
            submit_id: submitIdNota,
            _token: '{{ csrf_token() }}'
        };

        const pengendaliSimpan = new AbortController();
        let pembatasSimpan = null;
        try {
            // Kunci tombol selama request berjalan (flag JS + visual disabled).
            // Success & failure SAMA-SAMA membuka kunci: kalau gagal validasi,
            // operator harus bisa memperbaiki data lalu submit ulang; reload
            // hanya terjadi di jalur sukses.
            sedangMenyimpan = true;
            simpanMulai = Date.now();
            if (btn) { btn.disabled = true; btn.classList.add('opacity-50', 'pointer-events-none'); }
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
            // AbortController: server yang tidak pernah menjawab tidak boleh
            // membuat tombol simpan macet selamanya.
            pembatasSimpan = setTimeout(() => pengendaliSimpan.abort(), BATAS_SIMPAN_MS);
            const response = await fetch(MODE_EDIT ? MODE_EDIT.url : (MODE_SUSULAN ? MODE_SUSULAN.url : '{{ route('penjualan.store') }}'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                signal: pengendaliSimpan.signal
            });
            clearTimeout(pembatasSimpan);
            pembatasSimpan = null;
            if (!response.ok) {
                let pesan = 'Gagal menyimpan (HTTP ' + response.status + ').';
                try { const err = await response.json(); pesan = err.message || err.msg || pesan; } catch (e) {}
                throw new Error(pesan);
            }
            const result = await response.json();
            if (result.status === 'success') {
                if (MODE_EDIT) {
                    // Koreksi sudah menempel ke nota. Kembali ke detail nota
                    // itu supaya operator langsung melihat hasil koreksinya.
                    Swal.fire('Berhasil!', result.msg || ('Nota ' + MODE_EDIT.noNota + ' berhasil dikoreksi.'), 'success')
                        .then(() => { window.location.href = MODE_EDIT.kembali; });
                    return;
                }
                if (MODE_SUSULAN) {
                    // Item susulan sudah menempel ke nota asal. Kembali ke
                    // detail nota itu supaya operator langsung melihat baris
                    // barunya - reload halaman POS akan membuang kerjaan yang
                    // sudah diketik tanpa alasan.
                    Swal.fire('Berhasil!', result.message || ('Item ditambahkan ke nota ' + MODE_SUSULAN.noNota + '.'), 'success')
                        .then(() => { window.location.href = MODE_SUSULAN.kembali; });
                    return;
                }

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
                Swal.fire('Gagal!', result.msg || result.message || 'Terjadi kesalahan.', 'error');
            }
        } catch (e) {
            console.error('simpanTransaksi gagal:', e);
            const pesan = (e && e.name === 'AbortError')
                ? 'Server tidak merespons dalam ' + Math.round(BATAS_SIMPAN_MS / 1000) + ' detik. Periksa koneksi, lalu tab halaman ini dulu sebelum menyimpan ulang.'
                : (e && e.message) ? e.message : 'Permintaan tidak bisa diproses. Muat ulang halaman lalu coba lagi.';
            Swal.fire('Gagal', pesan, 'error');
        }
        finally {
            if (pembatasSimpan) clearTimeout(pembatasSimpan);
            sedangMenyimpan = false;
            if (btn) { btn.disabled = false; btn.classList.remove('opacity-50', 'pointer-events-none'); }
        }
    }
</script>
@endpush
