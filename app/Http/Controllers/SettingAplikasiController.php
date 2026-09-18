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
     * Preview LIVE nota (dipakai lewat <iframe> di tab Nota Pengaturan
     * Aplikasi) - merender view kasir.print_nota YANG SAMA PERSIS dengan
     * yang dipakai cetak sungguhan (bukan mockup terpisah yang harus
     * dijaga manual biar tetap sinkron - WYSIWYG asli, tidak mungkin
     * "lupa" diperbarui kalau print_nota diubah lagi nanti).
     *
     * format/prefix/footer diambil dari QUERY STRING (dikirim JS setiap
     * field di form Nota berubah) supaya preview reflect perubahan yang
     * BELUM disimpan - fallback ke AppSetting::current() kalau query
     * kosong (preview awal sebelum ada perubahan apa pun). Footer dari
     * query tetap disaring lewat sanitizeFooterHtml() yang sama seperti
     * saat disimpan sungguhan - endpoint ini publik-ke-user-login, bukan
     * alasan buat melewati sanitasi.
     *
     * Data transaksi & item SELALU contoh statis (bukan dari database) -
     * endpoint pratinjau tidak boleh membocorkan data transaksi/konsumen
     * asli, dan supaya preview selalu konsisten apa pun isi database saat
     * ini. Satu nama item sengaja dibuat sangat panjang untuk
     * memperlihatkan potongan ellipsis-nya juga.
     */
    public function previewNota(Request $request)
    {
        $setting = AppSetting::current();

        $format = $request->query('format');
        $format = in_array($format, ['thermal', 'a4', 'continuous'], true) ? $format : ($setting->format_nota ?? 'a4');

        $prefix = $request->query('prefix');
        $prefix = ($prefix && preg_match('/^[A-Za-z0-9\-]+$/', $prefix)) ? $prefix : ($setting->nomor_nota_prefix ?? 'INV');

        $footerQuery = $request->query('footer');
        $footerNota = $footerQuery !== null ? $this->sanitizeFooterHtml($footerQuery) : $setting->nota_footer_text;

        $transaksi = (object) [
            'nomor_nota' => $prefix . '-' . now()->format('Ymd') . '-001',
            'tgl_order' => now(),
            'nama_konsumen' => 'Budi Santoso (Contoh)',
            'telepon' => '0812xxxxxxx',
            'nama_kasir' => 'Contoh Kasir',
            'total_bayar' => 200000,
            'status_pembayaran' => 'Lunas',
            'id_konsumen' => 0,
        ];
        $items = collect([
            (object) ['nama_file' => 'Banner Outdoor 3x1m Custom Full Desain Logo Perusahaan', 'qty' => 2, 'subtotal' => 150000, 'biaya_cetak' => 150000, 'biaya_desain' => 0, 'biaya_finishing' => 0, 'p_ukuran' => 300, 'l_ukuran' => 100],
            (object) ['nama_file' => 'Stiker A3', 'qty' => 5, 'subtotal' => 50000, 'biaya_cetak' => 40000, 'biaya_desain' => 0, 'biaya_finishing' => 10000, 'p_ukuran' => 0, 'l_ukuran' => 0],
        ]);

        return view('kasir.print_nota', [
            'transaksi' => $transaksi,
            'items' => $items,
            'totalTerbayar' => 200000,
            'format' => $format,
            'totalCetak' => 190000,
            'totalDesain' => 0,
            'totalFinishing' => 10000,
            'kembali' => 0,
            'kurangBayar' => 0,
            'depositTerpakaiTransaksiIni' => 0,
            'saldoDepositSaatIni' => 0,
            'konsumenPernahPakaiDeposit' => false,
            'footerNotaOverride' => $footerNota,
        ]);
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
