@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 max-w-4xl mx-auto">

    <x-page-header title="Penjualan Barang" subtitle="Jual bahan baku langsung ke konsumen (mis. sisa roll bahan) - tanpa proses cetak/produksi. Harga diisi manual per transaksi." />

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
                <x-form.group label="Konsumen" required>
                    <select id="select_konsumen" name="id_konsumen" class="select2-konsumen-barang w-full" required>
                        <option value="">-- Cari Nama / No. HP --</option>
                        @foreach($konsumen as $k)
                            <option value="{{ $k->id_konsumen }}">{{ $k->nama_konsumen }}{{ $k->telepon ? ' - ' . $k->telepon : '' }}</option>
                        @endforeach
                    </select>
                </x-form.group>
            </div>
        </x-card>

        <x-card class="mb-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Tambah Barang</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
                            <option value="{{ $b->id_bahan }}" data-kategori="{{ $b->id_kategori }}" data-nama="{{ $b->nama_bahan }}" data-satuan="{{ $b->nama_satuan }}" data-stok="{{ $b->stok }}">{{ $b->nama_bahan }} ({{ number_format($b->stok, 2, ',', '.') }} {{ $b->nama_satuan }})</option>
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

    // Bahan TERKUNCI sampai Kategori dipilih - pola sama dengan Input Order
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
        daftarBahan.push({ id_bahan: idBahan, nama: opt.dataset.nama, satuan: opt.dataset.satuan, qty, harga });
        renderDaftarBahan();
        document.getElementById('select_bahan').value = '';
        $('#select_bahan').trigger('change');
        document.getElementById('input_qty_bahan').value = 1;
        document.getElementById('input_harga_bahan').value = '';
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

            ['id_bahan', 'qty', 'harga'].forEach(field => {
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
