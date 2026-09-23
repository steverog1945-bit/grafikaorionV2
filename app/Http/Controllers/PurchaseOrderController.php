<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Purchase Order (roadmap LEVEL 1, bagian 11 dokumen audit). Draft ->
 * Dipesan (diterbitkan ke supplier) -> Diterima Sebagian / Selesai
 * (diisi saat Kedatangan Bahan dikaitkan ke PO - tahap berikutnya) atau
 * Dibatalkan. Bisa dibuat dari Purchase Request yang sudah Disetujui.
 */
class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'pembuat'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $daftar = $query->paginate(20)->withQueryString();

        return view('purchase-order.index', compact('daftar'));
    }

    public function create(Request $request)
    {
        $prefill = [];

        if ($request->filled('from_pr')) {
            $pr = PurchaseRequest::with('details')->findOrFail($request->from_pr);

            if ($pr->status !== 'Disetujui') {
                return redirect()->route('purchase-request.index')->with('error', "Purchase Request {$pr->nomor} belum berstatus Disetujui.");
            }

            $prefill = [
                'id_purchase_request' => $pr->id,
                'nomor_pr' => $pr->nomor,
                'items' => $pr->details->map(fn ($d) => ['id_bahan' => $d->id_bahan, 'qty' => $d->qty, 'harga' => 0])->all(),
            ];
        }

        return view('purchase-order.form', $this->dataForm(null) + ['prefill' => $prefill]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);
        $hitung = $this->hitung($validated);

        $po = DB::transaction(function () use ($validated, $hitung, $request) {
            $idPr = $request->input('id_purchase_request');

            if ($idPr) {
                $pr = PurchaseRequest::whereKey($idPr)->lockForUpdate()->firstOrFail();

                if ($pr->status !== 'Disetujui') {
                    throw ValidationException::withMessages(['id_purchase_request' => "Purchase Request {$pr->nomor} tidak lagi berstatus Disetujui."]);
                }

                $pr->update(['status' => 'Diproses PO']);
            }

            $po = PurchaseOrder::create([
                'nomor' => DocumentNumberService::generate('PO'),
                'id_supplier' => $validated['id_supplier'],
                'id_purchase_request' => $idPr,
                'tanggal' => $validated['tanggal'],
                'tgl_pengiriman_estimasi' => $validated['tgl_pengiriman_estimasi'] ?? null,
                'termin_pembayaran' => $validated['termin_pembayaran'] ?? null,
                'status' => 'Draft',
                'subtotal' => $hitung['subtotal'],
                'diskon' => $hitung['diskon'],
                'pajak_persen' => $hitung['pajak_persen'],
                'ongkir' => $hitung['ongkir'],
                'total' => $hitung['total'],
                'catatan' => $validated['catatan'] ?? null,
                'id_user_pembuat' => Auth::id(),
            ]);

            $this->simpanBaris($po, $hitung['items']);

            return $po;
        });

        return redirect()->route('purchase-order.edit', $po->id)->with('success', "Purchase Order {$po->nomor} disimpan sebagai Draft.");
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('details.bahan', 'supplier', 'purchaseRequest');

        return view('purchase-order.form', $this->dataForm($purchaseOrder) + ['prefill' => []]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Draft') {
            return back()->with('error', 'Cuma Purchase Order berstatus Draft yang bisa diubah.');
        }

        $validated = $this->validasi($request);
        $hitung = $this->hitung($validated);

        DB::transaction(function () use ($validated, $hitung, $purchaseOrder) {
            $purchaseOrder->update([
                'id_supplier' => $validated['id_supplier'],
                'tanggal' => $validated['tanggal'],
                'tgl_pengiriman_estimasi' => $validated['tgl_pengiriman_estimasi'] ?? null,
                'termin_pembayaran' => $validated['termin_pembayaran'] ?? null,
                'subtotal' => $hitung['subtotal'],
                'diskon' => $hitung['diskon'],
                'pajak_persen' => $hitung['pajak_persen'],
                'ongkir' => $hitung['ongkir'],
                'total' => $hitung['total'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $purchaseOrder->details()->delete();
            $this->simpanBaris($purchaseOrder, $hitung['items']);
        });

        return back()->with('success', 'Purchase Order diperbarui.');
    }

    public function terbitkan(PurchaseOrder $purchaseOrder)
    {
        $ok = DB::transaction(function () use ($purchaseOrder) {
            $po = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();

            if ($po->status !== 'Draft') {
                return false;
            }

            $po->update(['status' => 'Dipesan']);

            return true;
        });

        return $ok
            ? back()->with('success', "Purchase Order {$purchaseOrder->nomor} diterbitkan ke supplier.")
            : back()->with('error', 'Purchase Order harus berstatus Draft untuk diterbitkan.');
    }

    public function batalkan(PurchaseOrder $purchaseOrder)
    {
        $galat = DB::transaction(function () use ($purchaseOrder) {
            $po = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();

            if (!in_array($po->status, ['Draft', 'Dipesan'], true)) {
                return "Purchase Order berstatus {$po->status} tidak bisa dibatalkan.";
            }

            if ($po->details()->where('qty_diterima', '>', 0)->exists()) {
                return 'Purchase Order ini sudah ada barang yang diterima, tidak bisa dibatalkan.';
            }

            $po->update(['status' => 'Dibatalkan']);

            return null;
        });

        return $galat ? back()->with('error', $galat) : back()->with('success', "Purchase Order {$purchaseOrder->nomor} dibatalkan.");
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Draft') {
            return back()->with('error', 'Cuma Purchase Order berstatus Draft yang bisa dihapus.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-order.index')->with('success', 'Purchase Order dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'tanggal' => 'required|date',
            'tgl_pengiriman_estimasi' => 'nullable|date|after_or_equal:tanggal',
            'termin_pembayaran' => 'nullable|string|max:100',
            'diskon' => 'nullable|numeric|min:0',
            'pajak_persen' => 'nullable|numeric|min:0|max:100',
            'ongkir' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id_bahan' => 'required|exists:bahan_baku,id_bahan',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
        ]);
    }

    /** Total dihitung di server - angka total dari form tidak pernah dipercaya. */
    private function hitung(array $v): array
    {
        $items = collect($v['items'])->map(fn ($i) => [
            'id_bahan' => $i['id_bahan'],
            'qty' => (float) $i['qty'],
            'harga' => (float) $i['harga'],
            'subtotal' => round((float) $i['qty'] * (float) $i['harga'], 2),
        ])->all();

        $subtotal = round(array_sum(array_column($items, 'subtotal')), 2);
        $diskon = round((float) ($v['diskon'] ?? 0), 2);

        if ($diskon > $subtotal) {
            throw ValidationException::withMessages(['diskon' => 'Diskon tidak boleh melebihi subtotal.']);
        }

        $pajakPersen = (float) ($v['pajak_persen'] ?? 0);
        $pajak = round(($subtotal - $diskon) * $pajakPersen / 100, 2);
        $ongkir = round((float) ($v['ongkir'] ?? 0), 2);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'pajak_persen' => $pajakPersen,
            'ongkir' => $ongkir,
            'total' => round($subtotal - $diskon + $pajak + $ongkir, 2),
        ];
    }

    private function simpanBaris(PurchaseOrder $po, array $items): void
    {
        $rows = collect($items)->map(fn ($i) => $i + [
            'id_purchase_order' => $po->id,
            'qty_diterima' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('purchase_order_details')->insert($rows);
    }

    private function dataForm(?PurchaseOrder $po): array
    {
        return [
            'po' => $po,
            'supplierList' => DB::table('supplier')->orderBy('nama_supplier')->get(),
            'bahanList' => DB::table('bahan_baku')->where('status', 'aktif')->orderBy('nama_bahan')->get(['id_bahan', 'nama_bahan', 'satuan_stok']),
        ];
    }
}
