<?php

namespace App\Http\Controllers\Jersey;

use App\Http\Controllers\Controller;
use App\Models\JerseyCategory;
use App\Models\JerseyDesigner;
use App\Models\JerseyPriceSetting;
use App\Models\JerseyProductType;
use App\Models\JerseySize;
use Illuminate\Http\Request;

/**
 * Semua master data modul jersey (desainer, tipe produk, kategori, ukuran,
 * tier harga) dalam SATU halaman bertab. Semua aksi (tambah/ubah/toggle/
 * hapus) dikirim lewat fetch() JSON dan mengembalikan JSON juga - BUKAN
 * submit form biasa - supaya tab yang sedang aktif tidak pernah ke-reset
 * ke tab pertama tiap kali user menambah satu baris data (keluhan
 * langsung dari user setelah demo pertama modul ini).
 */
class JerseyMasterController extends Controller
{
    public function index()
    {
        $designers = JerseyDesigner::orderBy('name')->get();
        $productTypes = JerseyProductType::orderBy('name')->get();
        $categories = JerseyCategory::orderBy('name')->get();
        $sizes = JerseySize::orderBy('sort_order')->get();
        $priceSettings = JerseyPriceSetting::with(['productType', 'category'])
            ->orderBy('jersey_category_id')
            ->orderBy('qty_min')
            ->get();

        return view('jersey.master.index', compact(
            'designers', 'productTypes', 'categories', 'sizes', 'priceSettings'
        ));
    }

    // ---- Desainer ----
    public function storeDesigner(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_designers,name']);
        $row = JerseyDesigner::create($data);
        return response()->json(['success' => true, 'message' => 'Desainer berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateDesigner(Request $request, JerseyDesigner $designer)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_designers,name,' . $designer->id]);
        $designer->update($data);
        return response()->json(['success' => true, 'message' => 'Desainer berhasil diperbarui.', 'row' => $designer]);
    }

    public function toggleDesigner(JerseyDesigner $designer)
    {
        $designer->update(['is_active' => !$designer->is_active]);
        return response()->json(['success' => true, 'message' => 'Status desainer diperbarui.', 'row' => $designer]);
    }

    // ---- Tipe Produk ----
    public function storeProductType(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_product_types,name']);
        $row = JerseyProductType::create($data);
        return response()->json(['success' => true, 'message' => 'Tipe produk berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateProductType(Request $request, JerseyProductType $productType)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_product_types,name,' . $productType->id]);
        $productType->update($data);
        return response()->json(['success' => true, 'message' => 'Tipe produk berhasil diperbarui.', 'row' => $productType]);
    }

    public function toggleProductType(JerseyProductType $productType)
    {
        $productType->update(['is_active' => !$productType->is_active]);
        return response()->json(['success' => true, 'message' => 'Status tipe produk diperbarui.', 'row' => $productType]);
    }

    // ---- Kategori ----
    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_categories,name']);
        $row = JerseyCategory::create($data);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateCategory(Request $request, JerseyCategory $category)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_categories,name,' . $category->id]);
        $category->update($data);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui.', 'row' => $category]);
    }

    public function toggleCategory(JerseyCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return response()->json(['success' => true, 'message' => 'Status kategori diperbarui.', 'row' => $category]);
    }

    // ---- Ukuran ----
    public function storeSize(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:jersey_sizes,name',
            'sort_order' => 'nullable|integer|min:0',
            'price_surcharge' => 'nullable|numeric|min:0',
        ]);
        $row = JerseySize::create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'price_surcharge' => $data['price_surcharge'] ?? 0,
        ]);
        return response()->json(['success' => true, 'message' => 'Ukuran berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateSize(Request $request, JerseySize $size)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:jersey_sizes,name,' . $size->id,
            'sort_order' => 'nullable|integer|min:0',
            'price_surcharge' => 'nullable|numeric|min:0',
        ]);
        $size->update([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'price_surcharge' => $data['price_surcharge'] ?? 0,
        ]);
        return response()->json(['success' => true, 'message' => 'Ukuran berhasil diperbarui.', 'row' => $size]);
    }

    public function toggleSize(JerseySize $size)
    {
        $size->update(['is_active' => !$size->is_active]);
        return response()->json(['success' => true, 'message' => 'Status ukuran diperbarui.', 'row' => $size]);
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
        $row = JerseyPriceSetting::create($data)->load(['productType', 'category']);
        return response()->json(['success' => true, 'message' => 'Tier harga berhasil ditambahkan.', 'row' => $row]);
    }

    public function destroyPriceSetting(JerseyPriceSetting $priceSetting)
    {
        $priceSetting->delete();
        return response()->json(['success' => true, 'message' => 'Tier harga berhasil dihapus.']);
    }
}
