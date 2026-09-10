@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Rekening Bank Perusahaan" subtitle="Daftar rekening bank untuk penerimaan pembayaran nota.">
        <x-slot:actions>
            <x-button onclick="toggleModal('modalBank')">
                <i class="fas fa-plus-circle"></i>
                Tambah Rekening
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('bank.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Rekening</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama bank, atas nama, atau no. rekening...">
                </div>
            </div>
            <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ route('bank.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @forelse($bank as $b)
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative group hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-xs font-extrabold uppercase">{{ $b->nama_bank }}</span>
            <div class="flex gap-2">
                <button onclick="openEditBank({{ json_encode($b) }})"
                        class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                    <i class="far fa-edit text-sm"></i>
                </button>

                <form action="{{ route('bank.destroy', $b->id_bank) }}" method="POST" onsubmit="return confirm('Hapus rekening ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                        <i class="far fa-trash-alt text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="text-xl font-bold text-slate-800 mb-1">{{ $b->nomor_rekening }}</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold italic">a/n {{ $b->atas_nama }}</div>
    </div>
    @empty
    <div class="col-span-full py-20 text-center text-slate-400 font-medium italic">Belum ada rekening bank yang terdaftar.</div>
    @endforelse
</div>

<x-table.pagination :paginator="$bank" />

</div>

<div id="modalBank" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalBank')"></div>
    <form action="{{ route('bank.store') }}" method="POST" class="relative w-full max-w-md bg-white rounded-3xl p-8 space-y-4 shadow-2xl">
        @csrf
        <h3 class="text-xl font-bold text-slate-900 mb-2">Input Rekening Baru</h3>
        <input type="text" name="nama_bank" placeholder="Nama Bank (BCA, Mandiri, dll)" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500" required>
        <input type="text" name="nomor_rekening" placeholder="Nomor Rekening" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500" required>
        <input type="text" name="atas_nama" placeholder="Atas Nama (Pemilik)" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500" required>
        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg mt-2 transition active:scale-95">Simpan Rekening</button>
    </form>
</div>

<div id="modalEditBank" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalEditBank')"></div>
    <form id="formEditBank" method="POST" class="relative w-full max-w-md bg-white rounded-3xl p-8 space-y-4 shadow-2xl">
        @csrf
        @method('PUT')
        <h3 class="text-xl font-bold text-slate-900 mb-2">Perbarui Rekening</h3>
        
        <div>
            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nama Bank</label>
            <input type="text" name="nama_bank" id="edit_nama_bank" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
        
        <div>
            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nomor Rekening</label>
            <input type="text" name="nomor_rekening" id="edit_nomor_rekening" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
        
        <div>
            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Atas Nama</label>
            <input type="text" name="atas_nama" id="edit_atas_nama" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>

        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl font-bold shadow-lg mt-2 transition active:scale-95">Update Rekening</button>
    </form>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function openEditBank(data) {
        // Set Action URL sesuai ID Bank
        const form = document.getElementById('formEditBank');
        form.action = `/bank/${data.id_bank}`;
        
        // Isi nilai input modal edit
        document.getElementById('edit_nama_bank').value = data.nama_bank;
        document.getElementById('edit_nomor_rekening').value = data.nomor_rekening;
        document.getElementById('edit_atas_nama').value = data.atas_nama;
        
        // Tampilkan modal
        toggleModal('modalEditBank');
    }
</script>
@endsection