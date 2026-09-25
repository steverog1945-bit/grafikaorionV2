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
@php
    $otIsi = $line->order_type ?? 'quantity_only';
    $sizeQtyMap = $line ? $line->sizeQuantities->pluck('quantity', 'jersey_size_id') : collect();
@endphp
<div class="px-5 py-3 bg-indigo-50/50 border-b border-indigo-100 flex items-center justify-between">
    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Barang #<span class="line-number">1</span></span>
    <button type="button" onclick="this.closest('.jersey-line-card').remove(); if (typeof jadwalkanPreviewHarga === 'function') jadwalkanPreviewHarga(); if (typeof jadwalkanPreviewHargaSusulan === 'function') jadwalkanPreviewHargaSusulan();" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
</div>
<div class="p-5 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Barang</label>
            <input type="text" name="lines[{{ $idx }}][nama_barang]" value="{{ $line->nama_barang ?? '' }}" required placeholder="mis. Jersey MTsN Buol" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe Produk</label>
            <select name="lines[{{ $idx }}][jersey_product_type_id]" onchange="perbaruiVisibilitasLenganKerah(this)" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($productTypes as $pt)<option value="{{ $pt->id }}" data-has-sleeve-collar="{{ $pt->has_sleeve_collar ? 1 : 0 }}" @selected(($line->jersey_product_type_id ?? null) == $pt->id)>{{ $pt->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kategori</label>
            <select name="lines[{{ $idx }}][jersey_category_id]" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(($line->jersey_category_id ?? null) == $cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Bahan</label>
            <select name="lines[{{ $idx }}][jersey_material_id]" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih Bahan --</option>
                @foreach($materials as $m)<option value="{{ $m->id }}" @selected(($line->jersey_material_id ?? null) == $m->id)>{{ $m->name }}</option>@endforeach
            </select>
            @if($materials->isEmpty())
                <p class="text-[10px] text-rose-500 mt-1">Belum ada data Bahan - <a href="{{ route('jersey.master.index') }}" target="_blank" class="underline">tambah dulu di Master Data</a>.</p>
            @endif
        </div>
        <div class="wrapper-lengan-kerah-default md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Lengan (default)</label>
                <select name="lines[{{ $idx }}][jersey_sleeve_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    <option value="">-- Pilih --</option>
                    @foreach($sleeveTypes as $st)<option value="{{ $st->id }}" @selected(($line->jersey_sleeve_type_id ?? null) == $st->id)>{{ $st->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kerah (default)</label>
                <select name="lines[{{ $idx }}][jersey_collar_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    <option value="">-- Pilih --</option>
                    @foreach($collarTypes as $ct)<option value="{{ $ct->id }}" @selected(($line->jersey_collar_type_id ?? null) == $ct->id)>{{ $ct->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Keterangan Desain</label>
            <textarea name="lines[{{ $idx }}][keterangan]" rows="2" placeholder="mis. Lengan kanan bendera, dada kiri logo klub..." class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ $line->keterangan ?? '' }}</textarea>
        </div>
    </div>
    <p class="text-[10px] text-slate-400 -mt-3 wrapper-lengan-kerah-default leading-snug">Lengan/Kerah default dipakai untuk mode <b>Qty per Ukuran</b> &amp; <b>Campuran</b> - Daftar Pemain pakai pilihan masing-masing pemain sendiri.</p>

    {{-- Dropzone dimampatkan (dulu 128x128px + 2 baris teks instruksi di
         dalam kotak) - sekarang 64x64px, instruksi cuma jadi title
         (tooltip hover) supaya tidak makan tempat vertikal, status teks di
         sampingnya juga dipersingkat. --}}
    <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Thumbnail Desain <span class="text-rose-500">*</span></label>
        <div class="flex items-center gap-3">
            <div class="thumbnail-dropzone w-16 h-16 rounded-lg bg-slate-50 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shrink-0 cursor-pointer transition-all hover:border-indigo-400 hover:bg-indigo-50/30" tabindex="0" onclick="this.querySelector('.thumbnail-file-input').click()" title="Klik, tempel (Ctrl+V), atau pilih file">
                <i class="fas fa-camera text-slate-300 text-sm thumbnail-placeholder {{ ($line->thumbnail ?? null) ? 'hidden' : '' }}"></i>
                <img class="w-full h-full object-cover thumbnail-preview {{ ($line->thumbnail ?? null) ? '' : 'hidden' }}" src="{{ ($line->thumbnail ?? null) ? asset('storage/' . $line->thumbnail) : '' }}" alt="">
                <input type="file" accept="image/*" onchange="uploadThumbnailLine(this)" class="hidden thumbnail-file-input">
            </div>
            <div class="flex-1 min-w-0">
                <input type="hidden" name="lines[{{ $idx }}][thumbnail]" value="{{ $line->thumbnail ?? '' }}" class="thumbnail-path" required>
                <p class="text-[11px] text-slate-400 thumbnail-status leading-snug">{{ ($line->thumbnail ?? null) ? 'Thumbnail siap. Klik kotak untuk mengganti.' : 'Klik atau tempel (Ctrl+V) gambar desain di sini.' }}</p>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Mode Order</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @foreach(['quantity_only' => 'Qty per Ukuran', 'player_list' => 'Daftar Pemain', 'mixed' => 'Campuran'] as $val => $label)
            <label class="line-type-option relative flex items-center justify-center py-2 px-3 rounded-xl border-2 font-bold text-[11px] cursor-pointer {{ $otIsi === $val ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-100 text-slate-400' }}">
                <input type="radio" name="lines[{{ $idx }}][order_type]" value="{{ $val }}" @checked($otIsi === $val) onchange="toggleLineType(this)" class="absolute top-1.5 right-1.5">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>

    <div class="wrapper-qty-ukuran {{ $otIsi === 'player_list' ? 'hidden' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Qty per Ukuran</p>
            <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[11px] font-black">Total: <span class="qty-total-line">0</span> pcs</span>
        </div>
        @foreach($sizes->groupBy(fn ($sz) => ($sz->kelompok ?? 'dewasa') === 'anak' ? 'anak' : 'dewasa') as $kelompok => $daftarSize)
        <div class="mb-3 rounded-2xl border {{ $kelompok === 'anak' ? 'border-amber-100 bg-amber-50/40' : 'border-slate-100 bg-slate-50/50' }} p-3 grup-ukuran" data-kelompok="{{ $kelompok }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black uppercase tracking-widest {{ $kelompok === 'anak' ? 'text-amber-600' : 'text-slate-500' }}"><i class="fas {{ $kelompok === 'anak' ? 'fa-child' : 'fa-user' }} mr-1"></i>{{ $kelompok === 'anak' ? 'Ukuran Anak' : 'Ukuran Dewasa' }}</span>
                <span class="text-[10px] font-bold text-slate-400"><span class="qty-total-grup">0</span> pcs</span>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                @foreach($daftarSize as $s)
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase text-center mb-1">{{ $s->name }}@if((float) ($s->price_percent ?? 0) < 0)<span class="block text-[8px] text-emerald-500 normal-case font-bold">{{ angka((float) $s->price_percent, 0) }}%</span>@endif</label>
                    <input type="number" inputmode="numeric" name="lines[{{ $idx }}][size_quantities][{{ $s->id }}]" min="0" value="{{ $sizeQtyMap[$s->id] ?? 0 }}" onfocus="this.select()" class="input-qty-ukuran w-full bg-white border border-slate-100 rounded-lg p-2 text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-300">
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        <p class="text-[10px] text-slate-400">Tekan <b>Enter</b> untuk pindah ke ukuran berikutnya. Ukuran anak otomatis memakai selisih harga yang diatur di Master Data.</p>
    </div>

    <div class="wrapper-daftar-pemain {{ $otIsi === 'quantity_only' ? 'hidden' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daftar Pemain</p>
            <button type="button" onclick="tambahBarisPemain(this)" class="text-[10px] font-bold text-indigo-600 uppercase underline">+ Tambah Pemain</button>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead><tr class="text-[10px] text-slate-400 uppercase">
                <th class="p-1 text-left">Nama</th><th class="p-1 text-left w-16">No.</th><th class="p-1 text-left w-20">Ukuran</th>
                <th class="p-1 text-left w-20">Gender</th><th class="p-1 text-left w-24 col-lengan-kerah">Lengan</th><th class="p-1 text-left col-lengan-kerah">Kerah</th><th class="p-1 w-8"></th>
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

    <div class="line-price-preview rounded-xl bg-emerald-50 border border-emerald-100 p-3 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest">Perkiraan Harga Barang Ini</span>
            <p class="text-[10px] text-emerald-600/80 mt-0.5 line-price-preview-detail">Isi qty &amp; pilihan barang untuk lihat perkiraan harga.</p>
        </div>
        <span class="text-xl font-black text-emerald-700 line-price-preview-value">Rp 0</span>
    </div>

    <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
        <i class="fas fa-info-circle text-indigo-300"></i>
        Kebutuhan bahan dihitung otomatis dari qty x estimasi Master Data.
    </p>
</div>
