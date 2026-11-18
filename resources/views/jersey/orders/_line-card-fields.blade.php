{{--
    Isi satu kartu "Barang" pada form order jersey - dipakai di DUA tempat:
    1. create.blade.php: dirender dengan $line=null (kosong, __LINE__ jadi
       placeholder) di dalam <template>, diisi manual lewat JS saat "Tambah
       Jenis Barang" diklik.
    2. edit-lines.blade.php: dirender server-side per barang yang SUDAH ADA
       ($line diisi, $idx = id barang) supaya form langsung terisi data
       lama - operator tinggal ubah yang perlu.
    Satu partial ini menjaga markup & nama field kedua form itu selalu
    identik, supaya submit-nya bisa diproses lewat logika yang sama persis
    (JerseyOrderController::simpanBarisOrder()).
--}}
<style>
/* Rapatkan kartu barang jersey (permintaan owner): ringkas padding/margin
   supaya blok "Perkiraan Harga" terlihat tanpa scrolling. Scope hanya ke
   .jersey-line-card - tidak menyentuh halaman/layout lain. */
.jersey-line-card .px-5.py-3 { padding-top: .45rem; padding-bottom: .45rem; }
.jersey-line-card .p-5 { padding: .75rem 1rem; }
.jersey-line-card .p-5.space-y-4 > * + * { margin-top: .6rem; }
.jersey-line-card .py-2\.5 { padding-top: .45rem; padding-bottom: .45rem; }
.jersey-line-card .mb-1\.5 { margin-bottom: .25rem; }
.jersey-line-card .line-type-option { padding-top: .35rem; padding-bottom: .35rem; }
.jersey-line-card .pb-5 { padding-bottom: .75rem; }
.jersey-line-card .wrapper-qty-ukuran .mb-2 { margin-bottom: .35rem; }
.jersey-line-card .grup-ukuran { padding: .5rem .6rem; margin-bottom: .45rem; }
.jersey-line-card .grup-ukuran .mb-2 { margin-bottom: .3rem; }
.jersey-line-card .input-qty-ukuran { padding: .3rem .2rem; }
.jersey-line-card .thumbnail-dropzone { width: 3.25rem; height: 3.25rem; }
.jersey-line-card .line-price-preview { padding: .6rem .75rem; }
html.dark .jersey-mobile-form-page .jersey-line-head { background-color: color-mix(in srgb, var(--color-indigo-600) 30%, transparent); border-color: color-mix(in srgb, var(--color-indigo-500) 28%, transparent); }
html.dark .jersey-mobile-form-page .jersey-line-head .jersey-line-label { color: var(--color-indigo-400); }
html.dark .jersey-mobile-form-page .jersey-line-head button:hover { background-color: color-mix(in srgb, var(--color-rose-500) 12%, transparent); }
</style>
@php
    $otIsi = $line->order_type ?? 'quantity_only';
    $sizeQtyMap = $line ? $line->sizeQuantities->pluck('quantity', 'jersey_size_id') : collect();
@endphp
<div class="jersey-line-head px-5 py-3 bg-indigo-50/50 border-b border-indigo-100 flex items-center justify-between">
    <span class="jersey-line-label text-xs font-medium text-indigo-600 uppercase tracking-widest">Barang #<span class="line-number">1</span></span>
    <button type="button" onclick="this.closest('.jersey-line-card').remove(); if (typeof jadwalkanPreviewHarga === 'function') jadwalkanPreviewHarga(); if (typeof jadwalkanPreviewHargaSusulan === 'function') jadwalkanPreviewHargaSusulan();" class="-my-1 w-10 h-10 rounded-lg text-rose-400 hover:bg-rose-50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-rose-300" aria-label="Hapus" title="Hapus"><i class="far fa-trash-alt text-xs"></i></button>
