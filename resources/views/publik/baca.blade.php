@extends('publik.layout')

@section('judul', $post->judul . ' - ' . \App\Models\AppSetting::current()->nama_aplikasi)
@section('deskripsi', $post->ringkasan)

@push('font')
    {{-- Nunito untuk isi artikel (kelas prose-post), sudah ada di server. --}}
    <link rel="stylesheet" href="{{ asset('vendor/fonts/majalah.css') }}">
@endpush

@section('gaya')
        /* Garis kemajuan baca: digerakkan lewat transform (tanpa layout ulang). */
        .bl-maju { position: fixed; top: 0; left: 0; right: 0; height: 2px; background: var(--tinta); transform: scaleX(0); transform-origin: left; z-index: 30; }
        .bl-artikel { max-width: 720px; margin: 0 auto; padding-top: 3rem; }
        .bl-lain { margin-bottom: 4rem; }
        .bl-kembali { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.9375rem; color: var(--tinta-3); transition: color 150ms ease; }
        .bl-kembali i { font-size: 0.75rem; transition: transform 180ms var(--keluar); }
        .bl-meta { display: flex; flex-wrap: wrap; gap: 0.375rem 0.75rem; margin: 2rem 0 0; font-size: 0.875rem; color: var(--tinta-3); }
        .bl-meta b { color: var(--tinta); font-weight: 500; }
        .bl-artikel h1 { font-weight: 650; font-size: clamp(2rem, 4.4vw, 3rem); line-height: 1.1; letter-spacing: -0.03em; margin: 0.75rem 0 0; text-wrap: balance; }
        .bl-dek { margin: 1.25rem 0 0; font-size: 1.1875rem; line-height: 1.55; color: var(--tinta-2); }
        .bl-sampul { max-width: 1120px; margin: 2.5rem auto 0; border-radius: 6px; overflow: hidden; background: #eeeae4; }
        .bl-sampul img { width: 100%; max-height: 560px; object-fit: cover; }
        .bl-isi { max-width: 720px; margin: 2.5rem auto 0; }
        .bl-isi .prose-post { color: #2b2926; }
        .bl-isi .prose-post img { border-radius: 6px; height: auto; }
        .bl-ekor { max-width: 720px; margin: 2.5rem auto 0; padding-top: 1.5rem; border-top: 1px solid var(--garis); display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between; }
        .bl-tag { display: flex; flex-wrap: wrap; gap: 0.25rem 1rem; font-size: 0.875rem; color: var(--tinta-3); }
        .bl-bagi { display: flex; gap: 0.5rem; }
        .bl-lain { margin-top: 4.5rem; padding-top: 1.5rem; border-top: 1px solid var(--tinta); }
        .bl-lain > h2 { font-size: 1.5rem; font-weight: 650; letter-spacing: -0.02em; margin: 0 0 1.75rem; }
        .bl-daftar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.5rem 2rem; }
        .bl-kartu-taut { display: block; }
        .bl-gambar { overflow: hidden; border-radius: 6px; background: #eeeae4; aspect-ratio: 3 / 2; }
        .bl-gambar img { width: 100%; height: 100%; object-fit: cover; transition: transform 320ms var(--keluar); }
        .bl-kartu .bl-meta { margin-top: 1rem; font-size: 0.8125rem; }
        .bl-kartu .bl-meta span { color: var(--tinta); font-weight: 500; }
        .bl-kartu h3 { font-weight: 600; font-size: 1.1875rem; line-height: 1.3; letter-spacing: -0.015em; margin: 0.375rem 0 0; }
        .bl-ringkas { margin: 0.5rem 0 0; color: var(--tinta-2); display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; }
        @media (hover: hover) {
            .bl-kembali:hover { color: var(--tinta); }
            .bl-kembali:hover i { transform: translateX(-3px); }
            .bl-kartu-taut:hover .bl-gambar img { transform: scale(1.03); }
            .bl-kartu-taut:hover h3 { text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 4px; }
        }
        @media (max-width: 900px) { .bl-daftar { grid-template-columns: 1fr; } .bl-artikel { padding-top: 2rem; } }
@endsection

@section('isi')
<div class="bl-maju" id="bl-maju" aria-hidden="true"></div>
<div class="bl-lebar">
    <article>
        <header class="bl-artikel">
            <a class="bl-kembali" href="{{ url('/') }}#tulisan"><i class="fas fa-arrow-left" aria-hidden="true"></i> Semua tulisan</a>
            <p class="bl-meta">
                @if($post->kategori)<b>{{ $post->kategori->nama }}</b>@endif
                <time datetime="{{ optional($post->terbit_at)->toDateString() }}">{{ optional($post->terbit_at)->locale('id')->translatedFormat('d F Y') }}</time>
                <span>{{ $post->menit_baca }} menit baca</span>
            </p>
            <h1>{{ $post->judul }}</h1>
            @if($post->ringkasan)<p class="bl-dek">{{ $post->ringkasan }}</p>@endif
        </header>

        @if($post->url_sampul)<div class="bl-sampul"><img src="{{ $post->url_sampul }}" alt=""></div>@endif

        <div class="bl-isi" id="bl-isi"><div class="prose-post" lang="id">{!! $post->isi_html !!}</div></div>

        <div class="bl-ekor">
            <div class="bl-tag">
                @foreach($post->daftar_tag as $tg)<span>#{{ $tg }}</span>@endforeach
            </div>
            <div class="bl-bagi">
                <button type="button" class="bl-tombol bl-tombol-garis" id="bl-salin"><i class="far fa-copy" aria-hidden="true"></i> <span>Salin tautan</span></button>
                <a class="bl-tombol bl-tombol-garis" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($post->judul . ' ' . route('publik.baca', $post->slug)) }}"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
            </div>
        </div>
    </article>

    @if($lain->isNotEmpty())
    <section class="bl-lain">
        <h2>Tulisan lain</h2>
        <div class="bl-daftar">@include('publik._kartu', ['posts' => $lain])</div>
    </section>
    @endif
</div>
@endsection

@push('skrip')
<script>
(function () {
    var garis = document.getElementById('bl-maju'), isi = document.getElementById('bl-isi'), tunggu = false;
    function hitung() {
        var r = isi.getBoundingClientRect(), total = r.height - window.innerHeight * 0.6;
        var maju = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : (r.top < 0 ? 1 : 0);
        garis.style.transform = 'scaleX(' + maju + ')';
        tunggu = false;
    }
    window.addEventListener('scroll', function () { if (!tunggu) { tunggu = true; requestAnimationFrame(hitung); } }, { passive: true });
    hitung();

    var salin = document.getElementById('bl-salin'), teks = salin.querySelector('span');
    salin.addEventListener('click', function () {
        var selesai = function () { teks.textContent = 'Tautan tersalin'; setTimeout(function () { teks.textContent = 'Salin tautan'; }, 1800); };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(location.href).then(selesai); return; }
        var t = document.createElement('textarea'); t.value = location.href; document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); selesai(); } finally { t.remove(); }
    });
})();
</script>
@endpush
