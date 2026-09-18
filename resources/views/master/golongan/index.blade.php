@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Master Golongan" subtitle="Klasifikasi pelanggan untuk pembedaan level harga dan layanan.">
        <x-slot:actions>
            <x-button onclick="toggleModal('modalGolongan')">
                <i class="fas fa-layer-group"></i>
                Tambah Golongan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('golongan.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Golongan</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama golongan...">
                </div>
            </div>
            <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ route('golongan.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE CONTAINER --}}
    <x-table>
        <thead>
            <tr>
                <th class="px-4 py-5 text-center w-10">#</th>
                <th class="px-8 py-5 text-left w-24">ID</th>
                <th class="px-6 py-5 text-left">Nama Golongan</th>
                <th class="px-6 py-5 text-left">Keterangan</th>
                <th class="px-8 py-5 text-right">Manajemen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($golongan as $i => $g)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $golongan->firstItem() + $i }}</td>
                <td class="px-8 py-5 text-xs text-slate-400">#{{ $g->id_golongan }}</td>
                <td class="px-6 py-5">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase ">
                        {{ $g->nama_golongan }}
                    </span>
                </td>
                <td class="px-6 py-5">
                    <p class="text-sm text-slate-500  leading-relaxed truncate max-w-xs">
                        {{ $g->keterangan ?? 'Tidak ada deskripsi tambahan' }}
                    </p>
                </td>
                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="openEditGolongan({{ json_encode($g) }})"
                                class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                            <i class="far fa-edit text-sm"></i>
                        </button>

                        <form id="delete-form-golongan-{{ $g->id_golongan }}" action="{{ route('golongan.destroy', $g->id_golongan) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" onclick="confirmDeleteGolongan('{{ $g->id_golongan }}', {{ json_encode($g->nama_golongan) }})"
                                class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$golongan" />
</div>

{{-- MODAL TAMBAH (Indigo) --}}
<div id="modalGolongan" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">
            
            <div class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle"></i>
                    <h3 class="text-xl font-bold tracking-tight">Tambah Golongan</h3>
                </div>
                <button onclick="toggleModal('modalGolongan')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form action="{{ route('golongan.store') }}" method="POST" class="py-12 space-y-8">
                @csrf
                <div class="space-y-6">
                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Nama Golongan</label>
                        <input type="text" name="nama_golongan" placeholder="Contoh: Reseller, Member, Umum" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Keterangan Singkat</label>
                        <textarea name="keterangan" placeholder="Keterangan klasifikasi harga..." 
                                  class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center"></textarea>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100">Simpan Data</button>
                    <button type="button" onclick="toggleModal('modalGolongan')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT (Amber) --}}
<div id="modalEditGolongan" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">
            
            <div class="px-10 py-8 bg-amber-500 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-edit"></i>
                    <h3 class="text-xl font-bold tracking-tight">Edit Golongan</h3>
                </div>
                <button onclick="toggleModal('modalEditGolongan')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="formEditGolongan" method="POST" class="py-12 space-y-8">
                @csrf @method('PUT')
                <div class="space-y-6">
                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Nama Golongan</label>
                        <input type="text" name="nama_golongan" id="edit_nama_golongan" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Keterangan Singkat</label>
                        <textarea name="keterangan" id="edit_keterangan"
                                  class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center"></textarea>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-amber-600 shadow-lg shadow-amber-100">Update Data</button>
                    <button type="button" onclick="toggleModal('modalEditGolongan')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Kembali</button>
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

    function openEditGolongan(data) {
        const form = document.getElementById('formEditGolongan');
        form.action = `{{ url('/golongan') }}/${data.id_golongan}`;
        document.getElementById('edit_nama_golongan').value = data.nama_golongan;
        document.getElementById('edit_keterangan').value = data.keterangan || '';
        toggleModal('modalEditGolongan');
    }

    function confirmDeleteGolongan(id, nama) {
        Swal.fire({
            title: 'Hapus Golongan?',
            html: `Golongan <b>${nama}</b> akan dihapus permanen.`,
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
                document.getElementById(`delete-form-golongan-${id}`).submit();
            }
        });
    }
</script>
@endsection