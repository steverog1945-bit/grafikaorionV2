<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

/**
 * LAPIS AI di atas rekonsiliasi deterministik (RekonsiliasiNotaService).
 *
 * Deterministik menangkap sebutan harfiah di kamus; AI menangkap kalimat
 * bebas yang tidak ada di kamus ("blur fotonya dulu sebelum cetak",
 * "printed side yang glossy", istilah campur, dsb) dan membandingkannya
 * dengan FAKTA NOTA yang terstruktur.
 *
 * Aturan main:
 * - Driver dikonfigurasi di config/services.php (ai_nota). KOSONG = mati
 *   total: method apa pun mengembalikan null/[] tanpa efek samping.
 * - HANYA dipanggil dari command `nota:audit-ai` (manual/terjadwal),
 *   bukan dari request halaman - LLM boleh lambat, halaman tidak boleh.
 * - Hasil DISIMPAN di `nota_audit_ai` (replace per nota), lalu tampil
 *   advisory di halaman Berkas Order. Tidak pernah memblokir transaksi.
 * - AI diberi fakta nota terstruktur sebagai SATU-SATUNYA sumber kebenaran
 *   dan dilarang mengarang; respons dikunci JSON (format:"json" Ollama).
 */
class AiNotaAuditService
{
    public const KATEGORI = ['finishing_tidak_tertagih', 'deskripsi_tak_cocok', 'ukuran_lain', 'lainnya'];

    /** @return array{driver:?string, url:string, model:string, timeout:int} */
    public static function konfig(): array
    {
        $c = config('services.ai_nota', []);
        return [
            'driver' => $c['driver'] ?? null,
            'url' => rtrim((string) ($c['url'] ?? ''), '/'),
            'model' => (string) ($c['model'] ?? ''),
            'timeout' => max(10, (int) ($c['timeout'] ?? 120)),
        ];
    }

    public static function aktif(): bool
    {
        return self::konfig()['driver'] !== null && self::konfig()['driver'] !== '';
    }

    /**
     * Fakta nota untuk prompt: terstruktur, minimal PII (tanpa no. HP,
     * tanpa alamat) supaya aman bila kelak driver-nya cloud.
     *
     * @return array<string, mixed>|null null bila nota tidak ada/kosong item
     */
    public static function faktaNota(int $idTransaksi): ?array
    {
        $nota = DB::table('transaksi')->where('id_transaksi', $idTransaksi)->first(['id_transaksi', 'nomor_nota', 'tgl_order', 'tipe_order']);
        if (!$nota) {
            return null;
        }

        $detail = DB::table('transaksi_detail')->where('id_transaksi', $idTransaksi)->get();
        if ($detail->isEmpty()) {
            return null;
        }

        $tertagih = RekonsiliasiNotaService::detailTertagih($idTransaksi);
        $namaFinishing = [];
        foreach (DB::table('finishing')->get(['id_finishing', 'nama_finishing']) as $f) {
            $namaFinishing[(int) $f->id_finishing] = $f->nama_finishing;
        }

        $items = [];
        foreach ($detail as $d) {
            $items[] = [
                'nama_file' => $d->nama_file,
                'qty' => (float) $d->qty,
                'ukuran_cm' => ((float) $d->p_ukuran > 0 && (float) $d->l_ukuran > 0) ? $d->p_ukuran . 'x' . $d->l_ukuran : null,
                'catatan_produksi' => $d->catatan_produksi,
                'finishing_tertagih' => array_map(fn ($id) => $namaFinishing[$id] ?? '#' . $id, $tertagih[(int) $d->id_detail] ?? []),
            ];
        }

        $berkas = DB::table('order_berkas')->where('id_transaksi', $idTransaksi)
            ->get(['kategori', 'nama_file_asli', 'catatan', 'id_detail'])
            ->map(fn ($b) => [
                'kategori' => $b->kategori,
                'nama_file' => $b->nama_file_asli,
                'catatan' => $b->catatan,
                'item' => $b->id_detail ? 'item #' . $b->id_detail : 'seluruh nota',
            ])->all();

        return [
            'nota' => $nota->nomor_nota . ' (' . $nota->tgl_order . ', ' . $nota->tipe_order . ')',
            'item_nota' => $items,
            'berkas_operator' => $berkas,
            // katalog jasa finishing yang sah - acuan model 3B kecil yang
            // tidak tahu sendiri bahwa "porporasi"/"kisscut" itu jasa finishing
            'daftar_finishing_master' => array_values(array_unique($namaFinishing)),
        ];
    }

