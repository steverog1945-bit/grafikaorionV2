@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 bg-slate-50 min-h-screen max-w-[1600px] mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                Master <span class="text-indigo-600">Karyawan</span>
            </h1>
            <p class="text-slate-500 font-medium mt-3 text-[13px] uppercase tracking-[0.2em] flex items-center gap-2">
                <i class="fas fa-users text-indigo-400"></i>
                SDM <span class="text-slate-300">/</span> Manajemen Profil & Produksi
            </p>
        </div>

        <button onclick="toggleModal('modalTambah')" 
                class="inline-flex items-center justify-center px-8 py-4 bg-slate-900 hover:bg-indigo-600 text-white rounded-[1.5rem] text-[13px] font-black uppercase tracking-[0.15em] transition-all shadow-xl shadow-slate-200 gap-3 active:scale-95">
            <i class="fas fa-plus-circle text-sm text-indigo-400"></i>
            Tambah Karyawan
        </button>
    </div>

    {{-- TABLE CONTAINER --}}
    <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table class="w-full border-separate border-spacing-y-4">
                <thead>
                    <tr class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">
                        <th class="px-6 py-4 w-20 text-center">ID</th>
                        <th class="px-4 py-4 text-left">Profil Karyawan</th>
                        <th class="px-4 py-4 text-left">Penempatan</th>
                        <th class="px-4 py-4 text-center">Status Kepegawaian</th>
                        <th class="px-8 py-4 text-center">Aksi Kendali</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($karyawan as $k)
                    <tr class="group hover:bg-indigo-50/40 transition-all duration-300">
                        {{-- ID Column --}}
                        <td class="px-6 py-6 text-center bg-slate-50 group-hover:bg-white rounded-l-[2rem] border-y border-l border-transparent group-hover:border-indigo-100 transition-all">
                            <span class="text-[11px] font-bold text-slate-400">#{{ $k->id_karyawan }}</span>
                        </td>
                        
                        {{-- Name & Phone --}}
                        <td class="px-4 py-6 border-y border-transparent group-hover:border-indigo-100 transition-all">
                            <div class="font-black text-slate-800 uppercase tracking-tight text-[14px]">{{ $k->nama_karyawan }}</div>
                            <div class="text-[12px] text-slate-400 font-bold mt-1 flex items-center gap-1.5">
                                <i class="fas fa-phone-alt text-[10px] text-indigo-300"></i> {{ $k->telepon }}
                            </div>
                        </td>

                        {{-- Division --}}
                        <td class="px-4 py-6 border-y border-transparent group-hover:border-indigo-100 transition-all">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[11px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-600 border border-indigo-100">
                                {{ $k->bagian }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-6 text-center border-y border-transparent group-hover:border-indigo-100 transition-all">
                            @if($k->status_aktif)
                                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 text-[11px] font-black uppercase tracking-widest shadow-sm">
                                    <span class="w-2 h-2 mr-2 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 text-slate-400 border border-slate-200 text-[11px] font-black uppercase tracking-widest">
                                    <span class="w-2 h-2 mr-2 rounded-full bg-slate-300"></span> Non-Aktif
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-8 py-6 text-center rounded-r-[2rem] border-y border-r border-transparent group-hover:border-indigo-100 transition-all">
                            <div class="flex items-center justify-center gap-3">
                                <button onclick="toggleModal('modalEdit{{ $k->id_karyawan }}')" 
                                        class="w-10 h-10 flex items-center justify-center bg-white border border-slate-100 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 rounded-xl transition-all shadow-sm active:scale-90">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                
                                <form action="{{ url('karyawan/delete/'.$k->id_karyawan) }}" method="POST" class="inline" id="delete-form-{{ $k->id_karyawan }}">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete('{{ $k->id_karyawan }}', '{{ $k->nama_karyawan }}')"
                                            class="w-10 h-10 flex items-center justify-center bg-white border border-slate-100 text-slate-400 hover:text-rose-600 hover:border-rose-200 rounded-xl transition-all shadow-sm active:scale-90">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- MODAL EDIT (Dalam Loop) --}}
                    <div id="modalEdit{{ $k->id_karyawan }}" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" onclick="toggleModal('modalEdit{{ $k->id_karyawan }}')"></div>
                        <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-10 shadow-2xl border border-white/20 transform transition-all scale-100">
                            <div class="mb-8">
                                <h3 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Edit <span class="text-indigo-600">Profil</span></h3>
                                <p class="text-slate-400 text-[13px] font-bold mt-1 uppercase tracking-wider">ID Karyawan: #{{ $k->id_karyawan }}</p>
                            </div>

                            <form action="{{ url('karyawan/update/'.$k->id_karyawan) }}" method="POST" class="space-y-6">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nama Lengkap</label>
                                    <input type="text" name="nama_karyawan" value="{{ $k->nama_karyawan }}" 
                                           class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Divisi / Bagian</label>
                                    <select name="bagian" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner appearance-none">
                                        @foreach(['Admin', 'Desain', 'Produksi', 'Kasir', 'Gudang'] as $div)
                                            <option value="{{ $div }}" {{ $k->bagian == $div ? 'selected' : '' }}>{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Status Karyawan</label>
                                    <select name="status_aktif" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                                        <option value="1" {{ $k->status_aktif == 1 ? 'selected' : '' }}>AKTIF</option>
                                        <option value="0" {{ $k->status_aktif == 0 ? 'selected' : '' }}>NON-AKTIF / RESIGN</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nomor Telepon</label>
                                    <input type="text" name="telepon" value="{{ $k->telepon }}" 
                                           class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                                </div>
                                <div class="flex gap-4 pt-4">
                                    <button type="button" onclick="toggleModal('modalEdit{{ $k->id_karyawan }}')" 
                                            class="flex-1 px-4 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all">Batal</button>
                                    <button type="submit" 
                                            class="flex-[2] bg-indigo-600 text-white px-6 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-black transition-all">Update Profil</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div id="modalTambah" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="toggleModal('modalTambah')"></div>
    <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-10 shadow-2xl border border-white/20">
        <div class="mb-8 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-plus text-indigo-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Karyawan <span class="text-indigo-600">Baru</span></h3>
            <p class="text-slate-400 text-[12px] font-bold mt-1 uppercase tracking-wider">Input data personil produksi</p>
        </div>

        <form action="{{ route('karyawan.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nama Lengkap</label>
                <input type="text" name="nama_karyawan" placeholder="Contoh: Budi Santoso"
                       class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner" required>
            </div>
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Pilih Bagian</label>
                <select name="bagian" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner" required>
                    <option value="Admin">Admin</option>
                    <option value="Desain">Desain</option>
                    <option value="Produksi">Produksi</option>
                    <option value="Kasir">Kasir</option>
                    <option value="Gudang">Gudang</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Kontak Telepon</label>
                <input type="text" name="telepon" placeholder="0812xxxx"
                       class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner" required>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="button" onclick="toggleModal('modalTambah')" 
                        class="flex-1 px-4 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" 
                        class="flex-[2] bg-slate-900 text-white px-6 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest shadow-lg shadow-slate-200 hover:bg-indigo-600 transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Karyawan?',
            html: `Anda akan menghapus data <b>${name}</b>.<br><small class="text-slate-400 uppercase font-black tracking-widest">Tindakan ini permanen!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }
</script>
@endpush
@endsection