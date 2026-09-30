@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Jasa Jahit Borongan"
                   subtitle="Harga borongan per jenis jahit, dan pencatatan pengeluaran untuk orderan yang sedang dijahit">
        <x-slot:actions>
            <x-button onclick="toggleModal('jahit-buat')">
                <i class="fas fa-plus"></i>
                Jenis Jahit Baru
            </x-button>
            <x-button onclick="toggleModal('jahit-catat')">
                <i class="fas fa-receipt"></i>
                Catat Pengeluaran
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Ringkasan tiga angka, satu baris.

         CATATAN: pakai md:grid-cols-3, BUKAN @md:grid-cols-3. Versi
         berprefix "@" adalah container query dan hanya berlaku kalau
         ada leluhur ber-atribut @container. Tanpa itu kelasnya diam-diam
         tidak pernah berlaku, dan ketiga kartu turun ke satu kolom -
         itulah yang bikin halamannya terlihat renggang dan kosong.
         Seluruh halaman lain di aplikasi ini memakai md: (breakpoint
         layar), jadi ini ikut pola itu. --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-lg border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-medium text-slate-400">Pengeluaran Jahit Bulan Ini</p>
            <p class="text-lg font-medium text-slate-900 mt-0.5">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-medium text-slate-400">Jenis Jahit Terdaftar</p>
            <p class="text-lg font-medium text-slate-900 mt-0.5">{{ $jenis->count() }}</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-medium text-slate-400">Orderan Menunggu Jahit</p>
            <p class="text-lg font-medium text-slate-900 mt-0.5">{{ $orderan->count() }}</p>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         KATALOG JENIS JAHIT
         Harga borongan disimpan apa adanya, bukan dihitung. Kalau diubah di
         sini, catatan pengeluaran yang sudah lewat TIDAK ikut berubah -
         karena harga saat pencatatan dibekukan. Itu disengaja: harga yang
         dibayar penjahit tahun lalu harus tetap tercetak seperti adanya.
    ------------------------------------------------------------------- --}}
    <x-card :padding="false" class="mb-6">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-sm font-medium text-slate-900">Katalog Jenis Jahit &amp; Harga Borongan</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">
                Kolom Finishing menentukan orderan mana yang muncul di form Catat Pengeluaran.
            </p>
        </div>
        <x-table bare>
            <thead>
                <tr>
                    <th class="text-left">Jenis Jahit</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-center">Finishing</th>
                    <th class="text-right">Harga Borongan</th>
                    <th class="text-center">Terpakai</th>
                    <th class="text-right">Manajemen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenis as $j)
                <tr>
                    <td>
                        <span class="text-sm font-medium text-slate-700">{{ $j->nama_jahit }}</span>
                        @if($j->keterangan)
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $j->keterangan }}</p>
                        @endif
                    </td>
                    <td class="text-center text-sm text-slate-600">{{ $j->nama_satuan ?? '-' }}</td>
                    <td class="text-center text-sm">
                        @if($j->id_finishing)
                            <span class="text-slate-600">{{ $j->nama_finishing }}</span>
                        @else
                            {{-- Tanpa finishing = jenis jahit ini tidak pernah
                                 muncul otomatis di form Catat. Bukan error,
                                 tapi perlu terlihat supaya kasir tahu harus
                                 menautkannya. --}}
                            <span class="text-slate-400">Belum ditautkan</span>
                        @endif
                    </td>
                    <td class="text-right text-sm font-medium text-slate-700">
                        Rp {{ number_format($j->harga_borongan, 0, ',', '.') }}
                    </td>
                    <td class="text-center text-sm text-slate-600">
                        {{ $j->jumlah_catatan ?? 0 }}× catatan
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button type="button" class="w-9 h-9 flex items-center justify-center text-slate-400 rounded-lg transition-all hover:text-indigo-500 hover:bg-indigo-50" title="Ubah jenis jahit"
                                    onclick='editJenisJahit({{ $j->id_jasa_jahit }}, @json($j->nama_jahit), "{{ $j->harga_borongan }}", "{{ $j->id_satuan }}", "{{ $j->id_finishing }}", @json($j->keterangan ?? ""))'>
                                <i class="fas fa-pen text-[11px]"></i>
                            </button>
                            <form action="{{ route('jasa-jahit.destroy', $j->id_jasa_jahit) }}" method="POST" class="inline"
                                  data-konfirmasi="Hapus jenis jahit &quot;{{ $j->nama_jahit }}&quot;? Catatan pengeluarannya ikut terhapus.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-9 h-9 flex items-center justify-center text-slate-400 rounded-lg transition-all hover:text-rose-500 hover:bg-rose-50" title="Hapus jenis jahit">
                                    <i class="fas fa-trash text-[11px]"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <x-empty-state title="Belum ada jenis jahit"
                                       description="Tambahkan jenis jahit beserta harga borongannya. Setelah itu, orderan yang finishing-nya sudah ditautkan akan muncul di form Catat Pengeluaran." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-table>
    </x-card>

    {{-- ------------------------------------------------------------------
         CATAT PENGELUARAN
    ------------------------------------------------------------------- --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-sm font-medium text-slate-900">Riwayat Pengeluaran Jahit</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">100 catatan terbaru</p>
        </div>
        <x-table bare>
            <thead>
                <tr>
                    <th class="text-left">Tanggal</th>
                    <th class="text-left">Jenis Jahit</th>
                    <th class="text-left">Order</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                    <th class="text-left">Dicatat Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                <tr>
                    <td class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($r->tgl_catat)->format('d/m/Y') }}</td>
                    <td class="text-sm font-medium text-slate-700">{{ $r->nama_jahit }}</td>
                    <td class="text-sm">
                        @if($r->nomor_nota)
                            <span class="text-slate-600">{{ $r->nomor_nota }}</span>
                            @if($r->nama_file)
                                <p class="text-[11px] text-slate-400">{{ $r->nama_file }}</p>
                            @endif
                        @else
                            <span class="text-slate-400">Tanpa order</span>
                        @endif
                    </td>
                    <td class="text-right text-sm text-slate-600">{{ angka($r->qty, 4) }}</td>
                    <td class="text-right text-sm text-slate-600">Rp {{ number_format($r->harga_snapshot, 0, ',', '.') }}</td>
                    <td class="text-right text-sm font-medium text-slate-700">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                    <td class="text-sm text-slate-500">{{ $r->username ?? '-' }}</td>
                    <td class="text-right">
                        <form action="{{ route('jasa-jahit.catat.destroy', $r->id_jasa_jahit_catat) }}" method="POST" class="inline"
                              data-konfirmasi="Hapus catatan ini? Catatan tidak bisa diubah, jadi kalau salah hapus lalu catat ulang yang benar.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-9 h-9 flex items-center justify-center text-slate-400 rounded-lg transition-all hover:text-rose-500 hover:bg-rose-50" title="Hapus catatan">
                                <i class="fas fa-trash text-[11px]"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <x-empty-state title="Belum ada pengeluaran jahit"
                                       description="Catat pengeluaran jahit dari tombol Catat Pengeluaran." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-table>
    </x-card>
