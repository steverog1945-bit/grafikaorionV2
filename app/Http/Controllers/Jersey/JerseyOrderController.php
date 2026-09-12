<?php

namespace App\Http\Controllers\Jersey;

use App\Http\Controllers\Controller;
use App\Models\JerseyCategory;
use App\Models\JerseyDesigner;
use App\Models\JerseyMaterial;
use App\Models\JerseyOrder;
use App\Models\JerseyOrderLine;
use App\Models\JerseyProductType;
use App\Models\JerseySize;
use App\Models\Konsumen;
use App\Services\JerseyMaterialEstimationService;
use App\Services\JerseyOrderPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JerseyOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = JerseyOrder::with(['konsumen', 'lines'])->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%$s%")
                    ->orWhere('customer_name', 'like', "%$s%")
                    ->orWhere('team_name', 'like', "%$s%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('jersey.orders.index', compact('orders'));
    }

    public function create()
    {
        return view('jersey.orders.create', $this->formData());
    }

    private function formData(): array
    {
        return [
            'konsumenList' => Konsumen::orderBy('nama_konsumen')->get(),
            'designers' => JerseyDesigner::where('is_active', true)->orderBy('name')->get(),
            'productTypes' => JerseyProductType::where('is_active', true)->orderBy('name')->get(),
            'categories' => JerseyCategory::where('is_active', true)->orderBy('name')->get(),
            'materials' => JerseyMaterial::where('is_active', true)->orderBy('name')->get(),
            'sizes' => JerseySize::where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }

    /**
     * Upload thumbnail mockup desain SATU line - dipanggil terpisah dari
     * submit form utama (sama seperti PenjualanController::uploadThumbnail)
     * karena line-nya sendiri belum tentu tersimpan ke database saat user
     * memilih file (order masih dalam proses diisi di form). Dipakai baik
     * dari input file biasa maupun tempel (Ctrl+V) gambar dari clipboard.
     */
    public function uploadThumbnail(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('thumbnail')->store('jersey_thumbnails', 'public');

        return response()->json([
            'status' => 'success',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_date' => 'required|date',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'team_name' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'jersey_designer_id' => 'nullable|exists:jersey_designers,id',
            'notes' => 'nullable|string|max:2000',
            'discount' => 'nullable|numeric|min:0',
            'additional_charge' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',

            'lines' => 'required|array|min:1',
            'lines.*.nama_barang' => 'required|string|max:255',
            'lines.*.jersey_product_type_id' => 'nullable|exists:jersey_product_types,id',
            'lines.*.jersey_category_id' => 'nullable|exists:jersey_categories,id',
            'lines.*.jersey_material_id' => 'nullable|exists:jersey_materials,id',
            'lines.*.keterangan' => 'nullable|string|max:2000',
            'lines.*.thumbnail' => 'required|string|max:255',
            'lines.*.order_type' => 'required|in:quantity_only,player_list,mixed',

            'lines.*.items' => 'nullable|array',
            'lines.*.items.*.player_name' => 'nullable|string|max:255',
            'lines.*.items.*.player_number' => 'nullable|string|max:20',
            'lines.*.items.*.size' => 'nullable|in:S,M,L,XL,2XL,3XL,4XL,5XL',
            'lines.*.items.*.gender' => 'nullable|in:L,P',
            'lines.*.items.*.sleeve' => 'nullable|in:Pendek,Panjang',
            'lines.*.items.*.collar' => 'nullable|string|max:100',

            'lines.*.size_quantities' => 'nullable|array',
            'lines.*.size_quantities.*' => 'nullable|integer|min:0',
        ], [
            'lines.*.thumbnail.required' => 'Thumbnail desain wajib diisi untuk setiap barang (acuan warna & bentuk produksi).',
        ]);

        $sizeIdByName = JerseySize::pluck('id', 'name');

        DB::beginTransaction();
        try {
            $konsumen = Konsumen::findOrFail($data['id_konsumen']);

            $order = JerseyOrder::create([
                'order_number' => JerseyOrder::generateOrderNumber(now()),
                'order_date' => $data['order_date'],
                'id_konsumen' => $konsumen->id_konsumen,
                'customer_name' => $konsumen->nama_konsumen,
                'customer_phone' => $konsumen->telepon,
                'team_name' => $data['team_name'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => 'baru',
                'jersey_designer_id' => $data['jersey_designer_id'] ?? null,
                'designer_name' => !empty($data['jersey_designer_id'])
                    ? JerseyDesigner::find($data['jersey_designer_id'])?->name
                    : null,
                'notes' => $data['notes'] ?? null,
                'total' => 0,
                'discount' => $data['discount'] ?? 0,
                'additional_charge' => $data['additional_charge'] ?? 0,
                'deposit' => $data['deposit'] ?? 0,
                'status_pembayaran' => 'Belum Bayar',
                'created_by' => Auth::id(),
            ]);

            $adaLineValid = false;

            foreach ($data['lines'] as $lineData) {
                $items = array_values(array_filter($lineData['items'] ?? [], fn ($i) => trim($i['player_name'] ?? '') !== ''));
                $sizeQuantitiesMentah = array_filter($lineData['size_quantities'] ?? [], fn ($q) => (int) $q > 0);

                $qty = count($items) + array_sum(array_map('intval', $sizeQuantitiesMentah));
                if ($qty <= 0) {
                    continue; // baris kosong (line ditambahkan tapi tidak diisi) - dilewati, bukan error
                }
                $adaLineValid = true;

                // Qty per ukuran DIGABUNG dari kedua sumber (bukan salah satu
                // saja) - untuk mode "mixed" qty datang dari size_quantities
                // DAN dari daftar pemain sekaligus, keduanya harus masuk
                // hitungan estimasi bahan & rekap ukuran, bukan cuma salah satu.
                $qtyPerSizeId = [];
                foreach ($sizeQuantitiesMentah as $sizeId => $q) {
                    $qtyPerSizeId[$sizeId] = ($qtyPerSizeId[$sizeId] ?? 0) + (int) $q;
                }
                foreach ($items as $item) {
                    $sizeId = $sizeIdByName[$item['size']] ?? null;
                    if ($sizeId) {
                        $qtyPerSizeId[$sizeId] = ($qtyPerSizeId[$sizeId] ?? 0) + 1;
                    }
                }

                $material = !empty($lineData['jersey_material_id']) ? JerseyMaterial::find($lineData['jersey_material_id']) : null;

                $tier = JerseyOrderPricingService::cariTierHarga(
                    $lineData['jersey_product_type_id'] ?? null,
                    $lineData['jersey_category_id'] ?? null,
                    $material?->id,
                    $qty
                );
                $hargaSatuan = (float) ($tier->price ?? 0);
                $subtotal = $hargaSatuan * $qty;

                $line = $order->lines()->create([
                    'jersey_product_type_id' => $lineData['jersey_product_type_id'] ?? null,
                    'jersey_category_id' => $lineData['jersey_category_id'] ?? null,
                    'jersey_material_id' => $material?->id,
                    'nama_barang' => $lineData['nama_barang'],
                    'bahan' => $material?->name,
                    'keterangan' => $lineData['keterangan'] ?? null,
                    'thumbnail' => $lineData['thumbnail'],
                    'order_type' => $lineData['order_type'],
                    'qty' => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'jersey_price_setting_id' => $tier->id ?? null,
                ]);

                foreach ($items as $item) {
                    $line->items()->create($item);
                }
                foreach ($qtyPerSizeId as $sizeId => $qtyUkuran) {
                    $line->sizeQuantities()->create([
                        'jersey_size_id' => $sizeId,
                        'quantity' => $qtyUkuran,
                    ]);
                }

                // Spesifikasi Order (pemakaian bahan) dihitung OTOMATIS dari
                // qty per ukuran x matriks estimasi ukuran-komponen di Master
                // Data - bukan diketik manual lagi (lihat
                // JerseyMaterialEstimationService). Estimasi murni.
                foreach (JerseyMaterialEstimationService::hitungEstimasi($qtyPerSizeId) as $estimasi) {
                    $line->materials()->create([
                        'nama_barang' => $estimasi['component']->name,
                        'penggunaan_meter' => round($estimasi['total_meter'], 2),
                    ]);
                }
            }

            if (!$adaLineValid) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Order harus punya minimal 1 jenis barang dengan pemain/qty terisi.');
            }

            $order->recalculateTotal();

            DB::commit();

            return redirect()->route('jersey.orders.show', $order->id)
                ->with('success', 'Order jersey ' . $order->order_number . ' berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan order: ' . $e->getMessage());
        }
    }

    public function show(JerseyOrder $order)
    {
        $order->load([
            'konsumen', 'designer',
            'lines.productType', 'lines.category', 'lines.material', 'lines.items', 'lines.sizeQuantities.size', 'lines.materials',
        ]);
        return view('jersey.orders.show', compact('order'));
    }

    public function update(Request $request, JerseyOrder $order)
    {
        $data = $request->validate([
            'status' => 'required|in:baru,proses,selesai,diambil',
            'status_pembayaran' => 'required|in:Belum Bayar,DP,Lunas',
            'catatan_referensi_transaksi' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $order->update($data);

        return back()->with('success', 'Order jersey berhasil diperbarui.');
    }

    public function destroy(JerseyOrder $order)
    {
        $order->delete();
        return redirect()->route('jersey.orders.index')->with('success', 'Order jersey berhasil dihapus.');
    }

    /**
     * Cetak ringkasan seluruh order - $format 'thermal' (58mm) atau
     * 'normal' (A4/F4), sama seperti pola print nota/closing kas di modul
     * Kasir yang sudah ada.
     */
    public function print(JerseyOrder $order, string $format = 'normal')
    {
        $order->load([
            'konsumen', 'designer',
            'lines.productType', 'lines.category', 'lines.items', 'lines.sizeQuantities.size', 'lines.materials',
        ]);
        $format = in_array($format, ['thermal', 'normal']) ? $format : 'normal';
        return view('jersey.orders.print', compact('order', 'format'));
    }

    /**
     * Cetak SPK (Surat Perintah Kerja) SATU line spesifik - format detail
     * produksi per-jenis-barang (thumbnail, ukuran, spesifikasi bahan),
     * beda dari print() di atas yang mencetak ringkasan seluruh order.
     */
    public function printLine(JerseyOrder $order, JerseyOrderLine $line)
    {
        abort_if($line->jersey_order_id !== $order->id, 404);
        $line->load(['productType', 'category', 'material', 'items', 'sizeQuantities.size', 'materials', 'order.konsumen', 'order.designer']);
        return view('jersey.orders.spk-print', compact('order', 'line'));
    }
}
