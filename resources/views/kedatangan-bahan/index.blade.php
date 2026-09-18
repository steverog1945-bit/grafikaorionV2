@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 bg-slate-50 min-h-screen max-w-[1600px] mx-auto">

    <x-page-header title="Kedatangan Bahan">
        <x-slot:subtitle>
            <i class="fas fa-truck-loading text-indigo-400"></i>
            Logistik <span class="text-slate-300">/</span> Manajemen Surat Jalan
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ route('kedatangan-bahan.create') }}">
                <i class="fas fa-plus-circle"></i>
                Tambah Kedatangan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER BAR --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-8 mb-8">
        <form method="GET" action="{{ route('kedatangan-bahan.index') }}" class="flex flex-wrap items-end gap-6">
            <div class="flex-1 min-w-[300px]">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-4">Pencarian</label>
                <div class="relative group">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-14 pr-4 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner"
                           placeholder="Nomor SJ atau Nama Supplier...">
                </div>
            </div>

            <div class="w-full md:w-56">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-4">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}" 
                       class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>

            <div class="w-full md:w-56">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-4">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}" 
                       class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="h-[60px] px-8 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-3">
                    <i class="fas fa-filter text-xs text-indigo-200"></i>
                    <span class="text-[12px] font-black uppercase tracking-widest">Filter</span>
                </button>
                @if(request()->anyFilled(['search', 'from', 'to']))
                    <a href="{{ route('kedatangan-bahan.index') }}" 
                       class="h-[60px] px-6 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-100 transition-all flex items-center gap-2">
                        <i class="fas fa-undo text-xs"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <form id="bulk-delete-form" method="POST" action="{{ route('kedatangan-bahan.bulk-delete') }}">
        @csrf
        <x-table>
            <thead>
                <tr>
                    <th class="px-6 py-5 w-12 text-center">
                        <input type="checkbox" id="checkAll" class="rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-4 py-5 text-center w-10">#</th>
                    <th class="px-4 py-5 text-left">Dokumen SJ</th>
                    <th class="px-4 py-5 text-left">Entitas</th>
                    <th class="px-4 py-5 text-left">Keterangan</th>
                    <th class="px-4 py-5 text-center">Status</th>
                    <th class="px-8 py-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($kedatangans as $i => $k)
                <tr class="group hover:bg-slate-50/50 transition-all">
                    <td class="px-6 py-5 text-center">
                        @if($k->status == 'Draft')
                            <input type="checkbox" name="ids[]" value="{{ $k->id_kedatangan }}" class="row-checkbox rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500">
                        @else
                            <i class="fas fa-lock text-slate-300 text-xs"></i>
                        @endif
                    </td>
                    <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $kedatangans->firstItem() + $i }}</td>

                    <td class="px-4 py-5">
                        <span class="text-[13px] font-bold text-slate-700 uppercase block">{{ $k->no_surat_jalan }}</span>
                        <span class="text-[11px] text-slate-400 font-semibold mt-0.5 block">{{ optional($k->tgl_datang)->format('d M Y') }}</span>
                    </td>

                    <td class="px-4 py-5">
                        <span class="text-[12px] font-bold text-slate-700 uppercase block leading-tight">{{ $k->supplier->nama_supplier ?? 'VENDOR UMUM' }}</span>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider flex items-center gap-1 mt-0.5">
                            <i class="fas fa-warehouse text-[9px]"></i> {{ $k->gudang->nama_gudang ?? 'UTAMA' }}
                        </span>
                    </td>

                    {{-- KETERANGAN: Mendukung Newline/Enter & Tooltip Detail --}}
                    <td class="px-4 py-5 relative max-w-xs">
                        <div class="relative group/note">
                            <p class="text-[12px] text-slate-500 leading-relaxed line-clamp-2 cursor-help italic whitespace-pre-line">
                                {{ $k->keterangan ?? 'Tidak ada catatan...' }}
                            </p>

                            <div class="absolute z-50 invisible group-hover/note:visible opacity-0 group-hover/note:opacity-100 transition-all duration-300 bottom-full left-0 mb-3 w-80 p-5 bg-slate-900 text-white text-[13px] rounded-2xl shadow-2xl pointer-events-none border border-slate-700">
                                <div class="font-black mb-2 uppercase text-indigo-400 tracking-tighter flex items-center gap-2">
                                    <i class="fas fa-sticky-note text-[10px]"></i> Detail Catatan:
                                </div>
                                <div class="whitespace-pre-line leading-relaxed text-slate-200">
                                    {{ $k->keterangan ?? '—' }}
                                </div>
                                <div class="absolute -bottom-1 left-6 w-3 h-3 bg-slate-900 rotate-45 border-b border-r border-slate-700"></div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-5 text-center">
                        <span class="px-3 py-1.5 rounded-lg border {{ $k->status == 'Selesai' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} text-[10px] font-bold uppercase tracking-widest">
                            {{ $k->status }}
                        </span>
                    </td>

                    <td class="px-8 py-5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($k->status == 'Draft')
                                <x-button type="button" onclick="confirmApprove('{{ $k->id_kedatangan }}', '{{ $k->no_surat_jalan }}')" size="sm" class="!px-4">
                                    Validasi
                                </x-button>
                                <x-button href="{{ route('kedatangan-bahan.edit', $k->id_kedatangan) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-indigo-600" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </x-button>
                                <x-button type="button" size="icon" variant="ghost" onclick="singleDelete('{{ $k->id_kedatangan }}')" class="border border-slate-100 hover:text-rose-600" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </x-button>
                            @else
                                <span class="text-[11px] font-bold text-slate-300 uppercase italic">
                                    <i class="fas fa-check-double mr-1"></i> Terverifikasi
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state title="Belum ada data kedatangan" description="Klik 'Tambah Kedatangan' untuk mencatat surat jalan pertama." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-table>

        {{-- BULK ACTIONS FOOTER --}}
        <div class="mt-4">
            <x-button type="button" id="btn-bulk-delete" variant="danger" class="opacity-0 pointer-events-none" disabled>
                <i class="fas fa-trash-alt"></i> Hapus Terpilih (<span id="count-checked">0</span>)
            </x-button>
        </div>
    </form>

    <x-table.pagination :paginator="$kedatangans" />

    <x-table.pagination :paginator="$kedatangans" />
