@extends('layouts.app')

@section('content')
@php
    $edit = $post->exists;
    $terbitAtInput = old('terbit_at', optional($post->terbit_at)->format('Y-m-d\TH:i'));
@endphp
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1300px] mx-auto">

    <x-page-header :title="$edit ? 'Edit Postingan' : 'Tulis Postingan'">
        <x-slot:subtitle>Info &amp; Panduan <span class="mx-1 text-slate-300">/</span> {{ $edit ? 'Ubah isi, gambar, dan pengaturan terbit' : 'Buat pengumuman, SOP, tips, atau hasil karya' }}</x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ $edit ? route('postingan.show', $post->slug) : route('postingan.index') }}" variant="secondary"><i class="fas fa-arrow-left"></i> Kembali</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 font-semibold">
            <ul class="list-disc list-inside space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" id="form-postingan" enctype="multipart/form-data" action="{{ $edit ? route('postingan.update', $post->id) : route('postingan.store') }}">
        @csrf
        @if($edit) @method('PUT') @endif
        <input type="hidden" name="isi" id="isi-input">

        <div class="flex flex-col lg:flex-row gap-6 items-start">
            {{-- ============ KIRI: JUDUL & EDITOR ============ --}}
            <div class="w-full lg:w-[68%] space-y-6">
                <x-card>
                    <x-form.group label="Judul" required>
                        <x-form.input type="text" name="judul" value="{{ old('judul', $post->judul) }}" required maxlength="200" placeholder="Judul yang jelas dan singkat" class="!text-lg !font-bold" />
                    </x-form.group>
                    <x-form.group label="Ringkasan (tampil di daftar; kosongkan untuk otomatis)" class="mt-4">
                        <textarea name="ringkasan" rows="2" maxlength="300" placeholder="Satu-dua kalimat pembuka..." class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ old('ringkasan', $post->ringkasan) }}</textarea>
                    </x-form.group>
                </x-card>

                <x-card :padding="false" class="overflow-hidden">
                    <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Isi Postingan <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-slate-400" id="hitung-kata">0 kata</span>
                    </div>
                    <div id="editor-postingan">{!! \App\Support\HtmlBersih::bersihkan(old('isi', $post->isi_html)) !!}</div>
                </x-card>
            </div>

            {{-- ============ KANAN: PENGATURAN ============ --}}
            <div class="w-full lg:w-[32%] space-y-6 lg:sticky lg:top-24">
                <x-card>
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Penerbitan</h3>
                    <x-form.group label="Status" required>
                        <x-form.select name="status" id="status-post">
                            <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft (belum terlihat orang lain)</option>
                            <option value="terbit" {{ old('status', $post->status) === 'terbit' ? 'selected' : '' }}>Terbit</option>
                        </x-form.select>
                    </x-form.group>
                    <x-form.group label="Jadwal terbit (opsional)" class="mt-4" help="Isi waktu di masa depan agar terbit otomatis pada saat itu (status harus Terbit).">
                        <x-form.input type="datetime-local" name="terbit_at" value="{{ $terbitAtInput }}" />
                    </x-form.group>
                    <label class="mt-4 flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 cursor-pointer" title="Postingan disematkan selalu tampil paling atas dan di dashboard">
                        <input type="checkbox" name="disematkan" value="1" class="accent-indigo-600" {{ old('disematkan', $post->disematkan) ? 'checked' : '' }}>
                        <span><span class="block text-xs font-bold text-slate-600 uppercase tracking-wide"><i class="fas fa-thumbtack text-amber-500"></i> Sematkan di atas</span><span class="block text-[11px] text-slate-400">Selalu tampil paling atas &amp; di dashboard</span></span>
                    </label>
                </x-card>

                <x-card>
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Kategori &amp; Tag</h3>
                    <x-form.group label="Kategori">
                        <x-form.select name="id_kategori">
                            <option value="">-- Tanpa kategori --</option>
                            @foreach($kategoriList as $k)<option value="{{ $k->id }}" {{ (string) old('id_kategori', $post->id_kategori) === (string) $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>@endforeach
                        </x-form.select>
                    </x-form.group>
                    <x-form.group label="atau buat kategori baru" class="mt-4">
                        <x-form.input type="text" name="kategori_baru" value="{{ old('kategori_baru') }}" maxlength="60" placeholder="mis. Info Supplier" />
                    </x-form.group>
                    <x-form.group label="Tag (pisahkan koma)" class="mt-4">
                        <x-form.input type="text" name="tags" value="{{ old('tags', $post->tags) }}" maxlength="255" placeholder="banner, laminasi, sop" />
                    </x-form.group>
                </x-card>

                <x-card>
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Gambar Sampul</h3>
                    <div id="pratinjau-sampul" class="rounded-2xl overflow-hidden bg-slate-100 aspect-[16/9] mb-3 {{ $post->url_sampul ? '' : 'hidden' }}">
                        <img id="img-sampul" src="{{ $post->url_sampul }}" alt="Sampul" class="w-full h-full object-cover">
                    </div>
                    <input type="file" name="sampul" id="input-sampul" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-indigo-50 file:text-indigo-600">
                    <p class="text-[11px] text-slate-400 mt-2">JPG/PNG/WebP, maks. 5 MB. Rasio 16:9 paling pas.</p>
                    @if($post->gambar_sampul)
                    <label class="mt-3 flex items-center gap-2 text-xs font-semibold text-slate-500"><input type="checkbox" name="hapus_sampul" value="1" class="accent-rose-500"> Hapus sampul saat ini</label>
                    @endif
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" id="btn-simpan-post" class="flex-1" title="Simpan postingan"><i class="fas fa-check"></i> {{ $edit ? 'Simpan Perubahan' : 'Simpan' }}</x-button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    const URL_UNGGAH = @json(route('postingan.unggah'));
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const quill = new Quill('#editor-postingan', {
        theme: 'snow',
        placeholder: 'Tulis isi postingan di sini... (bisa tempel atau seret gambar)',
        modules: {
            toolbar: {
                container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline', 'strike'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', 'image'], ['clean']],
                handlers: { image: pilihGambar },
            },
        },
    });

    // Paragraf kosong hasil tempel (Enter ganda dari Word/web) tidak ikut masuk
    quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
        const Delta = Quill.import('delta');
        return (node.tagName === 'P' && !node.textContent.replace(/\s/g, '') && !node.querySelector('img')) ? new Delta() : delta;
    });

    function hitungKata() {
        const t = quill.getText().trim();
        document.getElementById('hitung-kata').textContent = (t ? t.split(/\s+/).length : 0) + ' kata';
    }
    quill.on('text-change', hitungKata); hitungKata();

    // ---- Unggah gambar ke server, lalu sisipkan ke editor
    async function unggah(file) {
        const fd = new FormData();
        fd.append('gambar', file);
        const r = await fetch(URL_UNGGAH, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, credentials: 'same-origin', body: fd });
        if (!r.ok) throw new Error('Unggah gagal (' + r.status + ')');

        return (await r.json()).url;
    }
    function pilihGambar() {
        const input = document.createElement('input');
        input.type = 'file'; input.accept = 'image/png,image/jpeg,image/webp,image/gif'; input.multiple = true;
        input.onchange = async () => {
            for (const f of input.files) {
                try { const url = await unggah(f); const pos = (quill.getSelection(true) || { index: quill.getLength() }).index; quill.insertEmbed(pos, 'image', url, 'user'); quill.setSelection(pos + 1); }
                catch (e) { Swal.fire('Gagal', 'Gambar ' + f.name + ' tidak dapat diunggah (maks. 5 MB).', 'error'); }
            }
        };
        input.click();
    }

    // ---- Pratinjau sampul
    document.getElementById('input-sampul').addEventListener('change', function () {
        const f = this.files[0]; if (!f) return;
        document.getElementById('img-sampul').src = URL.createObjectURL(f);
        document.getElementById('pratinjau-sampul').classList.remove('hidden');
    });

    // ---- Simpan: gambar tempel (data:) diunggah dulu agar tidak hilang, lalu isi dikirim
    document.getElementById('form-postingan').addEventListener('submit', async function (e) {
        if (this.__siap) return;
        e.preventDefault();
        const btn = document.getElementById('btn-simpan-post'); btn.disabled = true;
        try {
            for (const img of quill.root.querySelectorAll('img[src^="data:"]')) {
                const blob = await (await fetch(img.src)).blob();
                img.src = await unggah(new File([blob], 'tempel.' + (blob.type.split('/')[1] || 'png'), { type: blob.type }));
            }
        } catch (err) { btn.disabled = false; Swal.fire('Gagal', 'Ada gambar tempel yang tidak dapat diunggah. Coba sisipkan lewat tombol gambar.', 'error'); return; }
        document.getElementById('isi-input').value = quill.root.innerHTML;
        this.__siap = true; this.submit();
    });
</script>
@endpush
@endsection
