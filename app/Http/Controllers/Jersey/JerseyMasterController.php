<?php

namespace App\Http\Controllers\Jersey;

use App\Http\Controllers\Controller;
use App\Models\JerseyCategory;
use App\Models\JerseyCustomer;
use App\Models\JerseyDesigner;
use App\Models\JerseyPriceSetting;
use App\Models\JerseyProductType;
use App\Models\JerseySize;
use Illuminate\Http\Request;

/**
 * Semua master data modul jersey (pelanggan, desainer, tipe produk,
 * kategori, ukuran, tier harga) dalam SATU halaman bertab - pola yang
 * sama seperti Master Jasa (jasa + jasa_harga digabung satu halaman) di
 * modul percetakan, supaya tidak perlu 6 halaman terpisah untuk data
 * pendukung yang masing-masing sangat sederhana (cuma nama + status aktif).
 */
class JerseyMasterController extends Controller
{
    public function index()
    {
        $customers = JerseyCustomer::orderBy('name')->get();
        $designers = JerseyDesigner::orderBy('name')->get();
        $productTypes = JerseyProductType::orderBy('name')->get();
        $categories = JerseyCategory::orderBy('name')->get();
        $sizes = JerseySize::orderBy('sort_order')->get();
        $priceSettings = JerseyPriceSetting::with(['productType', 'category'])
            ->orderBy('jersey_category_id')
            ->orderBy('qty_min')
            ->get();

        return view('jersey.master.index', compact(
            'customers', 'designers', 'productTypes', 'categories', 'sizes', 'priceSettings'
        ));
    }

    // ---- Pelanggan ----
    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);
        JerseyCustomer::create($data);
        return back()->with('success', 'Pelanggan jersey berhasil ditambahkan.');
    }

    public function updateCustomer(Request $request, JerseyCustomer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);
        $customer->update($data);
        return back()->with('success', 'Pelanggan jersey berhasil diperbarui.');
    }

    public function toggleCustomer(JerseyCustomer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        return back()->with('success', 'Status pelanggan diperbarui.');
    }

    // ---- Desainer ----
    public function storeDesigner(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_designers,name']);
        JerseyDesigner::create($data);
        return back()->with('success', 'Desainer berhasil ditambahkan.');
    }

    public function toggleDesigner(JerseyDesigner $designer)
    {
        $designer->update(['is_active' => !$designer->is_active]);
        return back()->with('success', 'Status desainer diperbarui.');
    }

    // ---- Tipe Produk ----
    public function storeProductType(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_product_types,name']);
        JerseyProductType::create($data);
        return back()->with('success', 'Tipe produk berhasil ditambahkan.');
    }

    public function toggleProductType(JerseyProductType $productType)
    {
        $productType->update(['is_active' => !$productType->is_active]);
        return back()->with('success', 'Status tipe produk diperbarui.');
    }

    // ---- Kategori ----
    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_categories,name']);
        JerseyCategory::create($data);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function toggleCategory(JerseyCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('success', 'Status kategori diperbarui.');
    }

    // ---- Ukuran ----
    public function storeSize(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:jersey_sizes,name',
            'sort_order' => 'nullable|integer|min:0',
            'price_surcharge' => 'nullable|numeric|min:0',
        ]);
        JerseySize::create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'price_surcharge' => $data['price_surcharge'] ?? 0,
        ]);
        return back()->with('success', 'Ukuran berhasil ditambahkan.');
    }

    public function toggleSize(JerseySize $size)
    {
        $size->update(['is_active' => !$size->is_active]);
        return back()->with('success', 'Status ukuran diperbarui.');
    }

    // ---- Tier Harga ----
    public function storePriceSetting(Request $request)
    {
        $data = $request->validate([
            'jersey_product_type_id' => 'nullable|exists:jersey_product_types,id',
            'jersey_category_id' => 'nullable|exists:jersey_categories,id',
            'qty_min' => 'required|integer|min:1',
            'qty_max' => 'nullable|integer|gte:qty_min',
            'price' => 'required|numeric|min:0',
        ]);
        JerseyPriceSetting::create($data);
        return back()->with('success', 'Tier harga berhasil ditambahkan.');
    }

    public function destroyPriceSetting(JerseyPriceSetting $priceSetting)
    {
        $priceSetting->delete();
        return back()->with('success', 'Tier harga berhasil dihapus.');
    }
}
