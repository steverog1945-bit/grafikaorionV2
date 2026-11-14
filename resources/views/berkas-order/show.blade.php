@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1100px] mx-auto">

    <x-page-header title="Berkas Order {{ $nota->nomor_nota }}">
        <x-slot:subtitle>
            <i class="fas fa-folder-open text-amber-400"></i>
            {{ $nota->nama_konsumen ?? 'tanpa pelanggan' }} - kumpulan berkas per kategori (bukti, tidak bisa dihapus dari sini)
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button :href="route('penjualan.show', $nota->id_transaksi)" variant="outline"><i class="fas fa-arrow-left"></i> Nota</x-button>
        </x-slot:actions>
    </x-page-header>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-600 text-sm font-medium">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @if(!empty($temuanRekonsiliasi))
    {{-- Rekonsiliasi teks-vs-nota: sebutan finishing di nama file/catatan
         yang tidak tertagih. Advisory untuk dicek sebelum SPK/cetak - bisa
         berarti finishing lupa dimasukkan ke nota (bocor tagihan) atau
         catatan yang usang. Tidak otomatis mengubah nota. --}}
    <div class="mb-6 rounded-xl border-2 border-amber-300 bg-amber-50 px-5 py-4">
        <h3 class="text-sm font-medium text-amber-800 mb-1">
            <i class="fas fa-magnifying-glass-exclamation text-amber-500 mr-1"></i>
            Rekonsiliasi berkas vs nota - {{ count($temuanRekonsiliasi) }} temuan
        </h3>
        <p class="text-xs text-amber-700/80 mb-3">Nama berkas/catatan menyebut pekerjaan yang tidak ada di nota. Periksa sebelum SPK: bisa jadi finishing lupa ditagih, atau catatan yang sudah usang.</p>
        @foreach($temuanRekonsiliasi as $t)
        <div class="text-sm text-slate-700 py-1.5 {{ !$loop->last ? 'border-b border-amber-100' : '' }}">
            <b>{{ $t['nama_finishing'] }}</b> disebut di {{ $t['sumber'] }}
            <span class="text-[11px] text-slate-400">&ldquo;{{ $t['kutipan'] }}&rdquo;</span>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($temuanAi))
    {{-- Hasil audit AI (command `nota:audit-ai`), tersimpan di nota_audit_ai.
         Advisory juga - keyakinian rendah (mis. < 0.6) tampil redup supaya
         operator tahu mana yang perlu diperiksa lebih dulu. --}}
    <div class="mb-6 rounded-xl border-2 border-indigo-200 bg-indigo-50 px-5 py-4">
        <h3 class="text-sm font-medium text-indigo-800 mb-1">
            <i class="fas fa-robot text-indigo-400 mr-1"></i>
            Audit AI - {{ count($temuanAi) }} temuan
            <span class="text-[11px] text-indigo-400 font-normal">({{ $temuanAi[0]['model'] }})</span>
        </h3>
        <p class="text-xs text-indigo-700/80 mb-3">Mesin membaca kalimat bebas berkas/catatan dan membandingkannya dengan fakta nota. Tandanya advisory - konfirmasi manual sebelum bertindak.</p>
        @foreach($temuanAi as $t)
        @php $redup = (float) $t['keyakinian'] < 0.6; @endphp
        <div class="text-sm py-1.5 {{ !$loop->last ? 'border-b border-indigo-100' : '' }} {{ $redup ? 'text-slate-500' : 'text-slate-700' }}">
            <b>{{ $t['temuan'] }}</b>
            @if($t['kutipan'])<span class="text-[11px] text-slate-400">&ldquo;{{ $t['kutipan'] }}&rdquo;</span>@endif
            <span class="text-[11px] text-slate-400"> - {{ $t['sumber' ] }}{{ $redup ? ' (keyakinian rendah ' . number_format((float) $t['keyakinian'] * 100, 0) . '%)' : '' }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <x-card class="mb-8">
        <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest mb-4">Unggah Berkas</h3>
        <form method="POST" action="{{ route('berkas-order.unggah', $nota->id_transaksi) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div><label class="block text-[10px] font-medium text-slate-400 uppercase mb-1">Kategori</label>
                <select name="kategori" class="w-full rounded-xl border-slate-200 text-sm p-2">@foreach($kategoriUnggah as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach</select></div>
            <div><label class="block text-[10px] font-medium text-slate-400 uppercase mb-1">Item (opsional)</label>
                <select name="id_detail" class="w-full rounded-xl border-slate-200 text-sm p-2"><option value="">Seluruh nota</option>@foreach($items as $it)<option value="{{ $it->id_detail }}">{{ $it->nama_file }}</option>@endforeach</select></div>
            <div><label class="block text-[10px] font-medium text-slate-400 uppercase mb-1">File (maks 50 MB)</label>
                <input type="file" name="file" required class="w-full text-sm"></div>
            <div><label class="block text-[10px] font-medium text-slate-400 uppercase mb-1">Catatan</label>
                <input type="text" name="catatan" maxlength="255" class="w-full rounded-xl border-slate-200 text-sm p-2"></div>
            <div class="md:col-span-4 text-right"><x-button type="submit" variant="primary"><i class="fas fa-upload"></i> Unggah</x-button></div>
        </form>
        <x-hint tip="Format: JPG, PNG, WEBP, PDF, ZIP, AI, PSD, CDR, EPS. Foto bukti serah terima lewat tombol serah terima di Collecting." icon="file-circle-check" />
    </x-card>

    @if(session('peringatanRekonsiliasi'))
    <div class="mb-6 rounded-xl border-2 border-amber-300 bg-amber-50 px-5 py-4">
        <h3 class="text-sm font-medium text-amber-800 mb-1"><i class="fas fa-triangle-exclamation text-amber-500 mr-1"></i> Berkas tersimpan, tapi ada yang janggal</h3>
        @foreach(session('peringatanRekonsiliasi') as $t)
        <div class="text-sm text-slate-700 py-1 {{ !$loop->last ? 'border-b border-amber-100' : '' }}">
            {{ $t['sumber'] }} menyebut <b>{{ $t['nama_finishing'] }}</b> yang tidak tertagih di nota{{ $t['kutipan'] ? ' - teks: "' . $t['kutipan'] . '"' : '' }}.
        </div>
        @endforeach
        <p class="text-xs text-amber-700/80 mt-2">Kalau pekerjaannya nyata, tambahkan finishing di nota dulu. Kalau catatannya usang, abaikan.</p>
    </div>
    @endif

    @if($pengambilan)
    <x-card class="mb-8">
        <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest mb-2">Serah Terima</h3>
        <div class="text-sm text-slate-600">{{ $pengambilan->jenis }}{{ $pengambilan->nama_penerima ? ' - ' . $pengambilan->nama_penerima : '' }} - {{ \Carbon\Carbon::parse($pengambilan->created_at)->translatedFormat('d M Y H:i') }}{{ $pengambilan->catatan ? ' - ' . $pengambilan->catatan : '' }}</div>
    </x-card>
    @endif

    @forelse($grup as $kategori => $daftar)
    <h3 class="text-xs font-medium text-slate-500 uppercase tracking-widest mb-3 mt-8">{{ $kategori }}</h3>
    <x-card>
        @foreach($daftar as $b)
        <div class="flex flex-wrap items-center justify-between gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
            <div class="min-w-0">
                <a href="{{ $b['url'] }}" target="_blank" class="text-sm font-medium text-slate-700 hover:text-indigo-600 break-all">{{ $b['nama'] }}</a>
                <div class="text-[11px] text-slate-400">{{ $b['item'] }}{{ $b['info'] ? ' - ' . $b['info'] : '' }}{{ $b['waktu'] ? ' - ' . \Carbon\Carbon::parse($b['waktu'])->translatedFormat('d M Y H:i') : '' }}</div>
            </div>
            <a href="{{ $b['url'] }}" target="_blank" class="text-xs font-medium text-indigo-600 uppercase">Buka</a>
        </div>
        @endforeach
    </x-card>
    @empty
    <x-card><x-empty-state title="Belum ada berkas untuk nota ini" /></x-card>
    @endforelse
</div>
@endsection
