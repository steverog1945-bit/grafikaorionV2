<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoringMachine;
use App\Services\MonitoringCetakEtaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint agent spooler di PC produksi (prefix /api/monitoring-cetak/agent).
 * Semua route dilindungi middleware MonitoringCetakAgent (header X-Agent-Token)
 * DAN config monitoring_cetak.enabled (fail-closed sampai GO).
 */
class MonitoringCetakAgentController extends Controller
{
    public function hello(Request $request): JsonResponse
    {
        $agent = $request->attributes->get('agent');
        $agent->load('machine');

        return response()->json([
            'ok' => true,
            'agent' => $agent->nama,
            'machine' => $agent->machine?->nama_mesin,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Terima event cetak: {event, printer, doc_name, pages_total, pages_done,
     * bytes_total, bytes_done, detail}
     */
    public function event(Request $request): JsonResponse
    {
        /** @var \App\Models\MonitoringAgent $agent */
        $agent = $request->attributes->get('agent');

        $data = $request->validate([
            'event' => ['required', 'in:started,progress,completed,error'],
            'printer' => ['nullable', 'string', 'max:100'],
            'doc_name' => ['nullable', 'string', 'max:255'],
            'pages_total' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'pages_done' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'bytes_total' => ['nullable', 'integer', 'min:0'],
            'bytes_done' => ['nullable', 'integer', 'min:0'],
            'detail' => ['nullable', 'array'],
        ]);

        // Printer dari payload menentukan mesin penerima; bila tidak cocok
        // jatuh kembali ke mesin milik agent.
        $machine = null;
        if (! empty($data['printer'])) {
            $machine = MonitoringMachine::where('printer_name', $data['printer'])
                ->where('aktif', true)
                ->first();
        }
        $machine ??= $agent->machine;

        if (! $machine) {
            return response()->json(['message' => 'Mesin untuk agent ini tidak ditemukan/nonaktif.'], 422);
        }

        // Histori untuk ETA harus langsung memperhitungkan job yang baru selesai.
        if ($data['event'] === 'completed') {
            \Illuminate\Support\Facades\Cache::forget(MonitoringCetakEtaService::CACHE_KEY);
        }

        $event = \App\Models\MonitoringEvent::create([
            'monitoring_agent_id' => $agent->id,
            'monitoring_mesin_id' => $machine->id,
            'event' => $data['event'],
            'printer' => $data['printer'] ?? null,
            'doc_name' => $data['doc_name'] ?? null,
            'pages_total' => $data['pages_total'] ?? null,
            'pages_done' => $data['pages_done'] ?? null,
            'bytes_total' => $data['bytes_total'] ?? null,
            'bytes_done' => $data['bytes_done'] ?? null,
            'detail' => $data['detail'] ?? null,
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true, 'id' => $event->id]);
    }

    /** Status ringkas untuk verifikasi deploy/agent --check. */
    public function ping(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'time' => now()->toIso8601String()]);
    }
}
