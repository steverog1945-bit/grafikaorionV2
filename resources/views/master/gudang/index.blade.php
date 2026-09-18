@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Master Gudang" subtitle="Lokasi penyimpanan bahan baku dan penanggung jawabnya.">
        <x-slot:actions>
            <x-button onclick="toggleModal('modalGudang')">
                <i class="fas fa-warehouse"></i>
                Tambah Gudang
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('gudang.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Gudang</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama gudang...">
                </div>
            </div>
            <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ route('gudang.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE CONTAINER --}}
    <x-table>
        <thead>
            <tr>
                <th class="px-4 py-5 text-center w-10">#</th>
                <th class="px-8 py-5 text-left">Nama Gudang</th>
                <th class="px-6 py-5 text-left">Alamat</th>
                <th class="px-6 py-5 text-left">Penanggung Jawab</th>
                <th class="px-6 py-5 text-left">Telepon</th>
                <th class="px-8 py-5 text-right">Manajemen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($gudang as $i => $g)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $gudang->firstItem() + $i }}</td>
                <td class="px-8 py-5">
                    <span class="text-sm font-bold text-slate-700 uppercase">{{ $g->nama_gudang }}</span>
                    <div class="text-[10px] text-slate-400 mt-0.5">ID: #{{ $g->id_gudang }}</div>
                </td>
                <td class="px-6 py-5">
                    <p class="text-sm text-slate-500 leading-relaxed truncate max-w-xs">{{ $g->alamat ?? '-' }}</p>
                </td>
                <td class="px-6 py-5 text-sm text-slate-600 font-medium">{{ $g->penanggung_jawab ?? '-' }}</td>
                <td class="px-6 py-5 text-sm text-slate-500">{{ $g->telepon ?? '-' }}</td>
                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="openEditGudang({{ json_encode($g) }})"
                                class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                            <i class="far fa-edit text-sm"></i>
                        </button>

                        <form id="delete-form-gudang-{{ $g->id_gudang }}" action="{{ route('gudang.destroy', $g->id_gudang) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" onclick="confirmDeleteGudang('{{ $g->id_gudang }}', {{ json_encode($g->nama_gudang) }})"
                                class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-20 text-center text-slate-400 font-medium italic">Belum ada gudang yang terdaftar.</td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$gudang" />
</div>

{{-- MODAL TAMBAH (Indigo) --}}
<div id="modalGudang" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">

            <div class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle"></i>
                    <h3 class="text-xl font-bold tracking-tight">Tambah Gudang</h3>
                </div>
                <button onclick="toggleModal('modalGudang')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form action="{{ route('gudang.store') }}" method="POST" class="py-10 space-y-6">
                @csrf
                <div class="space-y-5 max-w-[85%] mx-auto">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Nama Gudang</label>
                        <input type="text" name="nama_gudang" placeholder="Contoh: Gudang Utama" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Alamat</label>
                        <textarea name="alamat" placeholder="Alamat gudang..."
                                  class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" placeholder="Nama PIC gudang"
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Telepon</label>
                        <input type="text" name="telepon" placeholder="08xxxxxxxxxx"
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100">Simpan Data</button>
                    <button type="button" onclick="toggleModal('modalGudang')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT (Amber) --}}
<div id="modalEditGudang" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">

            <div class="px-10 py-8 bg-amber-500 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-edit"></i>
                    <h3 class="text-xl font-bold tracking-tight">Edit Gudang</h3>
                </div>
                <button onclick="toggleModal('modalEditGudang')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="formEditGudang" method="POST" class="py-10 space-y-6">
                @csrf @method('PUT')
                <div class="space-y-5 max-w-[85%] mx-auto">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Nama Gudang</label>
                        <input type="text" name="nama_gudang" id="edit_nama_gudang" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Alamat</label>
                        <textarea name="alamat" id="edit_alamat"
                                  class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" id="edit_penanggung_jawab"
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Telepon</label>
                        <input type="text" name="telepon" id="edit_telepon"
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner">
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-amber-600 shadow-lg shadow-amber-100">Update Data</button>
                    <button type="button" onclick="toggleModal('modalEditGudang')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }
    }

    function openEditGudang(data) {
        const form = document.getElementById('formEditGudang');
        form.action = `{{ url('/gudang') }}/${data.id_gudang}`;
        document.getElementById('edit_nama_gudang').value = data.nama_gudang;
        document.getElementById('edit_alamat').value = data.alamat || '';
        document.getElementById('edit_penanggung_jawab').value = data.penanggung_jawab || '';
        document.getElementById('edit_telepon').value = data.telepon || '';
        toggleModal('modalEditGudang');
    }

    function confirmDeleteGudang(id, nama) {
        Swal.fire({
            title: 'Hapus Gudang?',
            html: `Gudang <b>${nama}</b> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f8fafc',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-gudang-${id}`).submit();
            }
        });
    }
</script>
@endsection
