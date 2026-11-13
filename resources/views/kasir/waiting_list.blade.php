@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-8 max-w-[1600px] mx-auto">

    <x-page-header title="Antrean Pesanan Baru" subtitle="SPK aktif setelah nota diterima kasir — Terima Saja (bayar nanti) atau Terima &amp; Bayar (bayar sekarang).">
        <x-slot:actions>
            <x-button href="{{ route('kasir.index') }}" variant="secondary">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Monitoring
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Antrean terkunci selama kas belum dibuka --}}
    @if(!$kasAktif)
    <div class="mb-6 flex flex-wrap items-center gap-4 rounded-2xl bg-amber-50 border border-amber-100 px-6 py-4">
        <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="flex-1 min-w-[200px]">
            <p class="text-sm font-medium text-amber-800">Kas kasir belum dibuka</p>
            <p class="text-xs text-amber-600/90 mt-0.5">Transaksi belum bisa diproses sebelum saldo kas hari ini dibuka.</p>
        </div>
        <x-button href="{{ route('kasir.kelola') }}" variant="warning" class="shrink-0 w-full sm:w-auto"><i class="fas fa-wallet"></i> Buka Kas Sekarang</x-button>
    </div>
    @endif

    <div class="mb-5 flex items-center gap-3">
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span> {{ count($waiting) }} nota menunggu
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5">
        @forelse($waiting as $w)
        {{-- tgl_order (bukan created_at) = waktu pesanan masuk yang sebenarnya --}}
        <div class="group relative rounded-2xl bg-white border border-slate-100 shadow-[0_8px_30px_rgba(15,23,42,0.04)] hover:shadow-[0_16px_40px_rgba(79,70,229,0.10)] hover:border-indigo-100 transition-all duration-300 overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-indigo-300 opacity-70"></div>

            <div class="p-6">
                <div class="flex items-start justify-between gap-3">
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-medium">Nota #{{ $w->nomor_nota }}</span>
                    <span class="w-2 h-2 mt-1.5 rounded-full bg-amber-300 animate-pulse" title="Menunggu pembayaran"></span>
                </div>

                <h3 class="mt-4 text-lg font-medium text-slate-800 leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">{{ $w->nama_konsumen }}</h3>
                <p class="mt-1 text-xs text-slate-400"><i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($w->tgl_order)->translatedFormat('d M Y, H:i') }}</p>

                <div class="mt-5 rounded-2xl bg-slate-50 px-4 py-4 text-center">
                    <p class="text-[11px] font-medium text-slate-400 mb-1">Estimasi Tagihan</p>
                    <p class="text-2xl font-medium text-slate-800 tracking-tight"><span class="text-sm font-medium text-slate-400 mr-1">Rp</span>{{ number_format($w->total_bayar, 0, ',', '.') }}</p>
                </div>

                <div class="mt-5">
                    @if($kasAktif)
                        {{-- Dua aksi berbeda (Terima ≠ bayar sekarang): diberi
                             label yang jelas + ikon yang berbeda + tooltip supaya
                             kasir tidak salah tekan. "Terima &amp; Bayar" diberi
                             konfirmasi satu langkah - kasir salah klik tidak bisa
                             masuk pembayaran tanpa sadar. --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('kasir.terimaTransaksi', $w->id_transaksi) }}" class="terima-form">
                                @csrf
                                <input type="hidden" name="submit_id" value="terima-{{ $w->id_transaksi }}-{{ $loop->index }}">
                                <button type="submit" title="Terima nota ke antrean SPK saja - pembayaran dilakukan nanti lewat Monitoring Kasir"
                                    class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs shadow-sm transition-all">
                                    <i class="fas fa-inbox"></i> Terima Saja
                                </button>
                            </form>
                            <form method="POST" action="{{ route('kasir.terimaTransaksi', $w->id_transaksi) }}" class="terima-bayar-form">
                                @csrf
                                <input type="hidden" name="submit_id" value="terima-bayar-{{ $w->id_transaksi }}-{{ $loop->index }}">
                                <input type="hidden" name="lanjut" value="bayar">
                                <button type="submit" title="Terima nota DAN langsung buka form pembayaran sekarang (lunas / DP)"
                                    class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-indigo-500 hover:bg-indigo-600 text-white font-medium text-xs shadow-sm transition-all">
                                    <i class="fas fa-money-bill-wave"></i> Terima &amp; Bayar
                                </button>
                            </form>
                        </div>
                    @else
                        <span class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-slate-100 text-slate-400 font-medium text-xs cursor-not-allowed" title="Kas belum dibuka">
                            <i class="fas fa-lock"></i> Terkunci
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full rounded-2xl bg-white border border-dashed border-slate-200 p-16 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center mx-auto mb-5"><i class="fas fa-mug-hot text-2xl"></i></div>
            <h3 class="text-slate-700 font-medium">Tidak ada antrean baru</h3>
            <p class="text-slate-400 text-sm mt-1">Semua pesanan sudah masuk ke sistem pembayaran.</p>
        </div>
        @endforelse
    </div>
</div>

<script>
    // Konfirmasi KHUSUS untuk "Terima & Bayar" (aksi uang + buka form pembayaran).
    // "Terima Saja" alur lama: langsung jalan tanpa konfirmasi.
    (function () {
        // Anti klik-ganda: tombol submit mati begitu form terkirim
        function kunciTombol(form) {
            form.querySelectorAll('button[type="submit"]').forEach(function (btn) {
                btn.disabled = true;
            });
        }

        // "Terima Saja" - alur lama (submit biasa, langsung terima)
        document.querySelectorAll('.terima-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                kunciTombol(form);
            });
        });

        // "Terima & Bayar" - butuh konfirmasi eksplisit (salah klik = tidak terjadi apa-apa)
        document.querySelectorAll('.terima-bayar-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (form.dataset.terkirim === '1') {
                    return; // sudah terkirim sekali - abaikan klik berikutnya
                }
                Swal.fire({
                    title: 'Terima &amp; Bayar?',
                    html: 'Nota ini masuk SPK <b>dan langsung membuka form pembayaran</b>.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Terima &amp; Bayar',
                    cancelButtonText: 'Batal, cek dulu nanti',
                    confirmButtonColor: '#4f46e5',
                    reverseButtons: true
                }).then(function (res) {
                    if (!res.isConfirmed) return;
                    form.dataset.terkirim = '1';
                    kunciTombol(form);
                    form.submit();
                });
            });
        });
    })();
</script>
@endsection
