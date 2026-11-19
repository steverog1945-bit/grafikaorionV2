<?php

namespace App\Http\Controllers;

use App\Services\MonitoringCetakQuery;
use App\Services\MonitoringCetakService;

/**
 * Monitoring Cetak - pantau timing cetakan tim produksi per mesin.
 *
 * Halaman ini menggantikan aplikasi monitor berdiri sendiri. Data antrean
 * dibaca dari tabel ERP langsung; kondisi live (CETAK/SIAGA/OFFLINE) datang
 * dari agent spooler/RIP di PC produksi.
 */
class MonitoringCetakController extends Controller
{
    public function __construct(
        private readonly MonitoringCetakQuery $query,
        private readonly MonitoringCetakService $machines,
    ) {}

    public function index()
    {
        $data = $this->payload();
        $data['now'] = now()->toIso8601String();

        return view('monitoring_cetak.index', $data);
    }

    /** Polling JSON (4 detik) - data sama seperti render pertama. */
    public function status()
    {
        return response()->json($this->payload());
    }

    private function payload(): array
    {
        $queue = $this->query->activeQueue();
        $counts = $this->query->statusCounts();
        $states = $this->machines->liveStates();

        // Cocokkan id_mesin ERP -> sesi cetak agent yang sedang berjalan.
        $sessionByErpMesin = $states
            ->filter(fn ($m) => $m['state'] === 'printing' && $m['erp_mesin_id'] && $m['eta'])
            ->keyBy('erp_mesin_id');

        $queue = $queue->map(function ($item) use ($sessionByErpMesin) {
            $item->eta_selesai_fmt = null;
            $item->eta_mulai_fmt = null;
            $item->eta_sisa_label = null;
            $item->eta_akurasi = null;
            $item->eta_sampel = null;
            $item->eta_telat = false;

            if ($item->status_spk === 'Cetak') {
                $session = $sessionByErpMesin->get((int) $item->id_mesin);
                if ($session && $session['eta']) {
                    $eta = $session['eta'];
                    $item->eta_selesai_fmt = $eta['eta_selesai_fmt'] ?? null;
                    $item->eta_mulai_fmt = isset($eta['eta_mulai'])
                        ? \Carbon\Carbon::parse($eta['eta_mulai'])->format('H:i')
                        : null;
                    $item->eta_sisa_label = $eta['eta_sisa_label'] ?? null;
                    $item->eta_akurasi = $eta['eta_akurasi'] ?? null;
                    $item->eta_sampel = $eta['eta_sampel'] ?? null;
                    $item->eta_telat = $eta['eta_telat'] ?? false;
                }
            }

            $item->p_ukuran = (float) $item->p_ukuran;
            $item->l_ukuran = (float) $item->l_ukuran;

            return $item;
        })->values();

        return [
            'queue' => $queue,
            'counts' => $counts,
            'machines' => $states,
            'agents_online' => $states->where('agent_online', true)->count(),
            'agents_total' => $states->whereNotNull('agent_id')->count(),
            'sedang_cetak_agent' => $states->where('state', 'printing')->count(),
            'now' => now()->toIso8601String(),
        ];
    }
}
