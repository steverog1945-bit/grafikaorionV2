@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Master Data Jersey" subtitle="Pelanggan, desainer, tipe produk, kategori, ukuran & tier harga untuk modul Order Jersey.">
        <x-slot:actions>
            <x-button href="{{ route('jersey.orders.index') }}" variant="secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Order
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-6 font-semibold">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 font-semibold">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap gap-2 mb-6" id="jersey-master-tabs">
        @foreach(['pelanggan' => 'Pelanggan', 'desainer' => 'Desainer', 'tipe' => 'Tipe Produk', 'kategori' => 'Kategori', 'ukuran' => 'Ukuran', 'harga' => 'Tier Harga'] as $key => $label)
            <button type="button" onclick="switchJerseyTab('{{ $key }}')" id="tab-btn-{{ $key }}"
                class="jersey-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all {{ $loop->first ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-400 border border-slate-100' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- PELANGGAN --}}
    <div id="tab-panel-pelanggan" class="jersey-tab-panel">
        <x-card>
            <form action="{{ route('jersey.master.customers.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                @csrf
                <x-form.input type="text" name="name" placeholder="Nama pelanggan" required />
                <x-form.input type="text" name="phone" placeholder="No. HP" />
                <x-form.input type="text" name="address" placeholder="Alamat" />
                <x-button type="submit" variant="success">Tambah Pelanggan</x-button>
            </form>

            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">HP</th><th class="p-3 text-left">Alamat</th><th class="p-3 text-center w-28">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($customers as $c)
                    <tr>
                        <td class="p-3 font-semibold">{{ $c->name }}</td>
                        <td class="p-3">{{ $c->phone ?? '-' }}</td>
                        <td class="p-3 text-xs text-slate-400">{{ $c->address ?? '-' }}</td>
                        <td class="p-3 text-center">
                            <form action="{{ route('jersey.master.customers.toggle', $c->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $c->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><x-empty-state title="Belum ada pelanggan jersey" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- DESAINER --}}
    <div id="tab-panel-desainer" class="jersey-tab-panel hidden">
        <x-card>
            <form action="{{ route('jersey.master.designers.store') }}" method="POST" class="flex gap-3 mb-6 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                @csrf
                <div class="flex-1"><x-form.input type="text" name="name" placeholder="Nama desainer" required /></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center w-28">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($designers as $d)
                    <tr>
                        <td class="p-3 font-semibold">{{ $d->name }}</td>
                        <td class="p-3 text-center">
                            <form action="{{ route('jersey.master.designers.toggle', $d->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $d->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $d->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2"><x-empty-state title="Belum ada desainer" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- TIPE PRODUK --}}
    <div id="tab-panel-tipe" class="jersey-tab-panel hidden">
        <x-card>
            <form action="{{ route('jersey.master.product-types.store') }}" method="POST" class="flex gap-3 mb-6 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                @csrf
                <div class="flex-1"><x-form.input type="text" name="name" placeholder="Nama tipe produk (mis. Jersey, Jaket, Training)" required /></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center w-28">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($productTypes as $pt)
                    <tr>
                        <td class="p-3 font-semibold">{{ $pt->name }}</td>
                        <td class="p-3 text-center">
                            <form action="{{ route('jersey.master.product-types.toggle', $pt->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $pt->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $pt->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2"><x-empty-state title="Belum ada tipe produk" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- KATEGORI --}}
    <div id="tab-panel-kategori" class="jersey-tab-panel hidden">
        <x-card>
            <form action="{{ route('jersey.master.categories.store') }}" method="POST" class="flex gap-3 mb-6 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                @csrf
                <div class="flex-1"><x-form.input type="text" name="name" placeholder="Nama kategori (mis. Jersey Futsal)" required /></div>
                <x-button type="submit" variant="success">Tambah</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center w-28">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($categories as $cat)
                    <tr>
                        <td class="p-3 font-semibold">{{ $cat->name }}</td>
                        <td class="p-3 text-center">
                            <form action="{{ route('jersey.master.categories.toggle', $cat->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $cat->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2"><x-empty-state title="Belum ada kategori" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- UKURAN --}}
    <div id="tab-panel-ukuran" class="jersey-tab-panel hidden">
        <x-card>
            <form action="{{ route('jersey.master.sizes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                @csrf
                <x-form.input type="text" name="name" placeholder="Nama ukuran (mis. XXL)" required />
                <x-form.input type="number" name="sort_order" placeholder="Urutan tampil" />
                <x-form.input type="text" inputmode="numeric" name="price_surcharge" placeholder="Tambahan harga (Rp)" class="format-ribuan" />
                <x-button type="submit" variant="success">Tambah Ukuran</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-center">Urutan</th><th class="p-3 text-right">Tambahan Harga</th><th class="p-3 text-center w-28">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($sizes as $s)
                    <tr>
                        <td class="p-3 font-semibold">{{ $s->name }}</td>
                        <td class="p-3 text-center">{{ $s->sort_order }}</td>
                        <td class="p-3 text-right">Rp {{ number_format($s->price_surcharge, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">
                            <form action="{{ route('jersey.master.sizes.toggle', $s->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $s->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><x-empty-state title="Belum ada ukuran" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>

    {{-- TIER HARGA --}}
    <div id="tab-panel-harga" class="jersey-tab-panel hidden">
        <x-card>
            <p class="text-xs text-slate-400 mb-4">Kosongkan Tipe Produk/Kategori untuk berlaku sebagai tier umum ("berlaku untuk semua"). Pencarian harga otomatis memilih tier paling spesifik yang cocok dengan qty order.</p>
            <form action="{{ route('jersey.master.price-settings.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                @csrf
                <x-form.select name="jersey_product_type_id" class="md:col-span-1">
                    <option value="">-- Semua Tipe --</option>
                    @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                </x-form.select>
                <x-form.select name="jersey_category_id" class="md:col-span-1">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </x-form.select>
                <x-form.input type="number" name="qty_min" placeholder="Qty Min" required min="1" />
                <x-form.input type="number" name="qty_max" placeholder="Qty Max (~)" min="1" />
                <x-form.input type="text" inputmode="numeric" name="price" placeholder="Harga/pcs (Rp)" required class="format-ribuan" />
                <x-button type="submit" variant="success">Tambah Tier</x-button>
            </form>
            <x-table bare>
                <thead><tr><th class="p-3 text-left">Tipe</th><th class="p-3 text-left">Kategori</th><th class="p-3 text-center">Qty</th><th class="p-3 text-right">Harga/pcs</th><th class="p-3 text-center w-16">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($priceSettings as $p)
                    <tr>
                        <td class="p-3">{{ $p->productType->name ?? 'Semua' }}</td>
                        <td class="p-3">{{ $p->category->name ?? 'Semua' }}</td>
                        <td class="p-3 text-center">{{ $p->qty_min }} - {{ $p->qty_max ?? '∞' }}</td>
                        <td class="p-3 text-right font-bold">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">
                            <form action="{{ route('jersey.master.price-settings.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus tier harga ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada tier harga" description="Order jersey tetap bisa dibuat, harga akan Rp 0 sampai tier diisi." /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
    function switchJerseyTab(key) {
        document.querySelectorAll('.jersey-tab-panel').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-panel-' + key).classList.remove('hidden');
        document.querySelectorAll('.jersey-tab-btn').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-100');
            btn.classList.add('bg-white', 'text-slate-400', 'border', 'border-slate-100');
        });
        const activeBtn = document.getElementById('tab-btn-' + key);
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-100');
        activeBtn.classList.remove('bg-white', 'text-slate-400', 'border', 'border-slate-100');
    }
</script>
@endpush
@endsection
