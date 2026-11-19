@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-8 max-w-[1600px] mx-auto">

<x-page-header title="Monitoring Cetak" subtitle="Timing cetak tim produksi per mesin · antrean SPK hari ini">
    <x-slot:actions>
        <a href="{{ route('spk.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-[13px] font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-800">
            <i class="fas fa-list-check text-xs"></i> Antrian SPK
        </a>
    </x-slot:actions>
</x-page-header>

{{-- KPI STRIP --}}
@php
    $kpis = [
        ['id' => 'kpi-mesin', 'label' => 'Mesin Terpantau', 'value' => $machines->count(), 'sub' => "{$agents_online}/{$agents_total} agent online"],
        ['id' => 'kpi-cetak', 'label' => 'Sedang Cetak', 'value' => $sedang_cetak_agent, 'sub' => 'dikonfirmasi agent'],
        ['id' => 'kpi-antre', 'label' => 'Antre', 'value' => $counts['antre'], 'sub' => 'belum diambil'],
        ['id' => 'kpi-desain', 'label' => 'Desain / Prepress', 'value' => $counts['desain'], 'sub' => 'belum masuk mesin'],
        ['id' => 'kpi-selesai', 'label' => 'Selesai Hari Ini', 'value' => $counts['selesai'], 'sub' => 'status SPK = Selesai'],
    ];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6" id="kpi-strip">
    @foreach($kpis as $k)
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ $k['label'] }}</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 tabular-nums" id="{{ $k['id'] }}">{{ $k['value'] }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">{{ $k['sub'] }}</p>
        </div>
    @endforeach
</div>

{{-- MESIN --}}
<div class="mb-2 flex flex-wrap items-center justify-between gap-2">
    <h2 class="text-sm font-semibold text-slate-800">Status Mesin</h2>
    <p class="text-[11px] text-slate-500">sumber: agent spooler/RIP &middot; refresh tiap 4 dtk &middot; ETA dari histori agent (90 hari)</p>
</div>
<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 mb-6" id="machine-grid">
    @forelse($machines as $m)
        @php
            $pill = match ($m['state']) {
                'printing' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'CETAK', 'bg-emerald-500'],
                'idle' => ['bg-slate-100 text-slate-600 border-slate-200', 'SIAGA', 'bg-slate-400'],
                'offline' => ['bg-amber-50 text-amber-800 border-amber-200', 'OFFLINE', 'bg-amber-500'],
                default => ['bg-slate-50 text-slate-500 border-slate-200', 'TANPA AGENT', 'bg-slate-300'],
            };
        @endphp
        <article class="rounded-xl border border-slate-200 bg-white p-4 flex flex-col gap-2" data-mesin-id="{{ $m['id'] }}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 leading-snug">{{ $m['nama_mesin'] }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">{{ $m['tipe_mesin'] ?: '—' }} &middot; {{ $m['software'] }}</p>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $pill[0] }}">
                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $pill[2] }}"></span>{{ $pill[1] }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400">{{ $m['pc_ip'] ? 'PC ' . $m['pc_ip'] : '' }}{{ $m['printer_name'] ? ' · ' . $m['printer_name'] : '' }}</p>

            @if($m['state'] === 'printing')
                <p class="text-xs text-slate-700 truncate" title="{{ $m['current_doc'] }}"><span class="font-medium text-slate-900">Cetak:</span> {{ $m['current_doc'] ?: 'dokumen tidak diketahui' }}</p>
                @if($m['progres'] !== null)
                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden"><span class="block h-full bg-emerald-500" style="width: {{ $m['progres'] }}%"></span></div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 tabular-nums">
                        <span>{{ $m['pages_done'] ?? 0 }}/{{ $m['pages_total'] ?? '?' }} hal</span>
                        <span>{{ $m['progres'] }}%</span>
                    </div>
                @else
                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden animate-pulse"><span class="block h-full w-1/3 bg-emerald-300 rounded-full"></span></div>
                    <div class="text-[11px] text-slate-500">progress tidak tersedia (job dikirim ke RIP)</div>
                @endif
                <div class="text-[11px] leading-snug">
                    @if($m['eta'] && $m['eta']['eta_selesai_fmt'])
                        <span class="{{ $m['eta']['eta_telat'] ? 'text-rose-600' : 'text-emerald-700' }} font-medium">
                            ETA <span class="tabular-nums">{{ $m['eta']['eta_selesai_fmt'] }}</span> &middot; {{ $m['eta']['eta_sisa_label'] }}
                        </span>
                        <span class="text-slate-400"> &middot; akurasi {{ $m['eta']['eta_akurasi'] }} (n={{ $m['eta']['eta_sampel'] }})</span>
                    @else
                        <span class="text-slate-500 font-medium">ETA belum cukup data</span>
                        <span class="text-slate-400"> &middot; agent butuh ≥3 job selesai</span>
                    @endif
                </div>
            @else
                <p class="text-xs text-slate-400">Tidak ada job berjalan di mesin ini</p>
            @endif

            <p class="mt-auto text-[11px] {{ $m['agent_online'] ? 'text-emerald-700' : 'text-slate-400' }}">
                @if($m['agent_id'])
                    agent: {{ $m['agent_nama'] }} &middot; {{ $m['agent_online'] ? 'online' : 'offline' }}@if($m['agent_last_seen']) &middot; {{ \Carbon\Carbon::parse($m['agent_last_seen'])->diffForHumans() }}@endif
                @else
                    tanpa agent &middot; status hanya dari antrean SPK
                @endif
            </p>
        </article>
    @empty
        <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
            <p class="text-sm font-medium text-slate-700">Belum ada mesin terdaftar</p>
            <p class="mt-1 text-xs text-slate-500">Daftarkan dari tabel ERP: <code class="bg-white border border-slate-200 rounded px-1.5 py-0.5">php artisan monitoring-cetak:seed-machines</code></p>
        </div>
    @endforelse
