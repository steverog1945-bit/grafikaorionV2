@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Antrean Pesanan Baru" subtitle="Daftar transaksi yang belum diproses pembayarannya oleh kasir.">
        <x-slot:actions>
            <x-button href="{{ route('kasir.index') }}" variant="secondary">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Monitoring
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- LOCK KASIR - $kasAktif dibagikan global lewat AppServiceProvider.
         Antrean baru TERKUNCI (kartu "Buka Transaksi" dinonaktifkan) selama
         kas hari ini belum dibuka, supaya kasir tidak masuk ke form
         pembayaran yang ujung-ujungnya pasti ditolak. --}}
    @if(!$kasAktif)
    <div class="mb-8 flex items-center gap-3 bg-amber-50 border-2 border-amber-200 rounded-2xl px-5 py-4">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-lg">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-black text-amber-800 uppercase tracking-wide">Kas Kasir Belum Dibuka</p>
            <p class="text-xs text-amber-600 mt-0.5">Anda tidak dapat memproses transaksi apa pun sebelum membuka saldo kas hari ini.</p>
        </div>
        <x-button href="{{ route('kasir.kelola') }}" variant="warning" class="shrink-0">
            <i class="fas fa-wallet"></i> Buka Kas Sekarang
        </x-button>
    </div>
    @endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($waiting as $w)
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-300 transition-all overflow-hidden group relative">
        {{-- Dekorasi Dot --}}
        <div class="absolute top-6 right-6 flex gap-1">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
        </div>

        <div class="p-8">
            <div class="mb-6">
                <span class="bg-indigo-50 text-indigo-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Nota #{{ $w->nomor_nota }}</span>
                <p class="text-[10px] text-slate-400 mt-2 uppercase">{{ date('d M Y • H:i', strtotime($w->created_at)) }}</p>
            </div>
            
            <h3 class="text-xl font-black text-slate-800 uppercase leading-tight mb-2 group-hover:text-indigo-600 transition-colors">{{ $w->nama_konsumen }}</h3>
            
            <div class="flex items-center gap-2 mb-6">
                <div class="h-px flex-1 bg-slate-100"></div>
                <span class="text-[9px] font-black text-slate-300 uppercase">Estimasi Tagihan</span>
                <div class="h-px flex-1 bg-slate-100"></div>
            </div>

            <div class="mb-8 text-center">
                <span class="text-3xl font-black text-slate-800">
                    <span class="text-sm text-slate-400 font-normal">Rp</span> {{ number_format($w->total_bayar, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex flex-col gap-2">
                @if($kasAktif)
                    <a href="{{ url('/kasir/bayar-form/'.$w->id_transaksi) }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-indigo-100">
                        Buka Transaksi 💰
                    </a>
                @else
                    <span class="w-full bg-slate-100 text-slate-400 text-center py-4 rounded-2xl font-black text-xs uppercase tracking-widest cursor-not-allowed flex items-center justify-center gap-2" title="Kas belum dibuka">
                        <i class="fas fa-lock"></i> Terkunci
                    </span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white border-2 border-dashed border-slate-200 rounded-[3rem] p-20 text-center">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="text-4xl">☕</span>
        </div>
        <h3 class="text-slate-800 font-black uppercase tracking-tight">Tidak ada antrean baru</h3>
        <p class="text-slate-400 text-sm italic">Santai sejenak, semua pesanan sudah masuk ke sistem pembayaran.</p>
    </div>
    @endforelse
</div>

</div>

<style>
    /* Tambahan animasi agar kartu terlihat smooth saat hover */
    .group:hover {
        transform: translateY(-5px);
    }
</style>
@endsection