</div>

{{-- ================= MODAL BUAT JENIS JAHIT ================= --}}
<x-modal id="jahit-buat" title="Tambah Jenis Jahit"
         subtitle="Harga borongan per satuan, disepakati dengan penjahit"
         icon="fa-scissors" maxWidth="md">
    <form action="{{ route('jasa-jahit.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-[10px]">Nama Jenis Jahit</label>
            <input type="text" name="nama_jahit" placeholder="Contoh: Jahit Keliling Banner" required maxlength="100"
                   class="w-full text-sm font-medium text-slate-700">
        </div>

        <div>
            <label class="block text-[10px]">Harga Borongan (Rp) <span class="normal-case font-normal text-slate-400">- per satuan, disimpan apa adanya</span></label>
            <input type="text" inputmode="numeric" name="harga_borongan" placeholder="0" required
                   class="w-full text-sm font-medium text-slate-700 format-ribuan">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px]">Satuan</label>
                <select name="id_satuan" class="w-full text-sm font-medium text-slate-700 appearance-none">
                    <option value="">-- Pilih Satuan --</option>
                    @foreach($satuan as $s)
                        <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                {{-- Ini yang menentukan orderan mana yang muncul otomatis
                     di form Catat. Tanpa tautan ini, jenis jahit tetap bisa
                     dicatat, tapi kasir harus memilih baris ordernya manual
                     setiap kali. --}}
                <label class="block text-[10px]">Finishing <span class="normal-case font-normal text-slate-400">- penanda orderan perlu dijahit</span></label>
                <select name="id_finishing" class="w-full text-sm font-medium text-slate-700 appearance-none">
                    <option value="">-- Belum Ditautkan --</option>
                    @foreach($finishing as $f)
                        <option value="{{ $f->id_finishing }}">{{ $f->nama_finishing }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-[10px]">Keterangan</label>
            <textarea name="keterangan" rows="2" maxlength="1000" placeholder="Contoh: harga untuk keliling 1 banner, sudah termasuk benang"
                      class="w-full text-sm font-medium text-slate-700"></textarea>
        </div>

        <x-slot:footer>
            <x-button type="button" variant="secondary" onclick="toggleModal('jahit-buat')">Batal</x-button>
            <x-button type="submit">Simpan Jenis Jahit</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- ================= MODAL UBAH JENIS JAHIT ================= --}}
