@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Produksi">
        <x-slot:subtitle>
            Produksi &amp; Logistik <span class="mx-1 text-slate-300">/</span> Progres Pengerjaan SPK
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ route('spk.index') }}" variant="secondary">
                <i class="fas fa-list-check"></i>
                Lihat SPK
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('produksi.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Konsumen</label>
                <x-form.input type="text" name="konsumen" value="{{ request('konsumen') }}" placeholder="Nama konsumen..." />
            </div>

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">ID SPK</label>
                <x-form.input type="text" name="id_spk" value="{{ request('id_spk') }}" placeholder="#" />
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Order Dari</label>
                <x-form.input type="date" name="tgl_dari" value="{{ request('tgl_dari') }}" />
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Order Sampai</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ request('tgl_sampai') }}" />
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Produksi</label>
                <x-form.select name="status_produksi">
                    <option value="progress" {{ request('status_produksi', 'progress') === 'progress' ? 'selected' : '' }}>On Progress</option>
                    <option value="selesai" {{ request('status_produksi') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="pending" {{ request('status_produksi') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="batal" {{ request('status_produksi') === 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['konsumen', 'id_spk', 'tgl_dari', 'tgl_sampai']) || (request()->filled('status_produksi') && request('status_produksi') !== 'progress'))
                <a href="{{ route('produksi.index') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-4 py-5 text-left">Nama File</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-4 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-center">Urgent</th>
                <th class="px-4 py-5 text-center">Qty Order</th>
                <th class="px-4 py-5 text-center">Qty Cetak</th>
                <th class="px-4 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($produksi as $item)
            <tr class="group hover:bg-slate-50/50 transition-all {{ $item->status_urgent ? 'bg-rose-50/40' : '' }}">
                <td class="px-4 py-5">
                    <div class="text-[13px] font-bold text-slate-700 uppercase">{{ $item->nama_file }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->nama_produk }} &middot; SPK #{{ $item->id_detail }}</div>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-bold text-slate-700 uppercase">{{ $item->nama_konsumen }}</span>
                    @if($item->divisi)
                        <div class="text-[10px] text-slate-400">{{ $item->divisi }}</div>
                    @endif
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($item->tgl_order)->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    @if($item->status_urgent)
                        <i class="fas fa-fire text-rose-500" title="Urgent"></i>
                    @else
                        <span class="text-slate-200">-</span>
                    @endif
                </td>
                <td class="px-4 py-5 text-center">
                    <span class="text-[13px] font-bold text-slate-700">{{ $item->qty }}</span>
                </td>
                <td class="px-4 py-5 text-center">
                    @if(in_array($item->status_spk, ['Selesai', 'Batal', 'Pending']))
                        <span class="text-[13px] font-bold text-slate-700">{{ $item->qty_cetak ?? '-' }}</span>
                    @else
                        <form method="POST" action="{{ route('produksi.simpan-qty', $item->id_detail) }}" class="flex items-center justify-center gap-1">
                            @csrf @method('PATCH')
                            <input type="number" name="qty_cetak" min="0" value="{{ $item->qty_cetak ?? $item->qty }}"
                                class="w-20 bg-slate-50 border-none rounded-lg py-1.5 px-2 text-sm font-bold text-slate-700 text-center focus:ring-2 focus:ring-indigo-500/20">
                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all" title="Simpan jumlah produksi">
                                <i class="fas fa-save text-xs"></i>
                            </button>
                        </form>
                    @endif
                </td>
                <td class="px-4 py-5 text-center">
                    <x-badge :status="$item->status_spk" />
                </td>
                <td class="px-8 py-5 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <x-button type="button" size="icon" variant="ghost" onclick="showCatatan({{ $item->id_detail }}, {{ json_encode($item->catatan_produksi) }})" class="border border-slate-100 hover:text-indigo-600" title="Detail / Catatan">
                            <i class="fas fa-eye text-xs"></i>
                        </x-button>

                        <x-button href="{{ route('spk.print', ['id' => $item->id_detail, 'format' => 'thermal']) }}" target="_blank" size="icon" variant="ghost" class="border border-slate-100 hover:text-slate-800" title="Cetak SPK (Thermal)">
                            <i class="fas fa-print text-xs"></i>
                        </x-button>

                        @if($item->status_spk === 'Pending')
                            <x-button href="{{ route('spk.index', ['status_spk' => 'Pending']) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-amber-600" title="Selesaikan keputusan di halaman SPK">
                                <i class="fas fa-clipboard-list text-xs"></i>
                            </x-button>
                        @elseif(!in_array($item->status_spk, ['Selesai', 'Batal']))
                            <x-button type="button" size="icon" variant="ghost" onclick="selesaiProduksi({{ $item->id_detail }})" class="border border-slate-100 hover:text-emerald-600" title="Tandai Selesai">
                                <i class="fas fa-check text-xs"></i>
                            </x-button>
                            <x-button type="button" size="icon" variant="ghost" onclick="cancelSpk({{ $item->id_detail }})" class="border border-slate-100 hover:text-rose-600" title="Batalkan SPK">
                                <i class="fas fa-ban text-xs"></i>
                            </x-button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <x-empty-state title="Tidak ada pekerjaan produksi" description="Sesuaikan filter atau tunggu order baru dari Kasir." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$produksi" />

</div>
@endsection

@push('scripts')
<script>
    function selesaiProduksi(id) {
        Swal.fire({
            title: 'Tandai Selesai?',
            text: 'Item ini akan ditandai sudah selesai diproduksi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Ya, Selesai',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('produksi') }}/${id}/selesai`;
                form.innerHTML = `@csrf @method('PATCH')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function cancelSpk(id) {
        Swal.fire({
            title: 'Batalkan Produksi Item Ini?',
            html: `
                <div class="text-left">
                    <p class="text-sm text-slate-500 mb-3">Pilih tindakan selanjutnya untuk item ini:</p>
                    <label class="flex items-start gap-2 text-sm text-slate-700 mb-2 cursor-pointer">
                        <input type="radio" name="swal-tindakan" value="antre" class="mt-1 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <span><b>Kembalikan ke antrian SPK</b><br><small class="text-slate-400">Siap diambil ulang siapa saja, sama seperti SPK baru.</small></span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-slate-700 mb-2 cursor-pointer">
                        <input type="radio" name="swal-tindakan" value="pending" class="mt-1 text-amber-600 focus:ring-amber-500 cursor-pointer">
                        <span><b>Pending (belum diputuskan)</b><br><small class="text-slate-400">Diparkir di halaman SPK, tidak ikut antrian aktif sampai ada keputusan lanjutan.</small></span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-slate-700 mb-3 cursor-pointer">
                        <input type="radio" name="swal-tindakan" value="batal" class="mt-1 text-rose-600 focus:ring-rose-500 cursor-pointer" checked>
                        <span><b>Batal permanen</b><br><small class="text-slate-400">Tidak akan diambil/diproses lagi.</small></span>
                    </label>
                    <input id="swal-alasan-batal" class="swal2-input" placeholder="Alasan pembatalan (opsional)">
                    <label class="flex items-start gap-2 text-sm text-slate-600 mt-3 px-1 cursor-pointer">
                        <input type="checkbox" id="swal-kembalikan-stok" class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <span>Kembalikan bahan baku yang sudah dipotong ke stok<br><small class="text-slate-400">Centang HANYA kalau materialnya belum benar-benar dipakai/rusak - berlaku untuk tindakan apapun di atas.</small></span>
                    </label>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Kembali',
            customClass: { popup: 'rounded-[2rem]' },
            preConfirm: () => ({
                tindakan: document.querySelector('input[name="swal-tindakan"]:checked')?.value || 'batal',
                alasan: document.getElementById('swal-alasan-batal').value,
                kembalikanStok: document.getElementById('swal-kembalikan-stok').checked,
            })
        }).then((result) => {
            if (result.isConfirmed) {
                const { tindakan, alasan, kembalikanStok } = result.value;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('produksi') }}/${id}/cancel`;
                form.innerHTML = `@csrf @method('PATCH')<input type="hidden" name="tindakan" value="${tindakan}"><input type="hidden" name="alasan_batal" value="${(alasan || '').replace(/"/g, '&quot;')}"><input type="hidden" name="kembalikan_stok" value="${kembalikanStok ? '1' : '0'}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Escape HTML sebelum ditaruh di Swal.fire({html:...}). catatan berasal
    // dari input bebas operator (alasan pembatalan SPK) yang disimpan apa
    // adanya ke database - tanpa ini, siapa pun yang bisa membatalkan SPK bisa
    // menaruh <img onerror=...> atau <script> di alasannya, yang lalu
    // dieksekusi di browser siapa pun yang membuka popup "Detail / Catatan"
    // item itu (stored XSS).
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function showCatatan(id, catatan) {
        Swal.fire({
            title: 'SPK #' + id,
            html: catatan ? `<div class="text-left text-sm">${escapeHtml(catatan)}</div>` : '<div class="text-slate-400 italic text-sm">Belum ada catatan produksi.</div>',
            icon: 'info',
            customClass: { popup: 'rounded-[2rem]' }
        });
    }
</script>
@endpush
