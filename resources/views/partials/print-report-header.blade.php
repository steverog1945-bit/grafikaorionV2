{{--
    Header perusahaan KHUSUS SAAT DICETAK untuk halaman laporan (Laporan
    Kas/Order/Produksi) - halaman ini di layar memakai <x-page-header> biasa
    tanpa branding perusahaan (cukup untuk dilihat internal), tapi saat
    benar-benar dicetak/dijadikan PDF perlu identitas perusahaan yang sama
    seperti nota/SPK. Disembunyikan di layar (.print-report-header cuma
    muncul via @media print), pakai $docTitle untuk judul laporan.
--}}
@php($appSettingReportPrint = \App\Models\AppSetting::current())
<div class="print-report-header hidden print:block mb-6 pb-4 border-b border-slate-300 text-center">
    @if($appSettingReportPrint->logo_path)
        <img src="{{ asset('storage/' . $appSettingReportPrint->logo_path) }}" alt="Logo" class="h-10 mx-auto mb-2 object-contain">
    @endif
    <h1 class="text-lg font-extrabold text-slate-900">{{ $appSettingReportPrint->nama_aplikasi }}</h1>
    @if($appSettingReportPrint->alamat || $appSettingReportPrint->telepon)
        <p class="text-[11px] text-slate-500 mt-0.5">
            {{ $appSettingReportPrint->alamat }}{{ $appSettingReportPrint->alamat && $appSettingReportPrint->telepon ? ' • ' : '' }}{{ $appSettingReportPrint->telepon }}
        </p>
    @endif
    <p class="text-[11px] uppercase tracking-widest font-bold text-indigo-600 mt-2">{{ $docTitle }}</p>
</div>
