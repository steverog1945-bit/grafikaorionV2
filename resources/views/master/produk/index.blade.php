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

            @if(request()->anyFilled(['search', 'category']))
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
            <tr class="group hover:bg-indigo-50/40 transition-all">
                <td class="px-8 py-5">
                    <div class="font-bold text-slate-800 text-[14px]">{{ $p->nama_produk }}</div>
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

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                {{-- LEFT: Spesifikasi Produk --}}
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 space-y-6">
                        <p class="text-[11px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Info Produk
                        </p>

                        <x-form.group label="Nama Produk">
                            <x-form.input type="text" name="nama_produk" id="input_nama_produk" required class="!bg-white !shadow-sm" />
                        </x-form.group>

                        <x-form.group label="Mesin / Divisi Produksi">
                            <x-form.select name="id_mesin" id="input_id_mesin" onchange="filterBahanByMesin(this.value)" class="!bg-white !shadow-sm !text-indigo-600">
                                <option value="">-- Pilih Mesin --</option>
                                @foreach($mesin as $m)
                                    <option value="{{ $m->id_mesin }}">{{ $m->nama_mesin }}</option>
                                @endforeach
                            </x-form.select>
                        </x-form.group>

                        <div class="grid grid-cols-2 gap-4">
                            <x-form.group label="Metode Hitung">
                                <x-form.select name="metode_hitung" onchange="toggleMinOrderFields()" class="!bg-white !shadow-sm !py-3">
                                    <option value="ukuran">Ukuran (m²)</option>
                                    <option value="quantity">Satuan (Pcs)</option>
                                </x-form.select>
                            </x-form.group>
                            <x-form.group label="Barcode">
                                <x-form.input type="text" name="barcode" placeholder="ID-001" class="!bg-white !shadow-sm !font-mono !py-3" />
                            </x-form.group>
                        </div>

                        <div id="field_min_ukuran" class="grid grid-cols-2 gap-4">
                            <x-form.group label="Min Panjang (cm)">
                                <x-form.input type="number" name="min_p" step="0.01" min="0" placeholder="0" class="!bg-white !shadow-sm !py-3" />
                            </x-form.group>
                            <x-form.group label="Min Lebar (cm)">
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
                </div>

                {{-- RIGHT: Multi-BOM (Bahan Baku) --}}
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                        <p class="text-[11px] font-black text-emerald-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <i class="fas fa-boxes"></i> Komposisi Bahan Baku (BOM)
                        </p>

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
                                <label class="block text-[9px] font-black text-emerald-700 uppercase mb-2">Rumus Pemakaian</label>
                                <select id="temp_rumus" class="w-full border-none rounded-xl py-3 px-4 text-xs font-bold shadow-sm">
                                    <option value="(P*L)/10000">Luas (m²) - [P*L/10000]</option>
                                    <option value="Q">Quantity (1:1) - [Satuan]</option>
                                    <option value="2*(P+L)/100">Keliling (m) - [2*(P+L)/100]</option>
                                    <option value="(P*L)/10000*2">Bolak Balik (x2)</option>
                                </select>
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <x-button type="button" variant="success" onclick="addBahanToTable()" class="w-full">
                                    <i class="fas fa-plus"></i> Tambah Ke List
                                </x-button>
                            </div>
                        </div>

                        {{-- Tabel BOM: pakai bare karena sudah di dalam panel dashed di atas --}}
                        <x-table bare>
                            <thead>
                                <tr>
                                    <th class="p-4 text-left">Bahan Baku Penyusun</th>
                                    <th class="p-4 text-left">Logika Potong Stok</th>
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

                    <div class="flex gap-4">
                        <x-button type="button" variant="secondary" onclick="closeProdukModal()" class="flex-1">Batal</x-button>
                        <x-button type="submit" class="flex-[2]">Simpan Master Produk</x-button>
                    </div>
                </div>
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

        openModal('produk');
    }

    // Tampilkan input Min Order sesuai metode_hitung: Panjang/Lebar (m²) untuk
    // 'ukuran', atau Qty (Pcs) untuk 'quantity' - keduanya disimpan di kolom
    // berbeda (min_panjang/min_lebar vs min_order) jadi hanya satu yang relevan
    // untuk diisi tergantung metode yang dipilih.
    function toggleMinOrderFields() {
        const method = document.querySelector('select[name="metode_hitung"]').value;
        document.getElementById('field_min_ukuran').classList.toggle('hidden', method !== 'ukuran');
        document.getElementById('field_min_qty').classList.toggle('hidden', method === 'ukuran');
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
            const response = await fetch(`/produk/${id}`);
            const data = await response.json();

            const form = document.getElementById('formProduk');
            form.action = `/produk/${id}`;
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
            toggleMinOrderFields();

            const tbody = document.getElementById('bom_table_body');
            tbody.innerHTML = '';

            if (data.bom && data.bom.length > 0) {
                data.bom.forEach(item => {
                    renderBahanRow(item.id_bahan, item.nama_bahan, item.cara_hitung_pemakaian, item.satuan_stok);
                });
            } else {
                tbody.innerHTML = '<tr id="empty_state"><td colspan="3" class="p-8 text-center text-slate-300 italic">Belum ada bahan baku.</td></tr>';
            }

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

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Produk?',
            html: `Menghapus <b>${name}</b>?`,
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
