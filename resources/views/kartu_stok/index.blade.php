@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Kartu Stok">
        <x-slot:subtitle>
            Bahan Baku &amp; Logistik <span class="mx-1 text-slate-300">/</span> Riwayat Pergerakan Stok per Bahan
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button onclick="openModal('penyesuaian-stok')">
                <i class="fas fa-sliders-h"></i>
                Penyesuaian Stok
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
        <form method="GET" action="{{ route('kartu-stok.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Bahan</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ $search }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik SKU atau Nama Bahan...">
                </div>
            </div>
            <button type="submit" class="h-[46px] px-6 flex items-center justify-center bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
            @if($search)
                <a href="{{ route('kartu-stok.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                    <i class="fas fa-undo-alt text-sm"></i>
                </a>
            @endif
        </form>
    </div>

    <x-table>
        <thead>
            <tr>
                <th class="px-4 py-5 text-center w-10">#</th>
                <th class="px-6 py-5 text-left">Kode</th>
                <th class="px-4 py-5 text-left">Nama Bahan</th>
                <th class="px-4 py-5 text-right">Stok Saat Ini</th>
                <th class="px-4 py-5 text-left">Satuan</th>
                <th class="px-8 py-5 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($bahan as $i => $b)
            <tr class="group hover:bg-slate-50/50 transition-all">
                <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $i + 1 }}</td>
                <td class="px-6 py-5">
                    <span class="text-[12px] font-bold text-slate-700">{{ $b->kode_bahan }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[13px] font-bold text-slate-700 uppercase group-hover:text-indigo-600 transition-colors">{{ $b->nama_bahan }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[13px] font-bold text-slate-800">{{ angka($b->stok, 2) }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-black uppercase tracking-wide">{{ $b->nama_satuan_stok ?? $b->satuan_stok ?? '-' }}</span>
                </td>
                <td class="px-8 py-5 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <x-button href="{{ url('/kartu-stok/'.$b->id_bahan) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-indigo-600" title="Lihat Kartu Stok">
                            <i class="fas fa-book text-xs"></i>
                        </x-button>
                        <x-button type="button" size="icon" variant="ghost" onclick="openPenyesuaian({{ $b->id_bahan }}, {{ json_encode($b->nama_bahan) }}, {{ angka($b->stok, 4) }})" class="border border-slate-100 hover:text-amber-500" title="Penyesuaian Stok">
                            <i class="fas fa-sliders-h text-xs"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <x-empty-state :title="$search ? 'Tidak ada bahan yang cocok dengan pencarian' : 'Belum ada data bahan baku'" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- MODAL PENYESUAIAN STOK --}}
    <x-modal id="penyesuaian-stok" title="Penyesuaian Stok" maxWidth="md">
        <form id="formPenyesuaian" action="{{ route('kartu-stok.adjustment') }}" method="POST" class="space-y-5">
            @csrf
            <x-form.group label="Bahan" name="id_bahan" required>
                <x-form.select name="id_bahan" id="adj_id_bahan" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach($bahan as $b)
                        <option value="{{ $b->id_bahan }}" data-stok="{{ angka($b->stok, 4) }}" data-satuan="{{ $b->nama_satuan_stok ?? $b->satuan_stok }}" data-nama="{{ $b->nama_bahan }}">{{ $b->nama_bahan }} ({{ $b->kode_bahan }})</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <p class="text-[11px] text-slate-400 -mt-3 ml-1">Stok saat ini: <span id="adj_stok_display" class="font-bold text-slate-600">-</span> <span id="adj_satuan_display" class="inline-flex px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase"></span></p>

            <x-form.group label="Jenis Penyesuaian" name="tipe" required>
                <x-form.select name="tipe" id="adj_tipe" onchange="toggleAdjTipe()" required>
                    <option value="OUT">Barang Keluar (rusak, sample, hilang)</option>
                    <option value="ADJUSTMENT">Revisi Stok / Hasil Opname Fisik</option>
                </x-form.select>
            </x-form.group>

            <div id="adj_wrapper_qty">
                <x-form.group label="Qty Keluar (dalam satuan stok)" name="qty">
                    <div class="flex items-center gap-2"><x-form.input type="number" name="qty" id="adj_qty" step="0.01" min="0.01" /><span class="adj-unit shrink-0 px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-black uppercase">-</span></div>
                </x-form.group>
            </div>

            <div id="adj_wrapper_stok_baru" class="hidden">
                <x-form.group label="Stok Sebenarnya (Hasil Hitung Fisik)" name="stok_baru">
                    <div class="flex items-center gap-2"><x-form.input type="number" name="stok_baru" id="adj_stok_baru" step="0.01" min="0" /><span class="adj-unit shrink-0 px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-black uppercase">-</span></div>
                </x-form.group>
            </div>

            <x-form.group label="Keterangan / Alasan" name="keterangan" required>
                <textarea name="keterangan" rows="2" required placeholder="Contoh: Rusak saat produksi, hasil stock opname bulanan, dsb."
                    class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
            </x-form.group>

            <div class="pt-4 flex gap-3">
                <x-button type="button" variant="secondary" onclick="closeModal('penyesuaian-stok')" class="flex-1">Batalkan</x-button>
                <x-button type="submit" variant="warning" class="flex-1">Ajukan Penyesuaian</x-button>
            </div>
        </form>
    </x-modal>

</div>

@push('scripts')
<script>
    function toggleAdjTipe() {
        const tipe = document.getElementById('adj_tipe').value;
        document.getElementById('adj_wrapper_qty').classList.toggle('hidden', tipe !== 'OUT');
        document.getElementById('adj_wrapper_stok_baru').classList.toggle('hidden', tipe !== 'ADJUSTMENT');
    }

    document.getElementById('adj_id_bahan').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const stok = opt.getAttribute('data-stok');
        document.getElementById('adj_stok_display').textContent = stok ? parseFloat(stok).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
        tampilSatuanAdj(opt.getAttribute('data-satuan') || '');
    });

    function tampilSatuanAdj(satuan) {
        document.getElementById('adj_satuan_display').textContent = satuan;
        document.querySelectorAll('.adj-unit').forEach(el => el.textContent = satuan || '-');
    }

    // Konfirmasi sebelum diajukan: tampilkan angka + SATUAN-nya supaya tidak salah input (mis. 5 dus vs 5 pcs).
    document.getElementById('formPenyesuaian').addEventListener('submit', function (e) {
        if (this.__ok) return;
        e.preventDefault();
        const sel = document.getElementById('adj_id_bahan');
        const opt = sel.options[sel.selectedIndex];
        const satuan = opt.getAttribute('data-satuan') || '';
        const tipe = document.getElementById('adj_tipe').value;
        const nilai = parseFloat(tipe === 'OUT' ? document.getElementById('adj_qty').value : document.getElementById('adj_stok_baru').value);
        if (!(nilai >= 0)) { this.reportValidity(); return; }
        const teks = tipe === 'OUT'
            ? `Keluarkan <b>${nilai.toLocaleString('id-ID')} ${satuan}</b> dari <b>${opt.dataset.nama}</b>?`
            : `Set stok <b>${opt.dataset.nama}</b> menjadi <b>${nilai.toLocaleString('id-ID')} ${satuan}</b>?`;
        Swal.fire({ title: 'Cek satuan sebelum lanjut', html: teks + `<br><small class="text-slate-400">Satuan stok bahan ini: ${satuan}. Pastikan angka BUKAN dalam satuan lain.</small>`, icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, benar', cancelButtonText: 'Periksa lagi' })
            .then(r => { if (r.isConfirmed) { this.__ok = true; this.requestSubmit(); } });
    });

    function openPenyesuaian(idBahan, namaBahan, stok) {
        document.getElementById('adj_id_bahan').value = idBahan;
        document.getElementById('adj_stok_display').textContent = parseFloat(stok).toLocaleString('id-ID', {minimumFractionDigits: 2});
        tampilSatuanAdj(document.getElementById('adj_id_bahan').selectedOptions[0]?.getAttribute('data-satuan') || '');
        document.getElementById('adj_tipe').value = 'OUT';
        toggleAdjTipe();
        openModal('penyesuaian-stok');
    }
</script>
@endpush
@endsection
