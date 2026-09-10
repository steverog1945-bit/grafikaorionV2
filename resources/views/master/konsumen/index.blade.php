@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Database Konsumen" subtitle="Kelola pelanggan tetap dan temporary percetakan Anda.">
        <x-slot:actions>
            <x-button onclick="openModal('tambah-konsumen')">
                <i class="fas fa-user-plus"></i>
                Konsumen Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-5 text-left">Profil Pelanggan</th>
                <th class="px-6 py-5 text-left">Kontak & Alamat</th>
                <th class="px-6 py-5 text-center">Golongan</th>
                <th class="px-6 py-5 text-center">Tipe</th>
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($konsumen as $k)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-8 py-5">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 font-bold text-xs">
                            {{ substr($k->nama_konsumen, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors uppercase">
                                {{ $k->nama_konsumen }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">ID: #CST-{{ $k->id_konsumen }}</div>
                        </div>
                    </div>
                </td>

                <td class="px-6 py-5 text-left">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-indigo-600 mb-1"><i class="fab fa-whatsapp mr-1"></i> {{ $k->telepon }}</span>
                        <span class="text-[11px] text-slate-400 truncate max-w-[200px]">{{ $k->alamat ?? 'Tanpa alamat' }}</span>
                    </div>
                </td>

                <td class="px-6 py-5 text-center">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $k->nama_golongan }}
                    </span>
                </td>

                <td class="px-6 py-5 text-center">
                    @if($k->tipe_konsumen == 'permanent')
                        <x-badge variant="success">Member</x-badge>
                    @else
                        <x-badge variant="info">Guest</x-badge>
                    @endif
                </td>

                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <x-button size="icon" variant="ghost" onclick="openEditKonsumen({{ json_encode($k) }})" class="hover:text-amber-500 hover:bg-amber-50">
                            <i class="far fa-edit text-sm"></i>
                        </x-button>

                        <form action="{{ route('konsumen.destroy', $k->id_konsumen) }}" method="POST" class="inline" id="delete-form-konsumen-{{ $k->id_konsumen }}">
                            @csrf @method('DELETE')
                            <x-button type="button" size="icon" variant="ghost" onclick="confirmDeleteKonsumen('{{ $k->id_konsumen }}', '{{ $k->nama_konsumen }}')" class="hover:text-rose-500 hover:bg-rose-50">
                                <i class="far fa-trash-alt text-sm"></i>
                            </x-button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <x-empty-state title="Belum ada konsumen yang terdaftar" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- MODAL TAMBAH --}}
    <x-modal id="tambah-konsumen" title="Input Konsumen Baru" maxWidth="md">
        <form action="{{ route('konsumen.store') }}" method="POST" class="space-y-5">
            @csrf
            <x-form.group label="Nama Lengkap" name="nama_konsumen" required>
                <x-form.input type="text" name="nama_konsumen" required />
            </x-form.group>

            <div class="grid grid-cols-2 gap-4">
                <x-form.group label="No. Telepon/WA" name="telepon" required>
                    <x-form.input type="text" name="telepon" required />
                </x-form.group>
                <x-form.group label="Golongan" name="id_golongan">
                    <x-form.select name="id_golongan">
                        @foreach($golongan as $g)
                            <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>
            </div>

            <x-form.group label="Alamat Domisili" name="alamat">
                <textarea name="alamat" rows="2"
                          class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('tambah-konsumen')" class="flex-1">Batalkan</x-button>
                <x-button type="submit" class="flex-1">Simpan Data</x-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDIT --}}
    <x-modal id="edit-konsumen" title="Perbarui Data Konsumen" maxWidth="md">
        <form id="formEditKonsumen" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <x-form.group label="Nama Lengkap">
                <x-form.input type="text" name="nama_konsumen" id="edit_nama" required />
            </x-form.group>

            <div class="grid grid-cols-2 gap-4">
                <x-form.group label="No. Telepon/WA">
                    <x-form.input type="text" name="telepon" id="edit_telepon" required />
                </x-form.group>
                <x-form.group label="Golongan">
                    <x-form.select name="id_golongan" id="edit_golongan">
                        @foreach($golongan as $g)
                            <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>
            </div>

            <x-form.group label="Alamat Domisili">
                <textarea name="alamat" id="edit_alamat" rows="2"
                          class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-amber-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('edit-konsumen')" class="flex-1">Batalkan</x-button>
                <x-button type="submit" variant="warning" class="flex-1">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-modal>

</div>

@push('scripts')
<script>
    function openEditKonsumen(data) {
        const form = document.getElementById('formEditKonsumen');
        form.action = `/konsumen/${data.id_konsumen}`;

        document.getElementById('edit_nama').value = data.nama_konsumen;
        document.getElementById('edit_telepon').value = data.telepon;
        document.getElementById('edit_golongan').value = data.id_golongan;
        document.getElementById('edit_alamat').value = data.alamat || '';

        openModal('edit-konsumen');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function confirmDeleteKonsumen(id, name) {
        Swal.fire({
            title: 'Hapus Konsumen?',
            html: `Menghapus <b>${escapeHtml(name)}</b>? Data yang dihapus tidak dapat dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-konsumen-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
