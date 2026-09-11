@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Product Engineering">
        <x-slot:subtitle>
            <i class="fas fa-microchip text-indigo-400"></i>
            Engineering <span class="text-slate-300">/</span> Spesifikasi & Kalkulasi Stok
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button onclick="openTambahModal()">
                <i class="fas fa-plus-circle"></i>
                Tambah Produk
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- QUICK SEARCH & FILTER --}}
    <x-card class="mb-8">
        <form action="{{ route('produk.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <x-form.input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari Produk atau Barcode..." />
            </div>

            <div class="w-full lg:w-72">
                <x-form.select name="category" onchange="this.form.submit()">
                    <option value="">Semua Mesin</option>
                    @foreach($mesin as $m)
                        <option value="{{ $m->id_mesin }}" {{ request('category') == $m->id_mesin ? 'selected' : '' }}>
                            {{ strtoupper($m->nama_mesin) }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="w-full lg:w-48">
                <x-form.select name="status" onchange="this.form.submit()">
                    <option value="aktif" {{ request('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="semua" {{ request('status') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                </x-form.select>
            </div>

            @if(request()->anyFilled(['search', 'category']) || request('status', 'aktif') !== 'aktif')
                <a href="{{ route('produk.index') }}"
                   class="px-6 py-3 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-100 transition-all"
                   title="Reset Filter">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-5 text-left">Detail Produk</th>
                <th class="px-4 py-5 text-center">Metode Hitung</th>
                <th class="px-4 py-5 text-left">Bahan Baku (BOM)</th>
                <th class="px-4 py-5 text-center">Logic Pemakaian</th>
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($produk as $p)
            <tr class="group hover:bg-indigo-50/40 transition-all {{ $p->status === 'nonaktif' ? 'opacity-50' : '' }}">
                <td class="px-8 py-5">
                    <div class="font-bold text-slate-800 text-[14px] flex items-center gap-2">
                        {{ $p->nama_produk }}
                        @if($p->status === 'nonaktif')
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-400 text-[9px] font-black uppercase tracking-widest">Nonaktif</span>
                        @endif
                    </div>
                    <div class="text-[10px] text-indigo-500 font-bold mt-1 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                        {{ $p->nama_kategori ?? 'Umum' }}
                    </div>
                </td>

                <td class="px-4 py-5 text-center">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-widest {{ $p->metode_hitung == 'ukuran' ? 'bg-orange-50 text-orange-600 border border-orange-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }}">
                        <i class="fas {{ $p->metode_hitung == 'ukuran' ? 'fa-ruler-combined' : 'fa-layer-group' }} mr-2"></i>
                        {{ $p->metode_hitung }}
                    </span>
                </td>

                <td class="px-4 py-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i class="fas fa-boxes text-[12px]"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-600">
                            {{ $p->daftar_bahan ?? 'Belum ada bahan' }}
                        </span>
                    </div>
                </td>

                <td class="px-4 py-5 text-center">
                    <code class="px-3 py-1.5 rounded-lg bg-slate-100 text-indigo-600 text-[11px] font-black border border-slate-200">
                        {{ $p->cara_hitung_pemakaian ?? '-' }}
                    </code>
                </td>

                <td class="px-8 py-5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-button size="icon" variant="ghost" onclick="editProduk({{ json_encode($p) }})" class="border border-slate-100 hover:text-amber-500 hover:border-amber-200">
                            <i class="fas fa-edit text-sm"></i>
                        </x-button>

                        <form action="{{ route('produk.toggle-status', $p->id_produk) }}" method="POST" class="inline" id="toggle-form-{{ $p->id_produk }}">
                            @csrf @method('PATCH')
                            <x-button type="button" size="icon" variant="ghost" onclick="confirmToggleStatus('{{ $p->id_produk }}', '{{ $p->status }}', {{ json_encode($p->nama_produk) }})"
                                class="border border-slate-100 {{ $p->status === 'aktif' ? 'hover:text-slate-600' : 'hover:text-emerald-500 hover:border-emerald-200' }}"
                                title="{{ $p->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan kembali' }}">
                                <i class="far {{ $p->status === 'aktif' ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                            </x-button>
                        </form>

                        <form action="{{ route('produk.destroy', $p->id_produk) }}" method="POST" class="inline" id="delete-form-{{ $p->id_produk }}">
                            @csrf @method('DELETE')
                            <x-button type="button" size="icon" variant="ghost" onclick="confirmDelete('{{ $p->id_produk }}', '{{ $p->nama_produk }}')" class="border border-slate-100 hover:text-rose-600 hover:border-rose-200">
                                <i class="fas fa-trash text-sm"></i>
                            </x-button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <x-empty-state title="Database produk masih kosong" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$produk" />

    {{-- MODAL PRODUK + BOM --}}
    <x-modal id="produk" maxWidth="6xl">
        <form id="formProduk" action="{{ route('produk.store') }}" method="POST">
            @csrf
            <div id="methodField"></div>

            {{-- Header kustom (ikon + judul 2 baris) --}}
            <div class="flex justify-between items-start mb-10 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-drafting-compass text-2xl"></i>
                    </div>
                    <div>
                        <h3 id="modal-produk-title" class="text-2xl font-black text-slate-800 uppercase tracking-tighter">Product Engineering</h3>
                        <p class="text-slate-400 text-[12px] font-bold uppercase tracking-widest">Konfigurasi Multi-BOM & Alur Kerja Produksi</p>
                    </div>
                </div>
                <button type="button" onclick="closeProdukModal()" class="text-slate-300 hover:text-rose-500 transition-colors">
                    <i class="fas fa-times-circle text-3xl"></i>
                </button>
            </div>

            {{-- INFO PRODUK - full-width (dulu kolom sempit 4/12 di samping BOM,
                 sekarang selebar modal supaya field-nya mengalir jelas dari atas
                 ke bawah sebelum masuk ke komposisi BOM, mengikuti alur pada
                 referensi tampilan yang diberikan). --}}
            <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 space-y-6 mb-8">
                <p class="text-[11px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Info Produk
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-form.group label="Nama Produk">
                            <x-form.input type="text" name="nama_produk" id="input_nama_produk" required class="!bg-white !shadow-sm" />
                        </x-form.group>
                    </div>
                    <x-form.group label="Barcode">
                        <x-form.input type="text" name="barcode" placeholder="ID-001" class="!bg-white !shadow-sm" />
                    </x-form.group>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.group label="Mesin / Divisi Produksi">
                        <x-form.select name="id_mesin" id="input_id_mesin" onchange="filterBahanByMesin(this.value)" class="!bg-white !shadow-sm !text-indigo-600">
                            <option value="">-- Pilih Mesin --</option>
                            @foreach($mesin as $m)
                                <option value="{{ $m->id_mesin }}">{{ $m->nama_mesin }}</option>
                            @endforeach
                        </x-form.select>
                    </x-form.group>
                    <x-form.group label="Metode Hitung Order">
                        <x-form.select name="metode_hitung" onchange="toggleMinOrderFields()" class="!bg-white !shadow-sm !py-3">
                            <option value="ukuran">Berdasarkan Ukuran Cetak (m²)</option>
                            <option value="quantity">Berdasarkan Satuan (Pcs)</option>
                        </x-form.select>
                    </x-form.group>
                </div>

                {{-- Toggle eksplisit "pakai minimal order atau tidak" - sebelumnya
                     field min order cuma disembunyikan/tampil berdasarkan Metode
                     Hitung, tanpa cara eksplisit bilang "produk ini memang tidak
                     punya batas minimal" (mengandalkan konvensi implisit isi 0 =
                     tanpa minimum). Sekarang eksplisit seperti pada referensi -
                     pilih Tidak akan menyembunyikan & mengosongkan field ukuran/qty
                     minimumnya (tersimpan 0, sama artinya "tanpa minimum"). --}}
                <x-form.group label="Minimal Order">
                    <x-form.select id="input_pakai_min_order" onchange="toggleMinOrderFields()" class="!bg-white !shadow-sm !py-3">
                        <option value="ya">Menggunakan Minimal Order</option>
                        <option value="tidak">Tanpa Minimal Order</option>
                    </x-form.select>
                </x-form.group>

                <div id="field_min_ukuran" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.group label="Ukuran Min Order - Panjang (cm)">
                        <x-form.input type="number" name="min_p" step="0.01" min="0" placeholder="0" class="!bg-white !shadow-sm !py-3" />
                    </x-form.group>
                    <x-form.group label="Ukuran Min Order - Lebar (cm)">
                        <x-form.input type="number" name="min_l" step="0.01" min="0" placeholder="0" class="!bg-white !shadow-sm !py-3" />
                    </x-form.group>
                </div>

                <div id="field_min_qty">
                    <x-form.group label="Min Order (Pcs)">
                        <x-form.input type="number" name="min_order_qty" step="1" min="0" placeholder="0" class="!bg-white !shadow-sm !py-3" />
                    </x-form.group>
                </div>

                <x-form.group label="Keterangan">
                    <textarea name="keterangan" rows="2" placeholder="Catatan tambahan produk (opsional)" class="w-full bg-white shadow-sm rounded-xl border-none text-xs font-semibold p-3 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                </x-form.group>
            </div>

            {{-- KOMPOSISI: dua tab - Bahan Baku Penyusun (bom_detail, sudah ada
                 sejak lama) & Finishing Penyusun (bom_finishing - tabel & endpoint
                 lamanya, BomFinishingController, sudah ada di database tapi TIDAK
                 PERNAH disambungkan ke tampilan mana pun. Sekarang disatukan ke
                 form ini, disimpan atomik bareng data produk & bahan baku lewat
                 syncBomFinishing() di ProdukController, bukan panggilan terpisah). --}}
            <div class="bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 overflow-hidden">
                <div class="flex border-b border-slate-100">
                    <button type="button" onclick="switchBomTab('bahan')" id="tab-btn-bahan" class="flex-1 py-4 text-[11px] font-black uppercase tracking-widest transition-all bg-indigo-50 text-indigo-600 border-b-2 border-indigo-500">
                        <i class="fas fa-boxes mr-2"></i> Bahan Baku Penyusun
                    </button>
                    <button type="button" onclick="switchBomTab('finishing')" id="tab-btn-finishing" class="flex-1 py-4 text-[11px] font-black uppercase tracking-widest transition-all text-slate-400 border-b-2 border-transparent hover:text-slate-600">
                        <i class="fas fa-magic mr-2"></i> Finishing Penyusun
                    </button>
                </div>

                <div id="tab-panel-bahan" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-emerald-50/50 p-6 rounded-3xl border border-emerald-100">
                        <div class="md:col-span-1">
                            <label class="block text-[9px] font-black text-emerald-700 uppercase mb-2">Pilih Bahan</label>
                            <select id="temp_id_bahan" class="w-full border-none rounded-xl py-3 px-4 text-xs font-bold shadow-sm">
                                <option value="">-- Pilih Bahan --</option>
                                @foreach($bahan_baku as $bb)
                                    <option value="{{ $bb->id_bahan }}"
                                            data-nama="{{ $bb->nama_bahan }}"
                                            data-satuan="{{ $bb->satuan_stok }}"
                                            data-mesin="{{ $bb->id_mesin }}"
                                            class="bahan-opt hidden"> [{{ $bb->nama_kategori }}] {{ $bb->nama_bahan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[9px] font-black text-emerald-700 uppercase mb-2">Cara Hitung Pemakaian</label>
                            <select id="temp_rumus" class="w-full border-none rounded-xl py-3 px-4 text-xs font-bold shadow-sm">
                                <option value="(P*L)/10000">Luas (m²) - [P*L/10000]</option>
                                <option value="Q">Quantity (1:1) - [Satuan]</option>
                                <option value="2*(P+L)/100">Keliling (m) - [2*(P+L)/100]</option>
                                <option value="(P*L)/10000*2">Bolak Balik (x2)</option>
                            </select>
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <x-button type="button" variant="success" onclick="addBahanToTable()" class="w-full">
                                <i class="fas fa-plus"></i> Tambahkan
                            </x-button>
                        </div>
                    </div>

                    <x-table bare>
                        <thead>
                            <tr>
                                <th class="p-4 text-left">Bahan Baku Penyusun</th>
                                <th class="p-4 text-left">Cara Hitung Pemakaian</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bom_table_body" class="divide-y divide-slate-50 text-[12px] font-bold text-slate-600">
                            <tr id="empty_state">
                                <td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada bahan baku yang ditambahkan.</td>
                            </tr>
                        </tbody>
                    </x-table>
                </div>

                <div id="tab-panel-finishing" class="p-8 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-purple-50/50 p-6 rounded-3xl border border-purple-100">
                        <div class="md:col-span-1">
                            <label class="block text-[9px] font-black text-purple-700 uppercase mb-2">Pilih Finishing</label>
                            <select id="temp_id_finishing" class="w-full border-none rounded-xl py-3 px-4 text-xs font-bold shadow-sm">
                                <option value="">-- Pilih Finishing --</option>
                                @foreach($finishing as $f)
                                    <option value="{{ $f->id_finishing }}" data-nama="{{ $f->nama_finishing }}">{{ $f->nama_finishing }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[9px] font-black text-purple-700 uppercase mb-2">Qty per Produk</label>
                            <input type="number" id="temp_finishing_qty" value="1" min="0.01" step="0.01" class="w-full border-none rounded-xl py-3 px-4 text-xs font-bold shadow-sm">
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" onclick="addFinishingToTable()" class="w-full inline-flex items-center justify-center gap-2 rounded-xl font-bold uppercase tracking-wider transition-all active:scale-95 bg-purple-500 hover:bg-purple-600 text-white shadow-lg shadow-purple-100 text-xs px-6 py-3">
                                <i class="fas fa-plus"></i> Tambahkan
                            </button>
                        </div>
                    </div>

                    <x-table bare>
                        <thead>
                            <tr>
                                <th class="p-4 text-left">Finishing Penyusun</th>
                                <th class="p-4 text-left">Qty</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="finishing_table_body" class="divide-y divide-slate-50 text-[12px] font-bold text-slate-600">
                            <tr id="empty_state_finishing">
                                <td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada finishing yang ditambahkan.</td>
                            </tr>
                        </tbody>
                    </x-table>
                </div>
            </div>

            <div class="flex gap-4 mt-8">
                <x-button type="button" variant="secondary" onclick="closeProdukModal()" class="flex-1">Batal</x-button>
                <x-button type="submit" class="flex-[2]">Simpan Semua Data Produk</x-button>
            </div>
        </form>
    </x-modal>

</div>

@push('scripts')
<script>
    // Reset + tutup modal produk (menggantikan bagian "else" dari toggleModal() lama)
    function closeProdukModal() {
        document.getElementById('modal-produk-title').innerText = "Product Engineering";
        document.getElementById('formProduk').reset();
        document.getElementById('methodField').innerHTML = '';
        closeModal('produk');
    }

    function openTambahModal() {
        const form = document.getElementById('formProduk');
        document.getElementById('modal-produk-title').innerText = "Product Engineering";
        form.action = "{{ route('produk.store') }}";
        document.getElementById('methodField').innerHTML = '';
        form.reset();
        toggleMinOrderFields();

        document.getElementById('bom_table_body').innerHTML = `
            <tr id="empty_state">
                <td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada bahan baku yang ditambahkan.</td>
            </tr>`;
        document.getElementById('finishing_table_body').innerHTML = `
            <tr id="empty_state_finishing">
                <td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada finishing yang ditambahkan.</td>
            </tr>`;
        switchBomTab('bahan');

        openModal('produk');
    }

    // Tab "Bahan Baku Penyusun" / "Finishing Penyusun" - murni tampilan,
    // kedua tabel tetap ikut ter-submit bersamaan lewat form yang sama
    // (input hidden id_bahan[]/id_finishing[] ada di kedua tab, tab yang
    // tidak aktif cuma disembunyikan lewat class hidden, bukan dihapus).
    function switchBomTab(tab) {
        document.getElementById('tab-panel-bahan').classList.toggle('hidden', tab !== 'bahan');
        document.getElementById('tab-panel-finishing').classList.toggle('hidden', tab !== 'finishing');

        const aktif = 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-500';
        const nonaktif = 'text-slate-400 border-b-2 border-transparent hover:text-slate-600';
        document.getElementById('tab-btn-bahan').className = 'flex-1 py-4 text-[11px] font-black uppercase tracking-widest transition-all ' + (tab === 'bahan' ? aktif : nonaktif);
        document.getElementById('tab-btn-finishing').className = 'flex-1 py-4 text-[11px] font-black uppercase tracking-widest transition-all ' + (tab === 'finishing' ? aktif : nonaktif);
    }

    // Tampilkan input Min Order sesuai metode_hitung: Panjang/Lebar (m²) untuk
    // 'ukuran', atau Qty (Pcs) untuk 'quantity' - keduanya disimpan di kolom
    // berbeda (min_panjang/min_lebar vs min_order) jadi hanya satu yang relevan
    // untuk diisi tergantung metode yang dipilih.
    function toggleMinOrderFields() {
        const method = document.querySelector('select[name="metode_hitung"]').value;
        const pakaiMinOrder = document.getElementById('input_pakai_min_order').value === 'ya';

        document.getElementById('field_min_ukuran').classList.toggle('hidden', !pakaiMinOrder || method !== 'ukuran');
        document.getElementById('field_min_qty').classList.toggle('hidden', !pakaiMinOrder || method === 'ukuran');

        // "Tanpa Minimal Order" mengosongkan field yang disembunyikan (tersimpan
        // 0 di server, sama artinya tanpa batas minimum) - supaya angka lama
        // tidak diam-diam ikut tersubmit padahal sudah dipilih "Tidak".
        if (!pakaiMinOrder) {
            document.querySelector('input[name="min_p"]').value = '';
            document.querySelector('input[name="min_l"]').value = '';
            document.querySelector('input[name="min_order_qty"]').value = '';
        }
    }

    // EDIT PRODUK (AJAX FETCH)
    async function editProduk(dataAsli) {
        const id = dataAsli.id_produk;

        Swal.fire({
            title: 'Menyiapkan Data...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() }
        });

        try {
            const response = await fetch(`{{ url('/produk') }}/${id}`);
            const data = await response.json();

            const form = document.getElementById('formProduk');
            form.action = `{{ url('/produk') }}/${id}`;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            document.getElementById('modal-produk-title').innerText = "Edit: " + data.produk.nama_produk;
            document.getElementById('input_nama_produk').value = data.produk.nama_produk;
            document.getElementById('input_id_mesin').value = data.produk.id_mesin || '';
            form.querySelector('select[name="metode_hitung"]').value = data.produk.metode_hitung;
            form.querySelector('input[name="barcode"]').value = data.produk.barcode || '';
            form.querySelector('input[name="min_p"]').value = data.produk.min_panjang || '';
            form.querySelector('input[name="min_l"]').value = data.produk.min_lebar || '';
            form.querySelector('input[name="min_order_qty"]').value = data.produk.metode_hitung === 'quantity' ? (data.produk.min_order || '') : '';
            form.querySelector('textarea[name="keterangan"]').value = (data.produk.keterangan && data.produk.keterangan !== '-') ? data.produk.keterangan : '';

            // Tidak ada kolom tersendiri utk toggle "pakai minimal order?" -
            // disimpulkan dari data lama: ada angka minimum > 0 berarti "Ya",
            // kalau semuanya 0/kosong berarti "Tidak" (konvensi yang sama
            // dipakai sebelum toggle ini ada). WAJIB diisi SEBELUM
            // toggleMinOrderFields() di bawah supaya field yang baru saja
            // diisi tidak ikut dikosongkan kalau ternyata simpulannya "Tidak".
            const adaMinOrder = (parseFloat(data.produk.min_panjang) > 0) || (parseFloat(data.produk.min_lebar) > 0) || (parseFloat(data.produk.min_order) > 0);
            document.getElementById('input_pakai_min_order').value = adaMinOrder ? 'ya' : 'tidak';

            toggleMinOrderFields();

            // Set value mesin di atas TIDAK memicu event 'change' (cuma
            // assignment .value biasa), jadi filterBahanByMesin() harus
            // dipanggil manual di sini - sebelumnya terlewat, akibatnya saat
            // edit dropdown Bahan selalu kosong/tersembunyi semua sampai
            // operator sengaja mengubah-ubah Mesin/Divisi dulu (padahal
            // sudah benar) supaya listener onchange-nya ikut terpicu.
            filterBahanByMesin(data.produk.id_mesin || '');

            const tbody = document.getElementById('bom_table_body');
            tbody.innerHTML = '';

            if (data.bom && data.bom.length > 0) {
                data.bom.forEach(item => {
                    renderBahanRow(item.id_bahan, item.nama_bahan, item.cara_hitung_pemakaian, item.satuan_stok);
                });
            } else {
                tbody.innerHTML = '<tr id="empty_state"><td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada bahan baku.</td></tr>';
            }

            const tbodyFinishing = document.getElementById('finishing_table_body');
            tbodyFinishing.innerHTML = '';

            if (data.bom_finishing && data.bom_finishing.length > 0) {
                data.bom_finishing.forEach(item => {
                    renderFinishingRow(item.id_finishing, item.nama_finishing, item.qty);
                });
            } else {
                tbodyFinishing.innerHTML = '<tr id="empty_state_finishing"><td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada finishing.</td></tr>';
            }

            switchBomTab('bahan');

            Swal.close();
            openModal('produk');

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Gagal mengambil data. Pastikan Route /produk/{id} sudah benar.', 'error');
        }
    }

    function filterBahanByMesin(idMesin) {
        const selectBahan = document.getElementById('temp_id_bahan');
        const options = selectBahan.querySelectorAll('.bahan-opt');
        selectBahan.value = "";

        options.forEach(opt => {
            const mesinBahan = opt.getAttribute('data-mesin');
            if (idMesin === "" || mesinBahan == idMesin) {
                opt.classList.remove('hidden');
                opt.disabled = false;
            } else {
                opt.classList.add('hidden');
                opt.disabled = true;
            }
        });
    }

    function addBahanToTable() {
        const selectBahan = document.getElementById('temp_id_bahan');
        const selectRumus = document.getElementById('temp_rumus');

        if (!selectBahan.value) {
            Swal.fire('Pilih Bahan!', 'Silahkan pilih bahan baku.', 'error');
            return;
        }

        const optionSelected = selectBahan.options[selectBahan.selectedIndex];
        const idBahan = selectBahan.value;
        const namaBahan = optionSelected.getAttribute('data-nama');
        const satuan = optionSelected.getAttribute('data-satuan');
        let rumus = selectRumus.value;

        if (namaBahan.toLowerCase().includes('jahit')) {
            rumus = "2*(P+L)/100";
        }

        renderBahanRow(idBahan, namaBahan, rumus, satuan);
        selectBahan.value = "";
    }

    function renderBahanRow(id, nama, rumus, satuan) {
        const tbody = document.getElementById('bom_table_body');
        const emptyState = document.getElementById('empty_state');
        if (emptyState) emptyState.remove();

        const existing = tbody.querySelector(`input[value="${id}"][name="id_bahan[]"]`);
        if (existing) {
            Swal.fire('Info', 'Bahan sudah ada di list.', 'info');
            return;
        }

        const rowId = 'row_' + Math.random().toString(36).substr(2, 9);
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.className = "hover:bg-slate-50 border-b border-slate-50";

        tr.innerHTML = `
            <td class="p-4">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                    <input type="hidden" name="id_bahan[]" value="${id}">
                    <span class="font-bold">${nama}</span>
                    <small class="text-slate-400">${satuan ? '('+satuan+')' : ''}</small>
                </div>
            </td>
            <td class="p-4">
                <input type="hidden" name="cara_hitung_pemakaian[]" value="${rumus}">
                <code class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded text-[10px] font-black">${rumus}</code>
            </td>
            <td class="p-4 text-center">
                <button type="button" onclick="document.getElementById('${rowId}').remove(); checkEmpty();" class="text-slate-300 hover:text-rose-500">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function checkEmpty() {
        const tbody = document.getElementById('bom_table_body');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `<tr id="empty_state"><td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada bahan baku.</td></tr>`;
        }
    }

    // Finishing Penyusun - pola identik dengan Bahan Baku Penyusun di atas
    // (addBahanToTable/renderBahanRow/checkEmpty), cuma tanpa filter per
    // mesin (finishing tidak terikat divisi produksi seperti bahan baku).
    function addFinishingToTable() {
        const selectFinishing = document.getElementById('temp_id_finishing');
        const qtyInput = document.getElementById('temp_finishing_qty');

        if (!selectFinishing.value) {
            Swal.fire('Pilih Finishing!', 'Silahkan pilih finishing.', 'error');
            return;
        }

        const namaFinishing = selectFinishing.options[selectFinishing.selectedIndex].getAttribute('data-nama');
        const idFinishing = selectFinishing.value;
        const qty = parseFloat(qtyInput.value) || 1;

        renderFinishingRow(idFinishing, namaFinishing, qty);
        selectFinishing.value = "";
        qtyInput.value = "1";
    }

    function renderFinishingRow(id, nama, qty) {
        const tbody = document.getElementById('finishing_table_body');
        const emptyState = document.getElementById('empty_state_finishing');
        if (emptyState) emptyState.remove();

        const existing = tbody.querySelector(`input[value="${id}"][name="id_finishing[]"]`);
        if (existing) {
            Swal.fire('Info', 'Finishing sudah ada di list.', 'info');
            return;
        }

        const rowId = 'row_finishing_' + Math.random().toString(36).substr(2, 9);
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.className = "hover:bg-slate-50 border-b border-slate-50";

        tr.innerHTML = `
            <td class="p-4">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                    <input type="hidden" name="id_finishing[]" value="${id}">
                    <span class="font-bold">${nama}</span>
                </div>
            </td>
            <td class="p-4">
                <input type="hidden" name="finishing_qty[]" value="${qty}">
                <code class="bg-purple-50 text-purple-600 px-2 py-1 rounded text-[10px] font-black">${qty}</code>
            </td>
            <td class="p-4 text-center">
                <button type="button" onclick="document.getElementById('${rowId}').remove(); checkEmptyFinishing();" class="text-slate-300 hover:text-rose-500">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function checkEmptyFinishing() {
        const tbody = document.getElementById('finishing_table_body');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `<tr id="empty_state_finishing"><td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada finishing.</td></tr>`;
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function confirmToggleStatus(id, currentStatus, namaProduk) {
        const mauNonaktifkan = currentStatus === 'aktif';
        Swal.fire({
            title: mauNonaktifkan ? 'Nonaktifkan produk ini?' : 'Aktifkan kembali produk ini?',
            html: mauNonaktifkan
                ? `<b>${escapeHtml(namaProduk)}</b> tidak akan muncul lagi di pilihan produk saat membuat nota baru.<br><small class="text-slate-400">Riwayat transaksi lama tidak berubah, dan bisa diaktifkan lagi kapan saja.</small>`
                : `<b>${escapeHtml(namaProduk)}</b> akan muncul lagi di pilihan produk saat membuat nota baru.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: mauNonaktifkan ? '#64748b' : '#10b981',
            confirmButtonText: mauNonaktifkan ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) document.getElementById(`toggle-form-${id}`).submit();
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Produk?',
            html: `Menghapus <b>${escapeHtml(name)}</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById(`delete-form-${id}`).submit();
        });
    }
</script>
@endpush
@endsection
