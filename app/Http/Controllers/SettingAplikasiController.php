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
}
