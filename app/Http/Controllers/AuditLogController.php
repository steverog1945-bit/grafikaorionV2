<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Halaman baca-saja untuk menelusuri audit_logs (lihat App\Services\
 * AuditLogger untuk penjelasan lengkap apa yang tercakup & tidak).
 * Sengaja tanpa create/update/delete - satu-satunya jalur tulis tabel ini
 * adalah AuditLogger::log(), bukan lewat controller ini.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', 'like', '%' . $request->auditable_type . '%');
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('awal')) {
            $query->whereDate('created_at', '>=', $request->awal);
        }
        if ($request->filled('akhir')) {
            $query->whereDate('created_at', '<=', $request->akhir);
        }

        $logs = $query->paginate(20)->withQueryString();

        $daftarTipe = AuditLog::select('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        return view('audit-log.index', compact('logs', 'daftarTipe'));
    }
}
