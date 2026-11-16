<?php

namespace Tests\Feature;

use App\Services\AccountingService;
use App\Services\KasSesiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Koreksi metode pembayaran nota (KoreksiPembayaranService) - void + input
 * ulang dengan metode benar dalam SATU transaksi. Pemicunya: uang TUNAI
 * yang diinput sebagai Transfer menjalar salah ke jurnal (Kas vs Bank),
 * saldo laci/total_transfer kas_harian, hitungan laci saat closing, rekap
 * metode di laporan, dan metode yang tercetak di nota.
 *
 * Yang dijaga di sini:
 *   - jurnal lama DIBALIK (bukan dihapus), jurnal baru diposting metode benar;
 *   - kas_harian: nominal pindah antara saldo_akhir (Tunai) dan total_transfer;
 *   - nominal, tgl_bayar, dan status pembayaran nota TIDAK berubah;
 *   - baris void tidak pernah dihitung lagi di pembacaan mana pun;
 *   - Deposit ditolak (menarik saldo nyata pelanggan), Transfer wajib bank;
 *   - sesi kas sudah ditutup -> lewat approval, bukan eksekusi langsung.
 */
class KoreksiMetodePembayaranTest extends TestCase
{
    use RefreshDatabase;

    private function nota(array $pembayaran = [], array $kasOverride = [], array $transaksiOverride = []): array
    {
        $admin = \App\Models\User::factory()->create([
            'id_role' => \App\Models\Role::factory()->create(['nama_role' => 'Administrator'])->id_role,
        ]);

        $g = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $k = DB::table('konsumen')->insertGetId([
            'nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $g,
            'id_unique' => (string) \Illuminate\Support\Str::uuid(),
        ]);
        $p = DB::table('produk')->insertGetId(['nama_produk' => 'Stiker Uji', 'metode_hitung' => 'quantity', 'status' => 'Aktif']);

        $t = DB::table('transaksi')->insertGetId(array_merge([
            'nomor_nota' => 'N-KOREKSI-1',
            'tipe_order' => 'Reguler',
            'id_konsumen' => $k,
            'id_user_kasir' => $admin->id_user,
            'tgl_order' => now(),
            'total_bayar' => 100000,
            'status_pembayaran' => 'DP',
            'status_nota' => 'Aktif',
            'status_order' => 'Diterima Kasir',
        ], $transaksiOverride));

        DB::table('transaksi_detail')->insert([
            'id_transaksi' => $t, 'id_produk' => $p, 'nama_file' => 'a',
            'p_ukuran' => 0, 'l_ukuran' => 0, 'qty' => 1,
            'biaya_cetak' => 100000, 'subtotal' => 100000, 'status_spk' => 'Antre',
        ]);

        $idKas = DB::table('kas_harian')->insertGetId(array_merge([
            'tgl_kas' => now()->toDateString(),
            'id_user' => $admin->id_user,
            'saldo_awal' => 0,
            'saldo_akhir' => 0,
            'total_transfer' => 0,
            'status_lock' => 'open',
        ], $kasOverride));

        $idBank = DB::table('bank_perusahaan')->insertGetId([
            'nama_bank' => 'BRI Uji', 'nomor_rekening' => '123456', 'atas_nama' => 'Toko Uji',
        ]);

        $ids = [];
        foreach ($pembayaran as $b) {
            // Pola insert sama dengan KasirController::prosesBayar: referensi
            // jurnal disiapkan dulu, disimpan di baris, lalu jurnalnya diposting.
            $referensi = $b['metode'] === 'Deposit' ? null : AccountingService::referensiPembayaran('N-KOREKSI-1');
            $idBaris = DB::table('pembayaran_riwayat')->insertGetId(array_merge([
                'id_transaksi' => $t,
                'nominal' => 60000,
                'metode' => 'Tunai',
                'tgl_bayar' => now(),
                'keterangan' => 'Pembayaran via Kasir',
                'no_referensi_jurnal' => $referensi,
                'id_kas' => $idKas,
            ], $b));
            $ids[] = $idBaris;
            if ($referensi) {
                AccountingService::postPembayaran('N-KOREKSI-1', (float) ($b['nominal'] ?? 60000), $b['metode'], $referensi);
            }
        }

        return [$admin, $t, $idKas, $idBank, $ids];
    }

