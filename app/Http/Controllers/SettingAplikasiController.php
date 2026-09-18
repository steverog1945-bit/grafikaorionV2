<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingAplikasiController extends Controller
{
    public function edit()
    {
        $setting = AppSetting::current();
        return view('pengaturan.aplikasi', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_aplikasi' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:1000',
            'telepon' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'libur_kas_minggu' => 'nullable|boolean',
            'format_nota' => 'nullable|in:thermal,a4,continuous',
            'nomor_nota_prefix' => 'nullable|string|max:20|regex:/^[A-Za-z0-9\-]+$/',
            'nota_footer_text' => 'nullable|string|max:5000',
        ]);

        $setting = AppSetting::current();

        $updateData = [
            'nama_aplikasi' => $data['nama_aplikasi'],
            'alamat' => $data['alamat'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'fax' => $data['fax'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'libur_kas_minggu' => $request->boolean('libur_kas_minggu'),
            'format_nota' => $data['format_nota'] ?? 'a4',
            'nomor_nota_prefix' => $data['nomor_nota_prefix'] ?? 'INV',
            'nota_footer_text' => $this->sanitizeFooterHtml($data['nota_footer_text'] ?? null),
        ];

        if ($request->hasFile('logo')) {
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $updateData['logo_path'] = $request->file('logo')->store('company_logo', 'public');
        }

        $setting->update($updateData);
        AppSetting::clearCache();

        return back()->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }

    /**
     * Footer nota diketik lewat editor contenteditable (toolbar tebal/
     * miring/garis-bawah/daftar) di sisi klien, hasilnya HTML mentah
     * (element.innerHTML) - WAJIB disaring di server sebelum disimpan,
     * karena nilai ini nantinya dicetak balik sebagai HTML MENTAH
     * (bukan di-escape) di kasir.print_nota. Dua lapis:
     * 1. strip_tags dengan whitelist SANGAT ketat (cuma tag pembentuk
     *    format dasar, tidak ada <a>/<img>/<script>/dst).
     * 2. Buang SEMUA atribut dari tag yang tersisa (termasuk onclick=,
     *    style=, href=javascript:) - formatting dasar tidak butuh atribut
     *    apa pun, jadi paling aman dihapus total daripada whitelist per-
     *    atribut yang gampang bocor.
     */
    private function sanitizeFooterHtml(?string $html): ?string
    {
        if (!$html || trim(strip_tags($html)) === '') {
            return null;
        }

        $allowedTags = '<b><strong><i><em><u><br><p><div><ul><ol><li>';
        $bersih = strip_tags($html, $allowedTags);
        $bersih = preg_replace('/<([a-z][a-z0-9]*)[^>]*>/i', '<$1>', $bersih);

        return trim($bersih);
    }
}
