<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectingController extends Controller
{
    /**
     * Collecting = status kelengkapan barang per NOTA, dihitung langsung dari
     * transaksi_detail.status_spk (tidak ada tabel/kolom baru). Sebuah nota
     * dianggap LENGKAP kalau semua item di dalamnya sudah Selesai atau Batal
     * (tidak ada lagi yang masih Antre/Desain/Cetak).
     *
     * status_collecting (kolom baru, migrasi 160000) adalah konsep TERPISAH:
     * status PENGAMBILAN FISIK oleh customer, bukan kelengkapan pengerjaan.
     * Dua hal beda yang sebelumnya kebetulan dinamai sama ("Status
     * Collecting" dipakai untuk kelengkapan pengerjaan) - lihat rename di
     * view jadi "Status Pengerjaan" vs "Pengambilan".
     */
    public function index(Request $request)
    {
        $query = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('transaksi_detail', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->leftJoin('users', 'transaksi.id_user_collecting', '=', 'users.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'transaksi.status_nota',
                'transaksi.status_collecting',
                'transaksi.tgl_collecting',
                'konsumen.nama_konsumen',
                'users.username as diambil_oleh',
                DB::raw('COUNT(transaksi_detail.id_detail) as total_item'),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk IN ('Selesai','Batal') THEN 1 ELSE 0 END) as item_kelar"),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk = 'Batal' THEN 1 ELSE 0 END) as item_batal")
            )
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'transaksi.status_nota',
                'transaksi.status_collecting',
                'transaksi.tgl_collecting',
                'konsumen.nama_konsumen',
                'users.username'
            );

        if ($request->filled('nama_konsumen')) {
            $query->where('konsumen.nama_konsumen', 'like', '%' . $request->nama_konsumen . '%');
        }

        if ($request->filled('nomor_nota')) {
            $query->where('transaksi.nomor_nota', 'like', '%' . $request->nomor_nota . '%');
        }

        // Perf dataset besar: rentang sargable (DATE() mematikan index tgl_order).
        if ($request->filled('tgl_dari')) {
            $query->where('transaksi.tgl_order', '>=', $request->tgl_dari . ' 00:00:00');
        }

        if ($request->filled('tgl_sampai')) {
            $query->where('transaksi.tgl_order', '<', \Carbon\Carbon::parse($request->tgl_sampai)->addDay()->format('Y-m-d') . ' 00:00:00');
        }

        // Kelengkapan pengerjaan dihitung dari HAVING karena melibatkan hasil
        // agregasi (kolom turunan) - param request sengaja dinamai beda dari
        // kolom transaksi.status_collecting (konsep pengambilan fisik) supaya
        // tidak ada dua hal berbeda yang bernama sama.
        $statusPengerjaan = $request->input('status_pengerjaan');
        if ($statusPengerjaan === 'lengkap') {
            $query->havingRaw('total_item > 0 AND item_kelar = total_item');
        } elseif ($statusPengerjaan === 'belum') {
            $query->havingRaw('item_kelar < total_item');
        }

        if ($request->filled('status_pengambilan')) {
            $query->where('transaksi.status_collecting', $request->status_pengambilan);
        }

        $collecting = $query->orderByDesc('transaksi.id_transaksi')->paginate(20)->withQueryString();

        $jumlahSiapDiambilAwal = DB::table('transaksi')
            ->leftJoin('transaksi_detail', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('transaksi.status_collecting', 'Belum Diambil')
            ->groupBy('transaksi.id_transaksi')
            ->select('transaksi.id_transaksi')
            ->havingRaw('COUNT(transaksi_detail.id_detail) > 0 AND SUM(CASE WHEN transaksi_detail.status_spk IN (\'Selesai\',\'Batal\') THEN 1 ELSE 0 END) = COUNT(transaksi_detail.id_detail)')
            ->get()
            ->count();

        return view('collecting.index', compact('collecting', 'jumlahSiapDiambilAwal'));
    }

    /**
     * Tandai nota sudah diambil customer. Kelengkapan pengerjaan DIHITUNG
     * ULANG di sini (bukan percaya nilai yang sempat tampil di browser) -
     * satu-satunya penjaga sesungguhnya supaya nota dengan item yang masih
     * Antre/Desain/Cetak tidak bisa ditandai selesai diambil.
     */
    public function proses(Request $request, $id)
    {
        // Catatan serah terima (Pickup/Delivery Management) - semua OPSIONAL
        // supaya panggilan lama tanpa field ini tetap jalan; kalau jenisnya
        // perwakilan/dikirim, nama penerima wajib.
        $request->validate([
            'jenis' => 'nullable|in:Diambil Sendiri,Diambil Perwakilan,Dikirim',
            'nama_penerima' => 'nullable|string|max:150|required_if:jenis,Diambil Perwakilan,Dikirim',
            'catatan_serah' => 'nullable|string|max:1000',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            // Serah terima nota yang belum lunas: konfirmasi eksplisit + alasan tertulis (ditegakkan di server).
            'konfirmasi_belum_lunas' => 'nullable|boolean',
            'alasan_belum_lunas' => 'nullable|string|max:500',
        ]);

        $buktiPath = null;

        try {
            return DB::transaction(function () use ($id, $request, &$buktiPath) {
            $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->lockForUpdate()->first();

            if (!$transaksi) {
                return back()->with('error', 'Transaksi tidak ditemukan.');
            }

            if ($transaksi->status_collecting === 'Sudah Diambil') {
                return back()->with('error', 'Nota ini sudah ditandai diambil sebelumnya.');
            }

            if ($transaksi->status_nota === 'Dibatalkan') {
                return back()->with('error', 'Nota ini sudah dibatalkan - tidak ada barang yang perlu diambil.');
            }

            // Kontrol pelepasan barang: nota yang belum lunas tidak diserahkan begitu saja. Boleh (usaha ini
            // memang punya piutang), tetapi harus sengaja: konfirmasi + alasan, dan keduanya tercatat di audit.
            $sisaTagihan = 0.0;
            $alasanBelumLunas = null;
            if ($transaksi->status_pembayaran !== 'Lunas') {
                $terbayar = (float) DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal');
                $sisaTagihan = max(0, round((float) $transaksi->total_bayar - $terbayar, 2));

                $alasanBelumLunas = trim((string) $request->input('alasan_belum_lunas'));
                if (!$request->boolean('konfirmasi_belum_lunas') || mb_strlen($alasanBelumLunas) < 5) {
                    return back()->with('error', "Nota {$transaksi->nomor_nota} belum lunas (sisa Rp " . number_format($sisaTagihan, 0, ',', '.') . '). Barang belum diserahkan. Untuk menyerahkan dengan tagihan tersisa, konfirmasi dan tulis alasannya.');
                }
            }

            $progres = DB::table('transaksi_detail')
                ->where('id_transaksi', $id)
                ->selectRaw('COUNT(*) as total_item')
                ->selectRaw("SUM(CASE WHEN status_spk IN ('Selesai','Batal') THEN 1 ELSE 0 END) as item_kelar")
                ->first();

            if (!$progres->total_item || $progres->item_kelar < $progres->total_item) {
                $belum = $progres->total_item - $progres->item_kelar;
                return back()->with('error', "Belum bisa diproses - masih ada {$belum} item yang belum selesai dikerjakan.");
            }

            DB::table('transaksi')->where('id_transaksi', $id)->update([
                'status_collecting'   => 'Sudah Diambil',
                'tgl_collecting'      => now(),
                'id_user_collecting'  => Auth::id(),
                'updated_at'          => now(),
            ]);

            if ($request->hasFile('bukti')) {
                $buktiPath = $request->file('bukti')->store("bukti_serah_terima/{$id}", 'public');
            }

            DB::table('pengambilan')->insert([
                'id_transaksi' => $id,
                'jenis' => $request->input('jenis', 'Diambil Sendiri'),
                'nama_penerima' => $request->input('nama_penerima'),
                'catatan' => $request->input('catatan_serah'),
                'bukti_path' => $buktiPath,
                'id_user' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // F2 (audit putaran 2): jalur DB::table() tidak tersentuh trait
            // Auditable - serah terima barang fisik ke konsumen adalah mutasi
            // penting yang WAJIB punya jejak di menu Audit Log. Ditulis DI
            // DALAM transaksi yang sama (rollback -> log ikut batal).
            \App\Services\AuditLogger::log(
                'pengambilan_barang',
                'transaksi',
                (int) $id,
                ['status_collecting' => $transaksi->status_collecting],
                [
                    'status_collecting' => 'Sudah Diambil',
                    'jenis' => $request->input('jenis', 'Diambil Sendiri'),
                    'nama_penerima' => $request->input('nama_penerima'),
                    'bukti_path' => $buktiPath,
                ] + ($alasanBelumLunas !== null ? ['diserahkan_belum_lunas' => true, 'sisa_tagihan' => $sisaTagihan, 'alasan' => $alasanBelumLunas] : []),
                "Nota {$transaksi->nomor_nota} diserahkan ke penerima" . ($alasanBelumLunas !== null ? ' TANPA LUNAS (sisa Rp ' . number_format($sisaTagihan, 0, ',', '.') . ')' : '')
            );

            return back()->with('success', "Nota {$transaksi->nomor_nota} ditandai sudah diambil.");
            });
        } catch (\Throwable $e) {
            if ($buktiPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($buktiPath);
            }

            throw $e;
        }
    }

    /**
     * Polling notifikasi "ada nota siap diambil" - kasir/bagian collecting
     * tahu ada nota yang baru selesai dikerjakan (semua item Selesai/Batal)
     * & belum diambil customer, tanpa manual refresh.
     */
    public function cekAntrianBaru()
    {
        // ->select() eksplisit WAJIB sebelum groupBy() di sini - tanpa itu
        // MySQL/MariaDB (mode ONLY_FULL_GROUP_BY, default aktif) menolak
        // query karena secara implisit SELECT * menyertakan kolom yang
        // tidak ada di GROUP BY maupun agregat (ditemukan lewat test).
        $total = DB::table('transaksi')
            ->leftJoin('transaksi_detail', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('transaksi.status_collecting', 'Belum Diambil')
            ->groupBy('transaksi.id_transaksi')
            ->select('transaksi.id_transaksi')
            ->havingRaw('COUNT(transaksi_detail.id_detail) > 0 AND SUM(CASE WHEN transaksi_detail.status_spk IN (\'Selesai\',\'Batal\') THEN 1 ELSE 0 END) = COUNT(transaksi_detail.id_detail)')
            ->get()
            ->count();

        return response()->json(['total' => $total]);
    }
}
