<?php

namespace App\Http\Controllers;

use App\Models\TopbarTab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan Topbar Premium (baris kedua): tab modul bisa diatur
 * Administrator - label, URL, ikon, urutan, aktif/sembunyi.
 *
 * Hanya satu form satu tombol Simpan (pola yang sama dengan Pengaturan
 * Aplikasi). Validasi ketat pada href & kelas ikon karena keduanya
 * disuntik langsung ke atribut HTML di topbar (cegah injeksi markup).
 */
class PengaturanTopbarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdministrator($request);

        $tabs = Schema::hasTable('topbar_tabs')
            ? DB::table('topbar_tabs')->orderBy('urutan')->orderBy('id')->get()
            : collect();

        return view('pengaturan.topbar', compact('tabs'));
    }

    public function update(Request $request)
    {
        $this->authorizeAdministrator($request);

        if (! Schema::hasTable('topbar_tabs')) {
            return back()->with('error', 'Tabel topbar_tabs belum dimigrasi. Jalankan "php artisan migrate --force" terlebih dahulu.');
        }

        $data = $request->validate([
            'rows' => 'required|array|min:1|max:24',
            'rows.*.id' => 'nullable|integer|exists:topbar_tabs,id',
            'rows.*.label' => ['required', 'string', 'min:1', 'max:40'],
            'rows.*.url' => ['required', 'string', 'max:191', 'regex:#^/[A-Za-z0-9\-/]*$#'],
            'rows.*.icon' => ['required', 'string', 'max:64', 'regex:#^fa-[a-z0-9-]+$#'],
            'rows.*.urutan' => 'required|integer|min:0|max:9999',
            'rows.*.aktif' => 'nullable|boolean',
        ], [
            'rows.*.url.regex' => 'URL harus berupa path relatif yang bersih (mis. /produksi, tanpa domain, tanpa spasi).',
            'rows.*.icon.regex' => 'Ikon harus kelas Font Awesome valid (mis. fa-gauge).',
        ]);

        $idsDipost = collect($data['rows'])->pluck('id')->filter()->map(fn ($i) => (int) $i)->all();

        DB::transaction(function () use ($data, $idsDipost) {
            // Baris yang hilang dari POST = dihapus (dianggap "dihapus oleh admin").
            DB::table('topbar_tabs')->whereNotIn('id', $idsDipost)->delete();

            foreach ($data['rows'] as $i => $row) {
                $payload = [
                    'label' => $row['label'],
                    'url' => $row['url'],
                    'icon' => $row['icon'],
                    'urutan' => (int) $row['urutan'],
                    'aktif' => ! empty($row['aktif']),
                    'updated_at' => now(),
                ];
                if (! empty($row['id'])) {
                    DB::table('topbar_tabs')->where('id', (int) $row['id'])->update($payload);
                } else {
                    $payload['created_at'] = now();
                    DB::table('topbar_tabs')->insert($payload);
                }
            }
        });

        TopbarTab::bersihkanCache();

        return back()->with('success', 'Topbar Premium berhasil disimpan.');
    }

    private function authorizeAdministrator(Request $request): void
    {
        $roleName = (string) (optional($request->user()?->role)->nama_role ?? '');
        abort_unless(str_starts_with($roleName, 'Administrator'), 403, 'Hanya Administrator yang boleh mengatur topbar.');
    }
}