    /** Prompt pengaudit. AI boleh menyebut hanya yang ADA di fakta. */
    public static function prompt(array $fakta): string
    {
        return <<<'PROMPT'
Anda pemeriksa nota percetakan. FAKTA_NOTA di bawah adalah data sistem yang BENAR.
TEKS BEBAS di dalamnya (nama_file, catatan_produksi, catatan berkas operator) ditulis
manusia dan bisa menyebut pekerjaan yang LUPA dimasukkan ke nota.

Tugas: cari sebutan pekerjaan/bahan/finishing di TEKS BEBAS yang TIDAK tercakup
oleh kolom terstruktur nota (finishing_tertagih, item lain, dsb).
Aturan:
- BANDINGKAN teks bebas dengan daftar_finishing_master: kata/frasa di teks
  yang mirip salah satu entri daftar (lapis, potong, dsb boleh berbeda ejaan)
  tapi TIDAK ada di finishing_tertagih item itu = TEMUAN WAJIB dilaporkan.
- Abaikan negasi ("tanpa X", "non X", "jangan X").
- Abaikan sebutan yang benar-benar sudah tercakup (jangan dobel).
- JANGAN mengarang. Hanya gunakan isi FAKTA_NOTA. Kutipan = potongan teks
  persis (maks ~60 karakter).
- kategori salah satu: finishing_tidak_tertagih | deskripsi_tak_cocok | ukuran_lain | lainnya
- keyakinian angka 0.0-1.0.

Langkah wajib sebelum menutup jawaban (kerjakan dalam hati, jangan dilewati):
1. Baca SETIAP teks bebas (nama_file, catatan_produksi, catatan berkas operator).
2. Untuk setiap item, bandingkan isi teks bebas dengan daftar_finishing_master
   dan dengan finishing_tertagih item itu.
3. Sebutan yang mirip entri daftar_finishing_master tapi tidak tertagih = temuan.

FAKTA_NOTA (JSON):
{fakta}

Jawab HANYA JSON tunggal tanpa penjelasan. Field "sebutan" WAJIB diisi:
daftar setiap frasa pekerjaan/finishing yang Anda temukan di teks bebas beserta
statusnya; bila memang tidak ada sebutan sama sekali baru boleh array kosong.
{"sebutan":[{"frasa":"porporasi","asal":"nama_file item 1","tertagih":false}],"temuan":[{"sumber":"nama teks asal","kutipan":"...","temuan":"...","kategori":"...","keyakinian":0.8}]}
Setiap entri "sebutan" dengan tertagih=false HARUS punya pasangan di "temuan".
PROMPT;
    }

