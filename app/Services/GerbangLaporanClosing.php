<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Gembok laporan closing.
 *
 * Permintaan owner (19 Nov 2026): riwayat & rekap closing TIDAK boleh dibuka
 * begitu saja dari akun kasir - untuk membukanya harus memasukkan password
 * akun Administrator. Riwayat closing memang memuat isi laci, selisih, dan
 * rekap harian yang menjadi dasar rekonsiliasi uang, jadi yang boleh melihat
 * adalah pemilik uang (Administrator) atau kasir yang diizinkan langsung.
 *
 * Bentuk penegakannya:
 *  - Administrator yang sedang login selalu lolos (tidak perlu mengetik
 *    password sendiri).
 *  - Selain itu, password akun Administrator yang AKTIF harus diketik di
 *    halaman gerbang. Setelah cocok, laporan terbuka selama MENIT menit
 *    untuk sesi login itu saja (session browser), lalu terkunci lagi.
 *
 * Hanya MEMBACA password hash untuk Hash::check; tidak ada password yang
 * disimpan, dicatat, atau dikembalikan ke tampilan.
 */
class GerbangLaporanClosing
{
    /** Kunci session - berisi timestamp kapan gembok terbuka kembali. */
    public const SESSION_KEY = 'laporan_closing_terbuka_sampai';

    /** Lama laporan tetap terbuka setelah password Administrator dimasukkan. */
    public const MENIT = 30;

    /**
     * Apakah laporan closing boleh dibuka pada request ini?
     */
    public function terbuka(): bool
    {
        $user = auth()->user();

        if ($user && str_starts_with((string) optional($user->role)->nama_role, 'Administrator')) {
            return true;
        }

        $sampai = session(self::SESSION_KEY);

        return is_numeric($sampai) && (int) $sampai > now()->timestamp;
    }

    /**
     * Buka gembok untuk sesi login ini selama MENIT menit.
     */
    public function buka(): void
    {
        session([self::SESSION_KEY => now()->addMinutes(self::MENIT)->timestamp]);
    }

    /**
     * Tutup kembali gembok (dipakai bila Administrator ingin langsung
     * mengunci tanpa menunggu kedaluwarsa).
     */
    public function kunci(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Cocokkan password dengan akun Administrator yang aktif.
     *
     * Password akun kasir sendiri TIDAK berlaku - yang dicari hanya role
     * Administrator. Beberapa akun Administrator dicek satu per satu
     * (jumlahnya kecil), tanpa membocorkan akun mana yang cocok.
     */
    public function passwordAdministratorCocok(string $password): bool
    {
        if (trim($password) === '') {
            return false;
        }

        $administrator = User::query()
            ->where('is_active', 1)
            ->whereHas('role', fn ($q) => $q->where('nama_role', 'like', 'Administrator%'))
            ->get(['id_user', 'password']);

        foreach ($administrator as $akun) {
            if (Hash::check($password, (string) $akun->password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parameter yang diteruskan dari halaman laporan/cetak ke halaman
     * gerbang, lalu dipakai lagi untuk kembali ke tujuan semula.
     *
     * Hanya parameter yang dikenal yang diteruskan - halaman gerbang tidak
     * boleh menjadi open redirect ke URL sembarang.
     *
     * @return array<string, int|string>
     */
    public function parameter(Request $request): array
    {
        $cetak = $request->query('cetak');
        if (is_numeric($cetak) && (int) $cetak > 0) {
            return [
                'cetak' => (int) $cetak,
                'format' => $request->query('format') === 'thermal' ? 'thermal' : 'a4',
            ];
        }

        $parameter = [];

        foreach (['tgl_dari', 'tgl_sampai'] as $kunci) {
            $nilai = $request->query($kunci);
            if (is_string($nilai) && $nilai !== '') {
                $parameter[$kunci] = $nilai;
            }
        }

        if (in_array($request->query('status_lock'), ['open', 'closed'], true)) {
            $parameter['status_lock'] = $request->query('status_lock');
        }

        if (is_numeric($request->query('page')) && (int) $request->query('page') > 0) {
            $parameter['page'] = (int) $request->query('page');
        }

        return $parameter;
    }

    /**
     * URL tujuan setelah gembok terbuka: Laporan Kas, atau cetak rekap satu
     * sesi bila parameter "cetak" ada.
     */
    public function tujuan(Request $request): string
    {
        $parameter = $this->parameter($request);

        if (isset($parameter['cetak'])) {
            return route('kasir.print-closing', [
                'id' => $parameter['cetak'],
                'format' => $parameter['format'],
            ]);
        }

        return route('laporan.kas', $parameter);
    }
}
