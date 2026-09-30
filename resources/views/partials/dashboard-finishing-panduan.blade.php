{{--
    Panduan visual finishing item cetak: ilustrasi + perbandingan singkat, agar semua orang (kasir, desain, produksi)
    paham beda tiap finishing. Ilustrasi berupa gambar vektor (SVG) buatan sendiri, bukan foto produk.
    Dipakai di dashboard Administrator/Kasir, Operator Desain, dan Operator Produksi.
--}}
@php
    $garis = 'stroke="#64748b" stroke-width="1.5"';
    $kartu = [
        [
            'judul' => 'Mata Ayam', 'foto' => ['file' => 'mata-ayam.jpg', 'nama' => 'Grommets of a New Zealand flag', 'oleh' => 'Panamitsu', 'lisensi' => 'CC BY-SA 4.0', 'url' => 'https://commons.wikimedia.org/wiki/File:Grommets_of_a_New_Zealand_flag.jpg'], 'grup' => 'banner', 'tag' => 'Banner',
            'apa' => 'Cincin logam (eyelet) yang dipasang di tepi banner sebagai lubang untuk tali atau pengait gantung.',
            'cocok' => 'Spanduk & banner outdoor yang digantung atau diikat.',
            'catatan' => 'Jarak antar lubang dibuat merata (umumnya sekitar 40-60 cm) dan sisakan lebihan bahan di tepi supaya tidak sobek.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Banner dengan mata ayam di tepi">
  <rect x="12" y="22" width="136" height="58" rx="3" fill="#e0e7ff" stroke="#6366f1" stroke-width="1.5"/>
  <text x="80" y="56" text-anchor="middle" font-size="11" font-weight="700" fill="#6366f1" font-family="sans-serif">BANNER</text>
  <g fill="#e2e8f0" stroke="#64748b" stroke-width="1.5">
    <circle cx="22" cy="32" r="6.5"/><circle cx="138" cy="32" r="6.5"/><circle cx="22" cy="70" r="6.5"/><circle cx="138" cy="70" r="6.5"/>
    <circle cx="80" cy="32" r="6.5"/><circle cx="80" cy="70" r="6.5"/>
  </g>
  <g fill="#fff"><circle cx="22" cy="32" r="3"/><circle cx="138" cy="32" r="3"/><circle cx="22" cy="70" r="3"/><circle cx="138" cy="70" r="3"/><circle cx="80" cy="32" r="3"/><circle cx="80" cy="70" r="3"/></g>
  <path d="M28 14h46M86 14h46" stroke="#94a3b8" stroke-width="1" stroke-dasharray="2 2"/>
  <text x="80" y="12" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="sans-serif">jarak merata</text>
</svg>
SVG,
        ],
        [
            'judul' => 'Kisscut', 'foto' => ['file' => 'kisscut-stiker.png', 'nama' => 'FreeBSD Sticker Sheet (16 stickers) - contoh lembar stiker', 'oleh' => 'FAndrey', 'lisensi' => 'CC BY 2.0', 'url' => 'https://commons.wikimedia.org/wiki/File:FreeBSD_Sticker_Sheet_(16_stickers)_(44107423534).png'], 'grup' => 'stiker', 'tag' => 'Stiker',
            'apa' => 'Potong setengah: hanya lapisan stiker yang terpotong, kertas alas (backing) tetap utuh sehingga stiker mudah dikelupas satuan.',
            'cocok' => 'Stiker label, stiker kemasan, lembaran stiker banyak bentuk.',
            'catatan' => 'Dikerjakan mesin cutting; pastikan garis potong (cutline) ada di file desain dan kedalaman potong pas.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Lembar stiker dengan garis kisscut">
  <rect x="14" y="10" width="132" height="80" rx="4" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1.5"/>
  <rect x="26" y="22" width="44" height="30" rx="10" fill="#c7d2fe" stroke="#6366f1" stroke-width="1.5" stroke-dasharray="4 3"/>
  <circle cx="108" cy="38" r="20" fill="#fde68a" stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="4 3"/>
  <rect x="30" y="62" width="50" height="20" rx="10" fill="#bbf7d0" stroke="#22c55e" stroke-width="1.5" stroke-dasharray="4 3"/>
  <path d="M112 62 l22 0 l0 18 z" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.2"/>
  <path d="M112 62 q10 -8 22 0" fill="#c7d2fe" stroke="#6366f1" stroke-width="1.2"/>
  <text x="80" y="8" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="sans-serif">garis putus = potongan</text>
</svg>
SVG,
        ],
        [
            'judul' => 'Laminasi Panas', 'foto' => ['file' => 'laminasi-panas.jpg', 'nama' => 'GBC Fusion 1000L Laminator', 'oleh' => 'TaurusEmerald', 'lisensi' => 'CC BY-SA 4.0', 'url' => 'https://commons.wikimedia.org/wiki/File:GBC_Fusion_1000L_Laminator.jpg'], 'grup' => 'laminasi', 'tag' => 'Laminasi',
            'apa' => 'Lapisan film pelindung yang direkatkan dengan roller panas (hot roll) sehingga menyatu kuat dengan hasil cetak.',
            'cocok' => 'Kertas: poster, foto, kartu, brosur - butuh perlindungan dan tampilan glossy/doff yang awet.',
            'catatan' => 'Hindari bahan yang tidak tahan panas. Mesin harus pemanasan dulu, jadi cocok untuk jumlah banyak.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Laminasi panas dengan roller panas">
  <g transform="translate(-20 0)">
  <circle cx="80" cy="26" r="16" fill="#fecaca" stroke="#ef4444" stroke-width="1.5"/>
  <circle cx="80" cy="26" r="4" fill="#ef4444"/>
  <path d="M52 12c4-4 4 4 8 0M100 12c4-4 4 4 8 0M56 40c4-4 4 4 8 0M96 40c4-4 4 4 8 0" stroke="#ef4444" stroke-width="1.4" fill="none" stroke-linecap="round"/>
  <rect x="24" y="52" width="112" height="9" fill="#bae6fd" stroke="#38bdf8" stroke-width="1"/>
  <rect x="24" y="61" width="112" height="4" fill="#fcd34d"/>
  <rect x="24" y="65" width="112" height="9" fill="#a5b4fc" stroke="#6366f1" stroke-width="1"/>
  <rect x="24" y="74" width="112" height="12" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1"/></g>
  <g font-size="9" fill="#475569" font-family="sans-serif"><text x="118" y="58">film</text><text x="118" y="64.5">lem panas</text><text x="118" y="72">cetak</text><text x="118" y="82">kertas</text></g>
</svg>
SVG,
        ],
        [
            'judul' => 'Laminasi Dingin', 'grup' => 'laminasi', 'tag' => 'Laminasi',
            'apa' => 'Film berperekat tekanan yang ditempel dengan roller tanpa pemanas. Tersedia permukaan glossy atau doff.',
            'cocok' => 'Stiker vinyl, photopaper, dan bahan sensitif panas; praktis untuk order satuan.',
            'catatan' => 'Tempel perlahan agar tidak ada gelembung udara; hasil tidak semenyatu laminasi panas pada kertas.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Laminasi dingin dengan roller tekan tanpa panas">
  <g transform="translate(-20 0)">
  <circle cx="80" cy="26" r="16" fill="#e0f2fe" stroke="#38bdf8" stroke-width="1.5"/>
  <circle cx="80" cy="26" r="4" fill="#38bdf8"/>
  <path d="M80 4v8M74 8l6 6 6-6" stroke="#0ea5e9" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M52 30l5 5M57 30l-5 5M104 30l5 5M109 30l-5 5" stroke="#38bdf8" stroke-width="1.3" stroke-linecap="round"/>
  <rect x="24" y="52" width="112" height="9" fill="#bae6fd" stroke="#38bdf8" stroke-width="1"/>
  <rect x="24" y="61" width="112" height="4" fill="#e2e8f0"/>
  <rect x="24" y="65" width="112" height="9" fill="#a5b4fc" stroke="#6366f1" stroke-width="1"/>
  <rect x="24" y="74" width="112" height="12" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1"/></g>
  <g font-size="9" fill="#475569" font-family="sans-serif"><text x="118" y="58">film</text><text x="118" y="64.5">lem tekan</text><text x="118" y="72">cetak</text><text x="118" y="82">bahan</text></g>
</svg>
SVG,
        ],
        [
            'judul' => 'Lipat Press Banner', 'grup' => 'banner', 'tag' => 'Banner',
            'apa' => 'Tepi banner dilipat ke belakang lalu ditekan (press) dengan lem/panas sehingga rapi dan kuat tanpa jahitan.',
            'cocok' => 'Banner yang butuh tepi bersih dan tahan sobek, sebelum dipasang mata ayam.',
            'catatan' => 'Butuh lebihan bahan di semua sisi yang dilipat; lipatan mengurangi ukuran tampak sedikit.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Tepi banner dilipat dan dipress">
  <rect x="12" y="18" width="136" height="50" rx="2" fill="#e0e7ff" stroke="#6366f1" stroke-width="1.5"/>
  <rect x="12" y="60" width="136" height="12" fill="#c7d2fe" stroke="#6366f1" stroke-width="1.5"/>
  <path d="M14 66h132" stroke="#6366f1" stroke-width="1" stroke-dasharray="3 3"/>
  <path d="M40 84l0-8M40 76l-4 4M40 76l4 4" stroke="#f59e0b" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  <rect x="26" y="84" width="28" height="8" rx="2" fill="#fde68a" stroke="#f59e0b" stroke-width="1.2"/>
  <text x="70" y="92" font-size="9" fill="#64748b" font-family="sans-serif">tekan / press</text>
  <text x="80" y="42" text-anchor="middle" font-size="10" font-weight="700" fill="#6366f1" font-family="sans-serif">BANNER</text>
  <text x="80" y="69" text-anchor="middle" font-size="9" fill="#4338ca" font-family="sans-serif">lipatan</text>
</svg>
SVG,
        ],
        [
            'judul' => 'Slobokan Banner', 'grup' => 'banner', 'tag' => 'Banner',
            'apa' => 'Tepi atas/bawah dilipat dan direkat membentuk kantong (selongsong) tempat memasukkan tiang, pipa, atau bambu.',
            'cocok' => 'Banner yang dipasang tegak/menggantung dengan tiang, umbul-umbul, backdrop.',
            'catatan' => 'Tentukan diameter tiang agar lebar kantong pas; lebihan bahan di sisi slobokan lebih besar dari lipat biasa.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Banner dengan slobokan berisi tiang">
  <rect x="12" y="34" width="136" height="52" rx="2" fill="#e0e7ff" stroke="#6366f1" stroke-width="1.5"/>
  <rect x="12" y="16" width="136" height="18" rx="9" fill="#c7d2fe" stroke="#6366f1" stroke-width="1.5"/>
  <rect x="6" y="20" width="148" height="10" rx="5" fill="#cbd5e1" stroke="#64748b" stroke-width="1.3"/>
  <path d="M18 34h124" stroke="#6366f1" stroke-width="1" stroke-dasharray="3 3"/>
  <text x="80" y="66" text-anchor="middle" font-size="10" font-weight="700" fill="#6366f1" font-family="sans-serif">BANNER</text>
  <text x="80" y="11" text-anchor="middle" font-size="9" fill="#64748b" font-family="sans-serif">kantong berisi tiang / pipa</text>
</svg>
SVG,
        ],
        [
            'judul' => 'Lebihan Banner', 'grup' => 'banner', 'tag' => 'Banner',
            'apa' => 'Bahan ekstra di sekeliling area desain, disiapkan untuk dilipat, dipasang mata ayam, atau slobokan agar desain tidak ikut terpotong.',
            'cocok' => 'Semua banner yang akan difinishing lipat, mata ayam, atau slobokan.',
            'catatan' => 'Cantumkan lebihan di ukuran/catatan produksi (contoh: desain 100x200 + lebihan tiap sisi) supaya hitungan bahan benar.',
            'svg' => <<<'SVG'
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Area desain dikelilingi lebihan bahan">
  <rect x="10" y="10" width="140" height="80" rx="2" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" stroke-dasharray="4 3"/>
  <rect x="28" y="26" width="104" height="48" rx="1" fill="#e0e7ff" stroke="#6366f1" stroke-width="1.5"/>
  <text x="80" y="54" text-anchor="middle" font-size="10" font-weight="700" fill="#6366f1" font-family="sans-serif">AREA DESAIN</text>
  <g stroke="#f59e0b" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <path d="M10 50h16M22 46l4 4-4 4"/><path d="M150 50h-16M138 46l-4 4 4 4"/><path d="M80 10v14M76 20l4 4 4-4"/><path d="M80 90V76M76 80l4-4 4 4"/>
  </g>
  <text x="80" y="8" text-anchor="middle" font-size="9" fill="#f59e0b" font-family="sans-serif" font-weight="700">lebihan</text>
</svg>
SVG,
        ],
    ];
