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

        @php
            $tiers = $hargaTiers[$d->id_finishing] ?? collect();
            // Satuan rentang eksplisit ikut metode_hitung finishing ITU SENDIRI
            // ('luas' vs 'quantity' - lihat perbaikan qty acuan finishing sesi
            // sebelumnya), sama seperti Price List produk - luas m² boleh pecahan.
            $satuanRentangFinishing = $d->metode_hitung === 'luas' ? 'm²' : 'Pcs';
            $stepRentangFinishing = $d->metode_hitung === 'luas' ? '0.01' : '1';
        @endphp

        <div id="tier-container-finishing-{{ $d->id_finishing }}" class="space-y-2 mb-4" data-satuan-rentang="{{ $satuanRentangFinishing }}">
            <p class="text-xs text-slate-400 italic tier-empty-msg-finishing" {{ $tiers->isNotEmpty() ? 'hidden' : '' }}>Belum ada tier harga - selalu memakai Harga Default di atas.</p>
            @foreach($tiers as $t)
            <div class="tier-row-finishing flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2" data-id="{{ $t->id_finishing_harga }}">
                <form action="{{ route('finishing.harga.update', $t->id_finishing_harga) }}" class="tier-edit-form-finishing flex items-center gap-2 flex-1">
                    @csrf
                    <input type="number" name="range_min" value="{{ $t->range_min }}" min="0.01" step="{{ $stepRentangFinishing }}" required class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                    <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                    <input type="number" name="range_max" value="{{ $t->range_max }}" min="0.01" step="{{ $stepRentangFinishing }}" placeholder="~" class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                    <span class="text-[9px] text-slate-400 uppercase shrink-0">{{ $satuanRentangFinishing }}</span>
                    <span class="text-[10px] text-slate-400">=</span>
                    <span class="text-[10px] text-slate-400">Rp</span>
                    <input type="number" name="harga" value="{{ (int) $t->harga }}" min="0" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold">
                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                        <i class="fas fa-save text-xs"></i>
                    </button>
                </form>
                <button type="button" onclick="confirmHapusTierFinishing({{ $t->id_finishing_harga }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
            @endforeach
        </div>

        <form action="{{ route('finishing.harga.store', $d->id_finishing) }}" class="tier-add-form-finishing flex items-center gap-2 bg-emerald-50/50 border border-dashed border-emerald-200 rounded-xl p-2" data-id-finishing="{{ $d->id_finishing }}" data-satuan-rentang="{{ $satuanRentangFinishing }}">
            @csrf
            <input type="number" name="range_min" placeholder="Min" min="0.01" step="{{ $stepRentangFinishing }}" required class="w-20 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[10px] text-slate-300 font-bold">s/d</span>
            <input type="number" name="range_max" placeholder="~" min="0.01" step="{{ $stepRentangFinishing }}" class="w-20 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[9px] text-slate-400 uppercase shrink-0">{{ $satuanRentangFinishing }}</span>
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

    // Semua aksi tier harga (tambah/edit/hapus) dikirim lewat fetch(),
    // BUKAN submit form biasa - supaya modal tetap terbuka dan tabelnya
    // update di tempat tanpa reload seluruh halaman. Sebelumnya reload
    // penuh tiap satu tier disimpan terasa lambat kalau mau memasukkan
    // beberapa tier berturut-turut.
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function buildFinishingTierRowHtml(t, satuanRentang, stepRentang) {
        return `
            <div class="tier-row-finishing flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2" data-id="${t.id_finishing_harga}">
                <form action="{{ url('/finishing-harga') }}/${t.id_finishing_harga}" class="tier-edit-form-finishing flex items-center gap-2 flex-1">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="number" name="range_min" value="${t.range_min}" min="0.01" step="${stepRentang}" required class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                    <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                    <input type="number" name="range_max" value="${t.range_max ?? ''}" min="0.01" step="${stepRentang}" placeholder="~" class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                    <span class="text-[9px] text-slate-400 uppercase shrink-0">${satuanRentang}</span>
                    <span class="text-[10px] text-slate-400">=</span>
                    <span class="text-[10px] text-slate-400">Rp</span>
                    <input type="number" name="harga" value="${parseInt(t.harga)}" min="0" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold">
                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                        <i class="fas fa-save text-xs"></i>
                    </button>
                </form>
                <button type="button" onclick="confirmHapusTierFinishing(${t.id_finishing_harga})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>`;
    }

    // PENTING: body dikirim sebagai JSON, BUKAN FormData - PHP tidak
    // otomatis mem-parse body multipart/form-data untuk method PATCH/PUT/
    // DELETE (keterbatasan lama PHP, cuma method POST asli yang diurai ke
    // $_POST). Kalau tetap pakai FormData, request PATCH/DELETE akan
    // sampai ke Laravel dengan input KOSONG dan validasi selalu gagal
    // "field is required" walau datanya kelihatan benar di form.
    async function submitTierFormAjax(form, method) {
        const formData = new FormData(form);
        const payload = {};
        formData.forEach((v, k) => { if (k !== '_token') payload[k] = v; });
        try {
            const res = await fetch(form.action, {
                method,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                Swal.fire('Opps!', data.message || 'Gagal menyimpan.', 'error');
                return null;
            }
            return data;
        } catch (e) {
            Swal.fire('Opps!', 'Terjadi kesalahan koneksi.', 'error');
            return null;
        }
    }

    document.addEventListener('submit', async function (e) {
        if (e.target.matches('.tier-add-form-finishing')) {
            e.preventDefault();
            const form = e.target;
            const data = await submitTierFormAjax(form, 'POST');
            if (!data) return;
            const idFinishing = form.dataset.idFinishing;
            const container = document.getElementById('tier-container-finishing-' + idFinishing);
            container.querySelector('.tier-empty-msg-finishing').hidden = true;
            const stepRentang = form.querySelector('[name="range_min"]').step || '1';
            container.insertAdjacentHTML('beforeend', buildFinishingTierRowHtml(data.row, form.dataset.satuanRentang || '', stepRentang));
            form.reset();
            Toast.fire({ icon: 'success', title: data.message });
        } else if (e.target.matches('.tier-edit-form-finishing')) {
            e.preventDefault();
            const data = await submitTierFormAjax(e.target, 'PATCH');
            if (!data) return;
            Toast.fire({ icon: 'success', title: data.message });
        }
    });

    function confirmHapusTierFinishing(id) {
        Swal.fire({
            title: 'Hapus Tier Harga Ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const res = await fetch(`{{ url('/finishing-harga') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    Swal.fire('Opps!', data.message || 'Gagal menghapus.', 'error');
                    return;
                }
                const row = document.querySelector(`.tier-row-finishing[data-id="${id}"]`);
                const container = row.closest('[id^="tier-container-finishing-"]');
                row.remove();
                if (container.querySelectorAll('.tier-row-finishing').length === 0) {
                    container.querySelector('.tier-empty-msg-finishing').hidden = false;
                }
                Toast.fire({ icon: 'success', title: data.message });
            } catch (e) {
                Swal.fire('Opps!', 'Terjadi kesalahan koneksi.', 'error');
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