<x-modal id="jahit-ubah" title="Ubah Jenis Jahit"
         subtitle="Catatan lama memakai harga yang sudah dibekukan"
         icon="fa-pen" maxWidth="md">
    <form action="" method="POST" id="form-ubah-jahit" class="space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="id_jasa_jahit" id="ubah-id">

        <div>
            <label class="block text-[10px]">Nama Jenis Jahit</label>
            <input type="text" name="nama_jahit" id="ubah-nama" required maxlength="100"
                   class="w-full text-sm font-medium text-slate-700">
        </div>

        <div>
            <label class="block text-[10px]">Harga Borongan (Rp)</label>
            <input type="text" inputmode="numeric" name="harga_borongan" id="ubah-harga" required
                   class="w-full text-sm font-medium text-slate-700 format-ribuan">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px]">Satuan</label>
                <select name="id_satuan" id="ubah-satuan" class="w-full text-sm font-medium text-slate-700 appearance-none">
                    <option value="">-- Pilih Satuan --</option>
                    @foreach($satuan as $s)
                        <option value="{{ $s->id_satuan }}">{{ $s->nama_satuan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px]">Finishing</label>
                <select name="id_finishing" id="ubah-finishing" class="w-full text-sm font-medium text-slate-700 appearance-none">
                    <option value="">-- Belum Ditautkan --</option>
                    @foreach($finishing as $f)
                        <option value="{{ $f->id_finishing }}">{{ $f->nama_finishing }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-[10px]">Keterangan</label>
            <textarea name="keterangan" id="ubah-keterangan" rows="2" maxlength="1000"
                      class="w-full text-sm font-medium text-slate-700"></textarea>
        </div>

        <x-slot:footer>
            <x-button type="button" variant="secondary" onclick="toggleModal('jahit-ubah')">Batal</x-button>
            <x-button type="submit">Simpan Perubahan</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- ================= MODAL CATAT PENGELUARAN ================= --}}
