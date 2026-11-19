<?php

/*
|--------------------------------------------------------------------------
| Monitoring Cetak (agent API)
|--------------------------------------------------------------------------
|
| Endpoint /api/monitoring-cetak/agent/* dipakai agent spooler di PC
| produksi. Selama belum ada GO dari operator + owner, endpoint ini
| fail-closed (503) walau token valid. Untuk mengaktifkan:
|
|     MONITORING_CETAK_AGENT_ENABLED=true
|
| Halaman web /monitoring-cetak tidak ikut flag ini (izinnya menu SPK).
*/

return [
    'enabled' => (bool) env('MONITORING_CETAK_AGENT_ENABLED', false),
];
