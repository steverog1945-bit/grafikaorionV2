@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 bg-slate-50 min-h-screen max-w-[1600px] mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                Kedatangan <span class="text-indigo-600">Bahan</span>
            </h1>
            <p class="text-slate-500 font-medium mt-3 text-[13px] uppercase tracking-[0.2em] flex items-center gap-2">
                <i class="fas fa-truck-loading text-indigo-400"></i>
                Logistik <span class="text-slate-300">/</span> Manajemen Surat Jalan
            </p>
        </div>

        <a href="{{ route('kedatangan-bahan.create') }}"
           class="inline-flex items-center justify-center px-8 py-4 bg-slate-900 hover:bg-indigo-600 text-white rounded-[1.5rem] text-[13px] font-black uppercase tracking-[0.15em] transition-all shadow-xl shadow-slate-200 gap-3 active:scale-95">
            <i class="fas fa-plus-circle text-sm text-indigo-400"></i>
            Tambah Kedatangan
        </a>
    </div>

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
                <button type="submit" class="h-[60px] px-8 bg-slate-900 text-white rounded-2xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-100 flex items-center gap-3">
                    <i class="fas fa-filter text-xs text-indigo-400"></i>
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
    <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <form id="bulk-delete-form" method="POST" action="{{ route('kedatangan-bahan.bulk-delete') }}">
            @csrf
            <div class="overflow-x-auto p-4">
                <table class="w-full border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            <th class="px-6 py-4 w-16 text-center text-indigo-500">
                                <input type="checkbox" id="checkAll" class="rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500 w-5 h-5 transition-all cursor-pointer">
                            </th>
                            <th class="px-4 py-4 text-left">Dokumen SJ</th>
                            <th class="px-4 py-4 text-left">Entitas</th>
                            <th class="px-4 py-4 text-left w-[350px]">Keterangan</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kedatangans as $k)
                        <tr class="group hover:bg-indigo-50/40 transition-all duration-300">
                            <td class="px-6 py-6 text-center bg-slate-50 group-hover:bg-white rounded-l-[2rem] border-y border-l border-transparent group-hover:border-indigo-100 transition-all">
                                @if($k->status == 'Draft')
                                    <input type="checkbox" name="ids[]" value="{{ $k->id_kedatangan }}" class="row-checkbox rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500 w-5 h-5 transition-all cursor-pointer">
                                @else
                                    <i class="fas fa-lock text-slate-300 text-xs"></i>
                                @endif
                            </td>
                            
                            <td class="px-4 py-6 border-y border-transparent group-hover:border-indigo-100">
                                <span class="text-[14px] font-black text-slate-800 uppercase block tracking-tight">{{ $k->no_surat_jalan }}</span>
                                <span class="text-[12px] text-slate-400 font-bold mt-1 block uppercase">{{ optional($k->tgl_datang)->format('d M Y') }}</span>
                            </td>

                            <td class="px-4 py-6 border-y border-transparent group-hover:border-indigo-100">
                                <span class="text-[13px] font-bold text-slate-700 uppercase block leading-tight">{{ $k->supplier->nama_supplier ?? 'VENDOR UMUM' }}</span>
                                <span class="text-[11px] font-black text-indigo-500 uppercase tracking-wider flex items-center gap-1 mt-1">
                                    <i class="fas fa-warehouse text-[10px]"></i> {{ $k->gudang->nama_gudang ?? 'UTAMA' }}
                                </span>
                            </td>

                            {{-- KETERANGAN: Mendukung Newline/Enter & Tooltip Detail --}}
                            <td class="px-4 py-6 border-y border-transparent group-hover:border-indigo-100 relative">
                                <div class="relative group/note">
                                    <p class="text-[13px] text-slate-500 leading-relaxed line-clamp-3 cursor-help italic whitespace-pre-line">
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

                            <td class="px-4 py-6 text-center border-y border-transparent group-hover:border-indigo-100">
                                <span class="px-4 py-2 rounded-xl border {{ $k->status == 'Selesai' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} text-[11px] font-black uppercase tracking-widest shadow-sm">
                                    {{ $k->status }}
                                </span>
                            </td>

                            <td class="px-8 py-6 text-center rounded-r-[2rem] border-y border-r border-transparent group-hover:border-indigo-100">
                                <div class="flex items-center justify-center gap-3">
                                    @if($k->status == 'Draft')
                                        <button type="button" onclick="confirmApprove('{{ $k->id_kedatangan }}', '{{ $k->no_surat_jalan }}')"
                                            class="h-10 px-6 bg-indigo-600 text-white hover:bg-black rounded-xl transition-all text-[11px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100">
                                            Validasi
                                        </button>
                                        <a href="{{ route('kedatangan-bahan.edit', $k->id_kedatangan) }}" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-100 text-slate-400 hover:text-indigo-600 rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <button type="button" onclick="singleDelete('{{ $k->id_kedatangan }}')" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-100 text-slate-400 hover:text-rose-600 rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    @else
                                        <span class="text-[11px] font-black text-slate-300 uppercase italic">
                                            <i class="fas fa-check-double mr-1"></i> Terverifikasi
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-32 text-center">
                                <i class="fas fa-box-open text-slate-200 text-6xl mb-6"></i>
                                <p class="text-slate-400 text-[14px] font-black uppercase tracking-widest">Belum ada data kedatangan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- BULK ACTIONS FOOTER --}}
            <div id="bulk-footer" class="p-8 border-t border-slate-50 flex justify-start opacity-0 pointer-events-none transition-all duration-300 translate-y-2">
                <button type="button" id="btn-bulk-delete" 
                        class="px-8 py-4 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-[1.5rem] text-[12px] font-black uppercase tracking-widest transition-all flex items-center gap-3 shadow-lg shadow-rose-100">
                    <i class="fas fa-trash-alt"></i> Hapus Terpilih (<span id="count-checked">0</span>)
                </button>
            </div>
        </form>
    </div>
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
    const bulkFooter = document.getElementById('bulk-footer');
    const bulkBtn = document.getElementById('btn-bulk-delete');
    const countSpan = document.getElementById('count-checked');

    // Fungsi Update Bulk Button & Footer Visibility
    function updateBulkUI() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        countSpan.textContent = checkedCount;
        
        if(checkedCount > 0) {
            bulkFooter.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
            bulkFooter.classList.add('opacity-100', 'translate-y-0');
        } else {
            bulkFooter.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
            bulkFooter.classList.remove('opacity-100', 'translate-y-0');
        }
    }

    checkAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkUI();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkUI);
    });

    // Validasi Approval
    function confirmApprove(id, noSj) {
        Swal.fire({
            title: 'Validasi Kedatangan?',
            html: `Konfirmasi barang <b>${noSj}</b> telah diterima.<br><small class="text-rose-500 font-bold uppercase underline">Stok bertambah otomatis!</small>`,
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