<x-modal id="jahit-catat" title="Catat Pengeluaran Jahit"
         subtitle="Untuk orderan yang sedang membutuhkan jahit"
         icon="fa-receipt" maxWidth="lg">
    <form action="{{ route('jasa-jahit.catat.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-[10px]">Jenis Jahit</label>
            <select name="id_jasa_jahit" id="catat-jenis" required
                    class="w-full text-sm font-medium text-slate-700 appearance-none"
                    onchange="isiHargaOtomatis()">
                <option value="">-- Pilih Jenis Jahit --</option>
                @foreach($jenis as $j)
                    <option value="{{ $j->id_jasa_jahit }}"
                            data-harga="{{ $j->harga_borongan }}">{{ $j->nama_jahit }}</option>
                @endforeach
            </select>
        </div>

        <div>
            {{-- Baris order yang ditawarkan BUKAN daftar semua nota, tapi
                 hanya yang finishing-nya sudah ditautkan ke salah satu jenis
                 jahit di katalog. Jadi kasir tidak perlu mencari nomor nota
                 di antara ratusan order. --}}
            <label class="block text-[10px]">Orderan <span class="normal-case font-normal text-slate-400">- hanya yang finishing-nya ditandai jahit</span></label>
            <select name="id_transaksi_detail" id="catat-order" class="w-full text-sm font-medium text-slate-700 appearance-none">
                <option value="">-- Tanpa Order (biaya lain) --</option>
                @forelse($orderan as $o)
                    <option value="{{ $o->id_detail }}"
                            data-qty="{{ $o->qty }}"
                            data-nama="{{ $o->nama_finishing }}">
                        {{ $o->nomor_nota }} - {{ $o->nama_file ?? $o->nama_produk }} ({{ $o->nama_finishing }}, qty {{ $o->qty }})
                    </option>
                @empty
                    <option value="" disabled>-- Belum ada orderan yang ditandai perlu jahit --</option>
                @endforelse
            </select>
            @if($orderan->isEmpty())
                <p class="text-[11px] text-amber-600 mt-1.5 ml-2">
                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                    Tidak ada orderan aktif dengan finishing jahit. Tautan finishing di katalog
                    jenis jahit menentukan mana yang muncul di sini.
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px]">Tanggal Catat</label>
                <input type="date" name="tgl_catat" value="{{ date('Y-m-d') }}" required
                       class="w-full text-sm font-medium text-slate-700">
            </div>
            <div>
                <label class="block text-[10px]">Qty</label>
                <input type="number" name="qty" id="catat-qty" value="1" step="0.0001" min="0.0001" required oninput="hitungTotalJahit()"
                       class="w-full text-sm font-medium text-slate-700">
            </div>
        </div>

        <div>
            <label class="block text-[10px]">Harga Satuan (Rp) <span class="normal-case font-normal text-slate-400">- terisi otomatis dari harga borongan</span></label>
            <input type="text" inputmode="numeric" name="harga" id="catat-harga" oninput="hitungTotalJahit()"
                   class="w-full text-sm font-medium text-slate-700 format-ribuan">
        </div>

        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 flex items-center justify-between">
            <span class="text-[11px] font-medium text-slate-500">Total Pengeluaran</span>
            <span class="text-base font-medium text-slate-900" id="catat-total">Rp 0</span>
        </div>

        <div>
            <label class="block text-[10px]">Catatan</label>
            <textarea name="catatan" rows="2" maxlength="1000" placeholder="Contoh: 2 elicited banner, dibayar tunai"
                      class="w-full text-sm font-medium text-slate-700"></textarea>
        </div>

        <x-slot:footer>
            <x-button type="button" variant="secondary" onclick="toggleModal('jahit-catat')">Batal</x-button>
            <x-button type="submit">Catat Pengeluaran</x-button>
        </x-slot:footer>
    </form>
</x-modal>

<script>
    // Harga borongan ikut ke field harga supaya kasir tidak perlu mengetik
    // ulang - tapi fieldnya tetap bisa diubah kalau harga yang benar-benar
    // disepakati tahun ini berbeda dari harga katalog.
    function isiHargaOtomatis() {
        const sel = document.getElementById('catat-jenis');
        const harga = document.getElementById('catat-harga');
        if (!sel || !harga) return;
        const opt = sel.options[sel.selectedIndex];
        const nilai = opt && opt.dataset.harga ? parseFloat(opt.dataset.harga) : 0;
        harga.value = nilai > 0 ? formatRupiah(nilai) : '';
        hitungTotalJahit();
    }

    function hitungTotalJahit() {
        const inputQty = document.getElementById('catat-qty');
        const inputHarga = document.getElementById('catat-harga');
        const total = document.getElementById('catat-total');
        if (!inputQty || !inputHarga || !total) return;
        const qty = parseFloat(inputQty.value) || 0;
        const harga = cleanRupiah(inputHarga.value);
        total.textContent = 'Rp ' + formatRupiah(Math.round(qty * harga));
    }

    // Keterangan ikut diteruskan ke modal ubah. Kalau tidak, fieldnya
    // selalu kosong - dan karena form ini memakai POST biasa (bukan AJAX),
    // penghapusan yang tidak disengaja tidak akan ketahuan.
    function editJenisJahit(id, nama, harga, idSatuan, idFinishing, keterangan) {
        document.getElementById('ubah-id').value = id;
        document.getElementById('ubah-nama').value = nama;
        document.getElementById('ubah-harga').value = formatRupiah(parseFloat(harga) || 0);
        document.getElementById('ubah-satuan').value = idSatuan || '';
        document.getElementById('ubah-finishing').value = idFinishing || '';
        document.getElementById('ubah-keterangan').value = keterangan || '';
        document.getElementById('form-ubah-jahit').action = '{{ url('/jasa-jahit') }}/' + id;
        toggleModal('jahit-ubah');
    }

    // Dipanggil saat modal Catat dibuka: harga ikut terisi kalau jenis
    // jahit sudah dipilih sebelumnya.
    document.addEventListener('DOMContentLoaded', function () {
        const asli = window.toggleModal;
        if (typeof asli === 'function') {
            window.toggleModal = function (id) {
                const hasil = asli.apply(this, arguments);
                if (id === 'jahit-catat') isiHargaOtomatis();
                return hasil;
            };
        }
    });
</script>
@endsection
