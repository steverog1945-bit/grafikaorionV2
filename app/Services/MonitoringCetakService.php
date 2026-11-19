<?php

namespace App\Services;

use App\Models\MonitoringAgent;
use App\Models\MonitoringEvent;
use App\Models\MonitoringMachine;
use Illuminate\Support\Collection;

/**
 * Kondisi live per mesin (sumber A: agent spooler/RIP; sumber B: tanpa
 * agent -> kartu TANPA AGENT, hanya antrean ERP yang tersedia).
 */
class MonitoringCetakService
{
    public function __construct(private readonly MonitoringCetakEtaService $eta) {}

    public function liveStates(): Collection
    {
        $machines = MonitoringMachine::where('aktif', true)
            ->orderBy('id_mesin')
            ->get();
        $ids = $machines->pluck('id');

        $latest30 = MonitoringEvent::whereIn('monitoring_mesin_id', $ids)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('monitoring_mesin_id');

        $latestByMachine = $latest30->map(fn ($g) => $g->first());

        $started12h = MonitoringEvent::whereIn('monitoring_mesin_id', $ids)
            ->where('event', 'started')
            ->where('created_at', '>=', now()->subHours(12))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['monitoring_mesin_id', 'created_at', 'doc_name', 'pages_total', 'bytes_total'])
            ->groupBy('monitoring_mesin_id')
            ->map(fn ($g) => $g->first());

        $finished12h = MonitoringEvent::whereIn('monitoring_mesin_id', $ids)
            ->whereIn('event', ['completed', 'error'])
            ->where('created_at', '>=', now()->subHours(12))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['monitoring_mesin_id', 'created_at'])
            ->groupBy('monitoring_mesin_id')
            ->map(fn ($g) => $g->first());

        return $machines->map(function (MonitoringMachine $m) use ($latestByMachine, $started12h, $finished12h) {
            $agent = MonitoringAgent::where('monitoring_mesin_id', $m->id)
                ->where('aktif', true)
                ->first();
            $online = $agent ? $agent->online(2) : false;
            $latest = $latestByMachine->get($m->id);

            $state = 'tanpa_agent';
            if ($agent) {
                $state = $online ? 'idle' : 'offline';
            }

            $session = null;
            $started = $started12h->get($m->id);
            $finished = $finished12h->get($m->id);
            if ($started && (! $finished || $started->created_at > $finished->created_at)) {
                $session = $started;
                if ($agent && $online) {
                    $state = 'printing';
                }
            }

            $progress = null;
            $pagesTotal = $session?->pages_total;
            $pagesDone = null;
            if ($latest && ($latest->pages_total ?? 0) > 0 && ($latest->pages_done ?? 0) >= 0) {
                $pagesTotal = $latest->pages_total;
                $pagesDone = (int) $latest->pages_done;
                $progress = (int) min(100, round(($pagesDone / max(1, $pagesTotal)) * 100));
            } elseif ($latest && ($latest->bytes_total ?? 0) > 0 && ($latest->bytes_done ?? 0) >= 0) {
                $pagesTotal = null;
                $pagesDone = null;
                $progress = (int) min(100, round(($latest->bytes_done / $latest->bytes_total) * 100));
            }

            $eta = null;
            if ($session) {
                $eta = $this->eta->forSession(
                    $m->id,
                    $session->created_at,
                    $pagesTotal,
                    $pagesDone,
                    ($latest && $latest->event === 'progress') ? $latest->created_at : null
                );
                $eta['eta_mulai'] = $session->created_at->toIso8601String();
                $eta['eta_selesai'] = $eta['eta_selesai']?->toIso8601String();
                $eta['eta_selesai_fmt'] = $eta['eta_selesai']
                    ? \Carbon\Carbon::parse($eta['eta_selesai'])->format('H:i')
                    : null;
                $eta['eta_sisa_label'] = MonitoringCetakQuery::sisaLabel($eta['eta_sisa_detik']);
                $eta['eta_telat'] = ($eta['eta_sisa_detik'] ?? 0) < 0;
                $eta['doc_name'] = $session->doc_name;
            }

            return [
                'id' => $m->id,
                'erp_mesin_id' => $m->id_mesin,
                'nama_mesin' => $m->nama_mesin,
                'tipe_mesin' => $m->tipe_mesin,
                'software' => $m->softwareLabel(),
                'pc_ip' => $m->pc_ip,
                'printer_name' => $m->printer_name,
                'agent_id' => $agent?->id,
                'agent_nama' => $agent?->nama,
                'agent_online' => $online,
                'agent_last_seen' => $agent?->last_seen_at?->toIso8601String(),
                'state' => $state,
                'current_doc' => $session?->doc_name,
                'progres' => $progress,
                'pages_total' => $pagesTotal,
                'pages_done' => $pagesDone,
                'event_terakhir' => $latest?->event,
                'event_terakhir_at' => $latest?->created_at?->toIso8601String(),
                'eta' => $eta,
            ];
        })->values();
    }
}
