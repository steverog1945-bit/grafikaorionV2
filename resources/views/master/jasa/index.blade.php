@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Master Jasa" subtitle="Layanan non-material (mis. instalasi, laminasi luar).">
        <x-slot:actions>
            <x-button onclick="toggleModal('modalJasa')">
                <i class="fas fa-concierge-bell"></i>
                Tambah Jasa
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('jasa.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Jasa</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama jasa...">
                </div>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Unit Hitung</label>
                <select name="id_satuan" class="select2-filter w-full">
                    <option value="">Semua Satuan</option>
                    @foreach($satuan as $s)
                        <option value="{{ $s->id_satuan }}" {{ request('id_satuan') == $s->id_satuan ? 'selected' : '' }}>{{ $s->nama_satuan }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ route('jasa.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE CONTAINER --}}
    <x-table>
        <thead>
            <tr>
                        <th class="px-4 py-5 text-center w-10">#</th>
                        <th class="px-8 py-5 text-left w-24">ID</th>
                        <th class="px-6 py-5 text-left">Nama Jasa</th>
                        <th class="px-6 py-5 text-center">Unit Hitung</th>
                        <th class="px-6 py-5 text-right">Harga</th>
                        <th class="px-6 py-5 text-left">Keterangan</th>
                        <th class="px-8 py-5 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($jasa as $i => $j)
                    <tr class="group hover:bg-slate-50/30 transition-all">
                        <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $jasa->firstItem() + $i }}</td>
                        <td class="px-8 py-5 text-xs text-slate-400">#{{ $j->id_jasa }}</td>
                        <td class="px-6 py-5">
                            <span class="text-sm font-bold text-slate-700 uppercase">{{ $j->nama_jasa }}</span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-600 uppercase">
                                {{ $j->nama_satuan ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            @php $tierCount = ($hargaTiers[$j->id_jasa] ?? collect())->count(); @endphp
                            <span id="jasa-harga-summary-{{ $j->id_jasa }}">
                                @if($tierCount > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">BERTINGKAT ({{ $tierCount }})</span>
                                @elseif($j->harga_default)
                                    <span class="text-sm font-bold text-slate-700">Rp {{ number_format($j->harga_default, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-[10px] text-slate-300 italic">Manual di Kasir</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-sm text-slate-500 leading-relaxed truncate max-w-xs">
                                {{ $j->keterangan ?? 'Tidak ada deskripsi tambahan' }}
                            </p>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="openModal('harga-jasa-{{ $j->id_jasa }}')"
                                        class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded-lg transition-all" title="Harga Bertingkat per Qty">
                                    <i class="fas fa-layer-group text-sm"></i>
                                </button>
                                <button onclick="openEditJasa({{ json_encode($j) }})"
                                        class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                                    <i class="far fa-edit text-sm"></i>
                                </button>

                                <form id="delete-form-jasa-{{ $j->id_jasa }}" action="{{ route('jasa.destroy', $j->id_jasa) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" onclick="confirmDeleteJasa('{{ $j->id_jasa }}', {{ json_encode($j->nama_jasa) }})"
                                        class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center text-slate-400 font-medium italic">Belum ada jasa yang terdaftar.</td>
                    </tr>
                    @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$jasa" />
</div>

{{-- MODAL TAMBAH (Indigo) --}}
<div id="modalJasa" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">

            <div class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle"></i>
                    <h3 class="text-xl font-bold tracking-tight">Tambah Jasa</h3>
                </div>
                <button onclick="toggleModal('modalJasa')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form action="{{ route('jasa.store') }}" method="POST" class="py-12 space-y-8">
                @csrf
                <div class="space-y-6">
                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Nama Jasa</label>
                        <input type="text" name="nama_jasa" placeholder="Contoh: Instalasi, Laminasi Luar" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Unit Hitung</label>
                        <select name="id_satuan"
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center appearance-none">
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuan as $s)
                                <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Harga Dasar (Rp) <span class="normal-case font-normal text-slate-300">- opsional, starting point di Kasir</span></label>
                        <input type="text" inputmode="numeric" name="harga_default" placeholder="0"
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center format-ribuan">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Keterangan Singkat</label>
                        <textarea name="keterangan" placeholder="Keterangan jasa..."
                                  class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center"></textarea>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100">Simpan Data</button>
                    <button type="button" onclick="toggleModal('modalJasa')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT (Amber) --}}
<div id="modalEditJasa" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">

            <div class="px-10 py-8 bg-amber-500 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-edit"></i>
                    <h3 class="text-xl font-bold tracking-tight">Edit Jasa</h3>
                </div>
                <button onclick="toggleModal('modalEditJasa')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="formEditJasa" method="POST" class="py-12 space-y-8">
                @csrf @method('PUT')
                <div class="space-y-6">
                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Nama Jasa</label>
                        <input type="text" name="nama_jasa" id="edit_nama_jasa" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Unit Hitung</label>
                        <select name="id_satuan" id="edit_id_satuan"
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center appearance-none">
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuan as $s)
                                <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Harga Dasar (Rp) <span class="normal-case font-normal text-slate-300">- opsional, starting point di Kasir</span></label>
                        <input type="text" inputmode="numeric" name="harga_default" id="edit_harga_default" placeholder="0"
                               class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center format-ribuan">
                    </div>

                    <div class="max-w-[85%] mx-auto">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1 text-left">Keterangan Singkat</label>
                        <textarea name="keterangan" id="edit_keterangan"
                                  class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-inner text-center"></textarea>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-amber-600 shadow-lg shadow-amber-100">Update Data</button>
                    <button type="button" onclick="toggleModal('modalEditJasa')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL HARGA BERTINGKAT (satu per jasa - pola sama seperti Finishing,
     server-rendered per baris supaya add/edit/hapus satu tier tidak pernah
     menyentuh tier lain). --}}
@foreach($jasa as $j)
<x-modal id="harga-jasa-{{ $j->id_jasa }}" title="Harga Bertingkat: {{ $j->nama_jasa }}" maxWidth="lg">
    <div class="mb-4 p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500">
        @if($j->harga_default)
            Harga Dasar (<b>Rp {{ number_format($j->harga_default, 0, ',', '.') }}</b>) dipakai kalau qty tidak cocok tier manapun di bawah, atau belum ada tier sama sekali.
        @else
            Belum ada Harga Dasar diatur - tanpa tier yang cocok, kasir tetap input manual seperti biasa.
        @endif
        Kasir tetap bisa menimpa manual di Input Order - ini hanya acuan/starting point.
    </div>

    @php $tiersJ = $hargaTiers[$j->id_jasa] ?? collect(); @endphp

    <div id="tier-container-jasa-{{ $j->id_jasa }}" class="space-y-2 mb-4" data-harga-default="{{ $j->harga_default ?? '' }}">
        <p class="text-xs text-slate-400 italic tier-empty-msg-jasa" {{ $tiersJ->isNotEmpty() ? 'hidden' : '' }}>Belum ada tier harga.</p>
        @foreach($tiersJ as $t)
        <div class="tier-row-jasa flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2" data-id="{{ $t->id_jasa_harga }}">
            <form action="{{ route('jasa.harga.update', $t->id_jasa_harga) }}" class="tier-edit-form-jasa flex items-center gap-2 flex-1">
                @csrf
                <input type="number" name="range_min" value="{{ $t->range_min }}" min="1" required class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                <input type="number" name="range_max" value="{{ $t->range_max }}" min="1" placeholder="~" class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                <span class="text-[10px] text-slate-400">=</span>
                <span class="text-[10px] text-slate-400">Rp</span>
                <input type="text" inputmode="numeric" name="harga" value="{{ number_format((int) $t->harga, 0, '', '.') }}" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold format-ribuan">
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                    <i class="fas fa-save text-xs"></i>
                </button>
            </form>
            <button type="button" onclick="confirmHapusTierJasa({{ $t->id_jasa_harga }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                <i class="fas fa-trash-alt text-xs"></i>
            </button>
        </div>
        @endforeach
    </div>

    <form action="{{ route('jasa.harga.store', $j->id_jasa) }}" class="tier-add-form-jasa flex items-center gap-2 bg-emerald-50/50 border border-dashed border-emerald-200 rounded-xl p-2" data-id-jasa="{{ $j->id_jasa }}">
        @csrf
        <input type="number" name="range_min" placeholder="Min" min="1" required class="w-16 text-xs text-center bg-white border-none rounded-lg py-2">
        <span class="text-[10px] text-slate-300 font-bold">s/d</span>
        <input type="number" name="range_max" placeholder="~" min="1" class="w-16 text-xs text-center bg-white border-none rounded-lg py-2">
        <span class="text-[10px] text-slate-400">=</span>
        <span class="text-[10px] text-slate-400">Rp</span>
        <input type="text" inputmode="numeric" name="harga" placeholder="0" required class="flex-1 text-xs bg-white border-none rounded-lg py-2 font-bold format-ribuan">
        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition-all shrink-0" title="Tambah Tier">
            <i class="fas fa-plus text-xs"></i>
        </button>
    </form>

    <div class="pt-4 flex justify-end">
        <x-button type="button" variant="secondary" onclick="closeModal('harga-jasa-{{ $j->id_jasa }}')">Tutup</x-button>
    </div>
</x-modal>
@endforeach

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-filter').select2({ width: '100%' }).on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endpush

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }
    }

    function openEditJasa(data) {
        const form = document.getElementById('formEditJasa');
        form.action = `{{ url('/jasa') }}/${data.id_jasa}`;
        document.getElementById('edit_nama_jasa').value = data.nama_jasa;
        document.getElementById('edit_id_satuan').value = data.id_satuan || '';
        document.getElementById('edit_harga_default').value = data.harga_default ? formatRupiahDariAngka(data.harga_default) : '';
        document.getElementById('edit_keterangan').value = data.keterangan || '';
        toggleModal('modalEditJasa');
    }

    // Aksi tier harga (tambah/edit/hapus) dikirim lewat fetch() dengan body
    // JSON - modal tetap terbuka, tabel update di tempat tanpa reload
    // halaman. Body HARUS JSON (bukan FormData/multipart) untuk method
    // PATCH/DELETE - PHP tidak otomatis mem-parse body multipart untuk
    // method selain POST asli, jadi kalau tetap FormData request akan
    // sampai ke Laravel dengan input kosong dan validasi selalu gagal.
    const csrfTokenJasa = document.querySelector('meta[name="csrf-token"]').content;

    function buildJasaTierRowHtml(t) {
        return `
            <div class="tier-row-jasa flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2" data-id="${t.id_jasa_harga}">
                <form action="{{ url('/jasa-harga') }}/${t.id_jasa_harga}" class="tier-edit-form-jasa flex items-center gap-2 flex-1">
                    <input type="hidden" name="_token" value="${csrfTokenJasa}">
                    <input type="number" name="range_min" value="${t.range_min}" min="1" required class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                    <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                    <input type="number" name="range_max" value="${t.range_max ?? ''}" min="1" placeholder="~" class="w-16 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                    <span class="text-[10px] text-slate-400">=</span>
                    <span class="text-[10px] text-slate-400">Rp</span>
                    <input type="text" inputmode="numeric" name="harga" value="${formatRupiah(parseInt(t.harga))}" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold format-ribuan">
                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                        <i class="fas fa-save text-xs"></i>
                    </button>
                </form>
                <button type="button" onclick="confirmHapusTierJasa(${t.id_jasa_harga})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>`;
    }

    // Kolom "Harga" di TABEL UTAMA (di belakang modal) dihitung ulang dari
    // baris-baris yang SEDANG ada di modal - dipanggil tiap kali tambah/
    // edit/hapus tier sukses, supaya begitu modal ditutup tabel utama sudah
    // akurat tanpa perlu reload halaman.
    function refreshJasaSummary(idJasa) {
        const cell = document.getElementById('jasa-harga-summary-' + idJasa);
        const container = document.getElementById('tier-container-jasa-' + idJasa);
        if (!cell || !container) return;

        const tierCount = container.querySelectorAll('.tier-row-jasa').length;
        const hargaDefault = container.dataset.hargaDefault;

        if (tierCount > 0) {
            cell.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">BERTINGKAT (${tierCount})</span>`;
        } else if (hargaDefault) {
            cell.innerHTML = `<span class="text-sm font-bold text-slate-700">Rp ${new Intl.NumberFormat('id-ID').format(hargaDefault)}</span>`;
        } else {
            cell.innerHTML = '<span class="text-[10px] text-slate-300 italic">Manual di Kasir</span>';
        }
    }

    async function submitJasaTierFormAjax(form, method) {
        const formData = new FormData(form);
        const payload = {};
        formData.forEach((v, k) => { if (k !== '_token') payload[k] = v; });
        try {
            const res = await fetch(form.action, {
                method,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfTokenJasa },
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
        if (e.target.matches('.tier-add-form-jasa')) {
            e.preventDefault();
            const form = e.target;
            const data = await submitJasaTierFormAjax(form, 'POST');
            if (!data) return;
            const idJasa = form.dataset.idJasa;
            const container = document.getElementById('tier-container-jasa-' + idJasa);
            container.querySelector('.tier-empty-msg-jasa').hidden = true;
            container.insertAdjacentHTML('beforeend', buildJasaTierRowHtml(data.row));
            form.reset();
            refreshJasaSummary(idJasa);
            Toast.fire({ icon: 'success', title: data.message });
        } else if (e.target.matches('.tier-edit-form-jasa')) {
            e.preventDefault();
            const data = await submitJasaTierFormAjax(e.target, 'PATCH');
            if (!data) return;
            Toast.fire({ icon: 'success', title: data.message });
        }
    });

    function confirmHapusTierJasa(id) {
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
                const res = await fetch(`{{ url('/jasa-harga') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfTokenJasa },
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    Swal.fire('Opps!', data.message || 'Gagal menghapus.', 'error');
                    return;
                }
                const row = document.querySelector(`.tier-row-jasa[data-id="${id}"]`);
                const container = row.closest('[id^="tier-container-jasa-"]');
                const idJasa = container.id.replace('tier-container-jasa-', '');
                row.remove();
                if (container.querySelectorAll('.tier-row-jasa').length === 0) {
                    container.querySelector('.tier-empty-msg-jasa').hidden = false;
                }
                refreshJasaSummary(idJasa);
                Toast.fire({ icon: 'success', title: data.message });
            } catch (e) {
                Swal.fire('Opps!', 'Terjadi kesalahan koneksi.', 'error');
            }
        });
    }

    function confirmDeleteJasa(id, nama) {
        Swal.fire({
            title: 'Hapus Jasa?',
            html: `Jasa <b>${nama}</b> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f8fafc',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-jasa-${id}`).submit();
            }
        });
    }
</script>
@endsection
