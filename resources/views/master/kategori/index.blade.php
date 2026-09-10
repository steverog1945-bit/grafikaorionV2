@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Klasifikasi Kategori" subtitle="Pengelompokan Inventaris & Material Produksi">
        <x-slot:actions>
            <x-button onclick="openTambahModal()">
                <i class="fas fa-tags"></i>
                Kategori Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- TABLE CONTAINER --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-left w-48">Kode Identitas</th>
                        <th class="px-8 py-5 text-left">Nama Kategori</th>
                        <th class="px-8 py-5 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($kategori as $kat)
                    <tr class="group hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-5">
                            <span class="inline-flex items-center justify-center px-4 py-1.5 bg-indigo-50 text-indigo-600 text-[11px] font-black rounded-lg uppercase border border-indigo-100/50 ">
                                {{ $kat->kode_kategori }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors uppercase  tracking-tight">
                                {{ $kat->nama_kategori }}
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="editKategori({{ json_encode($kat) }})" 
                                    class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all">
                                    <i class="far fa-edit text-sm"></i>
                                </button>
                                
                                <form action="{{ route('kategori.destroy', $kat->id_kategori) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus kategori ini?')" 
                                        class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                                        <i class="far fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-24 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder-open text-slate-100 text-6xl mb-4"></i>
                                <p class="text-slate-300 font-medium  text-sm">Belum ada kategori yang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL CRUD (Unified) --}}
<div id="modalKategori" class="hidden fixed inset-0 z-[100] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-10">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.15)] w-full max-w-[500px] overflow-hidden transform transition-all border border-slate-100">
            
            {{-- Dynamic Header Color --}}
            <div id="modalHeader" class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white transition-colors duration-500">
                <div class="flex items-center gap-3">
                    <i id="modalIcon" class="fas fa-plus-circle"></i>
                    <h3 id="modalTitle" class="text-xl font-bold tracking-tight uppercase">Tambah Kategori</h3>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="formKategori" action="{{ route('kategori.store') }}" method="POST" class="py-12 space-y-8">
                @csrf
                <div id="methodField"></div>
                
                <div class="space-y-6">
                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="input_nama" placeholder="Contoh: Kertas Art Paper" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center uppercase">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Kode Singkatan</label>
                        <input type="text" name="kode_kategori" id="input_kode" maxlength="5" placeholder="Contoh: AP" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center uppercase tracking-widest">
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button id="btnSubmit" type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                        Simpan Data
                    </button>
                    <button type="button" onclick="closeModal()" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">
                        Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalKategori');
    const form = document.getElementById('formKategori');
    const modalHeader = document.getElementById('modalHeader');
    const btnSubmit = document.getElementById('btnSubmit');
    const modalIcon = document.getElementById('modalIcon');

    function openTambahModal() {
        document.getElementById('modalTitle').innerText = "Tambah Kategori Baru";
        modalHeader.classList.replace('bg-amber-500', 'bg-indigo-600');
        btnSubmit.classList.replace('bg-amber-500', 'bg-indigo-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-indigo-700');
        modalIcon.className = "fas fa-plus-circle";
        
        form.action = "{{ route('kategori.store') }}";
        document.getElementById('methodField').innerHTML = '';
        form.reset();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function editKategori(data) {
        document.getElementById('modalTitle').innerText = "Update Nama Kategori";
        modalHeader.classList.replace('bg-indigo-600', 'bg-amber-500');
        btnSubmit.classList.replace('bg-indigo-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-indigo-700', 'hover:bg-amber-600');
        modalIcon.className = "fas fa-edit";

        form.action = `/kategori/${data.id_kategori}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('input_nama').value = data.nama_kategori;
        document.getElementById('input_kode').value = data.kode_kategori;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
@endsection