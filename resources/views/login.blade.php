@extends('publik.layout')

@section('judul', $appSetting->nama_aplikasi ?? \App\Models\AppSetting::current()->nama_aplikasi)
@section('deskripsi', 'Kabar, panduan, dan hasil kerja dari ' . \App\Models\AppSetting::current()->nama_aplikasi . '.')

@section('gaya')
        .bl-pembuka { padding: 4rem 0 2.5rem; }
        .bl-pembuka h1 { font-family: var(--serif); font-weight: 600; font-size: clamp(2.25rem, 5vw, 3.75rem); line-height: 1.08; letter-spacing: -0.02em; margin: 0; max-width: 16ch; }
        .bl-pembuka p { margin: 1.25rem 0 0; font-size: 1.125rem; color: var(--tinta-2); max-width: 46ch; }

        /* Tulisan utama: gambar besar di kiri, teks di kanan. */
        .bl-utama { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); gap: 2.5rem; align-items: center; padding: 2rem 0 3.5rem; border-top: 1px solid var(--garis); }
        .bl-utama h2 { font-family: var(--serif); font-weight: 600; font-size: clamp(1.625rem, 2.6vw, 2.25rem); line-height: 1.18; letter-spacing: -0.015em; margin: 0.625rem 0 0; }
        .bl-utama .bl-ringkas { font-size: 1.0625rem; -webkit-line-clamp: 4; }
        .bl-baca { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1.25rem; font-weight: 500; border-bottom: 1px solid var(--tinta); padding-bottom: 2px; }
        .bl-baca i { font-size: 0.75rem; transition: transform 180ms var(--keluar); }

        .bl-gambar { overflow: hidden; border-radius: 6px; background: #eeeae4; aspect-ratio: 3 / 2; }
        .bl-gambar img { width: 100%; height: 100%; object-fit: cover; transition: transform 320ms var(--keluar); }
        .bl-meta { display: flex; flex-wrap: wrap; gap: 0.375rem 0.75rem; margin: 0; font-size: 0.8125rem; color: var(--tinta-3); }
        .bl-meta span { color: var(--tinta); font-weight: 500; }
        .bl-ringkas { margin: 0.625rem 0 0; color: var(--tinta-2); display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden; }

        /* Bilah saring */
        .bl-bilah { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--tinta); border-bottom: 1px solid var(--garis); scroll-margin-top: 64px; }
        .bl-kategori { display: flex; flex-wrap: wrap; gap: 0.25rem 1.5rem; }
        .bl-kategori button { background: none; border: 0; padding: 0.25rem 0; font: inherit; font-size: 0.9375rem; color: var(--tinta-3); cursor: pointer; position: relative; transition: color 150ms ease; }
        .bl-kategori button::after { content: ''; position: absolute; left: 0; right: 0; bottom: -2px; height: 2px; background: var(--tinta); transform: scaleX(0); transform-origin: left; transition: transform 200ms var(--keluar); }
        .bl-kategori button[aria-pressed="true"] { color: var(--tinta); font-weight: 500; }
        .bl-kategori button[aria-pressed="true"]::after { transform: scaleX(1); }
        .bl-cari { position: relative; flex: 0 1 260px; }
        .bl-cari i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--tinta-3); font-size: 0.8125rem; pointer-events: none; }
        .bl-cari input { width: 100%; padding: 0 0.75rem 0 2.25rem; background: var(--putih); border: 1px solid var(--garis-kuat); font: inherit; font-size: 0.9375rem; color: var(--tinta); outline: none; }
        .bl-cari input:focus { border-color: var(--aksen); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important; }

        /* Daftar */
        .bl-daftar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3rem 2rem; padding-top: 2.5rem; transition: opacity 150ms ease; }
        .bl-daftar[aria-busy="true"] { opacity: 0.5; }
        .bl-kartu-taut { display: block; }
        .bl-kartu .bl-meta { margin-top: 1rem; }
        .bl-kartu h3 { font-family: var(--serif); font-weight: 600; font-size: 1.3125rem; line-height: 1.25; letter-spacing: -0.01em; margin: 0.375rem 0 0; text-wrap: balance; }
        /* Kartu yang baru masuk naik sedikit sambil muncul; berurutan 40ms supaya terasa satu gerakan. */
        .bl-kartu.baru { animation: bl-naik 280ms var(--keluar) both; animation-delay: calc(var(--i, 0) * 40ms); }
        @keyframes bl-naik { from { opacity: 0; transform: translateY(10px); } }
        @media (hover: hover) {
            .bl-kartu-taut:hover .bl-gambar img, .bl-utama a:hover .bl-gambar img { transform: scale(1.03); }
            .bl-kartu-taut:hover h3 { text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 4px; }
            .bl-baca:hover i { transform: translateX(3px); }
            .bl-kategori button:hover { color: var(--tinta); }
        }
        .bl-kosong { padding: 4rem 0; text-align: center; color: var(--tinta-3); }
        .bl-lagi { display: flex; justify-content: center; padding-top: 3rem; }

        @media (max-width: 900px) {
            .bl-pembuka { padding: 2.5rem 0 1.5rem; }
            .bl-utama { grid-template-columns: 1fr; gap: 1.25rem; padding-bottom: 2.5rem; }
            .bl-daftar { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2.5rem 1.25rem; }
            .bl-cari { flex-basis: 100%; }
        }
        @media (max-width: 560px) { .bl-daftar { grid-template-columns: 1fr; } }
@endsection

