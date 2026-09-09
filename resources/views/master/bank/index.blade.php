@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 uppercase">Rekening Bank Perusahaan</h1>
        <p class="text-sm text-slate-500">Daftar rekening bank untuk penerimaan pembayaran nota.</p>
    </div>
    <button onclick="toggleModal('modalBank')" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg transition active:scale-95">＋ Tambah Rekening</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($bank as $b)
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative group hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-xs font-extrabold uppercase">{{ $b->nama_bank }}</span>
            <div class="flex gap-2">
                <button onclick="openEditBank({{ json_encode($b) }})" class="text-slate-300 hover:text-indigo-600 transition">✏️</button>
                
                <form action="{{ route('bank.destroy', $b->id_bank) }}" method="POST" onsubmit="return confirm('Hapus rekening ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-slate-300 hover:text-red-500 transition">🗑️</button>
                </form>
            </div>
        </div>
        <div class="text-xl font-mono font-bold text-slate-800 mb-1">{{ $b->nomor_rekening }}</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold italic">a/n {{ $b->atas_nama }}</div>
    </div>
    @endforeach
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

        <button type="submit" class="w-full bg-emerald-600 text-white py-3 rounded-xl font-bold shadow-lg mt-2 transition active:scale-95">Update Rekening</button>
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