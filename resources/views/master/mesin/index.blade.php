@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Master Aset Mesin" subtitle="Monitor dan kelola armada mesin cetak produksi real-time">
        <x-slot:actions>
            <x-button onclick="toggleModal('tambahMesin')">
                <i class="fas fa-plus-circle"></i>
                Registrasi Mesin
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- TABLE CONTAINER --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto overflow-y-visible">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-left">Identitas Mesin</th>
                        <th class="px-6 py-5 text-center">Tipe Operasional</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-8 py-5 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($mesin as $m)
                    <tr class="group hover:bg-slate-50/30 transition-all">
                        {{-- Identitas Mesin --}}
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 font-bold text-xs shadow-sm">
                                    <i class="fas fa-print"></i>
                                </div>
                                <div>
                                    <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors uppercase">
                                        {{ $m->nama_mesin }}
                                    </div>
                                    <div class="text-[12px] text-slate-400 mt-0.5">ID: #{{ $m->id_mesin }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Tipe Mesin --}}
                        <td class="px-6 py-5 text-center">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">
                                <i class="fas fa-bolt mr-1.5 text-blue-400"></i> {{ $m->tipe_mesin }}
                            </span>
                        </td>

                        {{-- Status (Placeholder Visual) --}}
                        <td class="px-6 py-5 text-center">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-500 bg-emerald-50 px-3 py-1.5 rounded-lg uppercase border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Active
                            </span>
                        </td>

                        {{-- Action Buttons --}}
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="toggleModal('edit{{ $m->id_mesin }}')" 
                                        class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all border border-transparent hover:border-amber-100">
                                    <i class="far fa-edit text-sm"></i>
                                </button>
                                
                                <form action="{{ url('mesin/'.$m->id_mesin) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus data mesin ini?')" 
                                            class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all border border-transparent hover:border-rose-100">
                                        <i class="far fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                   {{-- MODAL EDIT (Inline per Row) --}}
<div id="edit{{ $m->id_mesin }}" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        {{-- Card Modal --}}
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">
            
            {{-- Header: Warna Amber Solid untuk Indikasi Edit --}}
            <div class="px-10 py-8 bg-amber-500 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-bold text-white tracking-tight">Update Data Mesin</h3>
                </div>
                <button onclick="toggleModal('edit{{ $m->id_mesin }}')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            {{-- Form Body --}}
            <form action="{{ url('mesin/'.$m->id_mesin) }}" method="POST" class="py-6 space-y-8">
                @csrf @method('PUT')
                
                <div class="space-y-6">
                    {{-- Input Nama - Terpusat --}}
                    <div class="max-w-[85%] mx-auto text-left">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Nama Mesin Produksi</label>
                        <input type="text" name="nama_mesin" value="{{ $m->nama_mesin }}" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 transition-all shadow-inner text-center">
                    </div>

                    {{-- Input Tipe - Terpusat --}}
                    <div class="max-w-[85%] mx-auto text-left">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Tipe / Teknologi Mesin</label>
                        <input type="text" name="tipe_mesin" value="{{ $m->tipe_mesin }}" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 transition-all shadow-inner text-center">
                    </div>
                </div>

                {{-- Footer Buttons: Stacked & Centered --}}
                <div class="flex flex-col items-center gap-3 pt-4 max-w-[75%] mx-auto">
                    <button type="submit" 
                            class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-amber-600 transition-all">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="toggleModal('edit{{ $m->id_mesin }}')" 
                            class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:text-slate-600 transition-all">
                        Kembali / Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div id="tambahMesin" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-10">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-indigo-600">
                <h3 class="text-lg font-bold text-white">Registrasi Mesin Baru</h3>
                <button onclick="toggleModal('tambahMesin')" class="text-indigo-200 hover:text-white transition-colors">
                    <i class="fas fa-times-circle text-xl"></i>
                </button>
            </div>
            <form action="{{ url('mesin') }}" method="POST" class="p-8 space-y-5">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Mesin Produksi</label>
                    <input type="text" name="nama_mesin" placeholder="Contoh: Roland RF-640" required
                           class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Tipe / Teknologi Mesin</label>
                    <input type="text" name="tipe_mesin" placeholder="Contoh: Eco-Solvent" required
                           class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
                <div class="pt-5 flex gap-3">
                    <button type="button" onclick="toggleModal('tambahMesin')" class="flex-1 py-3.5 bg-slate-50 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slate-100 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3.5 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">Daftarkan Mesin</button>
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
            // Prevent body scroll when modal open
            if (!modal.classList.contains('hidden')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = 'auto';
            }
        }
    }
</script>
@endsection