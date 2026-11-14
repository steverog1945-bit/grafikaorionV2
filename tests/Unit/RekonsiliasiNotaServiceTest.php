<?php

namespace Tests\Unit;

use App\Services\AiNotaAuditService;
use App\Services\RekonsiliasiNotaService;
use PHPUnit\Framework\TestCase;

/**
 * Unit test fungsi murni rekonsiliasi & parser jawaban AI.
 *
 * TIDAK menyentuh database: kamus dikirim eksplisit (sama bentuknya
 * dengan hasil RekonsiliasiNotaService::kamus()), dan parsingJawaban()
 * (private) dipanggil lewat reflection. Fungsi berbasis DB (perNota,
 * detailTertagih, kamus) diuji terpisah lewat harness CLI - lihat
 * riwayat verifikasi di dokumentasi/laporan.
 */
class RekonsiliasiNotaServiceTest extends TestCase
{
    /** Kamus sintetis: alias => id_finishing + id_kandidat (bentuk dikembalikan kamus()). */
    private function kamus(): array
    {
        return [
            ['alias' => 'laminasi', 'nama_finishing' => 'Laminasi', 'id_finishing' => 30, 'id_kandidat' => [30]],
            ['alias' => 'laminasi glossy', 'nama_finishing' => 'Laminasi Glossy', 'id_finishing' => 31, 'id_kandidat' => [31]],
            ['alias' => 'potong', 'nama_finishing' => 'Potong Manual', 'id_finishing' => 15, 'id_kandidat' => [15]],
            ['alias' => 'porporasi', 'nama_finishing' => 'Porporasi', 'id_finishing' => 40, 'id_kandidat' => [40]],
            ['alias' => 'keping roll', 'nama_finishing' => 'Keping Roll', 'id_finishing' => 50, 'id_kandidat' => [50]],
        ];
    }

    /** @return array<int, array{id_finishing:int, nama_finishing:string, alias:string, kutipan:string}> */
    private function temuan(string $teks, array $tertagih): array
    {
        return RekonsiliasiNotaService::temuan($teks, $tertagih, $this->kamus());
    }

    public function test_normalisasi_membuang_sisa_non_alfanumerik(): void
    {
        $this->assertSame('laminasi glossy 2', RekonsiliasiNotaService::normalisasi('Laminasi-Glossy (2)'));
        $this->assertSame('a 150 cm', RekonsiliasiNotaService::normalisasi('  A 150 CM!! '));
        $this->assertSame('', RekonsiliasiNotaService::normalisasi('!!!'));
    }

    public function test_teks_kosong_tidak_ada_temuan(): void
    {
        $this->assertSame([], $this->temuan('', []));
        $this->assertSame([], $this->temuan('/// --- ...', []));
    }

    public function test_sebutan_yang_tidak_tertagih_ditemukan(): void
    {
        $hasil = $this->temuan('banner 150x40 potong', []);

        $this->assertCount(1, $hasil);
        $this->assertSame('potong', $hasil[0]['alias']);
        $this->assertSame('Potong Manual', $hasil[0]['nama_finishing']);
        $this->assertStringContainsString('potong', $hasil[0]['kutipan']);
    }

    public function test_sebutan_yang_tertagih_bukan_temuan(): void
    {
        $this->assertSame([], $this->temuan('banner 150x40 potong', [15]));
    }

    public function test_sebutan_ganda_hanya_ditandai_sekali(): void
    {
        $hasil = $this->temuan('potong banner potong lagi potong', []);

        $this->assertCount(1, $hasil);
        $this->assertSame('potong', $hasil[0]['alias']);
    }

    public function test_negasi_tidak_dihitung(): void
    {
        $this->assertSame([], $this->temuan('banner tanpa laminasi', []));
        $this->assertSame([], $this->temuan('banner TANPA Laminasi mengkilap', []));
        $this->assertSame([], $this->temuan('jangan laminasi area tulis', []));
        // negasi berlaku untuk kata yang menempel SEBELUM sebutan saja
        $hasil = $this->temuan('banner tanpa laminasi lalu potong', []);
        $this->assertCount(1, $hasil);
        $this->assertSame('potong', $hasil[0]['alias']);
    }

    public function test_sebutan_cekat_di_dalam_kata_lain_tidak_cocok(): void
    {
        $this->assertSame([], $this->temuan('nonlaminasi cetak digital', []));
        $this->assertSame([], $this->temuan('barang dipotong rapi', []));
    }

    public function test_subsumsi_alias_generik_dibuang_bila_spesifik_juga_disebut(): void
    {
        // Teks menyebut "laminasi glossy": "laminasi" (generik) adalah bagian
        // kata dari alias spesifik yang ikut cocok - jangan tanda dua kali.
        $hasil = $this->temuan('spanduk laminasi glossy', []);

        $alias = array_column($hasil, 'alias');
        $this->assertContains('laminasi glossy', $alias);
        $this->assertNotContains('laminasi', $alias);
    }

    public function test_tertagih_spesifik_menutup_generik(): void
    {
        // "Laminasi Glossy" tertagih di nota: sebutan "laminasi glossy" tidak
        // perlu ditandai, dan "laminasi" polos ikut tertutup penjaga TERGABUNG.
        $this->assertSame([], $this->temuan('spanduk laminasi glossy', [31]));
    }

