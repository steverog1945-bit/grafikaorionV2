@extends('layouts.app')

@section('content')
@php
    $variantMap = ['Menunggu Review' => 'warning', 'Revisi Diminta' => 'danger', 'Disetujui' => 'success', 'Digantikan' => 'info', 'Dibuka Kembali' => 'warning'];
    $bisaUnggah = !$terkunci;
@endphp
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1100px] mx-auto">

    <x-page-header title="Design Proof - {{ $detail->nomor_nota }}">
        <x-slot:subtitle>
            <i class="fas fa-pencil-ruler text-amber-400"></i>
            {{ $detail->nama_file }} - {{ $detail->qty }} pcs - {{ $detail->nama_konsumen ?? 'tanpa pelanggan' }}
        </x-slot:subtitle>
        <x-slot:actions>
            @if($terkunci)
                <span class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-wider"><i class="fas fa-lock"></i> Desain terkunci (rev #{{ $terakhir->revisi_no }})</span>
            @endif
            <x-button :href="route('design-proof.index')" variant="outline"><i class="fas fa-arrow-left"></i> Daftar</x-button>
        </x-slot:actions>
    </x-page-header>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-600 text-sm font-semibold">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @if($bisaUnggah)
    <x-card class="mb-8">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-4">Unggah Proof {{ $terakhir ? 'Revisi #' . ($terakhir->revisi_no + 1) : 'Pertama' }}</h3>
        <form method="POST" action="{{ route('design-proof.unggah', $detail->id_detail) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            @csrf
            <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">File (JPG/PNG/WEBP/PDF, maks 20 MB)</label>
                <input type="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full text-sm"></div>
            <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan desainer (opsional)</label>
                <input type="text" name="catatan" maxlength="1000" class="w-full rounded-xl border-slate-200 text-sm p-2"></div>
            <x-button type="submit" variant="primary"><i class="fas fa-upload"></i> Unggah</x-button>
        </form>
        @if($terakhir && $terakhir->status === 'Menunggu Review')
            <p class="text-[11px] text-amber-600 mt-3">Mengunggah revisi baru menggantikan proof #{{ $terakhir->revisi_no }} yang belum diputuskan pelanggan.</p>
        @endif
    </x-card>
    @endif

    @if($terkunci)
    <x-card class="mb-8">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-2">Perlu perubahan setelah disetujui?</h3>
        <p class="text-xs text-slate-500 mb-4">Desain yang sudah disetujui tidak bisa diganti langsung. Ajukan pembukaan revisi beserta alasannya; Administrator memutuskan di Approval Workflow.</p>
        @if($bukaMenunggu)
            <p class="text-xs font-bold text-amber-600">Pengajuan pembukaan revisi sedang menunggu persetujuan Administrator.</p>
        @else
        <form method="POST" action="{{ route('design-proof.ajukan-revisi', $detail->id_detail) }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[240px]"><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alasan</label>
                <input type="text" name="alasan" required maxlength="500" class="w-full rounded-xl border-slate-200 text-sm p-2" placeholder="Mis. pelanggan mengganti warna logo"></div>
            <x-button type="submit" variant="warning"><i class="fas fa-lock-open"></i> Ajukan Buka Revisi</x-button>
        </form>
        @endif
    </x-card>
    @endif

    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Riwayat Revisi</h3>
    <div class="space-y-4">
        @forelse($proofs as $p)
        <x-card>
            <div class="flex flex-col md:flex-row gap-5">
                <div class="md:w-48 shrink-0">
                    @if($p->gambar)
                        <a href="{{ $p->url }}" target="_blank"><img src="{{ $p->url }}" alt="Proof rev {{ $p->revisi_no }}" class="w-full rounded-xl border border-slate-100 object-cover max-h-48"></a>
                    @else
                        <a href="{{ $p->url }}" target="_blank" class="flex items-center justify-center h-32 rounded-xl bg-slate-50 text-slate-400 text-sm font-bold"><i class="fas fa-file-pdf text-3xl mr-2"></i> Buka PDF</a>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-3 mb-1">
                        <span class="text-base font-extrabold text-slate-700">Revisi #{{ $p->revisi_no }}</span>
                        <x-badge :status="$p->status" :variant="$variantMap[$p->status] ?? 'info'" />
                    </div>
                    <div class="text-xs text-slate-400">{{ $p->nama_file_asli }} - {{ number_format($p->ukuran_bytes / 1024, 0) }} KB - diunggah {{ \Carbon\Carbon::parse($p->created_at)->format('d M Y H:i') }} oleh {{ $p->pengunggah ?? '-' }}</div>
                    @if($p->catatan)<div class="text-sm text-slate-600 mt-2">Catatan desainer: {{ $p->catatan }}</div>@endif

                    @if($p->keputusan_at)
                        <div class="text-xs mt-2 {{ $p->status === 'Disetujui' ? 'text-emerald-600' : 'text-rose-500' }} font-semibold">
                            {{ $p->status === 'Disetujui' ? 'Disetujui pelanggan' : 'Revisi diminta pelanggan' }} lewat {{ $p->keputusan_kanal }},
                            {{ \Carbon\Carbon::parse($p->keputusan_at)->format('d M Y H:i') }} (dicatat {{ $p->pencatat ?? '-' }})
                            @if($p->keputusan_catatan)<div class="font-normal text-slate-600">"{{ $p->keputusan_catatan }}"</div>@endif
                        </div>
                    @endif

                    @if($p->status === 'Menunggu Review' && $loop->first)
                    <form method="POST" action="{{ route('design-proof.keputusan', $p->id) }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        @csrf @method('PATCH')
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Keputusan pelanggan</label>
                            <select name="keputusan" class="w-full rounded-xl border-slate-200 text-sm p-2"><option value="Disetujui">Setuju</option><option value="Revisi Diminta">Minta revisi</option></select></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Disampaikan lewat</label>
                            <select name="kanal" class="w-full rounded-xl border-slate-200 text-sm p-2">@foreach($kanal as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach</select></div>
                        <div class="md:col-span-2"><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan (wajib bila minta revisi)</label>
                            <input type="text" name="catatan" maxlength="1000" class="w-full rounded-xl border-slate-200 text-sm p-2"></div>
                        <div class="md:col-span-4 text-right"><x-button type="submit" variant="success"><i class="fas fa-check"></i> Catat Keputusan Pelanggan</x-button></div>
                    </form>
                    @endif
                </div>
            </div>
        </x-card>
        @empty
        <x-card><x-empty-state title="Belum ada proof untuk item ini" /></x-card>
        @endforelse
    </div>
</div>
@endsection
