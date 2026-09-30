<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Quotation / Penawaran (LEVEL 2). Aturan bisnis & perhitungan harga ada di
 * App\Services\QuotationService.
 */
class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::orderByDesc('id');

        if ($request->filled('q')) {
            $cari = $request->q;
            $query->where(fn ($w) => $w->where('nomor', 'like', "%{$cari}%")->orWhere('nama_pelanggan', 'like', "%{$cari}%"));
        }

        if ($request->filled('status')) {
            if ($request->status === 'Kedaluwarsa') {
                $query->whereIn('status', ['Draft', 'Terkirim'])->whereDate('berlaku_sampai', '<', now()->toDateString());
            } else {
                $query->where('status', $request->status);
            }
        }

        $daftar = $query->paginate(20)->withQueryString();

        return view('quotation.index', compact('daftar'));
    }

    public function create()
    {
        return view('quotation.form', $this->dataForm(null));
    }

    public function store(Request $request)
    {
        try {
            $q = QuotationService::simpan($this->validasi($request));
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', \App\Support\PesanError::untuk($e));
        }

        return redirect()->route('quotation.edit', $q->id)->with('success', "Penawaran {$q->nomor} disimpan sebagai Draft.");
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items');

        return view('quotation.form', $this->dataForm($quotation));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->status !== 'Draft') {
            return back()->with('error', 'Cuma penawaran berstatus Draft yang bisa diubah.');
        }

        try {
            QuotationService::simpan($this->validasi($request), $quotation);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', \App\Support\PesanError::untuk($e));
        }

        return back()->with('success', 'Penawaran diperbarui.');
    }

    public function kirim(Quotation $quotation)
    {
        try {
            QuotationService::kirim($quotation);
        } catch (\RuntimeException $e) {
            return back()->with('error', \App\Support\PesanError::untuk($e));
        }

        return back()->with('success', 'Penawaran ditandai terkirim ke pelanggan.');
    }

    public function tanggapi(Request $request, Quotation $quotation)
    {
        $request->validate(['keputusan' => 'required|in:Disetujui,Ditolak']);

        try {
            QuotationService::tanggapi($quotation, $request->keputusan);
        } catch (\RuntimeException $e) {
            return back()->with('error', \App\Support\PesanError::untuk($e));
        }

        if ($request->keputusan === 'Disetujui') {
            \App\Support\JalurLanjut::set('Pelanggan setuju - buat nota dari penawaran ini', 'penjualan.create', 'Buat Nota', ['quotation' => $quotation->id]);
        }

        return back()->with('success', "Tanggapan pelanggan dicatat: {$request->keputusan}.");
    }

    public function cetak(Quotation $quotation)
    {
        $quotation->load('items');

        return view('quotation.print', compact('quotation'));
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->status !== 'Draft') {
            return back()->with('error', 'Cuma penawaran berstatus Draft yang bisa dihapus.');
        }

        $quotation->delete();

        return redirect()->route('quotation.index')->with('success', 'Penawaran dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'tanggal' => 'required|date',
            'berlaku_sampai' => 'required|date|after_or_equal:tanggal',
            'id_konsumen' => 'nullable|exists:konsumen,id_konsumen',
            'nama_pelanggan' => 'nullable|string|max:150',
            'telepon' => 'nullable|string|max:30',
            'id_golongan' => 'nullable|exists:golongan_konsumen,id_golongan',
            'diskon' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.tipe' => 'required|in:Produk,Jasa',
            'items.*.id_produk' => 'nullable|exists:produk,id_produk',
            'items.*.id_jasa' => 'nullable|exists:jasa,id_jasa',
            'items.*.id_finishing' => 'nullable|exists:finishing,id_finishing',
            'items.*.p' => 'nullable|numeric|min:0',
            'items.*.l' => 'nullable|numeric|min:0',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'nullable|numeric|min:0',
            'items.*.biaya_finishing' => 'nullable|numeric|min:0',
            'items.*.biaya_desain' => 'nullable|numeric|min:0',
            'items.*.catatan' => 'nullable|string|max:255',
        ]);
    }

    private function dataForm(?Quotation $quotation): array
    {
        return [
            'quotation' => $quotation,
            'produkList' => DB::table('produk')->where('status', 'aktif')->orderBy('nama_produk')->get(['id_produk', 'nama_produk', 'metode_hitung']),
            'jasaList' => DB::table('jasa')->orderBy('nama_jasa')->get(['id_jasa', 'nama_jasa']),
            'finishingList' => DB::table('finishing')->orderBy('nama_finishing')->get(['id_finishing', 'nama_finishing']),
            'konsumenList' => DB::table('konsumen')->orderBy('nama_konsumen')->get(['id_konsumen', 'nama_konsumen', 'telepon', 'id_golongan']),
            'golonganList' => DB::table('golongan_konsumen')->orderBy('nama_golongan')->get(),
        ];
    }
}