</div>
@endsection

<style>
    body { font-size: 14px; }
    
    .whitespace-pre-line {
        white-space: pre-line;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkBtn = document.getElementById('btn-bulk-delete');
    const countSpan = document.getElementById('count-checked');

    // Fungsi Update Bulk Button Visibility - toggle langsung di tombolnya
    // sendiri (pola sama dengan pembelian-bahan/index.blade.php), bukan
    // wrapper footer terpisah.
    function updateBulkUI() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        countSpan.textContent = checkedCount;
        bulkBtn.disabled = checkedCount === 0;

        if (checkedCount > 0) {
            bulkBtn.classList.remove('opacity-0', 'pointer-events-none');
            bulkBtn.classList.add('opacity-100');
        } else {
            bulkBtn.classList.add('opacity-0', 'pointer-events-none');
            bulkBtn.classList.remove('opacity-100');
        }
    }

    checkAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkUI();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkUI);
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Validasi Approval
    function confirmApprove(id, noSj) {
        Swal.fire({
            title: 'Validasi Kedatangan?',
            html: `Konfirmasi barang <b>${escapeHtml(noSj)}</b> telah diterima.<br><small class="text-rose-500 font-bold uppercase underline">Stok bertambah otomatis!</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Selesaikan!',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('kedatangan-bahan/validate') }}/${id}`;
                form.innerHTML = `@csrf @method('PATCH')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Delete Satuan
    function singleDelete(id) {
        Swal.fire({
            title: 'Hapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('kedatangan-bahan') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Bulk Delete Action
    bulkBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Hapus Massal?',
            text: "Data draft yang terpilih akan dihapus selamanya.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus Semua',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulk-delete-form').submit();
            }
        });
    });
</script>
@endpush