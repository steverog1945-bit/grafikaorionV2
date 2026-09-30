@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 bg-slate-50 min-h-screen max-w-[1600px] mx-auto">

    <x-page-header title="Master Karyawan">
        <x-slot:subtitle>
            <i class="fas fa-users text-indigo-400"></i>
            SDM <span class="mx-1 text-slate-300">/</span> Manajemen Profil &amp; Produksi
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button onclick="toggleModal('modalTambah')">
                <i class="fas fa-plus-circle"></i>
                Tambah Karyawan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ url('/karyawan') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Karyawan</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama atau no. telepon...">
                </div>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Bagian</label>
                <select name="bagian" class="select2-filter w-full">
                    <option value="">Semua Bagian</option>
                    @foreach($daftarBagian as $b)
                        <option value="{{ $b }}" {{ request('bagian') == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ url('/karyawan') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE CONTAINER --}}
    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 w-20 text-center">#</th>
                <th class="px-4 py-5 text-left">Profil Karyawan</th>
                <th class="px-4 py-5 text-left">Penempatan</th>
                <th class="px-4 py-5 text-center">Status Kepegawaian</th>
                <th class="px-8 py-5 text-center">Aksi Kendali</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($karyawan as $i => $k)
            <tr class="group hover:bg-slate-50/30 transition-all">
                {{-- Nomor Urut (lintas halaman) --}}
                <td class="px-6 py-5 text-center">
                    <span class="text-[11px] font-bold text-slate-400">{{ $karyawan->firstItem() + $i }}</span>
                </td>

                {{-- Name & Phone --}}
                <td class="px-4 py-5">
                    <div class="font-black text-slate-800 uppercase tracking-tight text-[14px]">{{ $k->nama_karyawan }}</div>
                    <div class="text-[12px] text-slate-400 font-bold mt-1 flex items-center gap-1.5">
                        <i class="fas fa-phone-alt text-[10px] text-indigo-300"></i> {{ $k->telepon }}
                    </div>
                </td>

                {{-- Division --}}
                <td class="px-4 py-5">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[11px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-600 border border-indigo-100">
                        {{ $k->bagian }}
                    </span>
                    @if($k->mesin->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($k->mesin as $m)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wide bg-slate-100 text-slate-500">
                                    <i class="fas fa-cog text-[8px] mr-1"></i>{{ $m->nama_mesin }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </td>

                {{-- Status --}}
                <td class="px-4 py-5 text-center">
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
                <td class="px-8 py-5 text-center">
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
                        <div class="absolute inset-0 bg-slate-900/60 transition-opacity" onclick="toggleModal('modalEdit{{ $k->id_karyawan }}')"></div>
                        <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-10 shadow-2xl border border-white/20 transform transition-all scale-100">
                            <div class="mb-8">
                                <h3 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Edit Profil</h3>
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
                                    <select name="bagian" onchange="toggleMesinField(this, 'mesin_wrapper_edit{{ $k->id_karyawan }}')" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner appearance-none">
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
                                <div id="mesin_wrapper_edit{{ $k->id_karyawan }}" class="{{ in_array($k->bagian, ['Produksi', 'Desain']) ? '' : 'hidden' }}">
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Mesin / Divisi yang Ditangani</label>
                                    <p class="text-[10px] text-slate-400 mb-2 ml-1">Kosongkan = tidak dibatasi, tetap lihat semua divisi di SPK. Hanya berlaku untuk bagian Produksi/Desain.</p>
                                    <div class="grid grid-cols-2 gap-2 bg-slate-50 rounded-2xl p-4 shadow-inner">
                                        @foreach($daftarMesin as $m)
                                            <label class="flex items-center gap-2 text-[12px] font-semibold text-slate-600 cursor-pointer">
                                                <input type="checkbox" name="mesin_ids[]" value="{{ $m->id_mesin }}"
                                                       {{ $k->mesin->contains('id_mesin', $m->id_mesin) ? 'checked' : '' }}
                                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                                {{ $m->nama_mesin }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex gap-4 pt-4">
                                    <button type="button" onclick="toggleModal('modalEdit{{ $k->id_karyawan }}')"
                                            class="flex-1 px-4 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all">Batal</button>
                                    <button type="submit" 
                                            class="flex-[2] bg-indigo-600 text-white px-6 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Update Profil</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                    @endforeach
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$karyawan" />
</div>

{{-- MODAL TAMBAH --}}
<div id="modalTambah" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60" onclick="toggleModal('modalTambah')"></div>
    <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-10 shadow-2xl border border-white/20">
        <div class="mb-8 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-plus text-indigo-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Karyawan Baru</h3>
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
                <select name="bagian" onchange="toggleMesinField(this, 'mesin_wrapper_tambah')" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-5 text-[13px] font-bold text-slate-600 focus:ring-4 focus:ring-indigo-500/10 shadow-inner" required>
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
            <div id="mesin_wrapper_tambah" class="hidden">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Mesin / Divisi yang Ditangani</label>
                <p class="text-[10px] text-slate-400 mb-2 ml-1">Kosongkan = tidak dibatasi, tetap lihat semua divisi di SPK. Hanya berlaku untuk bagian Produksi/Desain.</p>
                <div class="grid grid-cols-2 gap-2 bg-slate-50 rounded-2xl p-4 shadow-inner">
                    @foreach($daftarMesin as $m)
                        <label class="flex items-center gap-2 text-[12px] font-semibold text-slate-600 cursor-pointer">
                            <input type="checkbox" name="mesin_ids[]" value="{{ $m->id_mesin }}"
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            {{ $m->nama_mesin }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="button" onclick="toggleModal('modalTambah')"
                        class="flex-1 px-4 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" 
                        class="flex-[2] bg-indigo-600 text-white px-6 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2-filter').select2({ width: '100%' }).on('change', function() {
            $(this).closest('form').submit();
        });
    });

    // Mesin/divisi cuma relevan untuk bagian yang benar-benar turun ke
    // mesin fisik (Produksi) atau lini desain per-mesin (Desain) - Kasir/
    // Admin/Gudang tidak beroperasi di mesin tertentu, jadi field-nya
    // disembunyikan & checkbox yang mungkin masih tercentang dari state
    // sebelumnya ikut dikosongkan supaya tidak ada data nyasar terkirim.
    const BAGIAN_BISA_MESIN = ['Produksi', 'Desain'];

    function toggleMesinField(selectEl, wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        if (BAGIAN_BISA_MESIN.includes(selectEl.value)) {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
            wrapper.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        }
    }

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

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Karyawan?',
            html: `Anda akan menghapus data <b>${escapeHtml(name)}</b>.<br><small class="text-slate-400 uppercase font-black tracking-widest">Tindakan ini permanen!</small>`,
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