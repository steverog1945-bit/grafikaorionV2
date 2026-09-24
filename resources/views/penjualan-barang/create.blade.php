@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 max-w-4xl mx-auto">

    <x-page-header title="Penjualan Barang" subtitle="Jual bahan baku atau barang lain (custom) langsung ke konsumen - tanpa proses cetak/produksi. Harga diisi manual per transaksi." />

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

    <form action="{{ route('penjualan-barang.store') }}" method="POST" id="form-penjualan-barang">
        @csrf

        <x-card class="mb-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.group label="Nomor Nota (contoh)">
                    <x-form.input type="text" value="{{ $no_nota }}" disabled />
                </x-form.group>
                <div class="w-full">
                    <div class="flex items-center justify-between mb-2 ml-2 mr-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Konsumen <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openModal('konsumen-baru')" class="text-indigo-600 hover:text-indigo-700 font-bold text-[11px] flex items-center gap-1">
                            <i class="fas fa-plus text-[9px]"></i> Baru
                        </button>
                    </div>
                    <select id="select_konsumen" name="id_konsumen" class="select2-konsumen-barang w-full" required>
                        <option value="">-- Cari Nama / No. HP --</option>
                        @foreach($konsumen as $k)
                            <option value="{{ $k->id_konsumen }}">{{ $k->nama_konsumen }}{{ $k->telepon ? ' - ' . $k->telepon : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-card>

        <x-card class="mb-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Tambah Barang</h3>

            <div class="flex gap-2 mb-5">
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-2.5 text-xs font-bold cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-600 text-slate-500">
                    <input type="radio" name="tipe_tambah_barang" value="bahan" checked onchange="toggleTipeTambahBarang(this.value)" class="accent-indigo-600"> Bahan Baku (Stok)
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-slate-200 p-2.5 text-xs font-bold cursor-pointer has-[:checked]:bg-amber-50 has-[:checked]:border-amber-300 has-[:checked]:text-amber-600 text-slate-500">
                    <input type="radio" name="tipe_tambah_barang" value="custom" onchange="toggleTipeTambahBarang(this.value)" class="accent-amber-600"> Barang Lain (Custom)
                </label>
            </div>

            {{-- BAHAN BAKU (dari master data, stok ikut berkurang) --}}
            <div id="blok-tambah-bahan">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                    <x-form.group label="Kategori">
                        <x-form.select id="filter_kategori_bahan" onchange="filterBahanByKategori(this.value)">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </x-form.select>
                    </x-form.group>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Bahan Baku</label>
                        <select id="select_bahan" class="select2-bahan-barang w-full" disabled>
                            <option value="">-- Ketik Nama Bahan --</option>
                            @foreach($bahan as $b)
                                <option value="{{ $b->id_bahan }}" data-kategori="{{ $b->id_kategori }}" data-nama="{{ $b->nama_bahan }}" data-satuan="{{ $b->nama_satuan }}" data-stok="{{ angka($b->stok, 4) }}">{{ $b->nama_bahan }} ({{ number_format($b->stok, 2, ',', '.') }} {{ $b->nama_satuan }})</option>
                            @endforeach
                        </select>
                        <p id="hint_kunci_bahan" class="text-[11px] text-amber-600 mt-1.5 ml-2">
                            <i class="fas fa-lock text-[10px]"></i> Pilih Kategori dulu untuk mengaktifkan pilihan bahan.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Qty</label>
                            <input type="number" id="input_qty_bahan" min="0.01" step="0.01" value="1" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-center focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mt-4">
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Harga Satuan (Rp) - manual, sesuaikan harga bahan saat ini</label>
                        <input type="text" inputmode="numeric" id="input_harga_bahan" placeholder="0" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 shadow-inner format-ribuan">
                    </div>
                    <x-button type="button" onclick="tambahBahanKeDaftar()" class="w-full">
                        <i class="fas fa-plus"></i> Tambah ke Daftar
                    </x-button>
                </div>
            </div>

            {{-- BARANG CUSTOM / LAINNYA (bukan dari master bahan baku, tidak memotong stok) --}}
            <div id="blok-tambah-custom" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Nama Barang / Jasa</label>
                        <input type="text" id="input_nama_custom" placeholder="mis. Spanduk bekas, jasa titipan, dll" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-amber-500/20 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Qty</label>
                        <input type="number" id="input_qty_custom" min="0.01" step="0.01" value="1" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-center focus:ring-2 focus:ring-amber-500/20 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Satuan</label>
                        <input type="text" id="input_satuan_custom" value="Pcs" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-center focus:ring-2 focus:ring-amber-500/20 shadow-inner">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mt-4">
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Harga Satuan (Rp) - manual</label>
                        <input type="text" inputmode="numeric" id="input_harga_custom" placeholder="0" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-amber-500/20 shadow-inner format-ribuan">
                    </div>
                    <x-button type="button" onclick="tambahCustomKeDaftar()" variant="warning" class="w-full">
                        <i class="fas fa-plus"></i> Tambah ke Daftar
                    </x-button>
                </div>
                <p class="text-[11px] text-amber-600 mt-2 ml-2">
                    <i class="fas fa-info-circle text-[10px]"></i> Barang/jasa custom TIDAK ada di data master bahan baku dan TIDAK memotong stok - dipakai untuk barang titipan, jasa tambahan, atau barang sekali jual lainnya.
                </p>
            </div>
        </x-card>

        <x-card class="mb-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Daftar Barang Dijual</h3>
            <div id="daftar-bahan-kosong" class="text-center py-10 text-slate-300 italic text-sm">Belum ada barang ditambahkan.</div>
            <div id="daftar-bahan-list" class="divide-y divide-slate-50"></div>
            <div id="wrapper-input-items"></div>

            <div class="flex items-center justify-between gap-4 flex-wrap mt-5 bg-indigo-600 rounded-2xl px-6 py-4">
                <span class="text-xs font-bold text-indigo-200 uppercase tracking-widest">Grand Total</span>
                <span id="grand-total-bahan" class="text-2xl font-black text-white">Rp 0</span>
            </div>
        </x-card>

        <div class="flex justify-end">
            <x-button type="submit" id="btn-submit-penjualan-barang" class="!px-12 !py-5" disabled>Simpan &amp; Lanjut Bayar</x-button>
        </div>
    </form>
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

@push('scripts')
<script>
    $(document).ready(function () {
        $('.select2-konsumen-barang').select2({ placeholder: '-- Cari Nama / No. HP --', allowClear: true, width: '100%' });
        $('.select2-bahan-barang').select2({
            width: '100%',
            matcher: function (params, data) {
                const el = data.element;
                const katId = el ? el.getAttribute('data-kategori') : null;
                if (katId !== null && kategoriBahanAktif && katId != kategoriBahanAktif) return null;
                if (!params.term || params.term.trim() === '') return data;
                if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) return data;
                return null;
            }
        });
    });

    // 1. TOGGLE Bahan Baku <-> Custom
    function toggleTipeTambahBarang(v) {
        document.getElementById('blok-tambah-bahan').classList.toggle('hidden', v !== 'bahan');
        document.getElementById('blok-tambah-custom').classList.toggle('hidden', v !== 'custom');
    }

    // 2. MODAL KONSUMEN BARU (pola sama dengan penjualan/create.blade.php)
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
                let newOption = new Option(res.data.nama_konsumen + (res.data.telepon ? ' - ' + res.data.telepon : ''), res.data.id_konsumen, true, true);
                $('#select_konsumen').append(newOption).trigger('change');
                closeModal('konsumen-baru');
                Swal.fire('Berhasil', 'Pelanggan Baru Terpilih', 'success');
            } else {
                Swal.fire('Gagal', res.message || 'Data tidak dapat disimpan.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Cek koneksi database', 'error');
        }
    }

    // 3. Bahan TERKUNCI sampai Kategori dipilih - pola sama dengan Input Order
    // (penjualan/create.blade.php), supaya kasir tidak asal pilih bahan
    // tanpa sadar kategorinya dulu.
    let kategoriBahanAktif = '';
    function filterBahanByKategori(idKat) {
        kategoriBahanAktif = idKat;
        $('#select_bahan').val('').trigger('change');
        const terkunci = !idKat;
        $('#select_bahan').prop('disabled', terkunci);
        document.getElementById('hint_kunci_bahan').classList.toggle('hidden', !terkunci);
    }

    let daftarBahan = [];

    function tambahBahanKeDaftar() {
        const idBahan = document.getElementById('select_bahan').value;
        if (!idBahan) {
            Swal.fire('Pilih Bahan Dulu', 'Pilih kategori & bahan baku yang mau dijual.', 'warning');
            return;
        }
        const opt = document.querySelector(`#select_bahan option[value="${idBahan}"]`);
        const qty = parseFloat(document.getElementById('input_qty_bahan').value) || 0;
        const harga = cleanRupiah(document.getElementById('input_harga_bahan').value);

        if (qty <= 0) {
            Swal.fire('Qty Tidak Valid', 'Qty harus lebih dari 0.', 'warning');
            return;
        }
        if (harga <= 0) {
            Swal.fire('Harga Belum Diisi', 'Isi harga satuan bahan ini dulu.', 'warning');
            return;
        }

        const stokTersedia = parseFloat(opt.dataset.stok) || 0;
        if (qty > stokTersedia) {
            Swal.fire({
                title: 'Qty Melebihi Stok',
                html: `Stok tersedia cuma <b>${stokTersedia}</b> ${opt.dataset.satuan}, tapi qty diisi <b>${qty}</b>. Tetap lanjutkan?<br><small class="text-slate-400">Stok bisa jadi minus - pastikan barang fisiknya memang ada.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
            }).then((r) => { if (r.isConfirmed) prosesTambahBahan(idBahan, opt, qty, harga); });
            return;
        }
        prosesTambahBahan(idBahan, opt, qty, harga);
    }

    function prosesTambahBahan(idBahan, opt, qty, harga) {
        daftarBahan.push({ tipe: 'bahan', id_bahan: idBahan, nama: opt.dataset.nama, satuan: opt.dataset.satuan, qty, harga });
        renderDaftarBahan();
        document.getElementById('select_bahan').value = '';
        $('#select_bahan').trigger('change');
        document.getElementById('input_qty_bahan').value = 1;
        document.getElementById('input_harga_bahan').value = '';
    }

    function tambahCustomKeDaftar() {
        const nama = document.getElementById('input_nama_custom').value.trim();
        const qty = parseFloat(document.getElementById('input_qty_custom').value) || 0;
        const satuan = document.getElementById('input_satuan_custom').value.trim() || 'Pcs';
        const harga = cleanRupiah(document.getElementById('input_harga_custom').value);

        if (!nama) {
            Swal.fire('Nama Belum Diisi', 'Isi nama barang/jasa custom ini dulu.', 'warning');
            return;
        }
        if (qty <= 0) {
            Swal.fire('Qty Tidak Valid', 'Qty harus lebih dari 0.', 'warning');
            return;
        }
        if (harga <= 0) {
            Swal.fire('Harga Belum Diisi', 'Isi harga satuan barang ini dulu.', 'warning');
            return;
        }

        daftarBahan.push({ tipe: 'custom', nama, satuan, qty, harga });
        renderDaftarBahan();
        document.getElementById('input_nama_custom').value = '';
        document.getElementById('input_qty_custom').value = 1;
        document.getElementById('input_satuan_custom').value = 'Pcs';
        document.getElementById('input_harga_custom').value = '';
    }

    function hapusBahanDariDaftar(index) {
        daftarBahan.splice(index, 1);
        renderDaftarBahan();
    }

    function renderDaftarBahan() {
        const listEl = document.getElementById('daftar-bahan-list');
        const kosongEl = document.getElementById('daftar-bahan-kosong');
        const wrapInput = document.getElementById('wrapper-input-items');
        listEl.innerHTML = '';
        wrapInput.innerHTML = '';

        kosongEl.classList.toggle('hidden', daftarBahan.length > 0);

        let grandTotal = 0;
        daftarBahan.forEach((item, idx) => {
            const subtotal = item.qty * item.harga;
            grandTotal += subtotal;

            const baris = document.createElement('div');
            baris.className = 'flex items-center gap-3 py-3';
            const namaEl = document.createElement('div');
            namaEl.className = 'flex-1 min-w-0';
            const namaTitle = document.createElement('p');
            namaTitle.className = 'text-sm font-bold text-slate-700 truncate';
            namaTitle.textContent = item.nama;
            if (item.tipe === 'custom') {
                const badge = document.createElement('span');
                badge.className = 'text-[9px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded ml-1.5 align-middle';
                badge.textContent = 'CUSTOM';
                namaTitle.appendChild(badge);
            }
            const namaSub = document.createElement('p');
            namaSub.className = 'text-[11px] text-slate-400';
            namaSub.textContent = `${item.qty} ${item.satuan} x Rp ${item.harga.toLocaleString('id-ID')}`;
            namaEl.append(namaTitle, namaSub);

            const subtotalEl = document.createElement('span');
            subtotalEl.className = 'text-sm font-bold text-slate-600 shrink-0';
            subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');

            const btnHapus = document.createElement('button');
            btnHapus.type = 'button';
            btnHapus.className = 'w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50 shrink-0';
            btnHapus.innerHTML = '<i class="far fa-trash-alt text-xs"></i>';
            btnHapus.onclick = () => hapusBahanDariDaftar(idx);

            baris.append(namaEl, subtotalEl, btnHapus);
            listEl.appendChild(baris);

            const fields = item.tipe === 'custom' ? ['tipe', 'nama', 'qty', 'harga'] : ['tipe', 'id_bahan', 'qty', 'harga'];
            fields.forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${idx}][${field}]`;
                input.value = item[field];
                wrapInput.appendChild(input);
            });
        });

        document.getElementById('grand-total-bahan').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        document.getElementById('btn-submit-penjualan-barang').disabled = daftarBahan.length === 0;
    }
</script>
@endpush
@endsection
