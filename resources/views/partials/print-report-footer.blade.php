{{--
    Footer cetak laporan: satu baris yang sama untuk SEMUA laporan
    (Rekap Order, Stok Bahan, Akuntansi, dst). Sebelumnya hanya
    Evaluasi Master yang punya footer seperti ini, jadi lembar
    tercetak tidak senada antar laporan.

    Hanya tampil saat dicetak (hidden print:block). Variabel opsional
    $printNote dipakai untuk catatan tambahan di sebelah kanan.
--}}
@php($printUser = auth()->user()?->username ?? auth()->user()?->name ?? 'sistem')
<div class="hidden print:block mt-6 pt-2 border-t border-[var(--line-200)] text-[9px] text-[var(--ink-600)] flex items-center justify-between gap-4">
    <span>Dicetak pada {{ now()->format('d/m/Y H:i') }} oleh {{ $printUser }}</span>
    @if(!empty($printNote ?? null))
        <span>{{ $printNote }}</span>
    @endif
</div>
