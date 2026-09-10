@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Chart of Accounts" subtitle="Daftar akun (bagan akun) yang dipakai untuk mencatat semua jurnal transaksi.">
        <x-slot:actions>
            <a href="{{ route('akuntansi.jurnal') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl text-[11px] font-bold uppercase tracking-widest text-slate-500 border border-slate-200 hover:bg-slate-50 transition-all">
                <i class="fas fa-book"></i> Lihat Jurnal
            </a>
            <x-button onclick="toggleModal('modalAkun')">
                <i class="fas fa-plus"></i>
                Tambah Akun
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @php
        $urutanTipe = ['Aset', 'Kewajiban', 'Modal', 'Pendapatan', 'Beban'];
        $warnaTipe = [
            'Aset'       => 'bg-sky-50 text-sky-700 border-sky-100',
            'Kewajiban'  => 'bg-rose-50 text-rose-700 border-rose-100',
            'Modal'      => 'bg-violet-50 text-violet-700 border-violet-100',
            'Pendapatan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'Beban'      => 'bg-amber-50 text-amber-700 border-amber-100',
        ];
        $grouped = $akun->groupBy('tipe_akun');
    @endphp

    <div class="space-y-8">
        @foreach($urutanTipe as $tipe)
            @if($grouped->has($tipe))
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border uppercase {{ $warnaTipe[$tipe] }}">{{ $tipe }}</span>
                    <span class="text-[11px] text-slate-400">{{ $grouped[$tipe]->count() }} akun</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                                <th class="px-8 py-4 text-left w-32">Kode Akun</th>
                                <th class="px-6 py-4 text-left">Nama Akun</th>
                                <th class="px-6 py-4 text-left w-32">Saldo Normal</th>
                                <th class="px-6 py-4 text-left w-28">Status</th>
                                <th class="px-8 py-4 text-right">Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($grouped[$tipe] as $a)
                            <tr class="group hover:bg-slate-50/30 transition-all {{ !$a->is_active ? 'opacity-50' : '' }}">
                                <td class="px-8 py-4 text-sm font-bold text-slate-600">{{ $a->kode_akun }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $a->nama_akun }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $a->saldo_normal }}</td>
                                <td class="px-6 py-4">
                                    @if($a->is_active)
                                        <span class="text-[10px] font-bold text-emerald-600 uppercase">Aktif</span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditAkun({{ json_encode($a) }})"
                                                class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                                            <i class="far fa-edit text-sm"></i>
                                        </button>
                                        <form action="{{ route('akun.toggle-active', $a->id_akun) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded-lg transition-all" title="{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas {{ $a->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} text-sm"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('akun.destroy', $a->id_akun) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus akun ini? Hanya bisa dihapus kalau belum pernah dipakai di jurnal.')"
                                                    class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                                <i class="far fa-trash-alt text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div id="modalAkun" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">
            <div class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle"></i>
                    <h3 class="text-xl font-bold tracking-tight">Tambah Akun</h3>
                </div>
                <button onclick="toggleModal('modalAkun')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <form action="{{ route('akun.store') }}" method="POST" class="py-12 space-y-6">
                @csrf
                <div class="space-y-6 max-w-[85%] mx-auto">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Kode Akun</label>
                        <input type="text" name="kode_akun" placeholder="Contoh: 1300" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Nama Akun</label>
                        <input type="text" name="nama_akun" placeholder="Contoh: Perlengkapan Kantor" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Tipe Akun</label>
                        <select name="tipe_akun" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                            <option value="Aset">Aset</option>
                            <option value="Kewajiban">Kewajiban</option>
                            <option value="Modal">Modal</option>
                            <option value="Pendapatan">Pendapatan</option>
                            <option value="Beban">Beban</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Saldo Normal</label>
                        <select name="saldo_normal" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                            <option value="Debet">Debet</option>
                            <option value="Kredit">Kredit</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100">Simpan Akun</button>
                    <button type="button" onclick="toggleModal('modalAkun')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEditAkun" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">
            <div class="px-10 py-8 bg-amber-500 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-edit"></i>
                    <h3 class="text-xl font-bold tracking-tight">Edit Akun</h3>
                </div>
                <button onclick="toggleModal('modalEditAkun')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <form id="formEditAkun" method="POST" class="py-12 space-y-6">
                @csrf @method('PUT')
                <div class="space-y-6 max-w-[85%] mx-auto">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Kode Akun</label>
                        <input type="text" name="kode_akun" id="edit_kode_akun" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Nama Akun</label>
                        <input type="text" name="nama_akun" id="edit_nama_akun" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Tipe Akun</label>
                        <select name="tipe_akun" id="edit_tipe_akun" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center">
                            <option value="Aset">Aset</option>
                            <option value="Kewajiban">Kewajiban</option>
                            <option value="Modal">Modal</option>
                            <option value="Pendapatan">Pendapatan</option>
                            <option value="Beban">Beban</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Saldo Normal</label>
                        <select name="saldo_normal" id="edit_saldo_normal" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center">
                            <option value="Debet">Debet</option>
                            <option value="Kredit">Kredit</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-amber-600 shadow-lg shadow-amber-100">Update Akun</button>
                    <button type="button" onclick="toggleModal('modalEditAkun')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Kembali</button>
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

    function openEditAkun(data) {
        const form = document.getElementById('formEditAkun');
        form.action = `/akun/${data.id_akun}`;
        document.getElementById('edit_kode_akun').value = data.kode_akun;
        document.getElementById('edit_nama_akun').value = data.nama_akun;
        document.getElementById('edit_tipe_akun').value = data.tipe_akun;
        document.getElementById('edit_saldo_normal').value = data.saldo_normal;
        toggleModal('modalEditAkun');
    }
</script>
@endsection
