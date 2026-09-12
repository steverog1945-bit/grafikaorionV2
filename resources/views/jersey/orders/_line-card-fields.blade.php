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
<div class="px-6 py-4 bg-indigo-50/50 border-b border-indigo-100 flex items-center justify-between">
    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Barang #<span class="line-number">1</span></span>
    <button type="button" onclick="this.closest('.jersey-line-card').remove()" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
</div>
<div class="p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Barang</label>
            <input type="text" name="lines[{{ $idx }}][nama_barang]" value="{{ $line->nama_barang ?? '' }}" required placeholder="mis. Jersey MTsN Buol" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe Produk</label>
            <select name="lines[{{ $idx }}][jersey_product_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($productTypes as $pt)<option value="{{ $pt->id }}" @selected(($line->jersey_product_type_id ?? null) == $pt->id)>{{ $pt->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
            <select name="lines[{{ $idx }}][jersey_category_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(($line->jersey_category_id ?? null) == $cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bahan</label>
            <select name="lines[{{ $idx }}][jersey_material_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih Bahan --</option>
                @foreach($materials as $m)<option value="{{ $m->id }}" @selected(($line->jersey_material_id ?? null) == $m->id)>{{ $m->name }}</option>@endforeach
            </select>
            @if($materials->isEmpty())
                <p class="text-[10px] text-rose-500 mt-1">Belum ada data Bahan - <a href="{{ route('jersey.master.index') }}" target="_blank" class="underline">tambah dulu di Master Data</a>.</p>
            @endif
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Lengan (default)</label>
            <select name="lines[{{ $idx }}][jersey_sleeve_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($sleeveTypes as $st)<option value="{{ $st->id }}" @selected(($line->jersey_sleeve_type_id ?? null) == $st->id)>{{ $st->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kerah (default)</label>
            <select name="lines[{{ $idx }}][jersey_collar_type_id]" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                <option value="">-- Pilih --</option>
                @foreach($collarTypes as $ct)<option value="{{ $ct->id }}" @selected(($line->jersey_collar_type_id ?? null) == $ct->id)>{{ $ct->name }}</option>@endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Keterangan Desain</label>
            <textarea name="lines[{{ $idx }}][keterangan]" rows="2" placeholder="mis. Lengan kanan bendera, dada kiri logo klub..." class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ $line->keterangan ?? '' }}</textarea>
        </div>
    </div>
    <p class="text-[10px] text-slate-400 -mt-4">Lengan &amp; Kerah default dipakai penuh untuk mode <b>Qty per Ukuran</b>, dan sebagai bawaan bagian qty-per-ukuran pada mode <b>Campuran</b> - baris Daftar Pemain di bawah selalu memakai pilihan masing-masing pemain sendiri.</p>

    <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
            Thumbnail Desain <span class="text-rose-500">*</span>
            <span class="normal-case font-normal text-slate-400">- acuan warna &amp; bentuk untuk laporan produksi</span>
        </label>
        <div class="flex flex-col sm:flex-row items-start gap-4">
            <div class="thumbnail-dropzone w-32 h-32 rounded-xl bg-slate-50 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center overflow-hidden shrink-0 cursor-pointer transition-all hover:border-indigo-400 hover:bg-indigo-50/30" tabindex="0" onclick="this.querySelector('.thumbnail-file-input').click()">
                <i class="fas fa-camera text-slate-300 text-lg thumbnail-placeholder {{ ($line->thumbnail ?? null) ? 'hidden' : '' }}"></i>
                <span class="text-[9px] text-slate-400 font-bold uppercase text-center px-2 mt-1 thumbnail-placeholder {{ ($line->thumbnail ?? null) ? 'hidden' : '' }}">Klik, Tempel (Ctrl+V), atau Pilih File</span>
                <img class="w-full h-full object-cover thumbnail-preview {{ ($line->thumbnail ?? null) ? '' : 'hidden' }}" src="{{ ($line->thumbnail ?? null) ? asset('storage/' . $line->thumbnail) : '' }}" alt="">
                <input type="file" accept="image/*" onchange="uploadThumbnailLine(this)" class="hidden thumbnail-file-input">
            </div>
            <div class="flex-1">
                <input type="hidden" name="lines[{{ $idx }}][thumbnail]" value="{{ $line->thumbnail ?? '' }}" class="thumbnail-path" required>
                <p class="text-xs text-slate-400 thumbnail-status">{{ ($line->thumbnail ?? null) ? 'Thumbnail siap. Klik kotak untuk mengganti.' : 'Belum ada gambar - klik kotak di samping lalu pilih file, atau salin gambar (mis. dari WhatsApp/desain) dan tempel (Ctrl+V) di sini.' }}</p>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mode Order</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @foreach(['quantity_only' => 'Qty per Ukuran', 'player_list' => 'Daftar Pemain', 'mixed' => 'Campuran'] as $val => $label)
            <label class="line-type-option relative flex items-center justify-center py-3 px-3 rounded-xl border-2 font-bold text-[11px] cursor-pointer {{ $otIsi === $val ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-100 text-slate-400' }}">
                <input type="radio" name="lines[{{ $idx }}][order_type]" value="{{ $val }}" @checked($otIsi === $val) onchange="toggleLineType(this)" class="absolute top-1.5 right-1.5">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>

    <div class="wrapper-qty-ukuran {{ $otIsi === 'player_list' ? 'hidden' : '' }}">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Qty per Ukuran</p>
        <div class="grid grid-cols-3 md:grid-cols-8 gap-2">
            @foreach($sizes as $s)
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase text-center mb-1">{{ $s->name }}</label>
                <input type="number" name="lines[{{ $idx }}][size_quantities][{{ $s->id }}]" min="0" value="{{ $sizeQtyMap[$s->id] ?? 0 }}" class="w-full bg-slate-50 border-none rounded-lg p-2 text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500/20">
            </div>
            @endforeach
        </div>
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
                <th class="p-1 text-left w-20">Gender</th><th class="p-1 text-left w-24">Lengan</th><th class="p-1 text-left">Kerah</th><th class="p-1 w-8"></th>
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

    <p class="text-[11px] text-slate-400 bg-slate-50 rounded-xl p-3">
        <i class="fas fa-info-circle text-indigo-400"></i>
        Spesifikasi kebutuhan bahan (meter kain per komponen) dihitung <strong>otomatis</strong> dari qty per ukuran x estimasi di Master Data, dan akan terlihat di halaman detail order &amp; SPK setelah disimpan.
    </p>
</div>
