<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Design Proof, Customer Approval & Revision Management (LEVEL 3, bagian
 * 33-35 dokumen audit), per item order.
 *
 * Aturan inti:
 *  - Setiap unggahan = revisi baru (#1, #2, ...); proof Menunggu Review yang
 *    belum diputuskan otomatis Digantikan revisi berikutnya.
 *  - Persetujuan pelanggan dicatat staf (kanal + catatan + waktu) - aplikasi
 *    belum punya portal pelanggan.
 *  - Setelah Disetujui desain TERKUNCI: unggahan baru ditolak. Membukanya
 *    lagi wajib lewat Approval Workflow ('buka_revisi_desain') dengan alasan.
 *  - Penjaga produksi bersifat opt-in per item: hanya item yang SUDAH punya
 *    proof yang tidak boleh naik cetak/selesai sebelum proof terakhirnya
 *    Disetujui; item tanpa proof tidak terpengaruh (alur lama tetap jalan).
 */
class DesignProofService
{
    public const KANAL = ['WhatsApp', 'Langsung', 'Email', 'Lainnya'];

    public static function terakhir(int $idDetail): ?object
    {
        return DB::table('design_proofs')->where('id_detail', $idDetail)->orderByDesc('revisi_no')->first();
    }

    /** True kalau item memakai proses proof tetapi belum ada persetujuan atas revisi terakhirnya. */
    public static function butuhPersetujuan(int $idDetail): bool
    {
        $terakhir = self::terakhir($idDetail);

        return $terakhir !== null && $terakhir->status !== 'Disetujui';
    }

    public static function unggah(int $idDetail, UploadedFile $file, ?string $catatan): object
    {
        $path = null;

        try {
            return DB::transaction(function () use ($idDetail, $file, $catatan, &$path) {
                $detail = DB::table('transaksi_detail')->where('id_detail', $idDetail)->lockForUpdate()->first();

                if (!$detail) {
                    throw new \RuntimeException('Item order tidak ditemukan.');
                }

                if ($detail->status_spk === 'Batal') {
                    throw new \RuntimeException('Item yang dibatalkan tidak bisa diberi proof.');
                }

                $terakhir = DB::table('design_proofs')->where('id_detail', $idDetail)->orderByDesc('revisi_no')->lockForUpdate()->first();

                if ($terakhir && $terakhir->status === 'Disetujui') {
                    throw new \RuntimeException("Desain revisi #{$terakhir->revisi_no} sudah disetujui dan terkunci. Ajukan pembukaan revisi lewat Approval Workflow.");
                }

                if ($terakhir && $terakhir->status === 'Menunggu Review') {
                    DB::table('design_proofs')->where('id', $terakhir->id)->update(['status' => 'Digantikan', 'updated_at' => now()]);
                }

                // Lewat BerkasAman (isi berkas diperiksa, ekstensi daftar putih, nama acak), sama dengan unggahan lain.
                $path = \App\Support\BerkasAman::simpan($file, "design_proofs/{$idDetail}", 'public');
                $revisi = ($terakhir->revisi_no ?? 0) + 1;

                $id = DB::table('design_proofs')->insertGetId([
                    'id_detail' => $idDetail,
                    'revisi_no' => $revisi,
                    'file_path' => $path,
                    'nama_file_asli' => $file->getClientOriginalName(),
                    'ukuran_bytes' => $file->getSize() ?: 0,
                    'catatan' => $catatan,
                    'status' => 'Menunggu Review',
                    'id_user_upload' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return DB::table('design_proofs')->where('id', $id)->first();
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    public static function catatKeputusan(int $idProof, string $keputusan, string $kanal, ?string $catatan): void
    {
        if (!in_array($keputusan, ['Disetujui', 'Revisi Diminta'], true)) {
            throw new \RuntimeException('Keputusan tidak valid.');
        }

        if (!in_array($kanal, self::KANAL, true)) {
            throw new \RuntimeException('Kanal persetujuan tidak valid.');
        }

        if ($keputusan === 'Revisi Diminta' && trim((string) $catatan) === '') {
            throw new \RuntimeException('Permintaan revisi wajib disertai catatan apa yang diubah.');
        }

        DB::transaction(function () use ($idProof, $keputusan, $kanal, $catatan) {
            $proof = DB::table('design_proofs')->where('id', $idProof)->lockForUpdate()->first();

            if (!$proof) {
                throw new \RuntimeException('Proof tidak ditemukan.');
            }

            if ($proof->status !== 'Menunggu Review') {
                throw new \RuntimeException("Proof revisi #{$proof->revisi_no} berstatus {$proof->status}, keputusan tidak bisa dicatat.");
            }

            DB::table('design_proofs')->where('id', $idProof)->update([
                'status' => $keputusan,
                'keputusan_kanal' => $kanal,
                'keputusan_catatan' => $catatan,
                'keputusan_at' => now(),
                'id_user_keputusan' => Auth::id(),
                'updated_at' => now(),
            ]);
        });
    }

    /** Mengajukan pembukaan desain yang sudah terkunci (Approval Workflow, tipe 'buka_revisi_desain'). */
    public static function ajukanBukaRevisi(int $idDetail, string $alasan): void
    {
        $terakhir = self::terakhir($idDetail);

        if (!$terakhir || $terakhir->status !== 'Disetujui') {
            throw new \RuntimeException('Hanya desain yang sudah disetujui yang perlu dibuka kembali.');
        }

        $sudahAda = \App\Models\ApprovalRequest::where('tipe_aksi', 'buka_revisi_desain')
            ->where('referensi_id', $idDetail)->where('status', 'Menunggu')->exists();

        if ($sudahAda) {
            throw new \RuntimeException('Sudah ada pengajuan pembukaan revisi untuk item ini yang menunggu.');
        }

        $nota = DB::table('transaksi_detail as d')->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->where('d.id_detail', $idDetail)->value('t.nomor_nota');

        ApprovalService::ajukan(
            'buka_revisi_desain', 'transaksi_detail', $idDetail, ['alasan' => $alasan],
            "Buka revisi desain {$nota} (setelah revisi #{$terakhir->revisi_no} disetujui)", $alasan
        );
    }

    /** Dipanggil lewat approval 'buka_revisi_desain'. */
    public static function eksekusiBukaRevisi(int $idDetail): void
    {
        DB::transaction(function () use ($idDetail) {
            $terakhir = DB::table('design_proofs')->where('id_detail', $idDetail)->orderByDesc('revisi_no')->lockForUpdate()->first();

            if (!$terakhir || $terakhir->status !== 'Disetujui') {
                throw new \RuntimeException('Desain item ini tidak lagi berstatus Disetujui.');
            }

            DB::table('design_proofs')->where('id', $terakhir->id)->update(['status' => 'Dibuka Kembali', 'updated_at' => now()]);
        });
    }
}
