<?php

namespace App\Http\Controllers\Jersey;

use App\Http\Controllers\Controller;
use App\Models\JerseyCategory;
use App\Models\JerseyCustomer;
use App\Models\JerseyDesigner;
use App\Models\JerseyOrder;
use App\Models\JerseyProductType;
use App\Models\JerseySize;
use App\Services\JerseyOrderPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JerseyOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = JerseyOrder::with(['category', 'productType'])->orderByDesc('id');

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
            'customers' => JerseyCustomer::where('is_active', true)->orderBy('name')->get(),
            'designers' => JerseyDesigner::where('is_active', true)->orderBy('name')->get(),
            'productTypes' => JerseyProductType::where('is_active', true)->orderBy('name')->get(),
            'categories' => JerseyCategory::where('is_active', true)->orderBy('name')->get(),
            'sizes' => JerseySize::where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_date' => 'required|date',
            'jersey_customer_id' => 'nullable|exists:jersey_customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'team_name' => 'nullable|string|max:255',
            'jersey_product_type_id' => 'nullable|exists:jersey_product_types,id',
            'jersey_category_id' => 'nullable|exists:jersey_categories,id',
            'deadline' => 'nullable|date',
            'order_type' => 'required|in:quantity_only,player_list,mixed',
            'jersey_designer_id' => 'nullable|exists:jersey_designers,id',
            'notes' => 'nullable|string|max:2000',
            'discount' => 'nullable|numeric|min:0',
            'additional_charge' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',

            'items' => 'required_if:order_type,player_list,mixed|array',
            'items.*.player_name' => 'required_with:items|string|max:255',
            'items.*.player_number' => 'nullable|string|max:20',
            'items.*.size' => 'required_with:items|in:S,M,L,XL,2XL,3XL,4XL',
            'items.*.gender' => 'required_with:items|in:L,P',
            'items.*.sleeve' => 'required_with:items|in:Pendek,Panjang',
            'items.*.collar' => 'nullable|string|max:100',
            'items.*.notes' => 'nullable|string|max:500',

            'size_quantities' => 'required_if:order_type,quantity_only,mixed|array',
            'size_quantities.*' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalQty = 0;
            if (!empty($data['items'])) {
                $totalQty += count($data['items']);
            }
            if (!empty($data['size_quantities'])) {
                $totalQty += array_sum(array_map('intval', $data['size_quantities']));
            }
            if ($totalQty <= 0) {
                return back()->withInput()->with('error', 'Order harus punya minimal 1 pemain atau 1 ukuran dengan qty > 0.');
            }

            $tier = JerseyOrderPricingService::cariTierHarga(
                $data['jersey_product_type_id'] ?? null,
                $data['jersey_category_id'] ?? null,
                $totalQty
            );
            $hargaSatuan = (float) ($tier->price ?? 0);
            $subtotal = $hargaSatuan * $totalQty;
            $discount = (float) ($data['discount'] ?? 0);
            $additionalCharge = (float) ($data['additional_charge'] ?? 0);
            $total = max(0, $subtotal - $discount + $additionalCharge);

            $customerName = $data['customer_name'];
            $customerPhone = $data['customer_phone'] ?? null;
            if (!empty($data['jersey_customer_id'])) {
                $cust = JerseyCustomer::find($data['jersey_customer_id']);
                if ($cust) {
                    $customerName = $cust->name;
                    $customerPhone = $cust->phone;
                }
            }

            $designerName = null;
            if (!empty($data['jersey_designer_id'])) {
                $designerName = JerseyDesigner::find($data['jersey_designer_id'])?->name;
            }

            $order = JerseyOrder::create([
                'order_number' => JerseyOrder::generateOrderNumber(now()),
                'order_date' => $data['order_date'],
                'jersey_customer_id' => $data['jersey_customer_id'] ?? null,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'team_name' => $data['team_name'] ?? null,
                'jersey_product_type_id' => $data['jersey_product_type_id'] ?? null,
                'jersey_category_id' => $data['jersey_category_id'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => 'baru',
                'order_type' => $data['order_type'],
                'jersey_designer_id' => $data['jersey_designer_id'] ?? null,
                'designer_name' => $designerName,
                'notes' => $data['notes'] ?? null,
                'total' => $total,
                'discount' => $discount,
                'additional_charge' => $additionalCharge,
                'price_breakdown' => [
                    'harga_satuan' => $hargaSatuan,
                    'qty' => $totalQty,
                    'subtotal' => $subtotal,
                ],
                'jersey_price_setting_id' => $tier->id ?? null,
                'deposit' => $data['deposit'] ?? 0,
                'status_pembayaran' => 'Belum Bayar',
                'created_by' => Auth::id(),
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create($item);
                }
            }

            if (!empty($data['size_quantities'])) {
                foreach ($data['size_quantities'] as $sizeId => $qty) {
                    $qty = (int) $qty;
                    if ($qty > 0) {
                        $order->sizeQuantities()->create([
                            'jersey_size_id' => $sizeId,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }

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
        $order->load(['customer', 'designer', 'productType', 'category', 'items', 'sizeQuantities.size']);
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

    public function print(JerseyOrder $order)
    {
        $order->load(['customer', 'designer', 'productType', 'category', 'items', 'sizeQuantities.size']);
        return view('jersey.orders.print', compact('order'));
    }
}
