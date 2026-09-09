@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Daftar Supplier" subtitle="Kelola vendor penyedia bahan baku percetakan Anda.">
        <x-slot:actions>
            <x-button onclick="openModal('tambah-supplier')">
                <i class="fas fa-truck-loading"></i>
                Tambah Supplier
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-5 text-left">Nama Perusahaan</th>
                <th class="px-6 py-5 text-left">Kontak Sales</th>
                <th class="px-6 py-5 text-left">Alamat Kantor</th>
                <th class="px-8 py-5 text-right">Manajemen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($supplier as $s)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-8 py-5">
                    <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors uppercase">
                        {{ $s->nama_supplier }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">SUP-ID: #{{ str_pad($s->id_supplier, 4, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm font-semibold text-indigo-600">
                        <i class="fab fa-whatsapp mr-1 text-xs"></i> {{ $s->kontak }}
                    </span>
                </td>
                <td class="px-6 py-5">
                    <p class="text-sm text-slate-500 leading-relaxed truncate max-w-xs">
                        {{ $s->alamat ?? 'Lokasi tidak terdaftar' }}
                    </p>
                </td>
                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <x-button size="icon" variant="ghost" onclick="openEditSupplier({{ json_encode($s) }})" class="hover:text-amber-500 hover:bg-amber-50">
                            <i class="far fa-edit text-sm"></i>
                        </x-button>

                        <form action="{{ route('supplier.destroy', $s->id_supplier) }}" method="POST" class="inline" id="delete-form-supplier-{{ $s->id_supplier }}">
                            @csrf @method('DELETE')
                            <x-button type="button" size="icon" variant="ghost" onclick="confirmDeleteSupplier('{{ $s->id_supplier }}', '{{ $s->nama_supplier }}')" class="hover:text-rose-500 hover:bg-rose-50">
                                <i class="far fa-trash-alt text-sm"></i>
                            </x-button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">
                    <x-empty-state title="Belum ada supplier terdaftar" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- MODAL TAMBAH --}}
    <x-modal id="tambah-supplier" title="Input Supplier Baru" maxWidth="md">
        <form action="{{ route('supplier.store') }}" method="POST" class="space-y-5">
            @csrf
            <x-form.group label="Nama Perusahaan/Vendor" name="nama_supplier" required>
                <x-form.input type="text" name="nama_supplier" placeholder="Contoh: PT. Sumber Kertas" required />
            </x-form.group>

            <x-form.group label="No. Telepon / Sales" name="kontak" required>
                <x-form.input type="text" name="kontak" placeholder="08xx xxxx xxxx" required />
            </x-form.group>

            <x-form.group label="Alamat Lengkap" name="alamat">
                <textarea name="alamat" placeholder="Jl. Raya Percetakan No. 1..."
                          class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('tambah-supplier')" class="flex-1">Batalkan</x-button>
                <x-button type="submit" class="flex-1">Simpan Supplier</x-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDIT --}}
    <x-modal id="edit-supplier" title="Perbarui Data Supplier" maxWidth="md">
        <form id="formEditSupplier" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <x-form.group label="Nama Perusahaan/Vendor">
                <x-form.input type="text" name="nama_supplier" id="edit_nama_supplier" required />
            </x-form.group>

            <x-form.group label="No. Telepon / Sales">
                <x-form.input type="text" name="kontak" id="edit_kontak" required />
            </x-form.group>

            <x-form.group label="Alamat Lengkap">
                <textarea name="alamat" id="edit_alamat"
                          class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('edit-supplier')" class="flex-1">Kembali</x-button>
                <x-button type="submit" variant="warning" class="flex-1">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-modal>

</div>

@push('scripts')
<script>
    function openEditSupplier(data) {
        const form = document.getElementById('formEditSupplier');
        form.action = `/supplier/${data.id_supplier}`;
        document.getElementById('edit_nama_supplier').value = data.nama_supplier;
        document.getElementById('edit_kontak').value = data.kontak;
        document.getElementById('edit_alamat').value = data.alamat || '';
        openModal('edit-supplier');
    }

    function confirmDeleteSupplier(id, name) {
        Swal.fire({
            title: 'Hapus Supplier?',
            html: `Menghapus <b>${name}</b>? Data yang dihapus tidak dapat dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-supplier-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