@endphp

<section class="mt-10" id="panduan-finishing">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-5">
        <div>
            <p class="text-[10px] font-medium text-indigo-500 uppercase tracking-[0.2em]">Panduan Visual</p>
            <h2 class="text-xl font-medium text-slate-800">Perbandingan Finishing Item Cetak</h2>
            <p class="text-xs text-slate-400 mt-0.5">Ilustrasi singkat tiap finishing agar tidak salah pilih saat menerima atau mengerjakan order.</p>
        </div>
        <div class="flex flex-wrap gap-2" id="tab-finishing">
            @foreach(['semua' => 'Semua', 'banner' => 'Banner', 'laminasi' => 'Laminasi', 'stiker' => 'Stiker & Potong'] as $k => $lbl)
                <button type="button" data-grup="{{ $k }}" onclick="saringFinishing('{{ $k }}')" title="Tampilkan finishing kelompok {{ $lbl }}"
                        class="px-4 py-2 rounded-xl text-[11px] font-medium uppercase tracking-widest transition-all {{ $k === 'semua' ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600' }}">{{ $lbl }}</button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5" id="grid-finishing">
        @foreach($kartu as $k)
        <article data-grup="{{ $k['grup'] }}" class="kartu-finishing bg-white rounded-lg border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-all">
            @if(!empty($k['foto']))
            <div class="relative bg-slate-100 kartu-foto">
                <img src="{{ asset('img/finishing/' . $k['foto']['file']) }}" alt="{{ $k['judul'] }} - {{ $k['foto']['nama'] }}" loading="lazy" class="w-full aspect-[8/5] object-cover">
                <span class="absolute left-3 bottom-3 max-w-[85%] text-[10px] leading-tight px-2 py-1 rounded-md bg-slate-900/70 text-white">
                    Foto: <a href="{{ $k['foto']['url'] }}" target="_blank" rel="noopener" class="underline" title="Sumber foto di Wikimedia Commons">{{ $k['foto']['nama'] }}</a> &middot; {{ $k['foto']['oleh'] }} &middot; {{ $k['foto']['lisensi'] }}
                </span>
                <button type="button" onclick="gantiGambarFinishing(this)" title="Tampilkan ilustrasi cara kerja" class="absolute right-3 top-3 px-2.5 py-1.5 rounded-lg bg-white/90 text-slate-600 text-[10px] font-medium uppercase tracking-widest hover:bg-white shadow-sm">Ilustrasi</button>
            </div>
            <div class="panel-ilustrasi hidden bg-gradient-to-br from-slate-50 to-indigo-50/40 px-4 pt-4 pb-2 relative kartu-ilustrasi">
                <div class="w-full aspect-[8/5]">{!! $k['svg'] !!}</div>
                <button type="button" onclick="gantiGambarFinishing(this)" title="Kembali ke foto" class="absolute right-3 top-3 px-2.5 py-1.5 rounded-lg bg-white text-slate-600 text-[10px] font-medium uppercase tracking-widest shadow-sm">Foto</button>
            </div>
            @else
            <div class="panel-ilustrasi bg-gradient-to-br from-slate-50 to-indigo-50/40 px-4 pt-4 pb-2">
                <div class="w-full aspect-[8/5]">{!! $k['svg'] !!}</div>
            </div>
            @endif
            <div class="p-5">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <h3 class="text-sm font-medium text-slate-800">{{ $k['judul'] }}</h3>
                    <span class="text-[9px] font-medium uppercase tracking-widest text-indigo-500 bg-indigo-50 rounded px-2 py-1">{{ $k['tag'] }}</span>
                </div>
                <p class="text-[12px] text-slate-500 leading-relaxed">{{ $k['apa'] }}</p>
                <div class="mt-3 space-y-2 text-[11px]">
                    <p><span class="font-medium text-emerald-600 uppercase tracking-wider text-[10px]">Cocok untuk</span><br><span class="text-slate-500">{{ $k['cocok'] }}</span></p>
                    <p><span class="font-medium text-amber-600 uppercase tracking-wider text-[10px]">Perhatikan</span><br><span class="text-slate-500">{{ $k['catatan'] }}</span></p>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    {{-- Perbandingan langsung laminasi panas vs dingin --}}
    <div class="mt-6 bg-white rounded-lg border border-slate-100 shadow-sm overflow-hidden" data-grup-tabel="laminasi">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="text-sm font-medium text-slate-800">Laminasi Panas vs Laminasi Dingin</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[12px]">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-medium text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-3">Aspek</th><th class="px-6 py-3 text-rose-500">Laminasi Panas</th><th class="px-6 py-3 text-sky-600">Laminasi Dingin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-600">
                    <tr><td class="px-6 py-3 font-medium text-slate-700">Cara rekat</td><td class="px-6 py-3">Roller panas melelehkan lem film</td><td class="px-6 py-3">Tekanan roller, lem sensitif tekanan</td></tr>
                    <tr><td class="px-6 py-3 font-medium text-slate-700">Bahan yang cocok</td><td class="px-6 py-3">Kertas (poster, foto, kartu, brosur)</td><td class="px-6 py-3">Stiker vinyl, photopaper, bahan sensitif panas</td></tr>
                    <tr><td class="px-6 py-3 font-medium text-slate-700">Hasil</td><td class="px-6 py-3">Menyatu kuat, awet</td><td class="px-6 py-3">Rapi & praktis, kekuatan rekat sedang</td></tr>
                    <tr><td class="px-6 py-3 font-medium text-slate-700">Persiapan</td><td class="px-6 py-3">Mesin perlu dipanaskan dulu</td><td class="px-6 py-3">Langsung pakai</td></tr>
                    <tr><td class="px-6 py-3 font-medium text-slate-700">Pilihan permukaan</td><td class="px-6 py-3" colspan="2">Glossy (mengkilap) atau Doff (matte) - pilih sesuai kebutuhan konsumen</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-[10px] text-slate-400 mt-3">Kartu ber-tombol "Ilustrasi" memuat foto dari Wikimedia Commons (lisensi terbuka, sumber & pembuat tertera pada foto); finishing lain berupa ilustrasi buatan sendiri. Daftar & tarif finishing tetap diatur di menu Finishing.</p>
</section>

<script>
    // Ganti tampilan kartu antara foto dan ilustrasi
    function gantiGambarFinishing(btn) {
        const kartu = btn.closest('article');
        kartu.querySelector('.kartu-foto').classList.toggle('hidden');
        kartu.querySelector('.kartu-ilustrasi').classList.toggle('hidden');
    }
    function saringFinishing(grup) {
        document.querySelectorAll('#grid-finishing .kartu-finishing').forEach(el => el.classList.toggle('hidden', grup !== 'semua' && el.dataset.grup !== grup));
        const tabel = document.querySelector('[data-grup-tabel="laminasi"]');
        if (tabel) tabel.classList.toggle('hidden', grup !== 'semua' && grup !== 'laminasi');
        document.querySelectorAll('#tab-finishing button').forEach(b => {
            const aktif = b.dataset.grup === grup;
            b.classList.toggle('bg-indigo-600', aktif); b.classList.toggle('text-white', aktif);
            b.classList.toggle('bg-white', !aktif); b.classList.toggle('border', !aktif); b.classList.toggle('text-slate-500', !aktif);
        });
    }
</script>
