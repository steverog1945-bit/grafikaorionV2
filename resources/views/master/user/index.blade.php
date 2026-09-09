@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
        <p class="text-slate-500 text-sm">Kelola akun login dan sinkronisasi hak akses karyawan.</p>
    </div>
    <button onclick="toggleModal('modalUser')" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-emerald-200">
        <span class="mr-2">👤+</span> Buat Akun User
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase italic">Username</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Nama Karyawan</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Role / Akses</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Keamanan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $u)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-mono text-indigo-600 font-bold">@ {{ $u->username }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900">{{ $u->karyawan->nama_karyawan }}</div>
                        <div class="text-xs text-slate-400">{{ $u->karyawan->bagian }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            🛡️ {{ $u->role->nama_role }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-xs font-bold text-amber-600 hover:text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100 transition-all active:scale-95">
                            Reset Password
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="modalUser" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalUser')"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
        
        <h3 class="text-xl font-bold mb-6 text-slate-900 flex items-center gap-2">
            <span>🔐</span> Buat Akses User Baru
        </h3>
        
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Tautkan Karyawan</label>
                <select name="id_karyawan" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500 transition" required>
                    <option value="">-- Pilih Nama Karyawan --</option>
                    @foreach($karyawan as $k)
                        <option value="{{ $k->id_karyawan }}">{{ $k->nama_karyawan }} ({{ $k->bagian }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Username</label>
                    <input type="text" name="username" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500 transition" placeholder="contoh: admin_cetak" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Password Awal</label>
                    <input type="password" name="password" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500 transition" placeholder="••••••••" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Role / Tingkat Akses</label>
                <select name="id_role" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-emerald-500 transition" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->id_role }}">{{ $r->nama_role }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="toggleModal('modalUser')" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Batal</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-100 transition-all active:scale-95">
                    Aktifkan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
    }
</script>
@endsection