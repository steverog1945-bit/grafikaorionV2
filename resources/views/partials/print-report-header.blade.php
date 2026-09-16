{{--
    Judul cetak laporan - desain sendiri (bukan meniru referensi), sengaja
    dibuat sesederhana mungkin: judul dokumen + periode, rata kiri dengan
    margin cukup di kedua sisi (tidak mepet ke tepi kertas), ditutup satu
    garis tipis. TANPA blok identitas toko (nama/alamat/logo) - laporan
    ini untuk kebutuhan internal, bukan dokumen resmi ke pihak luar
    seperti nota, jadi cukup judul & periode saja supaya tetap bersih.
    Cuma tampil saat dicetak (hidden print:block).

    Variabel dari view pemanggil:
    - $docTitle : judul dokumen, mis. "Rekap Transaksi Order"
    - $tglDari, $tglSampai : dipakai untuk baris periode (opsional)
--}}
<div class="hidden print:block mb-8 pb-4 border-b border-slate-300">
    <h1 class="text-[19px] font-bold text-slate-900">{{ $docTitle }}</h1>
    @if(isset($tglDari) && isset($tglSampai))
        <p class="text-[11px] text-slate-500 mt-1">
            Periode {{ \Carbon\Carbon::parse($tglDari)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($tglSampai)->format('d M Y') }}
        </p>
    @endif
</div>
