@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Price List & Level" subtitle="Harga jual produk per golongan konsumen, bisa bertingkat berdasarkan qty.">
        <x-slot:actions>
            <x-button href="{{ route('harga-promo.index') }}" variant="secondary">
                <i class="fas fa-fire"></i>
                Harga Promo
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <x-card class="mb-6">
        <form action="{{ route('setting-harga.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Produk</label>
                <x-form.input type="text" name="search" value="{{ request('search') }}" placeholder="Nama produk..." />
            </div>

            <div class="w-full md:w-52">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Mesin/Divisi</label>
                <x-form.select name="category">
                    <option value="">Semua Mesin</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_mesin }}" {{ request('category') == $cat->id_mesin ? 'selected' : '' }}>{{ strtoupper($cat->nama_mesin) }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['search', 'category']))
                <a href="{{ route('setting-harga.index') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Produk</th>
                @foreach($golongan as $g)
                <th class="px-4 py-5 text-center">{{ $g->nama_golongan }}</th>
                @endforeach
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($produk as $p)
            @php
                $hargaProduk = $all_harga[$p->id_produk] ?? collect();
                $satuanRentangRow = $p->metode_hitung === 'ukuran' ? 'm²' : 'Pcs';
                // rtrim ganda: buang nol desimal berlebih (2.00 -> 2, 0.50 -> 0.5)
                // supaya tier bilangan bulat tetap tampil bersih, bukan "2.00".
                $fmtRentang = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
            @endphp
            <tr class="group hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <div class="text-[13px] font-bold text-slate-700 uppercase">{{ $p->nama_produk }}</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase border border-indigo-100">
                            <i class="fas fa-print mr-1"></i>{{ $p->nama_mesin ?? 'Tanpa Mesin' }}
                        </span>
                        <span class="text-[9px] text-slate-400 font-medium italic">By {{ $p->metode_hitung }} ({{ $satuanRentangRow }})</span>
                    </div>
                </td>

                @foreach($golongan as $g)
                <td class="px-4 py-5 text-center">
                    @php $tiersGol = $hargaProduk->where('id_golongan', $g->id_golongan); @endphp
                    <span id="harga-summary-{{ $p->id_produk }}-{{ $g->id_golongan }}">
                        @if($tiersGol->isEmpty())
                            <span class="text-slate-300 italic text-[10px]">Belum Diatur</span>
                        @elseif($tiersGol->count() === 1)
                            @php $satu = $tiersGol->first(); @endphp
                            <span class="text-slate-700 font-semibold">Rp{{ number_format($satu->harga_jual, 0, ',', '.') }}</span>
                            @if($satu->range_min > 0.01 || $satu->range_max)
                                <div class="text-[9px] text-slate-400">{{ $fmtRentang($satu->range_min) }}-{{ $satu->range_max !== null ? $fmtRentang($satu->range_max) : '∞' }} {{ $satuanRentangRow }}</div>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">{{ $tiersGol->count() }} TIER</span>
                            <div class="text-[9px] text-slate-400 mt-0.5">Rp{{ number_format($tiersGol->min('harga_jual'), 0, ',', '.') }} - Rp{{ number_format($tiersGol->max('harga_jual'), 0, ',', '.') }}</div>
                        @endif
                    </span>
                </td>
                @endforeach

                <td class="px-8 py-5 text-right">
                    <x-button size="icon" variant="ghost" onclick="openModal('harga-produk-{{ $p->id_produk }}')" class="hover:text-indigo-600" title="Kelola Harga">
                        <i class="fas fa-sliders-h text-sm"></i>
                    </x-button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($golongan) + 2 }}">
                    <x-empty-state title="Produk tidak ditemukan" description="Sesuaikan filter di atas." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$produk" />

    {{-- MODAL KELOLA HARGA (satu per produk - server-rendered, setiap tier
         punya form CRUD sendiri dengan id_harga asli, add/edit/hapus satu
         tier TIDAK PERNAH menyentuh tier lain sama sekali). --}}
    @foreach($produk as $p)
    @php
        $hargaProdukModal = $all_harga[$p->id_produk] ?? collect();
        // Satuan rentang Min/Max HARUS eksplisit di sini - sebelumnya cuma
        // angka polos tanpa keterangan, admin yang mengisi tidak tahu apakah
        // "1" itu 1 m² atau 1 pcs. Ikut metode_hitung produk yang sama
        // seperti dipakai HargaService::getHargaEfektif() menghitung qty
        // sesungguhnya - m² untuk 'ukuran', Pcs untuk 'quantity'.
        $satuanRentang = $p->metode_hitung === 'ukuran' ? 'm²' : 'Pcs';
        $stepRentang = $p->metode_hitung === 'ukuran' ? '0.01' : '1';
    @endphp
    <x-modal id="harga-produk-{{ $p->id_produk }}" title="Kelola Harga: {{ $p->nama_produk }}" maxWidth="3xl">
        <div class="mb-4 p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500">
            Tiap baris berlaku untuk satu golongan &amp; rentang <b>{{ $satuanRentang }}</b> tertentu
            @if($p->metode_hitung === 'ukuran')
                (luas cetak = panjang &times; lebar, mis. stiker 30&times;30cm = 0,09 m² - boleh pecahan)
            @endif
            . Kosongkan <b>Max</b> untuk rentang tak terbatas ("{{ $satuanRentang }} X ke atas"). Harga dipilih otomatis di Kasir Utama berdasarkan golongan konsumen &amp; {{ $satuanRentang }} yang diinput.
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-[10px] uppercase text-slate-400 border-b border-slate-100">
                        <th class="text-left py-2 pr-2">Golongan</th>
                        <th class="text-center py-2 px-2 w-20">Min ({{ $satuanRentang }})</th>
                        <th class="text-center py-2 px-2 w-20">Max ({{ $satuanRentang }})</th>
                        <th class="text-left py-2 px-2">Harga (Rp)</th>
                        <th class="w-20"></th>
                    </tr>
                </thead>
                <tbody id="tier-tbody-produk-{{ $p->id_produk }}" class="divide-y divide-slate-50">
                    <tr class="tier-empty-msg-produk" {{ $hargaProdukModal->isNotEmpty() ? 'hidden' : '' }}>
                        <td colspan="5" class="py-2 text-xs text-slate-400 italic">Belum ada harga diatur - order dengan produk ini akan ditolak di Kasir sampai minimal satu tier diisi.</td>
                    </tr>
                    @foreach($hargaProdukModal as $t)
                    @php $namaGolonganTier = optional($golongan->firstWhere('id_golongan', $t->id_golongan))->nama_golongan ?? '?'; @endphp
                    <tr class="tier-row-produk" data-id="{{ $t->id_harga }}" data-id-golongan="{{ $t->id_golongan }}">
                        <td colspan="5" class="py-1">
                            <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2">
                                <form action="{{ route('setting-harga.tier.update', $t->id_harga) }}" class="tier-edit-form-produk flex items-center gap-2 flex-1">
                                    @csrf
                                    <span class="w-28 shrink-0 text-[11px] font-bold text-slate-600 uppercase truncate" title="{{ $namaGolonganTier }}">{{ $namaGolonganTier }}</span>
                                    <input type="number" name="range_min" value="{{ rtrim(rtrim(number_format($t->range_min, 2, '.', ''), '0'), '.') }}" min="0.01" step="{{ $stepRentang }}" required class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                                    <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                                    <input type="number" name="range_max" value="{{ $t->range_max !== null ? rtrim(rtrim(number_format($t->range_max, 2, '.', ''), '0'), '.') : '' }}" min="0.01" step="{{ $stepRentang }}" placeholder="~" class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                                    <span class="text-[9px] text-slate-400 uppercase shrink-0">{{ $satuanRentang }}</span>
                                    <span class="text-[10px] text-slate-400">Rp</span>
                                    <input type="number" name="harga_jual" value="{{ (int) $t->harga_jual }}" min="0" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold">
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                                        <i class="fas fa-save text-xs"></i>
                                    </button>
                                </form>
                                <button type="button" onclick="confirmHapusTierHarga({{ $t->id_harga }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('setting-harga.tier.store', $p->id_produk) }}" class="tier-add-form-produk flex items-center gap-2 bg-emerald-50/50 border border-dashed border-emerald-200 rounded-xl p-2" data-id-produk="{{ $p->id_produk }}" data-satuan-rentang="{{ $satuanRentang }}">
            @csrf
            <select name="id_golongan" required class="w-28 shrink-0 text-[11px] bg-white border-none rounded-lg py-2 px-1 font-bold text-slate-600">
                @foreach($golongan as $g)
                    <option value="{{ $g->id_golongan }}">{{ $g->nama_golongan }}</option>
                @endforeach
            </select>
            <input type="number" name="range_min" placeholder="Min" value="{{ $p->metode_hitung === 'ukuran' ? '0.01' : '1' }}" min="0.01" step="{{ $stepRentang }}" required class="w-20 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[10px] text-slate-300 font-bold">s/d</span>
            <input type="number" name="range_max" placeholder="~" min="0.01" step="{{ $stepRentang }}" class="w-20 text-xs text-center bg-white border-none rounded-lg py-2">
            <span class="text-[9px] text-slate-400 uppercase shrink-0">{{ $satuanRentang }}</span>
            <span class="text-[10px] text-slate-400">Rp</span>
            <input type="number" name="harga_jual" placeholder="0" min="0" required class="flex-1 text-xs bg-white border-none rounded-lg py-2 font-bold">
            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition-all shrink-0" title="Tambah Tier">
                <i class="fas fa-plus text-xs"></i>
            </button>
        </form>

        <div class="pt-4 flex justify-end">
            <x-button type="button" variant="secondary" onclick="closeModal('harga-produk-{{ $p->id_produk }}')">Tutup</x-button>
        </div>
    </x-modal>
    @endforeach

