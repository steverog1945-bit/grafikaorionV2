@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Master Bahan Baku" subtitle="Manajemen Inventaris & Kontrol Stok Real-time">
        <x-slot:actions>
            <x-button onclick="openTambahModal()">
                <i class="fas fa-plus-circle"></i>
                Registrasi Bahan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('bahan.index') }}" id="filterForm" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[300px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Material</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik SKU atau Nama Bahan...">
                </div>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Kategori</label>
                <select name="kategori" class="select2-filter w-full">
                    <option value="">Semua Divisi</option>
                    @foreach($kategori as $kat)
                        <option value="{{ $kat->id_kategori }}" {{ request('kategori') == $kat->id_kategori ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status</label>
                <select name="status" class="select2-filter w-full">
                    <option value="aktif" {{ request('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="semua" {{ request('status') == 'semua' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Urutan</label>
                <select name="sort" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                    <option value="nama_bahan" {{ request('sort') == 'nama_bahan' ? 'selected' : '' }}>Abjad (A-Z)</option>
                    <option value="stok" {{ request('sort') == 'stok' ? 'selected' : '' }}>Stok Terendah</option>
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </div>

            <a href="{{ route('bahan.index') }}" class="h-[46px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <form id="bulk-delete-form" method="POST" action="{{ route('bahan.bulk-delete') }}">
            @csrf
        <div class="overflow-x-auto modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-5 w-12 text-center">
                            <input type="checkbox" id="checkAll" class="rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-8 py-5 text-left">Identitas Material</th>
                        <th class="px-6 py-5 text-center">Spesifikasi</th>
                        <th class="px-6 py-5 text-center">Volume Stok</th>
                        <th class="px-6 py-5 text-center">Satuan</th>
                        <th class="px-8 py-5 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bahan as $b)
                    <tr class="group hover:bg-slate-50/30 transition-all {{ $b->status === 'nonaktif' ? 'opacity-50' : '' }}">
                        <td class="px-6 py-5 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $b->id_bahan }}" class="row-checkbox rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 font-bold text-xs">
                                    {{ substr($b->nama_bahan, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-[14px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">
                                        {{ $b->nama_bahan }}
                                        @if($b->status === 'nonaktif')
                                            <span class="ml-1 text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase align-middle">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $b->kode_bahan }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex flex-col gap-1 items-center">
                                <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md uppercase">
                                    {{ $b->mesin->nama_mesin ?? '-' }}
                                </span>
                                <span class="text-[9px] text-slate-400 font-medium uppercase">{{ $b->kategori->nama_kategori ?? 'Umum' }}</span>
                                @if($b->merk || $b->jenisBahan)
                                    <span class="text-[9px] text-indigo-400 font-medium uppercase">
                                        {{ $b->merk->nama_merk ?? '' }}{{ $b->merk && $b->jenisBahan ? ' • ' : '' }}{{ $b->jenisBahan->nama_jenis ?? '' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-base font-bold {{ $b->stok <= $b->stok_minimal ? 'text-rose-500' : 'text-slate-700' }}" title="Stok minimal: {{ number_format($b->stok_minimal, 2, ',', '.') }}">
                                {{ number_format($b->stok, 0, ',', '.') }}
                            </span>
                            @if($b->satuanHitung && $b->id_satuan_stok !== $b->id_satuan_hitung && $b->nilai_konversi > 1)
                                <div class="text-[10px] text-slate-400 font-semibold mt-1">
                                    ≈ {{ number_format($b->stok * $b->nilai_konversi, 0, ',', '.') }} {{ $b->satuanHitung->nama_satuan }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-3 py-1.5 rounded-lg uppercase">
                                {{ $b->satuan_stok }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick='editBahan(@json($b))' class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all">
                                    <i class="far fa-edit text-sm"></i>
                                </button>
                                <button type="button" onclick="toggleStatus('{{ $b->id_bahan }}', '{{ $b->status }}', {{ json_encode($b->nama_bahan) }})"
                                        class="w-9 h-9 flex items-center justify-center text-slate-400 {{ $b->status === 'aktif' ? 'hover:text-slate-600 hover:bg-slate-100' : 'hover:text-emerald-500 hover:bg-emerald-50' }} rounded-lg transition-all"
                                        title="{{ $b->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan kembali' }}">
                                    <i class="far {{ $b->status === 'aktif' ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                </button>
                                <button type="button" onclick="singleDelete('{{ $b->id_bahan }}')" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center text-slate-400 font-medium italic">Data tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- BULK ACTIONS FOOTER --}}
        <div id="bulk-footer" class="px-8 py-5 border-t border-slate-50 flex justify-start opacity-0 pointer-events-none transition-all duration-300 translate-y-2">
            <button type="button" id="btn-bulk-delete"
                    class="px-6 py-3 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-trash-alt"></i> Hapus Terpilih (<span id="count-checked">0</span>)
            </button>
        </div>
        </form>

       {{-- PAGINATION - komponen bersama x-table.pagination (sama dengan
            semua halaman lain), bukan lagi markup Bootstrap-4 sendiri -
            sekaligus dapat fitur "Ke Halaman" untuk lompat langsung ke
            nomor halaman berapa pun tanpa harus klik Next berulang. --}}
       <x-table.pagination :paginator="$bahan" />
    </div>
</div>

{{-- MODAL CRUD --}}
<div id="modalBahan" class="hidden fixed inset-0 z-[60] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-10">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-800">Registrasi Material</h3>
                <button onclick="closeModalBahan()" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fas fa-times-circle text-xl"></i>
                </button>
            </div>

            <form id="formBahan" method="POST" class="p-8">
                @csrf
                <div id="methodField"></div>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Bahan</label>
                        <input type="text" name="nama_bahan" id="input_nama_bahan" required
                               class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kode SKU</label>
                            <input type="text" name="kode_bahan" id="input_kode_bahan" required
                                   class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Mesin Alokasi</label>
                            <select name="id_mesin" id="input_id_mesin" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                @foreach($mesin as $m)
                                    <option value="{{ $m->id_mesin }}">{{ $m->nama_mesin }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori</label>
                        <select name="id_kategori" id="input_id_kategori" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                            @foreach($kategori as $k)
                                <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Merk (Opsional)</label>
                            <select name="id_merk" id="input_id_merk" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                <option value="">-- Tanpa Merk --</option>
                                @foreach($merk as $m)
                                    <option value="{{ $m->id_merk }}">{{ $m->nama_merk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Jenis Bahan (Opsional)</label>
                            <select name="id_jenis_bahan" id="input_id_jenis_bahan" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                <option value="">-- Tanpa Jenis --</option>
                                @foreach($jenisBahan as $j)
                                    <option value="{{ $j->id_jenis_bahan }}">{{ $j->nama_jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-4 mb-3">Konversi Satuan &amp; Stok</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Satuan Stok (Beli)</label>
                            <select name="id_satuan_stok" id="input_id_satuan_stok" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                <option value="">Pilih Satuan</option>
                                @foreach($satuan as $s)
                                    <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Satuan Hitung (Pakai)</label>
                            <select name="id_satuan_hitung" id="input_id_satuan_hitung" required class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner appearance-none cursor-pointer">
                                <option value="">Pilih Satuan</option>
                                @foreach($satuan as $s)
                                    <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="wrapper_dimensi_roll" class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-xl space-y-3">
                        <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">
                            <i class="fas fa-ruler-combined mr-1"></i> Kalkulator Dimensi Roll (Opsional - khusus bahan Roll/m²)
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Lebar Roll (meter)</label>
                                <input type="number" step="0.01" name="lebar_roll" id="input_lebar_roll" oninput="hitungNilaiKonversiRoll()" placeholder="Contoh: 1.5"
                                       class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Panjang Roll (meter)</label>
                                <input type="number" step="0.01" name="panjang_roll" id="input_panjang_roll" oninput="hitungNilaiKonversiRoll()" placeholder="Contoh: 50"
                                       class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                            </div>
                        </div>
                        <p class="text-[10px] text-indigo-400 leading-relaxed">
                            Isi kalau Satuan Hitung = m² (mis. bahan roll untuk banner/vinyl). Lebar × Panjang otomatis mengisi Nilai Konversi di bawah, supaya HPP &amp; pengurangan stok akurat berdasarkan luas roll sesungguhnya - bukan angka tebakan.
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nilai Konversi (1 Satuan Stok = berapa Satuan Hitung)</label>
                        <input type="number" step="0.001" name="nilai_konversi" id="input_nilai_konversi" required value="1"
                               class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        <p class="mt-2 ml-1 text-[10px] text-slate-400">
                            Contoh: 1 Rim = 500 Lembar → isi <b>500</b>. Kalau bahan roll, isi Lebar × Panjang Roll di atas dan ini terisi otomatis.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Stok Awal</label>
                            <input type="number" step="0.001" name="stok" id="input_stok" required value="0"
                                   class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner disabled:opacity-60 disabled:cursor-not-allowed">
                            <p id="stok_locked_hint" class="hidden mt-2 ml-1 text-[10px] text-slate-400 leading-relaxed">
                                <i class="fas fa-lock mr-0.5"></i> Sudah ada histori stok. Ubah lewat
                                <a href="{{ route('kartu-stok.index') }}" class="text-indigo-500 font-bold hover:underline" target="_blank">Kartu Stok &rarr; Penyesuaian</a>
                                supaya tercatat jejaknya.
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Stok Minimal</label>
                            <input type="number" step="0.001" name="stok_minimal" id="input_stok_minimal" required value="0"
                                   class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                        </div>
                    </div>
                </div>

                <div class="pt-8 flex gap-3">
                    <button type="button" onclick="closeModalBahan()" class="flex-1 py-3.5 bg-slate-50 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slate-100 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3.5 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-filter').select2({ width: '100%' }).on('change', function() {
            $(this).closest('form').submit();
        });
    });

    function openTambahModal() {
        document.getElementById('modalTitle').innerText = "Registrasi Material Baru";
        document.getElementById('formBahan').action = "{{ route('bahan.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('formBahan').reset();

        // Stok Awal hanya bisa diisi bebas saat REGISTRASI bahan baru (belum
        // ada histori kartu_stok apa pun) - lihat editBahan() untuk kenapa
        // ini dikunci saat mode edit.
        document.getElementById('input_stok').disabled = false;
        document.getElementById('stok_locked_hint').classList.add('hidden');

        document.getElementById('modalBahan').classList.remove('hidden');
    }

    function editBahan(data) {
        document.getElementById('modalTitle').innerText = "Update Spesifikasi Material";
        const form = document.getElementById('formBahan');
        form.action = `{{ url('/bahan-baku') }}/${data.id_bahan}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';

        document.getElementById('input_nama_bahan').value = data.nama_bahan;
        document.getElementById('input_kode_bahan').value = data.kode_bahan;
        document.getElementById('input_id_mesin').value = data.id_mesin;
        document.getElementById('input_id_kategori').value = data.id_kategori;
        document.getElementById('input_id_satuan_stok').value = data.id_satuan_stok;
        document.getElementById('input_id_satuan_hitung').value = data.id_satuan_hitung;
        document.getElementById('input_nilai_konversi').value = data.nilai_konversi;

        // Stok pada bahan yang SUDAH ada tidak boleh diubah lewat form ini
        // lagi - dulu field ini biasa saja, jadi mengedit nama/kategori bisa
        // ikut diam-diam mengubah angka stok tanpa jejak apa pun di
        // kartu_stok. Field dikunci (disabled -> tidak ikut terkirim saat
        // submit) dan diarahkan ke Kartu Stok > Penyesuaian, satu-satunya
        // jalur resmi yang selalu mewajibkan alasan & mencatat histori.
        document.getElementById('input_stok').value = data.stok;
        document.getElementById('input_stok').disabled = true;
        document.getElementById('stok_locked_hint').classList.remove('hidden');

        document.getElementById('input_stok_minimal').value = data.stok_minimal;
        document.getElementById('input_id_merk').value = data.id_merk || '';
        document.getElementById('input_id_jenis_bahan').value = data.id_jenis_bahan || '';
        document.getElementById('input_lebar_roll').value = data.lebar_roll || '';
        document.getElementById('input_panjang_roll').value = data.panjang_roll || '';

        document.getElementById('modalBahan').classList.remove('hidden');
    }

    function closeModalBahan() {
        document.getElementById('modalBahan').classList.add('hidden');
    }

    // Lebar x Panjang roll (meter) = total m2 per roll = Nilai Konversi
    // untuk bahan yang Satuan Hitung-nya m2. Sama prinsipnya dengan
    // (P*L)/10000 di form order - "hitungan panjang x lebar" diterapkan ke
    // dimensi ROLL bahan baku, bukan cuma ke ukuran cetak pesanan.
    function hitungNilaiKonversiRoll() {
        const lebar = parseFloat(document.getElementById('input_lebar_roll').value) || 0;
        const panjang = parseFloat(document.getElementById('input_panjang_roll').value) || 0;
        if (lebar > 0 && panjang > 0) {
            document.getElementById('input_nilai_konversi').value = (lebar * panjang).toFixed(3);
        }
    }

    // Fungsi Delete tetap sama dengan perbaikan visual sedikit
    function singleDelete(id) {
        Swal.fire({
            title: 'Hapus data?',
            text: "Data yang dihapus tidak dapat dikembalikan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f8fafc',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('/bahan-baku') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function toggleStatus(id, currentStatus, namaBahan) {
        const mauNonaktifkan = currentStatus === 'aktif';
        Swal.fire({
            title: mauNonaktifkan ? 'Nonaktifkan bahan ini?' : 'Aktifkan kembali bahan ini?',
            html: mauNonaktifkan
                ? `<b>${escapeHtml(namaBahan)}</b> tidak akan muncul lagi di pilihan Kedatangan Bahan & BOM Produk baru.<br><small class="text-slate-400">Riwayat & stok tidak berubah, dan bisa diaktifkan lagi kapan saja.</small>`
                : `<b>${escapeHtml(namaBahan)}</b> akan muncul lagi di pilihan transaksi baru.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: mauNonaktifkan ? '#64748b' : '#10b981',
            cancelButtonColor: '#f8fafc',
            confirmButtonText: mauNonaktifkan ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('/bahan-baku') }}/${id}/toggle-status`;
                form.innerHTML = `@csrf @method('PATCH')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Bulk Delete
    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkFooter = document.getElementById('bulk-footer');
    const bulkDeleteBtn = document.getElementById('btn-bulk-delete');
    const countCheckedSpan = document.getElementById('count-checked');

    function updateBulkUI() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        countCheckedSpan.textContent = checkedCount;

        if (checkedCount > 0) {
            bulkFooter.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
        } else {
            bulkFooter.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
        }
    }

    checkAll?.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkUI();
    });

    rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkUI));

    bulkDeleteBtn?.addEventListener('click', function() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        Swal.fire({
            title: 'Hapus Massal?',
            text: `${checkedCount} bahan terpilih akan dihapus. Bahan yang masih terikat riwayat transaksi otomatis dilewati.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#f8fafc',
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulk-delete-form').submit();
            }
        });
    });
</script>
@endpush