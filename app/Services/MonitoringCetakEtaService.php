<?php

namespace App\Services;

use App\Models\MonitoringEvent;
use Illuminate\Support\Facades\Cache;

/**
 * ETA selesai cetak dari histori agent sendiri (bukan klik operator).
 *
 * MULAI  = event 'started' untuk sesi cetak yang masih terbuka.
 * DURASI = pasangan started -> completed per mesin (90 hari), diambil median.
 * Progress diprioritaskan bila RIP melapor counter halaman/bytes.
 * Histori terlalu sedikit -> ETA null (tampil "belum cukup data"),
 * bukan angka palsu.
 */
class MonitoringCetakEtaService
{
    public const CACHE_KEY = 'erp.monitoring_cetak.agent_duration_stats';

    public function agentStats(int $hariKeBelakang = 90): array
    {
        return Cache::remember(self::CACHE_KEY, 120, function () use ($hariKeBelakang) {
            $events = MonitoringEvent::whereIn('event', ['started', 'completed'])
                ->where('created_at', '>=', now()->subDays($hariKeBelakang))
                ->orderBy('created_at')
                ->get(['monitoring_mesin_id', 'event', 'created_at']);

            $durasi = [];
            $open = [];

            foreach ($events as $e) {
                $mid = $e->monitoring_mesin_id;
                if ($e->event === 'started') {
                    $open[$mid] = $e->created_at;
                    continue;
                }
                if (isset($open[$mid])) {
                    $mulai = $open[$mid];
                    unset($open[$mid]);
                    $detik = abs($e->created_at->diffInSeconds($mulai));
                    if ($detik >= 5 && $detik < 12 * 3600) {
                        $durasi[$mid][] = $detik;
                    }
                }
            }

            $ringkas = function (array $arr) {
                $n = count($arr);
                if ($n === 0) {
                    return ['n' => 0, 'median_detik' => null, 'avg_detik' => null];
                }
                sort($arr);
                $mid = intdiv($n, 2);
                $median = $n % 2 ? $arr[$mid] : ($arr[$mid - 1] + $arr[$mid]) / 2;

                return [
                    'n' => $n,
                    'median_detik' => (int) round($median),
                    'avg_detik' => (int) round(array_sum($arr) / $n),
                ];
            };

            $perMesin = [];
            foreach ($durasi as $mid => $arr) {
                $perMesin[(int) $mid] = $ringkas($arr);
            }

            return [
                'per_mesin' => $perMesin,
                'global' => $ringkas(array_merge(...array_values($durasi) ?: [[]])),
            ];
        });
    }

    public function statsFor(int $monitoringMesinId): array
    {
        $s = $this->agentStats();
        $perMesin = $s['per_mesin'][$monitoringMesinId] ?? null;
        if ($perMesin && $perMesin['n'] > 0) {
            return $perMesin + ['basis' => 'mesin'];
        }

        return ($s['global'] ?? ['n' => 0, 'median_detik' => null, 'avg_detik' => null]) + ['basis' => 'global'];
    }

    public function forSession(int $monitoringMesinId, \Carbon\Carbon $mulai, ?int $pagesTotal = null, ?int $pagesDone = null, ?\Carbon\Carbon $progressAt = null): array
    {
        $basis = $this->statsFor($monitoringMesinId);
        $n = $basis['n'];

        $out = [
            'eta_selesai' => null,
            'eta_sisa_detik' => null,
            'eta_akurasi' => $n >= 10 ? 'tinggi' : ($n >= 3 ? 'sedang' : 'rendah'),
            'eta_sampel' => $n,
            'eta_basis' => $basis['basis'],
        ];

        // Jalur 1: progress counter (paling akurat).
        if ($pagesTotal && $pagesDone !== null && $pagesDone > 2 && $progressAt) {
            $elapsed = max(1, abs($progressAt->diffInSeconds($mulai)));
            $rate = $pagesDone / $elapsed;
            if ($rate > 0 && $pagesTotal > $pagesDone) {
                $sisa = (int) round(($pagesTotal - $pagesDone) / $rate);
                $out['eta_selesai'] = now()->addSeconds($sisa);
                $out['eta_sisa_detik'] = $sisa;

                return $out;
            }
        }

        // Jalur 2: median histori mesin (fallback global).
        if ($basis['median_detik'] !== null) {
            $selesai = $mulai->copy()->addSeconds($basis['median_detik']);
            $out['eta_selesai'] = $selesai;
            $out['eta_sisa_detik'] = now()->diffInSeconds($selesai, false);
        }

        return $out;
    }
}