    public function test_tertagih_generik_tidak_menutup_spesifik(): void
    {
        // Nota hanya menagih "Laminasi" (generik) padahal teks menyebut
        // "laminasi glossy" (spesifik) - spesifiknya tetap perlu ditinjau.
        $hasil = $this->temuan('spanduk laminasi glossy', [30]);

        $alias = array_column($hasil, 'alias');
        $this->assertContains('laminasi glossy', $alias);
    }

    public function test_kutipan_memuat_konteks_sebutan(): void
    {
        $hasil = $this->temuan('spanduk promosi besar porporasi logo sudut', []);

        $this->assertCount(1, $hasil);
        $this->assertStringContainsString('porporasi', $hasil[0]['kutipan']);
        // kutipan dibatasi (konteks ~25 karakter tiap sisi + kata sebutan)
        $this->assertLessThanOrEqual(80, mb_strlen($hasil[0]['kutipan']));
    }

    /* ---------- AiNotaAuditService::parsingJawaban (private, via reflection) ---------- */

    /** @return array<int, array<string, mixed>>|null */
    private function parsingJawaban(string $json): ?array
    {
        $method = new \ReflectionMethod(AiNotaAuditService::class, 'parsingJawaban');
        $method->setAccessible(true);

        return $method->invoke(null, $json);
    }

    public function test_parser_menolak_bukan_json(): void
    {
        $this->assertNull($this->parsingJawaban('bukan json sama sekali'));
        $this->assertNull($this->parsingJawaban('{"temuan": "bukan array"}'));
        $this->assertNull($this->parsingJawaban('{"lain": []}'));
    }

    public function test_parser_menata_placeholder_dari_sebutan(): void
    {
        // Kasus nyata qwen2.5:3b pada nota 236: temuan "...porporasi..." dan
        // kutipan "..." harus dibangun ulang dari sebutan hasil ekstraksinya.
        $jawaban = json_encode([
            'temuan' => [
                [
                    'temuan' => '...porporasi...',
                    'kutipan' => '...',
                    'sumber' => 'Nama file item',
                    'kategori' => 'lainnya',
                    'keyakinian' => 0.8,
                ],
            ],
            'sebutan' => [
                ['frasa' => 'porporasi', 'asal' => 'nama file item', 'tertagih' => false],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $hasil = $this->parsingJawaban($jawaban);

        $this->assertCount(1, $hasil);
        $this->assertSame('porporasi', $hasil[0]['kutipan']);
        $this->assertStringContainsString('porporasi', $hasil[0]['temuan']);
        $this->assertStringContainsString('tidak ada di finishing tertagih', $hasil[0]['temuan']);
        $this->assertSame('finishing_tidak_tertagih', $hasil[0]['kategori']);
        $this->assertSame(0.8, $hasil[0]['keyakinian']);
    }

    public function test_parser_mempertahankan_temuan_utuh(): void
    {
        $kalimat = 'Teks menyebut porporasi di berkas namun tidak ada item finishing porporasi tertagih di nota ini';
        $jawaban = json_encode([
            'temuan' => [
                [
                    'temuan' => $kalimat,
                    'kutipan' => 'porporasi berkas desain',
                    'sumber' => 'catatan produksi',
                    'kategori' => 'finishing_tidak_tertagih',
                    'keyakinian' => 0.9,
                ],
            ],
            'sebutan' => [['frasa' => 'porporasi', 'asal' => 'catatan', 'tertagih' => false]],
        ], JSON_UNESCAPED_UNICODE);

        $hasil = $this->parsingJawaban($jawaban);

        $this->assertSame($kalimat, $hasil[0]['temuan']);
        $this->assertSame('porporasi berkas desain', $hasil[0]['kutipan']);
        $this->assertSame('finishing_tidak_tertagih', $hasil[0]['kategori']);
    }

    public function test_parser_membatasi_keyakinian_dan_kategori(): void
    {
        $jawaban = json_encode([
            'temuan' => [
                [
                    'temuan' => 'Temuan cukup panjang untuk tidak dianggap lemah sama sekali karena ada kata tidak di dalamnya',
                    'kutipan' => 'kutipan cukup panjang',
                    'sumber' => 'teks bebas',
                    'kategori' => 'ngawur',
                    'keyakinian' => 1.7,
                ],
            ],
        ]);

        $hasil = $this->parsingJawaban($jawaban);

        $this->assertEquals(1.0, $hasil[0]['keyakinian']);
        $this->assertSame('lainnya', $hasil[0]['kategori']);
    }

    public function test_parser_membuang_temuan_kosong(): void
    {
        $jawaban = json_encode([
            'temuan' => [
                ['temuan' => '   ', 'kutipan' => 'x', 'kategori' => 'lainnya', 'keyakinian' => 0.5],
                ['temuan' => 'Ada temuan nyata: pekerjaan tidak tertagih pada item ini seperti disebutkan', 'kutipan' => 'kutipan', 'kategori' => 'lainnya', 'keyakinian' => 0.5],
            ],
        ]);

        $hasil = $this->parsingJawaban($jawaban);

        $this->assertCount(1, $hasil);
    }
}
