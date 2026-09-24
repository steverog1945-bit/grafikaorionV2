<?php

namespace App\Http\Controllers;

use App\Services\DesignProofService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Design Proof, Customer Approval & Revision (LEVEL 3). Aturan bisnis ada di
 * App\Services\DesignProofService.
 */
class DesignProofController extends Controller
{
    public function index(Request $request)
    {
        $terakhir = DB::table('design_proofs as p')
            ->selectRaw('p.id_detail, p.status, p.revisi_no')
            ->whereRaw('p.revisi_no = (SELECT MAX(p2.revisi_no) FROM design_proofs p2 WHERE p2.id_detail = p.id_detail)');

        $query = DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->leftJoinSub($terakhir, 'lp', 'lp.id_detail', '=', 'd.id_detail')
            ->where('t.status_nota', 'Aktif')
            ->whereNotNull('d.id_produk')
            ->whereNotIn('d.status_spk', ['Batal'])
            ->select('d.id_detail', 'd.nama_file', 'd.qty', 'd.status_spk', 't.nomor_nota', 't.tgl_order', 'lp.status as status_proof', 'lp.revisi_no')
            ->orderByDesc('d.id_detail');

        if ($request->filled('q')) {
            $cari = $request->q;
            $query->where(fn ($w) => $w->where('t.nomor_nota', 'like', "%{$cari}%")->orWhere('d.nama_file', 'like', "%{$cari}%"));
        }

        if ($request->filled('status')) {
            $request->status === 'Belum Ada Proof'
                ? $query->whereNull('lp.status')
                : $query->where('lp.status', $request->status);
        }

        $daftar = $query->paginate(20)->withQueryString();

        return view('design-proof.index', compact('daftar'));
    }

    public function show(int $idDetail)
    {
        $detail = DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->leftJoin('konsumen as k', 'k.id_konsumen', '=', 't.id_konsumen')
            ->where('d.id_detail', $idDetail)
            ->select('d.*', 't.nomor_nota', 'k.nama_konsumen', 'k.telepon')
            ->first();

        abort_if(!$detail, 404);

        $proofs = DB::table('design_proofs as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user_upload')
            ->leftJoin('users as uk', 'uk.id_user', '=', 'p.id_user_keputusan')
            ->where('p.id_detail', $idDetail)
            ->select('p.*', 'u.username as pengunggah', 'uk.username as pencatat')
            ->orderByDesc('p.revisi_no')
            ->get()
            ->map(function ($p) {
                $p->url = Storage::disk('public')->url($p->file_path);
                $p->gambar = (bool) preg_match('/\.(jpe?g|png|webp)$/i', $p->file_path);

                return $p;
            });

        $terakhir = $proofs->first();
        $terkunci = $terakhir && $terakhir->status === 'Disetujui';
        $bukaMenunggu = \App\Models\ApprovalRequest::where('tipe_aksi', 'buka_revisi_desain')
            ->where('referensi_id', $idDetail)->where('status', 'Menunggu')->exists();

        return view('design-proof.show', [
            'detail' => $detail, 'proofs' => $proofs, 'terakhir' => $terakhir, 'terkunci' => $terkunci,
            'bukaMenunggu' => $bukaMenunggu, 'kanal' => DesignProofService::KANAL,
        ]);
    }

    public function unggah(Request $request, int $idDetail)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
            'catatan' => 'nullable|string|max:1000',
        ]);

        try {
            $proof = DesignProofService::unggah($idDetail, $request->file('file'), $request->input('catatan'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Proof revisi #{$proof->revisi_no} diunggah - menunggu review pelanggan.");
    }

    public function keputusan(Request $request, int $idProof)
    {
        $data = $request->validate([
            'keputusan' => 'required|in:Disetujui,Revisi Diminta',
            'kanal' => 'required|in:' . implode(',', DesignProofService::KANAL),
            'catatan' => 'nullable|string|max:1000',
        ]);

        try {
            DesignProofService::catatKeputusan($idProof, $data['keputusan'], $data['kanal'], $data['catatan'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', $data['keputusan'] === 'Disetujui'
            ? 'Persetujuan pelanggan dicatat. Desain terkunci dan siap produksi.'
            : 'Permintaan revisi dicatat. Unggah proof revisi berikutnya.');
    }

    public function ajukanRevisi(Request $request, int $idDetail)
    {
        $request->validate(['alasan' => 'required|string|max:500']);

        try {
            DesignProofService::ajukanBukaRevisi($idDetail, $request->alasan);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan pembukaan revisi dikirim untuk persetujuan Administrator.');
    }
}