</div>

{{-- ANTREAN --}}
<div class="mb-2 flex flex-wrap items-center justify-between gap-2">
    <h2 class="text-sm font-semibold text-slate-800">Antrean Hari Ini</h2>
    <p class="text-[11px] text-slate-500">SPK hari ini (Asia/Jakarta) &middot; <span id="queue-count">{{ $queue->count() }}</span> item &middot; URGENT duluan &middot; ETA hanya untuk mesin sedang Cetak</p>
</div>
<x-table wrapperClass="overflow-hidden">
    <thead>
        <tr>
            <th class="p-4 w-10 text-center">#</th>
            <th class="p-4">Nota</th>
            <th class="p-4">Konsumen</th>
            <th class="p-4">File</th>
            <th class="p-4">Produk</th>
            <th class="p-4">Mesin</th>
            <th class="p-4 text-right">Qty</th>
            <th class="p-4 text-center">Status</th>
            <th class="p-4 text-center">Mulai</th>
            <th class="p-4 text-center">ETA</th>
            <th class="p-4 text-right">Sisa</th>
        </tr>
    </thead>
    <tbody id="queue-body">
        @forelse($queue as $i => $q)
            <tr class="{{ $q->status_urgent ? 'bg-rose-50/60' : '' }}">
                <td class="p-4 text-center text-xs tabular-nums text-slate-500">{{ $i + 1 }}@if($q->status_urgent)<br><span class="text-[9px] font-bold text-rose-600 uppercase">Urgent</span>@endif</td>
                <td class="p-4 text-xs font-medium text-slate-800 whitespace-nowrap">{{ $q->nomor_nota }}</td>
                <td class="p-4 text-xs text-slate-700">{{ $q->nama_konsumen }}</td>
                <td class="p-4 text-xs text-slate-600 max-w-[220px] truncate" title="{{ $q->nama_file }}">{{ $q->nama_file }}</td>
                <td class="p-4 text-xs text-slate-600">{{ $q->nama_produk }}</td>
                <td class="p-4 text-xs text-slate-600">{{ $q->nama_mesin ?? '—' }}</td>
                <td class="p-4 text-right text-xs tabular-nums text-slate-800">{{ $q->qty_cetak ?? $q->qty }}@if($q->qty_cetak !== null && (int) $q->qty_cetak !== (int) $q->qty)<span class="text-slate-400">/{{ $q->qty }}</span>@endif</td>
                <td class="p-4 text-center"><x-badge :status="$q->status_spk">{{ strtoupper($q->status_spk) }}</x-badge></td>
                <td class="p-4 text-center text-xs tabular-nums text-slate-600">{{ $q->eta_mulai_fmt ?? '—' }}</td>
                <td class="p-4 text-center text-xs tabular-nums text-slate-800">{{ $q->eta_selesai_fmt ?? '—' }}</td>
                <td class="p-4 text-right text-xs tabular-nums {{ $q->eta_telat ? 'text-rose-600' : 'text-amber-700' }}">{{ $q->eta_sisa_label ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="11"><x-empty-state title="Antrean kosong" /></td></tr>
        @endforelse
    </tbody>
</x-table>

<p class="text-[11px] text-slate-400 mt-4">
    Cara baca ETA: <b>progress</b> = counter halaman RIP (paling akurat) &middot; <b>median histori agent</b> = rata-rata waktu job di mesin yang sama &middot; <b>belum cukup data</b> = agent baru mulai. Klik operator sengaja tidak dipakai untuk ETA.
</p>

</div>

<script>
(function () {
    const STATUS_URL = @json(route('monitoring-cetak.status'));
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const grid = document.getElementById('machine-grid');
    let prevPrinting = new Set([...document.querySelectorAll('#machine-grid article[data-mesin-id]')]
        .filter((el) => el.innerText.includes('CETAK'))
        .map((el) => el.dataset.mesinId));

    const pillMap = {
        printing: ['bg-emerald-50 text-emerald-700 border-emerald-200', 'CETAK', 'bg-emerald-500'],
        idle: ['bg-slate-100 text-slate-600 border-slate-200', 'SIAGA', 'bg-slate-400'],
        offline: ['bg-amber-50 text-amber-800 border-amber-200', 'OFFLINE', 'bg-amber-500'],
        tanpa_agent: ['bg-slate-50 text-slate-500 border-slate-200', 'TANPA AGENT', 'bg-slate-300'],
    };

    function machineHTML(m) {
        const [pillCls, pillTxt, dot] = pillMap[m.state] || pillMap.tanpa_agent;
        let body;
        if (m.state === 'printing') {
            const doc = m.current_doc ? esc(m.current_doc) : 'dokumen tidak diketahui';
            const prog = (m.progres !== null && m.progres !== undefined)
                ? `<div class="h-1.5 rounded-full bg-slate-100 overflow-hidden"><span class="block h-full bg-emerald-500" style="width:${m.progres}%"></span></div>
                   <div class="flex items-center justify-between text-[11px] text-slate-500 tabular-nums"><span>${esc(m.pages_done ?? 0)}/${esc(m.pages_total ?? '?')} hal</span><span>${m.progres}%</span></div>`
                : `<div class="h-1.5 rounded-full bg-slate-100 overflow-hidden animate-pulse"><span class="block h-full w-1/3 bg-emerald-300 rounded-full"></span></div><div class="text-[11px] text-slate-500">progress tidak tersedia (job dikirim ke RIP)</div>`;
            const eta = m.eta;
            const etaHtml = (eta && eta.eta_selesai_fmt)
                ? `<span class="${eta.eta_telat ? 'text-rose-600' : 'text-emerald-700'} font-medium">ETA <span class="tabular-nums">${esc(eta.eta_selesai_fmt)}</span> · ${esc(eta.eta_sisa_label ?? '')}</span><span class="text-slate-400"> · akurasi ${esc(eta.eta_akurasi)} (n=${esc(eta.eta_sampel)})</span>`
                : `<span class="text-slate-500 font-medium">ETA belum cukup data</span><span class="text-slate-400"> · agent butuh ≥3 job selesai</span>`;
            body = `<p class="text-xs text-slate-700 truncate" title="${esc(doc)}"><span class="font-medium text-slate-900">Cetak:</span> ${esc(doc)}</p>${prog}<div class="text-[11px] leading-snug">${etaHtml}</div>`;
        } else {
            body = `<p class="text-xs text-slate-400">Tidak ada job berjalan di mesin ini</p>`;
        }
        const agentLine = m.agent_id
            ? `<p class="mt-auto text-[11px] ${m.agent_online ? 'text-emerald-700' : 'text-slate-400'}">agent: ${esc(m.agent_nama)} · ${m.agent_online ? 'online' : 'offline'}${m.agent_last_seen ? ' · ' + new Date(m.agent_last_seen).toLocaleString('id-ID') : ''}</p>`
            : `<p class="mt-auto text-[11px] text-slate-400">tanpa agent · status hanya dari antrean SPK</p>`;
        return `<article class="rounded-xl border border-slate-200 bg-white p-4 flex flex-col gap-2" data-mesin-id="${m.id}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900 leading-snug">${esc(m.nama_mesin)}</p>
                <p class="mt-0.5 text-[11px] text-slate-500">${esc(m.tipe_mesin || '—')} · ${esc(m.software)}</p></div>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-semibold ${pillCls}"><span class="inline-block w-1.5 h-1.5 rounded-full ${dot}"></span>${pillTxt}</span>
            </div>
            <p class="text-[11px] text-slate-400">${m.pc_ip ? 'PC ' + esc(m.pc_ip) : ''}${m.printer_name ? ' · ' + esc(m.printer_name) : ''}</p>
            ${body}
            ${agentLine}
        </article>`;
    }

    function queueRowHTML(q, i) {
        const urgent = q.status_urgent ? 'bg-rose-50/60' : '';
        const urg = q.status_urgent ? '<br><span class="text-[9px] font-bold text-rose-600 uppercase">Urgent</span>' : '';
        const qty = `${esc(q.qty_cetak ?? q.qty)}${q.qty_cetak !== null && Number(q.qty_cetak) !== Number(q.qty) ? `<span class="text-slate-400">/${esc(q.qty)}</span>` : ''}`;
        const etaCell = q.eta_selesai_fmt ?? '—';
        const sisa = q.eta_sisa_label ?? '—';
        const sisaCls = q.eta_telat ? 'text-rose-600' : 'text-amber-700';
        return `<tr class="${urgent}">
            <td class="p-4 text-center text-xs tabular-nums text-slate-500">${i + 1}${urg}</td>
            <td class="p-4 text-xs font-medium text-slate-800 whitespace-nowrap">${esc(q.nomor_nota)}</td>
            <td class="p-4 text-xs text-slate-700">${esc(q.nama_konsumen)}</td>
            <td class="p-4 text-xs text-slate-600 max-w-[220px] truncate" title="${esc(q.nama_file ?? '')}">${esc(q.nama_file ?? '')}</td>
            <td class="p-4 text-xs text-slate-600">${esc(q.nama_produk)}</td>
            <td class="p-4 text-xs text-slate-600">${esc(q.nama_mesin ?? '—')}</td>
            <td class="p-4 text-right text-xs tabular-nums text-slate-800">${qty}</td>
            <td class="p-4 text-center"><span class="chip chip-info rounded-sm">${esc(String(q.status_spk).toUpperCase())}</span></td>
            <td class="p-4 text-center text-xs tabular-nums text-slate-600">${esc(q.eta_mulai_fmt ?? '—')}</td>
            <td class="p-4 text-center text-xs tabular-nums text-slate-800">${esc(etaCell)}</td>
            <td class="p-4 text-right text-xs tabular-nums ${sisaCls}">${esc(sisa)}</td>
        </tr>`;
    }

    function toast(msg) {
        const el = document.createElement('div');
        el.className = 'fixed bottom-6 right-6 z-50 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-800 shadow-lg';
        el.innerHTML = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 6000);
    }

    async function refresh() {
        try {
            const res = await fetch(STATUS_URL, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!res.ok) return;
            const d = await res.json();

            document.getElementById('kpi-mesin').textContent = d.machines.length;
            document.getElementById('kpi-cetak').textContent = d.sedang_cetak_agent;
            document.getElementById('kpi-antre').textContent = d.counts.antre;
            document.getElementById('kpi-desain').textContent = d.counts.desain;
            document.getElementById('kpi-selesai').textContent = d.counts.selesai;

            document.getElementById('queue-count').textContent = d.queue.length;
            grid.innerHTML = d.machines.map(machineHTML).join('') || '';

            const nowPrinting = new Set(d.machines.filter((m) => m.state === 'printing').map((m) => String(m.id)));
            for (const id of prevPrinting) {
                if (!nowPrinting.has(id)) {
                    const m = d.machines.find((x) => String(x.id) === id);
                    if (m) toast(`<b>${esc(m.nama_mesin)}</b> selesai/berhenti mencetak`);
                }
            }
            prevPrinting = nowPrinting;

            document.getElementById('queue-body').innerHTML = d.queue.length
                ? d.queue.map(queueRowHTML).join('')
                : '<tr><td colspan="11"><p class="p-6 text-center text-sm text-slate-400">Antrean kosong</p></td></tr>';
        } catch (e) { /* jaringan sesaat - coba lagi tick berikutnya */ }
    }

    setInterval(refresh, 4000);
})();
</script>
@endsection
