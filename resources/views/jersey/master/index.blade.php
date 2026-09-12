@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Master Data Jersey" subtitle="Desainer, tipe produk, kategori, ukuran, bahan, estimasi bahan & tier harga untuk modul Order Jersey. Pelanggan diambil langsung dari Master Konsumen.">
        <x-slot:actions>
            <x-button href="{{ route('jersey.orders.index') }}" variant="secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Order
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="flex flex-wrap gap-2 mb-6" id="jersey-master-tabs">
        @foreach(['desainer' => 'Desainer', 'tipe' => 'Tipe Produk', 'kategori' => 'Kategori', 'ukuran' => 'Ukuran', 'bahan' => 'Bahan', 'estimasi' => 'Estimasi Bahan', 'harga' => 'Tier Harga'] as $key => $label)
            <button type="button" onclick="switchJerseyTab('{{ $key }}')" id="tab-btn-{{ $key }}"
                class="jersey-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all {{ $loop->first ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-white text-slate-400 border border-slate-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- DESAINER --}}
    <div id="tab-panel-desainer" class="jersey-tab-panel">
        <x-card>
            <form id="form-add-desainer" class="flex flex-col sm:flex-row gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama desainer" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20"></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-2.5 text-left">Nama</th><th class="p-2.5 text-center w-20">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-desainer" data-empty-colspan="2" data-empty-text="Belum ada desainer">
                    @forelse($designers as $d)
                        @include('jersey.master._row-simple', ['row' => $d, 'entity' => 'designers'])
                    @empty
                    <tr class="row-empty"><td colspan="2"><x-empty-state title="Belum ada desainer" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- TIPE PRODUK --}}
    <div id="tab-panel-tipe" class="jersey-tab-panel hidden">
        <x-card>
            <form id="form-add-tipe" class="flex flex-col sm:flex-row gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama tipe produk (mis. Jersey, Celana, Sublime Lainnya)" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20"></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-2.5 text-left">Nama</th><th class="p-2.5 text-center w-20">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-tipe" data-empty-colspan="2" data-empty-text="Belum ada tipe produk">
                    @forelse($productTypes as $pt)
                        @include('jersey.master._row-simple', ['row' => $pt, 'entity' => 'product-types'])
                    @empty
                    <tr class="row-empty"><td colspan="2"><x-empty-state title="Belum ada tipe produk" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- KATEGORI --}}
    <div id="tab-panel-kategori" class="jersey-tab-panel hidden">
        <x-card>
            <form id="form-add-kategori" class="flex flex-col sm:flex-row gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama kategori (mis. Jersey Futsal)" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20"></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-2.5 text-left">Nama</th><th class="p-2.5 text-center w-20">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-kategori" data-empty-colspan="2" data-empty-text="Belum ada kategori">
                    @forelse($categories as $cat)
                        @include('jersey.master._row-simple', ['row' => $cat, 'entity' => 'categories'])
                    @empty
                    <tr class="row-empty"><td colspan="2"><x-empty-state title="Belum ada kategori" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- UKURAN --}}
    <div id="tab-panel-ukuran" class="jersey-tab-panel hidden">
        <x-card>
            <form id="form-add-ukuran" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <input type="text" name="name" required placeholder="Nama ukuran (mis. XXL)" class="bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                <input type="number" name="sort_order" placeholder="Urutan tampil" class="bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                <input type="text" inputmode="numeric" name="price_surcharge" placeholder="Tambahan harga (Rp)" class="format-ribuan bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                <x-button type="submit" variant="success">Tambah Ukuran</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-2.5 text-left">Nama</th><th class="p-2.5 text-center">Urutan</th><th class="p-2.5 text-right">Tambahan Harga</th><th class="p-2.5 text-center w-20">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-ukuran" data-empty-colspan="4" data-empty-text="Belum ada ukuran">
                    @forelse($sizes as $s)
                        @include('jersey.master._row-size', ['row' => $s])
                    @empty
                    <tr class="row-empty"><td colspan="4"><x-empty-state title="Belum ada ukuran" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- BAHAN --}}
    <div id="tab-panel-bahan" class="jersey-tab-panel hidden">
        <x-card>
            <p class="text-xs text-slate-400 mb-4">Daftar bahan riil (mis. Dryfit, Cotton Combed) yang dipilih saat membuat baris barang di order - bukan lagi diketik bebas.</p>
            <form id="form-add-bahan" class="flex flex-col sm:flex-row gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama bahan (mis. Dryfit)" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20"></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-2.5 text-left">Nama</th><th class="p-2.5 text-center w-20">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-bahan" data-empty-colspan="2" data-empty-text="Belum ada bahan">
                    @forelse($materials as $m)
                        @include('jersey.master._row-simple', ['row' => $m, 'entity' => 'materials'])
                    @empty
                    <tr class="row-empty"><td colspan="2"><x-empty-state title="Belum ada bahan" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- ESTIMASI BAHAN (matriks ukuran x komponen) --}}
    <div id="tab-panel-estimasi" class="jersey-tab-panel hidden">
        <x-card>
            <p class="text-xs text-slate-400 mb-4">Estimasi kebutuhan kain (meter) per ukuran, per komponen (Body/Lengan/Rib/Celana, dst) - dipakai untuk menghitung otomatis "Spesifikasi Order" saat order dibuat. Murni estimasi, bukan pemotongan stok riil. Kosongkan sel yang belum diketahui angkanya.</p>
            <form id="form-add-komponen" class="flex flex-col sm:flex-row gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <div class="flex-1"><input type="text" name="name" required placeholder="Nama komponen baru (mis. Kerah)" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20"></div>
                <x-button type="submit" variant="success">Tambah Komponen</x-button>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse" id="tabel-estimasi">
                    <thead>
                        <tr>
                            <th class="p-2.5 text-left sticky left-0 bg-white z-10">Ukuran</th>
                            @foreach($components->where('is_active', true) as $comp)
                            <th class="p-2.5 text-center whitespace-nowrap" data-component-th="{{ $comp->id }}">
                                {{ $comp->name }} <span class="text-slate-300 font-normal">(m)</span>
                                <button type="button" onclick="nonaktifkanKomponen({{ $comp->id }})" class="ml-1 text-slate-300 hover:text-rose-500" title="Nonaktifkan komponen ini"><i class="fas fa-times-circle"></i></button>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="tbody-estimasi">
                        @foreach($sizes as $s)
                        <tr data-size-id="{{ $s->id }}">
                            <td class="p-2.5 font-semibold sticky left-0 bg-white">{{ $s->name }}</td>
                            @foreach($components->where('is_active', true) as $comp)
                            @php $nilaiUsage = optional(optional($usageMatrix[$s->id] ?? null)[$comp->id] ?? null)->usage_meter; @endphp
                            <td class="p-1" data-component-td="{{ $comp->id }}">
                                <input type="number" step="0.01" min="0" value="{{ $nilaiUsage }}"
                                    onchange="simpanEstimasi({{ $s->id }}, {{ $comp->id }}, this.value)"
                                    class="w-20 bg-slate-50 border-none rounded-lg p-2 text-xs text-center focus:ring-2 focus:ring-indigo-500/20">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    {{-- TIER HARGA --}}
    <div id="tab-panel-harga" class="jersey-tab-panel hidden">
        <x-card>
            <p class="text-xs text-slate-400 mb-4">Harga sekarang berpusat pada <b class="text-slate-500">Bahan</b> - tiap bahan bisa punya tier qty & harga sendiri karena selisih harga belanja antar bahan bisa sangat jauh. Kosongkan Bahan/Tipe Produk/Kategori untuk membuat tier "berlaku umum" (fallback kalau tidak ada tier spesifik yang cocok). Pencarian harga otomatis memilih tier paling spesifik yang cocok dengan bahan & qty tiap baris barang.</p>
            <form id="form-add-harga" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-1.5">Bahan</label>
                    <select name="jersey_material_id" id="harga-select-bahan" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-3 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">-- Semua Bahan --</option>
                        @foreach($materials->where('is_active', true) as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Tipe Produk</label>
                    <select name="jersey_product_type_id" id="harga-select-tipe" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-3 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">-- Semua Tipe --</option>
                        @foreach($productTypes->where('is_active', true) as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Kategori</label>
                    <select name="jersey_category_id" id="harga-select-kategori" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-3 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories->where('is_active', true) as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Qty Min</label>
                    <input type="number" name="qty_min" placeholder="1" required min="1" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Qty Max</label>
                    <input type="number" name="qty_max" placeholder="~ (kosongkan)" min="1" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Harga per Pcs (Rp)</label>
                    <input type="text" inputmode="numeric" name="price" placeholder="0" required class="format-ribuan w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div class="md:col-span-2 flex items-end">
                    <x-button type="submit" variant="success" class="w-full">
                        <i class="fas fa-plus"></i> Tambah Tier
                    </x-button>
                </div>
            </form>
            <x-table bare>
                <thead><tr><th class="p-2.5 text-left">Bahan</th><th class="p-2.5 text-left">Tipe</th><th class="p-2.5 text-left">Kategori</th><th class="p-2.5 text-center">Qty</th><th class="p-2.5 text-right">Harga/pcs</th><th class="p-2.5 text-center w-20">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50" id="tbody-harga" data-empty-colspan="6" data-empty-text="Belum ada tier harga">
                    @forelse($priceSettings as $p)
                        @include('jersey.master._row-harga', ['row' => $p])
                    @empty
                    <tr class="row-empty"><td colspan="6"><x-empty-state title="Belum ada tier harga" description="Order tetap bisa dibuat, harga akan Rp 0 sampai tier diisi." /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- MODAL EDIT (dipakai bersama untuk desainer/tipe/kategori/bahan - field-nya sama, cuma "nama") --}}
    <x-modal id="edit-simple" title="Ubah Data" maxWidth="sm">
        <form id="form-edit-simple" class="space-y-4">
            <input type="hidden" id="edit-simple-entity">
            <input type="hidden" id="edit-simple-id">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama</label>
                <input type="text" id="edit-simple-name" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('edit-simple')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-ukuran" title="Ubah Ukuran" maxWidth="sm">
        <form id="form-edit-ukuran" class="space-y-4">
            <input type="hidden" id="edit-ukuran-id">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama</label>
                <input type="text" id="edit-ukuran-name" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Urutan Tampil</label>
                <input type="number" id="edit-ukuran-sort" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tambahan Harga (Rp)</label>
                <input type="text" inputmode="numeric" id="edit-ukuran-surcharge" class="format-ribuan w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('edit-ukuran')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-harga" title="Ubah Tier Harga" maxWidth="sm">
        <form id="form-edit-harga" class="space-y-4">
            <input type="hidden" id="edit-harga-id">
            <div>
                <label class="block text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-2">Bahan</label>
                <select id="edit-harga-bahan" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    <option value="">-- Semua Bahan --</option>
                    @foreach($materials->where('is_active', true) as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe Produk</label>
                    <select id="edit-harga-tipe" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        <option value="">-- Semua --</option>
                        @foreach($productTypes->where('is_active', true) as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
                    <select id="edit-harga-kategori" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        <option value="">-- Semua --</option>
                        @foreach($categories->where('is_active', true) as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Qty Min</label>
                    <input type="number" id="edit-harga-qty-min" min="1" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Qty Max</label>
                    <input type="number" id="edit-harga-qty-max" min="1" placeholder="~ (kosongkan)" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Harga per Pcs (Rp)</label>
                <input type="text" inputmode="numeric" id="edit-harga-price" required class="format-ribuan w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('edit-harga')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold uppercase">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase">Simpan</button>
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
            btn.classList.remove('bg-indigo-50', 'text-indigo-600', 'border-indigo-100');
            btn.classList.add('bg-white', 'text-slate-400', 'border-slate-200');
        });
        const activeBtn = document.getElementById('tab-btn-' + key);
        activeBtn.classList.add('bg-indigo-50', 'text-indigo-600', 'border-indigo-100');
        activeBtn.classList.remove('bg-white', 'text-slate-400', 'border-slate-200');
    }

    const csrfTokenJerseyMaster = document.querySelector('meta[name="csrf-token"]').content;

    // Toast mengambang (tidak mendorong tabel naik-turun seperti sebelumnya)
    // - pola sama dengan Toast yang sudah dipakai di Kasir/Penjualan.
    const ToastJersey = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true });
    function tampilkanToastJersey(message, sukses = true) {
        ToastJersey.fire({ icon: sukses ? 'success' : 'error', title: message });
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

    function escapeHtmlJersey(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // ---- Entitas sederhana (desainer, tipe produk, kategori, bahan) - satu
    // fungsi dipakai untuk semuanya karena strukturnya identik. Baris pakai
    // opacity saat nonaktif + tombol ikon (edit pensil, toggle mata) -
    // konsisten dengan pola Master Bahan Baku yang sudah ada di aplikasi,
    // BUKAN badge status berwarna terpisah (makan tempat & beda gaya). ----
    const entityConfig = {
        designers: { tbody: 'tbody-desainer', form: 'form-add-desainer', routeBase: '{{ url("/jersey/master/designers") }}' },
        'product-types': { tbody: 'tbody-tipe', form: 'form-add-tipe', routeBase: '{{ url("/jersey/master/product-types") }}', selectTarget: 'harga-select-tipe' },
        categories: { tbody: 'tbody-kategori', form: 'form-add-kategori', routeBase: '{{ url("/jersey/master/categories") }}', selectTarget: 'harga-select-kategori' },
        materials: { tbody: 'tbody-bahan', form: 'form-add-bahan', routeBase: '{{ url("/jersey/master/materials") }}', selectTarget: 'harga-select-bahan' },
    };

    function baseRowHtml(entity, row) {
        return `
        <tr data-id="${row.id}" class="${row.is_active ? '' : 'opacity-50'}">
            <td class="p-2.5 font-semibold">${escapeHtmlJersey(row.name)}</td>
            <td class="p-2.5 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick="bukaEditSimple('${entity}', ${row.id}, ${JSON.stringify(row.name).replace(/"/g, '&quot;')})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Ubah"><i class="far fa-edit text-xs"></i></button>
                    <button type="button" onclick="toggleEntitas('${entity}', ${row.id})" class="w-8 h-8 rounded-lg text-slate-400 ${row.is_active ? 'hover:text-slate-600 hover:bg-slate-100' : 'hover:text-emerald-500 hover:bg-emerald-50'}" title="${row.is_active ? 'Nonaktifkan' : 'Aktifkan'}"><i class="far ${row.is_active ? 'fa-eye-slash' : 'fa-eye'} text-xs"></i></button>
                </div>
            </td>
        </tr>`;
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

            // Tipe Produk/Kategori baru langsung muncul di dropdown tier
            // harga TANPA reload - sebelumnya harus reload dulu baru
            // kelihatan (keluhan langsung dari user).
            if (cfg.selectTarget) {
                document.getElementById(cfg.selectTarget)
                    .insertAdjacentHTML('beforeend', `<option value="${data.row.id}">${escapeHtmlJersey(data.row.name)}</option>`);
            }
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
        if (cfg.selectTarget) {
            const opt = document.querySelector(`#${cfg.selectTarget} option[value="${id}"]`);
            if (opt) opt.textContent = data.row.name;
        }
        closeModal('edit-simple');
        tampilkanToastJersey(data.message);
    });

    async function toggleEntitas(entity, id) {
        const cfg = entityConfig[entity];
        const data = await kirimJson(`${cfg.routeBase}/${id}/toggle`, 'PATCH', {});
        if (!data) return;
        const row = document.querySelector(`#${cfg.tbody} tr[data-id="${id}"]`);
        if (row) row.outerHTML = baseRowHtml(entity, data.row);
        // Tipe/Kategori yang dinonaktifkan langsung hilang dari pilihan tier
        // harga (sebelumnya tetap muncul walau sudah nonaktif - keluhan
        // langsung dari user), yang diaktifkan lagi langsung muncul lagi.
        if (cfg.selectTarget) {
            const existing = document.querySelector(`#${cfg.selectTarget} option[value="${id}"]`);
            if (data.row.is_active && !existing) {
                document.getElementById(cfg.selectTarget).insertAdjacentHTML('beforeend', `<option value="${id}">${escapeHtmlJersey(data.row.name)}</option>`);
            } else if (!data.row.is_active && existing) {
                existing.remove();
            }
        }
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
        <tr data-id="${row.id}" class="${row.is_active ? '' : 'opacity-50'}">
            <td class="p-2.5 font-semibold">${escapeHtmlJersey(row.name)}</td>
            <td class="p-2.5 text-center">${row.sort_order}</td>
            <td class="p-2.5 text-right">Rp ${formatRupiah(Math.round(parseFloat(row.price_surcharge)))}</td>
            <td class="p-2.5 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick="bukaEditUkuran(${JSON.stringify(row).replace(/"/g, '&quot;')})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Ubah"><i class="far fa-edit text-xs"></i></button>
                    <button type="button" onclick="toggleUkuran(${row.id})" class="w-8 h-8 rounded-lg text-slate-400 ${row.is_active ? 'hover:text-slate-600 hover:bg-slate-100' : 'hover:text-emerald-500 hover:bg-emerald-50'}" title="${row.is_active ? 'Nonaktifkan' : 'Aktifkan'}"><i class="far ${row.is_active ? 'fa-eye-slash' : 'fa-eye'} text-xs"></i></button>
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
        if (row) row.outerHTML = baseRowSizeHtml(data.row);
        tampilkanToastJersey(data.message);
    }

    // ---- Estimasi Bahan (matriks ukuran x komponen) ----
    document.getElementById('form-add-komponen').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const name = form.querySelector('[name="name"]').value.trim();
        if (!name) return;
        const data = await kirimJson('{{ url("/jersey/master/components") }}', 'POST', { name });
        if (!data) return;
        form.reset();
        tampilkanToastJersey(data.message);

        // Tambah kolom baru ke tabel matriks TANPA reload - satu <th> di
        // header + satu <td> berisi input di tiap baris ukuran yang sudah ada.
        const table = document.getElementById('tabel-estimasi');
        const headRow = table.querySelector('thead tr');
        headRow.insertAdjacentHTML('beforeend', `
            <th class="p-2.5 text-center whitespace-nowrap" data-component-th="${data.row.id}">
                ${escapeHtmlJersey(data.row.name)} <span class="text-slate-300 font-normal">(m)</span>
                <button type="button" onclick="nonaktifkanKomponen(${data.row.id})" class="ml-1 text-slate-300 hover:text-rose-500" title="Nonaktifkan komponen ini"><i class="fas fa-times-circle"></i></button>
            </th>`);
        document.querySelectorAll('#tbody-estimasi tr').forEach(row => {
            const sizeId = row.dataset.sizeId;
            row.insertAdjacentHTML('beforeend', `
                <td class="p-1" data-component-td="${data.row.id}">
                    <input type="number" step="0.01" min="0" value=""
                        onchange="simpanEstimasi(${sizeId}, ${data.row.id}, this.value)"
                        class="w-20 bg-slate-50 border-none rounded-lg p-2 text-xs text-center focus:ring-2 focus:ring-indigo-500/20">
                </td>`);
        });
    });

    async function nonaktifkanKomponen(id) {
        if (!confirm('Nonaktifkan komponen ini? Kolomnya akan hilang dari matriks (data tersimpan, bisa dikembalikan lewat database bila perlu).')) return;
        const data = await kirimJson(`{{ url("/jersey/master/components") }}/${id}/toggle`, 'PATCH', {});
        if (!data) return;
        document.querySelector(`[data-component-th="${id}"]`)?.remove();
        document.querySelectorAll(`[data-component-td="${id}"]`).forEach(td => td.remove());
        tampilkanToastJersey(data.message);
    }

    let simpanEstimasiTimer = null;
    function simpanEstimasi(sizeId, componentId, value) {
        clearTimeout(simpanEstimasiTimer);
        simpanEstimasiTimer = setTimeout(async () => {
            const data = await kirimJson('{{ url("/jersey/master/usage") }}', 'POST', {
                jersey_size_id: sizeId,
                jersey_component_id: componentId,
                usage_meter: value === '' ? null : value,
            });
            if (data) tampilkanToastJersey(data.message);
        }, 400);
    }

    // ---- Tier Harga ----
    document.getElementById('form-add-harga').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = {
            jersey_material_id: form.querySelector('[name="jersey_material_id"]').value || null,
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
            <td class="p-2.5 font-semibold text-indigo-600">${row.material ? escapeHtmlJersey(row.material.name) : 'Semua Bahan'}</td>
            <td class="p-2.5">${row.product_type ? escapeHtmlJersey(row.product_type.name) : 'Semua'}</td>
            <td class="p-2.5">${row.category ? escapeHtmlJersey(row.category.name) : 'Semua'}</td>
            <td class="p-2.5 text-center">${row.qty_min} - ${row.qty_max ?? '∞'}</td>
            <td class="p-2.5 text-right font-bold">Rp ${formatRupiah(Math.round(parseFloat(row.price)))}</td>
            <td class="p-2.5 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick="bukaEditHarga(${JSON.stringify(row).replace(/"/g, '&quot;')})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Ubah"><i class="far fa-edit text-xs"></i></button>
                    <button type="button" onclick="hapusHarga(${row.id})" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50" title="Hapus"><i class="far fa-trash-alt text-xs"></i></button>
                </div>
            </td>
        </tr>`;
    }

    function bukaEditHarga(row) {
        document.getElementById('edit-harga-id').value = row.id;
        document.getElementById('edit-harga-bahan').value = row.jersey_material_id ?? '';
        document.getElementById('edit-harga-tipe').value = row.jersey_product_type_id ?? '';
        document.getElementById('edit-harga-kategori').value = row.jersey_category_id ?? '';
        document.getElementById('edit-harga-qty-min').value = row.qty_min;
        document.getElementById('edit-harga-qty-max').value = row.qty_max ?? '';
        document.getElementById('edit-harga-price').value = formatRupiah(Math.round(parseFloat(row.price)));
        openModal('edit-harga');
    }

    document.getElementById('form-edit-harga').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-harga-id').value;
        const payload = {
            jersey_material_id: document.getElementById('edit-harga-bahan').value || null,
            jersey_product_type_id: document.getElementById('edit-harga-tipe').value || null,
            jersey_category_id: document.getElementById('edit-harga-kategori').value || null,
            qty_min: document.getElementById('edit-harga-qty-min').value,
            qty_max: document.getElementById('edit-harga-qty-max').value || null,
            price: cleanRupiah(document.getElementById('edit-harga-price').value),
        };
        const data = await kirimJson(`{{ url("/jersey/master/price-settings") }}/${id}`, 'PUT', payload);
        if (!data) return;
        const row = document.querySelector(`#tbody-harga tr[data-id="${id}"]`);
        if (row) row.outerHTML = rowHargaHtml(data.row);
        closeModal('edit-harga');
        tampilkanToastJersey(data.message);
    });

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
