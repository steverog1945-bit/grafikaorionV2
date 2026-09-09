@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
                Master <span class="text-indigo-600">Bahan Baku</span>
            </h1>
            <p class="text-slate-400 font-medium mt-1 text-xs uppercase tracking-wider">
                Manajemen Inventaris & Kontrol Stok Real-time
            </p>
        </div>

        <button onclick="openTambahModal()"
           class="inline-flex items-center justify-center px-6 py-3 bg-slate-900 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-lg gap-3">
            <i class="fas fa-plus-circle text-indigo-400"></i>
            Registrasi Bahan
        </button>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('bahan.index') }}" id="filterForm" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[300px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Material</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik SKU atau Nama Bahan...">
                </div>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Kategori</label>
                <select name="kategori" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                    <option value="">Semua Divisi</option>
                    @foreach($kategori as $kat)
                        <option value="{{ $kat->id_kategori }}" {{ request('kategori') == $kat->id_kategori ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Urutan</label>
                <select name="sort" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                    <option value="nama_bahan" {{ request('sort') == 'nama_bahan' ? 'selected' : '' }}>Abjad (A-Z)</option>
                    <option value="stok" {{ request('sort') == 'stok' ? 'selected' : '' }}>Stok Terendah</option>
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </div>

            <a href="{{ route('bahan.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-left">Identitas Material</th>
                        <th class="px-6 py-5 text-center">Spesifikasi</th>
                        <th class="px-6 py-5 text-center">Volume Stok</th>
                        <th class="px-6 py-5 text-center">Satuan</th>
                        <th class="px-8 py-5 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bahan as $b)
                    <tr class="group hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 font-bold text-xs">
                                    {{ substr($b->nama_bahan, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">{{ $b->nama_bahan }}</div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $b->kode_bahan }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex flex-col gap-1 items-center">
                                <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md uppercase">
                                    {{ $b->mesin->nama_mesin ?? '-' }}
                                </span>
                                <span class="text-[9px] text-slate-400 font-medium uppercase">{{ $b->kategori->nama_kategori ?? 'Umum' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-base font-bold {{ $b->stok <= 10 ? 'text-rose-500' : 'text-slate-700' }}">
                                {{ number_format($b->stok, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-3 py-1.5 rounded-lg uppercase">
                                {{ $b->satuan_stok }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='editBahan(@json($b))' class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                                    <i class="far fa-edit text-sm"></i>
                                </button>
                                <button onclick="singleDelete('{{ $b->id_bahan }}')" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center text-slate-400 font-medium italic">Data tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

       {{-- PAGINATION --}}
<div class="px-8 py-6 bg-white border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
    
    {{-- Info Data --}}
    <div class="text-sm font-medium text-slate-400">
        Showing <span class="text-slate-900 font-bold">{{ $bahan->firstItem() }}</span> to 
        <span class="text-slate-900 font-bold">{{ $bahan->lastItem() }}</span> of 
        <span class="text-slate-900 font-bold">{{ $bahan->total() }}</span> entries
    </div>

    {{-- Navigasi Minimalis --}}
    <div class="pagination-custom relative flex items-center min-w-[300px] justify-end">
        {{-- Teks Tengah --}}
        <div class="absolute left-1/2 -translate-x-1/2 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400/80 whitespace-nowrap hidden sm:block">
            HAL {{ $bahan->currentPage() }} DARI {{ $bahan->lastPage() }}
        </div>

        {{-- Gunakan withQueryString() agar filter kategori & sort tidak hilang saat klik next --}}
        <div class="modern-nav">
            {{ $bahan->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
    </div>
</div>

{{-- MODAL CRUD --}}
<div id="modalBahan" class="hidden fixed inset-0 z-[60] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-10">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-800">Registrasi Material</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fas fa-times-circle text-xl"></i>
                </button>
            </div>

            <form id="formBahan" method="POST" class="p-8">
                @csrf
                <div id="methodField"></div>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Bahan</label>
                        <input type="text" name="nama_bahan" id="input_nama_bahan" required
                               class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kode SKU</label>
                            <input type="text" name="kode_bahan" id="input_kode_bahan" required
                                   class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Satuan</label>
                            <select name="satuan_stok" id="input_satuan_stok" class="select2-satuan" required style="width: 100%">
                                <option value="">Pilih Satuan</option>
                                @foreach($satuan as $s)
                                    <option value="{{ $s->nama_satuan }}">{{ $s->nama_satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Mesin Alokasi</label>
                            <select name="id_mesin" id="input_id_mesin" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                @foreach($mesin as $m)
                                    <option value="{{ $m->id_mesin }}">{{ $m->nama_mesin }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori</label>
                            <select name="id_kategori" id="input_id_kategori" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                @foreach($kategori as $k)
                                    <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pt-8 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 py-3.5 bg-slate-50 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slate-100 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3.5 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2-satuan').select2({
            dropdownParent: $('#modalBahan'),
            placeholder: "Cari satuan...",
            allowClear: true
        });
    });

    function openTambahModal() {
        document.getElementById('modalTitle').innerText = "Registrasi Material Baru";
        document.getElementById('formBahan').action = "{{ route('bahan.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('formBahan').reset();
        $('.select2-satuan').val(null).trigger('change'); // Reset Select2
        document.getElementById('modalBahan').classList.remove('hidden');
    }

    function editBahan(data) {
        document.getElementById('modalTitle').innerText = "Update Spesifikasi Material";
        const form = document.getElementById('formBahan');
        form.action = `/bahan/${data.id_bahan}`; 
        document.getElementById('methodField').innerHTML = '@method("PUT")';

        document.getElementById('input_nama_bahan').value = data.nama_bahan;
        document.getElementById('input_kode_bahan').value = data.kode_bahan;
        document.getElementById('input_id_mesin').value = data.id_mesin;
        document.getElementById('input_id_kategori').value = data.id_kategori;
        
        // Update Select2 value
        $('.select2-satuan').val(data.satuan_stok).trigger('change');

        document.getElementById('modalBahan').classList.remove('hidden');
    }

    function closeModal() { 
        document.getElementById('modalBahan').classList.add('hidden'); 
    }

    // Fungsi Delete tetap sama dengan perbaikan visual sedikit
    function singleDelete(id) {
        Swal.fire({
            title: 'Hapus data?',
            text: "Data yang dihapus tidak dapat dikembalikan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f8fafc',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/bahan/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<style>
    /* Custom Styling Select2 agar menyatu dengan UI */
    .select2-container--default .select2-selection--single {
        background-color: #f8fafc !important;
        border: none !important;
        border-radius: 0.75rem !important;
        height: 46px !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        padding-left: 1rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }
    .select2-dropdown {
        border: 1px solid #f1f5f9 !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }
</style>
@endpush