@section('isi')
@php $nama = \App\Models\AppSetting::current()->nama_aplikasi; @endphp
<div class="bl-lebar">
    <section class="bl-pembuka">
        <h1>Kabar dan catatan kerja {{ $nama }}</h1>
        <p>Pengumuman, panduan, dan hasil cetak terbaru kami.</p>
    </section>

    @if($unggulan)
    <section class="bl-utama" id="unggulan" aria-label="Tulisan utama">
        <a href="{{ route('publik.baca', $unggulan->slug) }}" tabindex="-1" aria-hidden="true">
            @if($unggulan->url_sampul)<div class="bl-gambar"><img src="{{ $unggulan->url_sampul }}" alt=""></div>@endif
        </a>
        <div>
            <p class="bl-meta">
                @if($unggulan->kategori)<span>{{ $unggulan->kategori->nama }}</span>@endif
                <time>{{ optional($unggulan->terbit_at)->locale('id')->translatedFormat('d F Y') }}</time>
                <span style="font-weight:400;color:inherit">{{ $unggulan->menit_baca }} menit baca</span>
            </p>
            <h2><a href="{{ route('publik.baca', $unggulan->slug) }}">{{ $unggulan->judul }}</a></h2>
            @if($unggulan->ringkasan)<p class="bl-ringkas">{{ $unggulan->ringkasan }}</p>@endif
            <a class="bl-baca" href="{{ route('publik.baca', $unggulan->slug) }}">Baca tulisan <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </section>
    @endif

    <div class="bl-bilah" id="tulisan">
        <div class="bl-kategori" role="group" aria-label="Saring menurut kategori">
            <button type="button" data-kategori="" aria-pressed="{{ $kategoriSlug === '' ? 'true' : 'false' }}">Semua</button>
            @foreach($kategoriList as $k)
                <button type="button" data-kategori="{{ $k->slug }}" aria-pressed="{{ $kategoriSlug === $k->slug ? 'true' : 'false' }}">{{ $k->nama }}</button>
            @endforeach
        </div>
        <label class="bl-cari">
            <span class="sr-only">Cari tulisan</span>
            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" id="bl-cari" value="{{ $cari }}" placeholder="Cari tulisan" autocomplete="off">
        </label>
    </div>

    <div class="bl-daftar" id="bl-daftar" aria-live="polite">
        @include('publik._kartu', ['posts' => $posts])
    </div>

    <p class="bl-kosong" id="bl-kosong" @if($posts->total() > 0 || $unggulan) hidden @endif>
        <span data-kosong-cari hidden>Tidak ada tulisan yang cocok. Coba kata lain atau pilih kategori Semua.</span>
        <span data-kosong-awal>Belum ada tulisan yang diterbitkan.</span>
    </p>

    <div class="bl-lagi" id="bl-lagi" @if(!$posts->hasMorePages()) hidden @endif>
        <button type="button" class="bl-tombol bl-tombol-garis" data-halaman="{{ $posts->currentPage() + 1 }}">Muat tulisan lain</button>
    </div>
</div>
@endsection

@push('skrip')
<script>
// Saring, cari, dan muat-lagi tanpa memuat ulang halaman. Tanpa JS, daftar awal tetap tampil lengkap.
(function () {
    var daftar = document.getElementById('bl-daftar'), lagi = document.getElementById('bl-lagi'), kosong = document.getElementById('bl-kosong');
    var unggulan = document.getElementById('unggulan'), cari = document.getElementById('bl-cari');
    var keadaan = { kategori: @json($kategoriSlug), cari: @json($cari) }, urutan = 0, tunda;

    function alamat(halaman) {
        var q = new URLSearchParams();
        if (keadaan.kategori) q.set('kategori', keadaan.kategori);
        if (keadaan.cari) q.set('cari', keadaan.cari);
        if (halaman > 1) q.set('page', halaman);
        var s = q.toString();
        return @json(url('/')) + (s ? '?' + s : '');
    }

    function muat(halaman, tambah) {
        var nomor = ++urutan;
        daftar.setAttribute('aria-busy', 'true');
        fetch(alamat(halaman), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) {
                if (nomor !== urutan) return; // jawaban lama kalah oleh ketikan yang lebih baru
                var wadah = document.createElement('div');
                wadah.innerHTML = d.html;
                var baru = Array.prototype.slice.call(wadah.children);
                baru.forEach(function (k, i) { k.classList.add('baru'); k.style.setProperty('--i', Math.min(i, 8)); });
                if (!tambah) daftar.innerHTML = '';
                baru.forEach(function (k) { daftar.appendChild(k); });

                var berfilter = !!(keadaan.kategori || keadaan.cari);
                if (unggulan) unggulan.hidden = berfilter;
                kosong.hidden = daftar.children.length > 0 || (!berfilter && !!unggulan);
                kosong.querySelector('[data-kosong-cari]').hidden = !berfilter;
                kosong.querySelector('[data-kosong-awal]').hidden = berfilter;
                lagi.hidden = !d.berikut;
                if (d.berikut) lagi.querySelector('button').dataset.halaman = d.berikut;
                if (!tambah) history.replaceState(null, '', alamat(1) + '#tulisan');
            })
            .catch(function () { if (!tambah) location.href = alamat(1); })
            .finally(function () { if (nomor === urutan) daftar.removeAttribute('aria-busy'); });
    }

    document.querySelectorAll('.bl-kategori button').forEach(function (t) {
        t.addEventListener('click', function () {
            document.querySelectorAll('.bl-kategori button').forEach(function (x) { x.setAttribute('aria-pressed', String(x === t)); });
            keadaan.kategori = t.dataset.kategori;
            muat(1, false);
        });
    });
    cari.addEventListener('input', function () {
        clearTimeout(tunda);
        tunda = setTimeout(function () { keadaan.cari = cari.value.trim(); muat(1, false); }, 250);
    });
    lagi.querySelector('button').addEventListener('click', function () { muat(parseInt(this.dataset.halaman, 10), true); });
})();
</script>
@endpush
