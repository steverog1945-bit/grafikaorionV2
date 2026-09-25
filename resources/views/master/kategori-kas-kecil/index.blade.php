@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Kategori Kas Kecil" subtitle="Daftar kategori pemasukan &amp; pengeluaran kas kecil - dipakai sebagai pilihan tetap saat mencatat">
        <x-slot:actions>
            <x-button onclick="openTambahModalKategoriKasKecil()">
                <i class="fas fa-tags"></i>
                Kategori Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('kategori-kas-kecil.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Kategori</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama kategori...">
                </div>
            </div>
            <button type="submit" class="h-[46px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ route('kategori-kas-kecil.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE CONTAINER --}}
    <x-table>
        <thead>
            <tr>
                <th class="px-4 py-5 text-center w-10">#</th>
                <th class="px-8 py-5 text-left">Nama Kategori</th>
                <th class="px-4 py-5 text-left">Jenis</th>
                <th class="px-6 py-5 text-left">Akun Terkait</th>
                <th class="px-8 py-5 text-right">Manajemen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($kategori as $i => $kat)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $kategori->firstItem() + $i }}</td>
                <td class="px-8 py-5">
                    <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">
                        {{ $kat->nama_kategori }}
                    </div>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[10px] font-bold uppercase rounded px-2 py-1 {{ $kat->jenis === 'Masuk' ? 'text-emerald-600 bg-emerald-50' : 'text-rose-500 bg-rose-50' }}">{{ $kat->jenis === 'Masuk' ? 'Pemasukan' : 'Pengeluaran' }}</span>
                </td>
                <td class="px-6 py-5">
                    @if($kat->kode_akun)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                            {{ $kat->kode_akun }} &middot; {{ optional($akunBeban->firstWhere('kode_akun', $kat->kode_akun))->nama_akun ?? '—' }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100 uppercase">
                            <i class="fas fa-exclamation-triangle"></i> Belum Dipetakan
                        </span>
                    @endif
                </td>
                <td class="px-8 py-5 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="editKategoriKasKecil({{ json_encode($kat) }})"
                            class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all">
                            <i class="far fa-edit text-sm"></i>
                        </button>

                        <form id="delete-form-kategori-kas-kecil-{{ $kat->id_kategori_kas_kecil }}" action="{{ route('kategori-kas-kecil.destroy', $kat->id_kategori_kas_kecil) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" onclick="confirmDeleteKategoriKasKecil('{{ $kat->id_kategori_kas_kecil }}', {{ json_encode($kat->nama_kategori) }})"
                            class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-24 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-folder-open text-slate-100 text-6xl mb-4"></i>
                        <p class="text-slate-300 font-medium text-sm">Belum ada kategori yang terdaftar.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$kategori" />
</div>

{{-- MODAL CRUD (Unified) --}}
<div id="modalKategoriKasKecil" class="hidden fixed inset-0 z-[100] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-10">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.15)] w-full max-w-[500px] overflow-hidden transform transition-all border border-slate-100">

            <div id="modalHeaderKategoriKasKecil" class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white transition-colors duration-500">
                <div class="flex items-center gap-3">
                    <i id="modalIconKategoriKasKecil" class="fas fa-plus-circle"></i>
                    <h3 id="modalTitleKategoriKasKecil" class="text-xl font-bold tracking-tight uppercase">Tambah Kategori</h3>
                </div>
                <button onclick="closeModalKategoriKasKecil()" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="formKategoriKasKecil" action="{{ route('kategori-kas-kecil.store') }}" method="POST" class="py-12 space-y-8">
                @csrf
                <div id="methodFieldKategoriKasKecil"></div>

                <div class="max-w-[85%] mx-auto">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="inputNamaKategoriKasKecil" placeholder="Contoh: Beban Internet" required
                           class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                </div>

                <div class="max-w-[85%] mx-auto">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Jenis</label>
                    <select name="jenis" id="inputJenisKategoriKasKecil" onchange="saringAkunKategoriKasKecil()"
                           class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                        <option value="Keluar">Pengeluaran</option>
                        <option value="Masuk">Pemasukan</option>
                    </select>
                </div>

                <div class="max-w-[85%] mx-auto">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Akun Terkait</label>
                    <select name="kode_akun" id="inputKodeAkunKategoriKasKecil"
                           class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner text-center">
                        <option value="">-- Belum Dipetakan --</option>
                        @foreach($akunBeban as $akun)
                            <option value="{{ $akun->kode_akun }}" data-jenis="{{ $akun->tipe_akun === 'Beban' ? 'Keluar' : 'Masuk' }}">{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-2 text-center">Pengeluaran dipetakan ke akun Beban, pemasukan ke akun Pendapatan/Modal - otomatis tercatat di jurnal & Laba Rugi tiap kali kategori ini dipakai di Kas Kecil.</p>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button id="btnSubmitKategoriKasKecil" type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                        Simpan Data
                    </button>
                    <button type="button" onclick="closeModalKategoriKasKecil()" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">
                        Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modalKategoriKasKecil = document.getElementById('modalKategoriKasKecil');
    const formKategoriKasKecil = document.getElementById('formKategoriKasKecil');
    const modalHeaderKategoriKasKecil = document.getElementById('modalHeaderKategoriKasKecil');
    const btnSubmitKategoriKasKecil = document.getElementById('btnSubmitKategoriKasKecil');
    const modalIconKategoriKasKecil = document.getElementById('modalIconKategoriKasKecil');

    function openTambahModalKategoriKasKecil() {
        document.getElementById('modalTitleKategoriKasKecil').innerText = "Tambah Kategori Baru";
        modalHeaderKategoriKasKecil.classList.replace('bg-amber-500', 'bg-indigo-600');
        btnSubmitKategoriKasKecil.classList.replace('bg-amber-500', 'bg-indigo-600');
        btnSubmitKategoriKasKecil.classList.replace('hover:bg-amber-600', 'hover:bg-indigo-700');
        modalIconKategoriKasKecil.className = "fas fa-plus-circle";

        formKategoriKasKecil.action = "{{ route('kategori-kas-kecil.store') }}";
        document.getElementById('methodFieldKategoriKasKecil').innerHTML = '';
        formKategoriKasKecil.reset();
        document.getElementById('inputJenisKategoriKasKecil').value = 'Keluar';
        saringAkunKategoriKasKecil();
        document.getElementById('inputKodeAkunKategoriKasKecil').value = '';
        modalKategoriKasKecil.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function editKategoriKasKecil(data) {
        document.getElementById('modalTitleKategoriKasKecil').innerText = "Update Nama Kategori";
        modalHeaderKategoriKasKecil.classList.replace('bg-indigo-600', 'bg-amber-500');
        btnSubmitKategoriKasKecil.classList.replace('bg-indigo-600', 'bg-amber-500');
        btnSubmitKategoriKasKecil.classList.replace('hover:bg-indigo-700', 'hover:bg-amber-600');
        modalIconKategoriKasKecil.className = "fas fa-edit";

        formKategoriKasKecil.action = `{{ url('/kategori-kas-kecil') }}/${data.id_kategori_kas_kecil}`;
        document.getElementById('methodFieldKategoriKasKecil').innerHTML = '@method("PUT")';
        document.getElementById('inputNamaKategoriKasKecil').value = data.nama_kategori;
        document.getElementById('inputJenisKategoriKasKecil').value = data.jenis || 'Keluar';
        saringAkunKategoriKasKecil();
        document.getElementById('inputKodeAkunKategoriKasKecil').value = data.kode_akun || '';
        modalKategoriKasKecil.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Pilihan akun mengikuti jenis: pengeluaran -> Beban, pemasukan -> Pendapatan/Modal.
    function saringAkunKategoriKasKecil() {
        const jenis = document.getElementById('inputJenisKategoriKasKecil').value;
        const sel = document.getElementById('inputKodeAkunKategoriKasKecil');
        [...sel.options].forEach(o => { if (o.value) { const cocok = o.dataset.jenis === jenis; o.hidden = !cocok; o.disabled = !cocok; } });
        if (sel.selectedOptions[0] && sel.selectedOptions[0].disabled) sel.value = '';
    }

    function closeModalKategoriKasKecil() {
        modalKategoriKasKecil.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function confirmDeleteKategoriKasKecil(id, nama) {
        Swal.fire({
            title: 'Hapus Kategori?',
            html: `Kategori <b>${nama}</b> akan dihapus permanen.`,
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
                document.getElementById(`delete-form-kategori-kas-kecil-${id}`).submit();
            }
        });
    }
</script>
@endsection
