@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    
    <x-page-header title="Manajemen Satuan" subtitle="Konfigurasi unit ukur & standarisasi konversi stok">
        <x-slot:actions>
            <x-button onclick="openTambahModal()">
                <i class="fas fa-plus-circle"></i>
                Tambah Satuan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- INFO PANEL (Kiri) --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-indigo-100">
                        <i class="fas fa-exchange-alt text-xl"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 mb-2 tracking-tight">Katalog Satuan</h4>
                    <p class="text-slate-500 text-[11px] leading-relaxed">
                        Daftar nama satuan yang dipakai di seluruh aplikasi (pilihan Satuan Stok/Satuan Hitung di Bahan Baku, satuan Jasa, dsb).
                    </p>

                    <div class="mt-8 p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-[0.15em] mb-2">
                            <i class="fas fa-info-circle mr-1"></i> Penting
                        </p>
                        <p class="text-[11px] text-amber-700 leading-relaxed">
                            Angka konversi di halaman ini <b>tidak dipakai otomatis</b> untuk menghitung stok. Konversi riil per bahan (mis. 1 Roll = 224 m²) diatur di
                            <a href="{{ route('bahan.index') }}" class="underline font-bold">Master Bahan Baku</a> masing-masing bahan, karena tiap bahan bisa punya nilai konversi berbeda meski satuannya sama (contoh: dua bahan roll dengan lebar berbeda).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE DATA (Kanan) --}}
        <div class="lg:col-span-3">
            {{-- FILTER & SEARCH --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
                <form method="GET" action="{{ route('satuan.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[260px]">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Satuan</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                                   placeholder="Ketik nama satuan...">
                        </div>
                    </div>
                    <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
                    <a href="{{ route('satuan.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                        <i class="fas fa-undo-alt text-sm"></i>
                    </a>
                </form>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                                <th class="px-8 py-5 text-left">Nama Satuan</th>
                                <th class="px-6 py-5 text-center">Catatan Konversi</th>
                                <th class="px-8 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($satuan as $s)
                            <tr class="group hover:bg-slate-50/30 transition-all">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 font-bold text-[10px] group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                            {{ substr($s->nama_satuan, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-bold text-slate-700 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">
                                            {{ $s->nama_satuan }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-[11px] font-semibold text-slate-400">{{ number_format($s->nilai_konversi, 2) }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal({{ $s->id_satuan }}, '{{ $s->nama_satuan }}', {{ $s->nilai_konversi }})" 
                                                class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <form id="delete-form-satuan-{{ $s->id_satuan }}" action="{{ route('satuan.destroy', $s->id_satuan) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button" onclick="confirmDeleteSatuan('{{ $s->id_satuan }}', {{ json_encode($s->nama_satuan) }})" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center text-slate-300  text-sm">Belum ada data satuan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-table.pagination :paginator="$satuan" />
        </div>
    </div>
</div>

{{-- MODAL (Unified) --}}
<div id="modalSatuan" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalSatuan()"></div>
    <div class="flex min-h-full items-center justify-center p-6 text-center">
        <div class="relative w-full max-w-[500px] transform overflow-hidden rounded-[3rem] bg-white text-center shadow-[0_20px_50px_rgba(0,0,0,0.15)] transition-all border border-slate-100">
            
            {{-- Dynamic Header Color --}}
            <div id="modalHeader" class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white text-left transition-colors duration-500">
                <div class="flex items-center gap-3">
                    <i id="modalIcon" class="fas fa-plus-circle"></i>
                    <h3 id="modalTitle" class="text-xl font-bold tracking-tight uppercase">Satuan Baru</h3>
                </div>
                <button onclick="closeModalSatuan()" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="formSatuan" method="POST" class="py-12 space-y-8">
                @csrf
                <div id="methodField"></div>
                
                <div class="space-y-6">
                    <div class="max-w-[85%] mx-auto text-left">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Label Satuan (Besar/Kecil)</label>
                        <input type="text" name="nama_satuan" id="input_nama" placeholder="CONTOH: RIM, PACK, LEMBAR" maxlength="20" required
                            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center uppercase tracking-widest">
                    </div>

                    <div class="max-w-[85%] mx-auto text-left">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Catatan Konversi (Opsional)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="nilai_konversi" id="input_nilai" placeholder="1.00" required
                                class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                        </div>
                        <p class="mt-4 px-2 text-[10px] text-slate-400 leading-relaxed text-center">
                            Angka ini murni catatan, tidak dipakai otomatis oleh sistem. Isi <b>1</b> kalau tidak ada catatan khusus.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button id="btnSubmit" type="submit" 
                        class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                        Simpan Satuan
                    </button>
                    <button type="button" onclick="closeModalSatuan()" 
                        class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:text-slate-600">
                        Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalSatuan');
    const form = document.getElementById('formSatuan');
    const modalHeader = document.getElementById('modalHeader');
    const btnSubmit = document.getElementById('btnSubmit');
    const modalIcon = document.getElementById('modalIcon');

    function openTambahModal() {
        document.getElementById('modalTitle').innerText = "Buat Satuan Baru";
        modalHeader.classList.replace('bg-amber-500', 'bg-indigo-600');
        btnSubmit.classList.replace('bg-amber-500', 'bg-indigo-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-indigo-700');
        modalIcon.className = "fas fa-plus-circle";
        
        form.action = "{{ route('satuan.store') }}";
        document.getElementById('methodField').innerHTML = '';
        form.reset();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openEditModal(id, nama, nilai) {
        document.getElementById('modalTitle').innerText = "Edit Data Satuan";
        modalHeader.classList.replace('bg-indigo-600', 'bg-amber-500');
        btnSubmit.classList.replace('bg-indigo-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-indigo-700', 'hover:bg-amber-600');
        modalIcon.className = "fas fa-edit";

        form.action = `/satuan/${id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('input_nama').value = nama;
        document.getElementById('input_nilai').value = nilai;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModalSatuan() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function confirmDeleteSatuan(id, nama) {
        Swal.fire({
            title: 'Hapus Satuan?',
            html: `Satuan <b>${nama}</b> akan dihapus permanen.<br><small class="text-amber-600 font-semibold">Perhitungan konversi stok yang menggunakan satuan ini mungkin akan kacau.</small>`,
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
                document.getElementById(`delete-form-satuan-${id}`).submit();
            }
        });
    }
</script>
@endsection