</div>

@push('scripts')
<script>
    // Aksi tier harga (tambah/edit/hapus) dikirim lewat fetch() dengan body
    // JSON - modal tetap terbuka, tabel update di tempat tanpa reload
    // halaman (sebelumnya submit form biasa = reload penuh tiap satu tier,
    // lambat kalau mau memasukkan beberapa tier berturut-turut). Body
    // HARUS JSON (bukan FormData/multipart) untuk method PATCH/DELETE -
    // PHP tidak otomatis mem-parse body multipart untuk method selain POST
    // asli, jadi kalau tetap FormData request sampai ke Laravel dengan
    // input kosong dan validasi selalu gagal walau datanya kelihatan benar.
    //
    const csrfTokenHarga = document.querySelector('meta[name="csrf-token"]').content;
    const namaGolonganMap = {
        @foreach($golongan as $g)
            {{ $g->id_golongan }}: @json($g->nama_golongan),
        @endforeach
    };

    function buildHargaTierRowHtml(t, satuanRentang, stepRentang) {
        const namaGol = namaGolonganMap[t.id_golongan] ?? '?';
        return `
            <tr class="tier-row-produk" data-id="${t.id_harga}" data-id-golongan="${t.id_golongan}">
                <td colspan="5" class="py-1">
                    <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl p-2">
                        <form action="{{ url('/setting-harga-tier') }}/${t.id_harga}" class="tier-edit-form-produk flex items-center gap-2 flex-1">
                            <input type="hidden" name="_token" value="${csrfTokenHarga}">
                            <span class="w-28 shrink-0 text-[11px] font-bold text-slate-600 uppercase truncate" title="${namaGol}">${namaGol}</span>
                            <input type="number" name="range_min" value="${t.range_min}" min="0.01" step="${stepRentang}" required class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                            <span class="text-[10px] text-slate-300 font-bold">s/d</span>
                            <input type="number" name="range_max" value="${t.range_max ?? ''}" min="0.01" step="${stepRentang}" placeholder="~" class="w-20 text-xs text-center bg-slate-50 border-none rounded-lg py-2">
                            <span class="text-[9px] text-slate-400 uppercase shrink-0">${satuanRentang}</span>
                            <span class="text-[10px] text-slate-400">Rp</span>
                            <input type="number" name="harga_jual" value="${parseInt(t.harga_jual)}" min="0" required class="flex-1 text-xs bg-slate-50 border-none rounded-lg py-2 font-bold">
                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shrink-0" title="Simpan">
                                <i class="fas fa-save text-xs"></i>
                            </button>
                        </form>
                        <button type="button" onclick="confirmHapusTierHarga(${t.id_harga})" class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-400 hover:bg-rose-50 transition-all shrink-0" title="Hapus tier ini">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
    }

    // Kolom ringkasan di TABEL UTAMA (di belakang modal) dihitung ulang
    // dari baris-baris yang SEDANG ada di modal untuk produk+golongan
    // terkait - dipanggil setiap kali tambah/edit/hapus tier sukses, supaya
    // begitu modal ditutup tabel utama sudah akurat tanpa perlu reload
    // halaman sama sekali.
    function refreshHargaSummary(idProduk, idGolongan) {
        const cell = document.getElementById(`harga-summary-${idProduk}-${idGolongan}`);
        if (!cell) return;

        const tbody = document.getElementById('tier-tbody-produk-' + idProduk);
        const rows = Array.from(tbody.querySelectorAll(`.tier-row-produk[data-id-golongan="${idGolongan}"]`));
        // Satuan rentang disimpan di form Tambah tier produk ini (data-satuan-
        // rentang) - dibaca dari sana supaya ringkasan di tabel utama juga
        // eksplisit menyebut m²/Pcs, bukan kata "qty" generik yang ambigu.
        const satuanRentang = document.querySelector(`.tier-add-form-produk[data-id-produk="${idProduk}"]`)?.dataset.satuanRentang || '';

        if (rows.length === 0) {
            cell.innerHTML = '<span class="text-slate-300 italic text-[10px]">Belum Diatur</span>';
            return;
        }

        const hargaList = rows.map(r => parseFloat(r.querySelector('[name="harga_jual"]').value) || 0);
        const fmt = (n) => 'Rp' + new Intl.NumberFormat('id-ID').format(n);

        if (rows.length === 1) {
            const min = rows[0].querySelector('[name="range_min"]').value;
            const max = rows[0].querySelector('[name="range_max"]').value;
            let html = `<span class="text-slate-700 font-semibold">${fmt(hargaList[0])}</span>`;
            if (parseFloat(min) > 0.01 || max) {
                html += `<div class="text-[9px] text-slate-400">${min}-${max || '&infin;'} ${satuanRentang}</div>`;
            }
            cell.innerHTML = html;
        } else {
            const minHarga = Math.min(...hargaList);
            const maxHarga = Math.max(...hargaList);
            cell.innerHTML = `
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">${rows.length} TIER</span>
                <div class="text-[9px] text-slate-400 mt-0.5">${fmt(minHarga)} - ${fmt(maxHarga)}</div>`;
        }
    }

    async function submitHargaTierFormAjax(form, method) {
        const formData = new FormData(form);
        const payload = {};
        formData.forEach((v, k) => { if (k !== '_token') payload[k] = v; });
        try {
            const res = await fetch(form.action, {
                method,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfTokenHarga },
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
        if (e.target.matches('.tier-add-form-produk')) {
            e.preventDefault();
            const form = e.target;
            const data = await submitHargaTierFormAjax(form, 'POST');
            if (!data) return;
            const idProduk = form.dataset.idProduk;
            const tbody = document.getElementById('tier-tbody-produk-' + idProduk);
            tbody.querySelector('.tier-empty-msg-produk').hidden = true;
            const stepRentang = form.querySelector('[name="range_min"]').step || '1';
            tbody.insertAdjacentHTML('beforeend', buildHargaTierRowHtml(data.row, form.dataset.satuanRentang || '', stepRentang));
            const golSelect = form.querySelector('[name="id_golongan"]');
            form.reset();
            golSelect.selectedIndex = 0;
            refreshHargaSummary(idProduk, data.row.id_golongan);
            Toast.fire({ icon: 'success', title: data.message });
        } else if (e.target.matches('.tier-edit-form-produk')) {
            e.preventDefault();
            const row = e.target.closest('.tier-row-produk');
            const data = await submitHargaTierFormAjax(e.target, 'PATCH');
            if (!data) return;
            const idProduk = row.closest('tbody').id.replace('tier-tbody-produk-', '');
            refreshHargaSummary(idProduk, row.dataset.idGolongan);
            Toast.fire({ icon: 'success', title: data.message });
        }
    });

    function confirmHapusTierHarga(id) {
        Swal.fire({
            title: 'Hapus Tier Harga Ini?',
            text: 'Order dengan qty di rentang ini tidak akan punya harga lagi sampai diatur ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color: #64748b">Batal</span>',
            confirmButtonText: 'Ya, Hapus!'
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const res = await fetch(`{{ url('/setting-harga-tier') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfTokenHarga },
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    Swal.fire('Opps!', data.message || 'Gagal menghapus.', 'error');
                    return;
                }
                const row = document.querySelector(`.tier-row-produk[data-id="${id}"]`);
                const tbody = row.closest('tbody');
                const idProduk = tbody.id.replace('tier-tbody-produk-', '');
                const idGolongan = row.dataset.idGolongan;
                row.remove();
                if (tbody.querySelectorAll('.tier-row-produk').length === 0) {
                    tbody.querySelector('.tier-empty-msg-produk').hidden = false;
                }
                refreshHargaSummary(idProduk, idGolongan);
                Toast.fire({ icon: 'success', title: data.message });
            } catch (e) {
                Swal.fire('Opps!', 'Terjadi kesalahan koneksi.', 'error');
            }
        });
    }
</script>
@endpush
@endsection
