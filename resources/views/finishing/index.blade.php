@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Finishing Produk" subtitle="Master Produk & BOM / Jenis Pekerjaan Finishing">
        <x-slot:actions>
            <x-button onclick="openModal('tambah-finishing')">
                <i class="fas fa-magic"></i>
                Finishing Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Nama Finishing</th>
                <th class="px-4 py-5 text-left">Mesin/Divisi</th>
                <th class="px-4 py-5 text-center">Metode Hitung</th>
                <th class="px-4 py-5 text-right">Harga Default</th>
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $d)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-6 py-5">
                    <div class="text-[13px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors uppercase">
                        {{ $d->nama_finishing }}
                    </div>
                    @if($d->keterangan)
                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $d->keterangan }}</div>
                    @endif
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ $d->mesin->nama_mesin ?? '-' }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $d->metode_hitung === 'luas' ? 'Luas (m²)' : 'Satuan (Pcs)' }}
                    </span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[13px] font-bold text-slate-800">Rp {{ number_format($d->harga_default, 0, ',', '.') }}</span>
                </td>
                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <x-button size="icon" variant="ghost" onclick="openModal('harga-finishing-{{ $d->id_finishing }}')" class="hover:text-indigo-500 hover:bg-indigo-50" title="Harga Bertingkat per Qty">
                            <i class="fas fa-layer-group text-sm"></i>
                        </x-button>
                        <x-button size="icon" variant="ghost" onclick="openEditFinishing({{ json_encode($d) }})" class="hover:text-amber-500 hover:bg-amber-50">
                            <i class="far fa-edit text-sm"></i>
                        </x-button>

                        <form action="{{ route('finishing.destroy', $d->id_finishing) }}" method="POST" class="inline" id="delete-form-finishing-{{ $d->id_finishing }}">
                            @csrf @method('DELETE')
                            <x-button type="button" size="icon" variant="ghost" onclick="confirmDeleteFinishing('{{ $d->id_finishing }}', '{{ $d->nama_finishing }}')" class="hover:text-rose-500 hover:bg-rose-50">
                                <i class="far fa-trash-alt text-sm"></i>
                            </x-button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <x-empty-state title="Belum ada jenis finishing" description="Tambahkan jenis finishing seperti Laminasi, Kisscut, atau Jahit Banner." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- MODAL HARGA BERTINGKAT (satu per finishing - dirender server-side,
         setiap baris tier form-nya sendiri dengan id_finishing_harga asli
         di database, supaya add/edit/hapus satu baris tidak pernah
         menyentuh baris lain sama sekali). --}}
    @foreach($data as $d)
    <x-modal id="harga-finishing-{{ $d->id_finishing }}" title="Harga Bertingkat: {{ $d->nama_finishing }}" maxWidth="lg">
        <div class="mb-4 p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500">
            Harga Default (<b>Rp {{ number_format($d->harga_default, 0, ',', '.') }}</b>) dipakai kalau qty tidak cocok tier manapun di bawah, atau belum ada tier sama sekali. Kasir tetap bisa menimpa manual di Kasir Utama - ini hanya acuan/starting point.
        </div>

        @php $tiers = $hargaTiers[$d->id_finishing] ?? collect(); @endphp

        @if($tiers->isEmpty())
            <p class="text-xs text-slate-400 italic mb-4">Belum ada tier harga - selalu memakai Harga Default di atas.</p>
        @else
            <div class="space-y-2 mb-4">
                @foreach($tiers as $t)
                <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2">
                    <form action="{{ route('finishing.harga.update', $t->id_finishing_harga) }}" method="POST" class="flex items-center gap-2 flex-1">
                        @csrf @method('PATCH')
                        <input type="number" name="range_min" value="{{ $t->range_min }}" min="1" required class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                        <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                        <input type="number" name="range_max" value="{{ $t->range_max }}" min="1" placeholder="~" class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                        <span class="text-[10px] text-slate-400">=</span>
                        <span class="text-[10px] text-slate-400">Rp</span>
                        <input type="number" name="harga" value="{{ (int) $t->harga }}" min="0" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold">
                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                            <i class="fas fa-save text-xs"></i>
                        </button>
                    </form>
                    <form action="{{ route('finishing.harga.destroy', $t->id_finishing_harga) }}" method="POST" id="hapus-tier-finishing-{{ $t->id_finishing_harga }}">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmHapusTierFinishing({{ $t->id_finishing_harga }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('finishing.harga.store', $d->id_finishing) }}" method="POST" class="flex items-center gap-2 bg-emerald-50/50 border border-dashed border-emerald-200 rounded-xl p-2">
            @csrf
            <input type="number" name="range_min" placeholder="Min" min="1" required class="w-16 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[10px] text-slate-300 font-bold">s/d</span>
            <input type="number" name="range_max" placeholder="~" min="1" class="w-16 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[10px] text-slate-400">=</span>
            <span class="text-[10px] text-slate-400">Rp</span>
            <input type="number" name="harga" placeholder="0" min="0" required class="flex-1 text-xs bg-white border-none rounded-lg py-2 font-bold">
            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition-all shrink-0" title="Tambah Tier">
                <i class="fas fa-plus text-xs"></i>
            </button>
        </form>

        <div class="pt-4 flex justify-end">
            <x-button type="button" variant="secondary" onclick="closeModal('harga-finishing-{{ $d->id_finishing }}')">Tutup</x-button>
        </div>
    </x-modal>
    @endforeach

    {{-- MODAL TAMBAH --}}
    <x-modal id="tambah-finishing" title="Finishing Baru" maxWidth="md">
        <form action="{{ route('finishing.store') }}" method="POST" class="space-y-5">
            @csrf
            <x-form.group label="Nama Finishing" name="nama_finishing" required>
                <x-form.input type="text" name="nama_finishing" placeholder="Contoh: Laminasi Doff" required />
            </x-form.group>

            <div class="grid grid-cols-2 gap-4">
                <x-form.group label="Mesin/Divisi" name="id_mesin">
                    <x-form.select name="id_mesin">
                        <option value="">-- Umum --</option>
                        @foreach($mesin as $m)
                            <option value="{{ $m->id_mesin }}">{{ $m->nama_mesin }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>
                <x-form.group label="Metode Hitung" name="metode_hitung" required>
                    <x-form.select name="metode_hitung" required>
                        <option value="luas">Luas (m²)</option>
                        <option value="quantity">Satuan (Pcs)</option>
                    </x-form.select>
                </x-form.group>
            </div>

            <x-form.group label="Harga Default (Rp)" name="harga_default" required>
                <x-form.input type="number" name="harga_default" min="0" step="1" required />
            </x-form.group>

            <x-form.group label="Keterangan" name="keterangan">
                <textarea name="keterangan" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('tambah-finishing')" class="flex-1">Batalkan</x-button>
                <x-button type="submit" class="flex-1">Simpan Finishing</x-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDIT --}}
    <x-modal id="edit-finishing" title="Perbarui Finishing" maxWidth="md">
        <form id="formEditFinishing" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <x-form.group label="Nama Finishing">
                <x-form.input type="text" name="nama_finishing" id="edit_nama_finishing" required />
            </x-form.group>

            <div class="grid grid-cols-2 gap-4">
                <x-form.group label="Mesin/Divisi">
                    <x-form.select name="id_mesin" id="edit_id_mesin">
                        <option value="">-- Umum --</option>
                        @foreach($mesin as $m)
                            <option value="{{ $m->id_mesin }}">{{ $m->nama_mesin }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>
                <x-form.group label="Metode Hitung">
                    <x-form.select name="metode_hitung" id="edit_metode_hitung" required>
                        <option value="luas">Luas (m²)</option>
                        <option value="quantity">Satuan (Pcs)</option>
                    </x-form.select>
                </x-form.group>
            </div>

            <x-form.group label="Harga Default (Rp)">
                <x-form.input type="number" name="harga_default" id="edit_harga_default" min="0" step="1" required />
            </x-form.group>

            <x-form.group label="Keterangan">
                <textarea name="keterangan" id="edit_keterangan" rows="2" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-amber-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('edit-finishing')" class="flex-1">Batalkan</x-button>
                <x-button type="submit" variant="warning" class="flex-1">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-modal>

</div>

@push('scripts')
<script>
    function openEditFinishing(data) {
        const form = document.getElementById('formEditFinishing');
        form.action = `{{ url('finishing') }}/${data.id_finishing}`;

        document.getElementById('edit_nama_finishing').value = data.nama_finishing;
        document.getElementById('edit_id_mesin').value = data.id_mesin || '';
        document.getElementById('edit_metode_hitung').value = data.metode_hitung || 'luas';
        document.getElementById('edit_harga_default').value = data.harga_default || 0;
        document.getElementById('edit_keterangan').value = data.keterangan || '';

        openModal('edit-finishing');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function confirmHapusTierFinishing(id) {
        Swal.fire({
            title: 'Hapus Tier Harga Ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('hapus-tier-finishing-' + id).submit();
            }
        });
    }

    function confirmDeleteFinishing(id, name) {
        Swal.fire({
            title: 'Hapus Finishing?',
            html: `Menghapus <b>${escapeHtml(name)}</b>? Data yang dihapus tidak dapat dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-finishing-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
