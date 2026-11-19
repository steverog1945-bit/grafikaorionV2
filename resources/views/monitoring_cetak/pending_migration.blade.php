@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-3xl mx-auto">

<x-page-header title="Monitoring Cetak" subtitle="Modul siap dipakai, menunggu migration di environment ini." />

<div class="rounded-xl border border-amber-200 bg-amber-50 p-6">
    <div class="flex items-start gap-4">
        <span class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
            <i class="fas fa-database text-sm"></i>
        </span>
        <div class="min-w-0">
            <h2 class="text-sm font-semibold text-amber-900">Tabel Monitoring Cetak belum dibuat di database ini</h2>
            <p class="mt-1.5 text-[13px] text-amber-900 leading-relaxed">
                Migration <code class="bg-white border border-amber-200 rounded px-1 py-0.5">monitoring_mesins</code>,
                <code class="bg-white border border-amber-200 rounded px-1 py-0.5">monitoring_agents</code>,
                <code class="bg-white border border-amber-200 rounded px-1 py-0.5">monitoring_events</code>
                belum dijalankan di environment ini. Additive - tidak mengubah data yang ada.
            </p>
            <div class="mt-4 rounded-lg bg-white border border-amber-200 px-4 py-3 text-[13px] font-mono text-amber-900 leading-relaxed overflow-x-auto">
                php artisan migrate --force<br>
                php artisan monitoring-cetak:seed-machines<br>
                php artisan monitoring-cetak:create-agent &lt;id&gt; --nama="PC-PRODUKSI-01"
            </div>
            <p class="mt-3 text-[11px] text-amber-800/90 leading-relaxed">
                Untuk PC produksi, agent spooler (<span class="font-medium">OrionMonitorAgent.ps1</span>) mengirim
                event ke <code class="bg-white border border-amber-200 rounded px-1 py-0.5">/api/monitoring-cetak/agent/*</code>.
                Endpoint agent hanya menjawab bila <code class="bg-white border border-amber-200 rounded px-1 py-0.5">MONITORING_CETAK_AGENT_ENABLED=true</code>
                disetel di server (default terkunci). Lihat
                <code class="bg-white border border-amber-200 rounded px-1 py-0.5">docs/reviews/MONITORING_CETAK_ERP.md</code>.
            </p>
        </div>
    </div>
</div>

<a href="{{ route('dashboard') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
    <i class="fas fa-arrow-left text-xs"></i> Kembali ke Dashboard
</a>

</div>
@endsection