    /**
     * Audit satu nota: kirim prompt ke driver, simpan hasil ke tabel
     * (replace baris nota itu), kembalikan temuan hasil simpan.
     *
     * @return array{temuan:array<int,array>, model:string}|null null bila driver mati/fakta kosong/gagal jaringan
     */
    public static function audit(int $idTransaksi): ?array
    {
        $konfig = self::konfig();
        if (!$konfig['driver']) {
            return null;
        }

        $fakta = self::faktaNota($idTransaksi);
        if ($fakta === null) {
            return null;
        }

        $prompt = str_replace('{fakta}', json_encode($fakta, JSON_UNESCAPED_UNICODE), self::prompt($fakta));
        $jawaban = self::kirim($prompt, $konfig);
        if ($jawaban === null) {
            return null;
        }

        $temuan = self::parsingJawaban($jawaban);
        if ($temuan === null) {
            return null;
        }

        // Replace: tabel ini = hasil pemeriksaan TERAKHIR per nota.
        DB::transaction(function () use ($idTransaksi, $temuan, $konfig) {
            DB::table('nota_audit_ai')->where('id_transaksi', $idTransaksi)->delete();
            $now = now();
            foreach ($temuan as $t) {
                DB::table('nota_audit_ai')->insert([
                    'id_transaksi' => $idTransaksi,
                    'sumber' => mb_substr((string) $t['sumber'], 0, 255),
                    'kutipan' => isset($t['kutipan']) ? mb_substr((string) $t['kutipan'], 0, 255) : null,
                    'temuan' => mb_substr((string) $t['temuan'], 0, 255),
                    'kategori' => in_array($t['kategori'] ?? null, self::KATEGORI, true) ? $t['kategori'] : 'lainnya',
                    'keyakinian' => min(1, max(0, (float) ($t['keyakinian'] ?? 0))),
                    'model' => mb_substr($konfig['model'], 0, 60),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        $hasil = DB::table('nota_audit_ai')->where('id_transaksi', $idTransaksi)->get()->map(fn ($r) => (array) $r)->all();
        return ['temuan' => $hasil, 'model' => $konfig['model']];
    }

    /** Kirim prompt ke driver yang dipilih. null = gagal. */
    private static function kirim(string $prompt, array $konfig): ?string
    {
        try {
            if ($konfig['driver'] === 'ollama') {
                $res = Http::timeout($konfig['timeout'])->post($konfig['url'] . '/api/generate', [
                    'model' => $konfig['model'],
                    'prompt' => $prompt,
                    'format' => 'json',
                    'stream' => false,
                    'options' => ['temperature' => 0.1], // pengaudit: jawaban konsisten, bukan kreatif
                ]);
                if (!$res->successful()) {
                    return null;
                }
                return (string) ($res->json('response') ?? '');
            }

            return null; // driver tak dikenal = mati
        } catch (\Throwable) {
            return null; // jaringan/timeout: advisory tidak boleh meledakkan command
        }
    }

    /**
     * Parsing ketat jawaban JSON: struktur salah / temuan bukan array /
     * item tidak punya "temuan" = ditolak (null), bukan setengah dipercaya.
     */
    private static function parsingJawaban(string $jawaban): ?array
    {
        $data = json_decode(trim($jawaban), true);
        if (!is_array($data) || !isset($data['temuan']) || !is_array($data['temuan'])) {
            return null;
        }

        // Model kecil sering mengisi temuan dengan placeholder ("...", kutipan
        // kosong). Data "sebutan" hasil ekstraksinya lebih tepercaya - pakai
        // itu untuk membangun ulang teks temuan yang lemah.
        $sebutan = is_array($data['sebutan'] ?? null) ? $data['sebutan'] : [];

        $bersih = [];
        foreach ($data['temuan'] as $t) {
            if (!is_array($t) || trim((string) ($t['temuan'] ?? '')) === '') {
                continue;
            }
            $t['sumber'] = trim((string) ($t['sumber'] ?? 'teks bebas'));
            $t['kutipan'] = trim((string) ($t['kutipan'] ?? ''));
            $t['temuan'] = trim((string) $t['temuan']);
            $t['kategori'] = in_array($t['kategori'] ?? null, self::KATEGORI, true) ? $t['kategori'] : 'lainnya';
            $t['keyakinian'] = min(1, max(0, (float) ($t['keyakinian'] ?? 0)));

            // "lemah" = bukan kalimat temuan utuh: kosong, atau pendek dan
            // tidak memuat kata kunci penjelasan; placeholder seperti
            // "...porporasi..." ikut tertangkap setelah titik dibuang.
            $inti = trim($t['temuan'], " \t\n\r\0\x0B.…");
            $lemah = $inti === '' || (mb_strlen($inti) < 40 && !preg_match('/tidak|tetapi|lupa|belum|missing/iu', $inti));
            $kutipanInti = trim($t['kutipan'], " \t\n\r\0\x0B.…");
            $kutipanLemah = $kutipanInti === '' || mb_strlen($kutipanInti) < 6;
            if (($lemah || $kutipanLemah) && $sebutan !== []) {
                foreach ($sebutan as $s) {
                    $frasa = trim((string) ($s['frasa'] ?? ''));
                    if ($frasa === '') {
                        continue;
                    }
                    if (mb_stripos($t['temuan'] . $t['kutipan'] . $t['sumber'], $frasa) !== false) {
                        if ($kutipanLemah) {
                            $t['kutipan'] = $frasa;
                        }
                        if ($lemah) {
                            $t['temuan'] = sprintf(
                                'Teks menyebut "%s" (%s) tetapi tidak ada di finishing tertagih nota',
                                $frasa,
                                trim((string) ($s['asal'] ?? 'teks bebas')) ?: 'teks bebas'
                            );
                            if ($t['kategori'] === 'lainnya' && ($s['tertagih'] ?? false) === false) {
                                $t['kategori'] = 'finishing_tidak_tertagih';
                            }
                        }
                        break;
                    }
                }
            }
            if ($t['kutipan'] === '' && mb_strlen($t['temuan']) >= 12) {
                $t['kutipan'] = mb_substr($t['temuan'], 0, 60);
            } elseif ($t['kutipan'] !== '' && $kutipanInti !== '' && $t['kutipan'] !== $kutipanInti) {
                $t['kutipan'] = $kutipanInti; // buang pembungkus titik/elipsis
            }
            $bersih[] = $t;
        }
        return $bersih;
    }

    /** Temuan AI yang SUDAH tersimpan untuk satu nota (untuk tampilan). */
    public static function tersimpan(int $idTransaksi): array
    {
        if (!Schema::hasTable('nota_audit_ai')) {
            return [];
        }
        return DB::table('nota_audit_ai')->where('id_transaksi', $idTransaksi)->orderBy('id_nota_audit_ai')->get()->map(fn ($r) => (array) $r)->all();
    }
}