</div>
<div class="p-5 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <label for="jersey-{{ $idx }}-nama-barang" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Nama Barang <span class="text-rose-500">*</span></label>
            <input id="jersey-{{ $idx }}-nama-barang" type="text" name="lines[{{ $idx }}][nama_barang]" value="{{ $line->nama_barang ?? '' }}" required placeholder="Deskripsikan barang pesanan" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">
        </div>
        <div>
            <label for="jersey-{{ $idx }}-tipe-produk" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Tipe Produk <span class="text-rose-500">*</span></label>
            <select id="jersey-{{ $idx }}-tipe-produk" name="lines[{{ $idx }}][jersey_product_type_id]" required onchange="perbaruiVisibilitasLenganKerah(this)" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($productTypes as $pt)<option value="{{ $pt->id }}" data-has-sleeve-collar="{{ $pt->has_sleeve_collar ? 1 : 0 }}" @selected(($line->jersey_product_type_id ?? null) == $pt->id)>{{ $pt->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="jersey-{{ $idx }}-kategori" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Kategori <span class="text-rose-500">*</span></label>
            <select id="jersey-{{ $idx }}-kategori" name="lines[{{ $idx }}][jersey_category_id]" required class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(($line->jersey_category_id ?? null) == $cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="jersey-{{ $idx }}-bahan" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Bahan <span class="text-rose-500">*</span></label>
            <select id="jersey-{{ $idx }}-bahan" name="lines[{{ $idx }}][jersey_material_id]" required class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">
                <option value="">-- Pilih Bahan --</option>
                @foreach($materials as $m)<option value="{{ $m->id }}" @selected(($line->jersey_material_id ?? null) == $m->id)>{{ $m->name }}</option>@endforeach
            </select>
            @if($materials->isEmpty())
                <p class="text-[10px] text-rose-500 mt-1">Belum ada data Bahan - <a href="{{ route('jersey.master.index') }}" target="_blank" class="underline">tambah dulu di Master Data</a>.</p>
            @endif
        </div>
        <div class="wrapper-lengan-kerah-default md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="jersey-{{ $idx }}-lengan" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Lengan <span class="text-rose-500">*</span></label>
                <select id="jersey-{{ $idx }}-lengan" name="lines[{{ $idx }}][jersey_sleeve_type_id]" required class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">
                    <option value="">-- Pilih --</option>
                    @foreach($sleeveTypes as $st)<option value="{{ $st->id }}" @selected(($line->jersey_sleeve_type_id ?? null) == $st->id)>{{ $st->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="jersey-{{ $idx }}-kerah" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Kerah <span class="text-rose-500">*</span></label>
                <select id="jersey-{{ $idx }}-kerah" name="lines[{{ $idx }}][jersey_collar_type_id]" required class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">
                    <option value="">-- Pilih --</option>
                    @foreach($collarTypes as $ct)<option value="{{ $ct->id }}" @selected(($line->jersey_collar_type_id ?? null) == $ct->id)>{{ $ct->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="jersey-{{ $idx }}-keterangan" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Keterangan </label>
                <textarea id="jersey-{{ $idx }}-keterangan" name="lines[{{ $idx }}][keterangan]" rows="2" placeholder="Tambah Keterangan" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/40 shadow-inner">{{ $line->keterangan ?? '' }}</textarea>
            </div>
            <div>
                <label for="jersey-{{ $idx }}-thumbnail" class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Thumbnail <span class="text-rose-500">*</span></label>
            <div class="flex items-center gap-3">
                <div class="thumbnail-dropzone w-14 h-14 rounded-lg bg-slate-50 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shrink-0 cursor-pointer transition-all hover:border-indigo-400 hover:bg-indigo-50/30 focus:outline-none focus:ring-2 focus:ring-indigo-400/60 focus:border-indigo-400" tabindex="0" onclick="this.querySelector('.thumbnail-file-input').click()" title="Klik, tempel (Ctrl+V), atau pilih file">
                    @php
                        $punyaBerkas = ($line->thumbnail ?? null) !== null;
                        $thumbTampil = $punyaBerkas && \App\Support\BerkasDesain::bisaDitampilkan($line->thumbnail);
                        $extThumb = $punyaBerkas ? strtoupper(\App\Support\BerkasDesain::ekstensi($line->thumbnail)) : '';
                    @endphp
                    <img loading="lazy" decoding="async" class="w-full h-full object-cover thumbnail-preview {{ $thumbTampil ? '' : 'hidden' }}" src="{{ $thumbTampil ? asset('storage/' . $line->thumbnail) : '' }}" alt="">
                    <x-thumb-berkas :path="$punyaBerkas ? $line->thumbnail : null" size="w-full h-full" iconSize="text-xl" class="rounded-lg {{ ($punyaBerkas && !$thumbTampil) ? '' : 'hidden' }}" />
                    <input type="file" accept="image/png,image/jpeg,image/webp,image/gif,image/bmp,image/svg+xml,image/tiff,application/pdf,.pdf,.cdr,.ai,.eps,.psd,.tif,.tiff,.heic" onchange="uploadThumbnailLine(this)" class="hidden thumbnail-file-input">
                </div>
                <div class="flex-1 min-w-0">
                    <input id="jersey-{{ $idx }}-thumbnail" type="hidden" name="lines[{{ $idx }}][thumbnail]" value="{{ $line->thumbnail ?? '' }}" class="thumbnail-path" required>
                    <p class="text-[11px] text-slate-400 thumbnail-status leading-snug">{{ $punyaBerkas ? ($thumbTampil ? 'Thumbnail siap. Klik kotak untuk mengganti.' : 'Berkas desain siap (' . $extThumb . '). File ada di server, dibuka tim produksi.') : 'Klik atau paste (Ctrl+V) gambar' }}</p>
                </div>
            </div>
        </div>
        </div>{{-- /keterangan + thumbnail (2 kolom) --}}
    </div>{{-- /grid utama kartu barang --}}

    {{--
        pb-5 saja, TANPA px-5. Kartu ini tidak punya padding sendiri
        (padding kartu = 0), jadi pemilih mode berdiri tepat di tepi
        kartu. Konten di bawahnya (wrapper-qty-ukuran /
        wrapper-daftar-pemain) mendapat px-5 sendiri - keduanya mulai
        dari titik yang sama, itulah yang membuatnya "lurus".

        Versi sebelumnya memberi px-5 di sini, yang mendorong pemilih
        mode 20px lebih dalam daripada konten - kesalahan berlawanan
        arah, tapi sama salah.
    --}}
    <div class="pb-5">
        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1.5">Mode Order</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @foreach(['quantity_only' => 'Qty per Ukuran', 'player_list' => 'Daftar Pemain', 'mixed' => 'Campuran'] as $val => $label)
            <label class="line-type-option relative flex items-center justify-center py-2 px-3 rounded-xl border-2 font-medium text-[11px] cursor-pointer {{ $otIsi === $val ? 'border-indigo-500 bg-indigo-50 text-indigo-800' : 'border-slate-200 text-slate-600' }}">
                <input type="radio" name="lines[{{ $idx }}][order_type]" value="{{ $val }}" @checked($otIsi === $val) onchange="toggleLineType(this)" class="absolute top-1.5 right-1.5">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>

    <div class="wrapper-qty-ukuran {{ $otIsi === 'player_list' ? 'hidden' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Qty per Ukuran</p>
            <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[11px] font-medium">Total: <span class="qty-total-line">0</span> pcs</span>
        </div>
        @foreach($sizes->groupBy(fn ($sz) => ($sz->kelompok ?? 'dewasa') === 'anak' ? 'anak' : 'dewasa') as $kelompok => $daftarSize)
        <div class="mb-3 rounded-2xl border {{ $kelompok === 'anak' ? 'border-amber-100 bg-amber-50/40' : 'border-slate-100 bg-slate-50/50' }} p-3 grup-ukuran" data-kelompok="{{ $kelompok }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-medium uppercase tracking-widest {{ $kelompok === 'anak' ? 'text-amber-600' : 'text-slate-500' }}"><i class="fas {{ $kelompok === 'anak' ? 'fa-child' : 'fa-user' }} mr-1"></i>{{ $kelompok === 'anak' ? 'Ukuran Anak' : 'Ukuran Dewasa' }}</span>
                <span class="text-[10px] font-medium text-slate-400"><span class="qty-total-grup">0</span> pcs</span>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 {{ $kelompok === 'anak' ? 'md:grid-cols-6' : 'md:grid-cols-6 lg:grid-cols-8' }} gap-2">
                @foreach($daftarSize as $s)
                <div>
                    @php
                        $bagianNama = explode(' (', rtrim($s->name, ')'), 2);
                        $namaUkuran = $bagianNama[0];
                        $usiaUkuran = $bagianNama[1] ?? null;
                        $persenUkuran = (float) ($s->price_percent ?? 0);
                    @endphp
                    {{--
                        Persentase harga dihapus dari sini. Angka itu tidak
                        dibutuhkan saat memilih ukuran, dan karena
                        panjangnya berbeda-beda (-3% vs -10%), baris
                        ukurannya tidak pernah lurus - yang satu turun
                        lebih dalam daripada yang lain. Kalau nanti
                        ingin ditampilkan lagi, taruh di tempat yang
                        tidak mengubah tinggi baris.
                    --}}
                    {{--
                        Nilai size_quantities SELALU diisi dari database,
                        termasuk saat mode-nya Daftar Pemain - sengaja, supaya
                        berpindah ke mode Campuran tidak menghapus data yang
                        sudah ada.

                        Tapi di mode Daftar Pemain inputnya DISABLE, bukan
                        sekadar disembunyikan. Wrapper `hidden` hanya
                        menyembunyikan tampilan; input di dalamnya tetap
                        terkirim ke server. Itulah sumber bug qty 35 -> 70:
                        35 pemain + sisa size_quantities 35 dijumlahkan.
                        Disable = tidak terkirim sama sekali, jadi tidak ada
                        data basi yang bisa ikut terhitung.
                    --}}
                    <label class="block text-[10px] font-medium text-slate-400 uppercase text-center mb-1 leading-tight break-words min-h-[24px]">
                        {{ $namaUkuran }}
                        @if($usiaUkuran)<span class="block text-[9px] text-amber-600 normal-case font-medium">{{ $usiaUkuran }}</span>@endif
                    </label>
                    <input type="number" inputmode="numeric" name="lines[{{ $idx }}][size_quantities][{{ $s->id }}]" min="0" value="{{ $sizeQtyMap[$s->id] ?? 0 }}" onfocus="this.select()" {{ $otIsi === 'player_list' ? 'disabled' : '' }} class="input-qty-ukuran w-full bg-white border border-slate-100 rounded-lg p-2 text-xs font-medium text-center focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    <x-hint tip="Tekan Enter untuk pindah ke ukuran berikutnya. Ukuran anak otomatis memakai selisih harga yang diatur di Master Data." icon="keyboard" />
    </div>

    <div class="wrapper-daftar-pemain {{ $otIsi === 'quantity_only' ? 'hidden' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Daftar Pemain</p>
            <div class="flex items-center gap-3">
                @include('jersey.orders._csv-pemain-card-buttons')
                <button type="button" onclick="tambahBarisPemain(this)" class="text-[10px] font-medium text-indigo-600 uppercase underline">+ Tambah Pemain</button>
            </div>
        </div>
        @include('jersey.orders._csv-pemain-hasil-panel')
        <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead><tr class="text-[10px] text-slate-400 uppercase">
                <th class="p-1 text-left w-32 md:w-40">Nama</th><th class="p-1 text-left w-12">No.</th><th class="p-1 text-left w-40">Ukuran <span class="text-rose-500">*</span></th>
                <th class="p-1 text-left w-14">Gender</th><th class="p-1 text-left w-24 col-lengan-kerah">Lengan <span class="text-rose-500">*</span></th><th class="p-1 text-left w-28 col-lengan-kerah">Kerah <span class="text-rose-500">*</span></th><th class="p-1 w-8"></th>
            </tr></thead>
            <tbody class="tbody-pemain divide-y divide-slate-50">
                @if($line)
                    @foreach($line->items as $itemIdx => $item)
                        @include('jersey.orders._player-row-fields', ['prefix' => "lines[{$idx}]", 'itemIdx' => $itemIdx, 'item' => $item, 'sizes' => $sizes, 'sleeveTypes' => $sleeveTypes, 'collarTypes' => $collarTypes])
                    @endforeach
                @endif
            </tbody>
        </table>
        </div>
    </div>

    <div class="line-price-preview rounded-xl bg-emerald-50 border border-emerald-100 p-3 space-y-2">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <span class="text-[10px] font-medium text-emerald-700 uppercase tracking-widest">Perkiraan Harga Barang Ini</span>
                <p class="text-[10px] text-emerald-600/80 mt-0.5 line-price-preview-detail">Isi qty &amp; pilihan barang untuk lihat perkiraan harga.</p>
            </div>
            <span class="text-xl font-medium text-emerald-700 line-price-preview-value">Rp 0</span>
        </div>
        <ul class="line-price-preview-list hidden text-[11px] text-emerald-800 divide-y divide-emerald-100/80"></ul>
    </div>

    <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
        <i class="fas fa-info-circle text-slate-500"></i>
        Kebutuhan bahan dihitung otomatis dari qty x estimasi Master Data.
    </p>
</div>
