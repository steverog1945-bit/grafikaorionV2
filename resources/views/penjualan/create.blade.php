@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-[#f3f4f6] p-4 lg:p-8">
    <div class="max-w-[1600px] mx-auto">

        <x-page-header>
            <x-slot:title>POINT OF <span class="text-indigo-600">SALE</span></x-slot:title>
            <x-slot:subtitle>
                <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                Sistem Kasir Percetakan &bull; {{ date('d M Y') }}
            </x-slot:subtitle>
            <x-slot:actions>
                <x-button href="{{ route('penjualan.riwayat') }}" variant="outline">
                    <i class="fas fa-receipt"></i> Riwayat Nota
                </x-button>
                <div class="bg-white shadow-sm border border-slate-100 rounded-2xl px-5 sm:px-8 py-4 flex items-center gap-4 sm:gap-6 max-w-full">
                    <div class="text-right border-r border-slate-100 pr-4 sm:pr-6 min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nomor Nota</p>
                        <p id="no_nota_display" class="text-base sm:text-xl font-black text-slate-800 tracking-tight truncate">{{ $no_nota }}</p>
                    </div>
                    <svg class="w-8 h-8 text-indigo-100 hidden sm:block shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="flex flex-col lg:flex-row gap-5 items-start">

            <div class="w-full lg:w-[58%] space-y-5">
                {{-- DATA PELANGGAN --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Data Pelanggan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-form.group label="Cari Pelanggan">
                            <div class="flex gap-2">
                                <div class="flex-1 min-w-0">
                                    <select id="id_konsumen" name="id_konsumen" class="select2-konsumen">
                                        <option value="">Cari Nama...</option>
                                        @foreach($konsumen as $k)
                                            <option value="{{ $k->id_konsumen }}"
                                                    data-nama="{{ $k->nama_konsumen }}"
                                                    data-telp="{{ $k->telepon }}"
                                                    data-golongan="{{ $k->id_golongan }}">
                                                {{ $k->nama_konsumen }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-button type="button" onclick="openModal('konsumen-baru')" size="icon" class="!w-12 !h-12 !rounded-xl shrink-0" title="Tambah Konsumen Baru">
                                    <i class="fas fa-plus text-xs"></i>
                                </x-button>
                            </div>
                        </x-form.group>

                        <x-form.group label="Nomor Telepon">
                            <input type="text" id="display_telp" class="w-full rounded-xl border-slate-200 bg-slate-50 p-3 text-sm font-bold text-slate-500" readonly placeholder="-">
                        </x-form.group>

                        <x-form.group label="Tipe Harga">
                            <x-form.select id="id_golongan">
                                @foreach($golongan as $g)
                                    <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                                @endforeach
                            </x-form.select>
                        </x-form.group>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <x-form.group label="Tipe Order">
                            <div class="flex gap-2">
                                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-2.5 text-xs font-bold cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-600 text-slate-500">
                                    <input type="radio" name="tipe_order" value="Reguler" checked onchange="onTipeOrderChange()" class="accent-indigo-600"> Reguler
                                </label>
                                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-2.5 text-xs font-bold cursor-pointer has-[:checked]:bg-amber-50 has-[:checked]:border-amber-300 has-[:checked]:text-amber-600 text-slate-500">
                                    <input type="radio" name="tipe_order" value="PO" onchange="onTipeOrderChange()" class="accent-amber-600"> PO
                                </label>
                            </div>
                        </x-form.group>
                        <x-form.group label="No. PO" id="wrapper_no_po" class="hidden">
                            <x-form.input type="text" id="no_po" placeholder="Nomor PO dari konsumen" />
                        </x-form.group>
                        <x-form.group label="Tanggal Order">
                            <x-form.input type="date" id="tgl_order" value="{{ date('Y-m-d') }}" />
                        </x-form.group>
                    </div>
                </x-card>

                {{-- DETAIL PESANAN --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Detail Pesanan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <x-form.group label="Kategori">
                            <x-form.select id="filter_kategori" onchange="filterProduk(this.value)">
                                <option value="all">Semua Kategori</option>
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </x-form.select>
                        </x-form.group>
                        <x-form.group label="Pilih Produk">
                            <select id="select_produk" onchange="getDetailProduk()" class="select2-produk w-full">
                                <option value="">-- Ketik Nama Produk --</option>
                                @foreach($produk as $p)
                                    <option value="{{ $p->id_produk }}" data-kategori="{{ $p->id_kategori }}" data-metode="{{ $p->metode_hitung }}">{{ $p->nama_produk }}</option>
                                @endforeach
                            </select>
                        </x-form.group>
                    </div>

                    <x-form.group label="Nama File / Deskripsi" class="mb-4">
                        <x-form.input type="text" id="nama_file" placeholder="Contoh: Banner Spanduk 3x1m" />
                    </x-form.group>

                    <div id="wrapper_ukuran" class="hidden grid grid-cols-3 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-3">
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Panjang (cm)</span>
                            <input type="number" id="input_p" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-2.5 text-sm font-black text-center">
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Lebar (cm)</span>
                            <input type="number" id="input_l" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-2.5 text-sm font-black text-center">
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Qty</span>
                            <input type="number" id="input_qty_ukuran" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-2.5 text-sm font-black text-center" value="1">
                        </div>
                    </div>
                    <p id="note_ukuran" class="hidden text-xs font-medium text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5 mb-4 leading-relaxed">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ukuran untuk menghitung harga bukan ukuran file asli. Silakan ubah ukuran jika bahan memerlukan penyesuaian.
                    </p>

                    <div id="wrapper_qty" class="hidden mb-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Jumlah Unit</label>
                        <input type="number" id="input_qty_biasa" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-3 text-2xl font-black text-center" value="1">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <x-form.group label="Status Urgensi">
                            <x-form.select id="input_status_urgensi">
                                <option value="Normal">Normal</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Very Urgent">Very Urgent</option>
                            </x-form.select>
                        </x-form.group>
                        <x-form.group label="Thumbnail Gambar" required>
                            <div class="flex items-center gap-2">
                                <input type="file" id="input_thumbnail" accept="image/png,image/jpeg,image/webp" onchange="uploadThumbnail(this)" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-indigo-50 file:text-indigo-600">
                                <img id="thumbnail_preview" class="hidden w-11 h-11 rounded-lg object-cover border border-slate-200">
                            </div>
                        </x-form.group>
                    </div>

                    <x-form.group label="Catatan Produksi" class="mb-4">
                        <textarea id="input_catatan_produksi" rows="2" placeholder="Catatan khusus untuk tim produksi (opsional)" class="w-full rounded-xl border-slate-200 p-2.5 text-sm"></textarea>
                    </x-form.group>

                    {{-- Indikator stok BOM - PERINGATAN saja, tidak memblokir order
                         (lihat catatan keputusan: workflow percetakan biasa order
                         dulu, restock belakangan sebelum naik cetak) --}}
                    <div id="stok_warning" hidden class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="text-xs font-bold text-amber-700 uppercase tracking-wide">Stok bahan tidak cukup</p>
                            <p id="stok_warning_detail" class="text-xs text-amber-600 mt-1"></p>
                        </div>
                    </div>

                    {{-- Indikator di bawah ukuran/qty minimum produk (Master Produk
                         punya field Min Panjang/Lebar/Order yang sebelumnya
                         dikumpulkan tapi tidak pernah dibaca di mana pun - PERINGATAN
                         saja seperti stok BOM, kasir yang putuskan tetap lanjut atau
                         tidak, mis. kalau memang ada kesepakatan khusus ke konsumen). --}}
                    <div id="min_order_warning" hidden class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                        <i class="fas fa-ruler-combined text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="text-xs font-bold text-amber-700 uppercase tracking-wide">Di bawah minimum order produk ini</p>
                            <p id="min_order_warning_detail" class="text-xs text-amber-600 mt-1"></p>
                        </div>
                    </div>

                    <div class="bg-indigo-900 rounded-2xl p-5 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-center md:text-left">
                            <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-widest mb-1">Harga Estimasi</p>
                            <h2 id="display_subtotal" class="text-3xl font-black text-white tracking-tight">Rp 0</h2>
                            <p id="display_harga" class="text-indigo-400 text-[10px] font-bold mt-1">Rp 0 /unit</p>
                            <span id="badge_promo" hidden class="mt-2 px-3 py-1 bg-rose-500 text-white text-xs font-bold uppercase tracking-widest rounded-full">🔥 Harga Promo Aktif</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-4">
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-widest mb-1.5">Finishing</p>
                                <select id="select_finishing" onchange="onFinishingChange()" class="w-32 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-xs focus:outline-none">
                                    <option value="">-- Tanpa Finishing --</option>
                                    @foreach($finishing as $f)
                                        <option value="{{ $f->id_finishing }}" data-harga="{{ $f->harga_default ?? 0 }}" data-metode="{{ $f->metode_hitung }}">{{ $f->nama_finishing }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-widest mb-1.5">Spek Finishing</p>
                                <select id="input_status_finishing" disabled class="w-28 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-xs focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed">
                                    <option value="Standar">Standar</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-widest mb-1.5">Biaya Cetak</p>
                                <input type="number" id="input_cetak" oninput="hitungSubtotal()" class="w-24 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-xs text-right focus:outline-none" value="0">
                            </div>
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-widest mb-1.5">Biaya Finishing</p>
                                <input type="number" id="input_finishing_biaya" oninput="hitungSubtotal()" class="w-24 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-xs text-right focus:outline-none" value="0">
                            </div>
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-widest mb-1.5">Biaya Desain</p>
                                <input type="number" id="input_desain" oninput="hitungSubtotal()" class="w-24 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-xs text-right focus:outline-none" value="0">
                            </div>
                            <button onclick="tambahKeKeranjang()" class="bg-emerald-500 hover:bg-emerald-400 text-white h-12 px-8 rounded-xl font-bold text-xs uppercase tracking-widest transition-all">
                                Tambah
                            </button>
                        </div>
                    </div>
                </x-card>

                {{-- TAMBAH JASA (layanan non-material, harga manual - lihat Master Jasa).
                     Accordion, TERTUTUP secara default - jasa dipakai jauh lebih jarang
                     dibanding produk cetak, jadi bentuk kartu penuh yang selalu terbuka
                     cuma memakan tempat vertikal tanpa dipakai kebanyakan order. Dibuka
                     lewat klik header, isinya tidak berubah sama sekali. --}}
                <x-card :padding="false">
                    <button type="button" onclick="toggleTambahJasa()" class="w-full flex items-center gap-3 p-5 text-left">
                        <div class="p-2 bg-purple-50 rounded-lg text-purple-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Tambah Jasa</h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">(Layanan non-material)</span>
                        <i id="icon_toggle_jasa" class="fas fa-chevron-down text-slate-300 ml-auto transition-transform"></i>
                    </button>

                    <div id="wrapper_tambah_jasa" class="hidden px-5 pb-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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
                                <x-form.input type="number" id="input_jasa_harga" placeholder="0" />
                            </x-form.group>
                        </div>

                        <x-form.group label="Keterangan" class="mb-4">
                            <x-form.input type="text" id="jasa_keterangan" placeholder="Contoh: Instalasi banner outdoor" />
                        </x-form.group>

                        <div class="flex justify-end">
                            <button onclick="tambahJasaKeKeranjang()" class="bg-purple-500 hover:bg-purple-400 text-white h-12 px-8 rounded-xl font-bold text-xs uppercase tracking-widest transition-all">
                                Tambah Jasa
                            </button>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- KERANJANG - sticky supaya Total & tombol Simpan selalu kelihatan di
                 layar berapa pun tingginya form Detail Pesanan di sebelah kiri,
                 tidak perlu scroll sampai bawah untuk menemukannya. Tinggi kartu
                 sendiri dibatasi setinggi viewport (dikurangi jarak dari atas),
                 daftar item di dalamnya yang scroll sendiri kalau melebihi. --}}
            <div class="w-full lg:w-[42%] flex flex-col lg:sticky lg:top-4 lg:self-start">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col overflow-hidden lg:max-h-[calc(100vh-2rem)]">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">Keranjang Pesanan</h3>
                        <div id="item_count_badge" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-xs font-bold">0 Items</div>
                    </div>

                    <div class="flex-grow overflow-y-auto custom-scrollbar p-0 min-h-[120px]">
                        <div id="cart_body" class="divide-y divide-slate-50 text-slate-600">
                            <div class="py-16 text-center opacity-30">
                                <i class="fas fa-receipt text-2xl mb-2 block"></i>
                                <p class="text-xs font-bold uppercase tracking-widest">Belum Ada Pesanan</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto p-5 bg-slate-900 rounded-b-2xl shrink-0">
                        <div class="flex justify-between items-center mb-5">
                            <div>
                                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Pembayaran</p>
                                <h2 id="grand_total" class="text-3xl font-black text-white tracking-tight leading-none">Rp 0</h2>
                            </div>
                            <button onclick="resetCart()" class="p-2.5 bg-slate-800 text-slate-500 hover:text-red-400 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        <button onclick="simpanTransaksi()" class="w-full py-4 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all">
                            Simpan & Cetak Nota
                        </button>
                    </div>
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
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-xs font-bold cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-600 text-slate-500">
                    <input type="radio" name="new_tipe_konsumen" value="temporary" checked class="accent-indigo-600"> Temporary
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-3 text-xs font-bold cursor-pointer has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-300 has-[:checked]:text-emerald-600 text-slate-500">
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

    // Nama file gambar -> teks deskripsi yang manusiawi: buang ekstensi,
    // ganti underscore/dash dengan spasi, rapikan spasi ganda. "banner_3x1-
    // meter_FINAL.jpg" jadi "banner 3x1 meter FINAL", bukan disalin mentah.
    function namaFileDariGambar(namaAsli) {
        return namaAsli
            .replace(/\.[^./]+$/, '')
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    // Kompres gambar lewat canvas sebelum diunggah: turunkan resolusi lalu
    // re-encode sebagai JPEG, dicoba bertahap (dimensi & kualitas makin
    // turun) sampai lolos target TARGET_BYTES. Thumbnail ini murni acuan
    // visual buat tim produksi (bukan untuk dicetak) - boleh dikompres
    // sangat agresif supaya proses load aplikasi (daftar order, kartu
    // keranjang, dst - semuanya menampilkan thumbnail ini) tetap ringan.
    // Dimensi ikut diperkecil (bukan cuma kualitas JPEG-nya) karena foto
    // dengan detail tinggi bisa tetap di atas target walau kualitasnya
    // sudah sangat rendah selama dimensinya masih besar. Latar putih
    // digambar dulu sebelum gambar asli supaya PNG transparan tidak berubah
    // jadi hitam saat di-flatten ke JPEG (JPEG tidak punya kanal alpha).
    function compressImage(file, targetBytes = 10 * 1024) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(objectUrl);

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                function renderAt(maxDim, quality) {
                    let { width, height } = img;
                    if (width > maxDim || height > maxDim) {
                        const scale = maxDim / Math.max(width, height);
                        width = Math.round(width * scale);
                        height = Math.round(height * scale);
                    }
                    canvas.width = width;
                    canvas.height = height;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);
                    return new Promise((res) => canvas.toBlob(res, 'image/jpeg', quality));
                }

                (async () => {
                    const dims = [480, 360, 280, 220, 160, 120, 90];
                    const qualities = [0.6, 0.4, 0.25, 0.15, 0.08];
                    let terkecil = null;

                    for (const dim of dims) {
                        for (const q of qualities) {
                            const blob = await renderAt(dim, q);
                            if (!blob) continue;
                            if (!terkecil || blob.size < terkecil.size) terkecil = blob;
                            if (blob.size <= targetBytes) { resolve(blob); return; }
                        }
                    }
                    // Tidak ada kombinasi yang lolos target (foto sangat kompleks) -
                    // pakai hasil terkecil yang berhasil didapat, jangan sampai
                    // gagal total/tidak ada thumbnail sama sekali.
                    if (terkecil) resolve(terkecil);
                    else reject(new Error('Gagal memproses gambar.'));
                })();
            };
            img.onerror = () => { URL.revokeObjectURL(objectUrl); reject(new Error('File bukan gambar yang valid.')); };
            img.src = objectUrl;
        });
    }

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
            width: '100%'
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
                const katId = el ? el.getAttribute('data-kategori') : null;
                if (katId !== null && currentKategoriFilter !== 'all' && katId != currentKategoriFilter) {
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

        $('.select2-konsumen').on('select2:select', function (e) {
            let idGol = $(this).find(':selected').data('golongan');
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
            let data = e.params.data.element.dataset;

            if (cart.length > 0) {
                cart = [];
                renderCart();
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Keranjang dikosongkan karena pelanggan berubah', showConfirmButton: false, timer: 2000 });
            }

            $('#display_telp').val(data.telp || '-');
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
        btn.className = "bg-emerald-500 hover:bg-emerald-400 text-white h-16 px-10 rounded-2xl font-bold text-xs uppercase tracking-widest transition-all";
    }

    // 2. MODAL FUNCTIONS
    async function saveKonsumen() {
        const nama = document.getElementById('new_nama').value;
        const hp = document.getElementById('new_hp').value;
        const idGol = document.getElementById('new_id_golongan').value;
        const tipeKonsumen = document.querySelector('input[name="new_tipe_konsumen"]:checked').value;

        if (!nama) return Swal.fire('Opps', 'Nama wajib diisi!', 'warning');

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
    }

    async function getDetailProduk() {
        const id = $('#select_produk').val();
        if (!id) { resetInputProduk(); return; }
        const selectedOption = $('#select_produk').find(':selected');
        const metode = selectedOption.data('metode');
        $('#wrapper_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#note_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#wrapper_qty').toggleClass('hidden', metode !== 'quantity');

        try {
            const res = await fetch(`{{ url('/api/penjualan/produk') }}/${id}`);
            currentProduk = await res.json();
            currentProduk.metode_hitung = metode;
            setTimeout(() => {
                if (metode === 'ukuran') { $('#input_p').focus().select(); }
                else { $('#input_qty_biasa').focus().select(); }
            }, 100);
            hitungSubtotal();
        } catch (e) { console.error("Gagal ambil produk:", e); }
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
        if (sel.value) {
            statusFinishing.disabled = false;
        } else {
            statusFinishing.disabled = true;
            statusFinishing.value = 'Standar';
            document.getElementById('input_finishing_biaya').value = 0;
        }

        // Reset lalu delegasikan ke hitungSubtotal() (yang sudah meng-await
        // refreshFinishingBiayaSuggestion() SEBELUM menghitung subtotal) -
        // sengaja TIDAK memanggil refreshFinishingBiayaSuggestion() lagi di
        // sini secara terpisah, supaya tidak ada dua fetch berjalan
        // bersamaan yang bisa saling menimpa (race condition).
        finishingBiayaManualEdit = false;
        await hitungSubtotal();
    }

    // Qty acuan tier finishing dipakai sama seperti qty produk (area untuk
    // metode 'ukuran', qty langsung untuk 'quantity') - finishing dianggap
    // mengikuti volume cetak yang sama dengan produk induknya.
    async function refreshFinishingBiayaSuggestion() {
        const sel = document.getElementById('select_finishing');
        if (!sel.value || finishingBiayaManualEdit || !currentProduk) return;
        const idFinishingSaatMulai = sel.value;

        // Satuan hitung finishing dipakai untuk cari qty acuan tier
        // finishing_harga adalah milik FINISHING ITU SENDIRI (kolom
        // finishing.metode_hitung, 'luas' vs 'quantity') - BUKAN otomatis
        // ikut metode_hitung produk induknya seperti sebelumnya. Sebelum ini,
        // finishing quantity (mis. "Pasang Mata Ayam" per pcs) yang dipasang
        // ke produk metode 'ukuran' (mis. Banner) salah dihitung pakai luas
        // m², padahal seharusnya tetap per pcs terpasang - dan sebaliknya.
        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 0;
        const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
        const metodeFinishing = sel.selectedOptions[0]?.dataset.metode;
        const pcsProduk = (currentProduk.metode_hitung === 'ukuran') ? qU : qB;
        const qtyCari = (metodeFinishing === 'luas')
            ? ((currentProduk.metode_hitung === 'ukuran') ? (p * l / 10000) * qU : pcsProduk)
            : pcsProduk;
        if (qtyCari <= 0) return;

        try {
            const res = await fetch(`{{ url('/api/penjualan/get-harga-finishing') }}/${sel.value}/${qtyCari}`);
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
            document.getElementById('input_finishing_biaya').value = data.harga ?? 0;
        } catch (e) {}
    }

    async function hitungSubtotal() {
        if (!currentProduk) return null;
        const idGol = document.getElementById('id_golongan').value;
        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 0;
        const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
        const biayaDesain = parseFloat(document.getElementById('input_desain').value) || 0;
        const biayaCetak = parseFloat(document.getElementById('input_cetak').value) || 0;

        let qtyCari = (currentProduk.metode_hitung === 'ukuran') ? (p * l / 10000) * qU : qB;
        if (qtyCari <= 0) { updateDisplay(0, 0); return null; }

        // Refresh dulu SEBELUM baca nilainya - supaya subtotal di bawah ini
        // memakai saran harga finishing yang sudah sesuai qty terbaru, bukan
        // nilai lama dari sebelum qty berubah (kalau dipanggil belakangan
        // tanpa di-await, hasilnya selalu telat satu langkah/race condition).
        await refreshFinishingBiayaSuggestion();
        const biayaFinishing = parseFloat(document.getElementById('input_finishing_biaya').value) || 0;

        try {
            const url = `{{ url('/api/penjualan/get-harga') }}/${currentProduk.id_produk}/${idGol}/${qtyCari}`;
            const response = await fetch(url);
            const data = await response.json();
            const hargaJual = parseFloat(data.harga_jual) || 0;
            const subtotal = (hargaJual * qtyCari) + biayaDesain + biayaFinishing + biayaCetak;
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

        if (!idKonsumen) return Swal.fire('Opps', 'Pilih pelanggan dulu', 'error');
        if (!currentProduk) return Swal.fire('Opps!', 'Pilih produk dulu.', 'warning');
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

        const res = await hitungSubtotal();
        if (!res || res.harga_satuan <= 0) return Swal.fire('Opps!', 'Detail belum lengkap.', 'error');

        const selFinishing = document.getElementById('select_finishing');
        const idFinishing = selFinishing.value || null;
        const namaFinishing = idFinishing ? selFinishing.options[selFinishing.selectedIndex].text : null;

        const itemData = {
            id_produk: currentProduk.id_produk,
            nama_produk: currentProduk.nama_produk,
            nama_file: namaFile,
            thumbnail: currentThumbnail,
            p: res.p, l: res.l,
            qty_input: res.qty_input,
            qty_hitung: res.qty_hitung,
            harga_satuan: res.harga_satuan,
            biaya_desain: parseFloat(document.getElementById('input_desain').value) || 0,
            biaya_cetak: parseFloat(document.getElementById('input_cetak').value) || 0,
            id_finishing: idFinishing,
            nama_finishing: namaFinishing,
            biaya_finishing: parseFloat(document.getElementById('input_finishing_biaya').value) || 0,
            status_urgensi: document.getElementById('input_status_urgensi').value,
            status_finishing: document.getElementById('input_status_finishing').value,
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

    async function onJasaChange() {
        const sel = document.getElementById('select_jasa');
        const namaJasa = sel.value ? sel.options[sel.selectedIndex].dataset.nama : '';
        const ketInput = document.getElementById('jasa_keterangan');
        if (namaJasa && !ketInput.value) {
            ketInput.value = namaJasa;
        }

        jasaHargaManualEdit = false;
        await refreshJasaHargaSuggestion();
    }

    async function refreshJasaHargaSuggestion() {
        const idJasa = document.getElementById('select_jasa').value;
        if (!idJasa || jasaHargaManualEdit) return;

        const qty = parseFloat(document.getElementById('input_jasa_qty').value) || 1;

        try {
            const res = await fetch(`{{ url('/api/penjualan/get-harga-jasa') }}/${idJasa}/${qty}`);
            const data = await res.json();
            // Cek ulang SETELAH await - sama seperti refreshFinishingBiayaSuggestion,
            // supaya fetch yang telat nyampe tidak menimpa balik angka yang
            // sudah keburu diketik manual selama fetch berjalan.
            if (jasaHargaManualEdit) return;
            if (data.harga > 0) {
                document.getElementById('input_jasa_harga').value = data.harga;
            }
        } catch (e) {}
    }

    function tambahJasaKeKeranjang() {
        const idKonsumen = $('#id_konsumen').val();
        if (!idKonsumen) return Swal.fire('Opps', 'Pilih pelanggan dulu', 'error');

        const selJasa = document.getElementById('select_jasa');
        const idJasa = selJasa.value;
        if (!idJasa) return Swal.fire('Opps!', 'Pilih jasa dulu.', 'warning');

        const namaJasa = selJasa.options[selJasa.selectedIndex].dataset.nama;
        const qty = parseFloat(document.getElementById('input_jasa_qty').value) || 0;
        const harga = parseFloat(document.getElementById('input_jasa_harga').value) || 0;
        const keterangan = document.getElementById('jasa_keterangan').value.trim() || namaJasa;

        if (qty <= 0) return Swal.fire('Opps!', 'Qty jasa harus lebih dari 0.', 'warning');
        if (harga <= 0) return Swal.fire('Opps!', 'Harga satuan jasa harus diisi.', 'warning');

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
                    <p class="text-xs font-bold uppercase tracking-widest">Belum Ada Pesanan</p>
                </div>`;
        }

        // Chip mini - satu ukuran (text-xs font-bold) dipakai untuk semua
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
                        ${item.thumbnail ? `<img src="/storage/${item.thumbnail}" class="w-11 h-11 rounded-lg object-cover border border-slate-100 shrink-0">` : `<div class="w-11 h-11 rounded-lg bg-slate-50 border border-slate-100 shrink-0 flex items-center justify-center text-slate-300"><i class="far fa-image text-sm"></i></div>`}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 leading-tight truncate">${escapeHtml(item.nama_produk)}</p>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">${escapeHtml(item.nama_file)}</p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                ${item.id_finishing ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600">${escapeHtml(item.nama_finishing)} &middot; ${escapeHtml(item.status_finishing || 'Standar')}</span>` : ''}
                                ${urgensi ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold ${urgensiChip[urgensi]}">${urgensi}</span>` : ''}
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
                        <span class="text-xs font-bold text-slate-400">Qty ${item.qty_hitung.toFixed(2)}</span>
                        <span class="text-sm font-bold text-slate-900">Rp ${new Intl.NumberFormat('id-ID').format(Math.round(item.subtotal))}</span>
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
        // akan menimpa balik angka historis begitu form-nya kebuka.
        finishingBiayaManualEdit = true;

        setTimeout(() => {
            document.getElementById('nama_file').value = item.nama_file;
            document.getElementById('input_p').value = item.p;
            document.getElementById('input_l').value = item.l;
            document.getElementById('input_qty_ukuran').value = item.qty_input;
            document.getElementById('input_qty_biasa').value = item.qty_input;
            document.getElementById('input_desain').value = item.biaya_desain;
            document.getElementById('input_cetak').value = item.biaya_cetak || 0;
            document.getElementById('select_finishing').value = item.id_finishing || '';
            document.getElementById('input_finishing_biaya').value = item.biaya_finishing || 0;
            document.getElementById('input_status_urgensi').value = item.status_urgensi || 'Normal';
            document.getElementById('input_status_finishing').disabled = !item.id_finishing;
            document.getElementById('input_status_finishing').value = item.status_finishing || 'Standar';
            document.getElementById('input_catatan_produksi').value = item.catatan_produksi || '';
            currentThumbnail = item.thumbnail || null;
            const preview = document.getElementById('thumbnail_preview');
            if (currentThumbnail) {
                preview.src = `/storage/${currentThumbnail}`;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }

            const btn = document.querySelector('button[onclick="tambahKeKeranjang()"]');
            btn.innerText = "UPDATE ITEM";
            btn.classList.replace('bg-emerald-500', 'bg-amber-500');

            hitungSubtotal();
            document.getElementById('nama_file').focus();
        }, 400);
    }

    function resetInputProduk() {
        document.getElementById('nama_file').value = "";
        document.getElementById('input_p').value = "";
        document.getElementById('input_l').value = "";
        document.getElementById('input_qty_ukuran').value = "1";
        document.getElementById('input_qty_biasa').value = "1";
        document.getElementById('input_desain').value = "0";
        document.getElementById('input_cetak').value = "0";
        document.getElementById('select_finishing').value = "";
        document.getElementById('input_finishing_biaya').value = "0";
        finishingBiayaManualEdit = false;
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
        btn.classList.replace('bg-amber-500', 'bg-emerald-500');

        document.getElementById('nama_file').focus();
    }

    function resetCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            icon: 'warning',
            showCancelButton: true
        }).then((r) => { if (r.isConfirmed) { cart = []; renderCart(); } });
    }

    async function simpanTransaksi() {
        if (cart.length === 0) return Swal.fire('Keranjang Kosong', 'Pilih produk dulu.', 'error');
        const idKonsumen = $('#id_konsumen').val();
        if (!idKonsumen) return Swal.fire('Konsumen', 'Pilih konsumen dulu.', 'warning');

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
            _token: '{{ csrf_token() }}'
        };

        try {
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
    }
</script>
@endpush
