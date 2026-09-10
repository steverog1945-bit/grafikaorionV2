@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Manajemen Pengguna" subtitle="Kelola akun login dan sinkronisasi hak akses karyawan.">
        <x-slot:actions>
            <x-button onclick="toggleModal('modalUser')">
                <i class="fas fa-user-plus"></i>
                Buat Akun User
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Username</th>
                <th class="px-4 py-5 text-left">Nama Karyawan</th>
                <th class="px-4 py-5 text-left">Role / Akses</th>
                <th class="px-8 py-5 text-right">Keamanan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($users as $u)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[13px] text-indigo-600 font-bold">{{ '@' . $u->username }}</span>
                </td>
                <td class="px-4 py-5">
                    <div class="text-[13px] font-bold text-slate-700 uppercase">{{ $u->karyawan->nama_karyawan }}</div>
                    <div class="text-[10px] text-slate-400">{{ $u->karyawan->bagian }}</div>
                </td>
                <td class="px-4 py-5">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <i class="fas fa-shield-alt mr-1.5"></i> {{ $u->role->nama_role }}
                    </span>
                </td>
                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <x-button type="button" size="icon" variant="ghost" onclick='openEditUser({{ json_encode($u) }})' class="border border-slate-100 hover:text-amber-500" title="Edit">
                            <i class="far fa-edit text-sm"></i>
                        </x-button>
                        <form action="{{ url('/users/delete/'.$u->id_user) }}" method="POST" class="inline" id="delete-form-user-{{ $u->id_user }}">
                            @csrf @method('DELETE')
                            <x-button type="button" size="icon" variant="ghost" onclick="confirmDeleteUser('{{ $u->id_user }}', '{{ $u->username }}')" class="border border-slate-100 hover:text-rose-500" title="Hapus">
                                <i class="far fa-trash-alt text-sm"></i>
                            </x-button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </x-table>

</div>

<div id="modalUser" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalUser')"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-indigo-600"></div>
        
        <h3 class="text-xl font-bold mb-6 text-slate-900 flex items-center gap-2">
            <span>🔐</span> Buat Akses User Baru
        </h3>
        
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Tautkan Karyawan</label>
                <select name="id_karyawan" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition" required>
                    <option value="">-- Pilih Nama Karyawan --</option>
                    @foreach($karyawan as $k)
                        <option value="{{ $k->id_karyawan }}">{{ $k->nama_karyawan }} ({{ $k->bagian }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Username</label>
                    <input type="text" name="username" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="contoh: admin_cetak" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Password Awal</label>
                    <input type="password" name="password" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="••••••••" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Role / Tingkat Akses</label>
                <select name="id_role" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->id_role }}">{{ $r->nama_role }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="toggleModal('modalUser')" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Batal</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-95">
                    Aktifkan Akun
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT: dulunya tombol ini cuma bertuliskan "Reset Password" tanpa
     onclick apapun - UserController::update() sudah ada dan bekerja (bisa
     ganti username/role, dan password kalau diisi), tapi tidak ada satupun
     elemen UI yang memanggilnya. --}}
<div id="modalEditUser" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalEditUser')"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>

        <h3 class="text-xl font-bold mb-6 text-slate-900 flex items-center gap-2">
            <span>✏️</span> Edit Akun User
        </h3>

        <form id="formEditUser" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Username</label>
                <input type="text" name="username" id="edit_username" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-amber-500 transition" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Role / Tingkat Akses</label>
                <select name="id_role" id="edit_id_role" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-amber-500 transition" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->id_role }}">{{ $r->nama_role }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1 ml-1">Password Baru (opsional)</label>
                <input type="password" name="password" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="toggleModal('modalEditUser')" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Batal</button>
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-amber-100 transition-all active:scale-95">
                    Simpan Perubahan
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

    function openEditUser(user) {
        document.getElementById('formEditUser').action = `/users/update/${user.id_user}`;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_id_role').value = user.id_role;
        toggleModal('modalEditUser');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function confirmDeleteUser(id, username) {
        Swal.fire({
            title: 'Hapus Akun User?',
            html: `Menghapus akun <b>@${escapeHtml(username)}</b>? Karyawan terkait tidak akan bisa login lagi sampai dibuatkan akun baru.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-user-' + id).submit();
            }
        });
    }
</script>
@endsection