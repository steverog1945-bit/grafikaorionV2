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
                <div class="bg-white shadow-sm border border-slate-200 rounded-2xl px-5 sm:px-8 py-4 flex items-center gap-4 sm:gap-6 max-w-full">
                    <div class="text-right border-r border-slate-100 pr-4 sm:pr-6 min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nomor Nota</p>
                        <p id="no_nota_display" class="text-base sm:text-xl font-black text-slate-800 font-mono tracking-tighter truncate">{{ $no_nota }}</p>
                    </div>
                    <svg class="w-8 h-8 text-indigo-100 hidden sm:block shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="flex flex-col lg:flex-row gap-8 items-start">

            <div class="w-full lg:w-[58%] space-y-8">
                {{-- DATA PELANGGAN --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Data Pelanggan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.group label="Cari Pelanggan">
                            <div class="flex gap-2">
                                <div class="flex-1">
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
                                <x-button type="button" onclick="openModal('konsumen-baru')" size="icon" class="!w-12 !h-12 !rounded-xl">+</x-button>
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
                </x-card>

                {{-- DETAIL PESANAN --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Detail Pesanan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
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

                    <x-form.group label="Nama File / Deskripsi" class="mb-8">
                        <x-form.input type="text" id="nama_file" placeholder="Contoh: Banner Spanduk 3x1m" />
                    </x-form.group>

                    <div id="wrapper_ukuran" class="hidden grid grid-cols-3 gap-6 p-6 bg-slate-50 rounded-2xl border border-slate-100 mb-8">
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Panjang (cm)</span>
                            <input type="number" id="input_p" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-3 text-sm font-black text-center">
                        </div>
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Lebar (cm)</span>
                            <input type="number" id="input_l" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-3 text-sm font-black text-center">
                        </div>
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Qty</span>
                            <input type="number" id="input_qty_ukuran" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-3 text-sm font-black text-center" value="1">
                        </div>
                    </div>

                    <div id="wrapper_qty" class="hidden mb-8">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-3">Jumlah Unit</label>
                        <input type="number" id="input_qty_biasa" oninput="hitungSubtotal()" class="w-full rounded-xl border-slate-200 p-4 text-2xl font-black text-center" value="1">
                    </div>

                    <div class="bg-indigo-900 rounded-3xl p-8 flex flex-col md:flex-row justify-between items-center gap-6 shadow-xl shadow-indigo-100">
                        <div class="text-center md:text-left">
                            <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Harga Estimasi</p>
                            <h2 id="display_subtotal" class="text-4xl font-black text-white italic tracking-tighter font-mono">Rp 0</h2>
                            <p id="display_harga" class="text-indigo-400 text-[10px] mt-1">Rp 0 /unit</p>
                            <span id="badge_promo" hidden class="mt-2 px-3 py-1 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full">🔥 Harga Promo Aktif</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-6">
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-[0.2em] mb-2">Finishing</p>
                                <select id="select_finishing" onchange="onFinishingChange()" class="w-36 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-xs focus:outline-none">
                                    <option value="">-- Tanpa Finishing --</option>
                                    @foreach($finishing as $f)
                                        <option value="{{ $f->id_finishing }}" data-harga="{{ $f->harga_default ?? 0 }}">{{ $f->nama_finishing }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-[0.2em] mb-2">Biaya Finishing</p>
                                <input type="number" id="input_finishing_biaya" oninput="hitungSubtotal()" class="w-28 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-right focus:outline-none" value="0">
                            </div>
                            <div class="text-right">
                                <p class="text-indigo-300 text-[10px] font-bold uppercase tracking-[0.2em] mb-2">Biaya Desain</p>
                                <input type="number" id="input_desain" oninput="hitungSubtotal()" class="w-28 bg-indigo-800/50 border border-indigo-400/30 rounded-lg p-2 text-white font-bold text-right focus:outline-none" value="0">
                            </div>
                            <button onclick="tambahKeKeranjang()" class="bg-emerald-500 hover:bg-emerald-400 text-white h-16 px-10 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                Tambah
                            </button>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- KERANJANG --}}
            <div class="w-full lg:w-[42%] flex flex-col self-stretch">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 flex flex-col h-full overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                        <h3 class="font-bold text-slate-800 uppercase text-[11px] tracking-widest">Keranjang Pesanan</h3>
                        <div id="item_count_badge" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-[10px] font-bold">0 Items</div>
                    </div>

                    <div class="flex-grow overflow-y-auto custom-scrollbar p-0">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-3">Produk & Deskripsi</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cart_body" class="divide-y divide-slate-50 text-slate-600">
                                <tr>
                                    <td colspan="4" class="py-20 text-center opacity-20">
                                        <p class="font-bold uppercase text-[9px] tracking-[0.2em]">Belum Ada Pesanan</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-auto p-8 bg-slate-900 rounded-b-[2rem]">
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <p class="text-slate-500 text-[9px] font-bold uppercase tracking-widest mb-1">Total Pembayaran</p>
                                <h2 id="grand_total" class="text-4xl font-black text-white font-mono tracking-tighter leading-none">Rp 0</h2>
                            </div>
                            <button onclick="resetCart()" class="p-3 bg-slate-800 text-slate-500 hover:text-red-400 rounded-xl transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        <button onclick="simpanTransaksi()" class="w-full py-5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-2xl font-black text-xs uppercase tracking-[0.3em] transition-all shadow-2xl shadow-indigo-900/40">
                            SIMPAN & CETAK NOTA
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
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

    .select2-container--default .select2-selection--single {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #f8fafc !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        color: #1e293b !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }
</style>
<script>
    // 1. GLOBAL STATE
    let cart = [];
    let currentProduk = null;
    let editIndex = null;

    $(document).ready(function() {
        $('.select2-konsumen').select2({
            placeholder: "-- Cari Nama / No. HP --",
            allowClear: true,
            width: '100%'
        });

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
        btn.innerText = "TAMBAH";
        btn.className = "bg-emerald-500 hover:bg-emerald-400 text-white h-16 px-10 rounded-2xl font-black text-xs uppercase tracking-widest transition-all";
    }

    // 2. MODAL FUNCTIONS
    async function saveKonsumen() {
        const nama = document.getElementById('new_nama').value;
        const hp = document.getElementById('new_hp').value;
        const idGol = document.getElementById('new_id_golongan').value;

        if (!nama) return Swal.fire('Opps', 'Nama wajib diisi!', 'warning');

        try {
            const response = await fetch('/konsumen/store-ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    nama_konsumen: nama,
                    telepon: hp,
                    id_golongan: idGol
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
        const selectProd = document.getElementById('select_produk');
        const options = selectProd.options;
        selectProd.value = "";
        resetInputProduk();
        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            if (opt.value === "") continue;
            const katId = opt.getAttribute('data-kategori');
            opt.style.display = (idKat === 'all' || katId == idKat) ? 'block' : 'none';
        }
    }

    async function getDetailProduk() {
        const id = $('#select_produk').val();
        if (!id) { resetInputProduk(); return; }
        const selectedOption = $('#select_produk').find(':selected');
        const metode = selectedOption.data('metode');
        $('#wrapper_ukuran').toggleClass('hidden', metode !== 'ukuran');
        $('#wrapper_qty').toggleClass('hidden', metode !== 'quantity');

        try {
            const res = await fetch(`/api/penjualan/produk/${id}`);
            currentProduk = await res.json();
            currentProduk.metode_hitung = metode;
            setTimeout(() => {
                if (metode === 'ukuran') { $('#input_p').focus().select(); }
                else { $('#input_qty_biasa').focus().select(); }
            }, 100);
            hitungSubtotal();
        } catch (e) { console.error("Gagal ambil produk:", e); }
    }

    function onFinishingChange() {
        const sel = document.getElementById('select_finishing');
        const opt = sel.options[sel.selectedIndex];
        const harga = opt ? parseFloat(opt.getAttribute('data-harga')) || 0 : 0;
        document.getElementById('input_finishing_biaya').value = harga;
        hitungSubtotal();
    }

    async function hitungSubtotal() {
        if (!currentProduk) return null;
        const idGol = document.getElementById('id_golongan').value;
        const p = parseFloat(document.getElementById('input_p').value) || 0;
        const l = parseFloat(document.getElementById('input_l').value) || 0;
        const qU = parseFloat(document.getElementById('input_qty_ukuran').value) || 0;
        const qB = parseFloat(document.getElementById('input_qty_biasa').value) || 0;
        const biayaDesain = parseFloat(document.getElementById('input_desain').value) || 0;
        const biayaFinishing = parseFloat(document.getElementById('input_finishing_biaya').value) || 0;

        let qtyCari = (currentProduk.metode_hitung === 'ukuran') ? (p * l / 10000) * qU : qB;
        if (qtyCari <= 0) { updateDisplay(0, 0); return null; }

        try {
            const url = `/api/penjualan/get-harga/${currentProduk.id_produk}/${idGol}/${qtyCari}`;
            const response = await fetch(url);
            const data = await response.json();
            const hargaJual = parseFloat(data.harga_jual) || 0;
            const subtotal = (hargaJual * qtyCari) + biayaDesain + biayaFinishing;
            updateDisplay(hargaJual, subtotal, data.is_promo);
            return { harga_satuan: hargaJual, subtotal: subtotal, qty_hitung: qtyCari, p: p, l: l, qty_input: (currentProduk.metode_hitung === 'ukuran') ? qU : qB };
        } catch (error) { return null; }
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
        if (namaFile.length < 3) {
            Swal.fire('Keterangan File', 'Isi deskripsi minimal 3 karakter', 'warning');
            document.getElementById('nama_file').focus();
            return;
        }
        if (!currentProduk) return Swal.fire('Opps!', 'Pilih produk dulu.', 'warning');

        const res = await hitungSubtotal();
        if (!res || res.harga_satuan <= 0) return Swal.fire('Opps!', 'Detail belum lengkap.', 'error');

        const selFinishing = document.getElementById('select_finishing');
        const idFinishing = selFinishing.value || null;
        const namaFinishing = idFinishing ? selFinishing.options[selFinishing.selectedIndex].text : null;

        const itemData = {
            id_produk: currentProduk.id_produk,
            nama_produk: currentProduk.nama_produk,
            nama_file: namaFile,
            p: res.p, l: res.l,
            qty_input: res.qty_input,
            qty_hitung: res.qty_hitung,
            harga_satuan: res.harga_satuan,
            biaya_desain: parseFloat(document.getElementById('input_desain').value) || 0,
            id_finishing: idFinishing,
            nama_finishing: namaFinishing,
            biaya_finishing: parseFloat(document.getElementById('input_finishing_biaya').value) || 0,
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
            container.innerHTML = '<tr><td colspan="4" class="py-20 text-center opacity-20"><p class="font-bold uppercase text-[9px] tracking-[0.2em]">Belum Ada Pesanan</p></td></tr>';
        }

        cart.forEach((item, index) => {
            total += item.subtotal;
            container.innerHTML += `
                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-50">
                    <td class="px-6 py-4">
                        <p class="text-[11px] font-black text-slate-800 uppercase leading-none">${escapeHtml(item.nama_produk)}</p>
                        <p class="text-[9px] text-slate-400 mt-1 uppercase font-bold tracking-tight">${escapeHtml(item.nama_file)}</p>
                        ${item.id_finishing ? `<p class="text-[9px] text-indigo-500 mt-1 uppercase font-bold tracking-tight">✨ ${escapeHtml(item.nama_finishing)}</p>` : ''}
                    </td>
                    <td class="px-4 py-4 text-center font-mono text-[10px] font-bold text-slate-700">
                        ${item.qty_hitung.toFixed(2)}
                    </td>
                    <td class="px-4 py-4 text-right">
                        <p class="text-[11px] font-black text-slate-900 font-mono">Rp ${new Intl.NumberFormat('id-ID').format(Math.round(item.subtotal))}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-3">
                            <button onclick="editItem(${index})" class="text-amber-500 hover:text-amber-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button onclick="hapusItem(${index})" class="text-red-400 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
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
        editIndex = index;

        $('#filter_kategori').val(item.id_kategori || 'all').trigger('change');
        $('#select_produk').val(item.id_produk).trigger('change');

        setTimeout(() => {
            document.getElementById('nama_file').value = item.nama_file;
            document.getElementById('input_p').value = item.p;
            document.getElementById('input_l').value = item.l;
            document.getElementById('input_qty_ukuran').value = item.qty_input;
            document.getElementById('input_qty_biasa').value = item.qty_input;
            document.getElementById('input_desain').value = item.biaya_desain;
            document.getElementById('select_finishing').value = item.id_finishing || '';
            document.getElementById('input_finishing_biaya').value = item.biaya_finishing || 0;

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
        document.getElementById('select_finishing').value = "";
        document.getElementById('input_finishing_biaya').value = "0";
        document.getElementById('display_harga').innerText = "Rp 0 /unit";
        document.getElementById('display_subtotal').innerText = "Rp 0";

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

        const payload = {
            id_konsumen: idKonsumen,
            id_golongan: document.getElementById('id_golongan').value,
            no_nota: document.getElementById('no_nota_display').innerText,
            items: cart,
            _token: '{{ csrf_token() }}'
        };

        try {
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
            const response = await fetch('/penjualan/store', {
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

                Swal.fire('Berhasil!', 'Transaksi disimpan.', 'success').then(() => window.location.reload());
            } else {
                Swal.fire('Gagal!', result.msg || 'Terjadi kesalahan.', 'error');
            }
        } catch (e) { Swal.fire('Error!', 'Gagal sistem.', 'error'); }
    }
</script>
@endpush