    public function test_koreksi_tunai_ke_transfer_membalik_jurnal_memindahkan_kas_dan_mengganti_baris(): void
    {
        [$admin, $t, $idKas, $idBank, $ids] = $this->nota(
            [['nominal' => 60000, 'metode' => 'Tunai']],
            ['saldo_akhir' => 60000, 'total_transfer' => 0],
            ['status_pembayaran' => 'DP', 'metode_pembayaran' => 'Tunai']
        );

        // Kas awal: Dr Kas 60000 / Cr Piutang 60000.
        $this->assertSame(60000.0, (float) DB::table('jurnal_umum')->where('kode_akun', '1000')->where('no_referensi', 'like', 'N-KOREKSI-1-BYR-%')->sum('debet'));

        $this->actingAs($admin)
            ->post(route('kasir.bayar.koreksi-metode', $ids[0]), [
                'metode_pembayaran' => 'Transfer',
                'id_bank' => $idBank,
                'alasan' => 'Uang tunai salah diinput sebagai Transfer',
            ])
            ->assertSessionHasNoErrors();

        // Baris lama di-void, baris baru dengan metode benar menggantikannya.
        $lama = DB::table('pembayaran_riwayat')->where('id_pembayaran', $ids[0])->first();
        $this->assertNotNull($lama->voided_at, 'Baris lama harus di-void.');
        $this->assertSame($admin->id_user, (int) $lama->voided_by);

        $baru = DB::table('pembayaran_riwayat')->where('id_pembayaran', '!=', $ids[0])->where('id_transaksi', $t)->first();
        $this->assertNotNull($baru, 'Harus ada baris pengganti.');
        $this->assertSame('Transfer', $baru->metode);
        $this->assertSame($idBank, (int) $baru->id_bank);
        $this->assertSame('60000.00', (string) $baru->nominal, 'Nominal tidak boleh berubah.');
        $this->assertNotNull($baru->no_referensi_jurnal, 'Baris baru harus membawa referensi jurnalnya.');

        // Jurnal lama dibalik: kelompok referensi itu sekarang berisi baris
        // asli DAN pembalikannya (Dr Kas 60000 + Cr Kas 60000) = net nol.
        $refLama = $lama->no_referensi_jurnal;
        $this->assertSame(60000.0, (float) DB::table('jurnal_umum')->where('no_referensi', $refLama)->where('kode_akun', '1000')->sum('debet'));
        $this->assertSame(60000.0, (float) DB::table('jurnal_umum')->where('no_referensi', $refLama)->where('kode_akun', '1000')->sum('kredit'));
        $refBaru = $baru->no_referensi_jurnal;
        $this->assertNotSame($refLama, $refBaru, 'Referensi jurnal baru harus unik.');
        $this->assertSame(60000.0, (float) DB::table('jurnal_umum')->where('no_referensi', $refBaru)->where('kode_akun', '1010')->sum('debet'));
        $this->assertSame(60000.0, (float) DB::table('jurnal_umum')->where('no_referensi', $refBaru)->where('kode_akun', '1100')->sum('kredit'));

        // Kas sesi: tunai keluar dari saldo laci, masuk ke total_transfer.
        $kas = DB::table('kas_harian')->where('id_kas', $idKas)->first();
        $this->assertSame(0.0, (float) $kas->saldo_akhir);
        $this->assertSame(60000.0, (float) $kas->total_transfer);

        // Nominal tidak berubah -> status tetap; metode nota mengikuti baris aktif.
        $transaksi = DB::table('transaksi')->where('id_transaksi', $t)->first();
        $this->assertSame('Transfer', $transaksi->metode_pembayaran);
        $this->assertSame('DP', $transaksi->status_pembayaran);
    }

    public function test_baris_void_tidak_dihitung_lagi_di_saldo_laci_dan_status(): void
    {
        [$admin, $t, $idKas, $idBank, $ids] = $this->nota(
            [['nominal' => 60000, 'metode' => 'Tunai']],
            ['saldo_akhir' => 60000]
        );

        \App\Services\KoreksiPembayaranService::koreksiMetode($ids[0], 'Transfer', $idBank, 'Salah input metode');

        $kas = DB::table('kas_harian')->where('id_kas', $idKas)->first();
        $this->assertSame(0.0, KasSesiService::saldoLaci($kas), 'Laci tidak boleh menghitung baris yang sudah di-void.');
        // Nominal total tetap 60000 (baris pengganti sah), tapi tidak ada lagi
        // baris Tunai aktif - yang di-void tidak ikut dihitung di mana pun.
        $this->assertSame('Transfer', DB::table('pembayaran_riwayat')->where('id_transaksi', $t)->whereNull('voided_at')->value('metode'));
        $this->assertSame(0.0, (float) DB::table('pembayaran_riwayat')->where('id_transaksi', $t)->whereNull('voided_at')->where('metode', 'Tunai')->sum('nominal'));
    }

