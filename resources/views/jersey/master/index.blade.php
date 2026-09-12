@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Master Data Jersey" subtitle="Desainer, tipe produk, kategori, ukuran & tier harga untuk modul Order Jersey. Pelanggan diambil langsung dari Master Konsumen.">
        <x-slot:actions>
            <x-button href="{{ route('jersey.orders.index') }}" variant="secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Order
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div id="jersey-toast" class="hidden mb-6 p-4 rounded-2xl font-semibold"></div>

    <div class="flex flex-wrap gap-2 mb-6" id="jersey-master-tabs">
        @foreach(['desainer' => 'Desainer', 'tipe' => 'Tipe Produk', 'kategori' => 'Kategori', 'ukuran' => 'Ukuran', 'harga' => 'Tier Harga'] as $key => $label)
            <button type="button" onclick="switchJerseyTab('{{ $key }}')" id="tab-btn-{{ $key }}"
                class="jersey-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all {{ $loop->first ? 'bg-slate-800 text-white shadow-lg shadow-slate-200' : 'bg-white text-slate-400 border border-slate-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- DESAINER --}}
    <div id="tab-panel-desainer" class="jersey-tab-panel">
        <x-card>
            <form id="form-add-desainer" class="flex gap-3 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama desainer" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30"></div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-widest">Tambah</button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center w-40">Status</th><th class="p-3 text-center w-16">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-desainer" data-empty-colspan="3" data-empty-text="Belum ada desainer">
                    @forelse($designers as $d)
                        @include('jersey.master._row-simple', ['row' => $d, 'entity' => 'designers'])
                    @empty
                    <tr class="row-empty"><td colspan="3"><x-empty-state title="Belum ada desainer" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- TIPE PRODUK --}}
    <div id="tab-panel-tipe" class="jersey-tab-panel hidden">
        <x-card>
            <form id="form-add-tipe" class="flex gap-3 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama tipe produk (mis. Jersey, Celana, Sublime Lainnya)" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30"></div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-widest">Tambah</button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center w-40">Status</th><th class="p-3 text-center w-16">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-tipe" data-empty-colspan="3" data-empty-text="Belum ada tipe produk">
                    @forelse($productTypes as $pt)
                        @include('jersey.master._row-simple', ['row' => $pt, 'entity' => 'product-types'])
                    @empty
                    <tr class="row-empty"><td colspan="3"><x-empty-state title="Belum ada tipe produk" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- KATEGORI --}}
    <div id="tab-panel-kategori" class="jersey-tab-panel hidden">
        <x-card>
            <form id="form-add-kategori" class="flex gap-3 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama kategori (mis. Jersey Futsal)" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30"></div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-widest">Tambah</button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center w-40">Status</th><th class="p-3 text-center w-16">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-kategori" data-empty-colspan="3" data-empty-text="Belum ada kategori">
                    @forelse($categories as $cat)
                        @include('jersey.master._row-simple', ['row' => $cat, 'entity' => 'categories'])
                    @empty
                    <tr class="row-empty"><td colspan="3"><x-empty-state title="Belum ada kategori" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- UKURAN --}}
    <div id="tab-panel-ukuran" class="jersey-tab-panel hidden">
        <x-card>
            <form id="form-add-ukuran" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <input type="text" name="name" required placeholder="Nama ukuran (mis. XXL)" class="bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                <input type="number" name="sort_order" placeholder="Urutan tampil" class="bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                <input type="text" inputmode="numeric" name="price_surcharge" placeholder="Tambahan harga (Rp)" class="format-ribuan bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-widest">Tambah Ukuran</button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center">Urutan</th><th class="p-3 text-right">Tambahan Harga</th><th class="p-3 text-center w-40">Status</th><th class="p-3 text-center w-16">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-ukuran" data-empty-colspan="5" data-empty-text="Belum ada ukuran">
                    @forelse($sizes as $s)
                        @include('jersey.master._row-size', ['row' => $s])
                    @empty
                    <tr class="row-empty"><td colspan="5"><x-empty-state title="Belum ada ukuran" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- TIER HARGA --}}
    <div id="tab-panel-harga" class="jersey-tab-panel hidden">
        <x-card>
            <p class="text-xs text-slate-400 mb-4">Kosongkan Tipe Produk/Kategori untuk berlaku sebagai tier umum ("berlaku untuk semua"). Pencarian harga otomatis memilih tier paling spesifik yang cocok dengan qty tiap baris barang.</p>
            <form id="form-add-harga" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <select name="jersey_product_type_id" class="md:col-span-1 bg-white border border-slate-200 rounded-xl py-3 px-3 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                    <option value="">-- Semua Tipe --</option>
                    @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                </select>
                <select name="jersey_category_id" class="md:col-span-1 bg-white border border-slate-200 rounded-xl py-3 px-3 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
                <input type="number" name="qty_min" placeholder="Qty Min" required min="1" class="bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                <input type="number" name="qty_max" placeholder="Qty Max (~)" min="1" class="bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                <input type="text" inputmode="numeric" name="price" placeholder="Harga/pcs (Rp)" required class="format-ribuan bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30">
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-widest">Tambah Tier</button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Tipe</th><th class="p-3 text-left">Kategori</th><th class="p-3 text-center">Qty</th><th class="p-3 text-right">Harga/pcs</th><th class="p-3 text-center w-16">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-harga" data-empty-colspan="5" data-empty-text="Belum ada tier harga">
                    @forelse($priceSettings as $p)
                        @include('jersey.master._row-harga', ['row' => $p])
                    @empty
                    <tr class="row-empty"><td colspan="5"><x-empty-state title="Belum ada tier harga" description="Order tetap bisa dibuat, harga akan Rp 0 sampai tier diisi." /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- MODAL EDIT (dipakai bersama untuk desainer/tipe/kategori - field-nya sama, cuma "nama") --}}
    <x-modal id="edit-simple" title="Ubah Data" maxWidth="sm">
        <form id="form-edit-simple" class="space-y-4">
            <input type="hidden" id="edit-simple-entity">
            <input type="hidden" id="edit-simple-id">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama</label>
                <input type="text" id="edit-simple-name" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('edit-simple')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-ukuran" title="Ubah Ukuran" maxWidth="sm">
        <form id="form-edit-ukuran" class="space-y-4">
            <input type="hidden" id="edit-ukuran-id">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama</label>
                <input type="text" id="edit-ukuran-name" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Urutan Tampil</label>
                <input type="number" id="edit-ukuran-sort" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tambahan Harga (Rp)</label>
                <input type="text" inputmode="numeric" id="edit-ukuran-surcharge" class="format-ribuan w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-slate-400/30 shadow-inner">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('edit-ukuran')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase">Simpan</button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
    function switchJerseyTab(key) {
        document.querySelectorAll('.jersey-tab-panel').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-panel-' + key).classList.remove('hidden');
        document.querySelectorAll('.jersey-tab-btn').forEach(btn => {
            btn.classList.remove('bg-slate-800', 'text-white', 'shadow-lg', 'shadow-slate-200');
            btn.classList.add('bg-white', 'text-slate-400', 'border', 'border-slate-200');
        });
        const activeBtn = document.getElementById('tab-btn-' + key);
        activeBtn.classList.add('bg-slate-800', 'text-white', 'shadow-lg', 'shadow-slate-200');
        activeBtn.classList.remove('bg-white', 'text-slate-400', 'border', 'border-slate-200');
    }

    const csrfTokenJerseyMaster = document.querySelector('meta[name="csrf-token"]').content;

    function tampilkanToastJersey(message, sukses = true) {
        const el = document.getElementById('jersey-toast');
        el.textContent = message;
        el.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-600', 'bg-rose-50', 'text-rose-600');
        el.classList.add(sukses ? 'bg-emerald-50' : 'bg-rose-50', sukses ? 'text-emerald-600' : 'text-rose-600');
        setTimeout(() => el.classList.add('hidden'), 3500);
    }

    async function kirimJson(url, method, payload) {
        const res = await fetch(url, {
            method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfTokenJerseyMaster },
            body: JSON.stringify(payload),
        });
        const data = await res.json().catch(() => null);
        if (!res.ok || !data || data.success === false) {
            tampilkanToastJersey((data && data.message) || 'Terjadi kesalahan.', false);
            return null;
        }
        return data;
    }

    function hapusBarisKosong(tbodyId) {
        const tbody = document.getElementById(tbodyId);
        const empty = tbody.querySelector('.row-empty');
        if (empty) empty.remove();
    }

    function badgeStatus(isActive) {
        return isActive
            ? '<span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase bg-slate-100 text-slate-700">Aktif</span>'
            : '<span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase bg-slate-50 text-slate-300 border border-slate-100">Nonaktif</span>';
    }

    // ---- Entitas sederhana (desainer, tipe produk, kategori) - satu fungsi
    // dipakai untuk ketiganya karena strukturnya identik (cuma nama+status). ----
    const entityConfig = {
        designers: { tbody: 'tbody-desainer', form: 'form-add-desainer', routeBase: '{{ url("/jersey/master/designers") }}' },
        'product-types': { tbody: 'tbody-tipe', form: 'form-add-tipe', routeBase: '{{ url("/jersey/master/product-types") }}' },
        categories: { tbody: 'tbody-kategori', form: 'form-add-kategori', routeBase: '{{ url("/jersey/master/categories") }}' },
    };

    function baseRowHtml(entity, row) {
        return `
        <tr data-id="${row.id}">
            <td class="p-3 font-semibold">${escapeHtmlJersey(row.name)}</td>
            <td class="p-3 text-center" data-status>${badgeStatus(row.is_active)}</td>
            <td class="p-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick='bukaEditSimple("${entity}", ${row.id}, ${JSON.stringify(row.name)})' class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100" title="Ubah"><i class="fas fa-pen text-xs"></i></button>
                    <button type="button" onclick="toggleEntitas('${entity}', ${row.id})" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100" title="Aktif/Nonaktif"><i class="fas fa-power-off text-xs"></i></button>
                </div>
            </td>
        </tr>`;
    }

    function escapeHtmlJersey(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    Object.entries(entityConfig).forEach(([entity, cfg]) => {
        document.getElementById(cfg.form).addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const name = form.querySelector('[name="name"]').value.trim();
            if (!name) return;
            const data = await kirimJson(cfg.routeBase, 'POST', { name });
            if (!data) return;
            hapusBarisKosong(cfg.tbody);
            document.getElementById(cfg.tbody).insertAdjacentHTML('beforeend', baseRowHtml(entity, data.row));
            form.reset();
            tampilkanToastJersey(data.message);
        });
    });

    function bukaEditSimple(entity, id, name) {
        document.getElementById('edit-simple-entity').value = entity;
        document.getElementById('edit-simple-id').value = id;
        document.getElementById('edit-simple-name').value = name;
        openModal('edit-simple');
    }

    document.getElementById('form-edit-simple').addEventListener('submit', async (e) => {
        e.preventDefault();
        const entity = document.getElementById('edit-simple-entity').value;
        const id = document.getElementById('edit-simple-id').value;
        const name = document.getElementById('edit-simple-name').value.trim();
        const cfg = entityConfig[entity];
        const data = await kirimJson(`${cfg.routeBase}/${id}`, 'PUT', { name });
        if (!data) return;
        const row = document.querySelector(`#${cfg.tbody} tr[data-id="${id}"]`);
        if (row) row.querySelector('td').textContent = data.row.name;
        closeModal('edit-simple');
        tampilkanToastJersey(data.message);
    });

    async function toggleEntitas(entity, id) {
        const cfg = entityConfig[entity];
        const data = await kirimJson(`${cfg.routeBase}/${id}/toggle`, 'PATCH', {});
        if (!data) return;
        const row = document.querySelector(`#${cfg.tbody} tr[data-id="${id}"]`);
        if (row) row.querySelector('[data-status]').innerHTML = badgeStatus(data.row.is_active);
        tampilkanToastJersey(data.message);
    }

    // ---- Ukuran ----
    document.getElementById('form-add-ukuran').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = {
            name: form.querySelector('[name="name"]').value.trim(),
            sort_order: form.querySelector('[name="sort_order"]').value || 0,
            price_surcharge: cleanRupiah(form.querySelector('[name="price_surcharge"]').value),
        };
        if (!payload.name) return;
        const data = await kirimJson('{{ url("/jersey/master/sizes") }}', 'POST', payload);
        if (!data) return;
        hapusBarisKosong('tbody-ukuran');
        document.getElementById('tbody-ukuran').insertAdjacentHTML('beforeend', baseRowSizeHtml(data.row));
        form.reset();
        tampilkanToastJersey(data.message);
    });

    function baseRowSizeHtml(row) {
        return `
        <tr data-id="${row.id}">
            <td class="p-3 font-semibold">${escapeHtmlJersey(row.name)}</td>
            <td class="p-3 text-center">${row.sort_order}</td>
            <td class="p-3 text-right" data-surcharge>Rp ${formatRupiah(Math.round(parseFloat(row.price_surcharge)))}</td>
            <td class="p-3 text-center" data-status>${badgeStatus(row.is_active)}</td>
            <td class="p-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick='bukaEditUkuran(${JSON.stringify(row)})' class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100" title="Ubah"><i class="fas fa-pen text-xs"></i></button>
                    <button type="button" onclick="toggleUkuran(${row.id})" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100" title="Aktif/Nonaktif"><i class="fas fa-power-off text-xs"></i></button>
                </div>
            </td>
        </tr>`;
    }

    function bukaEditUkuran(row) {
        document.getElementById('edit-ukuran-id').value = row.id;
        document.getElementById('edit-ukuran-name').value = row.name;
        document.getElementById('edit-ukuran-sort').value = row.sort_order;
        document.getElementById('edit-ukuran-surcharge').value = formatRupiah(Math.round(parseFloat(row.price_surcharge)));
        openModal('edit-ukuran');
    }

    document.getElementById('form-edit-ukuran').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-ukuran-id').value;
        const payload = {
            name: document.getElementById('edit-ukuran-name').value.trim(),
            sort_order: document.getElementById('edit-ukuran-sort').value || 0,
            price_surcharge: cleanRupiah(document.getElementById('edit-ukuran-surcharge').value),
        };
        const data = await kirimJson(`{{ url("/jersey/master/sizes") }}/${id}`, 'PUT', payload);
        if (!data) return;
        const row = document.querySelector(`#tbody-ukuran tr[data-id="${id}"]`);
        if (row) row.outerHTML = baseRowSizeHtml(data.row);
        closeModal('edit-ukuran');
        tampilkanToastJersey(data.message);
    });

    async function toggleUkuran(id) {
        const data = await kirimJson(`{{ url("/jersey/master/sizes") }}/${id}/toggle`, 'PATCH', {});
        if (!data) return;
        const row = document.querySelector(`#tbody-ukuran tr[data-id="${id}"]`);
        if (row) row.querySelector('[data-status]').innerHTML = badgeStatus(data.row.is_active);
        tampilkanToastJersey(data.message);
    }

    // ---- Tier Harga ----
    document.getElementById('form-add-harga').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = {
            jersey_product_type_id: form.querySelector('[name="jersey_product_type_id"]').value || null,
            jersey_category_id: form.querySelector('[name="jersey_category_id"]').value || null,
            qty_min: form.querySelector('[name="qty_min"]').value,
            qty_max: form.querySelector('[name="qty_max"]').value || null,
            price: cleanRupiah(form.querySelector('[name="price"]').value),
        };
        const data = await kirimJson('{{ url("/jersey/master/price-settings") }}', 'POST', payload);
        if (!data) return;
        hapusBarisKosong('tbody-harga');
        document.getElementById('tbody-harga').insertAdjacentHTML('beforeend', rowHargaHtml(data.row));
        form.reset();
        tampilkanToastJersey(data.message);
    });

    function rowHargaHtml(row) {
        return `
        <tr data-id="${row.id}">
            <td class="p-3">${row.product_type ? escapeHtmlJersey(row.product_type.name) : 'Semua'}</td>
            <td class="p-3">${row.category ? escapeHtmlJersey(row.category.name) : 'Semua'}</td>
            <td class="p-3 text-center">${row.qty_min} - ${row.qty_max ?? '∞'}</td>
            <td class="p-3 text-right font-bold">Rp ${formatRupiah(Math.round(parseFloat(row.price)))}</td>
            <td class="p-3 text-center">
                <button type="button" onclick="hapusHarga(${row.id})" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
            </td>
        </tr>`;
    }

    async function hapusHarga(id) {
        if (!confirm('Hapus tier harga ini?')) return;
        const data = await kirimJson(`{{ url("/jersey/master/price-settings") }}/${id}`, 'DELETE', {});
        if (!data) return;
        document.querySelector(`#tbody-harga tr[data-id="${id}"]`)?.remove();
        tampilkanToastJersey(data.message);
    }
</script>
@endpush
@endsection
