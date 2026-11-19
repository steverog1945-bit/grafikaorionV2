@extends('layouts.app')

@section('content')
<div class="w-full max-w-xl mx-auto px-4 sm:px-6 py-10">

    {{-- Halaman gerbang: sengaja TANPA data laporan apa pun. Yang tampil
         hanya penjelasan dan form password, supaya isi laci/selisih tidak
         bocor sebelum password Administrator dimasukkan. --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

        <div class="px-5 py-4 border-b border-slate-200 flex items-start gap-3">
            <span class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <i class="fas fa-lock text-sm"></i>
            </span>
            <div class="min-w-0">
                <h1 class="text-sm font-semibold text-slate-900">Laporan Closing Terkunci</h1>
                <p class="mt-1 text-[13px] text-slate-500">
                    Riwayat closing memuat isi laci dan selisih uang, jadi hanya bisa dibuka
                    dengan password akun Administrator.
                </p>
            </div>
        </div>

        <div class="px-5 py-5">
            {{-- Tujuan disebut supaya jelas APA yang sedang dibuka; tidak ada
                 angka laporan di sini. --}}
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5 text-[12px] text-slate-600 leading-relaxed">
                @if($cetak)
                    <p>
                        <span class="font-medium text-slate-800">Mencetak rekap closing sesi #{{ $cetak }}</span>
                        &middot; setelah dibuka, halaman cetak terbuka di tab ini.
                    </p>
                @else
                    <p>
                        <span class="font-medium text-slate-800">Membuka Laporan Kas &amp; riwayat closing</span>
                        @if(!empty($parameter['tgl_dari']) && !empty($parameter['tgl_sampai']))
                            &middot; periode {{ $parameter['tgl_dari'] }} sampai {{ $parameter['tgl_sampai'] }}
                        @endif
                        @if(!empty($parameter['status_lock']))
                            &middot; status {{ $parameter['status_lock'] === 'closed' ? 'sudah tutup' : 'masih buka' }}
                        @endif
                    </p>
                @endif
            </div>

            <form method="POST" action="{{ route('laporan.kas.gerbang', $parameter) }}" class="mt-4">
                @csrf

                <label for="password-administrator" class="block text-[11px] font-medium uppercase tracking-wide text-slate-400 mb-1">
                    Password Administrator
                </label>
                <x-form.input
                    id="password-administrator"
                    type="password"
                    name="password"
                    required
                    autofocus
                    autocomplete="off"
                    placeholder="Masukkan password akun Administrator"
                    :error="$errors->has('password')" />

                @error('password')
                    <p class="mt-1.5 text-[12px] font-medium text-rose-600">{{ $message }}</p>
                @enderror

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <x-button type="submit">
                        <i class="fas fa-lock-open"></i>
                        Buka Laporan
                    </x-button>
                    <x-button href="{{ route('kasir.kelola') }}" variant="outline">
                        Kembali ke Kelola Kas
                    </x-button>
                </div>
            </form>

            <p class="mt-4 text-[11px] text-slate-400 leading-relaxed">
                Setelah password benar, laporan terbuka {{ $menit }} menit untuk sesi login ini,
                lalu terkunci lagi. Administrator yang sedang login tidak perlu mengisi form ini.
                Password tidak disimpan atau ditampilkan ulang.
            </p>
        </div>
    </div>
</div>
@endsection