    public function test_baris_lama_tanpa_referensi_ditebak_dari_jurnal_yang_cocok(): void
    {
        [$admin, $t, $idKas, $idBank] = $this->nota([], ['saldo_akhir' => 60000]);

        // Baris lama pra-migrasi: tanpa no_referensi_jurnal, tapi jurnalnya ada.
        $referensi = AccountingService::referensiPembayaran('N-KOREKSI-1');
        AccountingService::postPembayaran('N-KOREKSI-1', 60000, 'Tunai', $referensi);
        $idBarisLama = DB::table('pembayaran_riwayat')->insertGetId([
            'id_transaksi' => $t, 'nominal' => 60000, 'metode' => 'Tunai', 'tgl_bayar' => now(),
            'id_kas' => $idKas,
        ]);

        \App\Services\KoreksiPembayaranService::koreksiMetode($idBarisLama, 'Transfer', $idBank, 'Data lama salah metode');

        $baru = DB::table('pembayaran_riwayat')->where('id_pembayaran', $idBarisLama)->first();
        $this->assertNotNull($baru->voided_at);
        $this->assertSame(60000.0, (float) DB::table('jurnal_umum')->where('no_referensi', '!=', $referensi)->where('kode_akun', '1010')->sum('debet'));
    }

    public function test_sesi_kas_sudah_tutup_menghasilkan_pengajuan_approval_bukan_eksekusi_langsung(): void
    {
        [$admin, $t, $idKas, $idBank, $ids] = $this->nota(
            [['nominal' => 60000, 'metode' => 'Tunai']],
            ['saldo_akhir' => 60000, 'status_lock' => 'closed']
        );

        $this->actingAs($admin)
            ->post(route('kasir.bayar.koreksi-metode', $ids[0]), [
                'metode_pembayaran' => 'Transfer',
                'id_bank' => $idBank,
                'alasan' => 'Koreksi sesi yang sudah ditutup',
            ])
            ->assertSessionHasNoErrors();

        // Tidak ada yang dieksekusi: baris masih utuh, kas tidak berubah.
        $lama = DB::table('pembayaran_riwayat')->where('id_pembayaran', $ids[0])->first();
        $this->assertNull($lama->voided_at);
        $this->assertSame(60000.0, (float) DB::table('kas_harian')->where('id_kas', $idKas)->value('saldo_akhir'));

        $pengajuan = DB::table('approval_requests')->where('tipe_aksi', 'koreksi_pembayaran_nota')->first();
        $this->assertNotNull($pengajuan, 'Harus tercipta pengajuan approval.');
        $this->assertSame('Menunggu', $pengajuan->status);
        $this->assertSame((string) $ids[0], (string) $pengajuan->referensi_id);
    }

    public function test_koreksi_yang_menyentuh_deposit_ditolak(): void
    {
        [$admin, $t, $idKas, $idBank, $ids] = $this->nota([['nominal' => 60000, 'metode' => 'Tunai']], ['saldo_akhir' => 60000]);

        $this->actingAs($admin)
            ->post(route('kasir.bayar.koreksi-metode', $ids[0]), [
                'metode_pembayaran' => 'Deposit',
                'alasan' => 'Coba koreksi ke deposit',
            ])
            ->assertSessionHasErrors();

        $this->assertNull(DB::table('pembayaran_riwayat')->where('id_pembayaran', $ids[0])->value('voided_at'));
    }

    public function test_transfer_wajib_memilih_bank(): void
    {
        [$admin, $t, $idKas, $idBank, $ids] = $this->nota([['nominal' => 60000, 'metode' => 'Tunai']], ['saldo_akhir' => 60000]);

        $this->actingAs($admin)
            ->post(route('kasir.bayar.koreksi-metode', $ids[0]), [
                'metode_pembayaran' => 'Transfer',
                'alasan' => 'Tanpa memilih rekening bank',
            ])
            ->assertSessionHas('error');

        $this->assertNull(DB::table('pembayaran_riwayat')->where('id_pembayaran', $ids[0])->value('voided_at'));
    }
}
