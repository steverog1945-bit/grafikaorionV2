{{--
    Letterhead cetak laporan: logo & identitas perusahaan (nama, alamat,
    telp/fax, email/website) rata kiri, judul dokumen + periode cetak rata
    kanan, ditutup garis tebal. Cuma tampil saat dicetak (hidden
    print:block).

    Sebelumnya versi ini terasa "mepet" ke tepi kertas - ternyata BUKAN
    karena tata letak kiri-kanannya, tapi karena setiap halaman laporan
    men-set `main { padding: 0 !important }` saat print (lihat <style>
    @media print di masing-masing laporan), jadi seluruh konten memang
    menempel ke tepi kertas fisik. Perbaikannya di situ (padding print
    dikembalikan wajar), bukan dengan menghilangkan letterhead ini.

    Variabel dari view pemanggil:
    - $docTitle  : judul dokumen, mis. "Rekap Transaksi Order"
    - $tglDari, $tglSampai : dipakai untuk baris "Periode Cetak" (opsional)
--}}
@php($appSettingReportPrint = \App\Models\AppSetting::current())
<div class="hidden print:block mb-10">
    <div class="flex items-start justify-between gap-6">
        <div class="flex items-start gap-3">
            @if($appSettingReportPrint->logo_path)
                <img src="{{ asset('storage/' . $appSettingReportPrint->logo_path) }}" alt="Logo" class="h-14 w-14 object-contain shrink-0">
            @endif
            <div>
                <h1 class="text-[15px] font-extrabold text-slate-900 uppercase leading-tight">{{ $appSettingReportPrint->nama_aplikasi }}</h1>
                @if($appSettingReportPrint->alamat)
                    <p class="text-[10.5px] text-slate-600 leading-snug mt-0.5">{{ $appSettingReportPrint->alamat }}</p>
                @endif
                @if($appSettingReportPrint->telepon || $appSettingReportPrint->fax)
                    <p class="text-[10.5px] text-slate-600 leading-snug">Telp : {{ $appSettingReportPrint->telepon ?: '-' }}, Fax : {{ $appSettingReportPrint->fax ?: '-' }}</p>
                @endif
                @if($appSettingReportPrint->email || $appSettingReportPrint->website)
                    <p class="text-[10.5px] text-slate-600 leading-snug">Email : {{ $appSettingReportPrint->email ?: '-' }}, Website : {{ $appSettingReportPrint->website ?: '-' }}</p>
                @endif
            </div>
        </div>
        <div class="text-right shrink-0">
            <h2 class="text-[17px] font-bold text-slate-900 leading-tight">{{ $docTitle }}</h2>
            @if(isset($tglDari) && isset($tglSampai))
                <p class="text-[11px] text-slate-600 mt-1">
                    Periode {{ \Carbon\Carbon::parse($tglDari)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tglSampai)->format('d/m/Y') }}
                </p>
            @endif
        </div>
    </div>
    <div class="mt-3 border-t-2 border-slate-900"></div>
</div>
